<?php
session_start();
require_once '../vendor/autoload.php';
require_once 'config.php';
require_once 'userC.php';

$provider = new Stevenmaguire\OAuth2\Client\Provider\Github([
    'clientId'          => GITHUB_CLIENT_ID,
    'clientSecret'      => GITHUB_CLIENT_SECRET,
    'redirectUri'       => BASE_URL . 'controller/oauth_github.php',
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

$userData = $provider->getResourceOwner($token)->toArray();

$email = $userData['email'] ?? ($userData['login']."@github.com");
$name = $userData['login'];
$githubId = $userData['id'];

$userRepo = new UserC();
$user = $userRepo->loginWithSocial($email, $name, $githubId, "github");

$_SESSION['user'] = $user;
header("Location: " . BASE_URL . "view/user-status.php");
exit;
