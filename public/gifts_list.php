<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Gift.php';
require_once __DIR__ . '/../repositories/GiftRepository.php';
require_once __DIR__ . '/../models/Event.php';
require_once __DIR__ . '/../repositories/EventRepository.php';

$pdo          = Database::getConnection();
$giftRepo     = new GiftRepository($pdo);
$eventRepo    = new EventRepository($pdo);
$gifts        = $giftRepo->findAll();
$eventsById   = [];
foreach ($eventRepo->findAll() as $event) {
    $eventsById[$event->getId()] = $event->getName();
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gifts list</title>
</head>
<body>
    <h1>Gifts</h1>
    <a href="index.php">Home</a> | <a href="gifts_create.php">Create Gift</a>
    <ul>
        <?php foreach ($gifts as $gift): ?>
            <li>
                <?= htmlspecialchars($gift->getName(), ENT_QUOTES, 'UTF-8') ?>
                (Event: <?= htmlspecialchars($eventsById[$gift->getEventId()] ?? 'Unknown', ENT_QUOTES, 'UTF-8') ?>)
                [<a href="gifts_edit.php?id=<?= $gift->getId() ?>">Edit</a>]
                [<a href="gifts_delete.php?id=<?= $gift->getId() ?>">Delete</a>]
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
