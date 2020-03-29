<?php
namespace TymFrontiers\Helper {
  require_once "HelperVars.php";
  // Admin settings conf
  function setting_variant (string $regex){
    $output = [
      "minlen" => 0,
      "maxlen" => 0,
      "minval" => 0,
      "maxval" => 0,
      "mindate" => 0,
      "maxdate" => 0,
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
