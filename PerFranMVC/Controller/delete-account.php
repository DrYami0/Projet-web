<?php

session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/UserC.php';

if (empty($_SESSION['uid'])) {
    header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/account-delete.php');
    exit;
}

$username = $_SESSION['uid'];
$userC = new UserC();

$user = $userC->findByUsername($username);

if ($user && !empty($user['uid'])) {
    // Set deleted_at so user appears in "Supprimé" list in admin
    $userC->softDeleteByUsername($username);
}

session_unset();
session_destroy();

session_start();
$_SESSION['delete_success'] = true;

header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/login.php');
exit;