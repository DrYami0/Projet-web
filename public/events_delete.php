<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Event.php';
require_once __DIR__ . '/../repositories/EventRepository.php';

$pdo  = Database::getConnection();
$repo = new EventRepository($pdo);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$event = $repo->findById($id);
if (!$event) {
    http_response_code(404);
    echo 'Event not found';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $repo->delete($id);
    header('Location: events_list.php');
    exit;
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Event</title>
</head>
<body>
    <h1>Delete Event</h1>
    <p>Are you sure you want to delete "<?= htmlspecialchars($event->getName(), ENT_QUOTES, 'UTF-8') ?>"?</p>
    <form method="post">
        <button type="submit">Yes, delete</button>
        <a href="events_list.php">Cancel</a>
    </form>
</body>
</html>
