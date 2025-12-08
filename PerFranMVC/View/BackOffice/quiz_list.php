<?php
session_start();
require_once __DIR__ . '/../../Controller/QuizController.php';

// Récupérer les données via le contrôleur
$data = QuizController::list();
$quizzes = $data['quizzes'];
$success = $data['success'];
$error = $data['error'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PerFran — Liste des quizz</title>
    <link rel="stylesheet" href="../FrontOffice/assets/css/bootstrap.css">
    <link rel="stylesheet" href="css/quiz_list.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="../FrontOffice/index.html" class="logo-container">
                <img src="../FrontOffice/assets/img/logo.png" alt="Logo" class="logo">
                <div class="title">
                    <h1>PerFran — Gestion des Quiz</h1>
                    <p>Liste complète des quiz disponibles</p>
                </div>
            </a>
        </div>
    </header>

    <div class="container">
        <?php if ($success): ?>
            <div id="success-message" data-message="<?= htmlspecialchars($success) ?>" style="display: none;"></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div id="error-message" data-message="<?= htmlspecialchars($error) ?>" style="display: none;"></div>
        <?php endif; ?>
        
        <div class="controls">
            <input type="text" 
                   id="filterInput" 
                   class="search-box" 
                   placeholder="Rechercher un quiz..." 
                   onkeyup="filterTable()">
            <div class="actions">
                <a href="../FrontOffice/index.html" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
                <a href="quiz_add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nouveau Quiz
                </a>
            </div>
        </div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                    <th>ID</th>
                    <th>quizz_text</th>
                    <th>Difficulté</th>
                    <th>Nb blanks</th>
                    <th>Approuvé</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($quizzes) > 0): ?>
                    <?php foreach ($quizzes as $quiz): ?>
                    <tr>
                        <td><?= htmlspecialchars($quiz->qid) ?></td>
                        <td><?= htmlspecialchars($quiz->paragraph) ?></td>
                        <td><?= htmlspecialchars(ucfirst($quiz->difficulty)) ?></td>
                        <td><?= htmlspecialchars($quiz->nbBlanks) ?></td>
                        <td>
                            <span class="status status-<?= $quiz->approved ? 'published' : 'draft' ?>">
                                <?= $quiz->approved ? 'Oui' : 'Non' ?>
                            </span>
                        </td>
                        <td class="table-actions">
                            <a href="quiz_edit.php?id=<?= $quiz->qid ?>" class="action-btn edit-btn">
                                <i class="fas fa-edit"></i> Éditer
                            </a>
                            <form method="post" action="quiz_delete.php" style="display: inline;" class="delete-quiz-form" data-quiz-id="<?= $quiz->qid ?>">
                                <input type="hidden" name="id" value="<?= $quiz->qid ?>">
                                <button type="submit" class="action-btn delete-btn" style="border: none; cursor: pointer;">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </form>
                            <a href="blank_list.php?quiz_id=<?= $quiz->qid ?>" class="action-btn" style="background: #6c757d; color: white;">
                                <i class="fas fa-list"></i> Blanks
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">Aucun quiz trouvé</td>
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
        document.querySelectorAll('.delete-quiz-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            
            showConfirm(
                'Supprimer le quiz',
                'Êtes-vous sûr de vouloir supprimer ce quiz ? Cette action est irréversible.',
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

    // Ajouter un écouteur d'événement pour le champ de recherche
    document.addEventListener('DOMContentLoaded', function() {
        const filterInput = document.getElementById('filterInput');
        if (filterInput) {
            filterInput.addEventListener('input', filterTable);
        }
    });
</script>
</body>
</html>
