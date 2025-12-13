<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/UserC.php';

$qrToken = $_SESSION['qr_token'] ?? $_GET['qr_token'] ?? '';

$provider = new League\OAuth2\Client\Provider\Github([
    'clientId'          => GITHUB_CLIENT_ID,
    'clientSecret'      => GITHUB_CLIENT_SECRET,
    'redirectUri'       => rtrim(BASE_URL, '/') . '/PerFranMVC/Controller/oauth_github.php',
]);

if (!isset($_GET['code'])) {
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    $_SESSION['qr_token'] = $qrToken;
    header('Location: ' . $authUrl);
    exit;
}

$token = $provider->getAccessToken('authorization_code', [
    'code' => $_GET['code']
]);

$userData = $provider->getResourceOwner($token)->toArray();

$email = $userData['email'] ?? ($userData['login']."@github.com");
$name = $userData['login'];
$githubId = $userData['id'];

$userRepo = new UserC();
$user = $userRepo->loginWithSocial($email, $name, $githubId, "github");

if ($user && ($user['status'] ?? 'Inactive') === 'Active') {
    // Mirror the regular login session keys so the rest of the app recognizes the user
    $_SESSION['uid'] = $user['username'] ?? ($user['username'] ?? $user['email'] ?? null);
    $_SESSION['user'] = $user;
    $_SESSION['role'] = $user['role'] ?? 0;
    $_SESSION['user_id'] = $user['uid'] ?? ($user['id'] ?? null);
    $_SESSION['username'] = $user['username'] ?? $user['email'] ?? null;
    $_SESSION['email'] = $user['email'] ?? $email;
    
    // If QR token present, update and redirect to QR page
    $qrToken = $_SESSION['qr_token'] ?? '';
    if ($qrToken) {
        try {
            $pdo = config::getConnexion();
            $updateStmt = $pdo->prepare("\
                UPDATE qr_login_tokens 
                SET user_id = :user_id, 
                    status = 'authenticated'
                WHERE token = :token
            ");
            $updateStmt->execute([
                'user_id' => $_SESSION['user_id'],
                'token' => $qrToken
            ]);
            header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/qr-login-simple.php?token=' . $qrToken);
            exit;
        } catch (Exception $e) {
            // Fallback
        }
    }
    
    header('Location: ' . BASE_URL . 'index.php');
    exit;
} else {
    $_SESSION['pending_username'] = $user['username'] ?? null;
    header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/user-status.php');
    exit;
}
$user = $userRepo->loginWithSocial($email, $name, $githubId, "github");

if ($user && ($user['status'] ?? 'Inactive') === 'Active') {
    $_SESSION['uid'] = $user['username'];
    $_SESSION['user'] = $user;
    $_SESSION['role'] = $user['role'] ?? 0;
    header('Location: ' . BASE_URL . 'index.php');
    exit;
} else {
    $_SESSION['pending_username'] = $user['username'] ?? null;
    header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/user-status.php');
    exit;
}
