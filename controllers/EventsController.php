<?php
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../models/Event.php';

class EventsController
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function index(): void
    {
        $searchTerm = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
        $toDate     = isset($_GET['to_date']) ? trim((string)$_GET['to_date']) : '';

        $conditions = [];
        $params     = [];

        if ($searchTerm !== '') {
            $conditions[] = 'title LIKE ?';
            $params[]     = '%' . $searchTerm . '%';
        }

        if ($toDate !== '') {
            $conditions[] = 'DATE(endDate) <= ?';
            $params[]     = $toDate;
        }

        $sql = 'SELECT eid, title, endDate, gift1, gift2, gift3 FROM events';
        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $sql .= ' ORDER BY eid DESC';

        if (!empty($params)) {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();
        } else {
            $stmt = $this->pdo->query($sql);
            $rows = $stmt->fetchAll();
        }

        $events = [];
        foreach ($rows as $row) {
            $events[] = new Event(
                (int)$row['eid'],
                $row['title'],
                $row['endDate'],
                (int)$row['gift1'],
                $row['gift2'],
                $row['gift3']
            );
        }

        include __DIR__ . '/../views/events/index.php';
    }

    public function create(): void
    {
        $error = '';
        $title = '';
        $endDate = '';
        $gift1 = '';
        $gift2 = '';
        $gift3 = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title   = trim($_POST['title'] ?? '');
            $endDate = trim($_POST['endDate'] ?? '');
            $gift1   = trim($_POST['gift1'] ?? '0');
            $gift2   = trim($_POST['gift2'] ?? '');
            $gift3   = trim($_POST['gift3'] ?? '');

            if ($title === '') {
                $error = 'Title is required';
            } else {
                $stmt = $this->pdo->prepare('INSERT INTO events (title, endDate, gift1, gift2, gift3) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([
                    $title,
                    $endDate !== '' ? $endDate : date('Y-m-d H:i:s'),
                    (int)$gift1,
                    $gift2,
                    $gift3,
                ]);
                header('Location: index.php?controller=events&action=index');
                exit;
            }
        }

        include __DIR__ . '/../views/events/create.php';
    }

    public function edit(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        $stmt = $this->pdo->prepare('SELECT eid, title, endDate, gift1, gift2, gift3 FROM events WHERE eid = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            http_response_code(404);
            echo 'Event not found';
            return;
        }

        $event = new Event(
            (int)$row['eid'],
            $row['title'],
            $row['endDate'],
            (int)$row['gift1'],
            $row['gift2'],
            $row['gift3']
        );

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title   = trim($_POST['title'] ?? '');
            $endDate = trim($_POST['endDate'] ?? '');
            $gift1   = trim($_POST['gift1'] ?? '0');
            $gift2   = trim($_POST['gift2'] ?? '');
            $gift3   = trim($_POST['gift3'] ?? '');

            if ($title === '') {
                $error = 'Title is required';
            } else {
                $stmt = $this->pdo->prepare('UPDATE events SET title = ?, endDate = ?, gift1 = ?, gift2 = ?, gift3 = ? WHERE eid = ?');
                $stmt->execute([
                    $title,
                    $endDate !== '' ? $endDate : date('Y-m-d H:i:s'),
                    (int)$gift1,
                    $gift2,
                    $gift3,
                    $event->getEid(),
                ]);
                header('Location: index.php?controller=events&action=index');
                exit;
            }
        }

        include __DIR__ . '/../views/events/edit.php';
    }

    public function delete(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // First delete related event_players rows to respect foreign key
            $stmt = $this->pdo->prepare('DELETE FROM event_players WHERE eid = ?');
            $stmt->execute([$id]);

            // Then delete the event itself
            $stmt = $this->pdo->prepare('DELETE FROM events WHERE eid = ?');
            $stmt->execute([$id]);
            header('Location: index.php?controller=events&action=index');
            exit;
        }

        $stmt = $this->pdo->prepare('SELECT eid, title FROM events WHERE eid = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            http_response_code(404);
            echo 'Event not found';
            return;
        }

        $title = $row['title'];
        include __DIR__ . '/../views/events/delete.php';
    }
}
