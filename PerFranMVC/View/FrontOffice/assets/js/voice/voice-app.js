/**
 * Application Principale du Mode Vocal
 * Initialise et connecte tous les composants
 */

let voiceController = null;

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
    init();
});

function init() {
    // Vérifier le support du navigateur
    if (!SpeechRecognitionManager.isSupported()) {
        showBrowserNotSupported();
        return;
    }

    // Initialiser le contrôleur vocal
    voiceController = new VoiceQuizController(quizData);

    // Surcharger les méthodes UI
    voiceController.showInterimTranscript = showInterimTranscript;
    voiceController.showCommandFeedback = showCommandFeedback;
    voiceController.showChoiceSelector = showChoiceSelector;
    voiceController.showListeningIndicator = showListeningIndicator;
    voiceController.hideListeningIndicator = hideListeningIndicator;
    voiceController.showError = showError;
    voiceController.playSuccessSound = playSuccessSound;
    voiceController.updateUI = updateUI;
    voiceController.showHelp = showHelpPanel;
    voiceController.showSuccessMessage = showSuccessMessage;
    voiceController.showConfirmation = showConfirmation;

    // Initialiser l'interface
    initUI();

    // Événements
    setupEventListeners();

    // Afficher le quiz initial
    renderQuiz();
}

/**
 * Configure les écouteurs d'événements
 */
function setupEventListeners() {
    // Bouton microphone
    document.getElementById('micBtn').addEventListener('click', toggleListening);

    // Boutons secondaires
    document.getElementById('readQuizBtn').addEventListener('click', () => {
        voiceController.readQuiz();
    });

    document.getElementById('readAnswerBtn').addEventListener('click', () => {
        voiceController.readAnswer();
    });

    document.getElementById('undoBtn').addEventListener('click', () => {
        voiceController.undo();
    });

    document.getElementById('submitBtn').addEventListener('click', () => {
        voiceController.submitQuiz();
    });

    // Aide
    document.getElementById('helpBtn').addEventListener('click', showHelpPanel);
    document.getElementById('closeHelpBtn').addEventListener('click', hideHelpPanel);
}

/**
 * Basculer l'écoute
 */
function toggleListening() {
    const btn = document.getElementById('micBtn');

    if (voiceController.mode === 'listening') {
        voiceController.stopListening();
        btn.querySelector('.text').textContent = 'Recommencer';
        btn.querySelector('.icon').textContent = '🎤';
        document.querySelector('.secondary-controls').style.display = 'flex';
    } else {
        // Demander la permission si première fois
        SpeechRecognitionManager.requestPermission().then(granted => {
            if (granted) {
                voiceController.startListening();
                btn.querySelector('.text').textContent = 'Arrêter l\'écoute';
                btn.querySelector('.icon').textContent = '⏸️';
                document.querySelector('.secondary-controls').style.display = 'flex';
            } else {
                showError('Permission du microphone refusée');
            }
        });
    }
}

/**
 * Affiche le quiz avec les blanks
 */
function renderQuiz() {
    const quizTextEl = document.getElementById('quizText');
    let html = quizData.paragraph;

    // Remplacer les blanks par des spans éditables
    quizData.blanks.forEach((blank, index) => {
        const blankHtml = `
            <span class="blank-slot" data-index="${index}" data-answer="${blank.answer}">
                <span class="blank-number">${index + 1}</span>
                <span class="blank-content" contenteditable="false">______</span>
                <span class="confidence-indicator"></span>
            </span>
        `;
        html = html.replace(`[${blank.answer}]`, blankHtml);
    });

    quizTextEl.innerHTML = html;
}

/**
 * Met à jour l'interface
 */
function updateUI() {
    // Mettre à jour les blanks dans le quiz
    voiceController.blanks.forEach((blank, index) => {
        const blankEl = document.querySelector(`.blank-slot[data-index="${index}"]`);
        if (!blankEl) return;

        const contentEl = blankEl.querySelector('.blank-content');
        const indicatorEl = blankEl.querySelector('.confidence-indicator');

        if (blank.filled) {
            contentEl.textContent = blank.value;
            contentEl.classList.add('filled');

            // Indicateur de confiance
            const emoji = voiceController.fuzzyMatcher.getConfidenceEmoji(blank.confidence);
            indicatorEl.textContent = emoji;
            indicatorEl.className = `confidence-indicator confidence-${blank.confidence.toLowerCase()}`;
        } else {
            contentEl.textContent = '______';
            contentEl.classList.remove('filled');
            indicatorEl.textContent = '';
        }
    });

    // Mise à jour de la progression
    const status = voiceController.getStatus();
    document.getElementById('progressFill').style.width = `${status.percentage}%`;
    document.getElementById('progressText').textContent =
        `${status.filled}/${status.total} blanks remplis (${status.percentage}%)`;
}

/**
 * Affiche la transcription intermédiaire
 */
function showInterimTranscript(text) {
    const transcriptArea = document.getElementById('transcriptArea');
    const interimText = document.getElementById('interimText');

    transcriptArea.style.display = 'block';
    interimText.textContent = text;
    interimText.style.opacity = '0.6';
}

/**
 * Affiche un feedback de commande
 */
function showCommandFeedback(message) {
    const feedbackArea = document.getElementById('feedbackArea');
    const feedbackText = document.getElementById('feedbackText');

    feedbackText.textContent = message;
    feedbackArea.style.display = 'block';
    feedbackArea.classList.add('show');

    // Aussi afficher dans la transcription
    const transcriptText = document.getElementById('transcriptText');
    transcriptText.textContent = message;

    // Cacher après 3 secondes
    setTimeout(() => {
        feedbackArea.classList.remove('show');
        setTimeout(() => {
            feedbackArea.style.display = 'none';
        }, 300);
    }, 3000);
}

/**
 * Affiche le sélecteur de choix
 */
function showChoiceSelector(blankIndex, options) {
    const choiceSelector = document.getElementById('choiceSelector');
    const choiceOptions = document.getElementById('choiceOptions');

    choiceOptions.innerHTML = '';

    options.forEach((option, index) => {
        const btn = document.createElement('button');
        btn.className = 'choice-option';
        btn.textContent = `${index + 1}. ${option}`;
        btn.onclick = () => {
            voiceController.fillBlank(blankIndex, option, 'MEDIUM');
            voiceController.updateUI();
            hideChoiceSelector();
        };
        choiceOptions.appendChild(btn);
    });

    choiceSelector.style.display = 'block';
}

function hideChoiceSelector() {
    document.getElementById('choiceSelector').style.display = 'none';
}

/**
 * Affiche l'indicateur d'écoute
 */
function showListeningIndicator() {
    const indicator = document.getElementById('listeningIndicator');
    indicator.style.display = 'flex';
    indicator.classList.add('active');
}

function hideListeningIndicator() {
    const indicator = document.getElementById('listeningIndicator');
    indicator.classList.remove('active');
    setTimeout(() => {
        indicator.style.display = 'none';
    }, 300);
}

/**
 * Affiche une erreur
 */
function showError(message) {
    const feedbackArea = document.getElementById('feedbackArea');
    const feedbackText = document.getElementById('feedbackText');

    feedbackText.textContent = '❌ ' + message;
    feedbackArea.style.display = 'block';
    feedbackArea.classList.add('error');

    setTimeout(() => {
        feedbackArea.classList.remove('error');
        feedbackArea.style.display = 'none';
    }, 4000);
}

/**
 * Joue un son de succès
 */
function playSuccessSound() {
    // Créer un son simple avec Web Audio API
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();

    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);

    oscillator.frequency.value = 800;
    oscillator.type = 'sine';

    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);

    oscillator.start(audioContext.currentTime);
    oscillator.stop(audioContext.currentTime + 0.2);
}

/**
 * Affiche le panneau d'aide
 */
function showHelpPanel() {
    document.getElementById('helpPanel').style.display = 'block';
}

function hideHelpPanel() {
    document.getElementById('helpPanel').style.display = 'none';
}

/**
 * Affiche le navigateur non supporté
 */
function showBrowserNotSupported() {
    document.getElementById('browserSupport').style.display = 'flex';
    document.getElementById('micBtn').disabled = true;
    document.getElementById('micBtn').querySelector('.text').textContent = 'Non supporté';
}

/**
 * Affiche un message de succès
 */
function showSuccessMessage(message) {
    showCommandFeedback('✅ ' + message);
}

/**
 * Affiche une confirmation
 */
function showConfirmation(message, onConfirm) {
    if (confirm(message)) {
        onConfirm();
    }
}

/**
 * Initialise les éléments UI
 */
function initUI() {
    // Ajouter des attributs ARIA pour l'accessibilité
    document.getElementById('micBtn').setAttribute('aria-label', 'Démarrer la reconnaissance vocale');

    // Raccourcis clavier
    document.addEventListener('keydown', (e) => {
        if (e.ctrlKey && e.key === 'm') {
            e.preventDefault();
            toggleListening();
        }
        if (e.ctrlKey && e.key === 'z') {
            e.preventDefault();
            voiceController.undo();
        }
        if (e.key === 'F1') {
            e.preventDefault();
            showHelpPanel();
        }
    });
}
