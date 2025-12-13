<?php

function getDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=localhost;dbname=perfran;charset=utf8mb4",
                'root',    
                '',         
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    return $pdo;
}

// AI Configuration
define('GEMINI_API_KEY', 'AIzaSyAMFeQNBwW5kyxL7UCc0WuH21aKfGsfIDo');
?>
