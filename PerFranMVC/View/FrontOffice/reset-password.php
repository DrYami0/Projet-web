<?php
session_start();
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../Controller/UserC.php';

$token = $_GET['token'] ?? '';
$userC = new UserC();
$validToken = false;
$user = null;

if ($token) {
    $user = $userC->findByResetToken($token);
    $validToken = $user !== null;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialiser le mot de passe - PerfRan</title>
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" media="print" onload="this.media='all'">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', 'Segoe UI', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #017a8a 0%, #093035 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .reset-container {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
        }

        .reset-header {
            background: linear-gradient(135deg, #026875, #074149);
            padding: 40px 30px;
            text-align: center;
        }

        .reset-header i {
            font-size: 48px;
            color: #fff;
            margin-bottom: 16px;
            display: block;
        }

        .reset-header h1 {
            color: #fff;
            font-size: 24px;
            font-weight: 700;
            margin: 0;
        }

        .reset-header p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            margin-top: 8px;
        }

        .reset-body {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 16px;
        }

        .input-wrapper input {
            width: 100%;
            padding: 14px 14px 14px 44px;
            font-size: 14px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            outline: none;
            transition: all 0.3s ease;
        }

        .input-wrapper input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            padding: 4px;
        }

        .password-toggle:hover {
            color: #667eea;
        }

        .error-message {
            background: #fee2e2;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .error-message i {
            font-size: 16px;
        }

        .success-message {
            background: #d1fae5;
            color: #059669;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #666;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: #667eea;
        }

        .back-link i {
            margin-right: 6px;
        }

        .expired-container {
            text-align: center;
            padding: 40px 30px;
        }

        .expired-icon {
            width: 80px;
            height: 80px;
            background: #fee2e2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .expired-icon i {
            font-size: 36px;
            color: #dc2626;
        }

        .expired-container h2 {
            color: #333;
            font-size: 20px;
            margin-bottom: 12px;
        }

        .expired-container p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .password-strength {
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-weak { width: 33%; background: #ef4444; }
        .strength-medium { width: 66%; background: #f59e0b; }
        .strength-strong { width: 100%; background: #10b981; }

        .password-hint {
            font-size: 12px;
            color: #999;
            margin-top: 6px;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <?php if (!$validToken): ?>
            <div class="reset-header">
                <i class="fas fa-exclamation-triangle"></i>
                <h1>Lien Invalide</h1>
            </div>
            <div class="expired-container">
                <div class="expired-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h2>Ce lien a expiré</h2>
                <p>Le lien de réinitialisation du mot de passe n'est plus valide. Les liens expirent après 1 heure pour des raisons de sécurité.</p>
                <a href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/login.php?forgot=1" class="submit-btn">
                    <i class="fas fa-redo"></i>
                    Demander un nouveau lien
                </a>
                <a href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/login.php" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    Retour à la connexion
                </a>
            </div>
        <?php else: ?>
            <div class="reset-header">
                <i class="fas fa-lock"></i>
                <h1>Nouveau mot de passe</h1>
                <p>Bonjour <?= htmlspecialchars($user['username']) ?>, créez votre nouveau mot de passe</p>
            </div>
            <div class="reset-body">
                <?php if (isset($_SESSION['reset_error'])): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($_SESSION['reset_error']) ?>
                    </div>
                    <?php unset($_SESSION['reset_error']); ?>
                <?php endif; ?>

                <form action="<?= BASE_URL ?>PerFranMVC/Controller/password-reset.php" method="POST" id="resetForm">
                    <input type="hidden" name="action" value="reset">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    
                    <div class="form-group">
                        <label for="password">Nouveau mot de passe</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" placeholder="Entrez votre nouveau mot de passe" required minlength="6">
                            <button type="button" class="password-toggle" onclick="togglePassword('password', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength">
                            <div class="password-strength-bar" id="strengthBar"></div>
                        </div>
                        <p class="password-hint">Minimum 6 caractères</p>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmer le mot de passe</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirmez votre mot de passe" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <p class="password-hint" id="matchHint"></p>
                    </div>

                    <button type="submit" class="submit-btn">
                        <i class="fas fa-check"></i>
                        Réinitialiser le mot de passe
                    </button>
                </form>

                <a href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/login.php" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    Retour à la connexion
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('confirm_password');
        const strengthBar = document.getElementById('strengthBar');
        const matchHint = document.getElementById('matchHint');

        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                strengthBar.className = 'password-strength-bar';
                
                if (password.length === 0) {
                    strengthBar.style.width = '0';
                } else if (password.length < 6) {
                    strengthBar.classList.add('strength-weak');
                } else if (password.length < 10 || !/[A-Z]/.test(password) || !/[0-9]/.test(password)) {
                    strengthBar.classList.add('strength-medium');
                } else {
                    strengthBar.classList.add('strength-strong');
                }
                
                checkMatch();
            });
        }

        if (confirmInput) {
            confirmInput.addEventListener('input', checkMatch);
        }

        function checkMatch() {
            if (!confirmInput || !passwordInput) return;
            
            if (confirmInput.value === '') {
                matchHint.textContent = '';
                matchHint.style.color = '#999';
            } else if (confirmInput.value === passwordInput.value) {
                matchHint.textContent = '✓ Les mots de passe correspondent';
                matchHint.style.color = '#10b981';
            } else {
                matchHint.textContent = '✗ Les mots de passe ne correspondent pas';
                matchHint.style.color = '#ef4444';
            }
        }

        const resetForm = document.getElementById('resetForm');
        if (resetForm) {
            resetForm.addEventListener('submit', function(e) {
                if (passwordInput.value !== confirmInput.value) {
                    e.preventDefault();
                    alert('Les mots de passe ne correspondent pas.');
                } else if (passwordInput.value.length < 6) {
                    e.preventDefault();
                    alert('Le mot de passe doit contenir au moins 6 caractères.');
                }
            });
        }
    </script>
</body>
</html>
