<?php
require_once __DIR__ . '/../Model/Quiz.php';
require_once __DIR__ . '/../Model/QuizBlank.php';

class QuizBlankController
{
    // Liste tous les blanks d'un quiz
    public function blank_list(): void
    {
        $quizId = $_GET['quiz_id'] ?? 0;
        $quiz = Quiz::getById((int)$quizId);
        
        if (!$quiz) {
            header('HTTP/1.0 404 Not Found');
            die('Quiz non trouvé');
        }
        
        $blanks = QuizBlank::getByQuizId($quiz->qid);
        include __DIR__ . '/../View/BackOffice/blank_list.php';
    }

    // Ajoute un nouveau blank
    public function blank_add(): void
    {
        $quizId = $_GET['quiz_id'] ?? 0;
        $quiz = Quiz::getById((int)$quizId);
        
        if (!$quiz) {
            header('HTTP/1.0 404 Not Found');
            die('Quiz non trouvé');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $position = (int)($_POST['position'] ?? 0);
            $correctAnswer = $_POST['correctAnswer'] ?? '';
            
            $blank = new QuizBlank(0, $quiz->qid, $position, $correctAnswer);
            if ($blank->save()) {
                header("Location: blank_list.php?quiz_id={$quiz->qid}");
                exit;
            }
        }
        
        include __DIR__ . '/../View/BackOffice/blank_add.php';
    }

    // Supprime un blank
    public function blank_delete(): void
    {
        session_start();
        $blankId = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        $blank = QuizBlank::getById($blankId);
        
        if (!$blank) {
            $_SESSION['error'] = 'Blank non trouvé';
            header('Location: quiz_list.php');
            exit;
        }
        
        $quizId = $blank->qid;
        $correctAnswer = $blank->correctAnswer;
        
        // Récupérer le quiz
        $quiz = Quiz::getById($quizId);
        
        // Sauvegarder les données pour annulation possible (avec le texte original)
        $_SESSION['deleted_blank'] = [
            'bid' => $blank->bid,
            'qid' => $blank->qid,
            'position' => $blank->position,
            'correctAnswer' => $blank->correctAnswer,
            'original_paragraph' => $quiz->paragraph // Sauvegarder le texte original
        ];
        if (!$quiz) {
            $_SESSION['error'] = 'Quiz non trouvé';
            header("Location: blank_list.php?quiz_id={$quizId}");
            exit;
        }
        
        // Retirer le blank du paragraphe (format: [mot])
        // On doit trouver le blank à la bonne position pour éviter de supprimer le mauvais
        $paragraph = $quiz->paragraph;
        
        // Extraire tous les blanks du paragraphe avec leurs positions
        preg_match_all('/\[([^\]]+)\]/', $paragraph, $matches, PREG_OFFSET_CAPTURE);
        $blanksInText = $matches[0] ?? [];
        $answersInText = $matches[1] ?? [];
        
        // Trouver le blank à supprimer selon sa position
        $blankToRemove = null;
        $blankIndex = 0;
        foreach ($answersInText as $index => $answerMatch) {
            if ($blankIndex == $blank->position && $answerMatch[0] == $correctAnswer) {
                $blankToRemove = $blanksInText[$index];
                break;
            }
            $blankIndex++;
        }
        
        // Si on n'a pas trouvé par position, chercher par texte exact
        if (!$blankToRemove) {
            foreach ($blanksInText as $index => $blankMatch) {
                if ($answersInText[$index][0] == $correctAnswer) {
                    $blankToRemove = $blankMatch;
                    break;
                }
            }
        }
        
        // Remplacer [mot] par mot (garder le mot sans les crochets)
        if ($blankToRemove) {
            $blankText = $blankToRemove[0]; // [mot]
            $wordWithoutBrackets = $correctAnswer; // mot
            // Remplacer [mot] par mot dans le paragraphe
            $paragraph = str_replace($blankText, $wordWithoutBrackets, $paragraph);
        }
        
        // Mettre à jour le nombre de blanks
        preg_match_all('/\[([^\]]+)\]/', $paragraph, $matches);
        $nbBlanks = count($matches[1] ?? []);
        
        // Mettre à jour le quiz
        $quiz->paragraph = $paragraph;
        $quiz->nbBlanks = $nbBlanks;
        
        // Supprimer le blank de la base de données
        if ($blank->delete() && $quiz->save()) {
            $_SESSION['success'] = 'Blank supprimé avec succès. <a href="blank_restore.php" style="color: #155724; text-decoration: underline; font-weight: bold;">Annuler la suppression</a>';
        } else {
            $_SESSION['error'] = 'Erreur lors de la suppression du blank';
            unset($_SESSION['deleted_blank']);
        }
        
        header("Location: blank_list.php?quiz_id={$quizId}");
        exit;
    }
    
    // Restaure un blank supprimé
    public function blank_restore(): void
    {
        session_start();
        
        if (!isset($_SESSION['deleted_blank'])) {
            $_SESSION['error'] = 'Aucune suppression à restaurer';
            header('Location: quiz_list.php');
            exit;
        }
        
        $deletedBlank = $_SESSION['deleted_blank'];
        $quizId = $deletedBlank['qid'];
        
        // Récupérer le quiz
        $quiz = Quiz::getById($quizId);
        if (!$quiz) {
            $_SESSION['error'] = 'Quiz non trouvé';
            unset($_SESSION['deleted_blank']);
            header('Location: quiz_list.php');
            exit;
        }
        
        // Restaurer le paragraphe original (avec le [mot] inclus)
        if (isset($deletedBlank['original_paragraph'])) {
            // Utiliser le texte original qui contient déjà le blank
            $paragraph = $deletedBlank['original_paragraph'];
        } else {
            // Fallback : remplacer le mot par [mot] dans le texte actuel
            $paragraph = $quiz->paragraph;
            $wordToRestore = $deletedBlank['correctAnswer'];
            $blankText = '[' . $wordToRestore . ']';
            
            // Chercher le mot dans le texte et le remplacer par [mot]
            // Utiliser un pattern pour trouver le mot entier (pas seulement une partie)
            // Remplacer le mot seulement s'il n'est pas déjà entre crochets
            $pattern = '/\b' . preg_quote($wordToRestore, '/') . '\b/';
            // Compter combien de fois on a déjà remplacé pour éviter de remplacer plusieurs fois
            $replacementCount = 0;
            $paragraph = preg_replace_callback($pattern, function($matches) use ($blankText, &$replacementCount) {
                $replacementCount++;
                // Remplacer seulement la première occurrence trouvée
                if ($replacementCount === 1) {
                    return $blankText;
                }
                return $matches[0];
            }, $paragraph, 1);
        }
        
        // Mettre à jour le nombre de blanks
        preg_match_all('/\[([^\]]+)\]/', $paragraph, $matches);
        $nbBlanks = count($matches[1] ?? []);
        
        // Restaurer le blank dans la base de données AVANT de mettre à jour le quiz
        $blank = new QuizBlank(
            0, // Nouvel ID (la base générera un nouvel ID)
            $deletedBlank['qid'],
            $deletedBlank['position'],
            $deletedBlank['correctAnswer']
        );
        
        // Sauvegarder le blank d'abord
        if (!$blank->save()) {
            $_SESSION['error'] = 'Erreur lors de la restauration du blank dans la base de données';
            header("Location: blank_list.php?quiz_id={$quizId}");
            exit;
        }
        
        // Mettre à jour le quiz avec le texte restauré
        $quiz->paragraph = $paragraph;
        $quiz->nbBlanks = $nbBlanks;
        
        if ($quiz->save()) {
            $_SESSION['success'] = 'Blank restauré avec succès ! Le blank est de retour dans le texte (entre [ ]), dans la base de données et dans le tableau.';
            unset($_SESSION['deleted_blank']);
        } else {
            $_SESSION['error'] = 'Erreur lors de la mise à jour du quiz';
            // Si le quiz n'a pas pu être mis à jour, supprimer le blank qu'on vient de créer
            $blank->delete();
        }
        
        header("Location: blank_list.php?quiz_id={$quizId}");
        exit;
    }
}
