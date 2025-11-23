<?php
require_once __DIR__ . '/../../database/config.php';

class Quiz
{
    public int $qid;
    public string $paragraph;
    public int $nbBlanks;
    public string $difficulty;
    public ?int $approved;

    public function __construct(int $qid = 0, string $paragraph = '', int $nbBlanks = 0, string $difficulty = 'easy', ?int $approved = null)
    {
        $this->qid = $qid;
        $this->paragraph = $paragraph;
        $this->nbBlanks = $nbBlanks;
        $this->difficulty = $difficulty;
        $this->approved = $approved;
    }

    /**
     * Get a quiz by ID
     */
    public static function getById(int $id): ?self
    {
        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT * FROM quiz WHERE qid = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        
        if (!$row) {
            return null;
        }
        
        return new self(
            $row['qid'],
            $row['paragraph'],
            $row['nbBlanks'],
            $row['difficulty'],
            $row['approved']
        );
    }

    /**
     * Get all quizzes
     * @return Quiz[]
     */
    public static function getAll(?string $difficulty = null, ?int $approved = 1): array
    {
        $pdo = getDB();
        $sql = 'SELECT * FROM quiz WHERE 1=1';
        $params = [];
        
        if ($difficulty !== null) {
            $sql .= ' AND difficulty = ?';
            $params[] = $difficulty;
        }
        
        if ($approved !== null) {
            $sql .= ' AND approved = ?';
            $params[] = $approved;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        $quizzes = [];
        while ($row = $stmt->fetch()) {
            $quizzes[] = new self(
                $row['qid'],
                $row['paragraph'],
                $row['nbBlanks'],
                $row['difficulty'],
                $row['approved']
            );
        }
        
        return $quizzes;
    }

    /**
     * Save the quiz to database (create or update)
     */
    public function save(): bool
    {
        $pdo = getDB();
        
        if ($this->qid === 0) {
            // Insert new quiz
            $stmt = $pdo->prepare(
                'INSERT INTO quiz (paragraph, nbBlanks, difficulty, approved) VALUES (?, ?, ?, ?)'
            );
            $result = $stmt->execute([
                $this->paragraph,
                $this->nbBlanks,
                $this->difficulty,
                $this->approved
            ]);
            
            if ($result) {
                $this->qid = (int)$pdo->lastInsertId();
            }
            
            return $result;
        } else {
            // Update existing quiz
            $stmt = $pdo->prepare(
                'UPDATE quiz SET paragraph = ?, nbBlanks = ?, difficulty = ?, approved = ? WHERE qid = ?'
            );
            return $stmt->execute([
                $this->paragraph,
                $this->nbBlanks,
                $this->difficulty,
                $this->approved,
                $this->qid
            ]);
        }
    }

    /**
     * Delete the quiz from database
     */
    public function delete(): bool
    {
        if ($this->qid === 0) {
            return false;
        }
        
        $pdo = getDB();
        $stmt = $pdo->prepare('DELETE FROM quiz WHERE qid = ?');
        return $stmt->execute([$this->qid]);
    }

    /**
     * Get all blanks for this quiz
     */
    public function getBlanks(): array
    {
        $pdo = getDB();
        $stmt = $pdo->prepare(
            'SELECT * FROM quiz_blanks WHERE qid = ? ORDER BY position'
        );
        $stmt->execute([$this->qid]);
        
        return $stmt->fetchAll();
    }

    /**
     * Add a blank to this quiz
     */
    public function addBlank(int $position, string $correctAnswer): bool
    {
        $pdo = getDB();
        $stmt = $pdo->prepare(
            'INSERT INTO quiz_blanks (qid, position, correctAnswer) VALUES (?, ?, ?)'
        );
        return $stmt->execute([$this->qid, $position, $correctAnswer]);
    }

    /**
     * Get a random quiz of specified difficulty
     */
    public static function getRandom(string $difficulty = 'easy'): ?self
    {
        $pdo = getDB();
        $stmt = $pdo->prepare(
            'SELECT qid FROM quiz WHERE difficulty = ? AND approved = 1 ORDER BY RAND() LIMIT 1'
        );
        $stmt->execute([$difficulty]);
        $row = $stmt->fetch();
        
        return $row ? self::getById($row['qid']) : null;
    }
}
