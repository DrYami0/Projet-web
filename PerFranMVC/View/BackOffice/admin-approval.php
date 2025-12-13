<?php
session_start();
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../Controller/UserC.php';
if (empty($_SESSION['user']) || $_SESSION['user']['role'] != 1) {
    header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/login.php');
    exit;
}
$userRepo = new UserC();
$pendings = $userRepo->listPending();
$pendingCount = count($pendings);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Approbations - Admin</title>
    <link rel="stylesheet" href="<?=htmlspecialchars(BASE_URL)?>PerFranMVC/View/BackOffice/assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
                    <div class="sidebar-title">Admin Panel</div>
                    <div class="sidebar-subtitle">Panneau de contrôle</div>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Principal</div>
                    <a href="<?=BASE_URL?>PerFranMVC/View/BackOffice/admin-dashboard.php" class="nav-link">
                        <i class="fas fa-th-large"></i>
                        <span>Tableau de bord</span>
                    </a>
                    <a href="<?=BASE_URL?>PerFranMVC/Controller/admin.php?action=list_all" class="nav-link">
                        <i class="fas fa-users"></i>
                        <span>Utilisateurs</span>
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Gestion</div>
                    <a href="<?=BASE_URL?>PerFranMVC/View/BackOffice/admin-approval.php" class="nav-link active">
                        <i class="fas fa-user-clock"></i>
                        <span>En attente</span>
                        <?php if ($pendingCount > 0): ?>
                            <span class="nav-badge"><?=$pendingCount?></span>
                        <?php endif; ?>
                    </a>
                    <a href="<?=BASE_URL?>PerFranMVC/Controller/admin.php?action=list_all&filter=banned" class="nav-link">
                        <i class="fas fa-user-slash"></i>
                        <span>Bannis</span>
                    </a>
                    <a href="<?=BASE_URL?>PerFranMVC/Controller/admin.php?action=list_all&filter=deleted" class="nav-link">
                        <i class="fas fa-trash-alt"></i>
                        <span>Supprimés</span>
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Paramètres</div>
                    <a href="#" class="nav-link">
                        <i class="fas fa-cog"></i>
                        <span>Configuration</span>
                    </a>
                    <a href="<?=BASE_URL?>PerFranMVC/Controller/auth.php?action=logout" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Déconnexion</span>
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
                    <h1 class="page-title">Approbations en attente</h1>
                </div>
                <div class="header-right">
                    <a href="<?=BASE_URL?>PerFranMVC/View/FrontOffice/dashboard.php" class="header-btn btn-user-view">
                        <i class="fas fa-external-link-alt"></i>
                        <span>Vue Utilisateur</span>
                    </a>
                    <div class="user-menu">
                        <div class="user-avatar">
                            <?=strtoupper(substr($_SESSION['user']['username'] ?? 'A', 0, 1))?>
                        </div>
                        <div class="user-info">
                            <div class="user-name"><?=htmlspecialchars($_SESSION['user']['username'] ?? 'Admin')?></div>
                            <div class="user-role">Administrateur</div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Content Area -->
            <div class="content-area">
                <!-- Stats -->
                <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
                    <div class="stat-card warning">
                        <div class="stat-header">
                            <div class="stat-icon">
                                <i class="fas fa-user-clock"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?=$pendingCount?></div>
                        <div class="stat-label">En attente d'approbation</div>
                    </div>
                    
                    <div class="stat-card success">
                        <div class="stat-header">
                            <div class="stat-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="stat-value">—</div>
                        <div class="stat-label">Approuvés aujourd'hui</div>
                    </div>
                    
                    <div class="stat-card danger">
                        <div class="stat-header">
                            <div class="stat-icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                        </div>
                        <div class="stat-value">—</div>
                        <div class="stat-label">Refusés aujourd'hui</div>
                    </div>
                </div>
                
                <!-- Pending Users Table -->
                <div class="table-card">
                    <div class="table-header">
                        <h3 class="table-title">Utilisateurs en attente de validation</h3>
                        <?php if ($pendingCount > 0): ?>
                        <div style="display: flex; gap: 12px;">
                            <a href="#" class="btn btn-success btn-sm" onclick="approveAll(); return false;">
                                <i class="fas fa-check-double"></i> Tout approuver
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Utilisateur</th>
                                    <th>Email</th>
                                    <th>Inscrit le</th>
                                    <th>Temps restant</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pendings)): ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 60px;">
                                            <div style="color: var(--text-muted);">
                                                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
                                                <p style="font-size: 18px; margin-bottom: 8px;">Aucune demande en attente</p>
                                                <p style="font-size: 14px;">Toutes les inscriptions ont été traitées.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($pendings as $p): 
                                        $avatarColors = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#8b5cf6'];
                                        $colorIndex = ord($p['username'][0] ?? 'A') % count($avatarColors);
                                        $avatarColor = $avatarColors[$colorIndex];
                                        $hoursLeft = $p['token'] ?? 48;
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="user-cell">
                                                    <div class="user-cell-avatar" style="background: <?=$avatarColor?>20; color: <?=$avatarColor?>">
                                                        <?=strtoupper(substr($p['username'] ?? 'U', 0, 2))?>
                                                    </div>
                                                    <div class="user-cell-info">
                                                        <div class="user-cell-name"><?=htmlspecialchars($p['username'] ?? '')?></div>
                                                        <div class="user-cell-email"><?=htmlspecialchars(($p['firstName'] ?? '') . ' ' . ($p['lastName'] ?? ''))?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?=htmlspecialchars($p['email'] ?? '')?></td>
                                            <td><?=htmlspecialchars(date('d/m/Y H:i', strtotime($p['creationDate'] ?? 'now')))?></td>
                                            <td>
                                                <span class="status-badge <?=$hoursLeft > 24 ? 'active' : ($hoursLeft > 6 ? 'inactive' : 'banned')?>">
                                                    <?=$hoursLeft?>h restantes
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="<?=BASE_URL?>PerFranMVC/Controller/admin.php?action=approve_user&username=<?=urlencode($p['username'])?>" class="btn btn-success btn-sm" style="padding: 8px 16px;">
                                                        <i class="fas fa-check"></i> Approuver
                                                    </a>
                                                    <a href="<?=BASE_URL?>PerFranMVC/Controller/admin.php?action=reject_user&username=<?=urlencode($p['username'])?>" class="btn btn-danger btn-sm" style="padding: 8px 16px;" onclick="return confirm('Refuser et supprimer cette demande ?')">
                                                        <i class="fas fa-times"></i> Refuser
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Mobile menu toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('open');
        });
        
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('open');
        });
        
        function approveAll() {
            if (confirm('Approuver tous les utilisateurs en attente ?')) {
                // Would need to implement this in controller
                alert('Fonctionnalité à implémenter');
            }
        }
    </script>
</body>
</html>
