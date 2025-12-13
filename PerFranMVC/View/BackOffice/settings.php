<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../Controller/UserC.php';
$userRepo = new UserC();
$pendingCount = $userRepo->countPendingUsers();
$totalUsers = $userRepo->countUsers();
$activeUsers = $userRepo->countActiveUsers();
$bannedUsers = $userRepo->countBannedUsers();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configuration - Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="stylesheet" href="<?=htmlspecialchars(BASE_URL)?>PerFranMVC/View/BackOffice/assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" media="print" onload="this.media='all'">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 24px;
        }
        .settings-card {
            background: var(--bg-card);
            border-radius: 16px;
            padding: 24px;
            border: 1px solid var(--border-color);
        }
        .settings-card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border-color);
        }
        .settings-card-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
        .settings-card-title {
            font-size: 18px;
            font-weight: 600;
        }
        .settings-card-desc {
            font-size: 13px;
            color: var(--text-muted);
        }
        .setting-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid var(--border-color);
        }
        .setting-item:last-child {
            border-bottom: none;
        }
        .setting-label {
            font-weight: 500;
        }
        .setting-desc {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 4px;
        }
        .setting-value {
            font-weight: 600;
            color: var(--primary-light);
        }
        .toggle-switch {
            position: relative;
            width: 50px;
            height: 26px;
        }
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--border-color);
            border-radius: 26px;
            transition: 0.3s;
        }
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 3px;
            bottom: 3px;
            background: white;
            border-radius: 50%;
            transition: 0.3s;
        }
        .toggle-switch input:checked + .toggle-slider {
            background: var(--success);
        }
        .toggle-switch input:checked + .toggle-slider:before {
            transform: translateX(24px);
        }
        .info-box {
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid var(--primary);
            border-radius: 10px;
            padding: 16px;
            margin-top: 20px;
        }
        .info-box i {
            color: var(--primary-light);
            margin-right: 8px;
        }
        .system-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        .system-info-item {
            background: var(--bg-dark);
            padding: 12px 16px;
            border-radius: 8px;
        }
        .system-info-label {
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 4px;
        }
        .system-info-value {
            font-weight: 600;
        }
    </style>
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
                    <a href="<?=BASE_URL?>PerFranMVC/Controller/admin.php?action=deleted" class="nav-link">
                        <i class="fas fa-trash-alt"></i>
                        <span>Supprimés</span>
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Paramètres</div>
                    <a href="<?=BASE_URL?>PerFranMVC/Controller/admin.php?action=settings" class="nav-link active">
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
                    <h1 class="page-title">Configuration</h1>
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
                
                <div class="settings-grid">
                    <!-- General Settings -->
                    <div class="settings-card">
                        <div class="settings-card-header">
                            <div class="settings-card-icon" style="background: rgba(99, 102, 241, 0.2); color: var(--primary-light);">
                                <i class="fas fa-sliders-h"></i>
                            </div>
                            <div>
                                <div class="settings-card-title">Paramètres généraux</div>
                                <div class="settings-card-desc">Configuration de base du site</div>
                            </div>
                        </div>
                        
                        <div class="setting-item">
                            <div>
                                <div class="setting-label">Approbation manuelle</div>
                                <div class="setting-desc">Les inscriptions nécessitent une approbation admin</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        
                        <div class="setting-item">
                            <div>
                                <div class="setting-label">Délai d'expiration</div>
                                <div class="setting-desc">Temps avant expiration des demandes</div>
                            </div>
                            <span class="setting-value">48 heures</span>
                        </div>
                        
                        <div class="setting-item">
                            <div>
                                <div class="setting-label">Durée de bannissement</div>
                                <div class="setting-desc">Durée par défaut des bannissements</div>
                            </div>
                            <span class="setting-value">30 jours</span>
                        </div>
                    </div>
                    
                    <!-- Security Settings -->
                    <div class="settings-card">
                        <div class="settings-card-header">
                            <div class="settings-card-icon" style="background: rgba(16, 185, 129, 0.2); color: var(--success);">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div>
                                <div class="settings-card-title">Sécurité</div>
                                <div class="settings-card-desc">Options de sécurité du site</div>
                            </div>
                        </div>
                        
                        <div class="setting-item">
                            <div>
                                <div class="setting-label">Authentification OAuth</div>
                                <div class="setting-desc">Connexion via Google, Facebook, GitHub</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        
                        <div class="setting-item">
                            <div>
                                <div class="setting-label">Longueur mot de passe</div>
                                <div class="setting-desc">Minimum requis pour les mots de passe</div>
                            </div>
                            <span class="setting-value">6 caractères</span>
                        </div>
                        
                        <div class="setting-item">
                            <div>
                                <div class="setting-label">Hachage des mots de passe</div>
                                <div class="setting-desc">Algorithme utilisé</div>
                            </div>
                            <span class="setting-value">bcrypt</span>
                        </div>
                    </div>
                    
                    <!-- System Info -->
                    <div class="settings-card">
                        <div class="settings-card-header">
                            <div class="settings-card-icon" style="background: rgba(6, 182, 212, 0.2); color: var(--info);">
                                <i class="fas fa-server"></i>
                            </div>
                            <div>
                                <div class="settings-card-title">Informations système</div>
                                <div class="settings-card-desc">État actuel du serveur</div>
                            </div>
                        </div>
                        
                        <div class="system-info">
                            <div class="system-info-item">
                                <div class="system-info-label">Version PHP</div>
                                <div class="system-info-value"><?=phpversion()?></div>
                            </div>
                            <div class="system-info-item">
                                <div class="system-info-label">Serveur</div>
                                <div class="system-info-value"><?=php_uname('s')?></div>
                            </div>
                            <div class="system-info-item">
                                <div class="system-info-label">Base de données</div>
                                <div class="system-info-value">MySQL</div>
                            </div>
                            <div class="system-info-item">
                                <div class="system-info-label">Mémoire</div>
                                <div class="system-info-value"><?=ini_get('memory_limit')?></div>
                            </div>
                        </div>
                        
                        <div class="info-box">
                            <i class="fas fa-info-circle"></i>
                            <span>Dernière mise à jour: <?=date('d/m/Y H:i')?></span>
                        </div>
                    </div>
                    
                    <!-- Statistics -->
                    <div class="settings-card">
                        <div class="settings-card-header">
                            <div class="settings-card-icon" style="background: rgba(245, 158, 11, 0.2); color: var(--warning);">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                            <div>
                                <div class="settings-card-title">Statistiques rapides</div>
                                <div class="settings-card-desc">Aperçu des données utilisateurs</div>
                            </div>
                        </div>
                        
                        <div class="system-info">
                            <div class="system-info-item">
                                <div class="system-info-label">Total utilisateurs</div>
                                <div class="system-info-value" style="color: var(--primary-light);"><?=$totalUsers?></div>
                            </div>
                            <div class="system-info-item">
                                <div class="system-info-label">Utilisateurs actifs</div>
                                <div class="system-info-value" style="color: var(--success);"><?=$activeUsers?></div>
                            </div>
                            <div class="system-info-item">
                                <div class="system-info-label">En attente</div>
                                <div class="system-info-value" style="color: var(--warning);"><?=$pendingCount?></div>
                            </div>
                            <div class="system-info-item">
                                <div class="system-info-label">Bannis</div>
                                <div class="system-info-value" style="color: var(--danger);"><?=$bannedUsers?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Admin Actions -->
                <div class="settings-card" style="margin-top: 24px;">
                    <div class="settings-card-header">
                        <div class="settings-card-icon" style="background: rgba(239, 68, 68, 0.2); color: var(--danger);">
                            <i class="fas fa-tools"></i>
                        </div>
                        <div>
                            <div class="settings-card-title">Actions administrateur</div>
                            <div class="settings-card-desc">Outils de maintenance</div>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                        <a href="<?=BASE_URL?>PerFranMVC/View/BackOffice/admin-dashboard.php" class="btn btn-primary">
                            <i class="fas fa-tachometer-alt"></i> Tableau de bord
                        </a>
                        <a href="<?=BASE_URL?>PerFranMVC/Controller/admin.php?action=list_all" class="btn btn-outline">
                            <i class="fas fa-users"></i> Gérer les utilisateurs
                        </a>
                        <a href="<?=BASE_URL?>PerFranMVC/Controller/admin.php?action=pending" class="btn btn-warning">
                            <i class="fas fa-user-clock"></i> Approbations (<?=$pendingCount?>)
                        </a>
                        <a href="<?=BASE_URL?>PerFranMVC/Controller/auth.php?action=logout" class="btn btn-danger">
                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                        </a>
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
