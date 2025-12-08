<?php
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../models/Gift.php';

class GiftsController
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function index(): void
    {
        $searchTerm = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
        $minPoints  = isset($_GET['min_points']) ? trim((string)$_GET['min_points']) : '';

        $conditions = [];
        $params     = [];

        if ($searchTerm !== '') {
            $conditions[] = 'name LIKE ?';
            $params[]     = '%' . $searchTerm . '%';
        }

        if ($minPoints !== '' && is_numeric($minPoints)) {
            $conditions[] = 'points >= ?';
            $params[]     = (int)$minPoints;
        }

        $sql = 'SELECT id, name, points FROM gifts';
        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $sql .= ' ORDER BY id DESC';

        if (!empty($params)) {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();
        } else {
            $stmt = $this->pdo->query($sql);
            $rows = $stmt->fetchAll();
        }

        $gifts = [];
        foreach ($rows as $row) {
            $gifts[] = new Gift(
                (int)$row['id'],
                $row['name'],
                (int)$row['points']
            );
        }

        include __DIR__ . '/../views/gifts/index.php';
    }

    public function create(): void
    {
        $error  = '';
        $name   = '';
        $points = '0';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name   = trim($_POST['name'] ?? '');
            $points = trim($_POST['points'] ?? '0');

            if ($name === '') {
                $error = 'Name is required';
            } elseif (!is_numeric($points) || (int)$points <= 10) {
                $error = 'Points must be a number greater than 10';
            } else {
                $stmt = $this->pdo->prepare('INSERT INTO gifts (name, points) VALUES (?, ?)');
                $stmt->execute([
                    $name,
                    (int)$points,
                ]);
                header('Location: index.php?controller=gifts&action=index');
                exit;
            }
        }

        include __DIR__ . '/../views/gifts/create.php';
    }

    public function edit(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        $stmt = $this->pdo->prepare('SELECT id, name, points FROM gifts WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            http_response_code(404);
            echo 'Gift not found';
            return;
        }

        $gift = new Gift(
            (int)$row['id'],
            $row['name'],
            (int)$row['points']
        );

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name   = trim($_POST['name'] ?? '');
            $points = trim($_POST['points'] ?? '0');

            if ($name === '') {
                $error = 'Name is required';
            } elseif (!is_numeric($points) || (int)$points <= 10) {
                $error = 'Points must be a number greater than 10';
            } else {
                $stmt = $this->pdo->prepare('UPDATE gifts SET name = ?, points = ? WHERE id = ?');
                $stmt->execute([
                    $name,
                    (int)$points,
                    $gift->getId(),
                ]);
                header('Location: index.php?controller=gifts&action=index');
                exit;
            }
        }

        include __DIR__ . '/../views/gifts/edit.php';
    }

    public function delete(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $this->pdo->prepare('DELETE FROM gifts WHERE id = ?');
            $stmt->execute([$id]);
            header('Location: index.php?controller=gifts&action=index');
            exit;
        }

        $stmt = $this->pdo->prepare('SELECT id, name FROM gifts WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            http_response_code(404);
            echo 'Gift not found';
            return;
        }

        $name = $row['name'];
        include __DIR__ . '/../views/gifts/delete.php';
    }
}
