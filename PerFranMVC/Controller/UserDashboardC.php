<?php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/UserC.php';
require_once __DIR__ . '/GameC.php';

class UserDashboardController {
    
    private $userC;
    private $gameC;
    
    public function __construct() {
        $this->userC = new UserC();
        $this->gameC = new GameC();
    }
    
    public function getProfile(int $userId): array {
        $pdo = config::getConnexion();
        $sql = 'SELECT uid, firstName, lastName, email, phone, avatar, username, role,
                       totalScore1, totalScore2, totalScore3,
                       dailyScore1, dailyScore2, dailyScore3,
                       gamesPlayed1, gamesPlayed2, gamesPlayed3,
                       wins, losses, streak, creationDate
                FROM users WHERE uid = :id LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch();
        return $row ?: [];
    }

    /**
     * Get complete dashboard data for a user
     */
    public function getDashboardData(int $userId): array {
        $profile = $this->getProfile($userId);
        
        if (empty($profile)) {
            return [];
        }
        
        // Calculate totals
        $totalScore = ($profile['totalScore1'] ?? 0) + ($profile['totalScore2'] ?? 0) + ($profile['totalScore3'] ?? 0);
        $totalGames = ($profile['gamesPlayed1'] ?? 0) + ($profile['gamesPlayed2'] ?? 0) + ($profile['gamesPlayed3'] ?? 0);
        $winRate = (($profile['wins'] ?? 0) + ($profile['losses'] ?? 0)) > 0 
            ? round(($profile['wins'] / ($profile['wins'] + $profile['losses'])) * 100, 1) 
            : 0;
        
        return [
            'userData' => $profile,
            'totalScore' => $totalScore,
            'totalGames' => $totalGames,
            'wins' => $profile['wins'] ?? 0,
            'losses' => $profile['losses'] ?? 0,
            'streak' => $profile['streak'] ?? 0,
            'winRate' => $winRate,
            'gameStats' => [
                'game1' => [
                    'score' => $profile['totalScore1'] ?? 0,
                    'played' => $profile['gamesPlayed1'] ?? 0,
                    'daily' => $profile['dailyScore1'] ?? 0,
                ],
                'game2' => [
                    'score' => $profile['totalScore2'] ?? 0,
                    'played' => $profile['gamesPlayed2'] ?? 0,
                    'daily' => $profile['dailyScore2'] ?? 0,
                ],
                'game3' => [
                    'score' => $profile['totalScore3'] ?? 0,
                    'played' => $profile['gamesPlayed3'] ?? 0,
                    'daily' => $profile['dailyScore3'] ?? 0,
                ],
            ],
            'recentGames' => $this->getUserRecentGames($userId),
        ];
    }

    /**
     * Get user's recent games
     */
    public function getUserRecentGames(int $userId, int $limit = 5): array {
        $pdo = config::getConnexion();
        $sql = "SELECT g.*, 
                       u1.username as player1_name, 
                       u2.username as player2_name
                FROM games g
                LEFT JOIN users u1 ON g.player1id = u1.uid
                LEFT JOIN users u2 ON g.player2id = u2.uid
                WHERE g.player1id = :uid OR g.player2id = :uid
                ORDER BY g.createdAt DESC
                LIMIT :limit";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get leaderboard data
     */
    public function getLeaderboard(int $limit = 10): array {
        return $this->userC->getLeaderboard($limit);
    }

    public function getTravelers(int $userId): array {
        $pdo = config::getConnexion();
        $sql = 'SELECT id, name, dob, passport_number FROM travelers WHERE user_id = :id ORDER BY name';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getPaymentMethods(int $userId): array {
        $pdo = config::getConnexion();
        $sql = 'SELECT id, type, last4, expiry_month, expiry_year, billing_address FROM user_payments WHERE user_id = :id ORDER BY id DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getWishlist(int $userId): array {
        $pdo = config::getConnexion();
        $sql = 'SELECT id, title, url, created_at FROM wishlist WHERE user_id = :id ORDER BY created_at DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $userId]);
        return $stmt->fetchAll();
    }
}
