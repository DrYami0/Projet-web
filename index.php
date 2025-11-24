<?php
session_start();


define('SECURE_ACCESS', true);


require_once __DIR__ . '/require/config.php';


if (isset($_SESSION['user']) && !empty($_SESSION['user']['id'])) {

    header('Location: ' . _BASE_URL_ . 'view/dashboard.php');
    exit;
} else {
    
    header('Location: ' . _BASE_URL_ . 'view/auth/login.php');
    exit;
}