<?php
require_once __DIR__ . '/../../database/config.php';

class QuizBlank
{
    public int $bid;
    public int $qid;
    public int $position;
    public string $correctAnswer;

    public function __construct(int $bid = 0, int $qid = 0, int $position = 0, string $correctAnswer = '')
    {
        $this->bid = $bid;
        $this->qid = $qid;
        $this->position = $position;
        $this->correctAnswer = $correctAnswer;
    }

    /**
     * Get a blank by ID
     */
    public static function getById(int $id): ?self
    {
        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT * FROM quiz_blanks WHERE bid = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        
        if (!$row) {
            return null;
        }
        
        return new self(
            $row['bid'],
            $row['qid'],
            $row['position'],
            $row['correctAnswer']
        );
    }

    /**
     * Get all blanks for a quiz
     * @return QuizBlank[]
     */
    public static function getByQuizId(int $qid): array
    {
        $pdo = getDB();
        $stmt = $pdo->prepare(
            'SELECT * FROM quiz_blanks WHERE qid = ? ORDER BY position'
        );
        $stmt->execute([$qid]);
        
        $blanks = [];
        while ($row = $stmt->fetch()) {
            $blanks[] = new self(
                $row['bid'],
                $row['qid'],
                $row['position'],
                $row['correctAnswer']
            );
        }
        
        return $blanks;
    }

    /**
     * Save the blank to database (create or update)
     */
    public function save(): bool
    {
        $pdo = getDB();
        
        if ($this->bid === 0) {
            // Insert new blank
            $stmt = $pdo->prepare(
                'INSERT INTO quiz_blanks (qid, position, correctAnswer) VALUES (?, ?, ?)'
            );
            $result = $stmt->execute([
                $this->qid,
                $this->position,
                $this->correctAnswer
            ]);
            
            if ($result) {
                $this->bid = (int)$pdo->lastInsertId();
            }
            
            return $result;
        } else {
            // Update existing blank
            $stmt = $pdo->prepare(
                'UPDATE quiz_blanks SET qid = ?, position = ?, correctAnswer = ? WHERE bid = ?'
            );
            return $stmt->execute([
                $this->qid,
                $this->position,
                $this->correctAnswer,
                $this->bid
            ]);
        }
    }

    /**
     * Delete the blank from database
     */
    public function delete(): bool
    {
        if ($this->bid === 0) {
            return false;
        }
        
        $pdo = getDB();
        $stmt = $pdo->prepare('DELETE FROM quiz_blanks WHERE bid = ?');
        return $stmt->execute([$this->bid]);
    }

    /**
     * Delete all blanks for a quiz
     */
    public static function deleteByQuizId(int $qid): bool
    {
        $pdo = getDB();
        $stmt = $pdo->prepare('DELETE FROM quiz_blanks WHERE qid = ?');
        return $stmt->execute([$qid]);
    }
}
