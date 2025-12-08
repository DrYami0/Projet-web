<?php
require_once __DIR__ . '/../Model/Quiz.php';
require_once __DIR__ . '/../Model/QuizBlank.php';
require_once __DIR__ . '/../../database/config.php';

class QuizBlankController
{
 
    public static function getById(int $id): ?QuizBlank
    {
        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT * FROM quiz_blanks WHERE bid = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        
        if (!$row) {
            return null;
        }
        
        return new QuizBlank(
            $row['bid'],
            $row['qid'],
            $row['position'],
            $row['correctAnswer']
        );
    }

   


    public static function save(QuizBlank $blank): bool
    {
        $pdo = getDB();
        
        if ($blank->bid === 0) {

            $stmt = $pdo->prepare(
                'INSERT INTO quiz_blanks (qid, position, correctAnswer) VALUES (?, ?, ?)'
            );
            $result = $stmt->execute([
                $blank->qid,
                $blank->position,
                $blank->correctAnswer
            ]);
            
            if ($result) {
                $blank->bid = (int)$pdo->lastInsertId();
            }
            
            return $result;
        } else {
            // Update existing blank
            $stmt = $pdo->prepare(
                'UPDATE quiz_blanks SET qid = ?, position = ?, correctAnswer = ? WHERE bid = ?'
            );
            return $stmt->execute([
                $blank->qid,
                $blank->position,
                $blank->correctAnswer,
                $blank->bid
            ]);
        }
    }




    public static function list(int $quizId): array
    {
        $quiz = QuizController::getById($quizId);
        
        if (!$quiz) {
            header('HTTP/1.0 404 Not Found');
            die('Quiz non trouvé');
        }
        
        $blanks = $quiz->blanks;
        usort($blanks, function($a, $b) {
            return $a->position <=> $b->position;
        });
        
        $success = $_SESSION['success'] ?? '';
        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['success'], $_SESSION['error']);
        
        return [
            'quiz' => $quiz,
            'blanks' => $blanks,
            'success' => $success,
            'error' => $error
        ];
    }

    public static function create(int $quizId, array $data): ?string
    {
        $quiz = QuizController::getById($quizId);
        if (!$quiz) {
            return 'Quiz non trouvé';
        }

        $position = (int)($data['position'] ?? 0);
        $correctAnswer = $data['correctAnswer'] ?? '';
        
        // Si la position est 0 (cas de l'ajout d'intrus), on la laisse à 0
        // Sinon on ajuste (0-indexed)
        if (isset($data['position'])) {
            $position = max(0, $position - 1);
        } else {
            $position = 0;
        }
        
        $blank = new QuizBlank(0, $quiz->qid, $position, $correctAnswer);
        if (self::save($blank)) {
            $_SESSION['success'] = 'Blank ajouté avec succès';
            header("Location: blank_list.php?quiz_id={$quiz->qid}");
            exit;
        }
        return 'Erreur lors de l\'ajout du blank';
    }

    /**
     * Handle blank deletion
     */
    public static function remove(): void
    {
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        $blank = self::getById($id);
        
        if (!$blank) {
            $_SESSION['error'] = 'Blank non trouvé';
            header('Location: quiz_list.php');
            exit;
        }
        
        $quizId = $blank->qid;
        $correctAnswer = $blank->correctAnswer;
        $quiz = QuizController::getById($quizId);
        
        if (!$quiz) {
            $_SESSION['error'] = 'Quiz non trouvé';
            header("Location: blank_list.php?quiz_id={$quizId}");
            exit;
        }

        $_SESSION['deleted_blank'] = [
            'bid' => $blank->bid,
            'qid' => $blank->qid,
            'position' => $blank->position,
            'correctAnswer' => $blank->correctAnswer,
            'original_paragraph' => $quiz->paragraph
        ];
        
        $paragraph = $quiz->paragraph;
        preg_match_all('/\[([^\]]+)\]/', $paragraph, $matches, PREG_OFFSET_CAPTURE);
        $blanksInText = $matches[0] ?? [];
        $answersInText = $matches[1] ?? [];
        
        $blankToRemove = null;
        $blankIndex = 0;
        foreach ($answersInText as $index => $answerMatch) {
            if ($blankIndex == $blank->position && $answerMatch[0] == $correctAnswer) {
                $blankToRemove = $blanksInText[$index];
                break;
            }
            $blankIndex++;
        }
        
        if (!$blankToRemove) {
            foreach ($blanksInText as $index => $blankMatch) {
                if ($answersInText[$index][0] == $correctAnswer) {
                    $blankToRemove = $blankMatch;
                    break;
                }
            }
        }
        
        if ($blankToRemove) {
            $blankText = $blankToRemove[0];
            $wordWithoutBrackets = $correctAnswer;
            $paragraph = str_replace($blankText, $wordWithoutBrackets, $paragraph);
        }
        
        preg_match_all('/\[([^\]]+)\]/', $paragraph, $matches);
        $nbBlanks = count($matches[1] ?? []);
        
        $quiz->paragraph = $paragraph;
        $quiz->nbBlanks = $nbBlanks;
        
        if ($nbBlanks < 3) {
            $_SESSION['error'] = 'Impossible de supprimer ce blank : un quiz doit contenir au moins 3 blanks.';
            header("Location: blank_list.php?quiz_id={$quizId}");
            exit;
        }
        

        if ($blank->bid === 0) {
            $_SESSION['error'] = 'ID de blank invalide';
            header("Location: blank_list.php?quiz_id={$quizId}");
            exit;
        }
        
        $pdo = getDB();
        $stmt = $pdo->prepare('DELETE FROM quiz_blanks WHERE bid = ?');
        $deleteSuccess = $stmt->execute([$blank->bid]);
        
        if ($deleteSuccess && QuizController::save($quiz)) {
            $restoreUrl = "blank_restore.php";
            $_SESSION['success'] = 'Blank supprimé avec succès. <a href="' . $restoreUrl . '" style="color: #155724; text-decoration: underline; font-weight: bold;">Annuler la suppression</a>';
        } else {
            $_SESSION['error'] = 'Erreur lors de la suppression du blank';
            unset($_SESSION['deleted_blank']);
        }
        
        header("Location: blank_list.php?quiz_id={$quizId}");
        exit;
    }

    /**
     * Handle blank restoration
     */
    public static function restore(array $sessionData): void
    {
        if (empty($sessionData)) {
            $_SESSION['error'] = 'Aucune suppression à restaurer';
            header('Location: quiz_list.php');
            exit;
        }
        
        $quizId = $sessionData['qid'];
        $quiz = QuizController::getById($quizId);
        
        if (!$quiz) {
            $_SESSION['error'] = 'Quiz non trouvé';
            unset($_SESSION['deleted_blank']);
            header('Location: quiz_list.php');
            exit;
        }
        
        if (isset($sessionData['original_paragraph'])) {
            $paragraph = $sessionData['original_paragraph'];
        } else {
            $paragraph = $quiz->paragraph;
            $wordToRestore = $sessionData['correctAnswer'];
            $blankText = '[' . $wordToRestore . ']';
            $pattern = '/\b' . preg_quote($wordToRestore, '/') . '\b/';
            $replacementCount = 0;
            $paragraph = preg_replace_callback($pattern, function($matches) use ($blankText, &$replacementCount) {
                $replacementCount++;
                if ($replacementCount === 1) {
                    return $blankText;
                }
                return $matches[0];
            }, $paragraph, 1);
        }
        
        preg_match_all('/\[([^\]]+)\]/', $paragraph, $matches);
        $nbBlanks = count($matches[1] ?? []);
        
        $blank = new QuizBlank(
            0,
            $sessionData['qid'],
            $sessionData['position'],
            $sessionData['correctAnswer']
        );
        
        if (!self::save($blank)) {
            $_SESSION['error'] = 'Erreur lors de la restauration du blank dans la base de données';
            header("Location: blank_list.php?quiz_id={$quizId}");
            exit;
        }
        
        $quiz->paragraph = $paragraph;
        $quiz->nbBlanks = $nbBlanks;
        
        if (QuizController::save($quiz)) {
            $_SESSION['success'] = 'Blank restauré avec succès ! Le blank est de retour dans le texte (entre [ ]), dans la base de données et dans le tableau.';
            unset($_SESSION['deleted_blank']);
        } else {
            $_SESSION['error'] = 'Erreur lors de la mise à jour du quiz';
            
            
            $pdo = getDB();
            $stmt = $pdo->prepare('DELETE FROM quiz_blanks WHERE bid = ?');
            $stmt->execute([$blank->bid]);
        }
        
        header("Location: blank_list.php?quiz_id={$quizId}");
        exit;
    }

  
    public static function edit(int $id, array $data): void
    {
        $blank = self::getById($id);
        
        if (!$blank) {
            $_SESSION['error'] = 'Blank non trouvé';
            header('Location: quiz_list.php');
            exit;
        }
        
        $quizId = $blank->qid;
        $quiz = QuizController::getById($quizId);
        
        if (!$quiz) {
            $_SESSION['error'] = 'Quiz non trouvé';
            header("Location: blank_list.php?quiz_id={$quizId}");
            exit;
        }

        $newCorrectAnswer = trim($data['correctAnswer'] ?? '');
        
        if (empty($newCorrectAnswer)) {
            $_SESSION['error'] = 'La réponse correcte ne peut pas être vide';
            header("Location: blank_edit.php?id={$id}");
            exit;
        }

        $oldCorrectAnswer = $blank->correctAnswer;
        $paragraph = $quiz->paragraph;
        
        // Find and replace the old blank with the new one in the paragraph
        preg_match_all('/\[([^\]]+)\]/', $paragraph, $matches, PREG_OFFSET_CAPTURE);
        $blanksInText = $matches[0] ?? [];
        $answersInText = $matches[1] ?? [];
        
        $blankToUpdate = null;
        $blankIndex = 0;
        foreach ($answersInText as $index => $answerMatch) {
            if ($blankIndex == $blank->position && $answerMatch[0] == $oldCorrectAnswer) {
                $blankToUpdate = $blanksInText[$index];
                break;
            }
            $blankIndex++;
        }
        
        // If not found by position, search by exact text
        if (!$blankToUpdate) {
            foreach ($blanksInText as $index => $blankMatch) {
                if ($answersInText[$index][0] == $oldCorrectAnswer) {
                    $blankToUpdate = $blankMatch;
                    break;
                }
            }
        }
        
        if ($blankToUpdate) {
            $oldBlankText = $blankToUpdate[0]; // [oldword]
            $newBlankText = '[' . $newCorrectAnswer . ']';
            $paragraph = str_replace($oldBlankText, $newBlankText, $paragraph);
        }
        
        // Update the blank in database
        $blank->correctAnswer = $newCorrectAnswer;
        
        // Update quiz paragraph
        $quiz->paragraph = $paragraph;
        
        if (self::save($blank) && QuizController::save($quiz)) {
            $_SESSION['success'] = 'Blank modifié avec succès';
        } else {
            $_SESSION['error'] = 'Erreur lors de la modification du blank';
        }
        
        header("Location: blank_list.php?quiz_id={$quizId}");
        exit;
    }
}
