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
            // Afficher le paragraphe avec les blanks
            $paragraph = $paragraph;
            $blankIndex = 0;
            $words = [];
            
            // Extraire les bonnes réponses depuis les blanks
            $correctAnswers = [];
            foreach ($blanks as $blank) {
                $correctAnswers[] = $blank->correctAnswer;
            }
            
            // Créer une liste de mots sans doublons (chaque réponse une seule fois)
            $allWords = array_unique($correctAnswers);
            shuffle($allWords);
            
            // Remplacer les [mot] par des zones de drop
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
        </div>
    </div>

    <script>
        // Variables globales
        const quizId = <?= $qid ?>;
        const blanks = <?= json_encode(array_map(function($b) {
            return ['position' => $b->position, 'correctAnswer' => $b->correctAnswer];
        }, $blanks)) ?>;
        
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
            
            blanks.forEach(blank => {
                if (!blank.textContent.trim()) {
                    allFilled = false;
                }
            });
            
            if (!allFilled) {
                alert('Veuillez remplir tous les espaces vides avant de valider.');
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
                    alert('Erreur: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Une erreur est survenue lors de la validation.');
            });
        }

        function displayResults(data) {
            const resultsDiv = document.getElementById('results');
            const scoreDisplay = document.getElementById('scoreDisplay');
            const resultsDetails = document.getElementById('resultsDetails');
            
            // Afficher le score
            let scoreClass = 'poor';
            if (data.score >= 80) scoreClass = 'excellent';
            else if (data.score >= 50) scoreClass = 'good';
            
            scoreDisplay.textContent = data.score + '%';
            scoreDisplay.className = 'score ' + scoreClass;
            
            // Afficher les détails
            let detailsHTML = '<p><strong>Résultats: ' + data.correctCount + '/' + data.totalBlanks + ' correctes</strong></p>';
            
            data.results.forEach((result, index) => {
                const blank = document.querySelector(`.blank[data-position="${result.position}"]`);
                if (result.isCorrect) {
                    blank.style.background = '#d4edda';
                    blank.style.borderColor = '#27ae60';
                    detailsHTML += `<div class="result-item correct">
                        ✓ Position ${result.position + 1}: "${result.userAnswer}" - Correct
                    </div>`;
                } else {
                    blank.style.background = '#f8d7da';
                    blank.style.borderColor = '#e74c3c';
                    detailsHTML += `<div class="result-item incorrect">
                        ✗ Position ${result.position + 1}: "${result.userAnswer}" - Incorrect (Réponse: "${result.correctAnswer}")
                    </div>`;
                }
            });
            
            resultsDetails.innerHTML = detailsHTML;
            resultsDiv.style.display = 'block';
            resultsDiv.scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</body>
</html>

