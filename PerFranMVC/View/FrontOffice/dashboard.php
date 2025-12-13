<?php
ob_start('ob_gzhandler');
session_start();

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../Controller/UserC.php';

// Set cache headers
header('Cache-Control: public, max-age=3600');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
header('Vary: Accept-Encoding');

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

$user['streak'] = $user['streak'] ?? 0;
$user['wins'] = $user['wins'] ?? 0;
$user['losses'] = $user['losses'] ?? 0;
$user['gamesPlayed1'] = $user['gamesPlayed1'] ?? 0;
$user['gamesPlayed2'] = $user['gamesPlayed2'] ?? 0;
$user['gamesPlayed3'] = $user['gamesPlayed3'] ?? 0;
$user['totalScore1'] = $user['totalScore1'] ?? 0;
$user['totalScore2'] = $user['totalScore2'] ?? 0;
$user['totalScore3'] = $user['totalScore3'] ?? 0;

$totalGames = $user['gamesPlayed1'] + $user['gamesPlayed2'] + $user['gamesPlayed3'];
$totalScore = $user['totalScore1'] + $user['totalScore2'] + $user['totalScore3'];
$winRate = ($user['wins'] + $user['losses']) > 0 
    ? round(($user['wins'] / ($user['wins'] + $user['losses'])) * 100, 1) 
    : 0;

// Get real recent activities from database
try {
    $pdo = config::getConnexion();
    $stmt = $pdo->prepare("SELECT * FROM game_activities WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([':user_id' => $user['uid']]);
    $recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $recentActivities = [];
}

// Avatar initials
$initials = strtoupper(substr($user['username'], 0, 2));
$avatarUrl = $user['avatar'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - PerFran</title>
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <style>:root{--bg:#0f172a;--text:#e2e8f0}body{font-family:Inter,-apple-system,sans-serif;background:var(--bg);color:var(--text);margin:0}.sidebar{width:280px;position:fixed;height:100vh;background:linear-gradient(180deg,#026875,#074149)}</style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/assets/css/user-dashboard.css">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"></noscript>
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts" defer></script>
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
    <!-- Logo -->
    <div style="padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px;">
        <a href="<?= BASE_URL ?>index.php" style="display: inline-block;">
            <img src="<?= BASE_URL ?>PerFranMVC/View/Perfran.png" alt="PerFran" style="height: 50px; width: auto; cursor: pointer;" loading="lazy">
        </a>
    </div>
    
    <div class="avatar-container">
        <div class="avatar">
            <?php if ($avatarUrl): ?>
                <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Avatar" loading="lazy">
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
                <h1 class="page-title">Bienvenue, <span><?= htmlspecialchars($user['username']) ?></span> ! 👋</h1>
                <p style="color: var(--muted); margin-top: 8px;">Voici un aperçu de vos performances</p>
            </div>
            <?php if (!empty($_SESSION['role']) && $_SESSION['role'] == 1): ?>
            <a href="<?= BASE_URL ?>PerFranMVC/View/BackOffice/admin-dashboard.php" class="btn btn-primary">
                <i class="fas fa-user-shield"></i> Panneau Admin
            </a>
            <?php endif; ?>
        </div>

        <!-- Welcome Card with Progress -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-line"></i> Progression du profil</h3>
                <span class="badge badge-success"><i class="fas fa-check"></i> Compte vérifié</span>
            </div>
            <div class="progress-container">
                <div class="progress-header">
                    <span>Profil complété</span>
                    <span style="color: var(--accent); font-weight: 600;">88%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress" style="width: 88%;"></div>
                </div>
            </div>
            <div class="badges">
                <span class="badge badge-success"><i class="fas fa-check-circle"></i> Email vérifié</span>
                <span class="badge badge-info"><i class="fas fa-gamepad"></i> Joueur actif</span>
                <span class="badge badge-warning"><i class="fas fa-fire"></i> Série de <?= $user['streak'] ?> jours</span>
                <span class="badge badge-primary"><i class="fas fa-plus"></i> Ajouter un avatar</span>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-gamepad" style="color: white;"></i>
                </div>
                <div class="stat-content">
                    <h4><?= $totalGames ?></h4>
                    <span>Parties jouées</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-trophy" style="color: white;"></i>
                </div>
                <div class="stat-content">
                    <h4><?= $user['wins'] ?></h4>
                    <span>Victoires</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-star" style="color: white;"></i>
                </div>
                <div class="stat-content">
                    <h4><?= number_format($totalScore) ?></h4>
                    <span>Score total</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon yellow">
                    <i class="fas fa-percentage" style="color: white;"></i>
                </div>
                <div class="stat-content">
                    <h4><?= $winRate ?>%</h4>
                    <span>Taux de victoire</span>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="chart-section">
            <!-- Main Chart -->
            <div class="chart-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-area"></i> Statistiques de jeu</h3>
                </div>
                <div id="gameStatsChart"></div>
            </div>

            <!-- Donut Chart -->
            <div class="chart-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-pie-chart"></i> Répartition</h3>
                </div>
                <div id="trafficChart"></div>
                <div style="margin-top: 20px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px;">
                        <span><span style="color: #8b5cf6;">●</span> Jeu 1</span>
                        <span style="font-weight: 600;"><?= $user['gamesPlayed1'] ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px;">
                        <span><span style="color: #10b981;">●</span> Jeu 2</span>
                        <span style="font-weight: 600;"><?= $user['gamesPlayed2'] ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 14px;">
                        <span><span style="color: #fbbf24;">●</span> Jeu 3</span>
                        <span style="font-weight: 600;"><?= $user['gamesPlayed3'] ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history"></i> Activités récentes</h3>
                <button class="btn btn-primary btn-sm">Voir tout</button>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Activité</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Score</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentActivities)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px; color: #94a3b8;">
                                <i class="fas fa-info-circle" style="font-size: 24px; margin-bottom: 10px; display: block;"></i>
                                Aucune activité récente. Commence à jouer pour voir tes statistiques!
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php 
                            $counter = 1;
                            $gameNames = [1 => 'Jeu de Grammaire', 2 => 'Jeu de Vocabulaire', 3 => 'Jeu de Conjugaison'];
                            $gameIcons = [1 => 'fa-spell-check', 2 => 'fa-book', 3 => 'fa-language'];
                            $gradients = [
                                'linear-gradient(135deg, #8b5cf6, #7c3aed)',
                                'linear-gradient(135deg, #10b981, #059669)',
                                'linear-gradient(135deg, #fbbf24, #f59e0b)',
                                'linear-gradient(135deg, #3b82f6, #2563eb)',
                                'linear-gradient(135deg, #ec4899, #db2777)'
                            ];
                            foreach ($recentActivities as $activity): 
                                $gameType = $activity['game_type'] ?? 1;
                                $gameName = $gameNames[$gameType] ?? 'Jeu';
                                $icon = $gameIcons[$gameType] ?? 'fa-gamepad';
                                $gradient = $gradients[($counter - 1) % count($gradients)];
                                $date = date('d M Y', strtotime($activity['created_at'] ?? 'now'));
                                $won = $activity['won'] ?? 0;
                                $statusBadge = $won ? 'status-success' : 'status-danger';
                                $statusText = $won ? 'Victoire' : 'Défaite';
                            ?>
                        <tr>
                            <td><?= str_pad($counter, 2, '0', STR_PAD_LEFT) ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 40px; height: 40px; background: <?= $gradient ?>; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas <?= $icon ?>" style="color: white;"></i>
                                    </div>
                                    <span><?= htmlspecialchars($gameName) ?></span>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($activity['activity_type'] ?? 'Match rapide') ?></td>
                            <td><?= $date ?></td>
                            <td><strong><?= $activity['score'] ?>/<?= $activity['max_score'] ?></strong></td>
                            <td><span class="status-badge <?= $statusBadge ?>"><?= $statusText ?></span></td>
                        </tr>
                            <?php 
                            $counter++;
                            endforeach; 
                            ?>
                        <?php endif; ?>
                    </tbody>
                </table>
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

// Update charts theme when theme changes
(function(){
    const originalSetAttribute = Element.prototype.setAttribute;
    Element.prototype.setAttribute = function(name, value){
        if(name === 'data-theme'){
            // Update charts when theme changes
            if (typeof gameStatsChart !== 'undefined') {
                gameStatsChart.updateOptions({ theme: { mode: value } });
            }
            if (typeof trafficChart !== 'undefined') {
                trafficChart.updateOptions({ theme: { mode: value } });
            }
        }
        return originalSetAttribute.call(this, name, value);
    };
})();

// Mobile sidebar toggle
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('open');
    document.querySelector('.sidebar-overlay').classList.toggle('show');
}

// Game Stats Chart
const gameStatsOptions = {
    series: [{
        name: 'Victoires',
        data: [<?= $user['wins'] ?>, 35, 40, 50, 45, 42]
    }, {
        name: 'Défaites',
        data: [<?= $user['losses'] ?>, 15, 18, 22, 20, 18]
    }],
    chart: {
        type: 'area',
        height: 320,
        toolbar: { show: false },
        background: 'transparent',
        fontFamily: 'Inter, sans-serif'
    },
    colors: ['#10b981', '#ef4444'],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 3 },
    xaxis: {
        categories: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'],
        labels: { style: { colors: '#94a3b8' } },
        axisBorder: { show: false },
        axisTicks: { show: false }
    },
    yaxis: {
        labels: { style: { colors: '#94a3b8' } }
    },
    grid: {
        borderColor: '#334155',
        strokeDashArray: 4,
        padding: { left: 10, right: 10 }
    },
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.5,
            opacityTo: 0.1
        }
    },
    legend: {
        position: 'top',
        horizontalAlign: 'right',
        labels: { colors: '#94a3b8' }
    },
    theme: { mode: localStorage.getItem('pref-theme') || 'dark' }
};

const gameStatsChart = new ApexCharts(document.querySelector("#gameStatsChart"), gameStatsOptions);
gameStatsChart.render();

// Traffic Donut Chart
const trafficOptions = {
    series: [<?= $user['gamesPlayed1'] ?>, <?= $user['gamesPlayed2'] ?>, <?= $user['gamesPlayed3'] ?>],
    chart: {
        type: 'donut',
        height: 260,
        background: 'transparent'
    },
    colors: ['#8b5cf6', '#10b981', '#fbbf24'],
    labels: ['Jeu 1', 'Jeu 2', 'Jeu 3'],
    legend: { show: false },
    plotOptions: {
        pie: {
            donut: {
                size: '75%',
                labels: {
                    show: true,
                    name: { show: true, color: '#94a3b8' },
                    value: { show: true, color: '#e2e8f0', fontSize: '24px', fontWeight: 700 },
                    total: {
                        show: true,
                        label: 'Total',
                        color: '#94a3b8',
                        formatter: function(w) {
                            return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                        }
                    }
                }
            }
        }
    },
    stroke: { show: false },
    dataLabels: { enabled: false },
    theme: { mode: localStorage.getItem('pref-theme') || 'dark' }
};

const trafficChart = new ApexCharts(document.querySelector("#trafficChart"), trafficOptions);
trafficChart.render();
</script>


<!-- AI Mascot Assistant -->
<link rel="stylesheet" href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/assets/css/mascot.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
<script>
    window.PERFRAN_AI_API_URL = '<?= PERFRAN_AI_API_URL ?>';
</script>
<script defer>
    // Lazy-load mascot after page becomes interactive to reduce initial render cost
    (function(){
        function loadMascot(){
            var s = document.createElement('script');
            s.src = '<?= BASE_URL ?>PerFranMVC/View/FrontOffice/assets/js/mascot.js';
            s.defer = true;
            document.body.appendChild(s);
        }
        if(document.readyState === 'complete' || document.readyState === 'interactive'){
            setTimeout(loadMascot, 700);
        } else {
            window.addEventListener('DOMContentLoaded', function(){ setTimeout(loadMascot, 700); });
        }
    })();
</script>

</body>
</html>
<?php ob_end_flush(); ?>
