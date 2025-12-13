<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

// Google
try {
    $google = new League\OAuth2\Client\Provider\Google([
        'clientId'     => GOOGLE_CLIENT_ID,
        'clientSecret' => GOOGLE_CLIENT_SECRET,
        'redirectUri'  => rtrim(BASE_URL, '/') . '/PerFranMVC/Controller/oauth_google.php',
    ]);
    $gUrl = $google->getAuthorizationUrl();
    echo "Google auth URL:\n" . $gUrl . "\n\n";
} catch (Exception $e) {
    echo "Google error: " . $e->getMessage() . "\n";
}

// GitHub
try {
    $gh = new League\OAuth2\Client\Provider\Github([
        'clientId'     => GITHUB_CLIENT_ID,
        'clientSecret' => GITHUB_CLIENT_SECRET,
        'redirectUri'  => rtrim(BASE_URL, '/') . '/PerFranMVC/Controller/oauth_github.php',
    ]);
    $ghUrl = $gh->getAuthorizationUrl();
    echo "GitHub auth URL:\n" . $ghUrl . "\n\n";
} catch (Exception $e) {
    echo "GitHub error: " . $e->getMessage() . "\n";
}

// Facebook
try {
    $fb = new League\OAuth2\Client\Provider\Facebook([
        'clientId'     => FB_CLIENT_ID,
        'clientSecret' => FB_CLIENT_SECRET,
        'redirectUri'  => rtrim(BASE_URL, '/') . '/PerFranMVC/Controller/oauth_facebook.php',
        'graphApiVersion' => 'v18.0',
    ]);
    $fbUrl = $fb->getAuthorizationUrl();
    echo "Facebook auth URL:\n" . $fbUrl . "\n\n";
} catch (Exception $e) {
    echo "Facebook error: " . $e->getMessage() . "\n";
}
