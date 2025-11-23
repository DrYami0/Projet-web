<?php
require_once __DIR__ . '/../../Model/Quiz.php';
require_once __DIR__ . '/../../Model/QuizBlank.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paragraph = $_POST['paragraph'] ?? '';
    $difficulty = $_POST['difficulty'] ?? 'easy';
    $approved = isset($_POST['approved']) ? (int)$_POST['approved'] : 0;
    
    // Extraire les blanks du paragraphe (format: [mot])
    $blanks = [];
    preg_match_all('/\[([^\]]+)\]/', $paragraph, $matches);
    if (!empty($matches[1])) {
        $blanks = $matches[1];
    }
    $nbBlanks = count($blanks);
    
    // Valider que le nombre de blanks est entre 3 et 8 (contrainte de la base)
    if ($nbBlanks < 3) {
        $error = 'Erreur : Vous devez ajouter au moins 3 blanks (format: [mot]). Exemple: Le [chat] de mon [voisin] est très [joueur].';
    } elseif ($nbBlanks > 8) {
        $error = 'Erreur : Vous ne pouvez pas ajouter plus de 8 blanks';
    } else {
        // Créer le quiz
        $quiz = new Quiz(0, $paragraph, $nbBlanks, $difficulty, $approved);
        
        if ($quiz->save()) {
            // Ajouter les blanks
            foreach ($blanks as $index => $answer) {
                $blank = new QuizBlank(0, $quiz->qid, $index, $answer);
                $blank->save();
            }
            
            header('Location: quiz_list.php');
            exit;
        } else {
            $error = 'Erreur lors de la création du quiz. Vérifiez que tous les champs sont corrects.';
        }
    }
}
?>
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un quizz</title>
    <link rel="stylesheet" href="../FrontOffice/assets/css/bootstrap.css">
    <link rel="stylesheet" href="../FrontOffice/assets/css/style.css">
    <style>
        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
            margin-bottom: 0;
        }
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        .editor-container {
            position: relative;
            margin-bottom: 20px;
        }
        #paragraphEditor {
            width: 100%;
            min-height: 200px;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            line-height: 1.6;
            font-family: inherit;
        }
        #paragraphEditor:focus {
            outline: none;
            border-color: #3498db;
        }
        .blank-highlight {
            background: #fff3cd;
            padding: 2px 5px;
            border-radius: 3px;
            font-weight: bold;
            color: #856404;
        }
        .toolbar {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        .btn-toolbar {
            padding: 8px 15px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-toolbar:hover {
            background: #2980b9;
        }
        .btn-toolbar.secondary {
            background: #6c757d;
        }
        .btn-toolbar.secondary:hover {
            background: #5a6268;
        }
        .preview {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
            min-height: 100px;
            border: 1px solid #dee2e6;
        }
        .preview-label {
            font-weight: bold;
            margin-bottom: 10px;
            color: #495057;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #495057;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .form-row {
            display: flex;
            gap: 20px;
        }
        .form-row .form-group {
            flex: 1;
        }
        .btn-submit {
            padding: 12px 30px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }
        .btn-submit:hover {
            background: #229954;
        }
        .btn-cancel {
            padding: 12px 30px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-cancel:hover {
            background: #5a6268;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Ajouter un quizz</h1>
            <p>Créer un nouveau quizz avec des blanks interactifs</p>
        </div>
        
        <div class="form-container">
            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
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

    <script>
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
            
            // Demander le mot correct pour le blank
            const word = prompt('Entrez le mot correct pour ce blank :');
            if (word === null || word.trim() === '') return; // Annulé ou vide
            
            const blankText = '[' + word.trim() + ']';
            editor.value = textBefore + blankText + textAfter;
            
            // Repositionner le curseur après le blank
            const newPos = cursorPos + blankText.length;
            editor.setSelectionRange(newPos, newPos);
            editor.focus();
            
            updatePreview();
        }
        
        function clearEditor() {
            if (confirm('Êtes-vous sûr de vouloir effacer tout le texte ?')) {
                editor.value = '';
                updatePreview();
            }
        }
        
        // Initialiser l'aperçu
        updatePreview();
    </script>
</body>
</html>
