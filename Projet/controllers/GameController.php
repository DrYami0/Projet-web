<?php
namespace Projet\Controllers;

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/GameModel.php';

use Projet\Models\GameModel;

class GameController extends \Controller {
    public function index() {
        $gameModel = new GameModel();
        $gameData = $gameModel->getGameData();
        
        $data = [
            'title' => 'Fill in the Blanks - PerFran Education',
            'page' => 'game',
            'gameData' => $gameData
        ];
        $this->renderWithLayout('game/game', $data);
    }

    public function submit() {

        $gameModel = new GameModel();
        $answers = [];

        // Récupérer les données POST depuis le formulaire HTML
        if (!empty($_POST['answers']) && is_array($_POST['answers'])) {
            // Reconstruire le tableau des réponses depuis le formulaire
            foreach ($_POST['answers'] as $answer) {
                if (isset($answer['id']) && isset($answer['order'])) {
                    $answers[] = [
                        'id'    => (int)$answer['id'],
                        'order' => (int)$answer['order'],
                    ];
                }
            }
        }



        $result = $gameModel->checkAnswers($answers);
        
    
        $gameData = $gameModel->getGameData();
        
        // Préparer les données pour la vue avec les résultats
        $data = [
            'title' => 'Fill in the Blanks - PerFran Education',
            'page' => 'game',
            'gameData' => $gameData,
            'feedback' => [
                'message' => $result['message'],
                'class'   => ($result['correct'] === $result['total']) ? 'success' : 'error',
            ],
            'submittedAnswers' => $answers,
            'results' => $result['results'],
        ];
        
        // Afficher la page avec les résultats
        $this->renderWithLayout('game/game', $data);
    }
}
