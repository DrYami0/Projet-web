<?php
session_start();
require_once "../../Controller/GameC.php";
require_once "../../Model/Game.php";

// Check if user is logged in
// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.php");
//     exit();
// }

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

// Check if user is admin (you'll need to implement this based on your auth system)
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] == 1; // Adjust based on your role system

// If not admin, check if user owns the game
if (!$is_admin && $existingGameData['player1id'] != $_SESSION['user_id']) {
    header("Location: index.html");
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $game = $_POST['game'] ?? '';
    $difficulty = $_POST['difficulty'] ?? '';
    $type = $_POST['type'] ?? '';
    
    // Create game object with updated data
    $gameObj = new Game(
        $title,
        $game,
        $difficulty,
        $type,
        $existingGameData['player1id'],
        $existingGameData['player2id'],
        $existingGameData['status'],
        $existingGameData['createdAt'],
        $existingGameData['startedAt'],
        $existingGameData['endedAt'],
        $existingGameData['winner'],
        $existingGameData['rounds_played'],
        $existingGameData['game_state']
    );
    
    // Update the game
    if ($gameC->editGame($gameObj, $gid, $_SESSION['user_id'], $is_admin)) {
        header("Location: " . ($is_admin ? "DisplayGamesAdmin.php" : "index.html") . "?message=Game updated successfully");
    } else {
        $error = "Failed to update game";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Game - PerFran</title>
    <link rel="stylesheet" href="displayGames.css">
</head>
<body>
    <header class="MainTitle smallHeader">
        <a href="index.html" class="brand">
            <img src="../Perfran.png" alt="Perfran">
            <div>
                <h1>PerFran</h1>
                <p>Modifier les paramètres du jeu</p>
            </div>
        </a>
    </header>

    <main class="container">
        <section class="form-section">
            <h2>Modifier le Jeu #<?php echo htmlspecialchars($gid); ?></h2>
            
            <?php if (isset($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" class="game-form">
                <div class="form-group">
                    <label for="title">Titre:</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($existingGameData['title']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="game">Jeu:</label>
                    <input type="text" id="game" name="game" value="<?php echo htmlspecialchars($existingGameData['game']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="difficulty">Difficulté:</label>
                    <select id="difficulty" name="difficulty" required>
                        <option value="easy" <?php echo $existingGameData['difficulty'] == 'easy' ? 'selected' : ''; ?>>Facile</option>
                        <option value="medium" <?php echo $existingGameData['difficulty'] == 'medium' ? 'selected' : ''; ?>>Moyen</option>
                        <option value="hard" <?php echo $existingGameData['difficulty'] == 'hard' ? 'selected' : ''; ?>>Difficile</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="type">Type:</label>
                    <select id="type" name="type" required>
                        <option value="public" <?php echo $existingGameData['type'] == 'public' ? 'selected' : ''; ?>>Public</option>
                        <option value="private" <?php echo $existingGameData['type'] == 'private' ? 'selected' : ''; ?>>Privé</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">Mettre à jour</button>
                    <a href="<?php echo $is_admin ? 'DisplayGamesAdmin.php' : 'index.html'; ?>" class="btn secondary">Annuler</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>