<?php

if (!isset($users)) $users = [];
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../Controller/UserC.php';
$userRepo = new UserC();
$pendingList = $userRepo->listPending();
$pendingCount = count($pendingList);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?=htmlspecialchars($pageTitle ?? 'Gestion des Utilisateurs')?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="stylesheet" href="<?=htmlspecialchars(BASE_URL)?>PerFranMVC/View/BackOffice/assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" media="print" onload="this.media='all'">
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
                    <a href="<?=BASE_URL?>PerFranMVC/Controller/admin.php?action=list_all" class="nav-link active">
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
                    <a href="<?=BASE_URL?>PerFranMVC/Controller/admin.php?action=deleted" class="nav-link">
                        <i class="fas fa-trash-alt"></i>
                        <span>Supprimés</span>
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
            <!-- Header -->
            <header class="admin-header">
                <div class="header-left">
                    <button class="menu-toggle" id="menuToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title"><?=htmlspecialchars($pageTitle ?? 'Gestion des Utilisateurs')?></h1>
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
                <!-- Alerts -->
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
                
                <!-- Users Table -->
                <div class="table-card">
                    <div class="table-header">
                        <h3 class="table-title">Liste des Utilisateurs (<?=count($users)?>)</h3>
                        <div class="table-search">
                            <a href="<?=BASE_URL?>PerFranMVC/View/BackOffice/admin-dashboard.php" class="btn btn-outline">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Utilisateur</th>
                                    <th>Statut</th>
                                    <th>Rôle</th>
                                    <th>Inscrit le</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr><td colspan="5" style="text-align: center; padding: 40px;">Aucun utilisateur.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($users as $u): 
                                        $avatarColors = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#8b5cf6'];
                                        $colorIndex = ord($u['username'][0] ?? 'A') % count($avatarColors);
                                        $avatarColor = $avatarColors[$colorIndex];
                                        
                                        $status = $u['status'] ?? 'Inactive';
                                        $isBanned = !empty($u['bannedUntil']) && strtotime($u['bannedUntil']) > time();
                                        if ($isBanned) $status = 'Banned';
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="user-cell">
                                                    <div class="user-cell-avatar" style="background: <?=$avatarColor?>20; color: <?=$avatarColor?>">
                                                        <?=strtoupper(substr($u['username'] ?? 'U', 0, 2))?>
                                                    </div>
                                                    <div class="user-cell-info">
                                                        <div class="user-cell-name"><?=htmlspecialchars($u['username'] ?? '')?></div>
                                                        <div class="user-cell-email"><?=htmlspecialchars($u['email'] ?? '')?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($isBanned): ?>
                                                    <span class="status-badge banned">Banni</span>
                                                <?php elseif ($status === 'Active'): ?>
                                                    <span class="status-badge active">Actif</span>
                                                <?php else: ?>
                                                    <span class="status-badge inactive">Inactif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (($u['role'] ?? 0) == 1): ?>
                                                    <span class="role-badge admin">Admin</span>
                                                <?php else: ?>
                                                    <span class="role-badge user">Utilisateur</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?=htmlspecialchars(date('d/m/Y', strtotime($u['creationDate'] ?? $u['created_at'] ?? 'now')))?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <?php if ($status !== 'Active'): ?>
                                                        <a href="<?=BASE_URL?>PerFranMVC/Controller/admin.php?action=approve_user&username=<?=urlencode($u['username'])?>" class="action-btn approve" title="Approuver">
                                                            <i class="fas fa-check"></i>
                                                        </a>
                                                        <a href="<?=BASE_URL?>PerFranMVC/Controller/admin.php?action=reject_user&username=<?=urlencode($u['username'])?>" class="action-btn delete" title="Refuser">
                                                            <i class="fas fa-times"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="<?=BASE_URL?>PerFranMVC/Controller/admin.php?action=deactivate&uid=<?=urlencode($u['uid'] ?? $u['id'] ?? '')?>" class="action-btn ban" title="Désactiver">
                                                            <i class="fas fa-pause"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if ($isBanned): ?>
                                                        <a href="<?=BASE_URL?>PerFranMVC/Controller/admin.php?action=unban&uid=<?=urlencode($u['uid'] ?? $u['id'] ?? '')?>" class="action-btn approve" title="Débannir">
                                                            <i class="fas fa-unlock"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="<?=BASE_URL?>PerFranMVC/Controller/admin.php?action=edit&uid=<?=urlencode($u['uid'] ?? $u['id'] ?? '')?>" class="action-btn edit" title="Modifier">
                                                        <i class="fas fa-pen"></i>
                                                    </a>
                                                    <a href="<?=BASE_URL?>PerFranMVC/Controller/admin.php?action=delete&uid=<?=urlencode($u['uid'] ?? $u['id'] ?? '')?>" class="action-btn delete" title="Supprimer" onclick="return confirm('Supprimer définitivement ?')">
                                                        <i class="fas fa-trash"></i>
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
    </script>
</body>
</html>
