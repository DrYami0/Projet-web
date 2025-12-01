<?php
session_start();
require_once "../../Controller/GameC.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$gid = $_GET['gid'] ?? null;
if (!$gid) {
    header("Location: index.html");
    exit();
}

$gameC = new GameC();
$existingGameData = $gameC->getGameById($gid);

// Check if game exists
if (!$existingGameData) {
    header("Location: index.html");
    exit();
}

// Check if user is admin
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] == 1; // Adjust based on your role system

// If not admin, check if user owns the game
if (!$is_admin && $existingGameData['player1id'] != $_SESSION['user_id']) {
    header("Location: index.html");
    exit();
}

// Delete the game
if ($gameC->deleteGame($gid, $_SESSION['user_id'], $is_admin)) {
    header("Location: " . ($is_admin ? "DisplayGamesAdmin.php" : "index.html") . "?message=Game deleted successfully");
} else {
    header("Location: " . ($is_admin ? "DisplayGamesAdmin.php" : "index.html") . "?error=Cannot delete this game");
}
exit();
?>