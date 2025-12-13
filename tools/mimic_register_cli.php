<?php
// Mimic the register-face.php fallback path for debugging via CLI
session_start();
// populate session variables as if logged in
$_SESSION['uid'] = 'admin';
$_SESSION['user_id'] = '1';
// simulate a base64 image post by reading existing tmp file and placing base64 in POST
$tmpSrc = __DIR__ . '/../tmp/face_input.png';
if (!file_exists($tmpSrc)) {
    echo "Source tmp image missing: $tmpSrc\n";
    exit(1);
}
$data = base64_encode(file_get_contents($tmpSrc));
$_POST['image'] = $data;
// include the controller (it will run and echo JSON)
chdir(__DIR__ . '/../Controller');
require 'register-face.php';
?>
