<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../Controller/DictionaryC.php';

// Check if user is admin
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] == 1;
if (!$is_admin) {
    header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/login.php');
    exit();
}

if (isset($_GET['wid'])) {
    $wid = intval($_GET['wid']);
    $dictC = new DictionaryC();
    $dictC->deleteWord($wid);
    header('Location: DisplayWords.php?deleted=1');
    exit;
} else {
    header('Location: DisplayWords.php?error=1');
    exit;
}