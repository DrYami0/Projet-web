<?php
session_start();
require_once __DIR__ . '/../../Controller/QuizController.php';

// Récupérer l'ID du quiz
$quizId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($quizId === 0) {
    header('Location: index.html');
    exit;
}

// Charger le quiz avec les blanks
$quiz = QuizController::getById($quizId);

if (!$quiz) {
    die('Quiz non trouvé');
}

// Les blanks sont déjà dans $quiz->blanks
$blanks = $quiz->blanks ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mode Vocal - PerFran</title>
    <link rel="stylesheet" href="assets/css/voice-mode.css">
</head>
<body>
    <div class="voice-container">
        <!-- Header -->
        <header class="voice-header">
            <div class="header-content">
                <h1>🎤 Mode Vocal</h1>
                <p class="subtitle">Complétez le quiz en parlant</p>
            </div>
            <div class="header-actions">
                <button id="helpBtn" class="btn-icon" title="Aide">
                    <span>❓</span>
                </button>
                <a href="quiz_play.php?id=<?= $quizId ?>" class="btn-icon" title="Retour au mode normal">
                    <span>🔄</span>
                </a>
            </div>
        </header>

        <!-- Statut de connexion -->
        <div id="browserSupport" class="support-banner" style="display: none;">
            <span class="icon">⚠️</span>
            <span class="message">Votre navigateur ne supporte pas la reconnaissance vocale.</span>
        </div>

        <!-- Indicateur d'écoute -->
        <div id="listeningIndicator" class="listening-indicator" style="display: none;">
            <div class="pulse-ring"></div>
            <div class="microphone-icon">🎤</div>
            <p class="listening-text">J'écoute...</p>
        </div>

        <!-- Zone de quiz -->
        <div class="quiz-area">
            <div class="difficulty-badge badge-<?= htmlspecialchars($quiz->difficulty) ?>">
                <?= ucfirst(htmlspecialchars($quiz->difficulty)) ?>
            </div>
            
            <div id="quizText" class="quiz-text">
                <?= htmlspecialchars($quiz->paragraph) ?>
            </div>

            <!-- Progress -->
            <div class="progress-section">
                <div class="progress-bar">
                    <div id="progressFill" class="progress-fill" style="width: 0%"></div>
                </div>
                <p id="progressText" class="progress-text">0/<?= $quiz->nbBlanks ?> blanks remplis</p>
            </div>
        </div>

        <!-- Transcription en temps réel -->
        <div id="transcriptArea" class="transcript-area" style="display: none;">
            <div class="transcript-header">
                <span class="icon">💬</span>
                <span>Vous avez dit:</span>
            </div>
            <p id="transcriptText" class="transcript-text"></p>
            <p id="interimText" class="interim-text"></p>
        </div>

        <!-- Feedback de commandes -->
        <div id="feedbackArea" class="feedback-area" style="display: none;">
            <p id="feedbackText"></p>
        </div>

        <!-- Sélecteur de choix -->
        <div id="choiceSelector" class="choice-selector" style="display: none;">
            <h3>🤔 Choisissez la bonne réponse:</h3>
            <div id="choiceOptions" class="choice-options"></div>
        </div>

        <!-- Contrôles principaux -->
        <div class="main-controls">
            <button id="micBtn" class="btn-primary btn-large">
                <span class="icon">🎤</span>
                <span class="text">Commencer</span>
            </button>
            
            <div class="secondary-controls" style="display: none;">
                <button id="readQuizBtn" class="btn-secondary">
                    <span>🔊 Lire le quiz</span>
                </button>
                <button id="readAnswerBtn" class="btn-secondary">
                    <span>📖 Lire ma réponse</span>
                </button>
                <button id="undoBtn" class="btn-secondary">
                    <span>↶ Annuler</span>
                </button>
                <button id="submitBtn" class="btn-success">
                    <span>✓ Valider</span>
                </button>
            </div>
        </div>

        <!-- Panel d'aide -->
        <div id="helpPanel" class="help-panel" style="display: none;">
            <div class="help-header">
                <h2>💡 Aide - Commandes Vocales</h2>
                <button id="closeHelpBtn" class="btn-close">×</button>
            </div>
            
            <div class="help-content">
                <section>
                    <h3>🎯 Comment ça marche?</h3>
                    <p>Vous pouvez remplir le quiz de 3 façons:</p>
                    <ol>
                        <li><strong>Phrase complète:</strong> Lisez toute la phrase avec les mots manquants</li>
                        <li><strong>Par nom:</strong> "Remplir ville avec Paris"</li>
                        <li><strong>Par numéro:</strong> "Remplir blanc 2 avec pizza"</li>
                    </ol>
                </section>

                <section>
                    <h3>📋 Commandes disponibles</h3>
                    <div class="command-list">
                        <div class="command-item">
                            <strong>"Remplir ville avec Paris"</strong>
                            <span>Remplir un blank par son nom</span>
                        </div>
                        <div class="command-item">
                            <strong>"Remplir blanc 2 avec pizza"</strong>
                            <span>Remplir le blank numéro 2</span>
                        </div>
                        <div class="command-item">
                            <strong>"Effacer blanc 3"</strong>
                            <span>Effacer un blank spécifique</span>
                        </div>
                        <div class="command-item">
                            <strong>"Annuler"</strong>
                            <span>Annuler la dernière action</span>
                        </div>
                        <div class="command-item">
                            <strong>"Lire le quiz"</strong>
                            <span>Écouter le texte du quiz</span>
                        </div>
                        <div class="command-item">
                            <strong>"Lire ma réponse"</strong>
                            <span>Écouter votre réponse</span>
                        </div>
                        <div class="command-item">
                            <strong>"Valider"</strong>
                            <span>Soumettre le quiz</span>
                        </div>
                        <div class="command-item">
                            <strong>"Arrêter"</strong>
                            <span>Arrêter l'écoute</span>
                        </div>
                    </div>
                </section>

                <section>
                    <h3>💡 Astuces</h3>
                    <ul>
                        <li>Parlez clairement et pas trop vite</li>
                        <li>Les blanks sont indiqués avec 🟢🟡🔴 selon la confiance</li>
                        <li>🟢 = Haute confiance, 🟡 = Moyenne, 🔴 = Faible</li>
                        <li>Vous pouvez corriger un blank rempli en le redemandant</li>
                    </ul>
                </section>
            </div>
        </div>
    </div>

    <!-- Quiz Data (JSON pour JavaScript) -->
    <script>
        const quizData = {
            id: <?= $quiz->qid ?>,
            paragraph: <?= json_encode($quiz->paragraph) ?>,
            difficulty: <?= json_encode($quiz->difficulty) ?>,
            nbBlanks: <?= $quiz->nbBlanks ?>,
            blanks: <?= json_encode($blanks) ?>
        };
    </script>

    <!-- Scripts -->
    <script src="assets/js/voice/SpeechRecognitionManager.js"></script>
    <script src="assets/js/voice/CommandParser.js"></script>
    <script src="assets/js/voice/FuzzyMatcher.js"></script>
    <script src="assets/js/voice/VoiceQuizController.js"></script>
    <script src="assets/js/voice/voice-app.js"></script>
</body>
</html>
