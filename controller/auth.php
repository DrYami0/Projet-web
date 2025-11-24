<?php
// controller/auth.php — VERSION FINALE PARFAITE (À REMPLACER MAINTENANT)
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/userC.php';

$userC = new UserC();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    // ==================================== INSCRIPTION ====================================
    case 'signup':
        $username  = trim($_POST['username'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = $_POST['password'] ?? '';
        $firstName = trim($_POST['firstName'] ?? '');
        $lastName  = trim($_POST['lastName'] ?? '');
        $phone     = trim($_POST['phone'] ?? '');

        if (empty($username) || strlen($username) < 3 || !preg_match('/^[a-zA-Z0-9_]+$/', $username)
            || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
            $_SESSION['signup_error'] = 'Données invalides.';
            header('Location: ' . BASE_URL . 'view/FrontOffice/login.php');
            exit;
        }

        $pendingFile  = __DIR__ . "/pending/{$username}.json";
        $approvedFile = __DIR__ . "/approved/{$username}.json";
        if (file_exists($pendingFile) || file_exists($approvedFile)) {
            $_SESSION['signup_error'] = 'Compte déjà existant ou en attente.';
            header('Location: ' . BASE_URL . 'view/FrontOffice/login.php');
            exit;
        }

        if (!is_dir(__DIR__ . '/pending')) mkdir(__DIR__ . '/pending', 0777, true);

        $userData = [
            'username'     => $username,
            'firstName'    => $firstName,
            'lastName'     => $lastName,
            'email'        => $email,
            'phone'        => $phone ? (int)$phone : null,
            'passwordHash' => password_hash($password, PASSWORD_DEFAULT),
            'created_at'   => time()
        ];

        file_put_contents($pendingFile, json_encode($userData, JSON_PRETTY_PRINT));

        // Email admin
        $approve = BASE_URL . "controller/validateUser.php?action=approve&user=" . urlencode($username);
        $refuse  = BASE_URL . "controller/validateUser.php?action=refuse&user=" . urlencode($username);

        $body = "<h2>Nouvelle inscription</h2><p><strong>$username</strong> ($email)</p>
                 <p>
                   <a href='$approve' style='background:#27ae60;color:#fff;padding:12px 24px;text-decoration:none;border-radius:6px;'>Approuver</a>
                   <a href='$refuse' style='background:#e74c3c;color:#fff;padding:12px 24px;text-decoration:none;border-radius:6px;'>Refuser</a>
                 </p>";

        mail(ADMIN_EMAIL, 'Nouvelle inscription - Validation requise', $body, "Content-Type: text/html; charset=UTF-8");

        $_SESSION['signup_success'] = true;
        header('Location: ' . BASE_URL . 'view/FrontOffice/login.php');
        exit;

    // ==================================== CONNEXION ====================================
    case 'login':
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Veuillez remplir tous les champs.';
            header('Location: ' . BASE_URL . 'view/FrontOffice/login.php');
            exit;
        }

        $pendingUser = $approvedUser = $dbUser = null;
        $pendingUsername = $approvedUsername = null;

        // 1. PENDING (priorité absolue)
        if (is_dir(__DIR__ . '/pending')) {
            foreach (glob(__DIR__ . '/pending/*.json') as $file) {
                $data = json_decode(file_get_contents($file), true);
                if ($data['email'] === $email && password_verify($password, $data['passwordHash'])) {
                    $pendingUser = $data;
                    $pendingUsername = $data['username'];
                    break;
                }
            }
        }

        if ($pendingUser) {
            $_SESSION['pending_username'] = $pendingUsername;
            header('Location: ' . BASE_URL . 'view/FrontOffice/user-status.php');
            exit;
        }

        // 2. APPROVED
        if (is_dir(__DIR__ . '/approved')) {
            foreach (glob(__DIR__ . '/approved/*.json') as $file) {
                $data = json_decode(file_get_contents($file), true);
                if ($data['email'] === $email && password_verify($password, $data['passwordHash'])) {
                    $approvedUser = $data;
                    $approvedUsername = $data['username'];
                    break;
                }
            }
        }

        if ($approvedUser) {
            if (!$userC->findByUsername($approvedUsername)) {
                $userC->createFromApproved($approvedUser);
            }
            $dbUser = $userC->findByUsername($approvedUsername);
            $_SESSION['uid'] = $dbUser['username'];
            $_SESSION['user'] = $dbUser;
            $_SESSION['role'] = 0;
            header('Location: ' . BASE_URL . 'user-dashboard.php');
            exit;
        }

        // 3. DANS LA BASE DE DONNÉES (utilisateurs déjà actifs)
        $dbUser = $userC->findByEmail($email);
        if ($dbUser && password_verify($password, $dbUser['password_hash'])) {
            $_SESSION['uid'] = $dbUser['username'];
            $_SESSION['user'] = $dbUser;
            $_SESSION['role'] = $dbUser['role'];

            // REDIRECTION FORCÉE VERS LE BON FICHIER
            header('Location: ' . BASE_URL . 'user-dashboard.php');
            exit;
        }

        // Aucun trouvé
        $_SESSION['error'] = 'Email ou mot de passe incorrect.';
        header('Location: ' . BASE_URL . 'view/FrontOffice/login.php');
        exit;

    case 'logout':
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . 'view/FrontOffice/login.php');
        exit;

    default:
        http_response_code(400);
        exit('Action invalide');
}