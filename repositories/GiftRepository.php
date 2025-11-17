<?php
require_once __DIR__ . '/../models/Gift.php';

class GiftRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function create(Gift $gift): int {
        $stmt = $this->pdo->prepare(
            "INSERT INTO gifts (event_id, name) VALUES (:event_id, :name)"
        );
        $stmt->execute([
            ':event_id' => $gift->getEventId(),
            ':name'     => $gift->getName(),
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function findAll(): array {
        $stmt = $this->pdo->query("SELECT id, event_id, name FROM gifts ORDER BY id DESC");
        $rows = $stmt->fetchAll();
        $gifts = [];
        foreach ($rows as $row) {
            $gifts[] = new Gift((int)$row['id'], (int)$row['event_id'], $row['name']);
        }
        return $gifts;
    }

    public function findById(int $id): ?Gift {
        $stmt = $this->pdo->prepare(
            "SELECT id, event_id, name FROM gifts WHERE id = :id"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        return new Gift((int)$row['id'], (int)$row['event_id'], $row['name']);
    }

    public function findByEvent(int $eventId): array {
        $stmt = $this->pdo->prepare(
            "SELECT id, event_id, name FROM gifts WHERE event_id = :event_id ORDER BY id DESC"
        );
        $stmt->execute([':event_id' => $eventId]);
        $rows = $stmt->fetchAll();
        $gifts = [];
        foreach ($rows as $row) {
            $gifts[] = new Gift((int)$row['id'], (int)$row['event_id'], $row['name']);
        }
        return $gifts;
    }

    public function update(Gift $gift): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE gifts SET event_id = :event_id, name = :name WHERE id = :id"
        );
        return $stmt->execute([
            ':event_id' => $gift->getEventId(),
            ':name'     => $gift->getName(),
            ':id'       => $gift->getId(),
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM gifts WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
