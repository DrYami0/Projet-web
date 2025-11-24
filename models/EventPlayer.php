<?php
class EventPlayer {
    private ?int $epid;
    private int $eid;
    private int $uid;
    private int $score;

    public function __construct(?int $epid, int $eid, int $uid, int $score)
    {
        $this->epid  = $epid;
        $this->eid   = $eid;
        $this->uid   = $uid;
        $this->score = $score;
    }

    public function getEpid(): ?int { return $this->epid; }
    public function getEid(): int { return $this->eid; }
    public function getUid(): int { return $this->uid; }
    public function getScore(): int { return $this->score; }

    public function setScore(int $score): void { $this->score = $score; }
}
