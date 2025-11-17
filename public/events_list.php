<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Event.php';
require_once __DIR__ . '/../repositories/EventRepository.php';

$pdo  = Database::getConnection();
$repo = new EventRepository($pdo);
$events = $repo->findAll();
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Events list</title>
</head>
<body>
    <h1>Events</h1>
    <a href="index.php">Home</a> | <a href="events_create.php">Create Event</a>
    <ul>
        <?php foreach ($events as $event): ?>
            <li>
                <?= htmlspecialchars($event->getName(), ENT_QUOTES, 'UTF-8') ?>
                [<a href="events_edit.php?id=<?= $event->getId() ?>">Edit</a>]
                [<a href="events_delete.php?id=<?= $event->getId() ?>">Delete</a>]
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
