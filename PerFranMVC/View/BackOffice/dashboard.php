<?php
session_start();
require_once __DIR__ . '/../../../config.php';
if (!isset($totalUsers)) $totalUsers = 0;

// Redirect to the main admin dashboard
header('Location: ' . BASE_URL . 'PerFranMVC/View/BackOffice/admin-dashboard.php');
exit;
?>
