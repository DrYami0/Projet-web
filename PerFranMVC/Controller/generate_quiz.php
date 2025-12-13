<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../Service/AIQuizGenerator.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée.');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    
    $theme = $input['theme'] ?? '';
    $nbBlanks = intval($input['nbBlanks'] ?? 0);

    if (empty($theme)) {
        throw new Exception('Le thème est requis.');
    }

    if ($nbBlanks < 3) {
        throw new Exception('Le nombre de blanks doit être d\'au moins 3.');
    }

    $generator = new AIQuizGenerator();
    $paragraph = $generator->generateQuiz($theme, $nbBlanks);

    // Determine difficulty
    $difficulty = 'easy';
    if ($nbBlanks == 4) {
        $difficulty = 'medium';
    } elseif ($nbBlanks > 4) {
        $difficulty = 'hard';
    }

    echo json_encode([
        'success' => true,
        'paragraph' => $paragraph,
        'difficulty' => $difficulty
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
