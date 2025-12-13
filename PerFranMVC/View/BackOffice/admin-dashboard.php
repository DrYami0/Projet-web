<?php

session_start();
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../Controller/UserC.php';

if (empty($_SESSION['user']) || $_SESSION['user']['role'] != 1) {
    header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/login.php');
    exit;
}

$userRepo = new UserC();

$totalUsers = $userRepo->countUsers();
$activeUsers = $userRepo->countActiveUsers();
$pendingUsers = $userRepo->countPendingUsers();
$bannedUsers = $userRepo->countBannedUsers();
$todaySignups = $userRepo->countTodaySignups();
$weekSignups = $userRepo->countWeekSignups();

$monthlyData = $userRepo->getMonthlySignups();
$chartLabels = array_column($monthlyData, 'month_name');
$chartValues = array_column($monthlyData, 'count');

$dailyData = $userRepo->getDailySignups();
$dailyLabels = array_column($dailyData, 'day_name');
$dailyValues = array_column($dailyData, 'count');

$recentUsers = $userRepo->getRecentUsers(5);

$admins = $userRepo->countAdmins();
$regularUsers = $totalUsers - $admins;

$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $users = $userRepo->searchUsers($q);
} else {
    $users = $userRepo->findAll();
}

$deleted = $userRepo->listDeleted();
$currentPage = 'dashboard';

$currentUser = $userRepo->findByUsername($_SESSION['user']['username'] ?? '');
$adminAvatar = $currentUser['avatar'] ?? null;
$adminInitials = strtoupper(substr($_SESSION['user']['username'] ?? 'A', 0, 1));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - Panneau d'administration</title>
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <style>:root{--bg-dark:#0f172a;--text-primary:#f1f5f9}body{font-family:Inter,-apple-system,sans-serif;background:var(--bg-dark);color:var(--text-primary);margin:0}.sidebar{width:280px;position:fixed;height:100vh;background:#1e293b}</style>
    <link rel="stylesheet" href="<?=htmlspecialchars(BASE_URL)?>PerFranMVC/View/BackOffice/assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"></noscript>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts" defer></script>
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
                    <a href="<?=BASE_URL?>PerFranMVC/View/BackOffice/admin-dashboard.php" class="nav-link active">
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
                        <?php if ($pendingUsers > 0): ?>
                            <span class="nav-badge"><?=$pendingUsers?></span>
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
                    <h1 class="page-title">Tableau de bord</h1>
                </div>
                <div class="header-right">
                    <a href="<?=BASE_URL?>PerFranMVC/View/FrontOffice/dashboard.php" class="header-btn btn-user-view">
                        <i class="fas fa-external-link-alt"></i>
                        <span>Vue Utilisateur</span>
                    </a>
                    <div class="user-menu">
                        <div class="user-avatar">
                            <?php if ($adminAvatar): ?>
                                <img src="<?=htmlspecialchars($adminAvatar)?>" alt="Avatar" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
                            <?php else: ?>
                                <?=$adminInitials?>
                            <?php endif; ?>
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
                
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card primary">
                        <div class="stat-header">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-trend up">
                                <i class="fas fa-arrow-up"></i>
                                +<?=$weekSignups?> cette semaine
                            </div>
                        </div>
                        <div class="stat-value"><?=$totalUsers?></div>
                        <div class="stat-label">Total Utilisateurs</div>
                    </div>
                    
                    <div class="stat-card success">
                        <div class="stat-header">
                            <div class="stat-icon">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div class="stat-trend up">
                                <i class="fas fa-circle"></i>
                                En ligne
                            </div>
                        </div>
                        <div class="stat-value"><?=$activeUsers?></div>
                        <div class="stat-label">Utilisateurs Actifs</div>
                    </div>
                    
                    <div class="stat-card warning">
                        <div class="stat-header">
                            <div class="stat-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="stat-trend up">
                                <i class="fas fa-arrow-up"></i>
                                +<?=$todaySignups?> aujourd'hui
                            </div>
                        </div>
                        <div class="stat-value"><?=$pendingUsers?></div>
                        <div class="stat-label">En Attente d'Approbation</div>
                    </div>
                    
                    <div class="stat-card danger">
                        <div class="stat-header">
                            <div class="stat-icon">
                                <i class="fas fa-user-slash"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?=$bannedUsers?></div>
                        <div class="stat-label">Utilisateurs Bannis</div>
                    </div>
                </div>
                
                <!-- Charts Grid -->
                <div class="charts-grid">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="chart-title">Évolution des Inscriptions</h3>
                            <div class="chart-actions">
                                <button class="chart-btn active" data-range="week">7 jours</button>
                                <button class="chart-btn" data-range="month">6 mois</button>
                            </div>
                        </div>
                        <div class="chart-container" id="signupsChart"></div>
                    </div>
                    
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="chart-title">Répartition des Rôles</h3>
                        </div>
                        <div class="chart-container" id="rolesChart"></div>
                    </div>
                </div>
                
                <!-- Users Table -->
                <div class="table-card">
                    <div class="table-header">
                        <h3 class="table-title">Tous les Utilisateurs</h3>
                        <div class="table-search">
                            <form method="get" style="display: flex; gap: 12px;">
                                <input type="text" name="q" value="<?=htmlspecialchars($q)?>" class="search-input" placeholder="Rechercher un utilisateur...">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-search"></i>
                                </button>
                            </form>
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
                                    <tr><td colspan="5" style="text-align: center; padding: 40px;">Aucun utilisateur trouvé.</td></tr>
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
                                            <td><?=htmlspecialchars(date('d/m/Y', strtotime($u['creationDate'] ?? 'now')))?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="<?=BASE_URL?>PerFranMVC/Controller/admin.php?action=edit&uid=<?=urlencode($u['uid'])?>" class="action-btn edit" title="Modifier">
                                                        <i class="fas fa-pen"></i>
                                                    </a>
                                                    <?php if ($status !== 'Active'): ?>
                                                        <a href="<?=BASE_URL?>PerFranMVC/Controller/admin.php?action=approve_user&username=<?=urlencode($u['username'])?>" class="action-btn approve" title="Approuver">
                                                            <i class="fas fa-check"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if (($u['role'] ?? 0) == 0): ?>
                                                        <a href="<?=BASE_URL?>PerFranMVC/Controller/admin.php?action=promote&uid=<?=urlencode($u['uid'])?>" class="action-btn view" title="Promouvoir admin">
                                                            <i class="fas fa-crown"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if (!$isBanned): ?>
                                                        <a href="<?=BASE_URL?>PerFranMVC/Controller/admin.php?action=ban&uid=<?=urlencode($u['uid'])?>" class="action-btn ban" title="Bannir">
                                                            <i class="fas fa-ban"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="<?=BASE_URL?>PerFranMVC/Controller/admin.php?action=delete&uid=<?=urlencode($u['uid'])?>" class="action-btn delete" title="Supprimer" onclick="return confirm('Supprimer définitivement cet utilisateur ?')">
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
                
                <!-- Deleted Users Section -->
                <?php if (!empty($deleted)): ?>
                <div class="table-card" style="margin-top: 24px;">
                    <div class="table-header">
                        <h3 class="table-title">Comptes Supprimés</h3>
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
                                <?php foreach ($deleted as $d): ?>
                                    <tr>
                                        <td><?=htmlspecialchars($d['username'] ?? '')?></td>
                                        <td><?=htmlspecialchars($d['email'] ?? '')?></td>
                                        <td><?=htmlspecialchars($d['deleted_at'] ?? '')?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="<?=BASE_URL?>PerFranMVC/Controller/admin.php?action=restore&uid=<?=urlencode($d['uid'])?>" class="action-btn approve" title="Restaurer">
                                                    <i class="fas fa-undo"></i>
                                                </a>
                                                <a href="<?=BASE_URL?>PerFranMVC/Controller/admin.php?action=delete&uid=<?=urlencode($d['uid'])?>" class="action-btn delete" title="Supprimer définitivement" onclick="return confirm('Supprimer définitivement ?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
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
        
        // Charts
        const dailyLabels = <?=json_encode($dailyLabels ?: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'])?>;
        const dailyValues = <?=json_encode(array_map('intval', $dailyValues ?: [0]))?>;
        const monthlyLabels = <?=json_encode($chartLabels ?: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun'])?>;
        const monthlyValues = <?=json_encode(array_map('intval', $chartValues ?: [0]))?>;
        
        // Signups Chart
        const signupsOptions = {
            series: [{
                name: 'Inscriptions',
                data: dailyValues
            }],
            chart: {
                type: 'area',
                height: 300,
                background: 'transparent',
                toolbar: { show: false },
                zoom: { enabled: false }
            },
            colors: ['#6366f1'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.1,
                    stops: [0, 90, 100]
                }
            },
            dataLabels: { enabled: false },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            xaxis: {
                categories: dailyLabels,
                labels: { style: { colors: '#94a3b8' } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: { style: { colors: '#94a3b8' } }
            },
            grid: {
                borderColor: '#334155',
                strokeDashArray: 4
            },
            tooltip: {
                theme: 'dark',
                x: { show: true }
            }
        };
        
        const signupsChart = new ApexCharts(document.querySelector("#signupsChart"), signupsOptions);
        signupsChart.render();
        
        // Chart range buttons
        document.querySelectorAll('.chart-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.chart-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                if (this.dataset.range === 'month') {
                    signupsChart.updateOptions({
                        xaxis: { categories: monthlyLabels }
                    });
                    signupsChart.updateSeries([{ data: monthlyValues }]);
                } else {
                    signupsChart.updateOptions({
                        xaxis: { categories: dailyLabels }
                    });
                    signupsChart.updateSeries([{ data: dailyValues }]);
                }
            });
        });
        
        // Roles Donut Chart
        const rolesOptions = {
            series: [<?=$regularUsers?>, <?=$admins?>],
            chart: {
                type: 'donut',
                height: 300,
                background: 'transparent'
            },
            colors: ['#6366f1', '#10b981'],
            labels: ['Utilisateurs', 'Admins'],
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            name: {
                                show: true,
                                color: '#f1f5f9'
                            },
                            value: {
                                show: true,
                                color: '#f1f5f9',
                                fontSize: '24px',
                                fontWeight: 700
                            },
                            total: {
                                show: true,
                                label: 'Total',
                                color: '#94a3b8',
                                fontSize: '14px',
                                formatter: function(w) {
                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                }
                            }
                        }
                    }
                }
            },
            dataLabels: { enabled: false },
            legend: {
                position: 'bottom',
                labels: { colors: '#f1f5f9' }
            },
            stroke: { show: false },
            tooltip: { theme: 'dark' }
        };
        
        const rolesChart = new ApexCharts(document.querySelector("#rolesChart"), rolesOptions);
        rolesChart.render();
    </script>
</body>
</html>
