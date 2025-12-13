<?php
if (!isset($user)) {
    header('Location: ' . BASE_URL . 'PerFranMVC/Controller/admin.php?action=list_all');
    exit;
}
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
    <title>Modifier l'utilisateur - Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="stylesheet" href="<?=htmlspecialchars(BASE_URL)?>PerFranMVC/View/BackOffice/assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" media="print" onload="this.media='all'">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .edit-form {
            background: var(--bg-card);
            border-radius: 16px;
            padding: 32px;
            border: 1px solid var(--border-color);
            max-width: 600px;
        }
        .form-group {
            margin-bottom: 24px;
        }
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-primary);
        }
        .form-input {
            width: 100%;
            padding: 12px 16px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            background: var(--bg-dark);
            color: var(--text-primary);
            font-size: 14px;
            transition: var(--transition);
        }
        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }
        .form-select {
            width: 100%;
            padding: 12px 16px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            background: var(--bg-dark);
            color: var(--text-primary);
            font-size: 14px;
            cursor: pointer;
        }
        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 32px;
        }
        .user-preview {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 20px;
            background: var(--bg-card-hover);
            border-radius: 12px;
            margin-bottom: 24px;
        }
        .user-preview-avatar {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
        }
        .user-preview-info h3 {
            margin-bottom: 4px;
        }
        .user-preview-info p {
            color: var(--text-muted);
            font-size: 14px;
        }
    </style>
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
                    <a href="<?=BASE_URL?>PerFranMVC/View/BackOffice/admin-approval.php" class="nav-link">
                        <i class="fas fa-user-clock"></i>
                        <span>En attente</span>
                        <?php if ($pendingCount > 0): ?>
                            <span class="nav-badge"><?=$pendingCount?></span>
                        <?php endif; ?>
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Paramètres</div>
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
                    <h1 class="page-title">Modifier l'utilisateur</h1>
                </div>
                <div class="header-right">
                    <a href="<?=BASE_URL?>PerFranMVC/Controller/admin.php?action=list_all" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
            </header>
            
            <!-- Content Area -->
            <div class="content-area">
                <?php if (!empty($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?=htmlspecialchars($_SESSION['error'])?>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                
                <?php
                    $avatarColors = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#8b5cf6'];
                    $colorIndex = ord($user['username'][0] ?? 'A') % count($avatarColors);
                    $avatarColor = $avatarColors[$colorIndex];
                ?>
                
                <div class="edit-form">
                    <div class="user-preview">
                        <div class="user-preview-avatar" style="background: <?=$avatarColor?>20; color: <?=$avatarColor?>">
                            <?=strtoupper(substr($user['username'] ?? 'U', 0, 2))?>
                        </div>
                        <div class="user-preview-info">
                            <h3><?=htmlspecialchars($user['username'] ?? '')?></h3>
                            <p>UID: <?=htmlspecialchars($user['uid'] ?? '')?> • Inscrit le <?=htmlspecialchars(date('d/m/Y', strtotime($user['creationDate'] ?? 'now')))?></p>
                        </div>
                    </div>
                    
                    <form method="post" action="<?=BASE_URL?>PerFranMVC/Controller/admin.php">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="uid" value="<?=htmlspecialchars($user['uid'] ?? '')?>">
                        
                        <div class="form-group">
                            <label class="form-label">Prénom</label>
                            <input type="text" name="firstName" class="form-input" value="<?=htmlspecialchars($user['firstName'] ?? '')?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Nom</label>
                            <input type="text" name="lastName" class="form-input" value="<?=htmlspecialchars($user['lastName'] ?? '')?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-input" value="<?=htmlspecialchars($user['email'] ?? '')?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Téléphone</label>
                            <input type="text" name="phone" class="form-input" value="<?=htmlspecialchars($user['phone'] ?? '')?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Rôle</label>
                            <select name="role" class="form-select">
                                <option value="0" <?=($user['role'] ?? 0) == 0 ? 'selected' : ''?>>Utilisateur</option>
                                <option value="1" <?=($user['role'] ?? 0) == 1 ? 'selected' : ''?>>Administrateur</option>
                            </select>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer
                            </button>
                            <a href="<?=BASE_URL?>PerFranMVC/Controller/admin.php?action=list_all" class="btn btn-outline">
                                Annuler
                            </a>
                        </div>
                    </form>
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
