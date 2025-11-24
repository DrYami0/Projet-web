<?php
session_start();

require_once __DIR__ . '/../../controller/config.php';
require_once __DIR__ . '/../../controller/userC.php';

if (!isset($_SESSION['uid'])) {
    header('Location: ' . BASE_URL . 'view/FrontOffice/login.php');
    exit;
}

$userC = new UserC();
$user = $userC->findByUsername($_SESSION['uid']);

if (!$user) { 
    session_destroy(); 
    header('Location: ' . BASE_URL . 'view/FrontOffice/login.php'); 
    exit; 
}

// Test values
$user['streak'] = 12;
$user['wins'] = 45;
$user['losses'] = 20;
$user['gamesPlayed1'] = 30;
$user['gamesPlayed2'] = 25;
$user['gamesPlayed3'] = 10;
$user['totalScore1'] = 1300;
$user['totalScore2'] = 800;
$user['totalScore3'] = 600;

$totalGames = $user['gamesPlayed1'] + $user['gamesPlayed2'] + $user['gamesPlayed3'];
$winRate = ($user['wins'] + $user['losses']) > 0 
    ? round(($user['wins'] / ($user['wins'] + $user['losses'])) * 100, 1) 
    : 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PerfRan | Tableau de bord</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <style>
        :root {
            --sidebar-start: #026875;
            --sidebar-end: #074149;
            --accent: #8b5cf6;
            --green: #10b981;
            --yellow: #fbbf24;
            --orange: #f97316;
            --blue: #3b82f6;
            --text: #e2e8f0;
            --muted: #94a3b8;
            --border: #334155;
            --bg: #0f172a;
            --card: #1e293b;
            --progress: #10b981;
            --sidebar-text: #e6f7f6;
        }

        [data-theme="light"] {
            --bg: #f7fafc;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #6b7280;
            --border: #e2e8f0;
            --sidebar-start: #026875;
            --sidebar-end: #074149;
            --sidebar-text: #f0fdfa;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
        }
        
        .sidebar {
            width: 280px;
            padding: 30px 24px;
            border-right: 1px solid var(--border);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            background: linear-gradient(180deg, var(--sidebar-start), var(--sidebar-end));
        }

        .profile-img {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: #ec4899;
            margin: 0 auto 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
            font-weight: bold;
        }

        .username {
            font-size: 19px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 4px;
            color: var(--sidebar-text);
        }

        .email {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.85);
            text-align: center;
            margin-bottom: 16px;
        }

        nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 16px;
            color: var(--sidebar-text);
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 6px;
            font-size: 14px;
            transition: 0.2s;
        }

        nav a:hover,
        nav a.active {
            background: rgba(255, 255, 255, 0.06);
            color: #fff;
        }

        nav a.active {
            font-weight: 600;
        }

        .logout {
            color: #fce7e9;
            margin-top: 26px;
        }

        .theme-toggle {
            display: block;
            margin: 18px auto 8px;
            padding: 10px 12px;
            border-radius: 8px;
            border: none;
            background: rgba(255, 255, 255, 0.08);
            color: var(--sidebar-text);
            cursor: pointer;
            font-weight: 600;
        }

        .main {
            flex: 1;
            margin-left: 280px;
            padding: 40px;
        }

        .header {
            background: var(--card);
            padding: 28px;
            border-radius: 16px;
            margin-bottom: 28px;
            border: 1px solid var(--border);
        }

        .progress-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 16px;
            font-size: 14px;
        }

        .progress-bar {
            height: 8px;
            background: #334155;
            border-radius: 4px;
            overflow: hidden;
            flex: 1;
            margin: 0 16px;
        }

        .progress {
            height: 100%;
            background: var(--progress);
            width: 88%;
            border-radius: 4px;
        }

        .badges {
            display: flex;
            gap: 12px;
            margin-top: 16px;
            flex-wrap: wrap;
        }

        .badge {
            background: rgba(16, 185, 129, 0.15);
            color: var(--green);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
        }

        /* Counter Cards */
        .counter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 28px;
        }

        .counter-card {
            background: var(--card);
            padding: 24px;
            border-radius: 12px;
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .counter-icon {
            width: 64px;
            height: 64px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
        }

        .counter-icon.success { background: var(--green); }
        .counter-icon.info { background: var(--blue); }
        .counter-icon.warning { background: var(--yellow); }
        .counter-icon.primary { background: var(--accent); }

        .counter-content h4 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .counter-content span {
            font-size: 14px;
            color: var(--muted);
        }

        /* Chart Section */
        .chart-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 28px;
        }

        .chart-card {
            background: var(--card);
            padding: 24px;
            border-radius: 12px;
            border: 1px solid var(--border);
        }

        .chart-header {
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
        }

        .chart-header h5 {
            font-size: 18px;
            font-weight: 600;
        }

        /* Traffic Chart */
        .traffic-legend {
            list-style: none;
            margin-top: 20px;
        }

        .traffic-legend li {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 0;
            font-size: 14px;
        }

        .traffic-legend li::before {
            content: '●';
            font-size: 20px;
        }

        .traffic-legend li:nth-child(1)::before { color: var(--accent); }
        .traffic-legend li:nth-child(2)::before { color: var(--green); }
        .traffic-legend li:nth-child(3)::before { color: var(--yellow); }
        .traffic-legend li:nth-child(4)::before { color: #ef4444; }

        /* Recent Activity Table */
        .table-card {
            background: var(--card);
            border-radius: 12px;
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .table-header {
            padding: 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header h5 {
            font-size: 18px;
            font-weight: 600;
        }

        .btn-primary {
            background: var(--accent);
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
        }

        .table-wrapper {
            padding: 24px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 12px;
            font-size: 13px;
            font-weight: 600;
            color: var(--muted);
            border-bottom: 1px solid var(--border);
        }

        td {
            padding: 16px 12px;
            font-size: 14px;
            border-bottom: 1px solid var(--border);
        }

        tr:last-child td {
            border-bottom: none;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-booked {
            background: rgba(16, 185, 129, 0.15);
            color: var(--green);
        }

        .status-reserved {
            background: rgba(59, 130, 246, 0.15);
            color: var(--blue);
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
            }

            .main {
                margin-left: 0;
            }

            .chart-section {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="profile-img">PR</div>
    <div class="username"><?= htmlspecialchars($user['username']) ?></div>
    <div class="email"><?= htmlspecialchars($user['email']) ?></div>

    <button id="themeToggle" class="theme-toggle" aria-pressed="false">Toggle theme</button>
    
    <nav>
        <a href="<?= BASE_URL ?>view/FrontOffice/dashboard.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : '' ?>">
            Tableau de bord
        </a>
        <a href="<?= BASE_URL ?>view/FrontOffice/account-profile.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'account-profile.php') ? 'active' : '' ?>">
            Mon Profil
        </a>
        <a href="<?= BASE_URL ?>view/FrontOffice/account-payment-details.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'account-payment-details.php') ? 'active' : '' ?>">
            Abonnement / Don
        </a>
        <a href="<?= BASE_URL ?>view/FrontOffice/account-settings.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'account-settings.php') ? 'active' : '' ?>">
            Paramètres
        </a>
        <a href="<?= BASE_URL ?>view/FrontOffice/account-delete.php" style="color:#fff;">
            Supprimer le compte
        </a>
        <a href="<?= BASE_URL ?>controller/auth.php?action=logout" class="logout">
            Déconnexion
        </a>
    </nav>
</aside>

<main class="main">
    <!-- Welcome Header -->
    <div class="header">
        <h1>Bienvenue, <?= htmlspecialchars($user['username']) ?> !</h1>
        <div class="progress-container">
            <span>Profil complété</span>
            <div class="progress-bar"><div class="progress"></div></div>
            <span>88%</span>
        </div>
        <div class="badges">
            <div class="badge">✓ Email vérifié</div>
            <div class="badge">🎮 Joueur actif</div>
            <div class="badge">+ Ajouter un avatar</div>
        </div>
    </div>

    <!-- Counter Cards -->
    <div class="counter-grid">
        <div class="counter-card">
            <div class="counter-icon success">🎮</div>
            <div class="counter-content">
                <h4><?= $totalGames ?></h4>
                <span>Parties jouées</span>
            </div>
        </div>

        <div class="counter-card">
            <div class="counter-icon info">💰</div>
            <div class="counter-content">
                <h4><?= $user['totalScore1'] + $user['totalScore2'] + $user['totalScore3'] ?></h4>
                <span>Score total</span>
            </div>
        </div>

        <div class="counter-card">
            <div class="counter-icon warning">👥</div>
            <div class="counter-content">
                <h4><?= $winRate ?>%</h4>
                <span>Taux de victoire</span>
            </div>
        </div>

        <div class="counter-card">
            <div class="counter-icon primary">⭐</div>
            <div class="counter-content">
                <h4><?= $user['streak'] ?></h4>
                <span>Série actuelle</span>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="chart-section">
        <!-- Main Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h5>Statistiques de jeu</h5>
            </div>
            <div id="gameStatsChart"></div>
        </div>

        <!-- Traffic Donut -->
        <div class="chart-card">
            <div class="chart-header">
                <h5>Répartition des parties</h5>
            </div>
            <div id="trafficChart"></div>
            <ul class="traffic-legend">
                <li>Jeu 1</li>
                <li>Jeu 2</li>
                <li>Jeu 3</li>
                <li>Autres</li>
            </ul>
        </div>
    </div>

    <!-- Recent Activity Table -->
    <div class="table-card">
        <div class="table-header">
            <h5>Activités récentes</h5>
            <button class="btn-primary">Voir tout</button>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Activité</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>01</td>
                        <td>Partie de Jeu 1</td>
                        <td>Match rapide</td>
                        <td>22 Nov</td>
                        <td><span class="status-badge status-booked">Terminé</span></td>
                        <td><button class="btn-primary">Voir</button></td>
                    </tr>
                    <tr>
                        <td>02</td>
                        <td>Tournoi Jeu 2</td>
                        <td>Compétition</td>
                        <td>21 Nov</td>
                        <td><span class="status-badge status-reserved">En cours</span></td>
                        <td><button class="btn-primary">Voir</button></td>
                    </tr>
                    <tr>
                        <td>03</td>
                        <td>Entraînement Jeu 3</td>
                        <td>Practice</td>
                        <td>20 Nov</td>
                        <td><span class="status-badge status-booked">Terminé</span></td>
                        <td><button class="btn-primary">Voir</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</main>

<script>
// Theme Toggle
const themeToggle = document.getElementById('themeToggle');
const html = document.documentElement;

(function(){
    const saved = localStorage.getItem('pref-theme') || 'dark';
    function setTheme(t){
        html.setAttribute('data-theme', t);
        const btn = document.getElementById('themeToggle');
        if(btn){
            btn.textContent = t === 'dark' ? '🌞 Light' : '🌙 Dark';
            btn.setAttribute('aria-pressed', String(t==='light'));
        }
    }
    setTheme(saved);
    document.addEventListener('DOMContentLoaded', function(){
        const btn = document.getElementById('themeToggle');
        if(!btn) return;
        btn.addEventListener('click', function(){
            const current = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
            setTheme(current);
            localStorage.setItem('pref-theme', current);
        });
    });
})();

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
        height: 350,
        toolbar: { show: false },
        background: 'transparent'
    },
    colors: ['#10b981', '#ef4444'],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 2 },
    xaxis: {
        categories: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'],
        labels: { style: { colors: '#94a3b8' } }
    },
    yaxis: {
        labels: { style: { colors: '#94a3b8' } }
    },
    grid: {
        borderColor: '#334155',
        strokeDashArray: 4
    },
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.7,
            opacityTo: 0.2
        }
    },
    legend: {
        labels: { colors: '#94a3b8' }
    },
    theme: { mode: 'dark' }
};

const gameStatsChart = new ApexCharts(document.querySelector("#gameStatsChart"), gameStatsOptions);
gameStatsChart.render();

// Traffic Donut Chart
const trafficOptions = {
    series: [<?= $user['gamesPlayed1'] ?>, <?= $user['gamesPlayed2'] ?>, <?= $user['gamesPlayed3'] ?>, 5],
    chart: {
        type: 'donut',
        height: 280
    },
    colors: ['#8b5cf6', '#10b981', '#fbbf24', '#ef4444'],
    labels: ['Jeu 1', 'Jeu 2', 'Jeu 3', 'Autres'],
    legend: { show: false },
    plotOptions: {
        pie: {
            donut: {
                size: '70%',
                labels: {
                    show: true,
                    total: {
                        show: true,
                        label: 'Total',
                        color: '#94a3b8'
                    }
                }
            }
        }
    },
    dataLabels: {
        style: { colors: ['#fff'] }
    },
    theme: { mode: 'dark' }
};

const trafficChart = new ApexCharts(document.querySelector("#trafficChart"), trafficOptions);
trafficChart.render();
</script>

</body>
</html>