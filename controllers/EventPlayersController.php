<?php
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../models/EventPlayer.php';

class EventPlayersController
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function index(): void
    {
        $eid = isset($_GET['eid']) ? (int)$_GET['eid'] : 0;

        // Get event info
        $eventStmt = $this->pdo->prepare('SELECT eid, title FROM events WHERE eid = ?');
        $eventStmt->execute([$eid]);
        $event = $eventStmt->fetch();
        if (!$event) {
            http_response_code(404);
            echo 'Event not found';
            return;
        }

        // Get players for this event, including username from users table
        $stmt = $this->pdo->prepare('
            SELECT ep.epid, ep.eid, ep.uid, ep.score, u.username
            FROM event_players ep
            LEFT JOIN users u ON ep.uid = u.uid
            WHERE ep.eid = ?
            ORDER BY ep.score DESC
        ');
        $stmt->execute([$eid]);
        $rows = $stmt->fetchAll();

        $players = [];
        foreach ($rows as $row) {
            $players[] = [
                'model'    => new EventPlayer(
                    (int)$row['epid'],
                    (int)$row['eid'],
                    (int)$row['uid'],
                    (int)$row['score']
                ),
                'username' => $row['username'] ?? null,
            ];
        }

        include __DIR__ . '/../views/event_players/index.php';
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

        // Load all users for dropdown
        $usersStmt = $this->pdo->query('SELECT uid, username FROM users ORDER BY username');
        $users = $usersStmt->fetchAll();

        $error = '';
        $uid = '';
        $score = '0';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $uid   = trim($_POST['uid'] ?? '');
            $score = trim($_POST['score'] ?? '0');

            if ($uid === '') {
                $error = 'User is required';
            } elseif (!is_numeric($score) || (int)$score < 0) {
                $error = 'Score must be a number greater than or equal to 0';
            } else {
                $stmt = $this->pdo->prepare('INSERT INTO event_players (eid, uid, score) VALUES (?, ?, ?)');
                $stmt->execute([
                    $eid,
                    (int)$uid,
                    (int)$score,
                ]);
                header('Location: index.php?controller=eventPlayers&action=index&eid=' . $eid);
                exit;
            }
        }

        include __DIR__ . '/../views/event_players/create.php';
    }

    public function edit(): void
    {
        $epid = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        $stmt = $this->pdo->prepare('SELECT epid, eid, uid, score FROM event_players WHERE epid = ?');
        $stmt->execute([$epid]);
        $row = $stmt->fetch();

        if (!$row) {
            http_response_code(404);
            echo 'Event player not found';
            return;
        }

        $player = new EventPlayer(
            (int)$row['epid'],
            (int)$row['eid'],
            (int)$row['uid'],
            (int)$row['score']
        );

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $score = trim($_POST['score'] ?? '0');

            if (!is_numeric($score) || (int)$score < 0) {
                $error = 'Score must be a number greater than or equal to 0';
            } else {
                $stmt = $this->pdo->prepare('UPDATE event_players SET score = ? WHERE epid = ?');
                $stmt->execute([
                    (int)$score,
                    $player->getEpid(),
                ]);
                header('Location: index.php?controller=eventPlayers&action=index&eid=' . $player->getEid());
                exit;
            }
        }

        include __DIR__ . '/../views/event_players/edit.php';
    }

    public function delete(): void
    {
        $epid = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        $stmt = $this->pdo->prepare('SELECT epid, eid, uid FROM event_players WHERE epid = ?');
        $stmt->execute([$epid]);
        $row = $stmt->fetch();

        if (!$row) {
            http_response_code(404);
            echo 'Event player not found';
            return;
        }

        $eid  = (int)$row['eid'];
        $uid  = (int)$row['uid'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $delStmt = $this->pdo->prepare('DELETE FROM event_players WHERE epid = ?');
            $delStmt->execute([$epid]);
            header('Location: index.php?controller=eventPlayers&action=index&eid=' . $eid);
            exit;
        }

        include __DIR__ . '/../views/event_players/delete.php';
    }
}
