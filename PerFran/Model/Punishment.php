<?php  

class Punishment
{

    private int $pid;
    private int $punishedID;
    private int $aid;
    private int $rid;
    private string $reason;    
    private int $duration;  

    public function __construct( string $reason, int $punishedID, int $duration, int $rid,int $aid, int $pid)
    {
        $this->punishedID = $punishedID;
        $this->reason = $reason;
        $this->duration = $duration;
        $this->rid = $rid;
        $this->aid = $aid;
        $this->pid = $pid;
    }
    public function getPunishedID(): int
    {
        return $this->punishedID;
    }
    public function getReason(): string
    {
        return $this->reason;
    }
    public function getDuration(): int
    {
        return $this->duration;
    }
    public function getRid(): int
    {
        return $this->rid;
    }
    public function getRid(): int
    {
        return $this->rid;
    }
    public function getAid(): int
    {
        return $this->aid;
    }
    public function getPid(): int
    {
        return $this->pid;
    }
    public function setPunishedID(int $punishedID): void
    {
        $this->punishedID = $punishedID;
    }
    public function setReason(string $reason): void
    {
        $this->reason = $reason;
    }
    public function setDuration(int $duration): void
    {
        $this->duration = $duration;
    }
    public function setRid(int $rid): void
    {
        $this->rid = $rid;
    }
    public function setAid(int $aid): void
    {
        $this->aid = $aid;
    }
    public function setPid(int $pid): void
    {
        $this->pid = $pid;
    }
}