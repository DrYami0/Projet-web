<?php

class QuizBlank
{
    public int $bid;
    public int $qid;
    public int $position;
    public string $correctAnswer;

    public function __construct(int $bid = 0, int $qid = 0, int $position = 0, string $correctAnswer = '')
    {
        $this->bid = $bid;
        $this->qid = $qid;
        $this->position = $position;
        $this->correctAnswer = $correctAnswer;
    }

    public function getBid(): int
    {
        return $this->bid;
    }

    public function setBid(int $bid): self
    {
        $this->bid = $bid;
        return $this;
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

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;
        return $this;
    }

    public function getCorrectAnswer(): string
    {
        return $this->correctAnswer;
    }

    public function setCorrectAnswer(string $correctAnswer): self
    {
        $this->correctAnswer = $correctAnswer;
        return $this;
    }
}
