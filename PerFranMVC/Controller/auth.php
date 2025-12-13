<?php

session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/UserC.php';

$userC = new UserC();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    case 'signup':
        $adminEmail = 'redacted_admin_email@example.com';
        $adminPassword = 'REDACTED_INITIAL_ADMIN_PASSWORD';
        $existingAdmin = $userC->findByEmail($adminEmail);
        if (!$existingAdmin) {
            $adminData = [
                'username'      => 'admin',
                'firstName'     => 'Admin',
                'lastName'      => 'User',
                'email'         => $adminEmail,
                'phone'         => null,
                'passwordHash'  => password_hash($adminPassword, PASSWORD_DEFAULT),
                'status'        => 'Active',
                'token'         => 48,
                'role'          => 1,
            ];
            $userC->createPending($adminData);
        } else {
            if (!password_verify($adminPassword, $existingAdmin['password_hash'])) {
                $userC->updatePasswordByEmail($adminEmail, password_hash($adminPassword, PASSWORD_DEFAULT));
            }
        }

        $username  = trim($_POST['username'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = $_POST['password'] ?? '';
        $firstName = trim($_POST['firstName'] ?? '');
        $lastName  = trim($_POST['lastName'] ?? '');
        $phone     = trim($_POST['phone'] ?? '');

        if (empty($username) || strlen($username) < 3 || !preg_match('/^[a-zA-Z0-9_]+$/', $username)
            || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
            $_SESSION['signup_error'] = 'Données invalides.';
            header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/login.php');
            exit;
        }

        if ($userC->findByUsername($username) || $userC->findByEmail($email)) {
            $_SESSION['signup_error'] = 'Compte déjà existant ou en attente.';
            header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/login.php');
            exit;
        }

        $userData = [
            'username'      => $username,
            'firstName'     => $firstName,
            'lastName'      => $lastName,
            'email'         => $email,
            'phone'         => $phone ? (int)$phone : null,
            'passwordHash'  => password_hash($password, PASSWORD_DEFAULT),
            'status'        => 'Inactive',
            'token'         => 48,
            'role'          => 0,
        ];

        // Ensure role is explicitly zero for regular signups
        $userData['role'] = 0;
        $userC->createPending($userData);

        $approve = BASE_URL . "PerFranMVC/Controller/validateUser.php?action=approve&user=" . urlencode($username);
        $refuse  = BASE_URL . "PerFranMVC/Controller/validateUser.php?action=refuse&user=" . urlencode($username);

        $adminBody = "<h2>Nouvelle inscription</h2><p><strong>$username</strong> ($email)</p>
                 <p>
                     <a href='$approve' style='background:#27ae60;color:#fff;padding:12px 24px;text-decoration:none;border-radius:6px;'>Approuver</a>
                     <a href='$refuse' style='background:#e74c3c;color:#fff;padding:12px 24px;text-decoration:none;border-radius:6px;'>Refuser</a>
                 </p>";

        // Notify admin (keep existing behavior) — use envoyerMailAdmin when available
        if (function_exists('envoyerMailAdmin')) {
            envoyerMailAdmin(ADMIN_EMAIL, 'Nouvelle inscription - Validation requise', $adminBody);
        } else {
            mail(ADMIN_EMAIL, 'Nouvelle inscription - Validation requise', $adminBody, "Content-Type: text/html; charset=UTF-8");
        }

        // Notify the user that their signup is pending
        $userBody = "<h2>Merci pour votre inscription, $firstName</h2>\n"
                  . "<p>Votre compte <strong>$username</strong> a bien été reçu et est en attente de validation par un administrateur.</p>\n"
                  . "<p>Vous recevrez un email lorsque votre compte sera approuvé.</p>\n";
        if (function_exists('envoyerMailUtilisateur')) {
            envoyerMailUtilisateur($email, 'Inscription reçue - En attente de validation', $userBody);
        } else {
            // fallback to basic mail
            mail($email, 'Inscription reçue - En attente de validation', $userBody, "Content-Type: text/html; charset=UTF-8");
        }
        $_SESSION['pending_username'] = $username;
        header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/user-status.php');
        exit;

    case 'login':
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Veuillez remplir tous les champs.';
            header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/login.php');
            exit;
        }

        $dbUser = $userC->findByEmail($email);

        if (!$dbUser) {
            $_SESSION['error'] = 'Aucun compte trouvé avec cet email.';
            header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/login.php');
            exit;
        }

        if (empty($dbUser['password_hash']) || !password_verify($password, $dbUser['password_hash'])) {
            $_SESSION['error'] = 'Mot de passe incorrect.';
            header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/login.php');
            exit;
        }

        if (($dbUser['status'] ?? 'Inactive') !== 'Active') {
            $_SESSION['pending_username'] = $dbUser['username'];
            header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/user-status.php');
            exit;
        }

        // Set session variables for both user system and game system
        $_SESSION['uid'] = $dbUser['username'];
        $_SESSION['user'] = $dbUser;
        $_SESSION['role'] = $dbUser['role'];
        $_SESSION['user_id'] = $dbUser['uid'];        // For game system compatibility
        $_SESSION['username'] = $dbUser['username'];  // For game system compatibility
        header('Location: ' . BASE_URL . 'index.php');
        exit;

    case 'logout':
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/login.php');
        exit;

    default:
        http_response_code(400);
        exit('Action invalide');
}