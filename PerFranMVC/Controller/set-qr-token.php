<?php
/**
 * Store QR token in session before OAuth redirect
 */
session_start();

$token = $_GET['token'] ?? '';
if ($token) {
    $_SESSION['qr_token'] = $token;
}

echo json_encode(['success' => true]);
