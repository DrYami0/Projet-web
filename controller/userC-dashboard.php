<?php
// controller/userC-dashboard.php
// Controller that only reads dashboard-related data via PDO + prepared statements.
namespace Controller;

require_once __DIR__ . '/../config.php';

class UserDashboardController
{
    private $pdo;

    public function __construct()
    {
        if (function_exists('getPDO')) {
            $this->pdo = getPDO();
            return;
        }
        if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof \PDO) {
            $this->pdo = $GLOBALS['pdo'];
            return;
        }
        if (defined('DB_DSN') && defined('DB_USER')) {
            $this->pdo = new \PDO(DB_DSN, DB_USER, defined('DB_PASS') ? DB_PASS : '', [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);
            return;
        }
        $this->pdo = null;
    }

    public function getProfile(int $userId): array
    {
        if (!$this->pdo) return [];
        $sql = 'SELECT id, full_name, email, phone, nationality, avatar, username FROM users WHERE id = :id LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch();
        return $row ?: [];
    }

    public function getTravelers(int $userId): array
    {
        if (!$this->pdo) return [];
        $sql = 'SELECT id, name, dob, passport_number FROM travelers WHERE user_id = :id ORDER BY name';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getPaymentMethods(int $userId): array
    {
        if (!$this->pdo) return [];
        $sql = 'SELECT id, type, last4, expiry_month, expiry_year, billing_address FROM user_payments WHERE user_id = :id ORDER BY id DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getWishlist(int $userId): array
    {
        if (!$this->pdo) return [];
        $sql = 'SELECT id, title, url, created_at FROM wishlist WHERE user_id = :id ORDER BY created_at DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $userId]);
        return $stmt->fetchAll();
    }
}