<?php
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../models/EventGift.php';
require_once __DIR__ . '/../models/Gift.php';
require_once __DIR__ . '/../models/Event.php';

class EventGiftsController
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function index(): void
    {
        $eid = isset($_GET['eid']) ? (int)$_GET['eid'] : 0;

        $eventStmt = $this->pdo->prepare('SELECT eid, title FROM events WHERE eid = ?');
        $eventStmt->execute([$eid]);
        $event = $eventStmt->fetch();
        if (!$event) {
            http_response_code(404);
            echo 'Event not found';
            return;
        }

        $stmt = $this->pdo->prepare(
            "SELECT eg.egid, eg.eid, eg.gid, g.name, g.points
             FROM event_gifts eg
             LEFT JOIN gifts g ON eg.gid = g.id
             WHERE eg.eid = ?
             ORDER BY eg.egid DESC"
        );
        $stmt->execute([$eid]);
        $rows = $stmt->fetchAll();

        $eventGifts = [];
        foreach ($rows as $row) {
            $eventGifts[] = [
                'model' => new EventGift(
                    (int)$row['egid'],
                    (int)$row['eid'],
                    (int)$row['gid']
                ),
                'giftName'   => $row['name'] ?? null,
                'giftPoints' => isset($row['points']) ? (int)$row['points'] : null,
            ];
        }

        include __DIR__ . '/../views/event_gifts/index.php';
    }

    public function create(): void
    {
        $eid = isset($_GET['eid']) ? (int)$_GET['eid'] : 0;

        $eventStmt = $this->pdo->prepare('SELECT eid, title FROM events WHERE eid = ?');
        $eventStmt->execute([$eid]);
        $event = $eventStmt->fetch();
        if (!$event) {
            http_response_code(404);
            echo 'Event not found';
            return;
        }

        $giftsStmt = $this->pdo->query('SELECT id, name FROM gifts ORDER BY name');
        $gifts = $giftsStmt->fetchAll();

        $error = '';
        $gid   = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $gid = trim($_POST['gid'] ?? '');

            if ($gid === '') {
                $error = 'Gift is required';
            } else {
                $stmt = $this->pdo->prepare('INSERT INTO event_gifts (eid, gid) VALUES (?, ?)');
                $stmt->execute([
                    $eid,
                    (int)$gid,
                ]);
                header('Location: index.php?controller=eventGifts&action=index&eid=' . $eid);
                exit;
            }
        }

        include __DIR__ . '/../views/event_gifts/create.php';
    }

    public function delete(): void
    {
        $egid = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        $stmt = $this->pdo->prepare('SELECT egid, eid FROM event_gifts WHERE egid = ?');
        $stmt->execute([$egid]);
        $row = $stmt->fetch();

        if (!$row) {
            http_response_code(404);
            echo 'Event gift not found';
            return;
        }

        $eid = (int)$row['eid'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $delStmt = $this->pdo->prepare('DELETE FROM event_gifts WHERE egid = ?');
            $delStmt->execute([$egid]);
            header('Location: index.php?controller=eventGifts&action=index&eid=' . $eid);
            exit;
        }

        include __DIR__ . '/../views/event_gifts/delete.php';
    }
}
