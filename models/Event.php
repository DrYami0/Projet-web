<?php
class Event {
    private ?int $eid;
    private string $title;
    private string $endDate;
    private int $gift1;
    private string $gift2;
    private string $gift3;

    public function __construct(?int $eid, string $title, string $endDate, int $gift1, string $gift2, string $gift3) {
        $this->eid     = $eid;
        $this->title   = $title;
        $this->endDate = $endDate;
        $this->gift1   = $gift1;
        $this->gift2   = $gift2;
        $this->gift3   = $gift3;
    }

    public function getEid(): ?int { return $this->eid; }
    public function getTitle(): string { return $this->title; }
    public function getEndDate(): string { return $this->endDate; }
    public function getGift1(): int { return $this->gift1; }
    public function getGift2(): string { return $this->gift2; }
    public function getGift3(): string { return $this->gift3; }

    public function setTitle(string $title): void { $this->title = $title; }
    public function setEndDate(string $endDate): void { $this->endDate = $endDate; }
    public function setGift1(int $gift1): void { $this->gift1 = $gift1; }
    public function setGift2(string $gift2): void { $this->gift2 = $gift2; }
    public function setGift3(string $gift3): void { $this->gift3 = $gift3; }
}
