<?php
require_once __DIR__ . '/../models/Event.php';

class EventRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function create(Event $event): int {
        $stmt = $this->pdo->prepare("INSERT INTO events (name) VALUES (:name)");
        $stmt->execute([':name' => $event->getName()]);
        return (int)$this->pdo->lastInsertId();
    }

    public function findAll(): array {
        $stmt = $this->pdo->query("SELECT id, name FROM events ORDER BY id DESC");
        $rows = $stmt->fetchAll();
        $events = [];
        foreach ($rows as $row) {
            $events[] = new Event((int)$row['id'], $row['name']);
        }
        return $events;
    }

    public function findById(int $id): ?Event {
        $stmt = $this->pdo->prepare("SELECT id, name FROM events WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        return new Event((int)$row['id'], $row['name']);
    }

    public function update(Event $event): bool {
        $stmt = $this->pdo->prepare("UPDATE events SET name = :name WHERE id = :id");
        return $stmt->execute([
            ':name' => $event->getName(),
            ':id'   => $event->getId(),
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM events WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
