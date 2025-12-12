<?php
session_start();
require_once __DIR__ . '/../../Controller/QuizController.php';

// Define BASE_URL relative to this file
const BASE_URL = '../../';

// Récupérer les données via le contrôleur
$data = QuizController::list();
$quizzes = $data['quizzes'];
$success = $data['success'];
$error = $data['error'];

// Mock user for display if not set
$username = $_SESSION['username'] ?? 'Admin';
$initials = strtoupper(substr($username, 0, 1));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PerFran - Gestion des Quiz</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>:root{--bg-dark:#0f172a;--text-primary:#f1f5f9}body{font-family:Inter,-apple-system,sans-serif;background:var(--bg-dark);color:var(--text-primary);margin:0}.sidebar{width:280px;position:fixed;height:100vh;background:#1e293b}</style>
    <link rel="stylesheet" href="assets/css/admin.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div>
                    <div class="sidebar-title">PerFran Admin</div>
                    <div class="sidebar-subtitle">Gestion des Quiz</div>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Principal</div>
                    <a href="quiz_list.php" class="nav-link active">
                        <i class="fas fa-th-large"></i>
                        <span>Liste des Quiz</span>
                    </a>
                    <a href="quiz_add.php" class="nav-link">
                        <i class="fas fa-plus-circle"></i>
                        <span>Nouveau Quiz</span>
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Navigation</div>
                    <a href="<?= BASE_URL ?>View/FrontOffice/index.php" class="nav-link">
                        <i class="fas fa-home"></i>
                        <span>Retour au Site</span>
                    </a>
                </div>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="admin-header">
                <div class="header-left">
                    <button class="menu-toggle" id="menuToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title">Gestion des Quiz</h1>
                </div>
                <div class="header-right">
                    <a href="<?= BASE_URL ?>View/FrontOffice/index.php" class="header-btn btn-user-view">
                        <i class="fas fa-external-link-alt"></i>
                        <span>Vue Utilisateur</span>
                    </a>
                    <div class="user-menu">
                        <div class="user-avatar">
                            <?= $initials ?>
                        </div>
                        <div class="user-info">
                            <div class="user-name"><?= htmlspecialchars($username) ?></div>
                            <div class="user-role">Administrateur</div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Content Area -->
            <div class="content-area">
                <!-- Alerts -->
                <?php if ($success): ?>
                    <div id="success-message" data-message="<?= htmlspecialchars($success) ?>" style="display: none;"></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div id="error-message" data-message="<?= htmlspecialchars($error) ?>" style="display: none;"></div>
                <?php endif; ?>
                
                <!-- Quiz Table -->
                <div class="table-card">
                    <div class="table-header">
                        <h3 class="table-title">Liste des Quiz</h3>
                        <div class="table-search">
                            <div style="display: flex; gap: 12px;">
                                <input type="text" id="filterInput" class="search-input" placeholder="Rechercher un quiz..." onkeyup="filterTable()">
                                <a href="quiz_add.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Nouveau
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Texte du Quiz</th>
                                    <th>Difficulté</th>
                                    <th>Blanks</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($quizzes) > 0): ?>
                                    <?php foreach ($quizzes as $quiz): ?>
                                    <tr>
                                        <td>#<?= htmlspecialchars($quiz->qid) ?></td>
                                        <td>
                                            <div style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                <?= htmlspecialchars($quiz->paragraph) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php 
                                            $diffClass = 'info';
                                            if ($quiz->difficulty == 'easy') $diffClass = 'success';
                                            if ($quiz->difficulty == 'hard') $diffClass = 'danger';
                                            if ($quiz->difficulty == 'medium') $diffClass = 'warning';
                                            ?>
                                            <span class="status-badge <?= $diffClass ?>"><?= htmlspecialchars(ucfirst($quiz->difficulty)) ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($quiz->nbBlanks) ?></td>
                                        <td>
                                            <?php if ($quiz->approved): ?>
                                                <span class="status-badge active">Approuvé</span>
                                            <?php else: ?>
                                                <span class="status-badge inactive">Brouillon</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="quiz_edit.php?id=<?= $quiz->qid ?>" class="action-btn edit" title="Modifier">
                                                    <i class="fas fa-pen"></i>
                                                </a>
                                                <a href="blank_list.php?quiz_id=<?= $quiz->qid ?>" class="action-btn view" title="Gérer les Blanks">
                                                    <i class="fas fa-list"></i>
                                                </a>
                                                <form method="post" action="quiz_delete.php" style="display: inline;" class="delete-quiz-form">
                                                    <input type="hidden" name="id" value="<?= $quiz->qid ?>">
                                                    <button type="submit" class="action-btn delete" title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 40px;">Aucun quiz trouvé</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../FrontOffice/assets/js/sweetalert2-helper.js"></script>
    <script>
        // Mobile menu toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        
        if (menuToggle) {
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('open');
                overlay.classList.toggle('open');
            });
        }
        
        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('open');
                overlay.classList.remove('open');
            });
        }

        // Afficher les messages PHP avec SweetAlert2
        document.addEventListener('DOMContentLoaded', function() {
            const successMsg = document.getElementById('success-message');
            const errorMsg = document.getElementById('error-message');
            
            if (successMsg && successMsg.dataset.message) {
                Swal.fire({
                    icon: 'success',
                    title: 'Succès !',
                    text: successMsg.dataset.message,
                    confirmButtonColor: '#10b981',
                    background: '#1e293b',
                    color: '#f1f5f9'
                });
            }
            
            if (errorMsg && errorMsg.dataset.message) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: errorMsg.dataset.message,
                    confirmButtonColor: '#ef4444',
                    background: '#1e293b',
                    color: '#f1f5f9'
                });
            }
        });

        // Intercepter les soumissions de formulaire de suppression
        document.querySelectorAll('.delete-quiz-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const form = this;
                
                Swal.fire({
                    title: 'Supprimer le quiz',
                    text: 'Êtes-vous sûr de vouloir supprimer ce quiz ? Cette action est irréversible.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Oui, supprimer',
                    cancelButtonText: 'Annuler',
                    background: '#1e293b',
                    color: '#f1f5f9'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        function filterTable() {
            const input = document.getElementById('filterInput');
            const filter = input.value.toLowerCase();
            const table = document.querySelector('.admin-table');
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
