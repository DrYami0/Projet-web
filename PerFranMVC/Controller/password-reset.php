<?php

session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/UserC.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$userC = new UserC();

switch ($action) {
    case 'request':
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['reset_error'] = "Veuillez entrer une adresse email valide.";
            header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/login.php?forgot=1');
            exit;
        }
        
        $user = $userC->findByEmail($email);
        
        $_SESSION['reset_message'] = "Si cette adresse email est associée à un compte, vous recevrez un lien de réinitialisation.";
        
        if ($user) {
            $token = $userC->createResetToken($email);
            
            if ($token) {
                $resetLink = BASE_URL . 'PerFranMVC/View/FrontOffice/reset-password.php?token=' . $token;
                
                $subject = "Réinitialisation de votre mot de passe - PerfRan";
                $body = "
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset='UTF-8'>
                </head>
                <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <div style='background: linear-gradient(135deg, #026875, #074149); padding: 30px; border-radius: 10px 10px 0 0; text-align: center;'>
                        <h1 style='color: #fff; margin: 0;'>PerfRan</h1>
                    </div>
                    <div style='background: #fff; padding: 30px; border: 1px solid #e0e0e0; border-top: none; border-radius: 0 0 10px 10px;'>
                        <h2 style='color: #026875; margin-top: 0;'>Réinitialisation du mot de passe</h2>
                        <p>Bonjour <strong>" . htmlspecialchars($user['username']) . "</strong>,</p>
                        <p>Vous avez demandé à réinitialiser votre mot de passe. Cliquez sur le bouton ci-dessous pour continuer :</p>
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='" . $resetLink . "' style='background: linear-gradient(135deg, #667eea, #764ba2); color: #fff; padding: 14px 40px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;'>Réinitialiser mon mot de passe</a>
                        </div>
                        <p style='color: #666; font-size: 14px;'>Ce lien expirera dans <strong>1 heure</strong>.</p>
                        <p style='color: #666; font-size: 14px;'>Si vous n'avez pas demandé cette réinitialisation, ignorez simplement cet email.</p>
                        <hr style='border: none; border-top: 1px solid #eee; margin: 25px 0;'>
                        <p style='color: #999; font-size: 12px; text-align: center;'>
                            Cet email a été envoyé automatiquement par PerfRan.<br>
                            Si le bouton ne fonctionne pas, copiez ce lien : <br>
                            <a href='" . $resetLink . "' style='color: #667eea;'>" . $resetLink . "</a>
                        </p>
                    </div>
                </body>
                </html>";
                
                envoyerMailUtilisateur($email, $subject, $body);
            }
        }
        
        header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/login.php?forgot=1');
        exit;
        break;
        
    case 'reset':
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($token)) {
            $_SESSION['reset_error'] = "Token invalide.";
            header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/login.php');
            exit;
        }
        
        if (strlen($password) < 6) {
            $_SESSION['reset_error'] = "Le mot de passe doit contenir au moins 6 caractères.";
            header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/reset-password.php?token=' . $token);
            exit;
        }
        
        if ($password !== $confirmPassword) {
            $_SESSION['reset_error'] = "Les mots de passe ne correspondent pas.";
            header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/reset-password.php?token=' . $token);
            exit;
        }
        
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        if ($userC->resetPassword($token, $passwordHash)) {
            $_SESSION['message'] = "Mot de passe réinitialisé avec succès ! Vous pouvez maintenant vous connecter.";
        } else {
            $_SESSION['error'] = "Le lien de réinitialisation est invalide ou a expiré.";
        }
        
        header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/login.php');
        exit;
        break;
        
    default:
        header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/login.php');
        exit;
}
