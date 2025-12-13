<?php
require_once __DIR__ . '/../../config.php';
header('Content-Type: application/json');
try {
    $pdo = config::getConnexion();
    $sql = "SELECT user_uid AS user_id, COUNT(*) AS embeddings FROM user_face_embeddings GROUP BY user_uid ORDER BY embeddings DESC";
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'counts' => $rows], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit(1);
}
