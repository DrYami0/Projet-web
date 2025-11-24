<?php
session_start();
require_once __DIR__ . '/../config.php';
$pdo = obtenirPDO();

if (empty($_SESSION['user']) || $_SESSION['user']['role'] != 1) {
    $_SESSION['error'] = "Accès refusé.";
    header('Location: ' . BASE_URL . 'view/FrontOffice/login.php');
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? 'dashboard';

switch ($action) {

    case 'list_all':
        $stmt = $pdo->query("SELECT * FROM users ORDER BY uid DESC");
        $users = $stmt->fetchAll();
        $pageTitle = "Gestion des utilisateurs";
        require __DIR__ . '/../view/BackOffice/users_list.php';
        break;

    case 'edit':
        $uid = (int)($_GET['uid'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM users WHERE uid=?");
        $stmt->execute([$uid]);
        $user = $stmt->fetch();
        if (!$user) {
            $_SESSION['error'] = "Utilisateur introuvable.";
            header('Location: ' . BASE_URL . 'controller/admin.php?action=list_all');
            exit;
        }
        $pageTitle = "Modifier l'utilisateur";
        require __DIR__ . '/../view/BackOffice/user_edit.php';
        break;

    case 'update':
        $uid = (int)($_POST['uid'] ?? 0);
        $email = trim($_POST['email'] ?? '');
        $role = (int)($_POST['role'] ?? 0);
        $firstName = trim($_POST['firstName'] ?? '');
        $lastName = trim($_POST['lastName'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if ($uid <= 0 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Données invalides.";
            header('Location: ' . BASE_URL . 'controller/admin.php?action=edit&uid=' . $uid);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE users SET email=?, role=?, firstName=?, lastName=?, phone=? WHERE uid=?");
        $stmt->execute([$email, $role, $firstName ?: null, $lastName ?: null, $phone ?: null, $uid]);

        $_SESSION['message'] = "Utilisateur mis à jour.";
        header('Location: ' . BASE_URL . 'controller/admin.php?action=list_all');
        exit;

    case 'delete':
        $uid = (int)($_GET['uid'] ?? 0);
        if ($uid > 0 && $uid != $_SESSION['user']['uid']) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE uid=?");
            $stmt->execute([$uid]);
            $_SESSION['message'] = "Utilisateur supprimé.";
        } else {
            $_SESSION['error'] = "Impossible de supprimer cet utilisateur.";
        }
        header('Location: ' . BASE_URL . 'controller/admin.php?action=list_all');
        exit;

    default:
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
        $totalUsers = $stmt->fetch()['total'];
        $pageTitle = "Tableau de bord administrateur";
        require __DIR__ . '/../view/BackOffice/dashboard.php';
        break;
}
?>