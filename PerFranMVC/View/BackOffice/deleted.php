<?php
if (!isset($deletedUsers)) $deletedUsers = [];
$deletedCount = count($deletedUsers);
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../Controller/UserC.php';
$userRepo = new UserC();
$pendingCount = $userRepo->countPendingUsers();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Utilisateurs supprimés - Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="stylesheet" href="<?=htmlspecialchars(BASE_URL)?>PerFranMVC/View/BackOffice/assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" media="print" onload="this.media='all'">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
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
                    <a href="<?=BASE_URL?>PerFranMVC/Controller/admin.php?action=pending" class="nav-link">
                        <i class="fas fa-user-clock"></i>
                        <span>En attente</span>
                        <?php if ($pendingCount > 0): ?>
                            <span class="nav-badge"><?=$pendingCount?></span>
                        <?php endif; ?>
                    </a>
                    <a href="<?=BASE_URL?>PerFranMVC/Controller/admin.php?action=banned" class="nav-link">
                        <i class="fas fa-user-slash"></i>
                        <span>Bannis</span>
                    </a>
                    <a href="<?=BASE_URL?>PerFranMVC/Controller/admin.php?action=deleted" class="nav-link active">
                        <i class="fas fa-trash-alt"></i>
                        <span>Supprimés</span>
                        <?php if ($deletedCount > 0): ?>
                            <span class="nav-badge" style="background: var(--text-muted);"><?=$deletedCount?></span>
                        <?php endif; ?>
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Paramètres</div>
                    <a href="<?=BASE_URL?>PerFranMVC/Controller/admin.php?action=settings" class="nav-link">
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
            <header class="admin-header">
                <div class="header-left">
                    <button class="menu-toggle" id="menuToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title">Utilisateurs supprimés</h1>
                </div>
                <div class="header-right">
                    <a href="<?=BASE_URL?>PerFranMVC/View/FrontOffice/dashboard.php" class="header-btn btn-user-view">
                        <i class="fas fa-external-link-alt"></i>
                        <span>Vue Utilisateur</span>
                    </a>
                </div>
            </header>
            
            <div class="content-area">
                <?php if (!empty($_SESSION['message'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?=htmlspecialchars($_SESSION['message'])?>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>
                
                <?php if (!empty($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?=htmlspecialchars($_SESSION['error'])?>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                
                <!-- Stats -->
                <div class="stats-grid" style="grid-template-columns: repeat(2, 1fr); max-width: 600px;">
                    <div class="stat-card" style="--stat-color: #64748b;">
                        <div class="stat-header">
                            <div class="stat-icon" style="background: rgba(100, 116, 139, 0.2); color: #94a3b8;">
                                <i class="fas fa-trash-alt"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?=$deletedCount?></div>
                        <div class="stat-label">Comptes supprimés</div>
                    </div>
                    
                    <div class="stat-card success">
                        <div class="stat-header">
                            <div class="stat-icon">
                                <i class="fas fa-undo"></i>
                            </div>
                        </div>
                        <div class="stat-value">∞</div>
                        <div class="stat-label">Restauration possible</div>
                    </div>
                </div>
                
                <!-- Deleted Users Table -->
                <div class="table-card">
                    <div class="table-header">
                        <h3 class="table-title">Comptes supprimés (<?=$deletedCount?>)</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Utilisateur</th>
                                    <th>Email</th>
                                    <th>Supprimé le</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($deletedUsers)): ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center; padding: 60px;">
                                            <div style="color: var(--text-muted);">
                                                <i class="fas fa-trash-restore" style="font-size: 48px; margin-bottom: 16px; display: block; color: var(--success);"></i>
                                                <p style="font-size: 18px; margin-bottom: 8px;">Aucun compte supprimé</p>
                                                <p style="font-size: 14px;">La corbeille est vide.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($deletedUsers as $u): 
                                        $avatarColors = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#8b5cf6'];
                                        $colorIndex = ord($u['username'][0] ?? 'A') % count($avatarColors);
                                        $avatarColor = $avatarColors[$colorIndex];
                                    ?>
                                        <tr style="opacity: 0.7;">
                                            <td>
                                                <div class="user-cell">
                                                    <div class="user-cell-avatar" style="background: <?=$avatarColor?>20; color: <?=$avatarColor?>">
                                                        <?=strtoupper(substr($u['username'] ?? 'U', 0, 2))?>
                                                    </div>
                                                    <div class="user-cell-info">
                                                        <div class="user-cell-name" style="text-decoration: line-through;"><?=htmlspecialchars($u['username'] ?? '')?></div>
                                                        <div class="user-cell-email"><?=htmlspecialchars(($u['firstName'] ?? '') . ' ' . ($u['lastName'] ?? ''))?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?=htmlspecialchars($u['email'] ?? '')?></td>
                                            <td><?=htmlspecialchars(date('d/m/Y H:i', strtotime($u['deleted_at'] ?? 'now')))?></td>
                                            <td>
                                                <div class="action-buttons" style="gap: 8px;">
                                                    <a href="<?=BASE_URL?>PerFranMVC/Controller/admin.php?action=restore&uid=<?=urlencode($u['uid'])?>" class="btn btn-success btn-sm">
                                                        <i class="fas fa-undo"></i> Restaurer
                                                    </a>
                                                    <a href="<?=BASE_URL?>PerFranMVC/Controller/admin.php?action=delete&uid=<?=urlencode($u['uid'])?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer DÉFINITIVEMENT cet utilisateur ? Cette action est irréversible.')">
                                                        <i class="fas fa-trash"></i> Supprimer définitivement
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
    </script>
</body>
</html>
