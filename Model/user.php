<?php
class user
{
    private string $username;
    private string $password;        
    private int $uid;
    private int $urid; //user report id

    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function getAid(): int
    {
        return $this->aid;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getUid(): int
    {
        return $this->uid;
    }

    public function getUrid(): int
    {
        return $this->urid;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function setUid(int $uid): void
    {
        $this->uid = $uid;
    }

    public function setUrid(int $rid): void
    {
        $this->urid = $rid;
    }
}

