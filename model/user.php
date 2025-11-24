<?php


class User {
    // === Attributs ===
    private $uid;           // ID unique de l'utilisateur 
    private $username;      // Nom d'utilisateur
    private $firstName;     // Prénom
    private $lastName;      // Nom 
    private $passwordHash;  // Mot de passe hash
    private $email;         // Email
    private $phone;         // Téléphone
    private $role;          // Rôle (0 = utilisateur, 1 = admin)
    private $totalScore1;   // Score total jeu 1
    private $totalScore2;   // Score total jeu 2
    private $totalScore3;   // Score total jeu 3
    private $dailyScore1;   // Score quotidien jeu 1
    private $dailyScore2;   // Score quotidien jeu 2
    private $dailyScore3;   // Score quotidien jeu 3
    private $streak;        // Série de victoires consécutives
    private $gamesPlayed1;  // Parties jouées jeu 1
    private $gamesPlayed2;  // Parties jouées jeu 2
    private $gamesPlayed3;  // Parties jouées jeu 3
    private $wins;          // Nombre de victoires totales
    private $losses;        // Nombre de défaites totales
    private $creationDate;  // Date de création du compte

  
    public function __construct(
        $uid = null,
        $username = null,
        $firstName = null,
        $lastName = null,
        $email = null,
        $passwordHash = null,
        $phone = null,
        $role = 0,
        $totalScore1 = 0,
        $totalScore2 = 0,
        $totalScore3 = 0,
        $dailyScore1 = 0,
        $dailyScore2 = 0,
        $dailyScore3 = 0,
        $streak = 0,
        $gamesPlayed1 = 0,
        $gamesPlayed2 = 0,
        $gamesPlayed3 = 0,
        $wins = 0,
        $losses = 0,
        $creationDate = null
    ) {
        $this->uid = $uid;
        $this->username = $username;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->phone = $phone;
        $this->role = $role;
        $this->totalScore1 = $totalScore1;
        $this->totalScore2 = $totalScore2;
        $this->totalScore3 = $totalScore3;
        $this->dailyScore1 = $dailyScore1;
        $this->dailyScore2 = $dailyScore2;
        $this->dailyScore3 = $dailyScore3;
        $this->streak = $streak;
        $this->gamesPlayed1 = $gamesPlayed1;
        $this->gamesPlayed2 = $gamesPlayed2;
        $this->gamesPlayed3 = $gamesPlayed3;
        $this->wins = $wins;
        $this->losses = $losses;
        $this->creationDate = $creationDate;
    }

    
    public function getUid() { return $this->uid; }
    public function setUid($uid) { $this->uid = $uid; }

    public function getUsername() { return $this->username; }
    public function setUsername($username) { $this->username = $username; }

    public function getFirstName() { return $this->firstName; }
    public function setFirstName($firstName) { $this->firstName = $firstName; }

    public function getLastName() { return $this->lastName; }
    public function setLastName($lastName) { $this->lastName = $lastName; }

    public function getEmail() { return $this->email; }
    public function setEmail($email) { $this->email = $email; }

    public function getPasswordHash() { return $this->passwordHash; }
    public function setPasswordHash($passwordHash) { $this->passwordHash = $passwordHash; }

    public function getPhone() { return $this->phone; }
    public function setPhone($phone) { $this->phone = $phone; }

    public function getRole() { return $this->role; }
    public function setRole($role) { $this->role = $role; }

    public function getTotalScore1() { return $this->totalScore1; }
    public function setTotalScore1($score) { $this->totalScore1 = $score; }

    public function getTotalScore2() { return $this->totalScore2; }
    public function setTotalScore2($score) { $this->totalScore2 = $score; }

    public function getTotalScore3() { return $this->totalScore3; }
    public function setTotalScore3($score) { $this->totalScore3 = $score; }

    public function getDailyScore1() { return $this->dailyScore1; }
    public function setDailyScore1($score) { $this->dailyScore1 = $score; }

    public function getDailyScore2() { return $this->dailyScore2; }
    public function setDailyScore2($score) { $this->dailyScore2 = $score; }

    public function getDailyScore3() { return $this->dailyScore3; }
    public function setDailyScore3($score) { $this->dailyScore3 = $score; }

    public function getStreak() { return $this->streak; }
    public function setStreak($streak) { $this->streak = $streak; }

    public function getGamesPlayed1() { return $this->gamesPlayed1; }
    public function setGamesPlayed1($games) { $this->gamesPlayed1 = $games; }

    public function getGamesPlayed2() { return $this->gamesPlayed2; }
    public function setGamesPlayed2($games) { $this->gamesPlayed2 = $games; }

    public function getGamesPlayed3() { return $this->gamesPlayed3; }
    public function setGamesPlayed3($games) { $this->gamesPlayed3 = $games; }

    public function getWins() { return $this->wins; }
    public function setWins($wins) { $this->wins = $wins; }

    public function getLosses() { return $this->losses; }
    public function setLosses($losses) { $this->losses = $losses; }

    public function getCreationDate() { return $this->creationDate; }
    public function setCreationDate($date) { $this->creationDate = $date; }

    // === Méthodes utilitaires ===
    public function getFullName() {
        $parts = array_filter([$this->firstName, $this->lastName]);
        return !empty($parts) ? implode(' ', $parts) : $this->username;
    }

    public function getTotalGamesPlayed() {
        return $this->gamesPlayed1 + $this->gamesPlayed2 + $this->gamesPlayed3;
    }

    public function getTotalScore() {
        return $this->totalScore1 + $this->totalScore2 + $this->totalScore3;
    }

    public function getWinRate() {
        $totalGames = $this->getTotalGamesPlayed();
        return $totalGames > 0 ? round(($this->wins / $totalGames) * 100, 1) : 0;
    }

    public function isAdmin() {
        return $this->role == 1;
    }

    public function toArray() {
        return [
            'uid' => $this->uid,
            'username' => $this->username,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'totalScore1' => $this->totalScore1,
            'totalScore2' => $this->totalScore2,
            'totalScore3' => $this->totalScore3,
            'gamesPlayed1' => $this->gamesPlayed1,
            'gamesPlayed2' => $this->gamesPlayed2,
            'gamesPlayed3' => $this->gamesPlayed3,
            'wins' => $this->wins,
            'losses' => $this->losses,
            'streak' => $this->streak,
            'creationDate' => $this->creationDate
        ];
    }
}
