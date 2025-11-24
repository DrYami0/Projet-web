<?php

session_start();
require_once __DIR__ . '/config.php';


if (empty($_SESSION['uid'])) {
    header('Location: ' . BASE_URL . 'view/FrontOffice/login.php');
    exit;
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'view/FrontOffice/user-dashboard.php');
    exit;
}

$username = $_SESSION['uid']; 


$stmt = $pdo->prepare("SELECT avatar FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);


if ($user && !empty($user['avatar'])) {
    $avatarPath = str_replace(BASE_URL, '', $user['avatar']);
    $avatarFullPath = __DIR__ . '/../' . $avatarPath;
    if (file_exists($avatarFullPath)) {
        @unlink($avatarFullPath);
    }
}


$stmt = $pdo->prepare("DELETE FROM users WHERE username = ?");
$stmt->execute([$username]);


$jsonFile = __DIR__ . '/approved/' . $username . '.json';

if (file_exists($jsonFile)) {
    @unlink($jsonFile);
}


session_unset();
session_destroy();


session_start();
$_SESSION['delete_success'] = true;


header('Location: ' . BASE_URL . 'view/FrontOffice/login.php');
exit;