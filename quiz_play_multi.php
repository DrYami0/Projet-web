<?php
require_once __DIR__ . '/../../Model/Quiz.php';
require_once __DIR__ . '/../../Model/QuizBlank.php';

$difficulty = $_GET['difficulty'] ?? 'easy';

// Valider la difficulté
if (!in_array($difficulty, ['easy', 'medium', 'hard'])) {
    $difficulty = 'easy';
}

// Récupérer un quiz aléatoire
$quiz = Quiz::getRandom($difficulty);

if (!$quiz) {
    die('Aucun quiz disponible pour cette difficulté. Veuillez créer des quiz dans le BackOffice.');
}

// Récupérer les blanks
$blanks = QuizBlank::getByQuizId($quiz->qid);

// Trier par position
usort($blanks, function($a, $b) {
    return $a->position <=> $b->position;
});

// Extraire les variables pour la vue
$qid = $quiz->qid;
$paragraph = $quiz->paragraph;
$difficulty = $quiz->difficulty;

// Extraire les bonnes réponses depuis les blanks
$correctAnswers = [];
foreach ($blanks as $blank) {
    $correctAnswers[] = $blank->correctAnswer;
}

// Créer une liste de mots sans doublons (chaque réponse une seule fois)
$allWords = array_unique($correctAnswers);
shuffle($allWords);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jeu 3 - Multijoueur</title>
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }
        .hidden {
            display: none !important;
        }
        .countdown-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: grid;
            place-items: center;
            z-index: 9999;
            visibility: hidden;
        }
        .countdown-content {
            text-align: center;
            color: white;
        }
        .countdown-content p {
            font-size: 120px;
            font-weight: bold;
            margin: 0;
            animation: pulse 1s infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }
        .multiplayer-header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .multiplayer-header h2 {
            margin: 0;
            font-size: 24px;
        }
        .room-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .room-id {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: bold;
        }
        .btn-leave {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-leave:hover {
            background: #c0392b;
        }
        .game-header-multi {
            text-align: center;
            padding: 20px;
            background: white;
            border-bottom: 2px solid #e0e0e0;
        }
        .labelTimer {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .wordSelector {
            font-size: 32px;
            color: #3498db;
            font-weight: bold;
            min-height: 50px;
        }
        .split-container {
            display: flex;
            height: calc(100vh - 200px);
        }
        .player-panel {
            flex: 1;
            padding: 20px;
            background: #f8f9fa;
            overflow-y: auto;
        }
        .player-panel h3 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 22px;
        }
        .divider {
            width: 3px;
            background: linear-gradient(to bottom, #3498db, #2c3e50);
        }
        .quiz-paragraph {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 16px;
            line-height: 1.8;
            min-height: 120px;
        }
        .blank {
            display: inline-block;
            min-width: 100px;
            height: 40px;
            border: 2px dashed #3498db;
            border-radius: 5px;
            margin: 0 3px;
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
        .words-container {
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .words-container h4 {
            margin-bottom: 10px;
            color: #2c3e50;
            font-size: 16px;
        }
        .words-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .blank-word {
            display: inline-block;
            padding: 6px 12px;
            background: #3498db;
            color: white;
            border-radius: 5px;
            cursor: move;
            user-select: none;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        .blank-word:hover {
            background: #2980b9;
            transform: scale(1.05);
        }
        .blank-word.dragging {
            opacity: 0.5;
        }
        .score {
            text-align: center;
            padding: 15px;
            background: white;
            border-radius: 10px;
            margin-top: 15px;
        }
        .score p {
            font-size: 32px;
            font-weight: bold;
            margin: 0;
            color: #3498db;
        }
        .score.bounce {
            animation: bounce 0.5s;
        }
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .btn-validate {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            font-weight: bold;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 15px;
        }
        .btn-validate:hover {
            background: #229954;
        }
        .btn-validate:disabled {
            background: #95a5a6;
            cursor: not-allowed;
        }
        .results-area {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 20px;
            box-shadow: 0 -5px 20px rgba(0,0,0,0.1);
            max-height: 300px;
            overflow-y: auto;
            display: none;
        }
        .results-area.show {
            display: block;
        }
        .success-message {
            text-align: center;
            color: #27ae60;
            font-size: 20px;
            font-weight: bold;
            padding: 20px;
        }
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .results-table th,
        .results-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .results-table th {
            background: #f8f9fa;
            font-weight: bold;
        }
        .cell-word {
            font-weight: bold;
        }
        .cell-type {
            color: #6c757d;
        }
    </style>
</head>
<body>
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
                countdownEl.style.display = 'grid';
                countdownEl.style.visibility = 'visible';
                
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
                alert(`Joueur ${player}: Veuillez remplir tous les espaces vides avant de valider.`);
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
                    alert('Erreur: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Une erreur est survenue lors de la validation.');
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
                <h4>Résultats Joueur ${player}: ${data.score}% (${data.correctCount}/${data.totalBlanks})</h4>
                <p>Joueur ${player} a terminé!</p>
            `;
            
            els.resultsArea.innerHTML += resultsHTML;
            els.resultsArea.classList.add('show');
        }

        function checkGameEnd() {
            if (isSubmittedP1 && isSubmittedP2) {
                els.labelTimerMulti.textContent = 'Partie terminée!';
                els.wordSelectorMulti.textContent = 'Les deux joueurs ont terminé!';
            }
        }

        // Leave room
        els.leaveRoom.addEventListener('click', () => {
            if (confirm('Êtes-vous sûr de vouloir quitter la partie?')) {
                window.location.href = 'jeu3_multijoueur.html';
            }
        });
    </script>
</body>
</html>

