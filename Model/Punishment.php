<?php  

class Punishment
{

    private int $pid;
    private string $punishedID;
    private string $reason;    
    private int $duration;
    private string $banType; 

    public function __construct(string $punishedID, string $reason, int $duration, string $banType)
    {
        $this->punishedID = $punishedID;
        $this->reason = $reason;
        $this->duration = $duration;
        $this->banType = $banType;
    }
    public function getPunishedID(): string
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
    public function getBanType(): string
    {
        return $this->banType;
    }
    public function getPid(): int
    {
        return $this->pid;
    }
    public function setPunishedID(string $punishedID): void
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
    public function setBanType(string $banType): void
    {
        $this->banType = $banType;
    }
    public function setPid(int $pid): void
    {
        $this->pid = $pid;
    }
}