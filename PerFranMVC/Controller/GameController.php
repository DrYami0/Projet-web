<?php
require_once __DIR__ . '/../Model/Quiz.php';
require_once __DIR__ . '/../Model/QuizBlank.php';
require_once __DIR__ . '/../../database/config.php';
require_once __DIR__ . '/QuizController.php';
require_once __DIR__ . '/QuizBlankController.php';

class GameController
{
   
    public function getRandomQuiz(string $difficulty = 'easy'): ?Quiz
    {
        $pdo = getDB();
        $stmt = $pdo->prepare(
            'SELECT qid FROM quiz WHERE difficulty = ? AND approved = 1 ORDER BY RAND() LIMIT 1'
        );
        $stmt->execute([$difficulty]);
        $row = $stmt->fetch();
        
        return $row ? QuizController::getById($row['qid']) : null;
    }

    
    public function play(): void
    {
        $difficulty = $_GET['difficulty'] ?? 'easy';
        
    
        if (!in_array($difficulty, ['easy', 'medium', 'hard'])) {
            $difficulty = 'easy';
        }
        
        $quiz = $this->getRandomQuiz($difficulty);
        
        if (!$quiz) {
            die('Aucun quiz disponible pour cette difficulté');
        }
        
        
        $blanks = $quiz->blanks;
        
        
        usort($blanks, function($a, $b) {
            return $a->position <=> $b->position;
        });
        
 
        $qid = $quiz->qid;
        $paragraph = $quiz->paragraph;
        $difficulty = $quiz->difficulty;
        
        include __DIR__ . '/../View/FrontOffice/quiz_play.php';
    }

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
        
        $quiz = QuizController::getById($qid);
        if (!$quiz) {
            echo json_encode(['success' => false, 'message' => 'Quiz non trouvé']);
            exit;
        }
        
        $blanks = $quiz->blanks;
        
        // Filter out intruders (position <= 0)
        $activeBlanks = array_filter($blanks, function($blank) {
            return $blank->position > 0;
        });
        
        // Sort by position
        usort($activeBlanks, function($a, $b) {
            return $a->position <=> $b->position;
        });
        
        $results = [];
        $correctCount = 0;
        $totalBlanks = count($activeBlanks);
        
        foreach ($activeBlanks as $index => $blank) {
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

