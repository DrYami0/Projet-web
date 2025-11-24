<?php
session_start();
require_once __DIR__ . '/../../controller/config.php';
if (!isset($_SESSION['uid'])) {
    header('Location: ' . BASE_URL . 'view/FrontOffice/login.php');
    exit;
}

// Exemple de données (à remplacer par ton vrai controller plus tard)
$paymentMethods = $paymentMethods ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abonnement & Don - PerfRan</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-start: #026875;
            --sidebar-end: #074149;
            --accent:#8b5cf6;
            --green:#10b981;
            --muted:#94a3b8;
            --border:#334155;
            --bg:#0f172a;
            --card:#1e293b;
            --text:#e2e8f0;
            --sidebar-text:#e6f7f6;
        }
        [data-theme="light"] {
            --bg:#f8fafc;
            --text:#0f172a;
            --card:#ffffff;
            --muted:#6b7280;
            --border:#e2e8f0;
            --sidebar-text:#f0fdfa;
        }

        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh;}
        .sidebar{width:280px;padding:30px 24px;border-right:1px solid var(--border);background:linear-gradient(180deg,var(--sidebar-start),var(--sidebar-end));}
        .profile-img{width:72px;height:72px;border-radius:50%;background:#ec4899;margin:0 auto 16px;display:flex;align-items:center;justify-content:center;font-size:28px;color:white;font-weight:bold;}
        .username{font-size:19px;font-weight:700;text-align:center;margin-bottom:4px;color:var(--sidebar-text);}
        .email{font-size:13px;color:rgba(255,255,255,0.85);text-align:center;margin-bottom:16px;}
        nav a{display:flex;align-items:center;gap:12px;padding:11px 16px;color:var(--sidebar-text);border-radius:8px;margin-bottom:6px;font-size:14px;text-decoration:none;}
        nav a:hover,nav a.active{background:rgba(255,255,255,0.06);color:#fff;}
        nav a.active{font-weight:600;}
        .logout{color:#fce7e9;margin-top:50px;}
        .theme-toggle{display:block;margin:18px auto;padding:10px;border-radius:8px;border:none;background:rgba(255,255,255,0.08);color:var(--sidebar-text);cursor:pointer;font-weight:600;}
        .main{flex:1;padding:40px;}
        .card{background:var(--card);border-radius:16px;padding:32px;border:1px solid var(--border);max-width:800px;margin:0 auto;}
        
        /* Card Image Header */
        .card-image-header {
            text-align: center;
            margin-bottom: 32px;
            padding: 40px;
            background: linear-gradient(135deg, var(--accent), #667eea);
            border-radius: 12px;
            position: relative;
            overflow: hidden;
        }
        .card-image-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 400%;
            height: 00%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        }
        .card-image-header img {
            max-width: 200px;
            height: auto;
            filter: drop-shadow(0 10px 20px rgba(0,0,0,0.3));
            position: relative;
            z-index: 1;
        }
        .card-image-header h2 {
            color: white;
            margin-top: 20px;
            font-size: 28px;
            position: relative;
            z-index: 1;
        }
        
        h2{font-size:28px;margin-bottom:24px;color:var(--text);}
        h3{font-size:20px;margin-top:32px;margin-bottom:16px;color:var(--text);}
        
        .method{
            background:rgba(139,92,246,0.1);
            padding:20px;
            border-radius:12px;
            margin-bottom:16px;
            border:1px solid var(--accent);
            color:var(--text);
            display:flex;
            align-items:center;
            justify-content:space-between;
        }
        [data-theme="light"] .method {
            background:rgba(139,92,246,0.05);
        }
        .method-info {
            flex: 1;
        }
        .method-icon {
            font-size: 32px;
            margin-right: 16px;
        }
        
        label{display:block;margin:16px 0 6px;color:var(--muted);font-size:14px;font-weight:500;}
        input,select{
            padding:12px;
            border-radius:8px;
            border:1px solid var(--border);
            background:#334155;
            color:white;
            width:100%;
            margin-bottom:16px;
            font-size:14px;
        }
        [data-theme="light"] input, [data-theme="light"] select { 
            background:#fff; 
            color:var(--text); 
            border:1px solid #d1d5db; 
        }
        button{
            background:var(--accent);
            color:white;
            padding:14px 28px;
            border:none;
            border-radius:8px;
            font-weight:600;
            cursor:pointer;
            font-size:16px;
            width:100%;
            margin-top:8px;
        }
        button:hover{background:#7c3aed;}
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--muted);
            background: rgba(139,92,246,0.05);
            border-radius: 12px;
            border: 2px dashed var(--border);
        }
        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="profile-img">PR</div>
    <div class="username"><?= htmlspecialchars($_SESSION['uid']) ?></div>
    <div class="email"><?= htmlspecialchars($_SESSION['email'] ?? '') ?></div>

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
    <div class="card">
        <!-- Card Image Header -->
        <div class="card-image-header">
            <!-- Replace this src with your actual credit card PNG path -->
            <img src="<?= BASE_URL ?>view/FrontOffice/assets/images/credit-card.png" alt="Carte bancaire" 
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
            <div style="display:none; font-size:80px;">💳</div>
            <h2>Gestion des Paiements</h2>
        </div>

        <h3>Moyens de paiement enregistrés</h3>

        <?php if (empty($paymentMethods)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">💳</div>
                <p>Aucun moyen de paiement enregistré pour le moment.</p>
            </div>
        <?php else: ?>
            <?php foreach ($paymentMethods as $p): ?>
                <div class="method">
                    <div class="method-icon">
                        <?php if(($p['type'] ?? 'card') === 'paypal'): ?>
                            💰
                        <?php else: ?>
                            💳
                        <?php endif; ?>
                    </div>
                    <div class="method-info">
                        <strong><?= htmlspecialchars($p['type'] ?? 'Carte') ?></strong> •••• <?= htmlspecialchars($p['last4'] ?? '0000') ?>
                        <br>
                        <small style="color:var(--muted);">
                            Expire <?= htmlspecialchars($p['expiry_month'] ?? '') ?>/<?= htmlspecialchars($p['expiry_year'] ?? '') ?>
                        </small>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <h3>Ajouter un nouveau moyen de paiement</h3>
        <p style="color:var(--muted);margin-bottom:24px;font-size:14px;">
            Pour soutenir le projet ou prendre un abonnement Premium
        </p>
        
        <form method="post" action="<?= BASE_URL ?>controller/add-payment.php">
            <label>Type de paiement</label>
            <select name="type" required>
                <option value="card">💳 Carte bancaire</option>
                <option value="paypal">💰 PayPal</option>
            </select>
            
            <label>Derniers 4 chiffres</label>
            <input type="text" name="last4" placeholder="4242" maxlength="4" pattern="[0-9]{4}" required>
            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div>
                    <label>Mois d'expiration</label>
                    <input type="text" name="expiry_month" placeholder="MM" maxlength="2" pattern="[0-9]{2}" required>
                </div>
                <div>
                    <label>Année d'expiration</label>
                    <input type="text" name="expiry_year" placeholder="YYYY" maxlength="4" pattern="[0-9]{4}" required>
                </div>
            </div>
            
            <button type="submit">Enregistrer le moyen de paiement</button>
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