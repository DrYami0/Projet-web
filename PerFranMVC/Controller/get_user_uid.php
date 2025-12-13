<?php
require_once __DIR__ . '/../../config.php';
try {
    $pdo = config::getConnexion();
    $stmt = $pdo->prepare('SELECT uid,username FROM users WHERE username IN ("Admin","TestLocal")');
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "error:" . $e->getMessage() . "\n";
}
