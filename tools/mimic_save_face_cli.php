<?php
// CLI mimic for update-profile.php?action=save_face_data
// Sets session and POST data and includes the controller
session_start();
$_SESSION['uid'] = 'admin';
$_SESSION['user_id'] = 1;
$src = __DIR__ . '/../tmp/face_input.png';
if (!file_exists($src)) { echo "Missing source image: $src\n"; exit(1); }
$data = base64_encode(file_get_contents($src));
// populate POST
$_POST['image'] = $data;
// simulate server values for CLI
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
// ensure GET param
$_GET['action'] = 'save_face_data';
chdir(__DIR__ . '/../Controller');
require 'update-profile.php';
?>
