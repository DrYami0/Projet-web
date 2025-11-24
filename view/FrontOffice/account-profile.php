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

$totalGames = $user['gamesPlayed1'] + $user['gamesPlayed2'] + $user['gamesPlayed3'];
$winRate = ($user['wins'] + $user['losses']) > 0 
    ? round(($user['wins'] / ($user['wins'] + $user['losses'])) * 100, 1) 
    : 0;

$fullNameDisplay = trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? ''));
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - PerfRan</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
    <style>
        :root {
            --sidebar-start: #026875;
            --sidebar-end: #074149;
            --accent: #8b5cf6;
            --green: #10b981;
            --yellow: #fbbf24;
            --label: #cbd5e1;
            --border: #334155;

            /* dark */
            --bg: #0f172a;
            --card: #1e293b;
            --text: #e2e8f0;
            --muted: #94a3b8;
            --sidebar-text: #e6f7f6;
        }
        [data-theme="light"]{
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #6b7280;
            --label: #475569;
            --border: #e2e8f0;
            /* Sidebar text stays light even in light mode */
            --sidebar-text: #f0fdfa;
        }

        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Inter',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; display:flex; }
        
        .sidebar {
            width:280px; padding:30px 24px; border-right:1px solid var(--border); position:fixed; height:100vh; overflow-y:auto;
            background: linear-gradient(180deg,var(--sidebar-start),var(--sidebar-end));
        }
        .profile-img {
            width:72px; height:72px; border-radius:50%; background:#ec4899; margin:0 auto 16px;
            display:flex; align-items:center; justify-content:center; font-size:28px; color:white; font-weight:bold;
        }
        .username { 
            font-size:19px; 
            font-weight:700; 
            text-align:center; 
            margin-bottom:4px; 
            color:var(--sidebar-text); /* Always light */
        }
        .email { 
            font-size:13px; 
            color:rgba(255,255,255,0.85); /* Always light */
            text-align:center; 
            margin-bottom:16px; 
        }
        nav a {
            display:flex; 
            align-items:center; 
            gap:12px; 
            padding:11px 16px; 
            color:var(--sidebar-text); /* Always light */
            text-decoration:none;
            border-radius:8px; 
            margin-bottom:6px; 
            font-size:14px; 
            transition:0.2s;
        }
        nav a:hover, nav a.active { 
            background:rgba(255,255,255,0.06);
            color:#fff; /* Stays white on hover */
        }
        nav a.active { font-weight:600; }
        .logout { 
            color:#fce7e9; /* Always light pink */
            margin-top:50px; 
        }

        .theme-toggle{
            display:block;
            margin:18px auto;
            padding:10px;
            border-radius:8px;
            border:none;
            background:rgba(255,255,255,0.08);
            color:var(--sidebar-text); /* Always light */
            cursor:pointer;
            font-weight:600;
        }

        .main { flex:1; margin-left:280px; padding:40px; }
        .container { max-width:1200px; margin:0 auto; }
        .card {
            background:var(--card); 
            border-radius:16px; 
            padding:28px; 
            border:1px solid var(--border); 
            margin-bottom:24px;
        }
        .progress-bar { height:8px; background:#334155; border-radius:4px; overflow:hidden; margin:16px 0; }
        .progress { height:100%; background:var(--green); width:88%; border-radius:4px; }
        .badges { display:flex; gap:12px; flex-wrap:wrap; margin-top:12px; }
        .badge { background:rgba(16,185,129,0.15); color:var(--green); padding:6px 12px; border-radius:20px; font-size:13px; }

        .stats-grid {
            display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:20px; margin:32px 0;
        }
        .stat-card {
            background:rgba(30,41,59,0.7); padding:24px; border-radius:12px; border:1px solid var(--border); text-align:center;
        }
        [data-theme="light"] .stat-card {
            background:rgba(248,250,252,0.9);
        }
        .stat-label { font-size:13px; color:var(--muted); margin-bottom:8px; }
        .stat-value { font-size:36px; font-weight:700; color:var(--accent); }
        .streak-value { font-size:48px; color:var(--yellow); text-shadow:0 0 20px rgba(251,191,36,0.4); }

        label {
            display:block; margin-bottom:8px; color:var(--label); font-size:14px; font-weight:500;
        }
        .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
        input, select, textarea {
            width:100%; padding:12px; border-radius:8px; border:1px solid var(--border);
            background:#334155; color:white;
        }
        [data-theme="light"] input, [data-theme="light"] textarea, [data-theme="light"] select {
            background:#fff; 
            color:var(--text);
            border:1px solid #d1d5db;
        }
        button {
            background:var(--accent); color:white; padding:12px 32px; border:none; border-radius:8px;
            font-weight:600; cursor:pointer;
        }
        button:hover { background:#7c3aed; }

        /* Success/Error messages */
        .message {
            padding:16px;
            border-radius:12px;
            margin:20px 0;
            text-align:center;
            font-weight:600;
        }
        .message-success {
            background:#10b981;
            color:white;
        }
        .message-error {
            background:#ef4444;
            color:white;
        }

        /* CHOICES.JS FULL DARK MODE */
        .choices__inner {
            background: #334155 !important; border: 1px solid #475569 !important;
            border-radius: 8px !important; min-height: 48px !important; padding: 8px 12px !important;
        }
        [data-theme="light"] .choices__inner {
            background: #fff !important;
            border: 1px solid #d1d5db !important;
        }

        @media (max-width:992px) {
            .main { margin-left:0; padding:20px; }
            .sidebar { position:relative; width:100%; height:auto; border-right:none; border-bottom:1px solid var(--border); }
            .form-grid { grid-template-columns:1fr; }
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
    <div class="container">

        <!-- Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="message message-success">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="message message-error">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="card">
            <h4>Complétez votre profil</h4>
            <div class="progress-bar"><div class="progress"></div></div>
            <div style="display:flex; justify-content:space-between; margin-top:8px; font-size:14px;">
                <span>88% complété</span><span>88%</span>
            </div>
            <div class="badges">
                <div class="badge">Email vérifié</div>
                <div class="badge">Joueur actif</div>
                <div class="badge">+ Ajouter un avatar</div>
            </div>
        </div>

        <div class="card">
            <h4>Mes Statistiques PerfRan</h4>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Streak actuel</div>
                    <div class="stat-value streak-value"><?= $user['streak'] ?></div>
                    <small style="color:var(--yellow);">jours consécutifs</small>
                </div>
                <div class="stat-card"><div class="stat-label">Victoires</div><div class="stat-value"><?= number_format($user['wins']) ?></div></div>
                <div class="stat-card"><div class="stat-label">Défaites</div><div class="stat-value"><?= number_format($user['losses']) ?></div></div>
                <div class="stat-card"><div class="stat-label">Taux de victoire</div><div class="stat-value"><?= $winRate ?>%</div></div>
                <div class="stat-card"><div class="stat-label">Score total</div><div class="stat-value"><?= number_format($user['totalScore1'] + $user['totalScore2'] + $user['totalScore3']) ?></div></div>
                <div class="stat-card"><div class="stat-label">Parties jouées</div><div class="stat-value"><?= $totalGames ?></div></div>
            </div>
        </div>

        <div class="card">
            <h4>Informations personnelles</h4>
            <form method="post" action="<?= BASE_URL ?>controller/update-profile.php" enctype="multipart/form-data" id="profileForm" novalidate>
                <div style="text-align:center; margin-bottom:32px;">
                    <img src="<?= htmlspecialchars($user['avatar'] ?? 'https://via.placeholder.com/120/ec4899/ffffff?text=PR') ?>" 
                         alt="Avatar" style="width:120px;height:120px;border-radius:50%;border:4px solid var(--accent);object-fit:cover;">
                    <div style="margin-top:16px;">
                        <label style="background:var(--accent);color:white;padding:8px 16px;border-radius:8px;cursor:pointer;">
                            Changer d'avatar <input type="file" name="avatar" hidden accept="image/*">
                        </label>
                    </div>
                </div>

                <div class="form-grid">
                    <div>
                        <label>Nom complet</label>
                        <input type="text" name="fullname" value="<?= htmlspecialchars($fullNameDisplay) ?>" required>
                    </div>
                    <div>
                        <label>Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div>
                        <label>Téléphone</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>

                    <div style="grid-column: span 2; margin-top: 32px; padding-top: 32px; border-top: 1px solid var(--border);">
                        <h5 style="margin-bottom:16px; color:var(--label);">Changer le mot de passe <small style="color:var(--muted)">(laisser vide pour ne pas modifier)</small></h5>
                        <div class="form-grid">
                            <div>
                                <label>Nouveau mot de passe</label>
                                <input type="password" name="new_password" placeholder="••••••••">
                            </div>
                            <div>
                                <label>Confirmer le mot de passe</label>
                                <input type="password" name="confirm_password" placeholder="••••••••">
                            </div>
                        </div>
                    </div>
                </div>

                <div style="text-align:right; margin-top:32px;">
                    <button type="submit">Enregistrer les modifications</button>
                </div>
            </form>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
    document.querySelectorAll('input').forEach(input => {
        input.setAttribute('autocomplete', 'off');
        input.setAttribute('autocorrect', 'off');
        input.setAttribute('autocapitalize', 'off');
        input.setAttribute('spellcheck', 'false');
    });

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