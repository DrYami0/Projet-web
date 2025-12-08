/**
 * Analyseur de Commandes Vocales en Français
 * Parse et exécute les commandes vocales
 */

class CommandParser {
    constructor() {
        // Patterns de commandes en français
        this.patterns = {
            // Remplir par nom: "remplir ville avec Paris"
            fillByName: /(?:remplir|remplis)\s+(\w+)\s+(?:avec|par)\s+(.+)/i,

            // Remplir par numéro: "remplir blanc 2 avec pizza"
            fillByNumber: /(?:remplir|remplis)\s+(?:blanc|le\s+blanc|blank)\s+(\d+)\s+(?:avec|par)\s+(.+)/i,

            // Effacer: "effacer blanc 3" ou "effacer tout"
            clear: /(?:effacer|efface|supprimer|supprime)\s+(?:(?:blanc|le\s+blanc)\s+(\d+)|tout)/i,

            // Navigation: "blanc suivant", "blanc précédent"
            navigate: /(?:blanc|aller au blanc)\s+(suivant|précédent|prochain|dernier)/i,

            // Validation: "valider", "soumettre", "terminer"
            submit: /(?:valider|soumettre|terminer|envoyer|fini|c'est bon)/i,

            // Annuler/Refaire
            undo: /(?:annuler|retour|annule)/i,
            redo: /(?:refaire|rétablir)/i,

            // Lecture: "lire le quiz", "lire ma réponse"
            read: /(?:lire|lis)\s+(?:le\s+)?(quiz|réponse|ma\s+réponse)/i,

            // Aide
            help: /(?:aide|commandes|help)/i,

            // Choix: "choix 1", "option 2", "un", "deux", "trois"
            choice: /(?:choix|option|numéro)\s+(\d+|un|deux|trois|quatre|cinq)/i,

            // Répéter
            repeat: /(?:répéter|répète|encore)/i,

            // Arrêter l'écoute
            stopListening: /(?:arrêter?|stop|pause)/i,

            // Recommencer
            restart: /(?:recommencer|recommence|tout\s+effacer)/i
        };

        // Conversion nombres en chiffres
        this.numberWords = {
            'un': 1, 'une': 1,
            'deux': 2,
            'trois': 3,
            'quatre': 4,
            'cinq': 5,
            'six': 6,
            'sept': 7,
            'huit': 8
        };
    }

    /**
     * Parse une commande vocale
     * @param {string} text - Texte à analyser
     * @returns {Object} - Commande parsée
     */
    parse(text) {
        text = this.normalize(text);

        // Tester chaque pattern
        for (const [commandType, pattern] of Object.entries(this.patterns)) {
            const match = text.match(pattern);
            if (match) {
                return this.buildCommand(commandType, match);
            }
        }

        // Aucune commande détectée
        return {
            type: 'UNKNOWN',
            text: text
        };
    }

    /**
     * Construit l'objet commande
     */
    buildCommand(type, match) {
        switch (type) {
            case 'fillByName':
                return {
                    type: 'FILL',
                    method: 'BY_NAME',
                    target: match[1].toLowerCase(),
                    value: match[2].trim()
                };

            case 'fillByNumber':
                return {
                    type: 'FILL',
                    method: 'BY_NUMBER',
                    target: parseInt(match[1]),
                    value: match[2].trim()
                };

            case 'clear':
                return {
                    type: 'CLEAR',
                    target: match[1] ? parseInt(match[1]) : 'ALL'
                };

            case 'navigate':
                return {
                    type: 'NAVIGATE',
                    direction: match[1].toLowerCase()
                };

            case 'submit':
                return {
                    type: 'SUBMIT'
                };

            case 'undo':
                return {
                    type: 'UNDO'
                };

            case 'redo':
                return {
                    type: 'REDO'
                };

            case 'read':
                return {
                    type: 'READ',
                    target: match[1].toLowerCase()
                };

            case 'help':
                return {
                    type: 'HELP'
                };

            case 'choice':
                const choiceNum = this.convertWordToNumber(match[1]);
                return {
                    type: 'CHOICE',
                    value: choiceNum
                };

            case 'repeat':
                return {
                    type: 'REPEAT'
                };

            case 'stopListening':
                return {
                    type: 'STOP_LISTENING'
                };

            case 'restart':
                return {
                    type: 'RESTART'
                };

            default:
                return {
                    type: 'UNKNOWN',
                    text: match[0]
                };
        }
    }

    /**
     * Normalise le texte
     */
    normalize(text) {
        return text
            .toLowerCase()
            .trim()
            .replace(/\s+/g, ' ')  // Multiples espaces → un seul
            .replace(/[.,!?;]/g, ''); // Retire la ponctuation
    }

    /**
     * Convertit un nombre en mot en chiffre
     */
    convertWordToNumber(word) {
        if (/^\d+$/.test(word)) {
            return parseInt(word);
        }
        return this.numberWords[word.toLowerCase()] || null;
    }

    /**
     * Détecte si le texte est une commande
     */
    isCommand(text) {
        const normalized = this.normalize(text);

        for (const pattern of Object.values(this.patterns)) {
            if (pattern.test(normalized)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtient une liste de suggestions de commandes
     */
    getSuggestions() {
        return [
            'Remplir ville avec Paris',
            'Remplir blanc 2 avec pizza',
            'Effacer blanc 3',
            'Blanc suivant',
            'Valider',
            'Annuler',
            'Lire ma réponse',
            'Aide'
        ];
    }
}
