/**
 * Correspondance Floue (Fuzzy Matching)
 * Gère les fautes de reconnaissance et les accents français
 */

class FuzzyMatcher {
    constructor() {
        // Carte de normalisation des accents français
        this.accentMap = {
            'à': 'a', 'â': 'a', 'ä': 'a',
            'é': 'e', 'è': 'e', 'ê': 'e', 'ë': 'e',
            'î': 'i', 'ï': 'i',
            'ô': 'o', 'ö': 'o',
            'ù': 'u', 'û': 'u', 'ü': 'u',
            'ç': 'c',
            'œ': 'oe', 'æ': 'ae'
        };

        // Corrections fréquentes en français
        this.corrections = {
            'shat': 'chat',
            'chatt': 'chat',
            'manjay': 'manger',
            'parry': 'paris',
            'soury': 'souris',
            'sourie': 'souris'
        };
    }

    /**
     * Compare deux mots et retourne un score de similarité
     * @param {string} word1 - Premier mot
     * @param {string} word2 - Second mot  
     * @returns {Object} - Résultat avec score et confiance
     */
    match(word1, word2) {
        // Normaliser les deux mots
        const normalized1 = this.normalize(word1);
        const normalized2 = this.normalize(word2);

        // Correspondance exacte
        if (normalized1 === normalized2) {
            return {
                matched: true,
                score: 1.0,
                confidence: 'HIGH',
                method: 'exact'
            };
        }

        // Auto-correction
        const corrected1 = this.autoCorrect(normalized1);
        if (corrected1 === normalized2) {
            return {
                matched: true,
                score: 0.95,
                confidence: 'HIGH',
                method: 'autocorrect',
                original: word1,
                corrected: corrected1
            };
        }

        // Distance de Levenshtein
        const distance = this.levenshteinDistance(normalized1, normalized2);
        const maxLength = Math.max(normalized1.length, normalized2.length);
        const similarity = 1 - (distance / maxLength);

        // Seuils de confiance
        let confidence = 'LOW';
        let matched = false;

        if (similarity >= 0.85) {
            confidence = 'HIGH';
            matched = true;
        } else if (similarity >= 0.70) {
            confidence = 'MEDIUM';
            matched = true;
        } else if (similarity >= 0.55) {
            confidence = 'LOW';
            matched = true;
        }

        return {
            matched: matched,
            score: similarity,
            confidence: confidence,
            method: 'levenshtein',
            distance: distance
        };
    }

    /**
     * Distance de Levenshtein (mesure de similarité de chaînes)
     */
    levenshteinDistance(str1, str2) {
        const len1 = str1.length;
        const len2 = str2.length;
        const matrix = [];

        // Initialiser la matrice
        for (let i = 0; i <= len1; i++) {
            matrix[i] = [i];
        }
        for (let j = 0; j <= len2; j++) {
            matrix[0][j] = j;
        }

        // Remplir la matrice
        for (let i = 1; i <= len1; i++) {
            for (let j = 1; j <= len2; j++) {
                const cost = str1[i - 1] === str2[j - 1] ? 0 : 1;
                matrix[i][j] = Math.min(
                    matrix[i - 1][j] + 1,      // Suppression
                    matrix[i][j - 1] + 1,      // Insertion
                    matrix[i - 1][j - 1] + cost // Substitution
                );
            }
        }

        return matrix[len1][len2];
    }

    /**
     * Normalise un texte (enlève accents, minuscules, espaces)
     */
    normalize(text) {
        text = text.toLowerCase().trim();

        // Remplacer les accents
        for (const [accented, plain] of Object.entries(this.accentMap)) {
            text = text.replace(new RegExp(accented, 'g'), plain);
        }

        return text;
    }

    /**
     * Auto-correction basée sur le dictionnaire
     */
    autoCorrect(word) {
        const normalized = this.normalize(word);
        return this.corrections[normalized] || word;
    }

    /**
     * Trouve la meilleure correspondance dans une liste
     * @param {string} word - Mot à trouver
     * @param {Array} candidates - Liste de mots candidats
     * @returns {Object} - Meilleure correspondance
     */
    findBestMatch(word, candidates) {
        if (!candidates || candidates.length === 0) {
            return null;
        }

        let bestMatch = null;
        let bestScore = 0;

        for (const candidate of candidates) {
            const result = this.match(word, candidate);
            if (result.matched && result.score > bestScore) {
                bestScore = result.score;
                bestMatch = {
                    candidate: candidate,
                    ...result
                };
            }
        }

        return bestMatch;
    }

    /**
     * Filtre une liste par similarité
     * @param {string} query - Requête
     * @param {Array} items - Liste d'items
     * @param {number} minScore - Score minimum (0-1)
     * @returns {Array} - Items filtrés et triés
     */
    filter(query, items, minScore = 0.6) {
        const results = [];

        for (const item of items) {
            const text = typeof item === 'string' ? item : item.text;
            const result = this.match(query, text);

            if (result.matched && result.score >= minScore) {
                results.push({
                    item: item,
                    ...result
                });
            }
        }

        // Trier par score décroissant
        return results.sort((a, b) => b.score - a.score);
    }

    /**
     * Ajoute une correction au dictionnaire
     */
    addCorrection(wrong, correct) {
        const normalizedWrong = this.normalize(wrong);
        this.corrections[normalizedWrong] = correct;
    }

    /**
     * Obtient un emoji de confiance
     */
    getConfidenceEmoji(confidence) {
        switch (confidence) {
            case 'HIGH': return '🟢';
            case 'MEDIUM': return '🟡';
            case 'LOW': return '🔴';
            default: return '⚪';
        }
    }
}
