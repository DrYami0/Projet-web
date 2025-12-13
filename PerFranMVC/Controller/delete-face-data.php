<?php
// Fixed Face Data Delete - Calls Python for Encryption Handling
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
header('Content-Type: application/json');

function load_dotenv($path = __DIR__ . '/../../.env') {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        putenv("$name=$value");
    }
}
load_dotenv();

if (!isset($_SESSION['uid']) || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['uid'];

// Call Python script to delete (handles decrypt/save)
$python = 'C:\\Users\\Yami\\AppData\\Local\\Programs\\Python\\Python311\\python.exe';
$script = __DIR__ . '/../../face_recognition_login.py';

// Add delete mode to script (we'll update Python below)
$pythonCmd = '"' . $python . '" "' . $script . '" --mode delete --user_id "' . $user_id . '" --username "' . $username . '"';

$descriptorspec = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
$env = $_ENV;
$env['FACE_ENCRYPT_KEY'] = getenv('FACE_ENCRYPT_KEY');  // Ensure passed
// Ensure Python prints UTF-8 (prevents UnicodeEncodeError on Windows consoles)
$env['PYTHONIOENCODING'] = 'utf-8';
$env['PYTHONUTF8'] = '1';

$process = proc_open($pythonCmd, $descriptorspec, $pipes, __DIR__ . '/../../', $env);
if (!is_resource($process)) {
    echo json_encode(['success' => false, 'error' => 'Failed to start delete process']);
    exit;
}

    $stdout = stream_get_contents($pipes[1]); fclose($pipes[1]);
    $stderr = stream_get_contents($pipes[2]); fclose($pipes[2]);
    $ret = proc_close($process);
    $output = trim($stdout . "\n" . $stderr);

// Write debug traces to tmp for easier troubleshooting
 $tmpDir = __DIR__ . '/../../tmp'; if (!is_dir($tmpDir)) @mkdir($tmpDir, 0755, true);
@file_put_contents($tmpDir . '/delete_face_output.log', date('c') . "\nCOMMAND: $pythonCmd\nOUTPUT:\n" . $output . "\nSTDERR:\n" . $stderr . "\n", FILE_APPEND);

// Extract last JSON-like line from output (robust against warnings/notices)
$lines = array_reverse(array_map('trim', explode("\n", $output)));
$jsonLine = null;
foreach ($lines as $line) {
    if ($line && $line[0] === '{' && substr($line, -1) === '}') {
        $jsonLine = $line;
        break;
    }
}
$result = $jsonLine ? json_decode($jsonLine, true) : null;

if ($result && !empty($result['success'])) {
    // Also remove DB-stored embeddings and disable face login flag
    try {
        require_once __DIR__ . '/../../config.php';
        $pdo = config::getConnexion();

        // Resolve numeric uid if possible. Session may contain both username and user_id.
        $uid = null;
        if (!empty($user_id) && is_numeric($user_id)) {
            $uid = (int)$user_id;
        } else {
            // try to lookup uid from username
            $stmt = $pdo->prepare('SELECT uid FROM users WHERE username = ? LIMIT 1');
            $stmt->execute([$username]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && !empty($row['uid'])) $uid = (int)$row['uid'];
        }

        if ($uid) {
            $del = $pdo->prepare('DELETE FROM user_face_embeddings WHERE user_uid = ?');
            $del->execute([$uid]);
        } else {
            // If we don't have uid, attempt best-effort delete via username metadata column
            $del = $pdo->prepare('DELETE FROM user_face_embeddings WHERE JSON_EXTRACT(metadata, "$.username") = ?');
            $del->execute([$username]);
        }

        // Ensure the users table reflects that face login is disabled. Prefer username update to avoid ambiguity.
        $upd = $pdo->prepare('UPDATE users SET face_recognition_enabled = 0 WHERE username = ?');
        $upd->execute([$username]);

        // If username update didn't affect rows and we have uid, try by uid
        if ($upd->rowCount() === 0 && $uid) {
            $upd2 = $pdo->prepare('UPDATE users SET face_recognition_enabled = 0 WHERE uid = ?');
            $upd2->execute([$uid]);
        }
    } catch (Exception $e) {
        // include debug but return failure so client can show error
        @file_put_contents($tmpDir . '/delete_face_output.log', "DB cleanup error: " . $e->getMessage() . "\n", FILE_APPEND);
        echo json_encode(['success' => false, 'error' => 'Échec du nettoyage des données en base', 'debug' => $e->getMessage()]);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Données faciales supprimées']);
} else {
    echo json_encode(['success' => false, 'error' => $result['error'] ?? 'Échec de la suppression', 'debug' => $output]);
}
