<?php

require_once __DIR__ . '/../../config.php';

class UserDashboardController {
    
    private $pdo;

    public function __construct() {
        if (function_exists('obtenirPDO')) {
            $this->pdo = obtenirPDO();
            return;
        }
        if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
            $this->pdo = $GLOBALS['pdo'];
            return;
        }
        $this->pdo = null;
    }

    public function getProfile(int $userId): array {
        if (!$this->pdo) return [];
        $sql = 'SELECT uid, firstName, lastName, email, phone, avatar, username FROM users WHERE uid = :id LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch();
        return $row ?: [];
    }

    public function getTravelers(int $userId): array {
        if (!$this->pdo) return [];
        $sql = 'SELECT id, name, dob, passport_number FROM travelers WHERE user_id = :id ORDER BY name';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getPaymentMethods(int $userId): array {
        if (!$this->pdo) return [];
        $sql = 'SELECT id, type, last4, expiry_month, expiry_year, billing_address FROM user_payments WHERE user_id = :id ORDER BY id DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getWishlist(int $userId): array {
        if (!$this->pdo) return [];
        $sql = 'SELECT id, title, url, created_at FROM wishlist WHERE user_id = :id ORDER BY created_at DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $userId]);
        return $stmt->fetchAll();
    }
}