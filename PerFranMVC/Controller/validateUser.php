<?php

session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/UserC.php';

$action   = $_GET['action'] ?? '';
$username = $_GET['user'] ?? '';

if (!$username || !in_array($action, ['approve', 'refuse'])) {
    die("Paramètres invalides.");
}

$userRepo = new UserC();
$user = $userRepo->findByUsername($username);
if (!$user) {
    die("Utilisateur introuvable.");
}

if ($action === 'approve') {
    $userRepo->activateByUsername($username);

    $subject = "Votre compte a été approuvé !";
    $body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
        <h2 style='color: #27ae60;'>Compte approuvé</h2>
        <p>Bonjour <strong>{$user['username']}</strong>,</p>
        <p>Votre compte a été approuvé ! Vous pouvez maintenant vous connecter.</p>
        <div style='text-align: center; margin: 30px 0;'>
            <a href='" . BASE_URL . "PerFranMVC/View/FrontOffice/login.php' style='padding: 12px 30px; background: #667eea; color: #fff; text-decoration: none; border-radius: 8px;'>Se connecter</a>
        </div>
        <p>Cordialement,<br>L'équipe PerfRan</p>
    </div>";

    envoyerMailUtilisateur($user['email'], $subject, $body);

    $message = "Utilisateur approuvé";
    $color   = "#27ae60";

} elseif ($action === 'refuse') {

    $subject = "Votre inscription a été refusée";
    $body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
        <h2 style='color: #e74c3c;'>Inscription refusée</h2>
        <p>Bonjour <strong>{$user['username']}</strong>,</p>
        <p>Votre demande d'inscription a été refusée par l'administrateur.</p>
        <p>Pour plus d'informations, contactez : " . ADMIN_EMAIL . "</p>
        <p>Cordialement,<br>L'équipe PerfRan</p>
    </div>";

    envoyerMailUtilisateur($user['email'], $subject, $body);
    
    $userRepo->refuseByUsername($username);

    $message = "Utilisateur refusé";
    $color   = "#e74c3c";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validation - PerfRan</title>
    <style>
        body {font-family: Arial;background: linear-gradient(135deg,#667eea,#764ba2);display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0;}
        .box {background:#fff;padding:40px;border-radius:20px;box-shadow:0 15px 35px rgba(0,0,0,0.2);text-align:center;max-width:500px;}
        h2 {color:<?= $color ?>;font-size:28px;margin-bottom:20px;}
        a {display:inline-block;margin:15px;padding:12px 30px;background:#667eea;color:#fff;text-decoration:none;border-radius:8px;}
        a:hover {opacity:0.9;}
    </style>
</head>
<body>
<div class="box">
    <h2><?= $message ?></h2>
    <p>L'utilisateur <strong><?= htmlspecialchars($username) ?></strong> a été traité.</p>
    <p>Un email a été envoyé à l'utilisateur.</p>
    <a href="<?= BASE_URL ?>PerFranMVC/View/BackOffice/pending.php">Retour à la liste</a>
</div>
</body>
</html>