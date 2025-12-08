<?php
session_start();
require_once __DIR__ . '/../../Model/Quiz.php';
require_once __DIR__ . '/../../Model/QuizBlank.php';
require_once __DIR__ . '/../../Controller/QuizController.php';
require_once __DIR__ . '/../../Controller/QuizBlankController.php';

$quizId = $_GET['quiz_id'] ?? 0;
$quiz = QuizController::getById((int)$quizId);
$error = '';

if (!$quiz) {
    header('HTTP/1.0 404 Not Found');
    die('Quiz non trouvé');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = QuizBlankController::create($quizId, $_POST);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un blank - PerFran</title>
    <link rel="stylesheet" href="../FrontOffice/assets/css/bootstrap.css">
    <link rel="stylesheet" href="../FrontOffice/assets/css/backoffice.css">
    <style>
        * {
            box-sizing: border-box;
        }
        
        body {
            margin: 0;
            background: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .header-custom {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-content {
            max-width: 800px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .header-content img {
            height: 50px;
            width: auto;
        }
        
        .header-content h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .header-content p {
            margin: 0.25rem 0 0;
            opacity: 0.9;
            font-size: 0.95rem;
        }
        
        .container-custom {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .error-alert {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            border-left: 4px solid #dc3545;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .error-alert::before {
            content: "⚠";
            font-size: 1.5rem;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            padding: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.95rem;
        }
        
        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        
        .form-group input[type="number"] {
            max-width: 200px;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e1e8ed;
        }
        
        .btn-custom {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }
        
        .btn-secondary {
            background: #f8f9fa;
            color: #2c3e50;
            border: 2px solid #e1e8ed;
        }
        
        .btn-secondary:hover {
            background: #e9ecef;
            border-color: #cbd3da;
        }
        
        footer {
            text-align: center;
            padding: 2rem;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn-custom {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <header class="header-custom">
        <div class="header-content">
            <img src="../FrontOffice/assets/img/logo.png" alt="PerFran">
            <div>
                <h1>Ajouter un mot intrus</h1>
                <p>Quiz #<?= htmlspecialchars($quiz->qid) ?></p>
            </div>
        </div>
    </header>

    <main class="container-custom">
        <?php if ($error): ?>
            <div class="error-alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <form method="post" action="">
                <!-- Position supprimée, sera définie à 0 automatiquement -->
                
                <div class="form-group">
                    <label for="correctAnswer">Réponse correcte</label>
                    <input type="text" id="correctAnswer" name="correctAnswer" placeholder="Entrez la réponse correcte" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-custom btn-primary">
                        <span>✓</span>
                        Enregistrer
                    </button>
                    <a href="blank_list.php?quiz_id=<?= htmlspecialchars($quiz->qid) ?>" class="btn-custom btn-secondary">
                        <span>×</span>
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </main>

    <footer>
        PerFran — Gestion des blanks
    </footer>
</body>
</html>
