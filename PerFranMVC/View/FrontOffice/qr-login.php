<?php
/**
 * QR Code Login Handler
 * Scanned by mobile app to authenticate user via QR code
 */

session_start();
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../Controller/UserC.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if (empty($token)) {
    $error = 'Token invalide ou manquant.';
} else {
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
            $error = 'Token invalide, expiré ou déjà utilisé.';
        } else {
            // If user is already logged in on this device, authenticate the token
            if (isset($_SESSION['user_id'])) {
                $userId = $_SESSION['user_id'];
                
                // Update token with user info
                $updateStmt = $pdo->prepare(
                    "UPDATE qr_login_tokens 
                    SET user_id = :user_id, 
                        status = 'authenticated'
                    WHERE token = :token"
                );
                $updateStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
                $updateStmt->bindValue(':token', $token, PDO::PARAM_STR);
                $updateStmt->execute();
                
                $success = 'Authentification réussie ! Vous pouvez retourner à votre ordinateur.';
            } else {
                // User not logged in on mobile - redirect to login with return URL
                $_SESSION['qr_token'] = $token;
                header('Location: login.php?qr=1');
                exit;
            }
        }
    } catch (PDOException $e) {
        $error = 'Erreur de connexion à la base de données.';
        error_log('QR Login Error: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion QR Code - PerfRan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 500px;
            width: 100%;
            text-align: center;
        }

        .icon {
            font-size: 80px;
            margin-bottom: 20px;
        }

        .icon.success {
            color: #10b981;
        }

        .icon.error {
            color: #ef4444;
        }

        .icon.pending {
            color: #f59e0b;
        }

        h1 {
            color: #1f2937;
            font-size: 28px;
            margin-bottom: 15px;
        }

        p {
            color: #6b7280;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 15px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #ef4444;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #017a8a 0%, #093035 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(1, 122, 138, 0.3);
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f4f6;
            border-top-color: #017a8a;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .steps {
            text-align: left;
            background: #f9fafb;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .steps h3 {
            color: #1f2937;
            font-size: 18px;
            margin-bottom: 15px;
        }

        .steps ol {
            margin-left: 20px;
        }

        .steps li {
            color: #4b5563;
            margin-bottom: 10px;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($success): ?>
            <div class="icon success">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Authentification réussie !</h1>
            <div class="alert alert-success">
                <?= htmlspecialchars($success) ?>
            </div>
            <p>Votre code QR a été validé. Vous pouvez maintenant retourner à votre ordinateur et finaliser la connexion.</p>
            <a href="dashboard.php" class="btn">
                <i class="fas fa-home"></i> Aller au tableau de bord
            </a>
        <?php elseif ($error): ?>
            <div class="icon error">
                <i class="fas fa-times-circle"></i>
            </div>
            <h1>Erreur d'authentification</h1>
            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
            <p>Le code QR n'est pas valide ou a expiré. Veuillez générer un nouveau code QR sur votre ordinateur.</p>
            <a href="login.php" class="btn">
                <i class="fas fa-sign-in-alt"></i> Se connecter
            </a>
        <?php else: ?>
            <div class="icon pending">
                <i class="fas fa-qrcode"></i>
            </div>
            <h1>Connexion QR Code</h1>
            <p>Authentification en cours...</p>
            <div class="loading"></div>
            
            <div class="steps">
                <h3>Comment ça fonctionne ?</h3>
                <ol>
                    <li>Scannez le code QR affiché sur votre ordinateur</li>
                    <li>Connectez-vous avec votre compte si nécessaire</li>
                    <li>Votre ordinateur sera automatiquement connecté</li>
                </ol>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
