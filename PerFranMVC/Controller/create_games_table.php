<?php
require_once __DIR__ . '/../../config.php';
$pdo = config::getConnexion();

// Create games table for multiplayer rooms
$sql = "
CREATE TABLE IF NOT EXISTS games (
    gid INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    game VARCHAR(100) DEFAULT 'grammar',
    difficulty ENUM('Easy', 'Medium', 'Hard') DEFAULT 'Easy',
    type VARCHAR(50) DEFAULT '1v1',
    player1id INT NOT NULL,
    player2id INT DEFAULT NULL,
    status ENUM('waiting', 'playing', 'finished') DEFAULT 'waiting',
    rounds_played INT DEFAULT 0,
    game_state TEXT DEFAULT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (player1id) REFERENCES users(uid) ON DELETE CASCADE,
    FOREIGN KEY (player2id) REFERENCES users(uid) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

try {
    $pdo->exec($sql);
    echo "Table 'games' created successfully!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
