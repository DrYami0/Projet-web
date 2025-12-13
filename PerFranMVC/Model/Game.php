<?php

class Game
{
    // Attributes
    private $gid;
    private $title;
    private $game;
    private $difficulty;
    private $type;
    private $player1id;
    private $player2id;
    private $status;
    private $createdAt;
    private $startedAt;
    private $endedAt;
    private $winner;
    private $rounds_played;
    private $game_state;

    // Constructor
    public function __construct($title = null, $game = null, $difficulty = null, $type = null, $player1id = null, $player2id = null, $status = 'waiting', $createdAt = null, $startedAt = null, $endedAt = null, $winner = null, $rounds_played = 0, $game_state = null) {
        $this->title = $title;
        $this->game = $game;
        $this->difficulty = $difficulty;
        $this->type = $type;
        $this->player1id = $player1id;
        $this->player2id = $player2id;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->startedAt = $startedAt;
        $this->endedAt = $endedAt;
        $this->winner = $winner;
        $this->rounds_played = $rounds_played;
        $this->game_state = $game_state;
    }

    // Getters
    public function getGid() { return $this->gid; }
    public function getTitle() { return $this->title; }
    public function getGame() { return $this->game; }
    public function getDifficulty() { return $this->difficulty; }
    public function getType() { return $this->type; }
    public function getPlayer1id() { return $this->player1id; }
    public function getPlayer2id() { return $this->player2id; }
    public function getStatus() { return $this->status; }
    public function getCreatedAt() { return $this->createdAt; }
    public function getStartedAt() { return $this->startedAt; }
    public function getEndedAt() { return $this->endedAt; }
    public function getWinner() { return $this->winner; }
    public function getRoundsPlayed() { return $this->rounds_played; }
    public function getGameState() { return $this->game_state; }

    // Setters (fluent interface)
    public function setGid($gid) { $this->gid = $gid; return $this; }
    public function setTitle($title) { $this->title = $title; return $this; }
    public function setGame($game) { $this->game = $game; return $this; }
    public function setDifficulty($difficulty) { $this->difficulty = $difficulty; return $this; }
    public function setType($type) { $this->type = $type; return $this; }
    public function setPlayer1id($player1id) { $this->player1id = $player1id; return $this; }
    public function setPlayer2id($player2id) { $this->player2id = $player2id; return $this; }
    public function setStatus($status) { $this->status = $status; return $this; }
    public function setCreatedAt($createdAt) { $this->createdAt = $createdAt; return $this; }
    public function setStartedAt($startedAt) { $this->startedAt = $startedAt; return $this; }
    public function setEndedAt($endedAt) { $this->endedAt = $endedAt; return $this; }
    public function setWinner($winner) { $this->winner = $winner; return $this; }
    public function setRoundsPlayed($rounds_played) { $this->rounds_played = $rounds_played; return $this; }
    public function setGameState($game_state) { $this->game_state = $game_state; return $this; }
}
?>