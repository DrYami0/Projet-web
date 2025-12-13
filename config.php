<?php

if (ob_get_level() === 0) ob_start('ob_gzhandler');

// Autoload vendor libraries (for PHPMailer, OAuth, etc.)
require_once __DIR__ . '/vendor/autoload.php';

// ============================================
// CONSTANTS
// ============================================
define('BASE_URL', 'http://localhost/projet-web/');
define('ADMIN_EMAIL', 'redacted_admin_email@example.com');

// Google Gemini API Key
define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: 'REDACTED_GEMINI_API_KEY');
// AI Server URL for front-end (ensure to set in .env)
// Default AI service URL. Use the '/ai' prefix so front-end posts target the correct route
// (the Node AI server exposes POST /ai). You can override via .env PERFRAN_AI_API_URL.
define('PERFRAN_AI_API_URL', getenv('PERFRAN_AI_API_URL') ?: 'http://localhost:3001/ai');

// Mail configuration
define('MAILER', 'smtp');
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'redacted_admin_email@example.com');
define('SMTP_PASS', 'ton_app_password_ici');
define('SMTP_SECURE', 'tls');
// Optional PHPMailer debug level (0 = off, 1 = client, 2 = client+server)
define('MAIL_DEBUG', 0);

// OAuth configuration
define('GOOGLE_CLIENT_ID', '508522416620-s4oe55orl56b53eo835uvr800gook6q5.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'REDACTED_GOOGLE_CLIENT_SECRET');

define('FB_CLIENT_ID', '697821080064236');
define('FB_CLIENT_SECRET', 'REDACTED_FACEBOOK_CLIENT_SECRET');

define('GITHUB_CLIENT_ID', 'Ov23liiY5RQigvhmqaSk');
define('GITHUB_CLIENT_SECRET', 'REDACTED_GITHUB_CLIENT_SECRET');

// ============================================
// DATABASE CONNECTION
// ============================================
class config
{
    private static $pdo = null;

    public static function getConnexion()
    {
        if (!isset(self::$pdo)) {
            try {
                self::$pdo = new PDO(
                    'mysql:host=127.0.0.1;port=3306;dbname=2a10_projet;charset=utf8mb4',
                    'root',
                    '',
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (Exception $e) {
                die('Erreur de connexion: ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}

// ============================================
// UTILITY FUNCTIONS
// ============================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function redirect($path = '') {
    header('Location: ' . BASE_URL . ltrim($path, '/'));
    exit;
}

// ============================================
// MAIL FUNCTIONS
// ============================================
function envoyerMail($to, $subject, $body, $fromName = 'Administrateur PerFran') {
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    if (defined('MAILER') && MAILER === 'smtp' && class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            // Optional debug output
            if (defined('MAIL_DEBUG') && MAIL_DEBUG) {
                $mail->SMTPDebug = (int)MAIL_DEBUG;
                $mail->Debugoutput = function($str, $level) { error_log('PHPMailer: ' . $str); };
            }
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = SMTP_SECURE === 'tls' ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = SMTP_PORT;

            $mail->setFrom(SMTP_USER, $fromName);
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->isHTML(true);

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("SMTP échoué : " . $e->getMessage());
        }
    }

    $headers .= "From: " . $fromName . " <" . SMTP_USER . ">\r\n";
    return mail($to, $subject, $body, $headers);
}

function envoyerMailAdmin($to, $subject, $body) {
    return envoyerMail($to, $subject, $body, 'Admin PerFran');
}

function envoyerMailUtilisateur($to, $subject, $body) {
    return envoyerMail($to, $subject, $body, 'Équipe PerFran');
}