<?php
require_once __DIR__ . '/../../Model/Quiz.php';
require_once __DIR__ . '/../../Model/QuizBlank.php';
require_once __DIR__ . '/../../Controller/GameController.php';
require_once __DIR__ . '/../../Controller/QuizBlankController.php';

$difficulty = $_GET['difficulty'] ?? 'easy';


if (!in_array($difficulty, ['easy', 'medium', 'hard'])) {
    $difficulty = 'easy';
}

$gameController = new GameController();
$quiz = $gameController->getRandomQuiz($difficulty);

if (!$quiz) {
    die('Aucun quiz disponible pour cette difficulté. Veuillez créer des quiz dans le BackOffice.');
}

$blanks = $quiz->blanks;

// Trier par position
usort($blanks, function($a, $b) {
    return $a->position <=> $b->position;
});

$qid = $quiz->qid;
$paragraph = $quiz->paragraph;
$difficulty = $quiz->difficulty;


$correctAnswers = [];
foreach ($blanks as $blank) {
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
    <title>Jeu 3 - Multijoueur</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/owl-mascot.css">
    <link rel="stylesheet" href="assets/css/theme.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Custom styles for multiplayer */
        .multiplayer-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
            background: rgba(10, 22, 40, 0.8);
            border-bottom: 1px solid rgba(0, 212, 255, 0.2);
            margin-top: 80px; /* Space for fixed nav */
        }

        .multiplayer-header h2 {
            font-size: 24px;
            color: #fff;
            margin: 0;
        }

        .room-info {
            display: flex;
            align-items: center;
            gap: 20px;
            color: #b0c4de;
        }

        .room-id {
            color: #00d4ff;
            font-weight: 700;
            font-family: monospace;
            font-size: 18px;
        }

        .btn-leave {
            padding: 8px 16px;
            background: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
            border: 1px solid #e74c3c;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-leave:hover {
            background: #e74c3c;
            color: white;
        }

        .game-header-multi {
            text-align: center;
            padding: 20px;
            background: rgba(255, 255, 255, 0.02);
        }

        .labelTimer {
            font-size: 24px;
            font-weight: 700;
            color: #00d4ff;
            margin-bottom: 5px;
        }

        .wordSelector {
            color: #b0c4de;
            font-size: 16px;
        }

        .split-container {
            display: grid;
            grid-template-columns: 1fr 2px 1fr;
            gap: 20px;
            padding: 20px 40px;
            min-height: calc(100vh - 250px);
        }

        .divider {
            background: linear-gradient(to bottom, transparent, rgba(0, 212, 255, 0.3), transparent);
        }

        .player-panel {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        .player-panel h3 {
            text-align: center;
            color: #fff;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        #player1 h3 { color: #2ecc71; }
        #player2 h3 { color: #f1c40f; }

        .quiz-paragraph {
            background: rgba(10, 22, 40, 0.6);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            line-height: 1.8;
            color: #e0e6ed;
            border: 1px solid rgba(0, 212, 255, 0.1);
            flex-grow: 1;
        }

        .blank {
            display: inline-block;
            min-width: 80px;
            height: 30px;
            border: 1px dashed #00d4ff;
            border-radius: 4px;
            margin: 0 3px;
            vertical-align: middle;
            background: rgba(0, 212, 255, 0.1);
            cursor: pointer;
            text-align: center;
            line-height: 28px;
            color: #fff;
            font-size: 14px;
        }

        .blank.filled {
            border-style: solid;
            background: rgba(0, 212, 255, 0.2);
            font-weight: 600;
        }

        .words-container {
            background: rgba(10, 22, 40, 0.6);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            min-height: 80px;
            border: 1px solid rgba(0, 212, 255, 0.1);
        }

        .words-container h4 {
            margin-bottom: 10px;
            color: #b0c4de;
            font-size: 14px;
            text-transform: uppercase;
        }

        .words-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
        }

        .blank-word {
            padding: 6px 12px;
            background: linear-gradient(135deg, #00d4ff 0%, #0099cc 100%);
            color: white;
            border-radius: 6px;
            cursor: move;
            font-size: 14px;
            user-select: none;
        }

        .blank-word.dragging { opacity: 0.5; }

        .btn-validate {
            width: 100%;
            padding: 12px;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
        }

        #btnValidateP1 {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
        }

        #btnValidateP2 {
            background: linear-gradient(135deg, #f1c40f 0%, #f39c12 100%);
            color: white;
        }

        .btn-validate:disabled {
            background: #95a5a6 !important;
            cursor: not-allowed;
        }

        .score {
            text-align: center;
            margin-top: 15px;
            font-size: 24px;
            font-weight: 700;
            color: #fff;
        }

        /* Countdown Overlay */
        .countdown-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(10, 22, 40, 0.95);
            z-index: 2000;
            display: flex;
            justify-content: center;
            align-items: center;
            visibility: hidden;
        }

        .countdown-overlay:not(.hidden) {
            visibility: visible;
        }

        .countdown-content {
            font-size: 120px;
            font-weight: 800;
            color: #00d4ff;
            text-shadow: 0 0 50px rgba(0, 212, 255, 0.5);
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .results-area {
            display: none;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            margin: 20px 40px;
            border-radius: 8px;
            text-align: center;
        }

        .results-area.show { display: block; }

        @media (max-width: 1024px) {
            .split-container {
                grid-template-columns: 1fr;
                grid-template-rows: auto 2px auto;
            }
            .divider {
                width: 100%;
                height: 2px;
                background: linear-gradient(to right, transparent, rgba(0, 212, 255, 0.3), transparent);
            }
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

    <!-- Countdown Overlay -->
    <div id="countdownMulti" class="countdown-overlay hidden">
        <div class="countdown-content">
            <p id="countdownValueMulti">3</p>
        </div>
    </div>

    <!-- Multiplayer Header -->
    <div class="multiplayer-header">
        <h2>Jeu 3 - Multijoueur</h2>
        <div class="room-info">
            <span>Room: <span id="roomId" class="room-id">-</span></span>
            <button id="leaveRoom" class="btn-leave">Quitter</button>
        </div>
    </div>

    <!-- Game Header -->
    <div class="game-header-multi">
        <p id="labelTimerMulti" class="labelTimer">Prêt</p>
        <div class="wordSelector" id="wordSelectorMulti">À vos places!</div>
    </div>

    <!-- Split Screen -->
    <div class="split-container">
        <!-- Player 1 -->
        <div class="player-panel" id="player1">
            <h3>Joueur 1</h3>
            <div class="quiz-paragraph" id="quizParagraphP1">
                <?php
                $blankIndex = 0;
                $displayParagraph = preg_replace_callback(
                    '/\[([^\]]+)\]/',
                    function($match) use (&$blankIndex) {
                        $blankIndex++;
                        return '<span class="blank" data-position="' . ($blankIndex - 1) . '" data-correct="' . htmlspecialchars($match[1]) . '" data-player="1"></span>';
                    },
                    $paragraph
                );
                echo $displayParagraph;
                ?>
            </div>
            <div class="words-container">
                <h4>Glissez les mots :</h4>
                <div class="words-list" id="wordsListP1">
                    <?php foreach ($allWords as $word): ?>
                        <span class="blank-word" draggable="true" data-word="<?= htmlspecialchars($word) ?>" data-player="1">
                            <?= htmlspecialchars($word) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
            <button class="btn-validate" id="btnValidateP1" onclick="validateAnswers(1)">Valider</button>
            <div class="score" id="scoreP1">
                <p>0/<?= count($blanks) ?></p>
            </div>
        </div>

        <!-- Divider -->
        <div class="divider"></div>

        <!-- Player 2 -->
        <div class="player-panel" id="player2">
            <h3>Joueur 2</h3>
            <div class="quiz-paragraph" id="quizParagraphP2">
                <?php
                $blankIndex = 0;
                $displayParagraph = preg_replace_callback(
                    '/\[([^\]]+)\]/',
                    function($match) use (&$blankIndex) {
                        $blankIndex++;
                        return '<span class="blank" data-position="' . ($blankIndex - 1) . '" data-correct="' . htmlspecialchars($match[1]) . '" data-player="2"></span>';
                    },
                    $paragraph
                );
                echo $displayParagraph;
                ?>
            </div>
            <div class="words-container">
                <h4>Glissez les mots :</h4>
                <div class="words-list" id="wordsListP2">
                    <?php foreach ($allWords as $word): ?>
                        <span class="blank-word" draggable="true" data-word="<?= htmlspecialchars($word) ?>" data-player="2">
                            <?= htmlspecialchars($word) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
            <button class="btn-validate" id="btnValidateP2" onclick="validateAnswers(2)">Valider</button>
            <div class="score" id="scoreP2">
                <p>0/<?= count($blanks) ?></p>
            </div>
        </div>
    </div>

    <!-- Results Area -->
    <div class="results-area" id="resultsArea"></div>

    <!-- Interactive Owl Mascot -->
    <script src="assets/js/owl-mascot.js"></script>

    <script src="assets/js/sweetalert2-helper.js"></script>
    <script>
        'use strict';

        // Variables globales
        const quizId = <?= $qid ?>;
        const blanks = <?= json_encode(array_map(function($b) {
            return ['position' => $b->position, 'correctAnswer' => $b->correctAnswer];
        }, $blanks)) ?>;
        
        let userAnswersP1 = {};
        let userAnswersP2 = {};
        let isSubmittedP1 = false;
        let isSubmittedP2 = false;
        let gameStartTime = null;
        const roomId = 'ROOM-' + Math.random().toString(36).slice(2, 7).toUpperCase();

        // Éléments DOM
        const els = {
            countdownMulti: document.getElementById('countdownMulti'),
            countdownValueMulti: document.getElementById('countdownValueMulti'),
            roomId: document.getElementById('roomId'),
            leaveRoom: document.getElementById('leaveRoom'),
            labelTimerMulti: document.getElementById('labelTimerMulti'),
            wordSelectorMulti: document.getElementById('wordSelectorMulti'),
            resultsArea: document.getElementById('resultsArea')
        };

        // Initialiser
        document.addEventListener('DOMContentLoaded', function() {
            els.roomId.textContent = roomId;
            initDragAndDrop();
            startCountdown();
        });

        // Countdown
        function startCountdown() {
            showCountdown(els.countdownMulti, els.countdownValueMulti, 3).then(() => {
                startGame();
            });
        }

        function showCountdown(countdownEl, countdownValueEl, duration = 3) {
            return new Promise((resolve) => {
                if (!countdownEl) {
                    resolve();
                    return;
                }
                
                countdownEl.classList.remove('hidden');
                
                let count = duration;
                if (countdownValueEl) countdownValueEl.textContent = count;
                const interval = setInterval(() => {
                    count--;
                    if (countdownValueEl) countdownValueEl.textContent = count;
                    if (count <= 0) {
                        clearInterval(interval);
                        countdownEl.classList.add('hidden');
                        resolve();
                    }
                }, 1000);
            });
        }

        function startGame() {
            gameStartTime = Date.now();
            els.labelTimerMulti.textContent = 'Jeu en cours!';
            els.wordSelectorMulti.textContent = 'Remplissez les espaces vides!';
        }

        // Drag & Drop
        function initDragAndDrop() {
            // Player 1
            const blankWordsP1 = document.querySelectorAll('.blank-word[data-player="1"]');
            const blanksP1 = document.querySelectorAll('.blank[data-player="1"]');
            
            blankWordsP1.forEach(word => {
                word.addEventListener('dragstart', handleDragStart);
                word.addEventListener('dragend', handleDragEnd);
            });

            blanksP1.forEach(blank => {
                blank.addEventListener('dragover', handleDragOver);
                blank.addEventListener('drop', handleDrop);
                blank.addEventListener('dragleave', handleDragLeave);
                blank.addEventListener('click', handleBlankClick);
            });

            // Player 2
            const blankWordsP2 = document.querySelectorAll('.blank-word[data-player="2"]');
            const blanksP2 = document.querySelectorAll('.blank[data-player="2"]');
            
            blankWordsP2.forEach(word => {
                word.addEventListener('dragstart', handleDragStart);
                word.addEventListener('dragend', handleDragEnd);
            });

            blanksP2.forEach(blank => {
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
            e.dataTransfer.setData('player', this.dataset.player);
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
            const player = e.dataTransfer.getData('player');
            const position = parseInt(this.dataset.position);
            const blankPlayer = this.dataset.player;
            
            // Vérifier que c'est le bon joueur
            if (player !== blankPlayer) {
                return false;
            }
            
            // Vérifier si le blank est déjà rempli
            if (this.textContent.trim() !== '') {
                const previousWord = this.textContent.trim();
                addWordToList(previousWord, parseInt(player));
            }
            
            // Remplir le blank
            this.textContent = word;
            this.classList.add('filled');
            
            if (player === '1') {
                userAnswersP1[position] = word;
            } else {
                userAnswersP2[position] = word;
            }
            
            // Retirer le mot de la liste
            removeWordFromList(word, parseInt(player));
            
            return false;
        }

        function handleBlankClick(e) {
            const player = parseInt(this.dataset.player);
            if (this.textContent.trim() !== '') {
                const word = this.textContent.trim();
                this.textContent = '';
                this.classList.remove('filled');
                
                const position = parseInt(this.dataset.position);
                if (player === 1) {
                    delete userAnswersP1[position];
                } else {
                    delete userAnswersP2[position];
                }
                
                addWordToList(word, player);
            }
        }

        function removeWordFromList(word, player) {
            const words = document.querySelectorAll(`.blank-word[data-player="${player}"]`);
            words.forEach(w => {
                if (w.dataset.word === word && !w.classList.contains('used')) {
                    w.style.display = 'none';
                    w.classList.add('used');
                    return;
                }
            });
        }

        function addWordToList(word, player) {
            const words = document.querySelectorAll(`.blank-word[data-player="${player}"]`);
            words.forEach(w => {
                if (w.dataset.word === word && w.classList.contains('used')) {
                    w.style.display = 'inline-block';
                    w.classList.remove('used');
                    return;
                }
            });
        }

        // Validation
        function validateAnswers(player) {
            const isP1 = player === 1;
            const userAnswers = isP1 ? userAnswersP1 : userAnswersP2;
            const isSubmitted = isP1 ? isSubmittedP1 : isSubmittedP2;
            
            if (isSubmitted) return;
            
            // Vérifier que tous les blanks sont remplis
            const blanks = document.querySelectorAll(`.blank[data-player="${player}"]`);
            let allFilled = true;
            
            blanks.forEach(blank => {
                if (!blank.textContent.trim()) {
                    allFilled = false;
                }
            });
            
            if (!allFilled) {
                Swal.fire({
                    icon: 'warning',
                    title: `Joueur ${player}`,
                    text: 'Veuillez remplir tous les espaces vides avant de valider.',
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
                    displayResults(data, player);
                    if (isP1) {
                        isSubmittedP1 = true;
                        document.getElementById('btnValidateP1').disabled = true;
                    } else {
                        isSubmittedP2 = true;
                        document.getElementById('btnValidateP2').disabled = true;
                    }
                    
                    // Mettre à jour le score
                    updateScore(player, data.correctCount, data.totalBlanks);
                    
                    // Vérifier si les deux joueurs ont terminé
                    checkGameEnd();
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

        function updateScore(player, correct, total) {
            const scoreEl = document.getElementById(`scoreP${player}`);
            const scoreP = scoreEl.querySelector('p');
            scoreP.textContent = `${correct}/${total}`;
            
            // Animation bounce
            scoreEl.classList.remove('bounce');
            void scoreEl.offsetWidth;
            scoreEl.classList.add('bounce');
        }

        function displayResults(data, player) {
            const resultsHTML = `
                <div style="margin-bottom: 20px; color: #fff;">
                    <h4 style="color: ${player === 1 ? '#2ecc71' : '#f1c40f'}">Résultats Joueur ${player}</h4>
                    <div style="font-size: 32px; font-weight: bold;">${data.score}%</div>
                    <div>(${data.correctCount}/${data.totalBlanks} correctes)</div>
                </div>
            `;
            
            els.resultsArea.innerHTML += resultsHTML;
            els.resultsArea.classList.add('show');
        }

        function checkGameEnd() {
            if (isSubmittedP1 && isSubmittedP2) {
                els.labelTimerMulti.textContent = 'Partie terminée!';
                els.wordSelectorMulti.textContent = 'Les deux joueurs ont terminé!';
                
                Swal.fire({
                    icon: 'success',
                    title: 'Partie terminée!',
                    text: 'Bravo aux deux joueurs!',
                    confirmButtonText: 'Retour au menu',
                    confirmButtonColor: '#3498db'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'jeu3_multijoueur.html';
                    }
                });
            }
        }

        // Leave room
        els.leaveRoom.addEventListener('click', () => {
            Swal.fire({
                title: 'Quitter la partie',
                text: 'Êtes-vous sûr de vouloir quitter la partie ?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#3498db',
                confirmButtonText: 'Oui, quitter',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'jeu3_multijoueur.html';
                }
            });
        });
    </script>
</body>
</html>
