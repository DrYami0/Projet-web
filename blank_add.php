<?php
// Vue ajout d'un blank
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un blank</title>
    <link rel="stylesheet" href="../../FrontOffice/assets/css/bootstrap.css">
    <link rel="stylesheet" href="../../FrontOffice/assets/css/backoffice.css">
</head>
<body>
<header class="MainTitle smallHeader">
    <a href="blank_list.php" class="brand">
        <img src="../../FrontOffice/assets/img/logo.png" alt="PerFran">
        <div>
            <h1>Ajouter un blank</h1>
            <p>Créer un nouveau blank pour ce quizz</p>
        </div>
    </a>
</header>

<main class="container">
    <form method="post" action="">
        <div class="tableWrap" style="padding:20px;">
            <div style="margin-bottom:12px;">
                <label for="position">Position</label><br>
                <input type="number" id="position" name="position" min="1" value="1">
            </div>
            <div style="margin-bottom:12px;">
                <label for="correctAnswer">Réponse correcte</label><br>
                <input type="text" id="correctAnswer" name="correctAnswer" style="width:100%;">
            </div>
            <button type="submit" class="btn">Enregistrer</button>
            <a href="blank_list.php" class="btn secondary">Annuler</a>
        </div>
    </form>
</main>

<footer>
    PerFran — Gestion des blanks
</footer>
</body>
</html>
