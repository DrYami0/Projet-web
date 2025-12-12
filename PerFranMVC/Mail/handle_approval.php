<?php
/**
 * Approval Handler
 * Processes admin approval or rejection of quiz suggestions
 */

session_start();
require_once __DIR__ . '/../Model/Quiz.php';
require_once __DIR__ . '/../Model/QuizBlank.php';
require_once __DIR__ . '/../Controller/QuizController.php';
require_once __DIR__ . '/../Controller/QuizBlankController.php';

// Get action and token from URL
$action = $_GET['action'] ?? '';
$token = $_GET['token'] ?? '';

// Validate inputs
if (!in_array($action, ['approve', 'reject']) || empty($token)) {
    showMessage('error', 'Erreur', 'Paramètres invalides.');
    exit;
}

// Load suggestion data
$pendingDir = __DIR__ . '/data/pending_suggestions/';
$filePath = $pendingDir . $token . '.json';

if (!file_exists($filePath)) {
    showMessage('error', 'Erreur', 'Cette suggestion n\'existe plus ou a déjà été traitée.');
    exit;
}

$suggestionData = json_decode(file_get_contents($filePath), true);

if ($action === 'approve') {
    // APPROVE: Save to database
    $result = processSuggestion($suggestionData);
    
    if ($result['success']) {
        // Delete the pending file
        unlink($filePath);
        showMessage('success', 'Quiz Approuvé !', $result['message']);
    } else {
        showMessage('error', 'Erreur', $result['message']);
    }
    
} else {
    // REJECT: Just delete the file
    unlink($filePath);
    showMessage('info', 'Quiz Rejeté', 'La suggestion a été rejetée et supprimée.');
}


function processSuggestion(array $data): array
{
    try {
        $paragraph = $data['paragraph'];
        $difficulty = $data['difficulty'];
        
        // Extract blanks from paragraph
        preg_match_all('/\[([^\]]+)\]/', $paragraph, $matches);
        $blanks = $matches[1];
        $nbBlanks = count($blanks);
        
        // Validate blank count
        if ($nbBlanks < 3) {
            return [
                'success' => false,
                'message' => 'Le quiz doit contenir au moins 3 blanks.'
            ];
        }
        
        if ($nbBlanks > 8) {
            return [
                'success' => false,
                'message' => 'Le quiz ne peut pas contenir plus de 8 blanks.'
            ];
        }
        
        // Create quiz (approved = 1)
        $quiz = new Quiz(0, $paragraph, $nbBlanks, $difficulty, 1);
        
        if (!QuizController::save($quiz)) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la création du quiz.'
            ];
        }
        
        // Save blanks with 1-indexed positions
        foreach ($blanks as $index => $answer) {
            // Position must be 1-indexed (1, 2, 3...) not 0-indexed
            $position = $index + 1;
            $blank = new QuizBlank(0, $quiz->qid, $position, trim($answer));
            if (!QuizBlankController::save($blank)) {
                return [
                    'success' => false,
                    'message' => 'Erreur lors de la création des blanks.'
                ];
            }
        }
        
        // Save intruders (position 0)
        if (!empty($data['intruder_words'])) {
            $intruders = array_map('trim', explode(',', $data['intruder_words']));
            foreach ($intruders as $intruder) {
                if (!empty($intruder)) {
                    $blank = new QuizBlank(0, $quiz->qid, 0, $intruder);
                    QuizBlankController::save($blank);
                }
            }
        }
        
        return [
            'success' => true,
            'message' => 'Le quiz a été approuvé et ajouté à la base de données avec succès ! (Quiz ID: ' . $quiz->qid . ')'
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Erreur système : ' . $e->getMessage()
        ];
    }
}

/**
 * Show message to admin
 */
function showMessage(string $type, string $title, string $message): void
{
    // Color scheme based on message type
    $colors = [
        'success' => ['bg' => '#27ae60', 'icon' => '✅'],
        'error' => ['bg' => '#e74c3c', 'icon' => '❌'],
        'info' => ['bg' => '#3498db', 'icon' => 'ℹ️']
    ];
    
    $color = $colors[$type] ?? $colors['info'];
    
    echo "
    <!DOCTYPE html>
    <html lang='fr'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>$title</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            
            .container {
                background: white;
                max-width: 600px;
                width: 100%;
                border-radius: 16px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                overflow: hidden;
                animation: slideIn 0.4s ease-out;
            }
            
            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(-30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .header {
                background: {$color['bg']};
                color: white;
                padding: 40px;
                text-align: center;
            }
            
            .icon {
                font-size: 64px;
                margin-bottom: 20px;
                animation: bounce 1s ease-in-out;
            }
            
            @keyframes bounce {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-10px); }
            }
            
            h1 {
                font-size: 28px;
                font-weight: 700;
                margin-bottom: 10px;
            }
            
            .content {
                padding: 40px;
                text-align: center;
            }
            
            .message {
                font-size: 18px;
                color: #2c3e50;
                line-height: 1.6;
                margin-bottom: 30px;
            }
            
            .button {
                display: inline-block;
                background: {$color['bg']};
                color: white;
                padding: 14px 32px;
                border-radius: 8px;
                text-decoration: none;
                font-size: 16px;
                font-weight: 600;
                transition: all 0.3s;
            }
            
            .button:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <div class='icon'>{$color['icon']}</div>
                <h1>$title</h1>
            </div>
            <div class='content'>
                <p class='message'>$message</p>
                <a href='../View/BackOffice/quiz_list.php' class='button'>Voir la liste des quiz</a>
            </div>
        </div>
    </body>
    </html>
    ";
}
