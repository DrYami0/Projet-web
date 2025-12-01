<?php
class EventGift {
    private ?int $egid;
    private int $eid;
    private int $gid;

    public function __construct(?int $egid, int $eid, int $gid)
    {
        $this->egid = $egid;
        $this->eid  = $eid;
        $this->gid  = $gid;
    }

    public function getEgid(): ?int { return $this->egid; }
    public function getEid(): int { return $this->eid; }
    public function getGid(): int { return $this->gid; }
}
