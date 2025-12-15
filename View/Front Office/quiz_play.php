<?php
require_once __DIR__ . '/../../Model/Quiz.php';
require_once __DIR__ . '/../../Model/QuizBlank.php';
require_once __DIR__ . '/../../Controller/GameController.php';
require_once __DIR__ . '/../../Controller/QuizBlankController.php';

$difficulty = $_GET['difficulty'] ?? 'easy';

// Valider la difficulté
if (!in_array($difficulty, ['easy', 'medium', 'hard'])) {
    $difficulty = 'easy';
}

// Récupérer un quiz aléatoire
$gameController = new GameController();
$quiz = $gameController->getRandomQuiz($difficulty);

if (!$quiz) {
    die('Aucun quiz disponible pour cette difficulté. Veuillez créer des quiz dans le BackOffice.');
}

// Récupérer les blanks et filtrer les intrus
$all_blanks = $quiz->blanks;
$blanks = array_filter($all_blanks, function($b) {
    return $b->position > 0;
});

// Trier les blanks par position
usort($blanks, function($a, $b) {
    return $a->position <=> $b->position;
});

// Extraire les variables pour la vue
$qid = $quiz->qid;
$paragraph = $quiz->paragraph;
$difficulty = $quiz->difficulty;

// Créer la liste de mots à partir de TOUS les blanks (y compris intrus)
$correctAnswers = [];
foreach ($all_blanks as $blank) {
    $correctAnswers[] = $blank->correctAnswer;
}

$allWords = array_unique($correctAnswers);
shuffle($allWords);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jeu 3 - Quiz Drag & Drop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/owl-mascot.css">
    <link rel="stylesheet" href="assets/css/theme.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .quiz-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(0, 212, 255, 0.3);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .quiz-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .quiz-header h2 {
            color: #fff;
            margin-bottom: 15px;
            font-size: 32px;
        }

        .difficulty-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .difficulty-badge.easy {
            background: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
            border: 1px solid #2ecc71;
        }

        .difficulty-badge.medium {
            background: rgba(241, 196, 15, 0.2);
            color: #f1c40f;
            border: 1px solid #f1c40f;
        }

        .difficulty-badge.hard {
            background: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
            border: 1px solid #e74c3c;
        }

        .quiz-paragraph {
            background: rgba(10, 22, 40, 0.6);
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            font-size: 18px;
            line-height: 2;
            min-height: 150px;
            color: #e0e6ed;
            border: 1px solid rgba(0, 212, 255, 0.1);
        }

        .blank {
            display: inline-block;
            min-width: 120px;
            height: 40px;
            border: 2px dashed #00d4ff;
            border-radius: 8px;
            margin: 0 5px;
            vertical-align: middle;
            background: rgba(0, 212, 255, 0.1);
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #fff;
            text-align: center;
            line-height: 36px;
        }

        .blank.drag-over {
            border-color: #2ecc71;
            background: rgba(46, 204, 113, 0.2);
            transform: scale(1.05);
        }

        .blank.filled {
            border: 2px solid #00d4ff;
            background: rgba(0, 212, 255, 0.2);
            color: #fff;
            font-weight: 600;
        }

        .blank-word {
            display: inline-block;
            padding: 10px 20px;
            margin: 8px;
            background: linear-gradient(135deg, #00d4ff 0%, #0099cc 100%);
            color: white;
            border-radius: 8px;
            cursor: move;
            user-select: none;
            transition: all 0.3s ease;
            font-weight: 500;
            box-shadow: 0 4px 10px rgba(0, 212, 255, 0.2);
        }

        .blank-word:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 212, 255, 0.3);
        }

        .blank-word.dragging {
            opacity: 0.5;
            transform: scale(0.95);
        }

        .words-container {
            background: rgba(10, 22, 40, 0.6);
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            min-height: 100px;
            border: 1px solid rgba(0, 212, 255, 0.1);
        }

        .words-container h4 {
            margin-bottom: 20px;
            color: #b0c4de;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .words-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }

        .btn-validate {
            width: 100%;
            padding: 18px;
            font-size: 18px;
            font-weight: bold;
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.3);
        }

        .btn-validate:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(46, 204, 113, 0.4);
        }

        .btn-validate:disabled {
            background: #95a5a6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .results {
            display: none;
            margin-top: 30px;
            padding: 30px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .results h3 {
            color: #fff;
            text-align: center;
            margin-bottom: 20px;
        }

        .score {
            text-align: center;
            font-size: 56px;
            font-weight: 800;
            margin: 20px 0;
            text-shadow: 0 0 20px rgba(255, 255, 255, 0.1);
        }

        .score.excellent { color: #2ecc71; }
        .score.good { color: #f1c40f; }
        .score.poor { color: #e74c3c; }

        .result-item {
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            font-size: 15px;
        }

        .result-item.correct {
            background: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
            border: 1px solid rgba(46, 204, 113, 0.3);
        }

        .result-item.incorrect {
            background: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
            border: 1px solid rgba(231, 76, 60, 0.3);
        }

        .btn-retry {
            margin-top: 20px;
            padding: 12px 30px;
            background: rgba(0, 212, 255, 0.1);
            color: #00d4ff;
            border: 2px solid #00d4ff;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-retry:hover {
            background: #00d4ff;
            color: #0a1628;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav>
        <div class="logo" onclick="window.location.href='index.php'">
            <img src="../../View/Perfran.png" alt="PerFran Logo" style="height: 60px; width: auto;">
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

    <!-- Main Content -->
    <div class="container" style="padding-top: 120px; padding-bottom: 60px;">
        <div class="quiz-container">
            <div class="quiz-header">
                <h2>Jeu 3 - Quiz Drag & Drop</h2>
                <span class="difficulty-badge <?= htmlspecialchars($difficulty) ?>">
                    <?= htmlspecialchars(ucfirst($difficulty)) ?>
                </span>
            </div>

            <div class="quiz-paragraph" id="quizParagraph">
                <?php
                $blankIndex = 0;
                $displayParagraph = preg_replace_callback(
                    '/\[([^\]]+)\]/',
                    function($match) use (&$blankIndex) {
                        $blankIndex++;
                        return '<span class="blank" data-position="' . ($blankIndex - 1) . '" data-correct="' . htmlspecialchars($match[1]) . '"></span>';
                    },
                    $paragraph
                );
                echo $displayParagraph;
                ?>
            </div>

            <div class="words-container">
                <h4>Glissez les mots dans les espaces vides :</h4>
                <div class="words-list" id="wordsList">
                    <?php foreach ($allWords as $word): ?>
                        <span class="blank-word" draggable="true" data-word="<?= htmlspecialchars($word) ?>">
                            <?= htmlspecialchars($word) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Voice Mode Controls -->
            <div style="text-align: right; margin-bottom: 20px;">
                <button id="voiceModeBtn" style="padding: 10px 20px; background: rgba(102, 126, 234, 0.2); color: #667eea; border: 2px solid #667eea; border-radius: 20px; font-size: 14px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s;">
                    <span style="font-size: 18px;">🎤</span>
                    <span id="voiceBtnText">Mode Vocal</span>
                </button>
            </div>

            <!-- Voice Listening Indicator -->
            <div id="voiceIndicator" style="display: none; background: rgba(102, 126, 234, 0.1); border: 1px solid #667eea; border-radius: 12px; padding: 15px; margin-bottom: 20px; text-align: center;">
                <div style="display: flex; align-items: center; justify-content: center; gap: 15px;">
                    <div style="font-size: 32px; animation: pulse 2s infinite;">🎤</div>
                    <div style="flex: 1; text-align: left;">
                        <p style="font-size: 14px; color: #667eea; font-weight: 600; margin: 0 0 5px 0;">Écoute active...</p>
                        <div style="background: rgba(255, 255, 255, 0.1); border-radius: 6px; padding: 8px 12px;">
                            <p id="voiceTranscript" style="font-size: 16px; color: #fff; margin: 0; font-weight: 600; min-height: 20px;"></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <style>
                @keyframes pulse {
                    0%, 100% { transform: scale(1); opacity: 1; }
                    50% { transform: scale(1.15); opacity: 0.7; }
                }
            </style>

            <button class="btn-validate" id="btnValidate" onclick="validateAnswers()">
                Valider les réponses
            </button>

            <div class="results" id="results">
                <h3>Résultats</h3>
                <div class="score" id="scoreDisplay"></div>
                <div id="resultsDetails"></div>
                <div style="text-align: center;">
                    <a href="quiz_play.php?difficulty=<?= htmlspecialchars($difficulty) ?>" class="btn-retry">
                        <i class="fas fa-redo"></i> Nouveau Quiz
                    </a>
                    <a href="jeu3_solo.html" class="btn-retry" style="margin-left: 10px;">
                        <i class="fas fa-layer-group"></i> Changer de difficulté
                    </a>
                    <button type="button" class="btn-retry" style="margin-left: 10px; border-color: #f1c40f; color: #f1c40f;" onclick="window.location.href='suggest.php'">
                        <i class="fas fa-lightbulb"></i> Suggérer un quiz
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>© 2025 PerfRan - Tous droits réservés | <a href="login.php">Connexion</a></p>
    </footer>

    <!-- Interactive Owl Mascot -->
    <script src="assets/js/owl-mascot.js"></script>

    <script src="assets/js/jquery-3.3.1.min.js"></script>
    <script src="assets/js/popper.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/sweetalert2-helper.js"></script>
    <script>
        // Variables globales
        const quizId = <?= $qid ?>;
        const blanks = <?= json_encode(array_map(function($b) {
            return ['position' => $b->position, 'correctAnswer' => $b->correctAnswer];
        }, array_values($blanks))) ?>;
        
        let userAnswers = {};
        let isSubmitted = false;

        // Initialiser le drag & drop
        document.addEventListener('DOMContentLoaded', function() {
            initDragAndDrop();
        });

        function initDragAndDrop() {
            const blankWords = document.querySelectorAll('.blank-word');
            const blanks = document.querySelectorAll('.blank');

            // Événements pour les mots à glisser
            blankWords.forEach(word => {
                word.addEventListener('dragstart', handleDragStart);
                word.addEventListener('dragend', handleDragEnd);
            });

            // Événements pour les zones de drop
            blanks.forEach(blank => {
                blank.addEventListener('dragover', handleDragOver);
                blank.addEventListener('drop', handleDrop);
                blank.addEventListener('dragleave', handleDragLeave);
                blank.addEventListener('click', handleBlankClick);
            });
        }

        function handleDragStart(e) {
            this.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', this.dataset.word);
        }

        function handleDragEnd(e) {
            this.classList.remove('dragging');
        }

        function handleDragOver(e) {
            if (e.preventDefault) {
                e.preventDefault();
            }
            e.dataTransfer.dropEffect = 'move';
            this.classList.add('drag-over');
            return false;
        }

        function handleDragLeave(e) {
            this.classList.remove('drag-over');
        }

        function handleDrop(e) {
            if (e.stopPropagation) {
                e.stopPropagation();
            }

            this.classList.remove('drag-over');
            
            const word = e.dataTransfer.getData('text/plain');
            const position = parseInt(this.dataset.position);
            
            // Vérifier si le blank est déjà rempli
            if (this.textContent.trim() !== '') {
                // Remettre le mot précédent dans la liste
                const previousWord = this.textContent.trim();
                addWordToList(previousWord);
            }
            
            // Remplir le blank
            this.textContent = word;
            this.classList.add('filled');
            userAnswers[position] = word;
            
            // Retirer le mot de la liste
            removeWordFromList(word);
            
            return false;
        }

        function handleBlankClick(e) {
            if (this.textContent.trim() !== '') {
                const word = this.textContent.trim();
                this.textContent = '';
                this.classList.remove('filled');
                
                const position = parseInt(this.dataset.position);
                delete userAnswers[position];
                
                addWordToList(word);
            }
        }

        function removeWordFromList(word) {
            const words = document.querySelectorAll('.blank-word');
            words.forEach(w => {
                if (w.dataset.word === word && !w.classList.contains('used')) {
                    w.style.display = 'none';
                    w.classList.add('used');
                    return;
                }
            });
        }

        function addWordToList(word) {
            const words = document.querySelectorAll('.blank-word');
            words.forEach(w => {
                if (w.dataset.word === word && w.classList.contains('used')) {
                    w.style.display = 'inline-block';
                    w.classList.remove('used');
                    return;
                }
            });
        }

        function validateAnswers() {
            if (isSubmitted) return;
            
            // Vérifier que tous les blanks sont remplis
            const blanks = document.querySelectorAll('.blank');
            let allFilled = true;
            let emptyCount = 0;
            
            blanks.forEach(blank => {
                const hasText = blank.textContent && blank.textContent.trim() !== '';
                const hasFilled = blank.classList.contains('filled');
                
                if (!hasText && !hasFilled) {
                    allFilled = false;
                    emptyCount++;
                    console.log('Empty blank found:', blank);
                }
            });
            
            if (!allFilled) {
                console.log(`${emptyCount} blank(s) still empty`);
                Swal.fire({
                    icon: 'warning',
                    title: 'Action requise',
                    text: `Veuillez remplir tous les espaces vides avant de valider. (${emptyCount} restant(s))`,
                    confirmButtonColor: '#3498db'
                });
                return;
            }
            
            // Préparer les réponses
            const answers = [];
            blanks.forEach(blank => {
                const position = parseInt(blank.dataset.position);
                answers[position] = blank.textContent.trim();
            });
            
            // Envoyer les réponses au serveur
            const formData = new FormData();
            formData.append('qid', quizId);
            formData.append('answers', JSON.stringify(answers));
            
            fetch('quiz_validate.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayResults(data);
                    isSubmitted = true;
                    document.getElementById('btnValidate').disabled = true;
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: data.message || 'Une erreur est survenue lors de la validation.',
                        confirmButtonColor: '#e74c3c'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Une erreur est survenue lors de la validation.',
                    confirmButtonColor: '#e74c3c'
                });
            });
        }

        function displayResults(data) {
            const resultsDiv = document.getElementById('results');
            const scoreDisplay = document.getElementById('scoreDisplay');
            const resultsDetails = document.getElementById('resultsDetails');
            
            // Afficher le score
            let scoreClass = 'poor';
            let icon = 'error';
            if (data.score >= 80) {
                scoreClass = 'excellent';
                icon = 'success';
            } else if (data.score >= 50) {
                scoreClass = 'good';
                icon = 'warning';
            }
            
            scoreDisplay.textContent = data.score + '%';
            scoreDisplay.className = 'score ' + scoreClass;
            
            // Préparer les détails
            let detailsHTML = '<p style="text-align:center; color:#b0c4de; margin-bottom:20px;"><strong>Résultats: ' + data.correctCount + '/' + data.totalBlanks + ' correctes</strong></p>';
            data.results.forEach((result, index) => {
                if (result.isCorrect) {
                    detailsHTML += `<div class="result-item correct">
                        <i class="fas fa-check-circle"></i> Position ${result.position}: "${result.userAnswer}" - Correct
                    </div>`;
                } else {
                    detailsHTML += `<div class="result-item incorrect">
                        <i class="fas fa-times-circle"></i> Position ${result.position}: "${result.userAnswer}" - Incorrect (Réponse: "${result.correctAnswer}")
                    </div>`;
                }
            });
            
            resultsDetails.innerHTML = detailsHTML;
            resultsDiv.style.display = 'block';
            resultsDiv.scrollIntoView({ behavior: 'smooth' });
            
            // Afficher aussi SweetAlert2
            Swal.fire({
                icon: icon,
                title: 'Résultats du Quiz',
                html: '<div style="font-size: 48px; font-weight: bold; margin: 20px 0; color: ' + (data.score >= 80 ? '#2ecc71' : data.score >= 50 ? '#f1c40f' : '#e74c3c') + ';">' + data.score + '%</div>',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3498db',
                width: '600px',
                background: '#1a2f4a',
                color: '#fff'
            });
        }

        // ==================== VOICE RECOGNITION SYSTEM ====================
        let voiceModeActive = false;
        let recognition = null;

        // Initialize speech recognition
        if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            recognition = new SpeechRecognition();
            recognition.lang = 'fr-FR';
            recognition.continuous = true;
            recognition.interimResults = true;

            recognition.onresult = function(event) {
                for (let i = event.resultIndex; i < event.results.length; i++) {
                    const transcript = event.results[i][0].transcript;
                    const isFinal = event.results[i].isFinal;

                    // Show transcript in real-time
                    const transcriptEl = document.getElementById('voiceTranscript');
                    
                    if (isFinal) {
                        // Final result - process it
                        transcriptEl.innerHTML = '<span style="color: #2ecc71;">✓ ' + transcript + '</span>';
                        console.log('Voice recognized:', transcript);
                        processVoiceCommand(transcript.toLowerCase().trim());
                        
                        // Clear after 2 seconds
                        setTimeout(() => {
                            transcriptEl.innerHTML = '<span style="color: rgba(255,255,255,0.5);">Parlez...</span>';
                        }, 2000);
                    } else {
                        // Interim result - show in blue
                        transcriptEl.innerHTML = '<span style="color: #00d4ff;">' + transcript + '</span>';
                    }
                }
            };

            recognition.onerror = function(event) {
                console.error('Speech recognition error:', event.error);
                if (event.error === 'no-speech') {
                    return; // Continue listening
                }
                Swal.fire('Erreur vocale', 'Erreur: ' + event.error, 'error');
            };

            recognition.onend = function() {
                if (voiceModeActive) {
                    // Restart if still active
                    setTimeout(() => {
                        try {
                            recognition.start();
                        } catch(e) {
                            console.log('Recognition restart:', e);
                        }
                    }, 500);
                }
            };
        }

        // Toggle voice mode
        document.getElementById('voiceModeBtn').addEventListener('click', async function() {
            if (!recognition) {
                Swal.fire('Non supporté', 'Votre navigateur ne supporte pas la reconnaissance vocale. Utilisez Chrome ou Edge.', 'warning');
                return;
            }

            voiceModeActive = !voiceModeActive;
            const indicator = document.getElementById('voiceIndicator');
            const btnText = document.getElementById('voiceBtnText');

            if (voiceModeActive) {
                // Request microphone permission
                try {
                    await navigator.mediaDevices.getUserMedia({ audio: true });
                } catch (err) {
                    console.error('Microphone permission denied:', err);
                    Swal.fire('Permission refusée', 'Veuillez autoriser l\'accès au microphone.', 'error');
                    voiceModeActive = false;
                    return;
                }

                // Start recognition
                try {
                    recognition.start();
                    this.style.borderColor = '#e74c3c';
                    this.style.color = '#e74c3c';
                    this.style.background = 'rgba(231, 76, 60, 0.2)';
                    btnText.textContent = '🔴 Actif';
                    indicator.style.display = 'block';
                    console.log('Voice mode activated');
                } catch(e) {
                    console.error('Start error:', e);
                    voiceModeActive = false;
                }
            } else {
                // Stop recognition
                recognition.stop();
                this.style.borderColor = '#667eea';
                this.style.color = '#667eea';
                this.style.background = 'rgba(102, 126, 234, 0.2)';
                btnText.textContent = 'Mode Vocal';
                indicator.style.display = 'none';
                console.log('Voice mode deactivated');
            }
        });

        /**
         * Process voice command and fill ALL blanks from complete sentence
         */
        function processVoiceCommand(transcript) {
            console.log('🎤 Processing complete sentence:', transcript);
            
            const blankElements = document.querySelectorAll('.blank');
            const availableWords = document.querySelectorAll('.blank-word:not(.used)');

            // Get expected words in order from the paragraph (data-correct attributes)
            const expectedWords = Array.from(blankElements).map(blank => ({
                element: blank,
                position: parseInt(blank.dataset.position),
                expectedWord: blank.dataset.correct.toLowerCase(),
                expectedNormalized: normalizeText(blank.dataset.correct)
            }));

            // Split transcript into words
            const spokenWords = transcript.toLowerCase().split(/\s+/);
            
            // Create a map of available words for quick lookup
            const availableWordsMap = new Map();
            availableWords.forEach(wordEl => {
                const word = wordEl.dataset.word.toLowerCase();
                const normalized = normalizeText(wordEl.dataset.word);
                availableWordsMap.set(normalized, {
                    element: wordEl,
                    word: word,
                    normalized: normalized
                });
            });

            // Match each expected blank with spoken words
            const matches = [];
            
            expectedWords.forEach((expected, index) => {
                // Skip if already filled
                if (expected.element.textContent.trim()) {
                    matches.push({ blank: expected, word: null });
                    return;
                }

                // Try to find this expected word in the spoken sentence
                let foundWord = null;

                for (const spokenWord of spokenWords) {
                    const spokenNormalized = normalizeText(spokenWord);

                    // Check if spoken word matches expected word
                    if (spokenNormalized === expected.expectedNormalized ||
                        expected.expectedNormalized.includes(spokenNormalized) ||
                        spokenNormalized.includes(expected.expectedNormalized) ||
                        spokenWord === expected.expectedWord ||
                        expected.expectedWord.includes(spokenWord) ||
                        spokenWord.includes(expected.expectedWord)) {
                        
                        // Check if we have this word available
                        if (availableWordsMap.has(expected.expectedNormalized)) {
                            foundWord = availableWordsMap.get(expected.expectedNormalized);
                            break;
                        }
                        
                        // Also check with the spoken normalized version
                        if (availableWordsMap.has(spokenNormalized)) {
                            foundWord = availableWordsMap.get(spokenNormalized);
                            break;
                        }
                    }
                }

                matches.push({ blank: expected, word: foundWord });
            });

            // Count successful matches
            const filledCount = matches.filter(m => m.word !== null).length;
            
            if (filledCount === 0) {
                return;
            }

            // Fill all matched blanks
            matches.forEach((match, index) => {
                if (match.word) {
                    const blank = match.blank.element;
                    const position = match.blank.position;
                    const wordToFill = match.word.element.dataset.word;
                    
                    // Fill the blank
                    blank.textContent = wordToFill;
                    blank.classList.add('filled');
                    userAnswers[position] = wordToFill;
                    
                    // Mark word as used and hide it
                    match.word.element.style.display = 'none';
                    match.word.element.classList.add('used');
                    
                    // Remove from available map to prevent reuse
                    availableWordsMap.delete(match.word.normalized);
                    
                    // Visual feedback with staggered animation
                    setTimeout(() => {
                        blank.style.background = 'rgba(46, 204, 113, 0.2)';
                        blank.style.borderColor = '#2ecc71';
                    }, index * 100);
                }
            });

            // Show success message
            setTimeout(() => {
                const transcriptEl = document.getElementById('voiceTranscript');
                if (filledCount === expectedWords.length) {
                    transcriptEl.innerHTML = '<span style="color: #2ecc71;">✅ Tous les blancs remplis !</span>';
                } else {
                    transcriptEl.innerHTML = `<span style="color: #f1c40f;">⚠️ ${filledCount}/${expectedWords.length} blancs remplis</span>`;
                }
            }, expectedWords.length * 100 + 200);
        }

        /**
         * Normalize text for matching (remove accents, lowercase)
         */
        function normalizeText(text) {
            return text
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, ''); // Remove accents
        }
    </script>
</body>
</html>
