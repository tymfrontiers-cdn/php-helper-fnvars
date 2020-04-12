<?php
// Helper viariables
$access_ranks = [
  "GUEST"       => 0,
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
$reverse_access_ranks = [
  0 => "GUEST",
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
$email_replace_pattern = [
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
$file_upload_groups = [
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
