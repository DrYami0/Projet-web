<?php
/**
 * QR Code Status Checker
 * Called by the login page to check if QR code has been scanned and authenticated
 */

session_start();
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

$token = $_GET['token'] ?? '';

if (empty($token)) {
    echo json_encode(['success' => false, 'status' => 'invalid']);
    exit;
}

try {
    $pdo = config::getConnexion();
    
    // Check token status
    $stmt = $pdo->prepare("
        SELECT * FROM qr_login_tokens 
        WHERE token = :token 
        AND expires_at > NOW()
    ");
    $stmt->execute([':token' => $token]);
    $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$tokenData) {
        echo json_encode(['success' => false, 'status' => 'expired']);
        exit;
    }
    
    if ($tokenData['status'] === 'authenticated' && $tokenData['user_id']) {
        // Get user data
        $userStmt = $pdo->prepare("SELECT * FROM users WHERE uid = :uid");
        $userStmt->execute([':uid' => $tokenData['user_id']]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Delete used token
            $deleteStmt = $pdo->prepare("DELETE FROM qr_login_tokens WHERE token = :token");
            $deleteStmt->execute([':token' => $token]);
            
            // Check user status
            if (($user['status'] ?? 'Inactive') !== 'Active') {
                $_SESSION['pending_username'] = $user['username'];
                echo json_encode([
                    'success' => true, 
                    'status' => 'user_pending',
                    'username' => $user['username'],
                    'redirect' => BASE_URL . 'PerFranMVC/View/FrontOffice/user-status.php'
                ]);
                exit;
            }
            
            // Set session for active user
            $_SESSION['uid'] = $user['username'];
            $_SESSION['user'] = $user;
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_id'] = $user['uid'];
            $_SESSION['username'] = $user['username'];
            
            echo json_encode([
                'success' => true, 
                'status' => 'authenticated',
                'redirect' => BASE_URL . 'index.php'
            ]);
            exit;
        }
    }
    
    echo json_encode(['success' => true, 'status' => $tokenData['status']]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur serveur']);
}
