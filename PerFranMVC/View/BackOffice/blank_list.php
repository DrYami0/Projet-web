<?php
session_start();
// Vue liste des blanks pour un quizz
require_once __DIR__ . '/../../Model/Quiz.php';
require_once __DIR__ . '/../../Model/QuizBlank.php';
require_once __DIR__ . '/../../Controller/QuizController.php';
require_once __DIR__ . '/../../Controller/QuizBlankController.php';

$quizId = (int)($_GET['quiz_id'] ?? 0);

// Valider que quiz_id est fourni
if ($quizId === 0) {
    $_SESSION['error'] = 'ID de quiz manquant. Veuillez sélectionner un quiz depuis la liste.';
    header('Location: quiz_list.php');
    exit;
}

// Récupérer les données via le contrôleur
$data = QuizBlankController::list($quizId);
$quiz = $data['quiz'];
$blanks = $data['blanks'];
$success = $data['success'];
$error = $data['error'];

// Compter les slots pour identifier les intrus
preg_match_all('/\[([^\]]+)\]/', $quiz->paragraph, $matches);
$expectedAnswers = $matches[1]; // Les mots attendus dans l'ordre

// Fonction pour vérifier si un blank est un intrus
$isIntruderFunc = function($blank) use ($expectedAnswers) {
    // Si position <= 0, c'est un intrus
    if ($blank->position <= 0) {
        return true;
    }
    
    // Array est 0-indexed, position est 1-indexed
    $arrayIndex = $blank->position - 1;
    
    // Vérifier si l'index existe dans le tableau
    if (!isset($expectedAnswers[$arrayIndex])) {
        return true;
    }
    
    $expected = trim(strtolower($expectedAnswers[$arrayIndex]));
    $actual = trim(strtolower($blank->correctAnswer));
    return $expected !== $actual;
};

// Trier les blanks : non-intrus d'abord, puis intrus
usort($blanks, function($a, $b) use ($isIntruderFunc) {
    $aIsIntruder = $isIntruderFunc($a);
    $bIsIntruder = $isIntruderFunc($b);
    
    if ($aIsIntruder === $bIsIntruder) {
        return $a->position <=> $b->position;
    }
    
    return $aIsIntruder ? 1 : -1;
});



?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PerFran — Gestion des Blanks</title>
    <link rel="stylesheet" href="../FrontOffice/assets/css/bootstrap.css">
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
            <div id="success-message" data-message="<?= htmlspecialchars(strip_tags($success)) ?>" style="display: none;"></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div id="error-message" data-message="<?= htmlspecialchars($error) ?>" style="display: none;"></div>
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
                    <i class="fas fa-plus"></i> Ajouter mot intrus
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
                        <?php foreach ($blanks as $key => $blank): ?>
                        <tr>
                            <td><?= htmlspecialchars($blank->bid) ?></td>
                            <td>
                                <?php 
                                $isIntruder = $isIntruderFunc($blank);
                                ?>
                                
                                <?php if ($isIntruder): ?>
                                    <span class="status status-inactive" style="background: #e74c3c; color: white;">Intrus</span>
                                <?php else: ?>
                                    <?= htmlspecialchars($blank->position) ?>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($blank->correctAnswer) ?></td>
                            <td class="table-actions">
                                <a href="blank_edit.php?id=<?= htmlspecialchars($blank->bid) ?>" class="action-btn edit-btn">
                                    <i class="fas fa-edit"></i> Éditer
                                </a>
                                <form method="post" action="blank_delete.php" style="display: inline;" class="delete-blank-form" data-blank-id="<?= htmlspecialchars($blank->bid) ?>">
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

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../FrontOffice/assets/js/sweetalert2-helper.js"></script>
    <script>
        // Afficher les messages PHP avec SweetAlert2
        document.addEventListener('DOMContentLoaded', function() {
            const successMsg = document.getElementById('success-message');
            const errorMsg = document.getElementById('error-message');
            
            if (successMsg && successMsg.dataset.message) {
                showSuccess('Succès !', successMsg.dataset.message);
            }
            
            if (errorMsg && errorMsg.dataset.message) {
                showError('Erreur !', errorMsg.dataset.message);
            }
        });

        // Intercepter les soumissions de formulaire de suppression
        document.querySelectorAll('.delete-blank-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const form = this;
                
                showConfirm(
                    'Supprimer le blank',
                    'Êtes-vous sûr de vouloir supprimer ce blank ?\n\nLe blank sera retiré du texte du quiz et vous pourrez l\'annuler après.',
                    'Oui, supprimer',
                    'Annuler',
                    function(confirmed) {
                        if (confirmed) {
                            form.submit();
                        }
                    }
                );
            });
        });

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
