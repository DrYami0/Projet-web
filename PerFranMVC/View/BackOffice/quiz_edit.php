<?php
session_start();
require_once __DIR__ . '/../../Model/Quiz.php';
require_once __DIR__ . '/../../Model/QuizBlank.php';
require_once __DIR__ . '/../../Controller/QuizController.php';
require_once __DIR__ . '/../../Controller/QuizBlankController.php';

$error = '';
$success = '';

$id = $_GET['id'] ?? 0;
$quiz = QuizController::getById((int)$id);

if (!$quiz) {
    header('HTTP/1.0 404 Not Found');
    die('Quiz non trouvé');
}

// Récupérer les blanks pour affichage dans l'info
$blanks = $quiz->blanks;
usort($blanks, function($a, $b) {
    return $a->position <=> $b->position;
});

// Le paragraphe est déjà au format [mot] dans la base de données
$paragraph = $quiz->paragraph;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Déléguer la mise à jour au contrôleur
    $resultError = QuizController::update($quiz->qid, $_POST);
    
    if ($resultError) {
        $error = $resultError;
    } else {
        // Succès
        $success = 'Quiz mis à jour avec succès !';
        $error = '';
        // Recharger les données
        $quiz = QuizController::getById($quiz->qid);
        $blanks = $quiz->blanks;
        usort($blanks, function($a, $b) {
            return $a->position <=> $b->position;
        });
        $paragraph = $quiz->paragraph;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Éditer un quizz</title>
    <link rel="stylesheet" href="../FrontOffice/assets/css/bootstrap.css">
    <link rel="stylesheet" href="../FrontOffice/assets/css/style.css">
    <link rel="stylesheet" href="../FrontOffice/assets/css/backoffice.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Éditer un quizz</h1>
        </div>
        
        <div class="form-container">
            <?php if ($error): ?>
                <div id="error-message" data-message="<?= htmlspecialchars($error) ?>" style="display: none;"></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div id="success-message" data-message="<?= htmlspecialchars($success) ?>" style="display: none;"></div>
            <?php endif; ?>
            
            <form method="post" action="" id="quizForm">
                <input type="hidden" name="id" value="<?= htmlspecialchars($quiz->qid) ?>">
                <input type="hidden" name="quiz_id" value="<?= htmlspecialchars($quiz->qid) ?>">
                
                <div class="form-group">
                    <label for="paragraphEditor">Texte du quizz</label>
                    <div class="toolbar">
                        <button type="button" class="btn-toolbar" onclick="addBlank()">
                            <i class="fa fa-plus"></i> Ajouter un Blank
                        </button>
                    </div>
                    <div class="editor-container">
                        <textarea id="paragraphEditor" name="paragraph" required><?= htmlspecialchars($paragraph) ?></textarea>
                    </div>
                    <div class="preview">
                        <div class="preview-label">Aperçu :</div>
                        <div id="previewText"></div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="difficulty">Difficulté</label>
                        <select id="difficulty" name="difficulty" required>
                            <option value="easy" <?= $quiz->difficulty === 'easy' ? 'selected' : '' ?>>Facile (Easy)</option>
                            <option value="medium" <?= $quiz->difficulty === 'medium' ? 'selected' : '' ?>>Moyen (Medium)</option>
                            <option value="hard" <?= $quiz->difficulty === 'hard' ? 'selected' : '' ?>>Difficile (Hard)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="approved">Statut</label>
                        <select id="approved" name="approved" required>
                            <option value="1" <?= $quiz->approved == 1 ? 'selected' : '' ?>>Approuvé</option>
                            <option value="0" <?= $quiz->approved == 0 ? 'selected' : '' ?>>Non approuvé</option>
                        </select>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 30px;">
                    <button type="submit" class="btn-submit">
                        <i class="fa fa-save"></i> Mettre à jour
                    </button>
                    <a href="blank_list.php?quiz_id=<?= htmlspecialchars($quiz->qid) ?>" class="btn-cancel" style="background: #17a2b8; color: white;">
                        <i class="fa fa-list"></i> Voir les Blanks
                    </a>
                    <a href="quiz_list.php" class="btn-cancel">
                        <i class="fa fa-times"></i> Retour
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="../FrontOffice/assets/js/sweetalert2-helper.js"></script>
    <script>
        // Afficher les messages PHP avec SweetAlert2
        document.addEventListener('DOMContentLoaded', function() {
            const successMsg = document.getElementById('success-message');
            const errorMsg = document.getElementById('error-message');
            
            if (successMsg && successMsg.dataset.message) {
                showSuccess('Succès !', successMsg.dataset.message);
            }
            
            if (errorMsg && errorMsg.dataset.message) {
                showError('Erreur !', errorMsg.dataset.message);
            }
        });

        const editor = document.getElementById('paragraphEditor');
        const preview = document.getElementById('previewText');
        const blanks = <?= json_encode(array_map(function($b) {
            return ['position' => $b->position, 'correctAnswer' => $b->correctAnswer];
        }, $blanks)) ?>;
        
        // Mettre à jour l'aperçu en temps réel
        editor.addEventListener('input', updatePreview);
        
        function updatePreview() {
            let text = editor.value;
            // Remplacer [mot] par un highlight visuel
            text = text.replace(/\[([^\]]+)\]/g, '<span class="blank-highlight">[$1]</span>');
            preview.innerHTML = text || '<em style="color: #999;">Aperçu du texte...</em>';
        }
        
        function addBlank() {
            const cursorPos = editor.selectionStart;
            const textBefore = editor.value.substring(0, cursorPos);
            const textAfter = editor.value.substring(editor.selectionEnd);
            
            // Demander le mot correct pour le blank avec SweetAlert2
            showPrompt(
                'Ajouter un blank',
                'Entrez le mot correct pour ce blank :',
                'Ex: chat, voisin, joueur...',
                '',
                function(word) {
                    if (word && word.trim() !== '') {
                        const blankText = '[' + word.trim() + ']';
                        editor.value = textBefore + blankText + textAfter;
                        
                        // Repositionner le curseur après le blank
                        const newPos = cursorPos + blankText.length;
                        editor.setSelectionRange(newPos, newPos);
                        editor.focus();
                        
                        updatePreview();
                    }
                }
            );
        }
        
        function showBlanksInfo() {
            let info = '<ul style="text-align: left; margin: 10px 0;">';
            blanks.forEach((blank, index) => {
                info += `<li>Position ${blank.position + 1}: "<strong>${blank.correctAnswer}</strong>"</li>`;
            });
            info += '</ul>';
            
            Swal.fire({
                title: 'Blanks actuels dans ce quiz',
                html: info,
                icon: 'info',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3498db'
            });
        }
        
        // Initialiser l'aperçu
        updatePreview();
    </script>
</body>
</html>
