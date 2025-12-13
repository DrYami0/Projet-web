<?php
// Include the controller that fetches all the dashboard data
require_once __DIR__ . '/../../Controller/userC-dashboard.php';

$initials = strtoupper(substr($userData['username'] ?? 'US', 0, 2));
$avatarUrl = $userData['avatar'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Tableau de Bord - PerfRan</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/assets/css/user-dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
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
    
    <div class="username"><?= htmlspecialchars($userData['username'] ?? 'Utilisateur') ?></div>
    <div class="email"><?= htmlspecialchars($userData['email'] ?? '') ?></div>
    
    <div class="user-role">
        <?php if (($userData['role'] ?? 0) == 1): ?>
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
        <a href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/dashboard.php" class="active">
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
    <div class="container">
        
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">Bienvenue, <span><?= htmlspecialchars($userData['username'] ?? 'Utilisateur') ?></span> ! 👋</h1>
                <p style="color: var(--muted); margin-top: 8px;">
                    <?php if(!empty($userData['firstName']) && !empty($userData['lastName'])): ?>
                        <?= htmlspecialchars($userData['firstName'] . ' ' . $userData['lastName']) ?> • 
                    <?php endif; ?>
                    <?= htmlspecialchars($userData['email'] ?? '') ?>
                </p>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-gamepad" style="color: white;"></i>
                </div>
                <div class="stat-content">
                    <h4><?= $totalGames ?? 0 ?></h4>
                    <span>Parties jouées</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-trophy" style="color: white;"></i>
                </div>
                <div class="stat-content">
                    <h4><?= $wins ?? 0 ?></h4>
                    <span>Victoires</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-star" style="color: white;"></i>
                </div>
                <div class="stat-content">
                    <h4><?= number_format($totalScore ?? 0) ?></h4>
                    <span>Score total</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon yellow">
                    <i class="fas fa-fire" style="color: white;"></i>
                </div>
                <div class="stat-content">
                    <h4><?= $streak ?? 0 ?> 🔥</h4>
                    <span>Série en cours</span>
                </div>
            </div>
        </div>

        <!-- Game Categories -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-th-large"></i> Catégories de Jeux</h3>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                <div class="stat-card" onclick="location.href='<?= BASE_URL ?>PerFranMVC/View/FrontOffice/game1.html'" style="cursor: pointer;">
                    <div class="stat-icon purple" style="font-size: 32px;">🎯</div>
                    <div class="stat-content">
                        <h4>Jeu 1 - Dictée</h4>
                        <span><?= $gamesPlayed1 ?? 0 ?> parties • Score: <?= number_format($totalScore1 ?? 0) ?></span>
                    </div>
                </div>
                
                <div class="stat-card" onclick="location.href='<?= BASE_URL ?>PerFranMVC/View/FrontOffice/game1.html'" style="cursor: pointer;">
                    <div class="stat-icon green" style="font-size: 32px;">📝</div>
                    <div class="stat-content">
                        <h4>Jeu 2 - Quiz</h4>
                        <span><?= $gamesPlayed2 ?? 0 ?> parties • Score: <?= number_format($totalScore2 ?? 0) ?></span>
                    </div>
                </div>
                
                <div class="stat-card" onclick="location.href='<?= BASE_URL ?>PerFranMVC/View/FrontOffice/game1.html'" style="cursor: pointer;">
                    <div class="stat-icon blue" style="font-size: 32px;">🧩</div>
                    <div class="stat-content">
                        <h4>Jeu 3 - Textes</h4>
                        <span><?= $gamesPlayed3 ?? 0 ?> parties • Score: <?= number_format($totalScore3 ?? 0) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Games -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history"></i> Parties Récentes</h3>
            </div>
            
            <?php if(empty($recentGames)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">🎮</div>
                    <h3>Aucune partie jouée</h3>
                    <p>Commencez à jouer pour voir vos statistiques ici !</p>
                </div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Adversaire</th>
                                <th>Durée</th>
                                <th>Résultat</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recentGames as $game): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($game['timestamp'])) ?></td>
                                    <td><?= htmlspecialchars($game['player1id']==$user['uid']?$game['player2_name']:$game['player1_name']) ?></td>
                                    <td><?= gmdate("i:s", $game['duration']) ?></td>
                                    <td>
                                        <span class="status-badge <?= $game['result']=='Gagné' ? 'status-success' : 'status-danger' ?>">
                                            <?= $game['result'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/game1.html?gid=<?= $game['gid'] ?>" class="btn btn-secondary btn-sm">Détails</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
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
</script>

</body>
</html>
