<?php
session_start();
require_once __DIR__ . '/../../Model/Quiz.php';
require_once __DIR__ . '/../../Model/QuizBlank.php';
require_once __DIR__ . '/../../Controller/QuizController.php';
require_once __DIR__ . '/../../Controller/QuizBlankController.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   
    $error = QuizController::add($_POST);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un quizz</title>
    <link rel="stylesheet" href="../FrontOffice/assets/css/bootstrap.css">
    <link rel="stylesheet" href="../FrontOffice/assets/css/style.css">
    <link rel="stylesheet" href="../FrontOffice/assets/css/backoffice.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Ajouter un quizz</h1>
            <p>Créer un nouveau quizz avec des blanks interactifs</p>
        </div>
        
        <div class="form-container">
            <?php if ($error): ?>
                <div id="error-message" data-message="<?= htmlspecialchars($error) ?>" style="display: none;"></div>
            <?php endif; ?>
            
            <form method="post" action="" id="quizForm">
                <div class="form-group">
                    <label for="paragraphEditor">Texte du quizz</label>
                    <div class="toolbar">
                        <button type="button" class="btn-toolbar" onclick="addBlank()">
                            <i class="fa fa-plus"></i> Ajouter un Blank
                        </button>
                        <button type="button" class="btn-toolbar secondary" onclick="clearEditor()">
                            <i class="fa fa-trash"></i> Effacer
                        </button>
                    </div>
                    <div class="editor-container">
                        <textarea id="paragraphEditor" name="paragraph" placeholder="Écrivez votre texte ici. Cliquez sur 'Ajouter un Blank' pour insérer un espace à compléter."></textarea>
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
                            <option value="easy">Facile (Easy)</option>
                            <option value="medium">Moyen (Medium)</option>
                            <option value="hard">Difficile (Hard)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="approved">Statut</label>
                        <select id="approved" name="approved" required>
                            <option value="1">Approuvé</option>
                            <option value="0">Non approuvé</option>
                        </select>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 30px;">
                    <button type="submit" class="btn-submit">
                        <i class="fa fa-save"></i> Enregistrer
                    </button>
                    <a href="quiz_list.php" class="btn-cancel">
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

        const editor = document.getElementById('paragraphEditor');
        const preview = document.getElementById('previewText');
        
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
        
        function clearEditor() {
            showConfirm(
                'Effacer le texte',
                'Êtes-vous sûr de vouloir effacer tout le texte ?',
                'Oui, effacer',
                'Annuler',
                function(confirmed) {
                    if (confirmed) {
                        editor.value = '';
                        updatePreview();
                    }
                }
            );
        }
        
        // Initialiser l'aperçu
        updatePreview();
    </script>
</body>
</html>
