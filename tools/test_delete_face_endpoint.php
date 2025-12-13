<?php
// CLI test runner: sets session for user and invokes PerFranMVC/Controller/delete-face-data.php
require_once __DIR__ . '/../config.php';
session_start();
// set to the user we just tested
$_SESSION['uid'] = 'admin';
$_SESSION['user_id'] = 1;
ob_start();
include __DIR__ . '/../PerFranMVC/Controller/delete-face-data.php';
$out = ob_get_clean();
$log = __DIR__ . '/../tmp/test_delete_face_endpoint.log';
file_put_contents($log, date('c') . "\nOUTPUT:\n" . $out . "\n", FILE_APPEND);
echo $out . PHP_EOL;
