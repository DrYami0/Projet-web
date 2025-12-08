<?php
session_start();
require_once __DIR__ . '/../../Mail/QuizSuggestionEmailer.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate basic inputs
    $paragraph = $_POST['paragraph'] ?? '';
    $difficulty = $_POST['difficulty'] ?? 'easy';
    
    if (empty($paragraph)) {
        $error = 'Le texte du quiz est requis.';
    } else {
        // Check for blanks count
        preg_match_all('/\[([^\]]+)\]/', $paragraph, $matches);
        $nbBlanks = count($matches[1]);
        
        if ($nbBlanks < 3) {
            $error = 'Vous devez ajouter au moins 3 blanks (format: [mot]). Exemple: Le [chat] de mon [voisin] est très [joueur].';
        } elseif ($nbBlanks > 8) {
            $error = 'Vous ne pouvez pas ajouter plus de 8 blanks.';
        } else {
            // Send email instead of saving to database
            $emailer = new QuizSuggestionEmailer();
            $result = $emailer->sendSuggestionEmail($_POST);
            
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['message'];
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suggérer un quizz</title>
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }
        
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .header h1 {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #7f8c8d;
            font-size: 1.1rem;
        }
        
        .form-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        
        .note {
            background-color: #e8f4f8;
            border-left: 4px solid #3498db;
            padding: 15px;
            margin-bottom: 30px;
            color: #2c3e50;
            border-radius: 4px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .toolbar {
            margin-bottom: 10px;
            display: flex;
            justify-content: flex-end;
        }
        
        .btn-toolbar {
            background: none;
            border: none;
            color: #e74c3c;
            cursor: pointer;
            font-size: 0.9rem;
            padding: 5px 10px;
            border-radius: 4px;
            transition: all 0.2s;
        }
        
        .btn-toolbar:hover {
            background: #fcebe9;
        }
        
        .editor-container textarea {
            width: 100%;
            min-height: 150px;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
            resize: vertical;
        }
        
        .editor-container textarea:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .preview {
            margin-top: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px dashed #cbd5e0;
        }
        
        .preview-label {
            font-size: 0.85rem;
            text-transform: uppercase;
            color: #7f8c8d;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .blank-highlight {
            background: #d6eaf8;
            color: #2980b9;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 600;
        }
        
        select.form-control, input[type="text"].form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            height: auto;
        }
        
        select:focus, input[type="text"]:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .btn-submit {
            background: #27ae60;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-submit:hover {
            background: #219150;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.3);
        }
        
        .btn-cancel {
            background: #95a5a6;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-cancel:hover {
            background: #7f8c8d;
            color: white;
            transform: translateY(-2px);
        }
        
        #intrudersContainer {
            background: #fff3cd !important;
            border-color: #ffeeba !important;
            color: #856404;
        }
        
        #intrudersContainer input {
            border-color: #ffeeba;
        }
        
        #intrudersContainer input:focus {
            border-color: #ffc107;
            box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Suggérer un quizz</h1>
            <p>Proposez un nouveau quizz à la communauté</p>
        </div>
        
        <div class="form-container">
            <?php if ($error): ?>
                <div id="error-message" data-message="<?= htmlspecialchars($error) ?>" style="display: none;"></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div id="success-message" data-message="<?= htmlspecialchars($success) ?>" style="display: none;"></div>
            <?php endif; ?>
            
            <form method="post" action="" id="quizForm">
                <div class="note">
                    <i class="fa fa-info-circle"></i> Chaque blank que vous voulez créer doit être mis entre [ ].
                </div>

                <div class="form-group">
                    <label for="paragraphEditor">Texte du quizz</label>
                    <div class="toolbar">
                        <!-- "Ajouter un Blank" button removed as requested -->
                        <button type="button" class="btn-toolbar secondary" onclick="clearEditor()">
                            <i class="fa fa-trash"></i> Effacer
                        </button>
                    </div>
                    <div class="editor-container">
                        <textarea id="paragraphEditor" name="paragraph" placeholder="Écrivez votre texte ici. Exemple : Le [chat] mange la [souris]." required></textarea>
                    </div>
                    <div class="preview">
                        <div class="preview-label">Aperçu :</div>
                        <div id="previewText"></div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group" style="width: 100%;">
                        <label for="difficulty">Difficulté</label>
                        <select id="difficulty" name="difficulty" required>
                            <option value="easy">Facile (Easy)</option>
                            <option value="medium">Moyen (Medium)</option>
                            <option value="hard">Difficile (Hard)</option>
                        </select>
                    </div>
                    <!-- Status field removed as requested -->
                </div>
                
                <div class="form-group" style="margin-top: 20px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" id="hasIntruders" name="has_intruders" onchange="toggleIntruders()">
                        <label for="hasIntruders" style="margin-bottom: 0; cursor: pointer;">Ajouter des mots intrus</label>
                    </div>
                    
                    <div id="intrudersContainer" style="display: none; margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 5px; border: 1px solid #ddd;">
                        <label for="intruderWords">Mots intrus (séparés par des virgules)</label>
                        <input type="text" id="intruderWords" name="intruder_words" class="form-control" placeholder="Ex: chien, oiseau, table">
                        <small class="text-muted">Ces mots apparaîtront dans la liste mais ne seront pas dans le texte.</small>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 30px;">
                    <button type="submit" class="btn-submit">
                        <i class="fa fa-paper-plane"></i> Envoyer suggestion
                    </button>
                    <a href="index.html" class="btn-cancel">
                        <i class="fa fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/sweetalert2-helper.js"></script>
    <script>
        // Afficher les messages PHP avec SweetAlert2
        document.addEventListener('DOMContentLoaded', function() {
            const errorMsg = document.getElementById('error-message');
            const successMsg = document.getElementById('success-message');
            
            if (errorMsg && errorMsg.dataset.message) {
                showError('Erreur !', errorMsg.dataset.message);
            }
            
            if (successMsg && successMsg.dataset.message) {
                showSuccess('Succès !', successMsg.dataset.message, function() {
                    window.location.href = 'index.html'; // Redirect after success
                });
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
        
        function toggleIntruders() {
            const checkbox = document.getElementById('hasIntruders');
            const container = document.getElementById('intrudersContainer');
            const input = document.getElementById('intruderWords');
            
            if (checkbox.checked) {
                container.style.display = 'block';
                input.focus();
            } else {
                container.style.display = 'none';
                input.value = ''; // Clear input when unchecked
            }
        }
    </script>
</body>
</html>
