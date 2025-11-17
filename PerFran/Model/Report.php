<?php  

class Report
{

    private int $rid;
    private string $description;
    private int $reporterID;
    private int $reportedID;
    private int $gid;
    private int $status;
    private int $aid;
    private int $pid;

    public function __construct( string $description, int $reporterID, int $reportedID, int $gid, int $status, int $aid, int $pid)
    {
        $this->description = $description;
        $this->reporterID = $reporterID;
        $this->reportedID = $reportedID;
        $this->gid = $gid;
        $this->status = $status;
        $this->aid = $aid;
        $this->pid = $pid;
    }
    public function getRid(): int
    {
        return $this->rid;
    }
    public function getDescription(): string
    {
        return $this->description;
    }
    public function getReporterID(): int
    {
        return $this->reporterID;
    }
    public function getReportedID(): int
    {
        return $this->reportedID;
    }
    public function getGid(): int
    {
        return $this->gid;
    }
    public function getStatus(): int
    {
        return $this->status;
    }
    public function getAid(): int
    {
        return $this->aid;
    }
    public function getPid(): int
    {
        return $this->pid;
    }
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }
    public function setReporterID(int $reporterID): void
    {
        $this->reporterID = $reporterID;
    }
    public function setReportedID(int $reportedID): void
    {
        $this->reportedID = $reportedID;
    }
    public function setGid(int $gid): void
    {
        $this->gid = $gid;
    }
    public function setStatus(int $status): void
    {
        $this->status = $status;
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