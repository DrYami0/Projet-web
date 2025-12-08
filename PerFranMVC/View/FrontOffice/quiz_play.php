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
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .quiz-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
        }
        .quiz-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .quiz-header h2 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .difficulty-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .difficulty-badge.easy {
            background: #d4edda;
            color: #155724;
        }
        .difficulty-badge.medium {
            background: #fff3cd;
            color: #856404;
        }
        .difficulty-badge.hard {
            background: #f8d7da;
            color: #721c24;
        }
        .quiz-paragraph {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            font-size: 18px;
            line-height: 1.8;
            min-height: 150px;
        }
        .blank {
            display: inline-block;
            min-width: 120px;
            height: 45px;
            border: 2px dashed #3498db;
            border-radius: 5px;
            margin: 0 5px;
            vertical-align: middle;
            background: #e8f4f8;
            position: relative;
            cursor: pointer;
        }
        .blank.drag-over {
            border-color: #27ae60;
            background: #d5f4e6;
        }
        .blank.filled {
            border: 2px solid #3498db;
            background: white;
        }
        .blank-word {
            display: inline-block;
            padding: 8px 15px;
            margin: 5px;
            background: #3498db;
            color: white;
            border-radius: 5px;
            cursor: move;
            user-select: none;
            transition: all 0.3s ease;
        }
        .blank-word:hover {
            background: #2980b9;
            transform: scale(1.05);
        }
        .blank-word.dragging {
            opacity: 0.5;
        }
        .words-container {
            background: #e8f4f8;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            min-height: 100px;
        }
        .words-container h4 {
            margin-bottom: 15px;
            color: #2c3e50;
        }
        .words-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .btn-validate {
            width: 100%;
            padding: 15px;
            font-size: 18px;
            font-weight: bold;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-validate:hover {
            background: #229954;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.3);
        }
        .btn-validate:disabled {
            background: #95a5a6;
            cursor: not-allowed;
        }
        .results {
            display: none;
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .score {
            text-align: center;
            font-size: 48px;
            font-weight: bold;
            margin: 20px 0;
        }
        .score.excellent {
            color: #27ae60;
        }
        .score.good {
            color: #f39c12;
        }
        .score.poor {
            color: #e74c3c;
        }
        .result-item {
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
        }
        .result-item.correct {
            background: #d4edda;
            color: #155724;
        }
        .result-item.incorrect {
            background: #f8d7da;
            color: #721c24;
        }
        .btn-retry {
            margin-top: 20px;
            padding: 12px 30px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-retry:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <header class="navbar-light header-sticky">
        <nav class="navbar navbar-expand-xl">
            <div class="container">
                <a class="navbar-brand" href="index.html">
                    <img class="navbar-brand-item" src="assets/img/logo.png" alt="logo">
                </a>
            </div>
        </nav>
    </header>

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
        <div style="text-align: right; margin-bottom: 15px;">
            <button id="voiceModeBtn" style="padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 20px; font-size: 14px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3); transition: all 0.3s;">
                <span style="font-size: 18px;">🎤</span>
                <span id="voiceBtnText">Mode Vocal</span>
            </button>
        </div>

        <!-- Voice Listening Indicator -->
        <div id="voiceIndicator" style="display: none; background: #667eea; border-radius: 8px; padding: 15px; margin-bottom: 15px; text-align: center; box-shadow: 0 2px 10px rgba(102, 126, 234, 0.3);">
            <div style="display: flex; align-items: center; justify-content: center; gap: 15px;">
                <div style="font-size: 32px; animation: pulse 2s infinite;">🎤</div>
                <div style="flex: 1; text-align: left;">
                    <p style="font-size: 14px; color: white; font-weight: 600; margin: 0 0 5px 0;">Écoute active...</p>
                    <div style="background: white; border-radius: 6px; padding: 8px 12px;">
                        <p id="voiceTranscript" style="font-size: 16px; color: #2c3e50; margin: 0; font-weight: 600; min-height: 20px;"></p>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.15); }
            }
        </style>

        <button class="btn-validate" id="btnValidate" onclick="validateAnswers()">
            Valider les réponses
        </button>

        <div class="results" id="results">
            <h3>Résultats</h3>
            <div class="score" id="scoreDisplay"></div>
            <div id="resultsDetails"></div>
            <a href="quiz_play.php?difficulty=<?= htmlspecialchars($difficulty) ?>" class="btn-retry">
                Nouveau Quiz
            </a>
            <a href="jeu3_solo.html" class="btn-retry" style="margin-left: 10px;">
                Changer de difficulté
            </a>
            <button type="button" class="btn-retry" style="margin-left: 10px; background-color: #f39c12;" onclick="window.location.href='suggest.php'">
                Suggérer un quiz
            </button>
        </div>
    </div>

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
                showWarning('Action requise', `Veuillez remplir tous les espaces vides avant de valider. (${emptyCount} restant(s))`);
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
                    showError('Erreur', data.message || 'Une erreur est survenue lors de la validation.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Erreur', 'Une erreur est survenue lors de la validation.');
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
            let detailsHTML = '<p><strong>Résultats: ' + data.correctCount + '/' + data.totalBlanks + ' correctes</strong></p>';
            data.results.forEach((result, index) => {
                if (result.isCorrect) {
                    detailsHTML += `<div class="result-item correct">
                        ✓ Position ${result.position}: "${result.userAnswer}" - Correct
                    </div>`;
                } else {
                    detailsHTML += `<div class="result-item incorrect">
                        ✗ Position ${result.position}: "${result.userAnswer}" - Incorrect (Réponse: "${result.correctAnswer}")
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
                html: '<div style="font-size: 48px; font-weight: bold; margin: 20px 0; color: ' + (data.score >= 80 ? '#27ae60' : data.score >= 50 ? '#f39c12' : '#e74c3c') + ';">' + data.score + '%</div>' + detailsHTML,
                confirmButtonText: 'OK',
                confirmButtonColor: '#3498db',
                width: '600px'
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
                        transcriptEl.innerHTML = '<span style="color: #27ae60;">✓ ' + transcript + '</span>';
                        console.log('Voice recognized:', transcript);
                        processVoiceCommand(transcript.toLowerCase().trim());
                        
                        // Clear after 2 seconds
                        setTimeout(() => {
                            transcriptEl.innerHTML = '<span style="color: #95a5a6;">Parlez...</span>';
                        }, 2000);
                    } else {
                        // Interim result - show in blue
                        transcriptEl.innerHTML = '<span style="color: #3498db;">' + transcript + '</span>';
                    }
                }
            };

            recognition.onerror = function(event) {
                console.error('Speech recognition error:', event.error);
                if (event.error === 'no-speech') {
                    return; // Continue listening
                }
                showWarning('Erreur vocale', 'Erreur: ' + event.error);
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
                showWarning('Non supporté', 'Votre navigateur ne supporte pas la reconnaissance vocale. Utilisez Chrome ou Edge.');
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
                    showWarning('Permission refusée', 'Veuillez autoriser l\'accès au microphone.');
                    voiceModeActive = false;
                    return;
                }

                // Start recognition
                try {
                    recognition.start();
                    this.style.background = '#e74c3c';
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
                this.style.background = '#667eea';
                btnText.textContent = 'Mode Vocal';
                indicator.style.display = 'none';
                console.log('Voice mode deactivated');
            }
        });

        /**
         * Process voice command and fill ALL blanks from complete sentence
         * NEW APPROACH: Match words in the order they appear in the paragraph
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

            console.log('Expected words in order:', expectedWords.map(e => e.expectedWord));

            // Split transcript into words
            const spokenWords = transcript.toLowerCase().split(/\s+/);
            console.log('Spoken words:', spokenWords);

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

            console.log('Available words:', Array.from(availableWordsMap.values()).map(w => w.word));

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
                            console.log(`✓ Blank ${index}: "${expected.expectedWord}" found in sentence`);
                            break;
                        }
                        
                        // Also check with the spoken normalized version
                        if (availableWordsMap.has(spokenNormalized)) {
                            foundWord = availableWordsMap.get(spokenNormalized);
                            console.log(`✓ Blank ${index}: "${expected.expectedWord}" matched with spoken "${spokenWord}"`);
                            break;
                        }
                    }
                }

                if (!foundWord) {
                    console.log(`✗ Blank ${index}: "${expected.expectedWord}" NOT found in sentence`);
                }

                matches.push({ blank: expected, word: foundWord });
            });

            // Count successful matches
            const filledCount = matches.filter(m => m.word !== null).length;
            
            if (filledCount === 0) {
                console.log('❌ No matches found in sentence');
                return;
            }

            console.log(`📝 Filling ${filledCount}/${expectedWords.length} blanks!`);

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
                        blank.style.background = '#d4edda';
                        blank.style.borderColor = '#27ae60';
                    }, index * 100);
                    
                    console.log(`✅ Filled position ${position} with "${wordToFill}"`);
                }
            });

            // Show success message
            setTimeout(() => {
                const transcriptEl = document.getElementById('voiceTranscript');
                if (filledCount === expectedWords.length) {
                    transcriptEl.innerHTML = '<span style="color: #27ae60;">✅ Tous les blancs remplis !</span>';
                } else {
                    transcriptEl.innerHTML = `<span style="color: #f39c12;">⚠️ ${filledCount}/${expectedWords.length} blancs remplis</span>`;
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
                .replace(/[\u0300-\u036f]/g, '') // Remove accents
                .replace(/[^a-z0-9]/g, '');
        }
    </script>
</body>
</html>
