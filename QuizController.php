<?php
require_once __DIR__ . '/../Model/Quiz.php';
require_once __DIR__ . '/../Model/QuizBlank.php';

class QuizController
{
    /**
     * Liste tous les quiz
     */
    public function list(): void
    {
        $quizzes = Quiz::getAll(null, null); // Tous les quiz, approuvés ou non
        include __DIR__ . '/../View/BackOffice/quiz_list.php';
    }

    /**
     * Affiche le formulaire d'ajout d'un quiz
     * Note: La logique est maintenant directement dans quiz_add.php pour éviter les boucles infinies
     */
    public function add(): void
    {
        // Cette méthode n'est plus utilisée directement
        // La logique est dans quiz_add.php
        include __DIR__ . '/../View/BackOffice/quiz_add.php';
    }

    /**
     * Affiche le formulaire d'édition d'un quiz
     */
    public function edit(): void
    {
        $id = $_GET['id'] ?? 0;
        $quiz = Quiz::getById((int)$id);
        
        if (!$quiz) {
            header('HTTP/1.0 404 Not Found');
            die('Quiz non trouvé');
        }

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $paragraph = $_POST['paragraph'] ?? '';
            $difficulty = $_POST['difficulty'] ?? 'easy';
            $approved = isset($_POST['approved']) ? (int)$_POST['approved'] : 0;
            
            // Extraire les blanks du paragraphe (format: [mot])
            $blanks = $this->extractBlanksFromParagraph($paragraph);
            $nbBlanks = count($blanks);
            
            // Valider que le nombre de blanks est entre 3 et 8 (contrainte de la base)
            if ($nbBlanks < 3) {
                $error = 'Erreur : Vous devez avoir au moins 3 blanks (format: [mot]). Exemple: Le [chat] de mon [voisin] est très [joueur].';
            } elseif ($nbBlanks > 8) {
                $error = 'Erreur : Vous ne pouvez pas avoir plus de 8 blanks';
            } else {
                // Mettre à jour le quiz
                $quiz->paragraph = $paragraph;
                $quiz->nbBlanks = $nbBlanks;
                $quiz->difficulty = $difficulty;
                $quiz->approved = $approved;
                
                if ($quiz->save()) {
                    // Supprimer les anciens blanks
                    QuizBlank::deleteByQuizId($quiz->qid);
                    
                    // Ajouter les nouveaux blanks (y compris les nouveaux ajoutés)
                    foreach ($blanks as $index => $answer) {
                        $blank = new QuizBlank(0, $quiz->qid, $index, trim($answer));
                        $blank->save();
                    }
                    
                    // Mise à jour réussie - rester sur la page
                    $success = 'Quiz mis à jour avec succès !';
                    $error = ''; // Pas d'erreur
                    // Recharger le quiz pour afficher les données mises à jour
                    $quiz = Quiz::getById($quiz->qid);
                } else {
                    $error = 'Erreur lors de la mise à jour du quiz';
                }
            }
        }
        
        // Le paragraphe est déjà au format [mot] dans la base de données
        $paragraph = $quiz->paragraph;
        
        // Passer les variables à la vue ($error, $paragraph, $quiz sont déjà définis)
        include __DIR__ . '/../View/BackOffice/quiz_edit.php';
    }

    /**
     * Supprime un quiz
     */
    public function delete(): void
    {
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        
        if ($id === 0) {
            $_SESSION['error'] = 'ID de quiz invalide';
            header('Location: quiz_list.php');
            exit;
        }
        
        $quiz = Quiz::getById($id);
        
        if (!$quiz) {
            $_SESSION['error'] = 'Quiz non trouvé';
            header('Location: quiz_list.php');
            exit;
        }
        
        if ($quiz->delete()) {
            // Les blanks seront supprimés automatiquement grâce à ON DELETE CASCADE
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
    private function extractBlanksFromParagraph(string $paragraph): array
    {
        $blanks = [];
        // Pattern pour trouver [mot]
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
    private function formatParagraphWithBlanks(Quiz $quiz): string
    {
        $paragraph = $quiz->paragraph;
        $blanks = QuizBlank::getByQuizId($quiz->qid);
        
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
