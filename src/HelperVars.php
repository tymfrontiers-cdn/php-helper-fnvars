<?php
use \TymFrontiers\File;
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
$reverse_access_ranks = \array_flip($access_ranks);
$email_replace_pattern = [
  "name" => "%name%",
  "surname" => "%surname%",
  "userid" => "%userid%",
  "email" => "%email%",
  "phone" => "%phone%",
  "country" => "%country%",
  "state" => "%state%",
  "city" => "%city%",
  "address" => "%address%",
  "zip_code" => "%zip_code%",
];
$file_upload_groups = [];
foreach ((new File)->types as $file_group => $file_types) {
  $file_upload_groups[$file_group] = $file_types;
  // foreach (\array_unique(\array_keys($file_types)) as $ext) {
  //   $file_upload_groups[$file_group][] = ".{$ext}";
  // }
  // foreach (\array_unique(\array_values($file_types)) as $mim) {
  //   $file_upload_groups[$file_group][] = $mim;
  // }
}
