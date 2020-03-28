<?php
namespace TymFrontiers\Helper;

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

function access_ranks () {
  return [
    "QUEST"       => 0,
    "USER"        => 1,
    "ANALYST"     => 2,
    "ADVERTISER"  => 3,
    "MODERATOR"   => 4,
    "EDITOR"      => 5,
    "ADMIN"       => 6,
    "DEVELOPER"   => 7,
    "SUPERADMIN"  => 8,
    "OWNER"       => 14
  ];
}
function reverse_access_ranks () {
  return [
    0 => "QUEST",
    1 => "USER",
    2 => "ANALYST",
    3 => "ADVERTISER",
    4 => "MODERATOR",
    5 => "EDITOR",
    6 => "ADMIN",
    7 => "DEVELOPER",
    8 => "SUPERADMIN",
    14 => "OWNER"
  ];
}
function email_replace_pattern () {
  return [
    "name" => "%name%",
    "surname" => "%surname%",
    "email" => "%email%",
    "phone" => "%phone%",
    "country" => "%country%",
    "state" => "%state%",
    "city" => "%city%",
    "address" => "%address%",
    "zip_code" => "%zip_code%",
  ];
}
function file_upload_groups (){
  return [
    "image" => [
      'png' => 'image/png',
      'jpg' => 'image/jpeg',
      'jpeg' => 'image/jpeg',
      'jpe' => 'image/jpeg',
      'gif' => 'image/gif'
    ],
    "document" => [
      'pdf' => 'application/pdf',
      'doc' => 'application/msword',
      'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      'rtf' => 'application/rtf',
      'xls' => 'application/vnd.ms-excel',
      'xlsx'=> 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'ppt' => 'application/vnd.ms-powerpoint',
      'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
      'odt' => 'application/vnd.oasis.opendocument.text',
      'ods' => 'application/vnd.oasis.opendocument.spreadsheet'
    ]
  ];
}
