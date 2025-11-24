<?php
// controller/add-wishlist.php
session_start();
require_once __DIR__ . '/userC.php';
use Controller\UserController;

if (empty($_SESSION['user_id'])) {
    header('Location: /view/FrontOffice/login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /view/FrontOffice/user-dashboard.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$item = [
    'title' => trim($_POST['title'] ?? ''),
    'url' => trim($_POST['url'] ?? ''),
];

$ctrl = new \Controller\UserController();
$ctrl->addWishlistItem($userId, $item);

header('Location: /view/FrontOffice/user-dashboard.php');
exit;