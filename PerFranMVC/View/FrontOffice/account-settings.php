<?php
session_start();
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../Controller/UserC.php';

if (!isset($_SESSION['uid'])) {
    header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/login.php');
    exit;
}

$userC = new UserC();
$user = $userC->findByUsername($_SESSION['uid']);

if (!$user) {
    session_destroy();
    header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/login.php');
    exit;
}

$initials = strtoupper(substr($user['username'], 0, 2));
$avatarUrl = $user['avatar'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres - PerfRan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/assets/css/user-dashboard.css">
    <style>
        .settings-section {
            margin-bottom: 16px;
        }
        
        .settings-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px;
            background: var(--bg-secondary);
            border-radius: 12px;
            margin-bottom: 12px;
            transition: all 0.2s ease;
        }
        
        .settings-item:hover {
            background: var(--card-hover);
        }
        
        .settings-item-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .settings-item-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: white;
        }
        
        .settings-item-icon.purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        .settings-item-icon.blue { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .settings-item-icon.green { background: linear-gradient(135deg, #10b981, #059669); }
        .settings-item-icon.orange { background: linear-gradient(135deg, #f97316, #ea580c); }
        .settings-item-icon.red { background: linear-gradient(135deg, #ef4444, #dc2626); }
        
        .settings-item-text h4 {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .settings-item-text p {
            font-size: 13px;
            color: var(--muted);
        }
        
        /* Toggle Switch */
        .toggle {
            position: relative;
            width: 52px;
            height: 28px;
        }
        
        .toggle input {
            display: none;
        }
        
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--border);
            border-radius: 28px;
            transition: 0.3s;
        }
        
        .toggle-slider::before {
            content: '';
            position: absolute;
            height: 22px;
            width: 22px;
            left: 3px;
            bottom: 3px;
            background: white;
            border-radius: 50%;
            transition: 0.3s;
        }
        
        .toggle input:checked + .toggle-slider {
            background: var(--accent);
        }
        
        .toggle input:checked + .toggle-slider::before {
            transform: translateX(24px);
        }
        
        /* Session Card */
        .session-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px;
            background: var(--bg-secondary);
            border-radius: 12px;
            margin-bottom: 12px;
        }
        
        .session-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .session-icon {
            width: 48px;
            height: 48px;
            background: var(--card);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .session-details h4 {
            font-size: 15px;
            margin-bottom: 4px;
        }
        
        .session-details p {
            font-size: 13px;
            color: var(--muted);
        }
        
        .session-current {
            background: rgba(16, 185, 129, 0.15);
            color: var(--green);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
    </style>
</head>
<body>

<!-- Mobile Toggle -->
<button class="mobile-toggle" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</button>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" onclick="toggleSidebar()"></div>

<!-- Sidebar -->
<aside class="sidebar">
    <div class="avatar-container">
        <div class="avatar">
            <?php if ($avatarUrl): ?>
                <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Avatar">
            <?php else: ?>
                <?= $initials ?>
            <?php endif; ?>
        </div>
        <div class="avatar-status"></div>
    </div>
    
    <div class="username"><?= htmlspecialchars($user['username']) ?></div>
    <div class="email"><?= htmlspecialchars($user['email']) ?></div>
    
    <div class="user-role">
        <?php if (($user['role'] ?? 0) == 1): ?>
            <span class="role-badge admin"><i class="fas fa-shield-alt"></i> Admin</span>
        <?php else: ?>
            <span class="role-badge user"><i class="fas fa-user"></i> Membre</span>
        <?php endif; ?>
    </div>

    <button id="themeToggle" class="theme-toggle">
        <i class="fas fa-sun"></i>
        <span>Thème clair</span>
    </button>
    
    <nav>
        <a href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/dashboard.php">
            <i class="fas fa-home"></i> Tableau de bord
        </a>
        <a href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/account-profile.php">
            <i class="fas fa-user"></i> Mon Profil
        </a>
        <a href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/account-payment-details.php">
            <i class="fas fa-credit-card"></i> Abonnement / Don
        </a>
        <a href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/account-settings.php" class="active">
            <i class="fas fa-cog"></i> Paramètres
        </a>
        
        <div class="nav-divider"></div>
        
        <a href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/account-delete.php">
            <i class="fas fa-user-slash"></i> Désactiver le compte
        </a>
        
        <?php if (!empty($_SESSION['role']) && $_SESSION['role'] == 1): ?>
        <a href="<?= BASE_URL ?>PerFranMVC/View/BackOffice/admin-dashboard.php" class="admin-link">
            <i class="fas fa-user-shield"></i> Panneau Admin
        </a>
        <?php endif; ?>
        
        <a href="<?= BASE_URL ?>PerFranMVC/Controller/auth.php?action=logout" class="logout">
            <i class="fas fa-sign-out-alt"></i> Déconnexion
        </a>
    </nav>
</aside>

<!-- Main Content -->
<main class="main">
    <div class="container" style="max-width: 800px;">
        
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title"><i class="fas fa-cog" style="color: var(--accent);"></i> Paramètres</h1>
                <div class="breadcrumb">
                    <a href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/dashboard.php">Tableau de bord</a>
                    <i class="fas fa-chevron-right"></i>
                    <span>Paramètres</span>
                </div>
            </div>
        </div>

        <!-- Notifications Settings -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bell"></i> Notifications</h3>
            </div>
            
            <form method="post" action="<?= BASE_URL ?>PerFranMVC/Controller/update-settings.php">
                <div class="settings-section">
                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-icon purple">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="settings-item-text">
                                <h4>Newsletter</h4>
                                <p>Recevoir des mises à jour par email</p>
                            </div>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" name="newsletter" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-icon blue">
                                <i class="fas fa-sign-in-alt"></i>
                            </div>
                            <div class="settings-item-text">
                                <h4>Alertes de connexion</h4>
                                <p>Me notifier par email lors de nouvelles connexions</p>
                            </div>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" name="notify_login" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-icon green">
                                <i class="fas fa-gamepad"></i>
                            </div>
                            <div class="settings-item-text">
                                <h4>Notifications de jeu</h4>
                                <p>Recevoir des rappels pour jouer</p>
                            </div>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" name="game_reminders">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-icon orange">
                                <i class="fas fa-tag"></i>
                            </div>
                            <div class="settings-item-text">
                                <h4>Promotions</h4>
                                <p>Recevoir des offres et promotions</p>
                            </div>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" name="promotions">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Enregistrer les préférences
                </button>
            </form>
        </div>

        <!-- Security Settings -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-shield-alt"></i> Sécurité</h3>
            </div>
            
            <form method="post" action="<?= BASE_URL ?>PerFranMVC/Controller/update-settings.php">
                <div class="settings-section">
                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-icon purple">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <div class="settings-item-text">
                                <h4>Authentification à deux facteurs</h4>
                                <p>Ajouter une couche de sécurité supplémentaire</p>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary" onclick="setup2FA()">
                            <i class="fas fa-cog"></i> Configurer
                        </button>
                    </div>
                    
                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-icon blue">
                                <i class="fas fa-eye"></i>
                            </div>
                            <div class="settings-item-text">
                                <h4>Profil public</h4>
                                <p>Permettre aux autres de voir mon profil</p>
                            </div>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" name="public_profile">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </form>
        </div>

        <!-- Active Sessions -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-laptop"></i> Sessions actives</h3>
                <button type="button" class="btn btn-danger btn-sm" onclick="logoutAll()">
                    <i class="fas fa-sign-out-alt"></i> Déconnecter tout
                </button>
            </div>
            
            <div class="session-card">
                <div class="session-info">
                    <div class="session-icon">💻</div>
                    <div class="session-details">
                        <h4>Windows • Chrome</h4>
                        <p>Paris, France • Actif maintenant</p>
                    </div>
                </div>
                <span class="session-current">Session actuelle</span>
            </div>
            
            <div class="session-card">
                <div class="session-info">
                    <div class="session-icon">📱</div>
                    <div class="session-details">
                        <h4>iPhone • Safari</h4>
                        <p>Paris, France • Il y a 2 heures</p>
                    </div>
                </div>
                <button class="btn btn-secondary btn-sm">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Privacy Settings -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-secret"></i> Confidentialité</h3>
            </div>
            
            <div class="settings-section">
                <div class="settings-item">
                    <div class="settings-item-info">
                        <div class="settings-item-icon green">
                            <i class="fas fa-download"></i>
                        </div>
                        <div class="settings-item-text">
                            <h4>Exporter mes données</h4>
                            <p>Télécharger une copie de toutes mes données</p>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary" onclick="exportData()">
                        <i class="fas fa-download"></i> Exporter
                    </button>
                </div>
                
                <div class="settings-item">
                    <div class="settings-item-info">
                        <div class="settings-item-icon red">
                            <i class="fas fa-user-slash"></i>
                        </div>
                        <div class="settings-item-text">
                            <h4>Désactiver mon compte</h4>
                            <p>Désactiver temporairement votre compte</p>
                        </div>
                    </div>
                    <a href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/account-delete.php" class="btn btn-danger">
                        <i class="fas fa-user-slash"></i> Désactiver
                    </a>
                </div>
            </div>
        </div>

    </div>
</main>

<script src="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/assets/js/theme-toggle.js" defer></script>

<script>
window.PERFRAN_AI_API_URL = '<?= PERFRAN_AI_API_URL ?>';
(function(){
    function loadMascot(){
        if(window.__perfRanMascotLoaded) return;
        window.__perfRanMascotLoaded = true;
        var s = document.createElement('script');
        s.src = '<?= BASE_URL ?>PerFranMVC/View/FrontOffice/assets/js/mascot.js';
        s.async = true;
        document.body.appendChild(s);
    }
    if(document.readyState === 'complete'){
        setTimeout(loadMascot, 1000);
    } else {
        window.addEventListener('load', function(){ setTimeout(loadMascot, 1000); });
    }
})();

// Mobile sidebar toggle
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('open');
    document.querySelector('.sidebar-overlay').classList.toggle('show');
}

function setup2FA() {
    alert('Configuration 2FA - Fonctionnalité à venir !');
}

function logoutAll() {
    if(confirm('Êtes-vous sûr de vouloir vous déconnecter de tous les appareils ?')) {
        alert('Déconnexion de tous les appareils...');
    }
}

function exportData() {
    alert('Export des données - Fonctionnalité à venir !');
}
</script>

</body>
</html>
