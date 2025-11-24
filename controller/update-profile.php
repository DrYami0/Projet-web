<?php
session_start();
require_once __DIR__ . '/config.php';

// User must be logged in
if (!isset($_SESSION['uid'])) {
    header('Location: ' . BASE_URL . 'view/FrontOffice/login.php');
    exit;
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'view/FrontOffice/account-profile.php');
    exit;
}

// Get username from session (NOT casting to int!)
$username = $_SESSION['uid'];

// === GET FORM DATA ===
$fullname = trim($_POST['fullname'] ?? '');
$email    = trim($_POST['email'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$newPass  = $_POST['new_password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';

// Split fullname
$names = explode(' ', $fullname, 2);
$firstName = $names[0] ?? '';
$lastName  = $names[1] ?? '';

// === PASSWORD UPDATE ===
$passwordHash = null;

if (!empty($newPass)) {
    if ($newPass !== $confirm) {
        $_SESSION['error'] = "Les mots de passe ne correspondent pas.";
        header('Location: ' . BASE_URL . 'view/FrontOffice/account-profile.php');
        exit;
    }

    if (strlen($newPass) < 6) {
        $_SESSION['error'] = "Le mot de passe doit contenir au moins 6 caractères.";
        header('Location: ' . BASE_URL . 'view/FrontOffice/account-profile.php');
        exit;
    }

    // Use password_hash() to match signup method
    $passwordHash = password_hash($newPass, PASSWORD_DEFAULT);
}

// === AVATAR UPLOAD ===
$avatarPath = null;

if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {

    $uploadDir = __DIR__ . '/../assets/uploads/avatars/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (in_array($ext, $allowed) && $_FILES['avatar']['size'] < 5000000) {
        $filename = "avatar_{$username}_" . time() . ".$ext";
        $destination = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $destination)) {
            $avatarPath = BASE_URL . 'assets/uploads/avatars/' . $filename;
        }
    }
}

// === UPDATE DATABASE ===
try {

    $sql = "UPDATE users SET 
                firstName = ?, 
                lastName = ?, 
                email = ?, 
                phone = ?";

    $params = [$firstName, $lastName, $email, $phone ?: null];

    if ($passwordHash !== null) {
        $sql .= ", password_hash = ?";
        $params[] = $passwordHash;
    }

    if ($avatarPath !== null) {
        $sql .= ", avatar = ?";
        $params[] = $avatarPath;
    }

    $sql .= " WHERE username = ?";
    $params[] = $username;

    // Execute update
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // === UPDATE JSON FILE ===
    $jsonFile = __DIR__ . '/approved/' . $username . '.json';
    
    if (file_exists($jsonFile)) {
        $jsonData = json_decode(file_get_contents($jsonFile), true);
        
        // Update JSON with new data
        $jsonData['firstName'] = $firstName;
        $jsonData['lastName'] = $lastName;
        $jsonData['email'] = $email;
        $jsonData['phone'] = $phone ?: null;
        
        if ($passwordHash !== null) {
            $jsonData['passwordHash'] = $passwordHash;
        }
        
        // Save updated JSON
        file_put_contents($jsonFile, json_encode($jsonData, JSON_PRETTY_PRINT));
    }

    // === REFRESH SESSION WITH UPDATED USER DATA ===
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $_SESSION['user'] = $stmt->fetch(PDO::FETCH_ASSOC);

    $_SESSION['success'] = "Profil mis à jour avec succès !";

} catch (Exception $e) {
    $_SESSION['error'] = "Erreur lors de la mise à jour : " . $e->getMessage();
}

// Redirect back to profile page
header('Location: ' . BASE_URL . 'view/FrontOffice/account-profile.php');
exit;