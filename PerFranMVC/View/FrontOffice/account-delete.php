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
    <title>Désactiver le compte - PerfRan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/assets/css/user-dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
    <style>
        body {
            background: url('<?= BASE_URL ?>PerFranMVC/View/FrontOffice/assets/images/waiting2-bg.gif') no-repeat center center fixed;
            background-size: cover;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: -1;
        }
        
        .delete-container {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .warning-card {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.05));
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 16px;
            padding: 32px;
            text-align: center;
            margin-bottom: 24px;
        }
        
        .warning-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 36px;
            color: white;
            box-shadow: 0 10px 40px rgba(239, 68, 68, 0.3);
        }
        
        .warning-card h2 {
            color: #ef4444;
            margin-bottom: 12px;
            font-size: 24px;
        }
        
        .warning-card p {
            color: var(--muted);
            font-size: 15px;
            line-height: 1.7;
        }
        
        .info-list {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 28px;
            margin-bottom: 24px;
        }
        
        .info-list h4 {
            color: var(--accent);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 16px;
        }
        
        .info-list ul {
            list-style: none;
            padding: 0;
        }
        
        .info-list li {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
            color: var(--text-secondary);
            font-size: 14px;
        }
        
        .info-list li:last-child {
            border-bottom: none;
        }
        
        .info-list li i {
            color: var(--green);
            margin-top: 3px;
        }
        
        .info-list li.warning-item i {
            color: var(--yellow);
        }
        
        .reassurance-card {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.05));
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 32px;
        }
        
        .reassurance-card h4 {
            color: var(--green);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .reassurance-card p {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
        }
        
        .action-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
        }
        
        .btn-cancel {
            background: var(--border);
            color: var(--text);
            padding: 14px 32px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }
        
        .btn-cancel:hover {
            background: var(--muted);
            transform: translateY(-2px);
        }
        
        .btn-delete {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            padding: 14px 32px;
            border-radius: 12px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.3);
        }
        
        .btn-delete:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
        }
        
        /* SweetAlert custom styles */
        .swal2-popup {
            background: var(--card) !important;
            color: var(--text) !important;
            border: 1px solid var(--border) !important;
            border-radius: 16px !important;
        }
        .swal2-title, .swal2-html-container {
            color: var(--text) !important;
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
        <a href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/account-settings.php">
            <i class="fas fa-cog"></i> Paramètres
        </a>
        
        <div class="nav-divider"></div>
        
        <a href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/account-delete.php" class="active">
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
    <div class="delete-container">
        
        <!-- Page Header -->
        <div class="page-header" style="justify-content: center; text-align: center;">
            <div>
                <h1 class="page-title"><i class="fas fa-user-slash" style="color: #ef4444;"></i> Désactiver le compte</h1>
                <div class="breadcrumb" style="justify-content: center;">
                    <a href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/dashboard.php">Tableau de bord</a>
                    <i class="fas fa-chevron-right"></i>
                    <span>Désactivation</span>
                </div>
            </div>
        </div>

        <!-- Warning Card -->
        <div class="warning-card">
            <div class="warning-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h2>Êtes-vous sûr ?</h2>
            <p>
                Vous êtes sur le point de désactiver votre compte. Cette action est réversible, 
                mais vous ne pourrez plus accéder à votre compte jusqu'à sa réactivation.
            </p>
        </div>

        <!-- Info List -->
        <div class="info-list">
            <h4><i class="fas fa-info-circle"></i> Ce qui va se passer :</h4>
            <ul>
                <li>
                    <i class="fas fa-check"></i>
                    <span>Votre compte sera <strong>désactivé</strong>, pas supprimé définitivement</span>
                </li>
                <li class="warning-item">
                    <i class="fas fa-exclamation"></i>
                    <span>Vous ne pourrez plus vous connecter avec ce compte</span>
                </li>
                <li>
                    <i class="fas fa-check"></i>
                    <span>Vos données seront conservées par les administrateurs</span>
                </li>
                <li>
                    <i class="fas fa-check"></i>
                    <span>Vous pourrez demander la réactivation de votre compte plus tard</span>
                </li>
                <li>
                    <i class="fas fa-check"></i>
                    <span>Pour réactiver, il suffira de refaire une demande comme lors de l'inscription</span>
                </li>
            </ul>
        </div>

        <!-- Reassurance Card -->
        <div class="reassurance-card">
            <h4><i class="fas fa-shield-alt"></i> Bonne nouvelle !</h4>
            <p>
                Votre compte n'est pas supprimé définitivement. Un administrateur peut réactiver 
                votre compte à tout moment si vous le demandez. Vos données et votre historique 
                seront préservés.
            </p>
        </div>

        <!-- Action Buttons -->
        <form id="deleteForm" method="post" action="<?= BASE_URL ?>PerFranMVC/Controller/delete-account.php">
            <div class="action-buttons">
                <a href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/dashboard.php" class="btn-cancel">
                    <i class="fas fa-arrow-left"></i> Annuler
                </a>
                <button type="button" class="btn-delete" onclick="confirmDelete()">
                    <i class="fas fa-user-slash"></i> Désactiver mon compte
                </button>
            </div>
        </form>

    </div>
</main>

<script>
function confirmDelete() {
    Swal.fire({
        title: "Désactiver votre compte ?",
        html: `
            <div style="text-align: left; font-size: 14px; color: #94a3b8;">
                <p style="margin-bottom: 12px;"><strong>⚠️ Attention :</strong></p>
                <ul style="margin-left: 20px; margin-bottom: 16px;">
                    <li>Votre compte sera désactivé immédiatement</li>
                    <li>Vous serez déconnecté</li>
                    <li>Vos données ne seront <strong>pas supprimées</strong></li>
                </ul>
                <p style="color: #10b981;">
                    ✅ Pour récupérer votre compte, vous pourrez faire une nouvelle demande d'activation 
                    (comme lors de votre inscription). Un administrateur pourra alors réactiver votre compte.
                </p>
            </div>
        `,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#ef4444",
        cancelButtonColor: "#64748b",
        confirmButtonText: '<i class="fas fa-user-slash"></i> Oui, désactiver',
        cancelButtonText: '<i class="fas fa-times"></i> Annuler',
        reverseButtons: true,
        background: 'var(--card)',
        color: 'var(--text)'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: "Compte désactivé",
                text: "Votre compte a été désactivé. Vous allez être redirigé...",
                icon: "success",
                timer: 2500,
                showConfirmButton: false,
                background: 'var(--card)',
                color: 'var(--text)'
            }).then(() => {
                document.getElementById('deleteForm').submit();
            });
        }
    });
}

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
</script>

</body>
</html>
