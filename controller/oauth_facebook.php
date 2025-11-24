<?php
session_start();
require_once '../vendor/autoload.php';
require_once 'config.php';
require_once 'userC.php';

$provider = new League\OAuth2\Client\Provider\Facebook([
    'clientId'          => FB_CLIENT_ID,
    'clientSecret'      => FB_CLIENT_SECRET,
    'redirectUri'       => BASE_URL . 'controller/oauth_facebook.php',
    'graphApiVersion'   => 'v18.0',
]);

if (!isset($_GET['code'])) {
    $authUrl = $provider->getAuthorizationUrl([
        'scope' => ['email']
    ]);
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: ' . $authUrl);
    exit;
}

$token = $provider->getAccessToken('authorization_code', [
    'code' => $_GET['code']
]);

$fbUser = $provider->getResourceOwner($token)->toArray();

$email = $fbUser['email'];
$name  = $fbUser['name'];
$fbId  = $fbUser['id'];

$userRepo = new UserC();
$user = $userRepo->loginWithSocial($email, $name, $fbId, "facebook");

$_SESSION['user'] = $user;
header("Location: " . BASE_URL . "view/user-status.php");
exit;
