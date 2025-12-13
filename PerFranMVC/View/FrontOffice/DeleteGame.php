<?php
require_once "../../config.php";
require_once "../../PerFranMVC/Controller/GameC.php";

// Check if user is logged in
$userId = $_SESSION['user_id'] ?? $_SESSION['user']['uid'] ?? null;
if (!$userId) {
    header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/login.php');
    exit();
}

$gid = $_GET['gid'] ?? null;
if (!$gid) {
    header("Location: /projet-web/index.php");
    exit();
}

$gameC = new GameC();
$existingGameData = $gameC->getGameById($gid);

// Check if game exists
if (!$existingGameData) {
    header("Location: /projet-web/index.php");
    exit();
}

// Check if user is admin
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] == 1; // Adjust based on your role system

// If not admin, check if user owns the game
if (!$is_admin && $existingGameData['player1id'] != $_SESSION['user_id']) {
    header("Location: /projet-web/index.php");
    exit();
}

// Delete the game
if ($gameC->deleteGame($gid, $_SESSION['user_id'], $is_admin)) {
    header("Location: " . ($is_admin ? "DisplayGamesAdmin.php" : "/projet-web/index.php") . "?message=Game deleted successfully");
} else {
    header("Location: " . ($is_admin ? "DisplayGamesAdmin.php" : "/projet-web/index.php") . "?error=Cannot delete this game");
}
exit();
?>