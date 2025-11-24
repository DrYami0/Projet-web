<?php
// controller/userC.php
require_once __DIR__ . '/config.php';

class UserC
{
    private PDO $pdo;

    
    public function __construct()
    {
        $this->pdo = obtenirPDO();
    }

    
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    // Trouver par username
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }

    // Insérer un utilisateur approuvé dans la DB
    public function createFromApproved(array $data): bool
    {
        $sql = "INSERT INTO users 
                (username, firstName, lastName, email, phone, password_hash, role)
                VALUES 
                (:username, :firstName, :lastName, :email, :phone, :password_hash, :role)";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':username'      => $data['username'],
            ':firstName'     => $data['firstName'] ?? null,
            ':lastName'      => $data['lastName'] ?? null,
            ':email'         => $data['email'],
            ':phone'         => $data['phone'] ?? null,
            ':password_hash' => $data['passwordHash'],
            ':role'          => 0, // Utilisateur standard
        ]);
    }
}