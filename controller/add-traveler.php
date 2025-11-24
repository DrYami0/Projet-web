<?php
// controller/add-traveler.php
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
$trav = [
    'name' => trim($_POST['name'] ?? ''),
    'dob' => trim($_POST['dob'] ?? null),
    'passport_number' => trim($_POST['passport_number'] ?? null),
];

$ctrl = new UserController();
$ctrl->addTraveler($userId, $trav);

header('Location: /view/FrontOffice/user-dashboard.php');
exit;