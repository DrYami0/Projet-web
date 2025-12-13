<?php
require_once __DIR__ . '/../config.php';
try {
    $pdo = config::getConnexion();
    $uid = 1;
    $stmt = $pdo->prepare('SELECT COUNT(*) AS c FROM user_face_embeddings WHERE user_uid = ?');
    $stmt->execute([$uid]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "before delete: " . ($row['c'] ?? 0) . "\n";
    $del = $pdo->prepare('DELETE FROM user_face_embeddings WHERE user_uid = ?');
    $del->execute([$uid]);
    $stmt->execute([$uid]);
    $row2 = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "after delete: " . ($row2['c'] ?? 0) . "\n";
} catch (Exception $e) {
    echo "error: " . $e->getMessage() . "\n";
}
?>