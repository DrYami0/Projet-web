<?php
session_start();
require_once __DIR__ . '/../../Mail/QuizSuggestionEmailer.php';

// Define BASE_URL relative to this file
const BASE_URL = '../../';

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/owl-mascot.css">
    <link rel="stylesheet" href="assets/css/theme.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Custom styles for suggest page */
        .form-container {
            background: rgba(255, 255, 255, 0.05);
            padding: 40px;
            border-radius: 16px;
            border: 1px solid rgba(0, 212, 255, 0.1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header h1 {
            color: #fff;
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 36px;
        }

        .header p {
            color: #b0c4de;
            font-size: 18px;
        }

        .note {
            background: rgba(0, 212, 255, 0.1);
            border-left: 4px solid #00d4ff;
            padding: 15px;
            margin-bottom: 30px;
            color: #e0e6ed;
            border-radius: 4px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #00d4ff;
            font-size: 16px;
        }

        .editor-container textarea {
            width: 100%;
            min-height: 150px;
            padding: 15px;
            background: rgba(10, 22, 40, 0.6);
            border: 2px solid rgba(0, 212, 255, 0.2);
            border-radius: 8px;
            font-size: 16px;
            color: #fff;
            transition: all 0.3s;
            resize: vertical;
        }

        .editor-container textarea:focus {
            outline: none;
            border-color: #00d4ff;
            box-shadow: 0 0 15px rgba(0, 212, 255, 0.1);
        }

        .preview {
            margin-top: 15px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            border: 1px dashed rgba(255, 255, 255, 0.2);
            color: #b0c4de;
        }

        .preview-label {
            font-size: 12px;
            text-transform: uppercase;
            color: #00d4ff;
            margin-bottom: 8px;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .blank-highlight {
            background: rgba(0, 212, 255, 0.2);
            color: #00d4ff;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 600;
            border: 1px solid rgba(0, 212, 255, 0.3);
        }

        /* Custom Select Styling */
        .custom-select-wrapper {
            position: relative;
            user-select: none;
        }

        select.form-control {
            width: 100%;
            padding: 15px;
            background: rgba(10, 22, 40, 0.6);
            border: 2px solid rgba(0, 212, 255, 0.2);
            border-radius: 8px;
            font-size: 16px;
            color: #fff;
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            transition: all 0.3s;
        }

        select.form-control:focus {
            outline: none;
            border-color: #00d4ff;
        }

        .select-arrow {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            color: #00d4ff;
            pointer-events: none;
        }

        /* Custom Checkbox Styling */
        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            padding: 10px;
            border-radius: 8px;
            transition: background 0.3s;
        }

        .checkbox-wrapper:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .custom-checkbox {
            width: 24px;
            height: 24px;
            border: 2px solid #00d4ff;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            background: rgba(10, 22, 40, 0.6);
        }

        input[type="checkbox"]:checked + .custom-checkbox {
            background: #00d4ff;
            box-shadow: 0 0 10px rgba(0, 212, 255, 0.4);
        }

        .custom-checkbox i {
            color: #0a1628;
            font-size: 14px;
            opacity: 0;
            transform: scale(0.5);
            transition: all 0.2s;
        }

        input[type="checkbox"]:checked + .custom-checkbox i {
            opacity: 1;
            transform: scale(1);
        }

        /* Intruders Container */
        #intrudersContainer {
            margin-top: 15px;
            padding: 20px;
            background: rgba(241, 196, 15, 0.1);
            border: 1px solid rgba(241, 196, 15, 0.3);
            border-radius: 8px;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        #intrudersContainer label {
            color: #f1c40f;
        }

        #intrudersContainer input {
            width: 100%;
            padding: 12px;
            background: rgba(10, 22, 40, 0.6);
            border: 2px solid rgba(241, 196, 15, 0.3);
            border-radius: 8px;
            color: #fff;
            font-size: 16px;
        }

        #intrudersContainer input:focus {
            outline: none;
            border-color: #f1c40f;
            box-shadow: 0 0 10px rgba(241, 196, 15, 0.2);
        }

        .btn-toolbar {
            background: transparent;
            border: 1px solid rgba(231, 76, 60, 0.5);
            color: #e74c3c;
            padding: 5px 15px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-toolbar:hover {
            background: #e74c3c;
            color: white;
        }

        .btn-submit {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(46, 204, 113, 0.4);
        }

        .btn-cancel {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        .toolbar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav>
        <div class="logo" onclick="window.location.href='index.php'">
            <img src="<?= BASE_URL ?>View/Perfran.png" alt="PerFran Logo" style="height: 60px; width: auto;">
        </div>
        <div class="nav-links">
            <a href="index.php#games">Jeux</a>
            <a href="index.php#features">Fonctionnalités</a>
        </div>
        <div class="nav-buttons">
            <a href="index.php" class="btn btn-outline">
                <i class="fas fa-home"></i> Accueil
            </a>
        </div>
    </nav>

    <div class="container" style="padding-top: 120px; padding-bottom: 60px;">
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
                        <button type="button" class="btn-toolbar" style="border-color: #9b59b6; color: #9b59b6;" onclick="generateWithAI()">
                            <i class="fas fa-magic"></i> Générer un quiz
                        </button>
                        <button type="button" class="btn-toolbar" onclick="clearEditor()">
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
                
                <div class="form-group">
                    <label for="difficulty">Difficulté</label>
                    <div class="custom-select-wrapper">
                        <select id="difficulty" name="difficulty" class="form-control" required>
                            <option value="easy">Facile (Easy)</option>
                            <option value="medium">Moyen (Medium)</option>
                            <option value="hard">Difficile (Hard)</option>
                        </select>
                        <i class="fas fa-chevron-down select-arrow"></i>
                    </div>
                </div>
                
                <div class="form-group" style="margin-top: 30px;">
                    <label class="checkbox-wrapper">
                        <input type="checkbox" id="hasIntruders" name="has_intruders" onchange="toggleIntruders()" style="display: none;">
                        <span class="custom-checkbox">
                            <i class="fas fa-check"></i>
                        </span>
                        <span style="color: #fff; font-size: 16px;">Ajouter des mots intrus</span>
                    </label>
                    
                    <div id="intrudersContainer" style="display: none;">
                        <label for="intruderWords"><i class="fas fa-exclamation-triangle"></i> Mots intrus</label>
                        <input type="text" id="intruderWords" name="intruder_words" placeholder="Ex: chien, oiseau, table (séparés par des virgules)">
                        <small style="color: rgba(241, 196, 15, 0.8); display: block; margin-top: 8px;">Ces mots apparaîtront dans la liste mais ne seront pas dans le texte.</small>
                    </div>
                </div>
                
                <div style="display: flex; gap: 15px; margin-top: 40px;">
                    <button type="submit" class="btn-submit">
                        <i class="fa fa-paper-plane"></i> Envoyer suggestion
                    </button>
                    <a href="index.php" class="btn-cancel">
                        <i class="fa fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>© 2025 PerfRan - Tous droits réservés | <a href="login.php">Connexion</a></p>
    </footer>

    <!-- Interactive Owl Mascot -->
    <script src="assets/js/owl-mascot.js"></script>

    <script src="assets/js/sweetalert2-helper.js"></script>
    <script>
        // Afficher les messages PHP avec SweetAlert2
        document.addEventListener('DOMContentLoaded', function() {
            const errorMsg = document.getElementById('error-message');
            const successMsg = document.getElementById('success-message');
            
            if (errorMsg && errorMsg.dataset.message) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: errorMsg.dataset.message,
                    confirmButtonColor: '#e74c3c',
                    background: '#1a2f4a',
                    color: '#fff'
                });
            }
            
            if (successMsg && successMsg.dataset.message) {
                Swal.fire({
                    icon: 'success',
                    title: 'Succès !',
                    text: successMsg.dataset.message,
                    confirmButtonColor: '#2ecc71',
                    background: '#1a2f4a',
                    color: '#fff'
                }).then(() => {
                    window.location.href = 'index.php';
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
            preview.innerHTML = text || '<em style="color: rgba(255,255,255,0.3);">Aperçu du texte...</em>';
        }
        
        function clearEditor() {
            Swal.fire({
                title: 'Effacer le texte',
                text: 'Êtes-vous sûr de vouloir effacer tout le texte ?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#3498db',
                confirmButtonText: 'Oui, effacer',
                cancelButtonText: 'Annuler',
                background: '#1a2f4a',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    editor.value = '';
                    updatePreview();
                }
            });
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

        function generateWithAI() {
            Swal.fire({
                title: 'Générer un quiz avec l\'IA',
                width: '600px',
                html:
                    '<input id="swal-input1" class="swal2-input" placeholder="Thème (ex: La plage, Le restaurant...)" style="color: #333; background: #fff;">' +
                    '<input id="swal-input2" class="swal2-input" type="number" min="3" max="8" placeholder="Nombre de blanks (min 3)" style="color: #333; background: #fff;">',
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Générer',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#9b59b6',
                background: '#1a2f4a',
                color: '#fff',
                preConfirm: () => {
                    const theme = document.getElementById('swal-input1').value;
                    const nbBlanks = document.getElementById('swal-input2').value;
                    
                    if (!theme) {
                        Swal.showValidationMessage('Le thème est requis');
                        return false;
                    }
                    if (!nbBlanks || nbBlanks < 3) {
                        Swal.showValidationMessage('Le nombre de blanks doit être d\'au moins 3');
                        return false;
                    }
                    
                    return { theme: theme, nbBlanks: nbBlanks };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const { theme, nbBlanks } = result.value;
                    
                    // Show loading
                    Swal.fire({
                        title: 'Génération en cours...',
                        text: 'L\'IA rédige votre quiz...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                        background: '#1a2f4a',
                        color: '#fff'
                    });
                    
                    // Call API
                    fetch('<?= BASE_URL ?>Controller/generate_quiz.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ theme: theme, nbBlanks: nbBlanks })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            editor.value = data.paragraph;
                            updatePreview();
                            
                            // Update difficulty
                            const difficultySelect = document.getElementById('difficulty');
                            difficultySelect.value = data.difficulty;
                            
                            Swal.fire({
                                icon: 'success',
                                title: 'Quiz généré !',
                                text: 'Le texte a été inséré avec succès.',
                                confirmButtonColor: '#2ecc71',
                                background: '#1a2f4a',
                                color: '#fff'
                            });
                        } else {
                            throw new Error(data.message || 'Erreur inconnue');
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: error.message,
                            confirmButtonColor: '#e74c3c',
                            background: '#1a2f4a',
                            color: '#fff'
                        });
                    });
                }
            });
        }
    </script>
</body>
</html>
