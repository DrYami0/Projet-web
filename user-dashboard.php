<?php
session_start();
require_once __DIR__ . '/controller/config.php';
require_once __DIR__ . '/controller/userC.php';

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

$totalGames = $user['gamesPlayed1'] + $user['gamesPlayed2'] + $user['gamesPlayed3'];
$winRate = ($user['wins'] + $user['losses']) > 0 ? round(($user['wins'] / ($user['wins'] + $user['losses'])) * 100, 1) : 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PerfRan | Tableau de bord</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-start: #026875;
            --sidebar-end: #074149;
            --accent: #8b5cf6;
            --green: #10b981;
            --text: #e2e8f0;
            --muted: #94a3b8;
            --border: #334155;
            --bg: #0f172a;
            --card: #1e293b;
            --progress: #10b981;
        }
        [data-theme="light"] {
            --bg:#f8fafc; --card:#ffffff; --text:#0f172a; --muted:#6b7280; --border:#e2e8f0;
        }

        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Inter',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; display:flex; }
        
        .sidebar {
            width:280px; background:linear-gradient(180deg,var(--sidebar-start),var(--sidebar-end)); padding:30px 24px; border-right:1px solid var(--border);
        }
        .profile-img {
            width:72px; height:72px; border-radius:50%; background:#ec4899; margin:0 auto 16px; display:flex; align-items:center; justify-content:center;
            font-size:28px; color:white; font-weight:bold;
        }
        .username { font-size:19px; font-weight:700; text-align:center; margin-bottom:4px; color:var(--text); }
        .email { font-size:13px; color:rgba(255,255,255,0.9); text-align:center; margin-bottom:16px; }
        nav a {
            display:flex; align-items:center; gap:12px; padding:11px 16px; color:var(--text); text-decoration:none;
            border-radius:8px; margin-bottom:6px; font-size:14px; transition:0.2s;
        }
        nav a:hover, nav a.active { background:rgba(255,255,255,0.06); color:#fff; }
        nav a.active { font-weight:600; }
        .logout { color:#fce7e9; margin-top:50px; font-size:14px; }

        .theme-toggle{display:block;margin:18px auto;padding:10px;border-radius:8px;border:none;background:rgba(255,255,255,0.08);color:var(--text);cursor:pointer;font-weight:600;}

        .main { flex:1; padding:32px; }
        .header {
            background:var(--card); padding:24px; border-radius:16px; margin-bottom:28px; border:1px solid var(--border);
        }
        .progress-container { display:flex; justify-content:space-between; align-items:center; margin-top:14px; font-size:14px; }
        .progress-bar { height:8px; background:#334155; border-radius:4px; overflow:hidden; flex:1; margin:0 16px; }
        .progress { height:100%; background:var(--progress); width:88%; border-radius:4px; }
        .badges { display:flex; gap:12px; margin-top:16px; flex-wrap:wrap; }
        .badge { background:rgba(16,185,129,0.15); color:var(--green); padding:6px 12px; border-radius:20px; font-size:13px; }

        .content {
            background:var(--card); border-radius:16px; padding:32px; border:1px solid var(--border);
        }
        .content h2 { font-size:26px; margin-bottom:28px; color:var(--text); }
        .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(270px,1fr)); gap:22px; }
        .stat-card {
            background:rgba(30,41,59,0.7); padding:22px; border-radius:12px; border:1px solid var(--border);
        }
        .stat-label { font-size:13px; color:var(--muted); margin-bottom:8px; }
        .stat-value { font-size:32px; font-weight:700; color:var(--accent); }
        .streak-value { font-size:48px; color:#fbbf24; text-shadow:0 0 20px rgba(251,191,36,0.4); }

        @media (max-width:992px) {
            body { flex-direction:column; }
            .sidebar { width:100%; padding:24px; border-right:none; border-bottom:1px solid var(--border); }
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="profile-img">PR</div>
    <div class="username"><?= htmlspecialchars($user['username']) ?></div>
    <div class="email"><?= htmlspecialchars($user['email']) ?></div>

    <button id="themeToggle" class="theme-toggle" aria-pressed="false">Toggle theme</button>
    
    <nav>
    <!-- THIS IS THE ONE THAT WILL ALWAYS BE VISIBLE AND ACTIVE ON DASHBOARD -->
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

<!-- MAIN CONTENT -->
<main class="main">
    <div class="header">
        <h1>Complétez votre profil</h1>
        <div class="progress-container">
            <span>88% complété</span>
            <div class="progress-bar"><div class="progress"></div></div>
            <span>88%</span>
        </div>
        <div class="badges">
            <div class="badge">Email vérifié</div>
            <div class="badge">Joueur actif</div>
            <div class="badge">+ Ajouter un avatar</div>
        </div>
    </div>

    <div class="content">
        <h2>Mes Statistiques PerfRan</h2>
        <div class="grid">

            <div class="stat-card">
                <div class="stat-label">Streak actuel</div>
                <div class="stat-value streak-value"><?= $user['streak'] ?></div>
                <small style="color:#fbbf24;">jours consécutifs !</small>
            </div>

            <div class="stat-card">
                <div class="stat-label">Victoires totales</div>
                <div class="stat-value"><?= number_format($user['wins']) ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Défaites</div>
                <div class="stat-value"><?= number_format($user['losses']) ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Taux de victoire</div>
                <div class="stat-value"><?= $winRate ?>%</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Score total</div>
                <div class="stat-value"><?= number_format($user['totalScore1'] + $user['totalScore2'] + $user['totalScore3']) ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Parties jouées</div>
                <div class="stat-value"><?= $totalGames ?></div>
            </div>

        </div>
    </div>
</main>

<script>
(function(){
    const saved = localStorage.getItem('pref-theme') || 'dark';
    const html = document.documentElement;
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
</script>

</body>
</html>