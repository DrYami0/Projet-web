<?php
require_once __DIR__ . '/../../config.php';
// Creates user_face_embeddings table using project's config::getConnexion()
try {
    $pdo = config::getConnexion();
    $sql = "CREATE TABLE IF NOT EXISTS user_face_embeddings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_uid INT NOT NULL,
        embedding LONGBLOB NOT NULL,
        method VARCHAR(32) DEFAULT NULL,
        metadata JSON DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX (user_uid),
        CONSTRAINT fk_user_face_embeddings_uid FOREIGN KEY (user_uid) REFERENCES users(uid) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $pdo->exec($sql);
    echo "Table 'user_face_embeddings' created or already exists.\n";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
    exit(1);
}
