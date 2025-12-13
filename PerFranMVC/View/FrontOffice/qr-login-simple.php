<?php
/**
 * QR Code Login - Simple Mobile Form
 * User scans QR code, fills email/password, gets authenticated
 */

session_start();
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../Controller/UserC.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = false;

if (!empty($token)) {
    try {
        $pdo = config::getConnexion();
        
        // Verify token exists and is valid
        $stmt = $pdo->prepare("
            SELECT * FROM qr_login_tokens 
            WHERE token = :token 
            AND status = 'pending' 
            AND expires_at > NOW()
        ");
        $stmt->execute(['token' => $token]);
        $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$tokenData) {
            $error = 'QR code expiré ou invalide';
        }
    } catch (Exception $e) {
        $error = 'Erreur serveur';
    }
}

// Handle login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Email et mot de passe requis';
    } elseif (empty($token)) {
        $error = 'QR code invalide ou expiré';
    } else {
        try {
            $pdo = config::getConnexion();
            
            // Verify user
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && !empty($user['password_hash']) && password_verify($password, $user['password_hash'])) {
                // Update token with user info
                $updateStmt = $pdo->prepare(
                    "UPDATE qr_login_tokens 
                    SET user_id = :user_id, 
                        status = 'authenticated'
                    WHERE token = :token"
                );
                $updateStmt->bindValue(':user_id', $user['uid'], PDO::PARAM_INT);
                $updateStmt->bindValue(':token', $token, PDO::PARAM_STR);
                $updateStmt->execute();
                
                // Set session
                $_SESSION['user_id'] = $user['uid'];
                $_SESSION['email'] = $user['email'];
                
                $success = true;
                $error = '';
            } else {
                $error = 'Email ou mot de passe incorrect';
            }
        } catch (Exception $e) {
            $error = 'Erreur lors de la connexion: ' . $e->getMessage();
        }
    }
}

// For security: do NOT accept an already-authenticated session as proof of
// identity for the QR flow. Always require explicit credentials on the
// mobile form. If a social OAuth flow set `$_SESSION['qr_token']`, clear it
// to avoid accidental reuse but do not auto-authenticate.
if (isset($_SESSION['qr_token']) && $_SESSION['qr_token'] === $token) {
    unset($_SESSION['qr_token']);
    // Do not set $success — force the user to submit email/password.
    $error = $error ?: 'Veuillez entrer votre email et mot de passe pour finaliser la connexion.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <title>Connexion QR - PerfRan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" media="print" onload="this.media='all'" />
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" /></noscript>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #017a8a 0%, #093035 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 380px;
            padding: 40px 30px;
            text-align: center;
        }

        .logo {
            font-size: 48px;
            color: #017a8a;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 16px;
            text-align: left;
        }

        label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #333;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #017a8a;
            box-shadow: 0 0 0 3px rgba(1, 122, 138, 0.1);
        }

        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px 15px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }

        .success-message {
            background: #efe;
            color: #3c3;
            padding: 12px 15px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            border-left: 4px solid #3c3;
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #017a8a, #026875);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(1, 122, 138, 0.3);
        }

        button:active {
            transform: translateY(0);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 25px 0 20px;
            color: #ccc;
            font-size: 12px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e0e0e0;
        }

        .divider span {
            padding: 0 10px;
            color: #999;
        }

        .oauth-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .oauth-btn {
            flex: 1;
            padding: 12px;
            border: 1px solid #e0e0e0;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            font-size: 20px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
        }

        .oauth-btn:hover {
            border-color: #017a8a;
            background: #f5f5f5;
            transform: translateY(-2px);
        }

        .token-info {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
            font-size: 12px;
            color: #666;
            margin-bottom: 20px;
            border-left: 4px solid #017a8a;
        }

        .loader {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #017a8a;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .invalid-token {
            background: #fee;
            color: #c33;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .invalid-token i {
            font-size: 40px;
            margin-bottom: 10px;
            display: block;
        }

        @media (max-width: 480px) {
            .container {
                padding: 30px 20px;
            }

            h1 {
                font-size: 20px;
            }

            .subtitle {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($success): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                Authentification réussie!
            </div>
            <div style="text-align: center; padding: 20px;">
                <div class="loader"></div>
                <p style="margin-top: 15px; color: #666; font-size: 14px;">Le QR a été validé — revenez à votre ordinateur pour finaliser la connexion.</p>
                <?php
                    // If the server stored the PC host when the token was generated, show a direct link
                    $pcLink = '';
                    if (!empty($tokenData) && !empty($tokenData['server_host'])) {
                        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                        $pcHost = $tokenData['server_host'];
                        // Avoid redirecting mobile to localhost; only show the link when server_host is a reachable host/IP
                        if ($pcHost !== 'localhost' && $pcHost !== '127.0.0.1') {
                            $pcLink = $scheme . '://' . $pcHost . '/projet-web/index.php';
                        }
                    }
                ?>
                <?php if ($pcLink): ?>
                    <p style="margin-top:10px;"><a href="<?= htmlspecialchars($pcLink) ?>" target="_blank" rel="noopener">Ouvrir la page de l'ordinateur</a></p>
                <?php else: ?>
                    <p style="margin-top:10px; color:#999; font-size:13px;">Si votre ordinateur n'est pas accessible depuis votre téléphone, retournez sur l'ordinateur et rafraîchissez la page.</p>
                <?php endif; ?>
            </div>
        <?php elseif (empty($token)): ?>
            <div class="invalid-token">
                <i class="fas fa-exclamation-circle"></i>
                <h2>QR Code Invalide</h2>
                <p style="margin-top: 10px; color: #999; font-size: 13px;">Ce code QR n'existe pas ou a expiré.</p>
            </div>
        <?php else: ?>
            <div class="logo">
                <i class="fas fa-qrcode"></i>
            </div>
            <h1>Connexion Sécurisée</h1>
            <p class="subtitle">
                Scannez ce QR code depuis votre téléphone et connectez-vous pour valider l'accès.
            </p>

            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="token-info">
                <i class="fas fa-info-circle"></i>
                Formulaire simple et sécurisé pour valider votre identité
            </div>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="votre@email.com"
                        required
                        autocomplete="email"
                    />
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    />
                </div>

                <button type="submit">
                    <i class="fas fa-lock"></i> Se Connecter
                </button>
            </form>

            <div class="divider">
                <span>OU</span>
            </div>

            <p style="font-size: 13px; color: #666; margin-bottom: 15px;">Utiliser un compte social</p>

            <div class="oauth-buttons">
                <a href="<?= BASE_URL ?>PerFranMVC/Controller/oauth_google.php" onclick="setQRToken()" class="oauth-btn" title="Google">
                    <i class="fab fa-google"></i>
                </a>
                <a href="<?= BASE_URL ?>PerFranMVC/Controller/oauth_facebook.php" onclick="setQRToken()" class="oauth-btn" title="Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="<?= BASE_URL ?>PerFranMVC/Controller/oauth_github.php" onclick="setQRToken()" class="oauth-btn" title="GitHub">
                    <i class="fab fa-github"></i>
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function setQRToken() {
            const token = '<?= htmlspecialchars($token) ?>';
            if (token) {
                fetch('<?= BASE_URL ?>PerFranMVC/Controller/set-qr-token.php?token=' + encodeURIComponent(token));
            }
        }
    </script>
</body>
</html>
