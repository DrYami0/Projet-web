<?php

class Quiz
{
    public int $qid;
    public string $paragraph;
    public int $nbBlanks;
    public string $difficulty;
    public ?int $approved;
    public array $blanks = [];

    public function __construct(int $qid = 0, string $paragraph = '', int $nbBlanks = 0, string $difficulty = 'easy', ?int $approved = null, array $blanks = [])
    {
        $this->qid = $qid;
        $this->paragraph = $paragraph;
        $this->nbBlanks = $nbBlanks;
        $this->difficulty = $difficulty;
        $this->approved = $approved;
        $this->blanks = $blanks;
    }

    public function getQid(): int
    {
        return $this->qid;
    }

    public function setQid(int $qid): self
    {
        $this->qid = $qid;
        return $this;
    }

    public function getParagraph(): string
    {
        return $this->paragraph;
    }

    public function setParagraph(string $paragraph): self
    {
        $this->paragraph = $paragraph;
        return $this;
    }

    public function getNbBlanks(): int
    {
        return $this->nbBlanks;
    }

    public function setNbBlanks(int $nbBlanks): self
    {
        $this->nbBlanks = $nbBlanks;
        return $this;
    }

    public function getDifficulty(): string
    {
        return $this->difficulty;
    }

    public function setDifficulty(string $difficulty): self
    {
        $this->difficulty = $difficulty;
        return $this;
    }

    public function getApproved(): ?int
    {
        return $this->approved;
    }

    public function setApproved(?int $approved): self
    {
        $this->approved = $approved;
        return $this;
    }
}
