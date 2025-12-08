<?php  

class Report
{

    private int $rid;
    private string $description;
    private int $reporterID;
    private string $nomj; 
    private int $gid;
    private int $status;
    private ?int $pid;

    public function __construct(string $description, int $reporterID, string $nomj, int $gid, int $status, ?int $pid)
    {
        $this->description = $description;
        $this->reporterID = $reporterID;
        $this->nomj = $nomj;
        $this->gid = $gid;
        $this->status = $status;
        $this->pid = $pid;
    }
    public function getRid(): int
    {
        return $this->rid;
    }
    public function getNomj(): string
    {
        return $this->nomj;
    }
    public function getDescription(): string
    {
        return $this->description;
    }
    public function getReporterID(): int
    {
        return $this->reporterID;
    }
    public function getGid(): int
    {
        return $this->gid;
    }
    public function getStatus(): int
    {
        return $this->status;
    }
    public function getPid(): ?int
    {
        return $this->pid;
    }
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }
    public function setNomj(string $nomj): void
    {
        $this->nomj = $nomj;
    }
    public function setReporterID(int $reporterID): void
    {
        $this->reporterID = $reporterID;
    }
    public function setGid(int $gid): void
    {
        $this->gid = $gid;
    }
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }
    public function setPid(int $pid): void
    {
        $this->pid = $pid;
    }
}