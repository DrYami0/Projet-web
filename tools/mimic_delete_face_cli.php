<?php
// Mimic a logged-in session for CLI testing of delete-face-data.php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
// Set these to match the test user used earlier
$_SESSION['uid'] = 'admin';
$_SESSION['user_id'] = 1;
// Include the controller which will echo JSON
require __DIR__ . '/../PerFranMVC/Controller/delete-face-data.php';
