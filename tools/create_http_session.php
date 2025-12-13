<?php
// Create a PHP session file with logged-in user data and output the session id and save_path
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
// regenerate id to be safe
session_regenerate_id(true);
$_SESSION['uid'] = 'admin';
$_SESSION['user_id'] = 1;
echo session_id() . "\n";
echo session_save_path() . "\n";
?>