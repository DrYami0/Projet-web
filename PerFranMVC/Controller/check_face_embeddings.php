<?php
require_once __DIR__ . '/../../config.php';
try {
    $pdo = config::getConnexion();
    $row = $pdo->query('SELECT COUNT(*) AS cnt FROM user_face_embeddings')->fetch(PDO::FETCH_ASSOC);
    echo "embeddings_count=" . ($row['cnt'] ?? '0') . "\n";
    $stmt = $pdo->query('SELECT id,user_uid,CHAR_LENGTH(embedding) AS emb_len,method,metadata,created_at FROM user_face_embeddings ORDER BY id DESC LIMIT 5');
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($results as $r) {
        echo json_encode($r) . "\n";
    }
} catch (Exception $e) {
    echo "error:" . $e->getMessage() . "\n";
    exit(1);
}
