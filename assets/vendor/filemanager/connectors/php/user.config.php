<?php
session_start();
/**
 *	Filemanager PHP connector
 *  This file should at least declare auth() function 
 *  and instantiate the Filemanager as '$fm'
 *  
 *  IMPORTANT : by default Read and Write access is granted to everyone
 *  Copy/paste this file to 'user.config.php' file to implement your own auth() function
 *  to grant access to wanted users only
 *
 *	filemanager.php
 *	use for ckeditor filemanager
 *
 *	@license	MIT License
 *  @author		Simon Georget <simon (at) linea21 (dot) com>
 *	@copyright	Authors
 */



/**
 *	Check if user is authorized
 *	
 *
 *	@return boolean true if access granted, false if no access
 */

function auth() {
  // You can insert your own code over here to check if the user is authorized.
  // If you use a session variable, you've got to start the session first (session_start())
  return true;
}

function unserialize_session($data) {
  $result = array();
  $offset = 0;
  while ($offset < strlen($data)) {
    if (!strstr(substr($data, $offset), '|')) {
        return false;
    }
    $pos = strpos($data, '|', $offset);
    $key = substr($data, $offset, $pos - $offset);
    $offset = $pos + 1;
    switch ($data[$offset]) {
      case 's':
        $pos = strpos($data, ':', $offset + 2);
        $len = (int)substr($data, $offset + 2, $pos - $offset - 2);
        $result[$key] = substr($data, $pos + 2, $len);
        $offset = $pos + 4 + $len;
        break;
      case 'i':
        $pos = strpos($data, ';', $offset + 2);
        $result[$key] = (int)substr($data, $offset + 2, $pos - $offset - 2);
        $offset = $pos + 1;
        break;
      case 'a':
        $pos = strpos($data, '}', $offset) + 1;
        $array_data = substr($data, $offset, $pos - $offset);
        $result[$key] = unserialize($array_data);
        $offset = $pos;
        break;
      default:
        return false;
    }
  }
  return $result;
}

$session_save_path = '/files';
if (isset($_COOKIE['_dispusip'])) {
  $session_id = $_COOKIE['_dispusip'];
  $session_file_path = $session_save_path . '/_dispusip' . $session_id;
  if (file_exists($session_file_path)) {
    $session_data = file_get_contents($session_file_path);
    $session_data = substr($session_data, 59);
    $unserialize = unserialize_session($session_data);
		$folder = $unserialize['folder_museum'];
		$user_role = $unserialize['user']['role'];
  } else {
    echo "File session tidak ditemukan.";
    exit;
  }
} else {
  echo "Cookie tidak ditemukan.";
  exit;
}

$asset_path = "/assets/museum/".$folder;
$upload_path = '../../../../../..'.$asset_path;
if (!is_dir($upload_path)) {
  mkdir($upload_path, 0777, TRUE);
  mkdir($upload_path . '/bin', 0777, TRUE);
}
$fm = new Filemanager();
$fm->setFileRoot('website2024'.$asset_path .'/');
?>