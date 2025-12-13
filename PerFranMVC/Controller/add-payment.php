<?php

session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/UserC.php';

if (empty($_SESSION['uid'])) {
    header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'user-dashboard.php');
    exit;
}

$username = $_SESSION['uid'];
$userC = new UserC();
$user = $userC->findByUsername($username);

if (!$user) {
    header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/login.php');
    exit;
}

$userId = (int)$user['uid'];

$card = [
    'type' => trim($_POST['type'] ?? 'card'),
    'last4' => trim($_POST['last4'] ?? null),
    'expiry_month' => trim($_POST['expiry_month'] ?? null),
    'expiry_year' => trim($_POST['expiry_year'] ?? null),
    'billing_address' => trim($_POST['billing_address'] ?? null),
];

try {
    $pdo = obtenirPDO();
    $stmt = $pdo->prepare("INSERT INTO user_payments (user_id, type, last4, expiry_month, expiry_year, billing_address) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $card['type'], $card['last4'], $card['expiry_month'], $card['expiry_year'], $card['billing_address']]);
} catch (Exception $e) {
}

header('Location: ' . BASE_URL . 'user-dashboard.php');
exit;