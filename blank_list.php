<?php
session_start();
// Vue liste des blanks pour un quizz
require_once __DIR__ . '/../../Model/Quiz.php';
require_once __DIR__ . '/../../Model/QuizBlank.php';

$quizId = $_GET['quiz_id'] ?? 0;
$quiz = Quiz::getById((int)$quizId);

if (!$quiz) {
    header('HTTP/1.0 404 Not Found');
    die('Quiz non trouvé');
}

// Récupérer tous les blanks de ce quiz
$blanks = QuizBlank::getByQuizId($quiz->qid);

// Trier par position
usort($blanks, function($a, $b) {
    return $a->position <=> $b->position;
});

// Récupérer les messages de session
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PerFran — Gestion des Blanks</title>
    <link rel="stylesheet" href="../../FrontOffice/assets/css/bootstrap.css">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            margin: 0;
            background: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        .header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 1.5rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .logo-container {
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
            color: white;
        }
        .logo {
            height: 50px;
            width: auto;
        }
        .title h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }
        .title p {
            margin: 0.25rem 0 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .search-box {
            flex: 1;
            min-width: 250px;
            padding: 0.6rem 1rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        .search-box:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
        }
        .btn-primary {
            background: #3498db;
            color: white;
        }
        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-1px);
        }
        .btn-secondary {
            background: #f8f9fa;
            color: #2c3e50;
            border: 1px solid #ddd;
        }
        .btn-secondary:hover {
            background: #e9ecef;
        }
        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }
        .table tbody tr:hover {
            background: #f8f9fa;
        }
        .table-actions {
            display: flex;
            gap: 0.5rem;
        }
        .action-btn {
            padding: 0.4rem 0.8rem;
            border-radius: 4px;
            font-size: 0.85rem;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        .edit-btn {
            background: #3498db;
            color: white;
        }
        .edit-btn:hover {
            background: #2980b9;
        }
        .delete-btn {
            background: #e74c3c;
            color: white;
        }
        .delete-btn:hover {
            background: #c0392b;
        }
        .status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        @media (max-width: 768px) {
            .table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            .controls {
                flex-direction: column;
                align-items: stretch;
            }
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="quiz_list.php" class="logo-container">
                <img src="../FrontOffice/assets/img/logo.png" alt="Logo" class="logo">
                <div class="title">
                    <h1>Gestion des Blanks</h1>
                    <p>Liste des blanks pour le quiz #<?= htmlspecialchars($quiz->qid) ?></p>
                </div>
            </a>
        </div>
    </header>

    <div class="container">
        <?php if ($success): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                <strong>✓ Succès :</strong> <?= $success ?> <!-- Pas de htmlspecialchars pour permettre les liens HTML -->
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                <strong>✗ Erreur :</strong> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <div class="controls">
            <input type="text" 
                   id="filterInput" 
                   class="search-box" 
                   placeholder="Rechercher un blank..." 
                   onkeyup="filterTable()">
            <div class="actions">
                <a href="quiz_list.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
                <a href="blank_add.php?quiz_id=<?= htmlspecialchars($quiz->qid) ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nouveau Blank
                </a>
                <a href="quiz_edit.php?id=<?= htmlspecialchars($quiz->qid) ?>" class="btn btn-secondary" style="margin-left: 10px;">
                    <i class="fas fa-edit"></i> Éditer le Quiz
                </a>
            </div>
        </div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Position</th>
                        <th>Réponse correcte</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($blanks) > 0): ?>
                        <?php foreach ($blanks as $blank): ?>
                        <tr>
                            <td><?= htmlspecialchars($blank->bid) ?></td>
                            <td><?= htmlspecialchars($blank->position + 1) ?></td>
                            <td><?= htmlspecialchars($blank->correctAnswer) ?></td>
                            <td class="table-actions">
                                <a href="quiz_edit.php?id=<?= htmlspecialchars($quiz->qid) ?>" class="action-btn edit-btn">
                                    <i class="fas fa-edit"></i> Éditer
                                </a>
                                <form method="post" action="blank_delete.php" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce blank ?\n\nLe blank sera retiré du texte du quiz et vous pourrez l\'annuler après.');">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($blank->bid) ?>">
                                    <button type="submit" class="action-btn delete-btn" style="border: none; cursor: pointer;">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 2rem;">
                                Aucun blank trouvé pour ce quiz. <a href="blank_add.php?quiz_id=<?= htmlspecialchars($quiz->qid) ?>">Ajouter un blank</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function filterTable() {
            const input = document.getElementById('filterInput');
            const filter = input.value.toLowerCase();
            const table = document.querySelector('.table');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                let td = tr[i].getElementsByTagName('td');
                let found = false;
                
                for (let j = 0; j < td.length; j++) {
                    const cell = td[j];
                    if (cell) {
                        const textValue = cell.textContent || cell.innerText;
                        if (textValue.toLowerCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }
                
                tr[i].style.display = found ? '' : 'none';
            }
        }
    </script>
</body>
</html>
