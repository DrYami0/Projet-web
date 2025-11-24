<?php
// controller/config.php
define('BASE_URL', 'http://localhost/projet-web/');
define('ADMIN_EMAIL', 'louayfkiri06@gmail.com');

// ====== SMTP CONFIG (OBLIGATOIRE) ======
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'louayfkiri06@gmail.com');           // ← TON EMAIL
define('SMTP_PASS', 'ton_app_password_ici');              // ← App Password Gmail (16 caractères)
define('SMTP_SECURE', 'tls');  // tls ou ssl
// ======================================

// Base de données
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', '2a10_projet');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
    
    // CRUCIAL: Make $pdo available globally AND in $GLOBALS
    $GLOBALS['pdo'] = $pdo;

} catch (PDOException $e) {
    die("Connexion échouée : " . $e->getMessage());
}

// Fonction pour récupérer le PDO partout
function obtenirPDO(): PDO {
    return $GLOBALS['pdo'] ?? throw new Exception("PDO non initialisé !");
}

// Démarrer la session si elle n'existe pas encore
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bonus: Helper pour les redirections propres
function redirect($path = '') {
    header('Location: ' . BASE_URL . ltrim($path, '/'));
    exit;
}