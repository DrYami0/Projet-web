<?php
class Dictionary
{
    private int $wid;
    private string $word;
    private string $type;
    private string $difficulty;

    public function __construct($word, $type, $difficulty)
    {
        $this->word = $word;
        $this->type = $type;
        $this->difficulty = $difficulty;
    }


    public function getWid(): int
    {
        return $this->wid;
    }

    public function getWord(): string
    {
        return $this->word;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getDifficulty(): string
    {
        return $this->difficulty;
    }

    public function setWord(string $word): void
    {
        $this->word = $word;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function setDifficulty(int $difficulty): void
    {
        $this->difficulty = $difficulty;
    }
}