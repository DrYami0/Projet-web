<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Gift.php';
require_once __DIR__ . '/../repositories/GiftRepository.php';
require_once __DIR__ . '/../models/Event.php';
require_once __DIR__ . '/../repositories/EventRepository.php';

$pdo       = Database::getConnection();
$giftRepo  = new GiftRepository($pdo);
$eventRepo = new EventRepository($pdo);
$events    = $eventRepo->findAll();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$gift = $giftRepo->findById($id);
if (!$gift) {
    http_response_code(404);
    echo 'Gift not found';
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $eventId  = (int)($_POST['event_id'] ?? 0);

    if ($name === '' || $eventId === 0) {
        $error = 'Name and event are required';
    } else {
        $gift->setName($name);
        $gift->setEventId($eventId);
        $giftRepo->update($gift);
        header('Location: gifts_list.php');
        exit;
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Gift</title>
</head>
<body>
    <h1>Edit Gift</h1>
    <a href="gifts_list.php">Back to list</a>
    <?php if ($error): ?>
        <p style="color:red;">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>
    <form method="post">
        <label>Name:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($gift->getName(), ENT_QUOTES, 'UTF-8') ?>"><br>
        <label>Event:</label>
        <select name="event_id">
            <?php foreach ($events as $event): ?>
                <option value="<?= $event->getId() ?>" <?= $gift->getEventId() === $event->getId() ? 'selected' : '' ?>>
                    <?= htmlspecialchars($event->getName(), ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select><br>
        <button type="submit">Update</button>
    </form>
</body>
</html>
