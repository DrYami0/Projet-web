<?php
session_start();

// Chemins corrects depuis view/FrontOffice/login.php
require_once __DIR__ . '/../../controller/config.php';     // ← config.php est dans controller/
require_once __DIR__ . '/../../model/user.php';
require_once __DIR__ . '/../../controller/userC.php';
// Check if user just signed up successfully
$showSignupSuccess = isset($_SESSION['signup_success']) && $_SESSION['signup_success'] === true;
if ($showSignupSuccess) {
    unset($_SESSION['signup_success']); // Clear it after displaying
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css" />
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
    z-index: 1000;
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
</style>
</head>
<body>

<div class="container" id="container">
    <!-- Sign Up Form -->
    <div class="form-container sign-up">
        <form id="signupForm" action="<?= BASE_URL ?>controller/auth.php" method="POST" novalidate>
            <input type="hidden" name="action" value="signup">
            <h1>Créer un Compte</h1>
            <div class="social-icons">
                <a href="#" class="icon"><i class="fa-brands fa-google-plus-g"></i></a>
                <a href="#" class="icon"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="#" class="icon"><i class="fa-brands fa-github"></i></a>
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
        <form id="loginForm" action="<?= BASE_URL ?>controller/auth.php" method="POST" novalidate>
            <input type="hidden" name="action" value="login">
            <h1>Se Connecter</h1>
            <div class="social-icons">
                <a href="#" class="icon"><i class="fa-brands fa-google-plus-g"></i></a>
                <a href="#" class="icon"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="#" class="icon"><i class="fa-brands fa-github"></i></a>
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
            
            <a href="#">Mot de passe oublié ?</a>
            <button type="submit">Se Connecter</button>
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
});
</script>


</body>
</html>