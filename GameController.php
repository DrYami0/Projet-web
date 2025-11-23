<?php
require_once __DIR__ . '/../Model/Quiz.php';
require_once __DIR__ . '/../Model/QuizBlank.php';

class GameController
{
    /**
     * Récupère un quiz aléatoire selon la difficulté
     */
    public function getRandomQuiz(string $difficulty = 'easy'): ?Quiz
    {
        return Quiz::getRandom($difficulty);
    }

    /**
     * Affiche la page de jeu avec le quiz
     */
    public function play(): void
    {
        $difficulty = $_GET['difficulty'] ?? 'easy';
        
        // Valider la difficulté
        if (!in_array($difficulty, ['easy', 'medium', 'hard'])) {
            $difficulty = 'easy';
        }
        
        $quiz = $this->getRandomQuiz($difficulty);
        
        if (!$quiz) {
            die('Aucun quiz disponible pour cette difficulté');
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
        
        include __DIR__ . '/../View/FrontOffice/quiz_play.php';
    }

    /**
     * Valide les réponses du joueur
     */
    public function validateAnswers(): void
    {
        header('Content-Type: application/json');
        
        $qid = (int)($_POST['qid'] ?? 0);
        $answersJson = $_POST['answers'] ?? '[]';
        $answers = json_decode($answersJson, true);
        
        if ($qid === 0 || empty($answers) || !is_array($answers)) {
            echo json_encode(['success' => false, 'message' => 'Données invalides']);
            exit;
        }
        
        $quiz = Quiz::getById($qid);
        if (!$quiz) {
            echo json_encode(['success' => false, 'message' => 'Quiz non trouvé']);
            exit;
        }
        
        $blanks = QuizBlank::getByQuizId($qid);
        usort($blanks, function($a, $b) {
            return $a->position <=> $b->position;
        });
        
        $results = [];
        $correctCount = 0;
        $totalBlanks = count($blanks);
        
        foreach ($blanks as $index => $blank) {
            $userAnswer = trim($answers[$index] ?? '');
            $correctAnswer = trim(strtolower($blank->correctAnswer));
            $isCorrect = (strtolower($userAnswer) === $correctAnswer);
            
            if ($isCorrect) {
                $correctCount++;
            }
            
            $results[] = [
                'position' => $blank->position,
                'userAnswer' => $userAnswer,
                'correctAnswer' => $blank->correctAnswer,
                'isCorrect' => $isCorrect
            ];
        }
        
        $score = $totalBlanks > 0 ? round(($correctCount / $totalBlanks) * 100) : 0;
        
        echo json_encode([
            'success' => true,
            'score' => $score,
            'correctCount' => $correctCount,
            'totalBlanks' => $totalBlanks,
            'results' => $results
        ]);
        exit;
    }
}

