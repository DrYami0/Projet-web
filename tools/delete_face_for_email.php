<?php
// CLI helper: delete face embeddings for a user identified by email
require_once __DIR__ . '/../config.php';
if (php_sapi_name() !== 'cli') {
    echo "This script must be run from CLI.\n";
    exit(1);
}
$email = $argv[1] ?? null;
$logfile = __DIR__ . '/../tmp/delete_face_manual.log';
file_put_contents($logfile, date('c') . " Running delete for email: " . var_export($email, true) . "\n", FILE_APPEND);
if (!$email) {
    echo "Usage: php delete_face_for_email.php user@example.com\n";
    file_put_contents($logfile, "No email provided\n", FILE_APPEND);
    exit(2);
}
try {
    $pdo = config::getConnexion();
    $stmt = $pdo->prepare('SELECT uid, username, face_recognition_enabled FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        echo "User not found for email: $email\n";
        file_put_contents($logfile, "User not found for email: $email\n", FILE_APPEND);
        exit(3);
    }
    $uid = (int)$user['uid'];
    $username = $user['username'];
    file_put_contents($logfile, "Found user uid=$uid username=$username face_enabled=" . ($user['face_recognition_enabled'] ?? 'NULL') . "\n", FILE_APPEND);

    // Delete embeddings
    $del = $pdo->prepare('DELETE FROM user_face_embeddings WHERE user_uid = ?');
    $del->execute([$uid]);
    $deleted = $del->rowCount();
    file_put_contents($logfile, "Deleted rows from user_face_embeddings: $deleted\n", FILE_APPEND);

    // Update flag
    $upd = $pdo->prepare('UPDATE users SET face_recognition_enabled = 0 WHERE uid = ?');
    $upd->execute([$uid]);
    $updCount = $upd->rowCount();
    file_put_contents($logfile, "Updated users.face_recognition_enabled rows: $updCount\n", FILE_APPEND);

    // Also try to clear any metadata JSON referencing username (defensive)
    try {
        $del2 = $pdo->prepare('DELETE FROM user_face_embeddings WHERE JSON_EXTRACT(metadata, "$.username") = ?');
        $del2->execute([$username]);
        file_put_contents($logfile, "Deleted rows by metadata username: " . $del2->rowCount() . "\n", FILE_APPEND);
    } catch (Exception $e) {
        file_put_contents($logfile, "Metadata delete attempt failed: " . $e->getMessage() . "\n", FILE_APPEND);
    }

    echo "Done. Deleted $deleted rows. face_recognition_enabled set to 0.\n";
    file_put_contents($logfile, "Done. Deleted $deleted rows. face_recognition_enabled set to 0.\n", FILE_APPEND);
    exit(0);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    file_put_contents($logfile, "Exception: " . $e->getMessage() . "\n", FILE_APPEND);
    exit(4);
}
