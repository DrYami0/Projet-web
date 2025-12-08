<?php
/**
 * Test Approval System
 * Shows all pending suggestions with approve/reject buttons
 */
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test - Approbation de Quiz</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
            margin: 0;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        h1 {
            color: white;
            text-align: center;
            margin-bottom: 30px;
        }
        .suggestion {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        .quiz-text {
            background: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #3498db;
            margin: 15px 0;
            font-size: 16px;
        }
        .blank {
            background: #d6eaf8;
            color: #2980b9;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 600;
        }
        .actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            color: white;
            transition: all 0.3s;
        }
        .btn-approve {
            background: #27ae60;
        }
        .btn-approve:hover {
            background: #219150;
        }
        .btn-reject {
            background: #e74c3c;
        }
        .btn-reject:hover {
            background: #c0392b;
        }
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-easy { background: #27ae60; color: white; }
        .badge-medium { background: #f39c12; color: white; }
        .badge-hard { background: #e74c3c; color: white; }
        .info {
            color: #7f8c8d;
            font-size: 14px;
            margin-top: 10px;
        }
        .no-suggestions {
            background: white;
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📋 Suggestions en Attente</h1>
        
        <?php
        $pendingDir = __DIR__ . '/Mail/data/pending_suggestions/';
        $files = glob($pendingDir . '*.json');
        
        if (empty($files)) {
            echo '<div class="no-suggestions">';
            echo '<h2>Aucune suggestion en attente</h2>';
            echo '<p>Toutes les suggestions ont été traitées!</p>';
            echo '</div>';
        } else {
            foreach ($files as $file) {
                $data = json_decode(file_get_contents($file), true);
                $token = $data['token'];
                
                echo '<div class="suggestion">';
                
                // Difficulty badge
                $badgeClass = 'badge-' . $data['difficulty'];
                echo '<span class="badge ' . $badgeClass . '">' . $data['difficulty'] . '</span>';
                
                // Quiz text with blanks highlighted
                echo '<div class="quiz-text">';
                $text = $data['paragraph'];
                $text = preg_replace('/\[([^\]]+)\]/', '<span class="blank">[$1]</span>', $text);
                echo $text;
                echo '</div>';
                
                // Intruders
                if (!empty($data['intruder_words'])) {
                    echo '<p><strong>Mots intrus:</strong> ' . htmlspecialchars($data['intruder_words']) . '</p>';
                }
                
                // Info
                echo '<div class="info">Soumis le: ' . $data['submitted_at'] . '</div>';
                
                // Actions
                echo '<div class="actions">';
                echo '<a href="Mail/handle_approval.php?action=approve&token=' . $token . '" class="btn btn-approve">✅ Approuver</a>';
                echo '<a href="Mail/handle_approval.php?action=reject&token=' . $token . '" class="btn btn-reject">❌ Rejeter</a>';
                echo '</div>';
                
                echo '</div>';
            }
        }
        ?>
    </div>
</body>
</html>
