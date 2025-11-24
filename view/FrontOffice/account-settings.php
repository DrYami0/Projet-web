<?php
session_start();
require_once __DIR__ . '/../../controller/config.php';
if (!isset($_SESSION['uid'])) {
    header('Location: ' . BASE_URL . 'view/FrontOffice/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres - PerfRan</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { 
            --sidebar-start: #026875; --sidebar-end: #074149;
            --accent:#8b5cf6; --green:#10b981; --text:#e2e8f0; --muted:#94a3b8; --border:#334155; 
            --card:#1e293b;
        }
        [data-theme="light"]{
            --text:#0f172a; --muted:#6b7280; --border:#e2e8f0; --card:#ffffff;
        }
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Inter',sans-serif;background:var(--card);color:var(--text);display:flex;min-height:100vh;}
        .sidebar{width:280px;padding:30px 24px;border-right:1px solid var(--border);background:linear-gradient(180deg,var(--sidebar-start),var(--sidebar-end));}
        .profile-img{width:72px;height:72px;border-radius:50%;background:#ec4899;margin:0 auto 16px;display:flex;align-items:center;justify-content:center;font-size:28px;color:white;font-weight:bold;}
        .username{font-size:19px;font-weight:700;text-align:center;margin-bottom:4px;color:var(--text);}
        .email{font-size:13px;color:rgba(255,255,255,0.9);text-align:center;margin-bottom:16px;}
        nav a{display:flex;align-items:center;gap:12px;padding:11px 16px;color:var(--text);text-decoration:none;border-radius:8px;margin-bottom:6px;font-size:14px;transition:.2s;}
        nav a:hover,nav a.active{background:rgba(255,255,255,0.06);color:#fff;}
        nav a.active{font-weight:600;}
        .logout{color:#fce7e9;margin-top:50px;}
        .theme-toggle{display:block;margin:18px auto;padding:10px;border-radius:8px;border:none;background:rgba(255,255,255,0.08);color:var(--text);cursor:pointer;font-weight:600;}
        .main{flex:1;padding:40px;}
        .card{background:var(--card);border-radius:16px;padding:32px;border:1px solid var(--border);max-width:680px;margin:0 auto;margin-bottom:24px;}
        h2{font-size:28px;margin-bottom:24px;color:var(--text);}
        h3{font-size:20px;margin-bottom:16px;color:var(--text);}
        label{display:block;margin-bottom:8px;color:var(--muted);font-size:14px;}
        select,input{padding:12px;border-radius:8px;border:1px solid var(--border);background:#334155;color:white;width:100%;margin-bottom:20px;}
        [data-theme="light"] select,input{background:#fff;color:var(--text);}
        button{background:var(--accent);color:white;padding:12px 24px;border:none;border-radius:8px;font-weight:600;cursor:pointer;margin-top:16px;margin-bottom:24px;}
        button:hover{background:#7c3aed;}
        p{margin-bottom:16px;}
    </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="profile-img">PR</div>
    <div class="username"><?= htmlspecialchars($_SESSION['uid']) ?></div>
    <div class="email"><?= htmlspecialchars($_SESSION['email'] ?? 'email@exemple.com') ?></div>

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
    <!-- Carte Notifications -->
    <div class="card">
        <h2>Paramètres de notification</h2>
        <form method="post" action="<?= BASE_URL ?>controller/update-settings.php">
            <div>
                <label for="newsletter">Newsletter *</label>
                <select id="newsletter" name="newsletter">
                    <option value="daily">Quotidienne</option>
                    <option value="twice-weekly">Deux fois par semaine</option>
                    <option value="weekly">Hebdomadaire</option>
                    <option value="never">Jamais</option>
                </select>
            </div>
            <label><input type="checkbox" name="notify_login"> Me notifier par email lors de la connexion</label>
            <label><input type="checkbox" name="booking_reminders"> Recevoir des rappels d’assistance de réservation</label>
            <label><input type="checkbox" name="promotions"> Recevoir des emails sur les promotions</label>
            <label><input type="checkbox" name="trip_info"> Être informé des offres liées à mon prochain voyage</label>
            <label><input type="checkbox" name="public_profile"> Afficher mon profil publiquement</label>
            <label><input type="checkbox" name="sms_payments"> Envoyer une confirmation SMS pour tous les paiements en ligne</label>
            <label><input type="checkbox" name="device_check"> Vérifier quels appareils accèdent à mon compte</label>
            <button type="submit">Enregistrer les modifications</button>
        </form>
    </div>

    <!-- Carte Sécurité -->
    <div class="card">
        <h2>Sécurité</h2>
        <form method="post" action="<?= BASE_URL ?>controller/update-settings.php">
            <h3>Authentification à deux facteurs</h3>
            <label for="phone">Ajouter un numéro de téléphone</label>
            <input type="tel" id="phone" name="phone" placeholder="Entrez votre numéro de mobile">
            <button type="button">Envoyer le code</button>

            <h3>Sessions actives</h3>
            <p>Sélectionner « Déconnexion » vous déconnectera de tous les appareils sauf celui-ci. Cela peut prendre jusqu’à 10 minutes.</p>
            <button type="button">Déconnexion des autres appareils</button>
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
