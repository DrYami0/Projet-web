<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Gift.php';
require_once __DIR__ . '/../repositories/GiftRepository.php';

$pdo      = Database::getConnection();
$giftRepo = new GiftRepository($pdo);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$gift = $giftRepo->findById($id);
if (!$gift) {
    http_response_code(404);
    echo 'Gift not found';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $giftRepo->delete($id);
    header('Location: gifts_list.php');
    exit;
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Gift</title>
</head>
<body>
    <h1>Delete Gift</h1>
    <p>Are you sure you want to delete "<?= htmlspecialchars($gift->getName(), ENT_QUOTES, 'UTF-8') ?>"?</p>
    <form method="post">
        <button type="submit">Yes, delete</button>
        <a href="gifts_list.php">Cancel</a>
    </form>
</body>
</html>
