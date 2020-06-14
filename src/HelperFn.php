<?php
namespace TymFrontiers\Helper {
  use \TymFrontiers\MultiForm,
      \TymFrontiers\InstanceError;
  require_once "HelperVars.php";
  // Admin settings conf
  function setting_variant (string $regex){
    $output = [
      "optiontype" => "", // "radio", "checkbox"
      "minval" => 0,
      "maxval" => 0,
      "options" => [],
    ];
    $regex = \explode("-;", $regex);
    if (empty($regex)) return false;
    foreach ($regex as $var) {
      $key_val = \explode("-:", $var);
      if (\count($key_val) !== 2) return false;
      list($key, $val) = $key_val;
      if ($key == 'options') {
        foreach (\explode("-,",$val) as $opt) {
          $output["options"][] = $opt;
        }
      } else {
        if (\array_key_exists($key, $output)) $output[$key] = $val;
      }
    }
    return $output;
  }
  function setting_get_value (string $user, string $key, string $domain = PRJ_DOMAIN) {
    global $database;
    $user = $database->escapeValue("{$domain}\\{$user}");
    if (!\defined("MYSQL_BASE_DB")) throw new \Exception("Database for settings [MYSQL_BASE_DB] not defined.", 1);

    global $database;
    $found = (new \TymFrontiers\MultiForm(MYSQL_BASE_DB, "setting", "id"))
      ->findBySql("SELECT sval FROM :db:.:tbl: WHERE user='{$user}' AND skey='{$database->escapeValue($key)}' LIMIT 1");
    return $found ? $found[0]->sval : null;
  }
  function setting_set_value (string $user, string $key, $value, string $domain = PRJ_DOMAIN) {
    global $database;
    $key = $database->escapeValue($key);
    if (!\defined("MYSQL_BASE_DB")) throw new \Exception("Database for settings [MYSQL_BASE_DB] not defined.", 1);
    $key_prop = (new \TymFrontiers\MultiForm(MYSQL_BASE_DB, "setting_option", "id"))
      ->findBySql("SELECT *
                   FROM :db:.:tbl:
                   WHERE `domain` = '{$database->escapeValue($domain)}'
                   AND `name` = '{$key}'
                   LIMIT 1");
    if (!$key_prop) throw new \Exception("Setting property not found \r\n" . $database->last_query, 1);
    $key_prop = $key_prop[0];
    $is_new = true;
    $find_user = "{$domain}\\\\{$user}";
    if (!(bool)$key_prop->multi_val && $set = (new \TymFrontiers\MultiForm(MYSQL_BASE_DB, "setting", "id"))->findBySql("SELECT * FROM :db:.:tbl: WHERE `user` = '{$find_user}' AND skey='{$key}' LIMIT 1")) {
      $set = $set[0];
      $is_new = false;
    } else {
      $set = new \TymFrontiers\MultiForm(MYSQL_BASE_DB, "setting", "id");
    }
    // validate [value] presented
    // get expexted value
    $rqp = [];
    $typev = empty($key_prop->type_variant) ? false : \TymFrontiers\Helper\setting_variant($key_prop->type_variant);
    $filt_arr = ["value", $key_prop->type];
    if (\in_array($key_prop->type, ["username","text","html","markdown","mixed","script","date","time","datetime","int","float"])) {
      $filt_arr[2] = !empty($typev["minval"]) ? $typev["minval"] : 0;
      $filt_arr[3] = !empty($typev["maxval"]) ? $typev["maxval"] : 0;
    } if ($key_prop->type == "option" && !empty($typev["optiontype"]) && $typev["optiontype"]=="checkbox") {
      $filt_arr[1] = "text";
      $filt_arr[2] = 3;
      $filt_arr[3] = 127;
    } if ($key_prop->type == "option" && !empty($typev["optiontype"]) && $typev["optiontype"]=="radio") {
      if (empty($typev["options"])) {
        throw new \Exception("No pre-set options for this setting, contact Developer", 1);
      }
      $filt_arr[2] = $typev["options"];
    }
    $rqp["value"] = $filt_arr;
    $gen = new \TymFrontiers\Generic;
    $params = $gen->requestParam($rqp,["value" => $value], ["value"]);
    if (!$params || !empty($gen->errors)) {
      $errors = (new \TymFrontiers\InstanceError($gen,true))->get("requestParam",true);
      $errors = \implode("\r\n",$errors);
      throw new \Exception($errors, 1);
    }
    $value = $database->escapeValue($params['value']);
    if (!$is_new) {
      // run update
      $set->sval = $value;
    } else {
      $set->user = "{$domain}\\{$user}";
      $set->skey = $key;
      $set->sval = $value;
    }
    if ($set->save()) {
      return true;
    }
    $set->mergeErrors();
    $errors = (new \TymFrontiers\InstanceError($set,true))->get("",true);
    $errors = \implode("\r\n",$errors);
    throw new \Exception($errors, 1);
  }
  function setting_set_file_default(string $user, string $set_key, int $file_id, bool $set_multiple = false) {
    global $database;
    $user = $database->escapeValue($user);
    $set_key = $database->escapeValue($set_key);
    if ((new MultiForm(MYSQL_FILE_DB, "file_default","id"))->findBySql("SELECT * FROM :db:.:tbl: WHERE `user`='{$user}' AND `set_key` = '{$set_key}' AND `file_id` = {$file_id} LIMIT 1")) {
      // already set
      return true;
    }
    if (!$set_multiple) { // delete if previously set
      $file_db = MYSQL_FILE_DB;
      $database->query("DELETE FROM `{$file_db}`.file_default WHERE `user`='{$user}' AND `set_key`='{$set_key}'");
    }
    $set = new MultiForm(MYSQL_FILE_DB, "file_default", "id");
    $set->user = $user;
    $set->set_key = $set_key;
    $set->file_id = $file_id;
    if (!$set->save()) {
      $set->mergeErrors();
      $errors = [];
      $more_err = (new InstanceError($set))->get("",true);
      if (!empty($more_err)) {
        foreach ($more_err as $method=>$errs) {
          foreach ($errs as $err){
            $errors[] = $err;
          }
        }
      }
      throw new \Exception("Failed to save file-default setting. [Error]: \r\n" . \implode("\r\n",$errors), 1);
    }
    return true;
  }
  function setting_get_file_default(string $user, string $set_key) {
    global $database;
    $file_db = MYSQL_FILE_DB;
    $file_tbl = MYSQL_FILE_TBL;
    $whost = WHOST;
    $user = $database->escapeValue($user);
    $set_key = $database->escapeValue($set_key);
    $query = "SELECT fd.id, fd.user, fd.set_key,
                     fi.id AS file_id, fi.type_group AS file_type, fi._type AS file_mime, fi.caption AS file_caption, fi._size AS file_size,
                     CONCAT('{$whost}/file/', fi._name) AS url
              FROM :db:.:tbl: AS fd
              LEFT JOIN `{$file_db}`.`{$file_tbl}` AS fi ON fi.id = fd.file_id
              WHERE fd.user = '{$user}'
              AND fd.set_key = '{$set_key}'";
    return (new MultiForm(MYSQL_FILE_DB, "file_default", "id"))->findBySql($query);
  }
  function setting_check_file_default(int $file_id) {
    global $database;
    if ($set = (new MultiForm(MYSQL_FILE_DB, "file_default", "id"))->findBySql("SELECT * FROM :db:.:tbl: WHERE `file_id` = {$file_id}")) {
      $set_r = [];
      foreach ($set as $st) {
        $set_r[] = [
          "id" => $st->id,
          "user" => $st->user,
          "key" => $st->set_key,
        ];
      }
      return $set_r;
    }
    return [];
  }
  function destroy_cookie (string $cname) {
    global $_COOKIE;
    if (isset($_COOKIE[$cname])) {
      unset($_COOKIE[$cname]);
      \setcookie($cname, null, -1, '/');
      return true;
    }
    return false;
  }

  function email_mask ( string $email, string $mask_char="*", int $percent=50 ){
    list( $user, $domain ) = \preg_split("/@/", $email );
    $len = \strlen( $user );
    $mask_count = \floor( $len * $percent /100 );
    $offset = \floor( ( $len - $mask_count ) / 2 );
    $masked = \substr( $user, 0, $offset )
      . \str_repeat( $mask_char, $mask_count )
      . \substr( $user, $mask_count+$offset );

    return( $masked.'@'.$domain );
  }

  function phone_mask (string $number){
    $mask_number =  \str_repeat("*", \strlen($number)-4) . \substr($number, -4);
    return $mask_number;
  }
  function file_set(string $mime){
    global $file_upload_groups;
    $return = "unknown";
    foreach($file_upload_groups as $type=>$arr){
      if( \in_array($mime,$arr) ){
        $return = $type;
        break;
      }
    }
    return $return;
  }
  function auth_errors (\TymFrontiers\API\Authentication $auth, string $message, string $errname, bool $override=true) {
    $auth_errors = (new \TymFrontiers\InstanceError ($auth,$override))->get($errname,true);
    $out_errors = [
    "Message" => $message
    ];
    $i=0;
    if (!empty($auth_errors)) {
      foreach ($auth_errors as $err) {
        $out_errors["Error-{$i}"] = $err;
        $i++;
      }
    }
    $out_errors["Status"] = "1" . (\count($out_errors) - 1);
    return $out_errors;
  }
  function setup_page(string $page_name, string $page_group = "base", bool $show_dnav = true, int $dnav_ini_top_pos=0, string $dnav_stick_on='#page-head',string $dnav_clear_elem = '#main-content', string $dnav_pos = "affix"){
    $set = "<input ";
    $set .=   "type='hidden' ";
    $set .=   "id='setup-page' ";
    $set .=   "data-show-nav = {$show_dnav} ";
    $set .=   "data-group = '{$page_group}' ";
    $set .=   "data-name = '{$page_name}' ";
    $set .= ">";
    $set .= "<input ";
    $set .=   "type='hidden' ";
    $set .=   "id='setup-dnav' ";
    $set .=   "data-clear-elem='{$dnav_clear_elem}' ";
    $set .=   "data-ini-top-pos={$dnav_ini_top_pos} ";
    $set .=   "data-pos='{$dnav_pos}' ";
    $set .=   "data-stick-on='{$dnav_stick_on}' ";
    $set .= ">";
    echo $set;
  }
  function file_size_unit($bytes) {
    if ($bytes >= 1073741824) {
      $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
      $bytes = number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
      $bytes = number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
      $bytes = $bytes . ' bytes';
    } elseif ($bytes == 1) {
      $bytes = $bytes . ' byte';
    } else {
      $bytes = '0 bytes';
    }
    return $bytes;
  }
}

// apache_request_headers
namespace {
  if ( !\function_exists('apache_request_headers') ) {
    function apache_request_headers() {
      // Based on: http://www.iana.org/assignments/message-headers/message-headers.xml#perm-headers
      $arrCasedHeaders = [
        'Dasl'             => 'DASL',
        'Dav'              => 'DAV',
        'Etag'             => 'ETag',
        'Mime-Version'     => 'MIME-Version',
        'Slug'             => 'SLUG',
        'Te'               => 'TE',
        'Www-Authenticate' => 'WWW-Authenticate',
        // MIME
        'Content-Md5'      => 'Content-MD5',
        'Content-Id'       => 'Content-ID',
        'Content-Features' => 'Content-features'
      ];
      $arrHttpHeaders = [];

      foreach($_SERVER as $strKey => $mixValue) {
        if('HTTP_' === \substr($strKey, 0, 5)) {
          $strHeaderKey = \strtolower(\substr($strKey, 5));
          $arrHeaderKey = \explode('_', $strHeaderKey);

          if(0 < count($arrHeaderKey)) {
            $arrHeaderKey = \array_map('ucfirst', $arrHeaderKey);
            $strHeaderKey = \implode('-', $arrHeaderKey);
          } else {
            $strHeaderKey = \ucfirst($strHeaderKey);
          }

          if( \array_key_exists($strHeaderKey, $arrCasedHeaders)) {
            $strHeaderKey = $arrCasedHeaders[$strHeaderKey];
          }

          $arrHttpHeaders[$strHeaderKey] = $mixValue;
        }
      }
      return $arrHttpHeaders;
    }
  }
}
