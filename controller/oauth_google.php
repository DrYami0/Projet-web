<?php
session_start();
require_once '../vendor/autoload.php';
require_once 'config.php';
require_once 'userC.php';

$provider = new League\OAuth2\Client\Provider\Google([
    'clientId'     => GOOGLE_CLIENT_ID,
    'clientSecret' => GOOGLE_CLIENT_SECRET,
    'redirectUri'  => BASE_URL . 'controller/oauth_google.php',
]);

if (!isset($_GET['code'])) {
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: ' . $authUrl);
    exit;
}

$token = $provider->getAccessToken('authorization_code', [
    'code' => $_GET['code']
]);

$googleUser = $provider->getResourceOwner($token);

$email = $googleUser->getEmail();
$name = $googleUser->getName();
$googleId = $googleUser->getId();

$userRepo = new UserC();
$user = $userRepo->loginWithSocial($email, $name, $googleId, "google");

$_SESSION['user'] = $user;
header("Location: " . BASE_URL . "view/user-status.php");
exit;
