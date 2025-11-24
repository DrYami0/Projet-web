<?php
// controller/add-payment.php
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
$card = [
    'type' => trim($_POST['type'] ?? 'card'),
    'last4' => trim($_POST['last4'] ?? null),
    'expiry_month' => trim($_POST['expiry_month'] ?? null),
    'expiry_year' => trim($_POST['expiry_year'] ?? null),
    'billing_address' => trim($_POST['billing_address'] ?? null),
];

$ctrl = new \Controller\UserController();
$ctrl->addPaymentMethod($userId, $card);

header('Location: /view/FrontOffice/user-dashboard.php');
exit;