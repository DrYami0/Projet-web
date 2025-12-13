<?php
ob_start('ob_gzhandler');
session_start();
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../Model/User.php';
require_once __DIR__ . '/../../Controller/UserC.php';

// Set cache headers
header('Cache-Control: public, max-age=3600');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
header('Vary: Accept-Encoding');

$showSignupSuccess = isset($_SESSION['signup_success']) && $_SESSION['signup_success'] === true;
if ($showSignupSuccess) {
    unset($_SESSION['signup_success']);
}

$showForgotModal = isset($_GET['forgot']) && $_GET['forgot'] == '1';
$resetMessage = $_SESSION['reset_message'] ?? null;
$resetError = $_SESSION['reset_error'] ?? null;
unset($_SESSION['reset_message'], $_SESSION['reset_error']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
<link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
<style>body{background:linear-gradient(to right,#017a8a,#093035);display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}</style>
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" media="print" onload="this.media='all'" />
<noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" /></noscript>
<!-- SweetAlert2 -->
<link rel="preload" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
<title>Connexion - PerfRan Jeux</title>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

body {
    background: linear-gradient(to right, #017a8a, #093035ff);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    height: 100vh;
}

.container {
    background-color: #fff;
    border-radius: 30px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.35); 
    position: relative;
    overflow: hidden;
    width: 768px;
    max-width: 100%;
    min-height: 580px;
}

.container p {
    font-size: 14px;
    line-height: 20px;
    letter-spacing: 0.3px;
    margin: 10px 0;
}

.container span {
    font-size: 12px;
}

.container a {
    color: #333;
    font-size: 13px;
    text-decoration: none;
    margin: 10px 0;
}

.container button {
    background-color: #667eea;
    color: #fff;
    font-size: 12px;
    padding: 10px 45px;
    border: 1px solid transparent;
    border-radius: 8px;
    font-weight: 600;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    margin-top: 10px;
    cursor: pointer;
}

.container button.hidden {
    background-color: transparent;
    border-color: #fff;
}

.form-container {
    position: absolute;
    top: 0;
    height: 100%;
    width: 50%;
    transition: all 0.6s ease-in-out;
}

.sign-in {
    left: 0;
    z-index: 2;
}

.container.active .sign-in {
    transform: translateX(100%);
}

.sign-up {
    left: 0;
    opacity: 0;
    z-index: 1;
}

.container.active .sign-up {
    transform: translateX(100%);
    opacity: 1;
    z-index: 5;
    animation: move 0.6s;
}

@keyframes move {
    0%, 49.99% { opacity: 0; z-index: 1; }
    50%, 100% { opacity: 1; z-index: 5; }
}

.form-container form {
    background-color: #fff;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: stretch;
    padding: 0 40px;
    height: 100%;
}

.container input {
    background-color: #eee;
    border: none;
    padding: 8px 12px;
    font-size: 12px;
    border-radius: 6px;
    width: 100%;
    outline: none;
    margin-bottom: 2px;
}

.input-group {
    width: 100%;
    margin-bottom: 6px;
}

.form-group label {
    display: block;
    margin-bottom: 4px;
    font-size: 13px;
    font-weight: 500;
}

.error-message {
    color: #e74c3c;
    font-size: 10px;
    margin: 0 0 4px 0;
    line-height: 1.1;
    text-align: left;
    width: 100%;
    min-height: 11px;
}

.success-message {
    color: #27ae60;
    font-size: 12px;
    margin: 8px 0;
    text-align: center;
    font-weight: 600;
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    padding: 12px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.success-message i {
    font-size: 16px;
}

.sign-up h1 {
    font-size: 24px;
    margin-bottom: 8px;
    text-align: center;
}

.sign-in h1 {
    font-size: 26px;
    margin-bottom: 12px;
    text-align: center;
}

.sign-up span {
    font-size: 11px;
    margin-bottom: 8px;
    text-align: center;
    display: block;
}

.sign-in span {
    font-size: 13px;
    margin-bottom: 12px;
    text-align: center;
}

.input-row {
    display: flex;
    gap: 8px;
    width: 100%;
    margin-bottom: 2px;
}

.input-row input {
    flex: 1;
}

.sign-in input {
    margin-bottom: 12px;
}

.social-icons {
    margin: 10px 0;
    display: flex;
    justify-content: center;
}

.social-icons a {
    border: 1px solid #ccc;
    border-radius: 20%;
    display: inline-flex;
    justify-content: center;
    align-items: center;
    margin: 0 3px;
    width: 35px;
    height: 35px;
}

.social-icons a i {
    font-size: 14px;
}

.toggle-container {
    position: absolute;
    top: 0;
    left: 50%;
    width: 50%;
    height: 100%;
    overflow: hidden;
    transition: all 0.6s ease-in-out;
    border-radius: 150px 0 0 100px;
    z-index: 100;
    pointer-events: auto;
}

.toggle-container.modal-open {
    pointer-events: none;
    z-index: -1;
    opacity: 0;
}

.container.active .toggle-container {
    transform: translateX(-100%);
    border-radius: 0 150px 100px 0;
}

.toggle {
    background: linear-gradient(to right, #026875, #074149);
    color: #fff;
    position: relative;
    left: -100%;
    height: 100%;
    width: 200%;
    transform: translateX(0);
    transition: all 0.6s ease-in-out;
}

.container.active .toggle {
    transform: translateX(50%);
}

.toggle-panel {
    position: absolute;
    width: 50%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    padding: 0 30px;
    text-align: center;
    top: 0;
    transform: translateX(0);
    transition: all 0.6s ease-in-out;
}

.toggle-left {
    transform: translateX(-200%);
}

.container.active .toggle-left {
    transform: translateX(0);
}

.toggle-right {
    right: 0;
    transform: translateX(0);
}

.container.active .toggle-right {
    transform: translateX(200%);
}

.sign-up .input-group input {
    padding: 7px 10px;
    font-size: 11px;
}

.sign-up button {
    margin-top: 6px;
    padding: 9px 40px;
}

@media (max-width: 768px) {
    .form-container form {
        padding: 0 20px;
    }
    .input-row {
        flex-direction: column;
        gap: 0;
    }
    .container {
        width: 95%;
        min-height: 600px;
    }
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.modal-overlay.active {
    opacity: 1;
    visibility: visible;
}

.modal {
    background: #fff;
    border-radius: 20px;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
    transform: scale(0.9) translateY(20px);
    transition: all 0.3s ease;
    overflow: hidden;
}

.modal-overlay.active .modal {
    transform: scale(1) translateY(0);
}

.modal-header {
    background: linear-gradient(135deg, #026875, #074149);
    padding: 30px;
    text-align: center;
    position: relative;
}

.modal-header i.header-icon {
    font-size: 40px;
    color: #fff;
    margin-bottom: 12px;
    display: block;
}

.modal-header h2 {
    color: #fff;
    font-size: 22px;
    margin: 0;
    font-weight: 600;
}

.modal-header p {
    color: rgba(255, 255, 255, 0.8);
    font-size: 13px;
    margin-top: 8px;
}

.modal-close {
    position: absolute;
    top: 15px;
    right: 15px;
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: #fff;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 16px;
    transition: all 0.3s;
}

.modal-close:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: rotate(90deg);
}

.modal-body {
    padding: 30px;
}

.modal-body .input-group {
    margin-bottom: 20px;
}

.modal-body .input-group label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

.modal-body .input-group input {
    width: 100%;
    padding: 14px 16px;
    font-size: 14px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    outline: none;
    transition: all 0.3s;
    margin: 0;
}

.modal-body .input-group input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.modal-body button[type="submit"] {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 10px;
}

.modal-body button[type="submit"]:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
}

.modal-message {
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 13px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.modal-message.success {
    background: #d1fae5;
    color: #059669;
}

.modal-message.error {
    background: #fee2e2;
    color: #dc2626;
}

.modal-footer {
    text-align: center;
    padding-top: 15px;
    border-top: 1px solid #eee;
}

.modal-footer a {
    color: #667eea;
    text-decoration: none;
    font-size: 13px;
}

.modal-footer a:hover {
    text-decoration: underline;
}
/* Animated wave button styles */
.wave-btn {
    position: relative;
    width: 38px;
    height: 38px;
    border-radius: 8px;
    background: linear-gradient(270deg, #667eea, #764ba2, #667eea);
    background-size: 400% 400%;
    animation: waveGradient 3s ease infinite;
    border: 1px solid transparent;
    box-shadow: 0 4px 16px rgba(102,126,234,0.15);
    cursor: pointer;
    outline: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    overflow: hidden;
    transition: box-shadow 0.2s, border 0.3s;
}
.wave-btn::before {
    content: '';
    position: absolute;
    left: 1px;
    top: 1px;
    right: 1px;
    bottom: 1px;
    border-radius: 6px;
    background: #fff;
    z-index: 1;
    transition: background 0.3s;
}
.wave-btn:active::before,
.login-alt-btns .wave-btn:hover::before {
    background: linear-gradient(270deg, #667eea, #764ba2, #667eea);
}
.wave-btn > span, .wave-btn > i {
    position: relative;
    z-index: 2;
    color: #667eea;
    transition: color 0.3s;
}
.wave-btn:active > span img,
.login-alt-btns .wave-btn:hover > span img,
.wave-btn:active > i,
.login-alt-btns .wave-btn:hover > i {
    filter: brightness(0) invert(1);
}
}
.wave-btn:active {
    transform: scale(0.95);
    box-shadow: 0 2px 8px rgba(102,126,234,0.25);
    background: linear-gradient(270deg, #667eea, #764ba2, #667eea);
    color: #fff;
}
.wave-btn:focus {
    box-shadow: 0 0 0 3px rgba(102,126,234,0.25);
}
@keyframes waveGradient {
    0% {background-position:0% 50%}
    50% {background-position:100% 50%}
    100% {background-position:0% 50%}
}
.login-alt-btns .wave-btn:hover {
    background: linear-gradient(270deg, #667eea, #764ba2, #667eea);
    color: #fff;
    filter: brightness(1.08) drop-shadow(0 0 8px #764ba2);
}
</style>
</head>
<body>



<div class="container" id="container">
    <!-- Sign Up Form -->
    <div class="form-container sign-up">
        <form id="signupForm" action="<?= BASE_URL ?>PerFranMVC/Controller/auth.php" method="POST" novalidate>
            <input type="hidden" name="action" value="signup">
            <h1>Créer un Compte</h1>
            <div class="social-icons">
                <a href="<?= BASE_URL ?>PerFranMVC/Controller/oauth_google.php" class="icon" title="S'inscrire avec Google"><i class="fa-brands fa-google-plus-g"></i></a>
                <a href="<?= BASE_URL ?>PerFranMVC/Controller/oauth_facebook.php" class="icon" title="S'inscrire avec Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="<?= BASE_URL ?>PerFranMVC/Controller/oauth_github.php" class="icon" title="S'inscrire avec GitHub"><i class="fa-brands fa-github"></i></a>
            </div>
            <span>Ou remplissez le formulaire d'inscription</span>
            
            <div class="input-group">
                <input type="text" name="username" id="username" placeholder="Nom d'utilisateur *" maxlength="50" />
                <p class="error-message" id="usernameError"></p>
            </div>
            
            <div class="input-row">
                <input type="text" name="firstName" id="firstName" placeholder="Prénom (optionnel)" />
                <input type="text" name="lastName" id="lastName" placeholder="Nom (optionnel)" />
            </div>
            <p class="error-message" id="nameError"></p>
            
            <div class="input-group">
                <input type="email" name="email" id="email" placeholder="Email *" />
                <p class="error-message" id="emailError"></p>
            </div>
            
            <div class="input-group">
                <input type="tel" name="phone" id="phone" placeholder="Téléphone (optionnel, 8-15 chiffres)" maxlength="15" />
                <p class="error-message" id="phoneError"></p>
            </div>
            
            <div class="input-group">
                <input type="password" name="password" id="password" placeholder="Mot de passe (min 6 caractères) *" />
                <p class="error-message" id="passwordError"></p>
            </div>
            
            <?php if(isset($_SESSION['signup_error'])): ?>
                <p class="error-message" style="text-align: center; margin-bottom: 10px;">
                    <?= htmlspecialchars($_SESSION['signup_error']) ?>
                </p>
                <?php unset($_SESSION['signup_error']); 
            endif; ?>
            
            <button type="submit">S'inscrire</button>
        </form>
    </div>

    <!-- Sign In Form -->
    <div class="form-container sign-in">
        <form id="loginForm" action="<?= BASE_URL ?>PerFranMVC/Controller/auth.php" method="POST" novalidate>
            <input type="hidden" name="action" value="login">
            <h1>Se Connecter</h1>
            <div class="social-icons">
                <a href="<?= BASE_URL ?>PerFranMVC/Controller/oauth_google.php" class="icon" title="Se connecter avec Google"><i class="fa-brands fa-google-plus-g"></i></a>
                <a href="<?= BASE_URL ?>PerFranMVC/Controller/oauth_facebook.php" class="icon" title="Se connecter avec Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="<?= BASE_URL ?>PerFranMVC/Controller/oauth_github.php" class="icon" title="Se connecter avec GitHub"><i class="fa-brands fa-github"></i></a>
            </div>
            
            <!-- QR Code Modal -->
            <div id="qrModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 999999 !important; align-items: center; justify-content: center;">
                <div style="background: white; padding: 30px; border-radius: 20px; text-align: center; max-width: 400px; position: relative;">
                    <button id="qrCloseBtn" style="position: absolute; top: 10px; right: 10px; background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">&times;</button>
                    <h2 style="color: #333; margin-bottom: 20px;">Connexion QR Code</h2>
                    <div id="qrCodeContainer" style="margin: 20px 0;">
                        <div style="padding: 40px;">
                            <i class="fas fa-spinner fa-spin" style="font-size: 48px; color: #667eea;"></i>
                            <p style="margin-top: 15px; color: #666;">Génération du QR code...</p>
                        </div>
                    </div>
                    <p style="color: #666; font-size: 13px; margin-top: 15px;">Scannez ce QR code avec votre téléphone pour vous connecter</p>
                    <p id="qrExpiry" style="color: #ff6b6b; font-size: 12px; margin-top: 10px; font-weight: 600;"><i class="fas fa-clock"></i> Expire dans 5:00</p>
                </div>
            </div>
            
            <span>Utilisez votre email et mot de passe</span>
            
            <?php if($showSignupSuccess): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <span>Inscription réussie ! Votre demande a été envoyée à l'administrateur.</span>
                </div>
            <?php endif; ?>
            
            <div class="input-group">
                <input type="email" name="email" id="loginEmail" placeholder="Email" />
                <p class="error-message" id="loginEmailError"></p>
            </div>
            
            <div class="input-group">
                <input type="password" name="password" id="loginPassword" placeholder="Mot de passe" />
                <p class="error-message" id="loginPasswordError"></p>

                <!-- AI password helper placed under the password label -->
                <div id="ai-password-helper" style="margin-top:8px;display:flex;flex-direction:column;gap:6px;align-items:flex-start;">
                    <div style="display:flex;gap:8px;align-items:center;">
                        <button id="suggestWithAI" type="button" class="btn small" style="background:#0b76c9;color:#fff;border:none;padding:6px 10px;border-radius:6px;cursor:pointer;">Avec l'IA</button>
                        <small id="aiHelperNote" style="color:#666;font-size:13px;">Génère un mot de passe fort et explique pourquoi il est sûr.</small>
                    </div>
                    <div id="aiSuggestion" style="display:none;padding:8px;border-radius:8px;background:#f7fbff;border:1px solid #e2f0ff;width:100%;box-sizing:border-box;">
                        <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
                            <div id="aiSuggestionText" style="word-break:break-all;color:#062a44;font-weight:600"></div>
                            <div style="display:flex;gap:8px;align-items:center;">
                                <button id="copyAiPassword" type="button" class="btn tiny" style="background:#17a673;color:#fff;border:none;padding:6px 8px;border-radius:6px;cursor:pointer;">Copier</button>
                                <button id="useAiPassword" type="button" class="btn tiny" style="background:#017a8a;color:#fff;border:none;padding:6px 8px;border-radius:6px;cursor:pointer;">Utiliser</button>
                            </div>
                        </div>
                        <div id="aiExplanation" style="margin-top:6px;color:#375a7f;font-size:13px;"></div>
                    </div>

                    <!-- Password strength meter -->
                    <div id="password-strength" style="width:100%;">
                        <div id="strength-bar" style="height:8px;border-radius:6px;background:#eee;overflow:hidden;">
                            <div id="strength-fill" style="height:100%;width:0%;background:linear-gradient(90deg,#ff4d4f,#ffb84d);transition:width .35s ease;"></div>
                        </div>
                        <div id="strength-text" style="margin-top:6px;font-size:13px;color:#444;">Entrez un mot de passe pour voir la force</div>
                    </div>
                </div>
            </div>
            
            <?php if(isset($_SESSION['error'])): ?>
                <p class="error-message" style="text-align: center;">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                </p>
                <?php unset($_SESSION['error']); 
            endif; ?>
            
            <?php if(isset($_SESSION['message'])): ?>
                <p class="success-message">
                    <?= htmlspecialchars($_SESSION['message']) ?>
                </p>
                <?php unset($_SESSION['message']); 
            endif; ?>
            
            <a href="#" id="forgotPasswordLink">Mot de passe oublié ?</a>
            <button type="submit">Se Connecter</button>
            <div class="login-alt-btns" style="display:flex;gap:16px;margin-top:16px;justify-content:center;">
                <button type="button" id="faceRecLoginBtn" class="wave-btn" title="Connexion par reconnaissance faciale">
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:100%;height:100%;">
                        <img src="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/assets/images/face-recognition.png" alt="Face Recognition" style="width:30px;height:30px;background:transparent;border:none;display:block;margin:0 auto;position:relative;top:6px;" />
                    </span>
                </button>
                <button type="button" id="qrCodeBtn" class="wave-btn" title="Connexion par QR Code">
                    <i class="fas fa-qrcode" style="font-size:30px;"></i>
                </button>
            </div>
        </form>
    </div>

    <!-- Toggle Panel -->
    <div class="toggle-container">
        <div class="toggle">
            <div class="toggle-panel toggle-left">
                <h1>Bienvenue !</h1>
                <p>Inscrivez-vous pour commencer votre aventure de jeu</p>
                <button class="hidden" id="login">Se Connecter</button>
            </div>
            <div class="toggle-panel toggle-right">
                <h1>Bienvenue !</h1>
                <p>Connectez-vous pour accéder à tous vos jeux et statistiques</p>
                <button class="hidden" id="register">S'inscrire</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Interactive login alt buttons
    const faceRecLoginBtn = document.getElementById('faceRecLoginBtn');
    const qrLoginBtn = document.getElementById('qrLoginBtn') || document.getElementById('qrCodeBtn');
    if (faceRecLoginBtn) {
        faceRecLoginBtn.addEventListener('click', async () => {
            // Open a lightweight camera modal, capture a single frame, then POST to the server endpoint
            const modal = document.getElementById('faceModal');
            const video = document.getElementById('faceVideo');
            const captureBtn = document.getElementById('faceCaptureBtn');
            const cancelBtn = document.getElementById('faceCancelBtn');
            const statusEl = document.getElementById('faceStatus');
            // Open modal and auto-start the camera so the user can click Capturer immediately
            modal.style.display = 'flex';
            statusEl.textContent = 'Activation de la caméra...';

            let stream = null;
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: { width: { ideal: 1280 }, height: { ideal: 720 }, facingMode: 'user' }, audio: false });
                video.srcObject = stream;
                await video.play();
                statusEl.textContent = 'Positionnez votre visage et cliquez sur Capturer';
                if (captureBtn) captureBtn.disabled = false;
            } catch (err) {
                statusEl.textContent = 'Impossible d\'accéder à la caméra. Autorisez la caméra ou réessayez.';
                console.error('Camera start failed:', err);
                // keep capture disabled when no stream
                if (captureBtn) captureBtn.disabled = true;
            }

            // capture handler (only enabled after start)
            captureBtn.onclick = async () => {
                    statusEl.textContent = 'Analyse en cours...';
                    // Capture multiple quick frames (5) so we can pick the sharpest/brightest
                    const captures = [];
                    const canvas = document.createElement('canvas');
                    canvas.width = video.videoWidth || 1280;
                    canvas.height = video.videoHeight || 720;
                    const ctx = canvas.getContext('2d');

                    const captureCount = 5;
                    for (let i = 0; i < captureCount; i++) {
                        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                        // copy ImageData (so subsequent draws don't mutate it)
                        const id = ctx.getImageData(0, 0, canvas.width, canvas.height);
                        captures.push({ data: new Uint8ClampedArray(id.data), width: id.width, height: id.height });
                        await new Promise(r => setTimeout(r, 150));
                    }

                    // simple liveness/motion check: compare frame0 vs frameN (sampled pixels)
                    try {
                        let diff = 0;
                        if (captures.length >= 3) {
                            const a = captures[0].data;
                            const b = captures[captures.length - 1].data;
                            // sample every 8th pixel (RGBA -> step 32 bytes) to be fast
                            for (let p = 0; p < a.length; p += 32) {
                                diff += Math.abs(a[p] - b[p]);
                                if (diff > 5000) break; // early exit
                            }
                        }
                        if (diff < 1000) {
                            statusEl.textContent = 'Mouvement insuffisant. Bougez légèrement la tête et réessayez.';
                            return;
                        }
                    } catch (e) {
                        console.warn('Liveness check failed to run:', e);
                    }

                    // Score frames by sharpness (variance of luminance) and brightness
                    function scoreFrame(frame) {
                        const d = frame.data;
                        let sum = 0, sumSq = 0, count = 0;
                        // sample every Nth pixel to reduce CPU
                        const step = 32; // RGBA per pixel => step 32 samples ~ every 8 pixels
                        for (let i = 0; i < d.length; i += step) {
                            const r = d[i], g = d[i + 1], b = d[i + 2];
                            const lum = 0.299 * r + 0.587 * g + 0.114 * b;
                            sum += lum;
                            sumSq += lum * lum;
                            count++;
                        }
                        const mean = sum / count;
                        const variance = (sumSq / count) - (mean * mean);
                        return { variance, mean };
                    }

                    const scored = captures.map(c => ({ frame: c, score: scoreFrame(c) }));
                    scored.sort((a, b) => {
                        // prefer higher variance (sharpness), then higher mean (brightness)
                        if (b.score.variance !== a.score.variance) return b.score.variance - a.score.variance;
                        return b.score.mean - a.score.mean;
                    });

                    // If best frame is very dark or very low variance, warn user
                    const best = scored[0];
                    if (!best || best.score.mean < 30 || best.score.variance < 50) {
                        statusEl.textContent = 'Image trop sombre ou floue. Essayez d\'augmenter l\'éclairage ou rapprochez-vous.';
                        return;
                    }

                    // Prepare FormData with up to 3 best frames (best, 2nd, 3rd) to improve server success
                    try {
                        const expected = (document.getElementById('loginEmail') && document.getElementById('loginEmail').value) ? document.getElementById('loginEmail').value.trim() : '';
                        const form = new FormData();
                        // include expected identifier when available, but do not require it
                        if (expected) form.append('expected', expected);

                        // draw chosen frames into a new canvas and use toBlob for proper binary upload
                        const outCanvas = document.createElement('canvas');
                        outCanvas.width = canvas.width;
                        outCanvas.height = canvas.height;
                        const outCtx = outCanvas.getContext('2d');

                        const toAppend = Math.min(3, scored.length);
                        for (let k = 0; k < toAppend; k++) {
                            const s = scored[k];
                            const id = new ImageData(new Uint8ClampedArray(s.frame.data), s.frame.width, s.frame.height);
                            outCtx.putImageData(id, 0, 0);
                            // eslint-disable-next-line no-await-in-loop
                            const blob = await new Promise(resolve => outCanvas.toBlob(resolve, 'image/png'));
                            form.append('image' + k, blob, 'image' + k + '.png');
                        }

                        const res = await fetch('<?= BASE_URL ?>PerFranMVC/Controller/face_recognition_login.php', {
                            method: 'POST',
                            credentials: 'same-origin',
                            body: form
                        });
                        const json = await res.json();
                        console.log('Face service response:', json);
                        if (json.success) {
                            window.location.href = '<?= BASE_URL ?>index.php';
                        } else {
                            statusEl.textContent = json.error || 'Visage non reconnu.';
                            if (json.debug) console.warn('Face debug:', json.debug);
                        }
                    } catch (err) {
                        statusEl.textContent = 'Erreur réseau lors de la reconnaissance.';
                        console.error(err);
                    }
                };

                cancelBtn.onclick = () => {
                    modal.style.display = 'none';
                    // stop stream
                    const tracks = stream ? stream.getTracks() : (video.srcObject ? video.srcObject.getTracks() : []);
                    tracks.forEach(t => t.stop());
                    video.srcObject = null;
                    // disable capture and clean UI state
                    if (captureBtn) captureBtn.disabled = true;
                };
        });
    }
    if (qrLoginBtn) {
        qrLoginBtn.addEventListener('click', (e) => {
            e.preventDefault();
            // Prefer opening the in-page QR modal if available
            if (qrCodeBtn) {
                qrCodeBtn.click();
                return;
            }
            // Fallback: older standalone QR page
            window.location.href = '<?= BASE_URL ?>PerFranMVC/View/FrontOffice/qr-login-simple.php';
        });
    }
    // Toggle between Sign Up and Sign In
    const container = document.getElementById('container');
    const registerBtn = document.getElementById('register');
    const loginBtn = document.getElementById('login');

    registerBtn.addEventListener('click', () => {
        container.classList.add('active');
    });

    loginBtn.addEventListener('click', () => {
        container.classList.remove('active');
    });

    // ==================== QR CODE LOGIN ====================
    let qrCheckInterval = null;
    let qrCountdownInterval = null;
    let currentToken = null;

    const qrCodeBtn = document.getElementById('qrCodeBtn');
    const qrModal = document.getElementById('qrModal');
    
    if (qrCodeBtn) {
        qrCodeBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            qrModal.style.display = 'flex';
            document.querySelector('.toggle-container').classList.add('modal-open');
            
            try {
                const response = await fetch('<?= BASE_URL ?>PerFranMVC/Controller/qr-generate.php');
                const data = await response.json();
                
                if (data.success) {
                    currentToken = data.token;
                    
                    // Generate QR code using QRCode.js or display URL
                    const qrContainer = document.getElementById('qrCodeContainer');
                    qrContainer.innerHTML = `
                        <div style="background: white; padding: 20px; border-radius: 10px; display: inline-block;">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(data.qrUrl)}" 
                                 alt="QR Code" 
                                 style="width: 250px; height: 250px; display: block;">
                        </div>
                    `;
                    
                    // Start checking for authentication
                    startQRCheck();

                    // (Removed AI password suggestion UI for QR modal)
                    
                    // Set expiry countdown
                    let secondsLeft = 300; // 5 minutes
                    const expiryEl = document.getElementById('qrExpiry');
                    qrCountdownInterval = setInterval(() => {
                        secondsLeft--;
                        const minutes = Math.floor(secondsLeft / 60);
                        const seconds = secondsLeft % 60;
                        const timeString = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                        
                        // Change color based on time left
                        if (secondsLeft <= 60) {
                            expiryEl.style.color = '#ff4757';
                            expiryEl.innerHTML = `<i class="fas fa-exclamation-triangle"></i> Expire dans ${timeString}`;
                        } else if (secondsLeft <= 120) {
                            expiryEl.style.color = '#ffa502';
                            expiryEl.innerHTML = `<i class="fas fa-clock"></i> Expire dans ${timeString}`;
                        } else {
                            expiryEl.innerHTML = `<i class="fas fa-clock"></i> Expire dans ${timeString}`;
                        }
                        
                        if (secondsLeft <= 0) {
                            clearInterval(qrCountdownInterval);
                            stopQRCheck();
                            qrModal.style.display = 'none';
                            alert('Le QR code a expiré. Veuillez en générer un nouveau.');
                        }
                    }, 1000);
                } else {
                    alert('Erreur lors de la génération du QR code');
                    qrModal.style.display = 'none';
                }
            } catch (error) {
                console.error('QR Error:', error);
                alert('Erreur de connexion au serveur');
                qrModal.style.display = 'none';
            }
        });
    }
    
    function startQRCheck() {
        qrCheckInterval = setInterval(async () => {
            if (!currentToken) return;
            
            try {
                const response = await fetch(`<?= BASE_URL ?>PerFranMVC/Controller/qr-check.php?token=${currentToken}`);
                const data = await response.json();

                // Server returns { success: true, status: 'authenticated'|'pending'|'user_pending', redirect: '...' }
                if (data && data.success && (data.status === 'authenticated' || data.status === 'user_pending')) {
                    stopQRCheck();
                    if (qrCountdownInterval) clearInterval(qrCountdownInterval);
                    qrModal.style.display = 'none';

                    // If server provided a redirect URL, use it; otherwise fallback to homepage
                    const redirectUrl = data.redirect || '<?= BASE_URL ?>index.php';

                    // Show a SweetAlert2 popup if available, otherwise fallback to inline message
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Vérification réussie',
                            text: 'Connexion en cours — ouverture automatique...',
                            icon: 'success',
                            timer: 900,
                            showConfirmButton: false,
                            timerProgressBar: true,
                            didClose: () => { window.location.href = redirectUrl; }
                        });
                    } else {
                        const qrContainer = document.getElementById('qrCodeContainer');
                        qrContainer.innerHTML = `
                            <div style="background:#fff;padding:24px;border-radius:12px;text-align:center;min-width:260px;">
                                <div style="font-size:36px;color:#2ecc71;margin-bottom:8px;">✔</div>
                                <h3 style="margin:0 0 8px;color:#2c3e50;">Vérification réussie</h3>
                                <p style="margin:0;color:#555;">Connexion en cours — ouverture automatique...</p>
                            </div>
                        `;
                        setTimeout(() => { window.location.href = redirectUrl; }, 900);
                    }
                }
            } catch (error) {
                console.error('QR Check Error:', error);
            }
        }, 2000); // Check every 2 seconds
    }
    
    function stopQRCheck() {
        if (qrCheckInterval) {
            clearInterval(qrCheckInterval);
            qrCheckInterval = null;
        }
        if (qrCountdownInterval) {
            clearInterval(qrCountdownInterval);
            qrCountdownInterval = null;
        }
        currentToken = null;
    }
    
    // Close modal handlers
    const qrCloseBtn = document.getElementById('qrCloseBtn');
    qrCloseBtn?.addEventListener('click', () => {
        qrModal.style.display = 'none';
        document.querySelector('.toggle-container').classList.remove('modal-open');
        stopQRCheck();
    });
    
    qrModal?.addEventListener('click', (e) => {
        if (e.target === qrModal) {
            qrModal.style.display = 'none';
            document.querySelector('.toggle-container').classList.remove('modal-open');
            stopQRCheck();
        }
    });

    // ==================== SIGN UP VALIDATION ====================
    const signupForm = document.getElementById('signupForm');

    if (signupForm) {
        signupForm.addEventListener('submit', (e) => {
            e.preventDefault();
            document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
            let isValid = true;

            // Username validation
            const username = document.getElementById('username').value.trim();
            if (!username) {
                document.getElementById('usernameError').textContent = "Le nom d'utilisateur est requis.";
                isValid = false;
            } else if (username.length < 3) {
                document.getElementById('usernameError').textContent = "Minimum 3 caractères requis.";
                isValid = false;
            } else if (username.length > 50) {
                document.getElementById('usernameError').textContent = "Maximum 50 caractères autorisés.";
                isValid = false;
            } else if (!/^[a-zA-Z0-9_]+$/.test(username)) {
                document.getElementById('usernameError').textContent = "Seuls lettres, chiffres et _ sont autorisés.";
                isValid = false;
            }

            // Email validation
            const email = document.getElementById('email').value.trim();
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email) {
                document.getElementById('emailError').textContent = "L'email est requis.";
                isValid = false;
            } else if (!emailPattern.test(email)) {
                document.getElementById('emailError').textContent = "Format d'email invalide.";
                isValid = false;
            } else if (email.length > 100) { // allow long emails
                document.getElementById('emailError').textContent = "Maximum 100 caractères autorisés.";
                isValid = false;
            }

            // Phone validation (optional)
            const phone = document.getElementById('phone').value.trim();
            if (phone) {
                if (!/^\d+$/.test(phone)) {
                    document.getElementById('phoneError').textContent = "Seuls les chiffres sont autorisés.";
                    isValid = false;
                } else if (phone.length < 8) {
                    document.getElementById('phoneError').textContent = "Minimum 8 chiffres requis.";
                    isValid = false;
                } else if (phone.length > 15) {
                    document.getElementById('phoneError').textContent = "Maximum 15 chiffres autorisés.";
                    isValid = false;
                }
            }

            // Password validation
            const password = document.getElementById('password').value;
            if (!password) {
                document.getElementById('passwordError').textContent = "Le mot de passe est requis.";
                isValid = false;
            } else if (password.length < 6) {
                document.getElementById('passwordError').textContent = "Minimum 6 caractères requis.";
                isValid = false;
            } else if (password.length > 100) {
                document.getElementById('passwordError').textContent = "Maximum 100 caractères autorisés.";
                isValid = false;
            }

            if (isValid) signupForm.submit();
        });
    }

    // ==================== LOGIN VALIDATION ====================
    const loginForm = document.getElementById('loginForm');

    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            document.getElementById('loginEmailError').textContent = '';
            document.getElementById('loginPasswordError').textContent = '';
            let isValid = true;

            const email = document.getElementById('loginEmail').value.trim();
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email) {
                document.getElementById('loginEmailError').textContent = "L'email est requis.";
                isValid = false;
            } else if (!emailPattern.test(email)) {
                document.getElementById('loginEmailError').textContent = "Format d'email invalide.";
                isValid = false;
            } else if (email.length > 100) { // allow long emails
                document.getElementById('loginEmailError').textContent = "Maximum 100 caractères autorisés.";
                isValid = false;
            }

            const password = document.getElementById('loginPassword').value;
            if (!password) {
                document.getElementById('loginPasswordError').textContent = "Le mot de passe est requis.";
                isValid = false;
            }

            if (isValid) loginForm.submit();
        });
    }

    // ==================== FORGOT PASSWORD MODAL ====================
    const forgotLink = document.getElementById('forgotPasswordLink');
    const modalOverlay = document.getElementById('forgotPasswordModal');
    const modalClose = document.getElementById('modalClose');
    const forgotForm = document.getElementById('forgotPasswordForm');

    if (forgotLink && modalOverlay) {
        forgotLink.addEventListener('click', (e) => {
            e.preventDefault();
            modalOverlay.classList.add('active');
        });
    }

    if (modalClose && modalOverlay) {
        modalClose.addEventListener('click', () => {
            modalOverlay.classList.remove('active');
        });
    }

    if (modalOverlay) {
        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) {
                modalOverlay.classList.remove('active');
            }
        });
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modalOverlay && modalOverlay.classList.contains('active')) {
            modalOverlay.classList.remove('active');
        }
    });

    if (forgotForm) {
        forgotForm.addEventListener('submit', (e) => {
            const emailInput = document.getElementById('forgotEmail');
            const email = emailInput.value.trim();
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (!email || !emailPattern.test(email)) {
                e.preventDefault();
                alert("Veuillez entrer une adresse email valide.");
            }
        });
    }

    <?php if ($showForgotModal): ?>
    if (modalOverlay) modalOverlay.classList.add('active');
    <?php endif; ?>
});
</script>
<!-- Forgot Password Modal -->
<!-- Face Recognition Modal -->
<div id="faceModal" style="display:none;position:fixed;inset:0;align-items:center;justify-content:center;background:rgba(0,0,0,0.5);z-index:9999999;padding:20px;">
    <div style="background:#fff;border-radius:12px;max-width:540px;width:100%;padding:16px;color:#111;display:flex;flex-direction:column;gap:8px;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <strong>Connexion par reconnaissance faciale</strong>
            <button id="faceCancelBtn" style="background:transparent;border:none;font-size:18px;cursor:pointer;">✖</button>
        </div>
        <video id="faceVideo" autoplay muted playsinline style="width:100%;height:auto;background:#000;border-radius:8px;" ></video>
        <div style="display:flex;gap:8px;align-items:center;">
            <button id="faceCaptureBtn" style="flex:1;padding:10px;border-radius:8px;border:none;background:#017a8a;color:#fff;cursor:pointer;">Capturer</button>
            <div id="faceStatus" style="font-size:13px;color:#333;min-width:180px;">En attente</div>
        </div>
    </div>
</div>

<!-- Forgot Password Modal -->
<div class="modal-overlay" id="forgotPasswordModal">
    <div class="modal">
        <div class="modal-header">
            <button type="button" class="modal-close" id="modalClose">
                <i class="fas fa-times"></i>
            </button>
            <i class="fas fa-key header-icon"></i>
            <h2>Mot de passe oublié ?</h2>
            <p>Entrez votre email pour recevoir un lien de réinitialisation</p>
        </div>
        <div class="modal-body">
            <?php if ($resetMessage): ?>
                <div class="modal-message success">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($resetMessage) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($resetError): ?>
                <div class="modal-message error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($resetError) ?>
                </div>
            <?php endif; ?>
            
            <form id="forgotPasswordForm" action="<?= BASE_URL ?>PerFranMVC/Controller/password-reset.php" method="POST">
                <input type="hidden" name="action" value="request">
                <div class="input-group">
                    <label for="forgotEmail">Adresse email</label>
                    <input type="email" id="forgotEmail" name="email" placeholder="Entrez votre email" required>
                </div>
                <button type="submit">
                    <i class="fas fa-paper-plane"></i>
                    Envoyer le lien
                </button>
            </form>
            <div class="modal-footer">
                <a href="#" onclick="document.getElementById('forgotPasswordModal').classList.remove('active'); return false;">
                    <i class="fas fa-arrow-left"></i> Retour à la connexion
                </a>
            </div>
        </div>
    </div>
</div>



<script>
// AI password helper + strength meter
window.PERFRAN_AI_API_URL = '<?= PERFRAN_AI_API_URL ?>';
document.addEventListener('DOMContentLoaded', function(){
    const pwd = document.getElementById('loginPassword');
    const fill = document.getElementById('strength-fill');
    const text = document.getElementById('strength-text');
    const suggestBtn = document.getElementById('suggestWithAI');
    const aiBox = document.getElementById('aiSuggestion');
    const aiText = document.getElementById('aiSuggestionText');
    const aiExplain = document.getElementById('aiExplanation');
    const copyBtn = document.getElementById('copyAiPassword');
    const useBtn = document.getElementById('useAiPassword');
    const helperNote = document.getElementById('aiHelperNote');

    function evaluatePassword(p) {
        let score = 0;
        if (!p) return {score:0,label:'',color:'#ddd'};
        // length
        score += Math.min(40, p.length * 4);
        // variety
        if (/[a-z]/.test(p)) score += 15;
        if (/[A-Z]/.test(p)) score += 15;
        if (/[0-9]/.test(p)) score += 15;
        if (/[^A-Za-z0-9]/.test(p)) score += 15;
        score = Math.max(0, Math.min(100, score));
        let label='Très faible', color='#ff4d4f';
        if (score > 85) { label='Très fort'; color='#16a34a'; }
        else if (score > 70) { label='Fort'; color='#2bb673'; }
        else if (score > 50) { label='Moyen'; color='#ffb84d'; }
        else if (score > 30) { label='Faible'; color='#ff874d'; }
        return {score, label, color};
    }

    function updateStrength(p) {
        const r = evaluatePassword(p);
        fill.style.width = r.score + '%';
        // gradient/color
        if (r.score > 85) fill.style.background = 'linear-gradient(90deg,#2dd4bf,#16a34a)';
        else if (r.score > 70) fill.style.background = 'linear-gradient(90deg,#9be7b8,#2bb673)';
        else if (r.score > 50) fill.style.background = 'linear-gradient(90deg,#ffd78a,#ffb84d)';
        else fill.style.background = 'linear-gradient(90deg,#ff9b8a,#ff4d4f)';
        text.textContent = r.label + (p ? (' — ' + Math.round(r.score) + '%') : '');

        // suggestions for weak passwords
        if (r.score < 50 && p) {
            const suggestions = [];
            if (p.length < 12) suggestions.push('augmentez la longueur');
            if (!/[A-Z]/.test(p)) suggestions.push('ajoutez des majuscules');
            if (!/[0-9]/.test(p)) suggestions.push('ajoutez des chiffres');
            if (!/[^A-Za-z0-9]/.test(p)) suggestions.push('ajoutez des symboles');
            helperNote.textContent = 'Suggestions: ' + suggestions.join(', ') + '.';
        } else {
            helperNote.textContent = "Génère un mot de passe fort.";
        }
    }

    if (pwd) {
        pwd.addEventListener('input', (e) => updateStrength(e.target.value));
        // init
        updateStrength(pwd.value || '');
    }

    function getAiBase() {
        let b = (window.PERFRAN_AI_API_URL && window.PERFRAN_AI_API_URL.trim()) || '';
        if (!b) b = 'http://localhost:3001/ai';
        return b.replace(/\/$/, '');
    }

    let lastAi = null;
    if (suggestBtn) {
        suggestBtn.addEventListener('click', async function(){
            suggestBtn.disabled = true; suggestBtn.textContent = 'Génération...';
            aiBox.style.display = 'none';
            try {
                const configured = (window.PERFRAN_AI_API_URL || '').replace(/\/$/, '');
                const base = getAiBase();
                const candidates = [];
                if (configured) candidates.push(configured.replace(/\/?$/, ''));
                // if configured points to an AI prefix like /ai, try both with and without '/ai'
                if (configured && !configured.endsWith('/suggest-password')) candidates.push(configured + '/suggest-password');
                // standard candidate using base
                candidates.push(base + '/suggest-password');

                let j = null;
                for (const url of candidates) {
                    try {
                        const res = await fetch(url + (url.includes('?') ? '&' : '?') + 'strength=strong');
                        if (!res.ok) continue;
                        j = await res.json();
                        if (j && (j.password || j.error)) break;
                    } catch (e) {
                        // try next
                    }
                }

                if (j && j.password) {
                    const pwdVal = String(j.password).trim();
                    lastAi = {pwd: pwdVal, src: j.source || 'ai'};
                    aiText.textContent = pwdVal;
                    aiExplain.textContent = 'Pourquoi: mot long, mélange de majuscules/minuscules, chiffres et symboles. Source: ' + lastAi.src;
                    aiBox.style.display = 'block';
                } else {
                    aiText.textContent = '';
                    aiExplain.textContent = 'Le service IA est indisponible ou n\'a pas renvoyé de mot de passe.';
                    aiBox.style.display = 'block';
                }
            } catch (err) {
                aiText.textContent = '';
                aiExplain.textContent = 'Erreur lors de la requête au service IA.';
                aiBox.style.display = 'block';
            } finally {
                suggestBtn.disabled = false; suggestBtn.textContent = "Avec l'IA";
            }
        });
    }

    if (copyBtn) copyBtn.addEventListener('click', async function(){
        if (!lastAi || !lastAi.pwd) return;
        try {
            await navigator.clipboard.writeText(lastAi.pwd);
            if (window.Swal) Swal.fire({icon:'success',title:'Copié',text:'Mot de passe copié dans le presse-papiers',timer:1400,showConfirmButton:false});
        } catch (e) {
            if (window.Swal) Swal.fire({icon:'error',title:'Erreur',text:'Impossible de copier automatiquement.'});
        }
    });

    function setFullStrong() {
        if (!fill) return;
        fill.style.width = '100%';
        fill.style.background = 'linear-gradient(90deg,#2dd4bf,#16a34a)';
        if (text) text.textContent = 'Très fort — 100%';
        if (helperNote) helperNote.textContent = 'Mot de passe IA appliqué — sécurisé.';
    }

    if (useBtn) useBtn.addEventListener('click', function(){
        if (!lastAi || !lastAi.pwd) return;
        if (pwd) { pwd.value = lastAi.pwd; pwd.focus(); }
        // Make the strength meter show full green for AI suggestions
        setFullStrong();
        if (window.Swal) Swal.fire({icon:'success',title:'Mot de passe appliqué',timer:1200,showConfirmButton:false});
    });
});
// mascot loader follows
(function(){
    function loadMascot(){
        if(window.__perfRanMascotLoaded) return;
        window.__perfRanMascotLoaded = true;
        var s = document.createElement('script');
        s.src = '<?= BASE_URL ?>PerFranMVC/View/FrontOffice/assets/js/mascot.js';
        s.async = true;
        document.body.appendChild(s);
    }
    setTimeout(loadMascot, 1500);
})();
</script>

</body>
</html>
<?php ob_end_flush(); ?>