<?php
require_once __DIR__ . '/../../database/config.php';

class AIQuizGenerator {
    private $apiKey;
    private $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

    public function __construct() {
        if (!defined('GEMINI_API_KEY') || GEMINI_API_KEY === 'your-api-key-here') {
            throw new Exception("Clé API Gemini non configurée. Veuillez l'ajouter dans database/config.php");
        }
        $this->apiKey = GEMINI_API_KEY;
    }

    public function generateQuiz($theme, $nbBlanks) {
        // Mock Mode
        if ($this->apiKey === 'MOCK') {
            sleep(1); // Simulate API delay
            
            // Generate a simple mock text based on theme
            $words = explode(' ', "Le la les un une des est a ont sont et mais ou car donc ni or");
            $themeWords = [$theme, 'exemple', 'test', 'quiz', 'français', 'langue', 'mots', 'phrase'];
            
            $text = "Voici un paragraphe généré par le mode simulation sur le thème '$theme'. ";
            $text .= "Ce texte sert à tester la fonctionnalité sans utiliser de clé API réelle. ";
            
            // Add sentences with blanks
            for ($i = 0; $i < $nbBlanks; $i++) {
                $word = $themeWords[$i % count($themeWords)] ?? 'mot';
                $text .= "Ceci est un [$word] à trouver. ";
            }
            
            return $text;
        }

        $prompt = "Génère un TRÈS COURT paragraphe narratif en français (maximum 2-3 phrases) sur le thème : '$theme'. " .
                  "Le texte doit raconter une situation simple, PAS une définition. " .
                  "Le texte doit contenir EXACTEMENT $nbBlanks mots mis entre crochets [mot]. " .
                  "IMPORTANT: Ne mets JAMAIS de parenthèses ou d'explications. " .
                  "Exemple: 'Le [chat] mange la [souris] dans le [jardin].' " .
                  "Sois concis et direct.";

        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ];

        $url = $this->apiUrl . '?key=' . $this->apiKey;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            throw new Exception('Erreur Curl : ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if (isset($result['error'])) {
            throw new Exception('Erreur API Gemini : ' . $result['error']['message']);
        }
        
        if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            throw new Exception('Réponse invalide de l\'IA.');
        }

        return trim($result['candidates'][0]['content']['parts'][0]['text']);
    }
}
