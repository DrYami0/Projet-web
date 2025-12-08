<?php
require_once __DIR__ . '/../Model/Quiz.php';
require_once __DIR__ . '/../Model/QuizBlank.php';
require_once __DIR__ . '/../../database/config.php';

class QuizController
{
    
    public static function getById(int $id): ?Quiz
    {
        $pdo = getDB();
        $stmt = $pdo->prepare('
            SELECT q.*, qb.bid, qb.position, qb.correctAnswer 
            FROM quiz q
            JOIN quiz_blanks qb ON q.qid = qb.qid
            WHERE q.qid = ?
            ORDER BY qb.position
        ');
        $stmt->execute([$id]);
        $rows = $stmt->fetchAll();
        
        if (empty($rows)) {
            return null;
        }
        
        
        $firstRow = $rows[0];
        $blanks = [];
        
        foreach ($rows as $row) {
            if ($row['bid']) {  // If there's a blank
                $blanks[] = new QuizBlank(
                    $row['bid'],
                    $row['qid'],
                    $row['position'],
                    $row['correctAnswer']
                );
            }
        }
        
        return new Quiz(
            $firstRow['qid'],
            $firstRow['paragraph'],
            $firstRow['nbBlanks'],
            $firstRow['difficulty'],
            $firstRow['approved'],
            $blanks
        );
    }

    /**
     * Get all quizzes
     * @return Quiz[]
     */
    public static function getAll(?string $difficulty = null, ?int $approved = 1): array
    {
        $pdo = getDB();
        $sql = '
            SELECT q.*, qb.bid, qb.position, qb.correctAnswer 
            FROM quiz q
            JOIN quiz_blanks qb ON q.qid = qb.qid
            WHERE 1=1
        ';
        $params = [];
        
        if ($difficulty !== null) {
            $sql .= ' AND q.difficulty = ?';
            $params[] = $difficulty;
        }
        
        if ($approved !== null) {
            $sql .= ' AND q.approved = ?';
            $params[] = $approved;
        }
        
        $sql .= ' ORDER BY q.qid, qb.position';
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        $quizzes = [];
        $currentQuizId = null;
        $currentQuiz = null;
        $blanks = [];
        
        while ($row = $stmt->fetch()) {
            // New quiz encountered
            if ($currentQuizId !== $row['qid']) {
                // Save previous quiz if exists
                if ($currentQuiz !== null) {
                    $currentQuiz->blanks = $blanks;
                    $quizzes[] = $currentQuiz;
                }
                
                // Start new quiz
                $currentQuizId = $row['qid'];
                $currentQuiz = new Quiz(
                    $row['qid'],
                    $row['paragraph'],
                    $row['nbBlanks'],
                    $row['difficulty'],
                    $row['approved']
                );
                $blanks = [];
            }
            
            // Add blank if exists
            if ($row['bid']) {
                $blanks[] = new QuizBlank(
                    $row['bid'],
                    $row['qid'],
                    $row['position'],
                    $row['correctAnswer']
                );
            }
        }
        
        // Don't forget the last quiz
        if ($currentQuiz !== null) {
            $currentQuiz->blanks = $blanks;
            $quizzes[] = $currentQuiz;
        }
        
        return $quizzes;
    }


   
    public static function save(Quiz $quiz): bool
    {
        $pdo = getDB();
        
        if ($quiz->qid === 0) {
            // Insert new quiz
            $stmt = $pdo->prepare(
                'INSERT INTO quiz (paragraph, nbBlanks, difficulty, approved) VALUES (?, ?, ?, ?)'
            );
            $result = $stmt->execute([
                $quiz->paragraph,
                $quiz->nbBlanks,
                $quiz->difficulty,
                $quiz->approved
            ]);
            
            if ($result) {
                $quiz->qid = (int)$pdo->lastInsertId();
            }
            
            return $result;
        } else {
            // Update existing quiz
            $stmt = $pdo->prepare(
                'UPDATE quiz SET paragraph = ?, nbBlanks = ?, difficulty = ?, approved = ? WHERE qid = ?'
            );
            return $stmt->execute([
                $quiz->paragraph,
                $quiz->nbBlanks,
                $quiz->difficulty,
                $quiz->approved,
                $quiz->qid
            ]);
        }
    }

    /**
     * Delete the quiz from database
     */
    public static function delete(Quiz $quiz): bool
    {
        if ($quiz->qid === 0) {
            return false;
        }
        
        $pdo = getDB();
        $stmt = $pdo->prepare('DELETE FROM quiz WHERE qid = ?');
        return $stmt->execute([$quiz->qid]);
    }

    /**
     * Get all blanks for this quiz
     */
    public static function getBlanks(Quiz $quiz): array
    {
        $pdo = getDB();
        $stmt = $pdo->prepare(
            'SELECT * FROM quiz_blanks WHERE qid = ? ORDER BY position'
        );
        $stmt->execute([$quiz->qid]);
        
        return $stmt->fetchAll();
    }

    /**
     * Prepare data for quiz list
     */
    public static function list(): array
    {
        $quizzes = self::getAll(null, null);
        $success = $_SESSION['success'] ?? '';
        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['success'], $_SESSION['error']);
        
        return [
            'quizzes' => $quizzes,
            'success' => $success,
            'error' => $error
        ];
    }


    public static function add(array $data): ?string
    {
        $paragraph = $data['paragraph'] ?? '';
        $difficulty = $data['difficulty'] ?? 'easy';
        $approved = isset($data['approved']) ? (int)$data['approved'] : 0;
        
        $blanks = self::extractBlanksFromParagraph($paragraph);
        $nbBlanks = count($blanks);
        
        if ($nbBlanks < 3) {
            return 'Erreur : Vous devez ajouter au moins 3 blanks (format: [mot]). Exemple: Le [chat] de mon [voisin] est très [joueur].';
        } elseif ($nbBlanks > 8) {
            return 'Erreur : Vous ne pouvez pas ajouter plus de 8 blanks';
        } else {
            $quiz = new Quiz(0, $paragraph, $nbBlanks, $difficulty, $approved);
            
            if (self::save($quiz)) {
                foreach ($blanks as $index => $answer) {
                    $blank = new QuizBlank(0, $quiz->qid, $index, trim($answer));
                    QuizBlankController::save($blank);
                }
                
                $_SESSION['success'] = 'Quiz créé avec succès';
                header('Location: quiz_list.php');
                exit;
            } else {
                return 'Erreur lors de la création du quiz. Vérifiez que tous les champs sont corrects.';
            }
        }
    }

    
    public static function update(int $id, array $data): ?string
    {
        $quiz = self::getById($id);
        if (!$quiz) {
            return 'Quiz non trouvé';
        }

        $paragraph = $data['paragraph'] ?? '';
        $difficulty = $data['difficulty'] ?? 'easy';
        $approved = isset($data['approved']) ? (int)$data['approved'] : 0;
        
        $blanksArray = self::extractBlanksFromParagraph($paragraph);
        $nbBlanks = count($blanksArray);
        
        if ($nbBlanks < 3) {
            return 'Erreur : Vous devez avoir au moins 3 blanks (format: [mot]). Exemple: Le [chat] de mon [voisin] est très [joueur].';
        } elseif ($nbBlanks > 8) {
            return 'Erreur : Vous ne pouvez pas avoir plus de 8 blanks';
        } else {
            $quiz->paragraph = $paragraph;
            $quiz->nbBlanks = $nbBlanks;
            $quiz->difficulty = $difficulty;
            $quiz->approved = $approved;
            
            if (self::save($quiz)) {
                // Supprimer les anciens blanks
                $pdo = getDB();
                $stmt = $pdo->prepare('DELETE FROM quiz_blanks WHERE qid = ?');
                $stmt->execute([$quiz->qid]);
                foreach ($blanksArray as $index => $answer) {
                    $blank = new QuizBlank(0, $quiz->qid, $index, trim($answer));
                    QuizBlankController::save($blank);
                }
                $_SESSION['success'] = 'Quiz mis à jour avec succès !';
                header('Location: quiz_list.php');
                exit;
            } else {
                return 'Erreur lors de la mise à jour du quiz';
            }
        }
    }

    /**
     * Handle quiz deletion
     */
    public static function remove(int $id): void
    {
        if ($id === 0) {
            $_SESSION['error'] = 'ID de quiz invalide';
            header('Location: quiz_list.php');
            exit;
        }
        
        $quiz = self::getById($id);
        
        if (!$quiz) {
            $_SESSION['error'] = 'Quiz non trouvé';
            header('Location: quiz_list.php');
            exit;
        }
        
        // Suppression du quiz de la base de données
        $pdo = getDB();
        $stmt = $pdo->prepare('DELETE FROM quiz WHERE qid = ?');
        $deleteSuccess = $stmt->execute([$quiz->qid]);
        
        if ($deleteSuccess) {
            $_SESSION['success'] = 'Quiz supprimé avec succès';
        } else {
            $_SESSION['error'] = 'Erreur lors de la suppression du quiz';
        }
        
        header('Location: quiz_list.php');
        exit;
    }

    /**
     * Extrait les blanks d'un paragraphe au format [mot]
     * @return array Tableau des réponses correctes
     */
    public static function extractBlanksFromParagraph(string $paragraph): array
    {
        $blanks = [];
        preg_match_all('/\[([^\]]+)\]/', $paragraph, $matches);
        
        if (!empty($matches[1])) {
            $blanks = $matches[1];
        }
        
        return $blanks;
    }

    /**
     * Formate un quiz avec ses blanks pour l'édition
     * Remplace les placeholders par [mot]
     */
    public static function formatParagraphWithBlanks(Quiz $quiz): string
    {
        $paragraph = $quiz->paragraph;
        $blanks = $quiz->blanks;
        
        // Trier les blanks par position
        usort($blanks, function($a, $b) {
            return $a->position <=> $b->position;
        });
        
        // Remplacer les placeholders dans le texte original
        // Le format original dans la DB est: "La [mère] de mon ami"
        // On doit le garder tel quel pour l'édition
        return $paragraph;
    }
}
