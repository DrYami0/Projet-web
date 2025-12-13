<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/UserC.php';

if (empty($_SESSION['uid']) || empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$imageData = $_POST['image'] ?? null;
if (!$imageData) {
    echo json_encode(['success' => false, 'error' => 'No image provided']);
    exit;
}

// Save tmp file
$tmpFile = __DIR__ . '/../../tmp/face_register_input.png';
if (!is_dir(__DIR__ . '/../../tmp')) mkdir(__DIR__ . '/../../tmp', 0755, true);
file_put_contents($tmpFile, base64_decode($imageData));

// Call local service register endpoint with path to file
$ch = curl_init('http://127.0.0.1:5000/register');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'path' => $tmpFile,
    'user_id' => $_SESSION['user_id'],
    'username' => $_SESSION['uid']
]);
$res = curl_exec($ch);
$err = curl_error($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// log curl response for debugging
try {
    if (!is_dir(__DIR__ . '/../../tmp')) mkdir(__DIR__ . '/../../tmp', 0755, true);
    $dbg = [
        'time' => date('c'),
        'curl_err' => $err,
        'http_code' => $code,
        'response_snip' => substr($res ?? '', 0, 1000)
    ];
    file_put_contents(__DIR__ . '/../tmp/register_debug.txt', json_encode($dbg) . "\n", FILE_APPEND | LOCK_EX);
} catch (Exception $e) {
}

if ($res === false) {
    // Fallback: try invoking the Python CLI register directly if the local service is unavailable
    try {
        // prefer explicit python path on Windows to avoid PATH issues
        $py = 'C:\\Users\\Yami\\AppData\\Local\\Programs\\Python\\Python311\\python.exe';
        $script = __DIR__ . '/../../face_recognition_login.py';
        $args = [
            '--mode', 'register',
            '--input', $tmpFile,
            '--user_id', $_SESSION['user_id'],
            '--username', $_SESSION['uid'],
            '--force_register'
        ];
        $esc = array_map(function($a){ return escapeshellarg($a); }, $args);
        // ensure script runs with project root as CWD so relative model paths resolve
        $projectRoot = realpath(__DIR__ . '/../../');
        if ($projectRoot !== false) {
            // set FORCE_REGISTER=1 so the Python register bypasses duplicate-check when called from PHP
            $cmd = 'set FORCE_REGISTER=1 && cd /d ' . escapeshellarg($projectRoot) . ' && ' . $py . ' ' . escapeshellarg($script) . ' ' . implode(' ', $esc) . ' 2>&1';
        } else {
            $cmd = 'set FORCE_REGISTER=1 && ' . $py . ' ' . escapeshellarg($script) . ' ' . implode(' ', $esc) . ' 2>&1';
        }
        $out = shell_exec($cmd);
        // log fallback output
        file_put_contents(__DIR__ . '/../../tmp/register_debug.txt', json_encode(['time' => date('c'), 'fallback_cmd' => $cmd, 'fallback_out' => substr($out ?? '',0,2000)]) . "\n", FILE_APPEND | LOCK_EX);
        $decoded = json_decode($out, true);
        if (json_last_error() === JSON_ERROR_NONE && !empty($decoded['success'])) {
            // emulate a successful service response so the code below can process uniformly
            $res = json_encode(['success' => true, 'message' => 'Face registered (fallback)']);
            $code = 200;
        } else {
            echo json_encode(['success' => false, 'error' => 'Service error', 'debug' => $err, 'fallback_out' => $out]);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Service error', 'debug' => $err, 'exception' => $e->getMessage()]);
        exit;
    }
}
$decoded = json_decode($res, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'error' => 'Invalid service response', 'debug' => $res]);
    exit;
}

if (!empty($decoded['success'])) {
    // Mark face recognition enabled for this user
    try {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare('UPDATE users SET face_recognition_enabled = 1 WHERE uid = ?');
        $stmt->execute([$_SESSION['user_id']]);
    } catch (Exception $e) {
        // continue even if DB flag update fails
    }
    echo json_encode(['success' => true, 'message' => 'Face registered for user']);
} else {
    echo json_encode(['success' => false, 'error' => $decoded['error'] ?? 'Registration failed', 'debug' => $decoded]);
}
