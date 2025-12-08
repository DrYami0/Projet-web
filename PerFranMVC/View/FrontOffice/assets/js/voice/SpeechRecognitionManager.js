/**
 * Gestionnaire de Reconnaissance Vocale
 * Gère l'API Web Speech pour la reconnaissance vocale en français
 */

class SpeechRecognitionManager {
    constructor() {
        this.recognition = null;
        this.isListening = false;
        this.onResultCallback = null;
        this.onErrorCallback = null;
        this.onEndCallback = null;

        this.init();
    }

    /**
     * Initialise la reconnaissance vocale
     */
    init() {
        // Vérifier la compatibilité du navigateur
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

        if (!SpeechRecognition) {
            console.error('Reconnaissance vocale non supportée par ce navigateur');
            return false;
        }

        this.recognition = new SpeechRecognition();

        // Configuration pour le français
        this.recognition.lang = 'fr-FR';
        this.recognition.continuous = true;
        this.recognition.interimResults = true;
        this.recognition.maxAlternatives = 3;

        // Événements
        this.recognition.onstart = () => {
            this.isListening = true;
            console.log('🎤 Écoute démarrée');
        };

        this.recognition.onresult = (event) => {
            this.handleResult(event);
        };

        this.recognition.onerror = (event) => {
            this.handleError(event);
        };

        this.recognition.onend = () => {
            this.isListening = false;
            if (this.onEndCallback) {
                this.onEndCallback();
            }
            console.log('🎤 Écoute terminée');
        };

        return true;
    }

    /**
     * Traite les résultats de la reconnaissance
     */
    handleResult(event) {
        const results = [];

        for (let i = event.resultIndex; i < event.results.length; i++) {
            const result = event.results[i];
            const transcript = result[0].transcript;
            const confidence = result[0].confidence;
            const isFinal = result.isFinal;

            // Alternatives
            const alternatives = [];
            for (let j = 0; j < result.length && j < 3; j++) {
                alternatives.push({
                    transcript: result[j].transcript,
                    confidence: result[j].confidence
                });
            }

            results.push({
                transcript: transcript.trim(),
                confidence: confidence,
                isFinal: isFinal,
                alternatives: alternatives
            });
        }

        if (this.onResultCallback) {
            this.onResultCallback(results);
        }
    }

    /**
     * Gère les erreurs
     */
    handleError(event) {
        console.error('Erreur de reconnaissance vocale:', event.error);

        const errorMessages = {
            'no-speech': 'Aucune parole détectée',
            'audio-capture': 'Microphone non disponible',
            'not-allowed': 'Permission microphone refusée',
            'network': 'Erreur réseau',
            'aborted': 'Reconnaissance interrompue'
        };

        const message = errorMessages[event.error] || 'Erreur inconnue';

        if (this.onErrorCallback) {
            this.onErrorCallback({
                type: event.error,
                message: message
            });
        }
    }

    /**
     * Démarre l'écoute
     */
    start() {
        if (!this.recognition) {
            console.error('Reconnaissance vocale non initialisée');
            return false;
        }

        if (this.isListening) {
            console.warn('Déjà en écoute');
            return false;
        }

        try {
            this.recognition.start();
            return true;
        } catch (error) {
            console.error('Erreur au démarrage:', error);
            return false;
        }
    }

    /**
     * Arrête l'écoute
     */
    stop() {
        if (!this.recognition || !this.isListening) {
            return false;
        }

        try {
            this.recognition.stop();
            return true;
        } catch (error) {
            console.error('Erreur à l\'arrêt:', error);
            return false;
        }
    }

    /**
     * Redémarre l'écoute
     */
    restart() {
        this.stop();
        setTimeout(() => {
            this.start();
        }, 100);
    }

    /**
     * Définit le callback pour les résultats
     */
    onResult(callback) {
        this.onResultCallback = callback;
    }

    /**
     * Définit le callback pour les erreurs
     */
    onError(callback) {
        this.onErrorCallback = callback;
    }

    /**
     * Définit le callback pour la fin
     */
    onEnd(callback) {
        this.onEndCallback = callback;
    }

    /**
     * Vérifie si le navigateur supporte la reconnaissance vocale
     */
    static isSupported() {
        return !!(window.SpeechRecognition || window.webkitSpeechRecognition);
    }

    /**
     * Demande la permission du microphone
     */
    static async requestPermission() {
        try {
            await navigator.mediaDevices.getUserMedia({ audio: true });
            return true;
        } catch (error) {
            console.error('Permission microphone refusée:', error);
            return false;
        }
    }
}
