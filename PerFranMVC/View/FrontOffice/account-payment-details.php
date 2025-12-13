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

// Example payment methods (replace with real data)
$paymentMethods = $paymentMethods ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abonnement & Don - PerfRan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/assets/css/user-dashboard.css">
    <style>
        /* Credit Card Styles */
        .credit-card-container {
            perspective: 1000px;
            margin-bottom: 40px;
        }
        
        .credit-card {
            width: 100%;
            max-width: 420px;
            height: 260px;
            margin: 0 auto;
            border-radius: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f64f59 100%);
            padding: 30px;
            position: relative;
            box-shadow: 0 25px 60px rgba(102, 126, 234, 0.4);
            transform-style: preserve-3d;
            transition: transform 0.6s;
            overflow: hidden;
        }
        
        .credit-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.3;
        }
        
        .credit-card:hover {
            transform: rotateY(-5deg) rotateX(5deg);
        }
        
        .card-chip {
            width: 55px;
            height: 45px;
            background: linear-gradient(135deg, #ffd700, #b8860b);
            border-radius: 8px;
            margin-bottom: 30px;
            position: relative;
        }
        
        .card-chip::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 5px;
            right: 5px;
            height: 1px;
            background: rgba(0,0,0,0.2);
        }
        
        .card-chip::after {
            content: '';
            position: absolute;
            left: 50%;
            top: 5px;
            bottom: 5px;
            width: 1px;
            background: rgba(0,0,0,0.2);
        }
        
        .card-number {
            font-size: 26px;
            font-weight: 600;
            letter-spacing: 4px;
            color: white;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
            margin-bottom: 30px;
            font-family: 'Courier New', monospace;
        }
        
        .card-details {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        
        .card-holder {
            color: rgba(255,255,255,0.8);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        
        .card-holder-name {
            color: white;
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .card-expiry {
            text-align: right;
        }
        
        .card-expiry-label {
            color: rgba(255,255,255,0.8);
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        
        .card-expiry-date {
            color: white;
            font-size: 18px;
            font-weight: 600;
        }
        
        .card-brand {
            position: absolute;
            top: 25px;
            right: 25px;
            font-size: 48px;
            opacity: 0.9;
        }
        
        .card-contactless {
            position: absolute;
            top: 80px;
            right: 30px;
            width: 35px;
            height: 35px;
            opacity: 0.7;
        }
        
        /* Payment Method Cards */
        .payment-method {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 20px;
            transition: all 0.3s ease;
        }
        
        .payment-method:hover {
            border-color: var(--accent);
            transform: translateX(5px);
        }
        
        .payment-method-icon {
            width: 60px;
            height: 40px;
            background: linear-gradient(135deg, var(--accent), #7c3aed);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
        
        .payment-method-info {
            flex: 1;
        }
        
        .payment-method-info strong {
            display: block;
            font-size: 16px;
            margin-bottom: 4px;
        }
        
        .payment-method-info small {
            color: var(--muted);
            font-size: 13px;
        }
        
        .payment-method-actions {
            display: flex;
            gap: 10px;
        }
        
        /* Donation Tiers */
        .donation-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .donation-tier {
            background: var(--card);
            border: 2px solid var(--border);
            border-radius: 16px;
            padding: 30px 24px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .donation-tier:hover {
            border-color: var(--accent);
            transform: translateY(-5px);
            box-shadow: 0 15px 40px var(--shadow);
        }
        
        .donation-tier.popular {
            border-color: var(--accent);
            position: relative;
        }
        
        .donation-tier.popular::before {
            content: 'Populaire';
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--accent);
            color: white;
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .donation-tier-icon {
            font-size: 40px;
            margin-bottom: 16px;
        }
        
        .donation-tier-amount {
            font-size: 36px;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 8px;
        }
        
        .donation-tier-label {
            color: var(--muted);
            font-size: 14px;
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
        <a href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/account-payment-details.php" class="active">
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
    <div class="container" style="max-width: 900px;">
        
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title"><i class="fas fa-credit-card" style="color: var(--accent);"></i> Paiement & Dons</h1>
                <div class="breadcrumb">
                    <a href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/dashboard.php">Tableau de bord</a>
                    <i class="fas fa-chevron-right"></i>
                    <span>Abonnement / Don</span>
                </div>
            </div>
        </div>

        <!-- Credit Card Display -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-wallet"></i> Votre carte</h3>
            </div>
            
            <div class="credit-card-container">
                <div class="credit-card">
                    <div class="card-brand">
                        <i class="fab fa-cc-visa"></i>
                    </div>
                    <svg class="card-contactless" viewBox="0 0 24 24" fill="white">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"/>
                        <path d="M12 6c-3.31 0-6 2.69-6 6s2.69 6 6 6 6-2.69 6-6-2.69-6-6-6zm0 10c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z"/>
                        <circle cx="12" cy="12" r="2"/>
                    </svg>
                    <div class="card-chip"></div>
                    <div class="card-number">•••• •••• •••• 4242</div>
                    <div class="card-details">
                        <div>
                            <div class="card-holder">Titulaire</div>
                            <div class="card-holder-name"><?= strtoupper(htmlspecialchars($user['username'])) ?></div>
                        </div>
                        <div class="card-expiry">
                            <div class="card-expiry-label">Expire</div>
                            <div class="card-expiry-date">12/28</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Saved Payment Methods -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-wallet"></i> Moyens de paiement enregistrés</h3>
            </div>

            <?php if (empty($paymentMethods)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">💳</div>
                    <h3>Aucun moyen de paiement</h3>
                    <p>Vous n'avez pas encore enregistré de moyen de paiement.</p>
                </div>
            <?php else: ?>
                <?php foreach ($paymentMethods as $p): ?>
                    <div class="payment-method">
                        <div class="payment-method-icon">
                            <?php if(($p['type'] ?? 'card') === 'paypal'): ?>
                                <i class="fab fa-paypal"></i>
                            <?php else: ?>
                                <i class="fas fa-credit-card"></i>
                            <?php endif; ?>
                        </div>
                        <div class="payment-method-info">
                            <strong><?= htmlspecialchars($p['type'] ?? 'Carte') ?> •••• <?= htmlspecialchars($p['last4'] ?? '0000') ?></strong>
                            <small>Expire <?= htmlspecialchars($p['expiry_month'] ?? '') ?>/<?= htmlspecialchars($p['expiry_year'] ?? '') ?></small>
                        </div>
                        <div class="payment-method-actions">
                            <button class="btn btn-secondary btn-sm"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Donation Section -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-heart"></i> Soutenir le projet</h3>
            </div>
            
            <p style="color: var(--muted); margin-bottom: 24px;">
                Votre soutien nous aide à améliorer PerfRan et à ajouter de nouvelles fonctionnalités !
            </p>
            
            <div class="donation-grid">
                <div class="donation-tier" onclick="selectDonation(5)">
                    <div class="donation-tier-icon">☕</div>
                    <div class="donation-tier-amount">5 TND</div>
                    <div class="donation-tier-label">Un café</div>
                </div>
                
                <div class="donation-tier popular" onclick="selectDonation(10)">
                    <div class="donation-tier-icon">🍕</div>
                    <div class="donation-tier-amount">10 TND</div>
                    <div class="donation-tier-label">Un repas</div>
                </div>
                
                <div class="donation-tier" onclick="selectDonation(25)">
                    <div class="donation-tier-icon">🎁</div>
                    <div class="donation-tier-amount">25 TND</div>
                    <div class="donation-tier-label">Un cadeau</div>
                </div>
                
                <div class="donation-tier" onclick="selectDonation(50)">
                    <div class="donation-tier-icon">💎</div>
                    <div class="donation-tier-amount">50 TND</div>
                    <div class="donation-tier-label">Généreux</div>
                </div>
            </div>
        </div>

        <!-- Add New Payment Method -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus-circle"></i> Ajouter un moyen de paiement</h3>
            </div>
            
            <form method="post" action="<?= BASE_URL ?>PerFranMVC/Controller/add-payment.php">
                <div class="form-grid">
                    <div class="form-group">
                        <label><i class="fas fa-wallet"></i> Type de paiement</label>
                        <select name="type" required>
                            <option value="card">💳 Carte bancaire</option>
                            <option value="paypal">💰 PayPal</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-hashtag"></i> 4 derniers chiffres</label>
                        <input type="text" name="last4" placeholder="4242" maxlength="4" pattern="[0-9]{4}" required>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> Mois d'expiration</label>
                        <input type="text" name="expiry_month" placeholder="MM" maxlength="2" pattern="[0-9]{2}" required>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-calendar-alt"></i> Année d'expiration</label>
                        <input type="text" name="expiry_year" placeholder="YYYY" maxlength="4" pattern="[0-9]{4}" required>
                    </div>
                </div>
                
                <div style="margin-top: 24px;">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-plus"></i> Enregistrer le moyen de paiement
                    </button>
                </div>
            </form>
        </div>

    </div>
</main>

<script>
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

// Donation selection
function selectDonation(amount) {
    document.querySelectorAll('.donation-tier').forEach(tier => {
        tier.style.borderColor = '';
        tier.style.background = '';
    });
    
    event.currentTarget.style.borderColor = 'var(--accent)';
    event.currentTarget.style.background = 'rgba(139, 92, 246, 0.1)';
    
    alert('Don de ' + amount + ' TND sélectionné ! (Simulation)');
}
</script>

</body>
</html>
