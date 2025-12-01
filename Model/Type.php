<?php


class Type
{

    private int $wid;

    private string $type;

    private string $difficulty;

    public function __construct(string $type, string $difficulty)
    {
        $this->type = $type;
        $this->difficulty = $difficulty;
    }

    // ==================== GETTERS ====================

    public function getWid(): ?int
    {
        return $this->wid;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getDifficulty(): string
    {
        return $this->difficulty;
    }

    // ==================== SETTERS ====================

    public function setWid(?int $wid): void
    {
        $this->wid = $wid;
    }


    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function setDifficulty(string $difficulty): void
    {
        $this->difficulty = $difficulty;
    }

}