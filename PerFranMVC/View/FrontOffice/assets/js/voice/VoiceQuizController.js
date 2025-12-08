/**
 * Contrôleur Principal du Mode Vocal
 * Orchestre tous les composants du système vocal
 */

class VoiceQuizController {
    constructor(quizData) {
        this.quizData = quizData;
        this.blanks = this.extractBlanks();
        this.filledBlanks = new Map();
        this.history = []; // Pour undo/redo
        this.historyIndex = -1;

        // Composants
        this.speechManager = new SpeechRecognitionManager();
        this.commandParser = new CommandParser();
        this.fuzzyMatcher = new FuzzyMatcher();
        this.tts = window.speechSynthesis;

        // État
        this.mode = 'ready'; // ready, listening, command, choice
        this.currentBlankIndex = 0;
        this.lastTranscript = '';

        this.init();
    }

    /**
     * Initialise le contrôleur
     */
    init() {
        // Callbacks de reconnaissance vocale
        this.speechManager.onResult((results) => {
            this.handleSpeechResults(results);
        });

        this.speechManager.onError((error) => {
            this.showError(error.message);
        });

        this.speechManager.onEnd(() => {
            if (this.mode === 'listening') {
                // Redémarrer automatiquement
                setTimeout(() => {
                    this.speechManager.start();
                }, 500);
            }
        });
    }

    /**
     * Extrait les blanks du quiz
     */
    extractBlanks() {
        const blanks = [];
        const regex = /\[([^\]]+)\]/g;
        let match;
        let index = 0;

        while ((match = regex.exec(this.quizData.paragraph)) !== null) {
            blanks.push({
                index: index,
                answer: match[1].trim(),
                position: match.index,
                length: match[0].length,
                name: this.generateBlankName(match[1]),
                filled: false,
                value: null,
                confidence: null
            });
            index++;
        }

        return blanks;
    }

    /**
     * Génère un nom pour le blank (premier mot de la réponse)
     */
    generateBlankName(answer) {
        return answer.split(' ')[0].toLowerCase();
    }

    /**
     * Traite les résultats de la reconnaissance
     */
    handleSpeechResults(results) {
        for (const result of results) {
            if (!result.isFinal) {
                // Résultat intermédiaire - afficher en temps réel
                this.showInterimTranscript(result.transcript);
                continue;
            }

            // Résultat final
            const transcript = result.transcript;
            this.lastTranscript = transcript;

            // Vérifier si c'est une commande
            if (this.commandParser.isCommand(transcript)) {
                this.executeCommand(transcript);
            } else {
                // Essayer de remplir les blanks automatiquement
                this.fillFromFullSentence(transcript, result.confidence);
            }
        }
    }

    /**
     * Remplit les blanks à partir d'une phrase complète
     */
    fillFromFullSentence(transcript, confidence) {
        const words = transcript.toLowerCase().split(/\s+/);
        const paragraphWords = this.quizData.paragraph
            .replace(/\[[^\]]+\]/g, '____')
            .toLowerCase()
            .split(/\s+/);

        let wordIndex = 0;
        let blankIndex = 0;

        for (let i = 0; i < paragraphWords.length; i++) {
            if (paragraphWords[i] === '____') {
                // C'est un blank - prendre le mot correspondant
                if (wordIndex < words.length) {
                    const blank = this.blanks[blankIndex];
                    const spokenWord = words[wordIndex];

                    // Vérifier la correspondance avec fuzzy matching
                    const match = this.fuzzyMatcher.match(spokenWord, blank.answer);

                    if (match.matched) {
                        this.fillBlank(blankIndex, spokenWord, match.confidence);
                    } else {
                        // Correspondance faible - proposer des choix
                        this.showChoiceSelector(blankIndex, [spokenWord, blank.answer]);
                    }
                }
                blankIndex++;
                wordIndex++;
            } else {
                // Mot normal - vérifier qu'il correspond
                if (wordIndex < words.length) {
                    const expected = paragraphWords[i].replace(/[.,!?;]/g, '');
                    const spoken = words[wordIndex].replace(/[.,!?;]/g, '');

                    if (this.fuzzyMatcher.normalize(expected) ===
                        this.fuzzyMatcher.normalize(spoken)) {
                        wordIndex++;
                    }
                }
            }
        }

        this.updateUI();
    }

    /**
     * Exécute une commande vocale
     */
    executeCommand(text) {
        const command = this.commandParser.parse(text);

        console.log('Commande détectée:', command);

        switch (command.type) {
            case 'FILL':
                this.handleFillCommand(command);
                break;
            case 'CLEAR':
                this.handleClearCommand(command);
                break;
            case 'NAVIGATE':
                this.handleNavigateCommand(command);
                break;
            case 'SUBMIT':
                this.submitQuiz();
                break;
            case 'UNDO':
                this.undo();
                break;
            case 'REDO':
                this.redo();
                break;
            case 'READ':
                this.handleReadCommand(command);
                break;
            case 'HELP':
                this.showHelp();
                break;
            case 'CHOICE':
                this.selectChoice(command.value);
                break;
            case 'REPEAT':
                this.repeatLastCommand();
                break;
            case 'STOP_LISTENING':
                this.stopListening();
                break;
            case 'RESTART':
                this.restart();
                break;
            case 'UNKNOWN':
                this.showCommandFeedback('Commande non reconnue. Dites "aide" pour voir les commandes disponibles.');
                break;
        }
    }

    /**
     * Gère la commande FILL
     */
    handleFillCommand(command) {
        if (command.method === 'BY_NAME') {
            const blank = this.blanks.find(b => b.name === command.target);
            if (blank) {
                this.fillBlank(blank.index, command.value, 'HIGH');
                this.showCommandFeedback(`Blank "${blank.name}" rempli avec "${command.value}"`);
            } else {
                this.showCommandFeedback(`Blank "${command.target}" non trouvé`);
            }
        } else if (command.method === 'BY_NUMBER') {
            const index = command.target - 1; // Conversion 1-based → 0-based
            if (index >= 0 && index < this.blanks.length) {
                this.fillBlank(index, command.value, 'HIGH');
                this.showCommandFeedback(`Blank ${command.target} rempli avec "${command.value}"`);
            } else {
                this.showCommandFeedback(`Numéro de blank invalide: ${command.target}`);
            }
        }

        this.updateUI();
    }

    /**
     * Remplit un blank
     */
    fillBlank(index, value, confidence) {
        // Sauvegarder dans l'historique
        this.saveToHistory({
            type: 'FILL',
            index: index,
            oldValue: this.blanks[index].value,
            newValue: value
        });

        this.blanks[index].filled = true;
        this.blanks[index].value = value;
        this.blanks[index].confidence = confidence;

        this.filledBlanks.set(index, value);

        // Effet sonore de succès
        this.playSuccessSound();
    }

    /**
     * Gère la commande CLEAR
     */
    handleClearCommand(command) {
        if (command.target === 'ALL') {
            this.clearAll();
            this.showCommandFeedback('Tous les blanks ont été effacés');
        } else {
            const index = command.target - 1;
            if (index >= 0 && index < this.blanks.length) {
                this.clearBlank(index);
                this.showCommandFeedback(`Blank ${command.target} effacé`);
            }
        }

        this.updateUI();
    }

    /**
     * Efface un blank
     */
    clearBlank(index) {
        this.saveToHistory({
            type: 'CLEAR',
            index: index,
            oldValue: this.blanks[index].value
        });

        this.blanks[index].filled = false;
        this.blanks[index].value = null;
        this.blanks[index].confidence = null;
        this.filledBlanks.delete(index);
    }

    /**
     * Efface tous les blanks
     */
    clearAll() {
        for (let i = 0; i < this.blanks.length; i++) {
            this.clearBlank(i);
        }
    }

    /**
     * Lit le quiz ou la réponse avec TTS
     */
    handleReadCommand(command) {
        if (command.target === 'quiz') {
            this.readQuiz();
        } else if (command.target.includes('réponse')) {
            this.readAnswer();
        }
    }

    /**
     * Lit le quiz avec TTS
     */
    readQuiz() {
        const text = this.quizData.paragraph.replace(/\[([^\]]+)\]/g, 'blank');
        this.speak(text);
    }

    /**
     * Lit la réponse remplie
     */
    readAnswer() {
        let text = this.quizData.paragraph;

        for (const blank of this.blanks) {
            const value = blank.value || 'blank vide';
            text = text.replace(`[${blank.answer}]`, value);
        }

        this.speak(text);
    }

    /**
     * Synthèse vocale
     */
    speak(text) {
        if (this.tts.speaking) {
            this.tts.cancel();
        }

        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'fr-FR';
        utterance.rate = 0.9;
        utterance.pitch = 1.0;

        this.tts.speak(utterance);
    }

    /**
     * Démarre l'écoute
     */
    startListening() {
        if (this.speechManager.start()) {
            this.mode = 'listening';
            this.showListeningIndicator();
            return true;
        }
        return false;
    }

    /**
     * Arrête l'écoute
     */
    stopListening() {
        this.speechManager.stop();
        this.mode = 'ready';
        this.hideListeningIndicator();
    }

    /**
     * Soumet le quiz
     */
    submitQuiz() {
        const allFilled = this.blanks.every(b => b.filled);

        if (!allFilled) {
            this.showConfirmation('Certains blanks sont vides. Voulez-vous quand même soumettre ?', () => {
                this.doSubmit();
            });
        } else {
            this.doSubmit();
        }
    }

    /**
     * Soumet effectivement le quiz
     */
    doSubmit() {
        // Préparer les données
        const answers = {};
        for (const blank of this.blanks) {
            answers[blank.index] = blank.value || '';
        }

        // Appeler l'API ou le formulaire existant
        console.log('Soumission:', answers);

        // Rediriger vers la page de résultats (à implémenter)
        this.showSuccessMessage('Quiz soumis avec succès!');
    }

    /**
     * Annuler (Undo)
     */
    undo() {
        if (this.historyIndex < 0) {
            this.showCommandFeedback('Rien à annuler');
            return;
        }

        const action = this.history[this.historyIndex];
        this.revertAction(action);
        this.historyIndex--;
        this.updateUI();
        this.showCommandFeedback('Action annulée');
    }

    /**
     * Refaire (Redo)
     */
    redo() {
        if (this.historyIndex >= this.history.length - 1) {
            this.showCommandFeedback('Rien à refaire');
            return;
        }

        this.historyIndex++;
        const action = this.history[this.historyIndex];
        this.applyAction(action);
        this.updateUI();
        this.showCommandFeedback('Action rétablie');
    }

    /**
     * Sauvegarde dans l'historique
     */
    saveToHistory(action) {
        // Supprimer les actions après l'index actuel
        this.history = this.history.slice(0, this.historyIndex + 1);
        this.history.push(action);
        this.historyIndex++;
    }

    /**
     * Obtient le statut du quiz
     */
    getStatus() {
        const filled = this.blanks.filter(b => b.filled).length;
        const total = this.blanks.length;
        const percentage = Math.round((filled / total) * 100);

        return {
            filled: filled,
            total: total,
            percentage: percentage,
            allFilled: filled === total
        };
    }

    /**
     * Méthodes UI (à implémenter dans VoiceUI.js)
     */
    showInterimTranscript(text) { console.log('Interim:', text); }
    showCommandFeedback(message) { console.log('Feedback:', message); }
    showChoiceSelector(blankIndex, options) { console.log('Choix:', options); }
    showListeningIndicator() { console.log('🎤 Écoute...'); }
    hideListeningIndicator() { console.log('🎤 Arrêt'); }
    showError(message) { console.error('Erreur:', message); }
    playSuccessSound() { }
    updateUI() { console.log('UI update'); }
    showHelp() { console.log('Aide'); }
    showSuccessMessage(msg) { console.log(msg); }
}
