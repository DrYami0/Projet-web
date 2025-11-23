<?php
require_once __DIR__ . '/../../Model/Quiz.php';
require_once __DIR__ . '/../../Model/QuizBlank.php';

$error = '';
$success = '';

$id = $_GET['id'] ?? 0;
$quiz = Quiz::getById((int)$id);

if (!$quiz) {
    header('HTTP/1.0 404 Not Found');
    die('Quiz non trouvé');
}

// Récupérer les blanks pour affichage dans l'info
$blanks = QuizBlank::getByQuizId($quiz->qid);
usort($blanks, function($a, $b) {
    return $a->position <=> $b->position;
});

// Le paragraphe est déjà au format [mot] dans la base de données
$paragraph = $quiz->paragraph;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paragraph = $_POST['paragraph'] ?? '';
    $difficulty = $_POST['difficulty'] ?? 'easy';
    $approved = isset($_POST['approved']) ? (int)$_POST['approved'] : 0;
    
    // Extraire les blanks du paragraphe (format: [mot])
    $blanksArray = [];
    preg_match_all('/\[([^\]]+)\]/', $paragraph, $matches);
    if (!empty($matches[1])) {
        $blanksArray = array_map('trim', $matches[1]);
    }
    $nbBlanks = count($blanksArray);
    
    // Valider que le nombre de blanks est entre 3 et 8 (contrainte de la base)
    if ($nbBlanks < 3) {
        $error = 'Erreur : Vous devez avoir au moins 3 blanks (format: [mot]). Exemple: Le [chat] de mon [voisin] est très [joueur].';
    } elseif ($nbBlanks > 8) {
        $error = 'Erreur : Vous ne pouvez pas avoir plus de 8 blanks';
    } else {
        // Mettre à jour le quiz
        $quiz->paragraph = $paragraph;
        $quiz->nbBlanks = $nbBlanks;
        $quiz->difficulty = $difficulty;
        $quiz->approved = $approved;
        
        if ($quiz->save()) {
            // Supprimer les anciens blanks
            QuizBlank::deleteByQuizId($quiz->qid);
            
            // Ajouter les nouveaux blanks (y compris les nouveaux ajoutés)
            foreach ($blanksArray as $index => $answer) {
                $blank = new QuizBlank(0, $quiz->qid, $index, $answer);
                $blank->save();
            }
            
            // Mise à jour réussie - rester sur la page
            $success = 'Quiz mis à jour avec succès !';
            $error = ''; // Pas d'erreur
            // Recharger le quiz pour afficher les données mises à jour
            $quiz = Quiz::getById($quiz->qid);
            $blanks = QuizBlank::getByQuizId($quiz->qid);
            usort($blanks, function($a, $b) {
                return $a->position <=> $b->position;
            });
            $paragraph = $quiz->paragraph; // Mettre à jour le paragraphe affiché
        } else {
            $error = 'Erreur lors de la mise à jour du quiz';
        }
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
        .info-box {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Éditer un quizz</h1>
        </div>
        
        <div class="form-container">
            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <?= htmlspecialchars($success) ?>
                </div>
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

    <script>
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
            
            // Demander le mot correct pour le blank
            const word = prompt('Entrez le mot correct pour ce blank :');
            if (word === null) return; // Annulé
            
            const blankText = '[' + word + ']';
            editor.value = textBefore + blankText + textAfter;
            
            // Repositionner le curseur après le blank
            const newPos = cursorPos + blankText.length;
            editor.setSelectionRange(newPos, newPos);
            editor.focus();
            
            updatePreview();
        }
        
        function showBlanksInfo() {
            let info = 'Blanks actuels dans ce quiz :\n\n';
            blanks.forEach((blank, index) => {
                info += `${index + 1}. Position ${blank.position}: "${blank.correctAnswer}"\n`;
            });
            alert(info);
        }
        
        // Initialiser l'aperçu
        updatePreview();
    </script>
</body>
</html>
