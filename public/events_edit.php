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

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    if ($name === '') {
        $error = 'Name is required';
    } else {
        $event->setName($name);
        $repo->update($event);
        header('Location: events_list.php');
        exit;
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Event</title>
</head>
<body>
    <h1>Edit Event</h1>
    <a href="events_list.php">Back to list</a>
    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <form method="post">
        <label>Name:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($event->getName(), ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit">Update</button>
    </form>
</body>
</html>
