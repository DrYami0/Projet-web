<?php
class Gift {
    private ?int $id;
    private int $eventId;
    private string $name;

    public function __construct(?int $id, int $eventId, string $name) {
        $this->id      = $id;
        $this->eventId = $eventId;
        $this->name    = $name;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getEventId(): int {
        return $this->eventId;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setEventId(int $eventId): void {
        $this->eventId = $eventId;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }
}
