<?php
session_start();
require_once __DIR__ . '/../../controller/config.php';
if (!isset($_SESSION['uid'])) {
    header('Location: ' . BASE_URL . 'projet-web/view/FrontOffice/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer le compte - PerfRan</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Theme variables */
        :root {
            --sidebar-start: #026875;
            --sidebar-end: #074149;
            --accent: #8b5cf6;
            --muted: #94a3b8;
            --border: #334155;

            /* Dark mode defaults */
            --bg: #0f172a;
            --card: #1e293b;
            --sidebar-text: #e6f7f6;
            --text: #e2e8f0;
            --button-danger: #dc2626;
            --button-danger-hover: #b91c1c;
        }
         body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('<?= BASE_URL ?>view/FrontOffice/assets/images/waiting2-bg.gif') center center;
            background-size: cover;
            z-index: -2;
        }
        /* Light theme overrides */
        [data-theme="light"] {
            --bg: #f7fafc;
            --card: #ffffff;
            --sidebar-text: #f0fdfa;
            --text: #0f172a;
            --muted: #6b7280;
            --border: #e2e8f0;
            --button-danger: #ef4444;
            --button-danger-hover: #dc2626;
        }

        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh;}
        .sidebar{width:280px;padding:30px 24px;border-right:1px solid var(--border);
            background: linear-gradient(180deg,var(--sidebar-start),var(--sidebar-end));}
        .profile-img{width:72px;height:72px;border-radius:50%;background:#ec4899;margin:0 auto 16px;display:flex;align-items:center;justify-content:center;font-size:28px;color:white;font-weight:bold;}
        .username,.email{text-align:center;color:var(--sidebar-text);}
        .username{font-size:19px;font-weight:700;margin-bottom:4px;}
        .email{font-size:13px;color:rgba(255,255,255,0.85);margin-bottom:16px;}
        nav a{display:flex;align-items:center;gap:12px;padding:11px 16px;color:var(--sidebar-text);border-radius:8px;margin-bottom:6px;font-size:14px;text-decoration:none;}
        nav a:hover,nav a.active{background:rgba(255,255,255,0.06);color:#fff;}
        nav a.active{font-weight:600;}
        .logout{color:#fce7e9;margin-top:26px;display:block}
        .theme-toggle{display:block;margin:18px auto 8px;padding:10px 12px;border-radius:8px;border:none;background:rgba(255,255,255,0.08);color:var(--sidebar-text);cursor:pointer;font-weight:600;}
        .main{flex:1;padding:40px;display:flex;align-items:center;justify-content:center;}
        .card{background:var(--card);border-radius:16px;padding:40px;max-width:520px;width:100%;text-align:center;border:1px solid var(--border);}
        h2{font-size:28px;margin-bottom:16px;}
        p{color:var(--muted);margin-bottom:32px;}
        button{background:var(--button-danger);color:white;padding:14px 32px;border:none;border-radius:8px;font-weight:600;cursor:pointer;font-size:16px;}
        button:hover{background:var(--button-danger-hover);}
    </style>
</head>
<body>
<aside class="sidebar">
    <div class="profile-img">PR</div>
    <div class="username"><?= htmlspecialchars($_SESSION['uid']) ?></div>
    <div class="email"><?= htmlspecialchars($_SESSION['email'] ?? '') ?></div>

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

<main class="main">
    <div class="card">
        <h2>Supprimer définitivement mon compte</h2>
        <p>Cette action est irréversible. Toutes vos données, scores et progression seront perdus.</p>
        <form method="post" action="<?= BASE_URL ?>controller/delete-account.php" onsubmit="return confirm('Êtes-vous absolument sûr ? Cette action est irréversible.');">
            <button type="submit">Oui, supprimer mon compte</button>
        </form>
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