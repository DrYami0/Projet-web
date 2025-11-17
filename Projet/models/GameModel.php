<?php
namespace Projet\Models;

class GameModel {

    private \PDO $db;

    public function __construct()
    {
        global $pdo;
        $this->db = $pdo;
    }

    public function getGameData() {
        
        $gameId = 1;

        try {
            
            $sqlTexts = "SELECT text_content FROM game_text WHERE game_id = :game_id ORDER BY display_order ASC";
            $stmtTexts = $this->db->prepare($sqlTexts);
            $stmtTexts->execute([':game_id' => $gameId]);
            $textRows = $stmtTexts->fetchAll(\PDO::FETCH_ASSOC);

            $text = [];
            foreach ($textRows as $row) {
                $text[] = $row['text_content'];
            }

            $sqlWords = "SELECT id, word_text, correct_order FROM words WHERE game_id = :game_id ORDER BY correct_order ASC";
            $stmtWords = $this->db->prepare($sqlWords);
            $stmtWords->execute([':game_id' => $gameId]);
            $wordsDb = $stmtWords->fetchAll(\PDO::FETCH_ASSOC);

            // Transformer les champs de la BDD dans la structure attendue par la vue
            $words = array_map(function ($row) {
                return [
                    'id'    => (int)$row['id'],
                    'text'  => $row['word_text'],
                    'order' => (int)$row['correct_order'],
                ];
            }, $wordsDb);


            return [
                'text'  => $text,
                'words' => $words,
            ];
        } catch (\Exception $e) {
            return $this->getDefaultGameData();
        }
    }

    

  
    public function checkAnswers($answers) {
        $correct = 0;
        $results = [];
        $total = count($answers);

        foreach ($answers as $answer) {
            $id = isset($answer['id']) ? (int)$answer['id'] : 0;         
            $userOrder = isset($answer['order']) ? (int)$answer['order'] : 0; 

            
            $expectedOrder = null;

            if ($this->db !== null) {
                
                $sql = "SELECT correct_order FROM words WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$id]);
                $expectedOrder = (int)$stmt->fetchColumn();
            }

            
        
            $isCorrect = ($expectedOrder === $userOrder);
            if ($isCorrect) {
                $correct++;
            }

            $results[] = [
                'id'           => $id,
                'userOrder'    => $userOrder,
                'expectedOrder'=> $expectedOrder,
                'correct'      => $isCorrect,
            ];
        }

        return [
            'success' => true,
            'correct' => $correct,
            'total'   => $total,
            'results' => $results,
            'message' => ($correct === $total)
                ? "🎉 Parfait ! Tout est correct."
                : "Bon essai ! {$correct}/{$total} corrects.",
        ];
    
    }
     

    public function deleteGame(int $gameId): bool
    {
        $sql = "DELETE FROM games WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $gameId]);
    }

    public function updateWord(int $id, string $wordText, int $correctOrder, ?bool $isCorrect = null): bool
    {
        $fields = ['word_text = :word_text', 'correct_order = :correct_order'];
        $params = [
            ':id'           => $id,
            ':word_text'    => $wordText,
            ':correct_order'=> $correctOrder,
        ];

        if ($isCorrect !== null) {
            $fields[] = 'is_correct = :is_correct';
            $params[':is_correct'] = $isCorrect ? 1 : 0;
        }

        $sql = 'UPDATE words SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);

        return $stmt->execute($params);
    }

    public function deleteWord(int $id): bool
    {
        $sql = "DELETE FROM words WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
     private function getDefaultGameData() {
        return [
            'text' => [
                'Le petit chat gris dort sur le <span class="blank" data-order="1" data-filled="false"></span> canapé.',
                'Il regarde par la <span class="blank" data-order="2" data-filled="false"></span> fenêtre.',
                'Dehors, les oiseaux <span class="blank" data-order="3" data-filled="false"></span> joyeusement.'
            ],
            'words' => [
                ['id' => 1, 'text' => 'vieux',    'order' => 1],
                ['id' => 2, 'text' => 'petite',   'order' => 2],
                ['id' => 3, 'text' => 'chantent', 'order' => 3],
            ],
        ];
    }
}