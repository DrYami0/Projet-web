<?php
class admin
{
    private int $aid;
    private string $username;
    private string $password;        
    private int $uid; //user id
    private int $rid; //report id

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

    public function getRid(): int
    {
        return $this->rid;
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

    public function setRid(int $rid): void
    {
        $this->rid = $rid;
    }
}

//ADD GET DELETE UPDATE FUNCTIONS