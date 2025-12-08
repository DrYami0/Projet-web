<?php
session_start();
require_once __DIR__ . '/../../Model/Quiz.php';
require_once __DIR__ . '/../../Model/QuizBlank.php';
require_once __DIR__ . '/../../Controller/QuizController.php';
require_once __DIR__ . '/../../Controller/QuizBlankController.php';

$error = '';

$id = (int)($_GET['id'] ?? 0);
$blank = QuizBlankController::getById($id);

if (!$blank) {
    header('HTTP/1.0 404 Not Found');
    die('Blank non trouvé');
}

$quiz = QuizController::getById($blank->qid);

if (!$quiz) {
    header('HTTP/1.0 404 Not Found');
    die('Quiz non trouvé');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    QuizBlankController::edit($id, $_POST);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Éditer un Blank</title>
    <link rel="stylesheet" href="../FrontOffice/assets/css/bootstrap.css">
    <link rel="stylesheet" href="../FrontOffice/assets/css/style.css">
    <link rel="stylesheet" href="../FrontOffice/assets/css/backoffice.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Éditer un Blank</h1>
            <p>Modifier la réponse correcte du blank</p>
        </div>
        
        <div class="form-container">
            <div class="info-box">
                <strong>Quiz:</strong> #<?= htmlspecialchars($quiz->qid) ?><br>
                <strong>Position:</strong> <?= htmlspecialchars($blank->position + 1) ?><br>
                <strong>Ancienne réponse:</strong> <code><?= htmlspecialchars($blank->correctAnswer) ?></code>
            </div>
            
            <form method="post" action="">
                <div class="form-group">
                    <label for="correctAnswer">Nouvelle réponse correcte</label>
                    <input type="text" 
                           id="correctAnswer" 
                           name="correctAnswer" 
                           value="<?= htmlspecialchars($blank->correctAnswer) ?>" 
                           required
                           autofocus>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 30px;">
                    <button type="submit" class="btn-submit">
                        <i class="fa fa-check"></i> Appliquer
                    </button>
                    <a href="blank_list.php?quiz_id=<?= htmlspecialchars($quiz->qid) ?>" class="btn-cancel">
                        <i class="fa fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="../FrontOffice/assets/js/sweetalert2-helper.js"></script>
    <script>
        // Afficher les messages PHP avec SweetAlert2
        document.addEventListener('DOMContentLoaded', function() {
            const errorMsg = document.getElementById('error-message');
            
            if (errorMsg && errorMsg.dataset.message) {
                showError('Erreur !', errorMsg.dataset.message);
            }
        });
    </script>
</body>
</html>
