<?php
ob_start('ob_gzhandler');
session_start();
require_once __DIR__ . '/config.php';

// Set cache headers for better performance
header('Cache-Control: public, max-age=3600');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
header('Vary: Accept-Encoding');

// Check if user is logged in and redirect accordingly
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PerFran - Apprenez le français en jouant</title>
    <!-- Preconnect to external CDNs for faster loading -->
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <!-- Critical CSS - inline for instant rendering -->
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        body { background: linear-gradient(135deg, #0a1628 0%, #1a2f4a 100%); color: #fff; min-height: 100vh; overflow-x: hidden; }
        nav { display: flex; justify-content: space-between; align-items: center; padding: 20px 80px; position: fixed; top: 0; width: 100%; background: rgba(10, 22, 40, 0.95); backdrop-filter: blur(10px); z-index: 1000; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
        .logo { display: flex; align-items: center; gap: 10px; cursor: pointer; }
        .logo img { height: 50px; width: auto; }
        .logo-text { font-size: 28px; font-weight: 700; color: #00d4ff; }
        .nav-links { display: flex; gap: 40px; align-items: center; }
        .nav-links a { text-decoration: none; color: #fff; font-size: 16px; transition: color 0.3s; position: relative; }
        .nav-links a:hover { color: #00d4ff; }
        .nav-buttons { display: flex; gap: 15px; }
        .btn { padding: 12px 28px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; border: none; font-size: 15px; }
        .btn-outline { background: transparent; border: 2px solid #00d4ff; color: #00d4ff; }
        .btn-outline:hover { background: #00d4ff; color: #0a1628; }
        .btn-primary { background: linear-gradient(135deg, #00d4ff 0%, #0099cc 100%); color: #fff; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(0, 212, 255, 0.4); }
        .hero { padding: 150px 80px 80px; display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; min-height: 100vh; }
        .hero-content h1 { font-size: 56px; font-weight: 800; line-height: 1.2; margin-bottom: 20px; }
        .hero-content h1 .highlight { color: #00d4ff; }
        .hero-content p { font-size: 18px; color: #b0c4de; margin-bottom: 40px; line-height: 1.6; }
        .hero-buttons { display: flex; gap: 20px; }
        .hero-image { display: flex; justify-content: center; align-items: center; }
        .hero-circle { width: 450px; height: 450px; background: radial-gradient(circle, rgba(0, 212, 255, 0.2) 0%, transparent 70%); border-radius: 50%; display: flex; align-items: center; justify-content: center; position: relative; }
        .hero-circle::before { content: ''; position: absolute; width: 100%; height: 100%; border: 3px solid rgba(0, 212, 255, 0.3); border-radius: 50%; animation: pulse 3s ease-in-out infinite; }
        @keyframes pulse { 0%, 100% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.1); opacity: 0.7; } }
        .hero-circle i { font-size: 200px; color: rgba(255, 255, 255, 0.9); }
        .games-section { padding: 80px; background: rgba(255, 255, 255, 0.03); }
        .section-title { text-align: center; font-size: 42px; font-weight: 700; margin-bottom: 60px; }
        .games-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 40px; max-width: 1400px; margin: 0 auto; }
        .game-card { background: rgba(255, 255, 255, 0.05); border: 2px solid rgba(0, 212, 255, 0.3); border-radius: 16px; padding: 40px; transition: all 0.3s; cursor: pointer; }
        .game-card:hover { transform: translateY(-10px); border-color: #00d4ff; box-shadow: 0 20px 40px rgba(0, 212, 255, 0.2); }
        .game-icon { width: 80px; height: 80px; background: rgba(0, 212, 255, 0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 25px; }
        .game-icon i { font-size: 36px; color: #00d4ff; }
        .game-card h3 { font-size: 24px; margin-bottom: 15px; }
        .game-card p { color: #b0c4de; line-height: 1.6; margin-bottom: 20px; }
        .game-tags { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; }
        .tag { background: rgba(0, 212, 255, 0.2); color: #00d4ff; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 500; }
        .game-button { width: 100%; padding: 12px; background: transparent; border: 2px solid #00d4ff; color: #00d4ff; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
        .game-button:hover { background: #00d4ff; color: #0a1628; }
        .game-button.primary { background: #00d4ff; color: #0a1628; }
        .game-button.primary:hover { background: #00b8e6; }
        .features-section { padding: 80px; }
        .features-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 40px; max-width: 1400px; margin: 0 auto; }
        .feature-card { text-align: center; padding: 30px; }
        .feature-icon { font-size: 60px; color: #00d4ff; margin-bottom: 20px; }
        .feature-card h3 { font-size: 20px; margin-bottom: 15px; }
        .feature-card p { color: #b0c4de; line-height: 1.6; }
        footer { text-align: center; padding: 40px; background: rgba(0, 0, 0, 0.3); border-top: 1px solid rgba(255, 255, 255, 0.1); }
        footer p { color: #b0c4de; }
        footer a { color: #00d4ff; text-decoration: none; }
        footer a:hover { text-decoration: underline; }
        @media (max-width: 1024px) {
            .hero, .games-section, .features-section { padding: 60px 40px; }
            nav { padding: 20px 40px; }
            .games-grid { grid-template-columns: repeat(2, 1fr); }
            .features-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            .hero { grid-template-columns: 1fr; padding: 120px 20px 60px; }
            .hero-content h1 { font-size: 36px; }
            .games-grid, .features-grid { grid-template-columns: 1fr; }
            nav { padding: 15px 20px; }
            .nav-links { display: none; }
        }
    </style>
    <!-- Deferred non-critical CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" media="print" onload="this.media='all'" defer>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" defer>
    <link rel="stylesheet" href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/assets/css/mascot.css">
    <style>
        /* Mascot footer UI overrides: smaller model selector and send button, ensure menu is interactive */
        .pf-owl-model-display{padding:8px 10px !important;font-size:13px !important;min-height:36px !important}
        .pf-owl-model-wrap{min-width:120px !important;margin-right:12px !important}
        .pf-owl-model-menu{min-width:140px !important}
        .pf-owl-send{min-width:36px !important;height:34px !important;padding:6px 8px !important;font-size:13px !important}
        .pf-owl-send svg{width:14px !important;height:14px !important}
        .pf-owl-model-menu.pf-owl-model-open{display:block !important;opacity:1 !important;transform:translateY(0) !important;pointer-events:auto !important;visibility:visible !important;transition:opacity .18s ease,transform .18s ease !important;z-index:2147483650 !important}
    </style>
    <style>
        /* Hide/neutralize mascot chat scrollbars across browsers */
        .pf-owl-board, .pf-owl-chat, .pf-owl-model-menu { scrollbar-width: none; -ms-overflow-style: none; }
        .pf-owl-board::-webkit-scrollbar, .pf-owl-chat::-webkit-scrollbar, .pf-owl-model-menu::-webkit-scrollbar { width: 0; height: 0; }
        .pf-owl-board::-webkit-scrollbar-track, .pf-owl-chat::-webkit-scrollbar-track, .pf-owl-model-menu::-webkit-scrollbar-track { background: transparent; }
        .pf-owl-board::-webkit-scrollbar-thumb, .pf-owl-chat::-webkit-scrollbar-thumb, .pf-owl-model-menu::-webkit-scrollbar-thumb { background: transparent; }
        /* also hide the input bubble scrollbar if any */
        .pf-owl-input::-webkit-scrollbar { width: 0; height: 0; }
        .pf-owl-input { scrollbar-width: none; -ms-overflow-style: none; }
        /* ensure the AI model menu items don't show scrollbars on overflow */
        .pf-owl-model-menu .pf-owl-model-item { scrollbar-width: none; -ms-overflow-style: none; }
        .pf-owl-model-menu .pf-owl-model-item::-webkit-scrollbar { width: 0; height: 0; }
    </style>
    <noscript>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    </noscript>
</head>
<body>
    <!-- Navigation -->
    <nav>
        <div class="logo" onclick="window.location.href='<?= BASE_URL ?>index.php'">
            <img src="<?= BASE_URL ?>PerFranMVC/View/Perfran.png" alt="PerFran Logo" style="height: 60px; width: auto;" loading="lazy">
        </div>
        <div class="nav-links">
            <a href="#games">Jeux</a>
            <a href="#features">Fonctionnalités</a>
        </div>
        <div class="nav-buttons">
            <button id="themeToggle" class="theme-toggle" style="width: auto; margin: 0; padding: 10px 14px; background: rgba(255,255,255,0.1); border-radius: 8px; border: 1px solid rgba(255,255,255,0.15); color: #e8f4f8; cursor: pointer; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s ease;">
                <i class="fas fa-sun"></i>
                <span>Thème clair</span>
            </button>
            <?php if ($isLoggedIn): ?>
                <a href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/dashboard.php" class="btn btn-outline">
                    <i class="fas fa-chart-line"></i> Tableau de bord
                </a>
                <?php if ($userRole == 1): ?>
                <a href="<?= BASE_URL ?>PerFranMVC/View/BackOffice/admin-dashboard.php" class="btn btn-primary">
                    <i class="fas fa-user-shield"></i> Admin
                </a>
                <?php endif; ?>
            <?php else: ?>
                <a href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/login.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Connexion
                </a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Apprenez le français <span class="highlight">en jouant</span></h1>
            <p>PerfRan est une plateforme d'apprentissage ludique qui combine jeux interactifs et exercices de grammaire pour maîtriser la langue française de manière amusante et efficace.</p>
            <div class="hero-buttons">
                <a href="#games" class="btn btn-primary">
                    <i class="fas fa-gamepad"></i> Découvrir les jeux
                </a>
                <?php if ($isLoggedIn): ?>
                <a href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/dashboard.php" class="btn btn-outline">
                    <i class="fas fa-chart-line"></i> Voir mes stats
                </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="hero-image">
            <div class="hero-circle">
                <i class="fas fa-graduation-cap"></i>
            </div>
        </div>
    </section>

    <!-- Games Section -->
    <section class="games-section" id="games">
        <h2 class="section-title">Nos Jeux Éducatifs</h2>
        <div class="games-grid">
            <div class="game-card">
                <div class="game-icon">
                    <i class="fas fa-spell-check"></i>
                </div>
                <h3>Jeu de Grammaire</h3>
                <p>Testez vos connaissances en grammaire française avec des questions interactives et des défis adaptés à votre niveau.</p>
                <div class="game-tags">
                    <span class="tag">Solo</span>
                    <span class="tag">Multijoueur</span>
                    <span class="tag">3 Niveaux</span>
                </div>
                <button class="game-button primary" onclick="window.location.href='<?= BASE_URL ?>PerFranMVC/View/FrontOffice/game1.html'">
                    <i class="fas fa-play"></i> Jouer maintenant
                </button>
            </div>

            <div class="game-card">
                <div class="game-icon">
                    <i class="fas fa-book"></i>
                </div>
                <h3>Quiz Vocabulaire</h3>
                <p>Enrichissez votre vocabulaire avec des quiz thématiques et des exercices de mémorisation.</p>
                <div class="game-tags">
                    <span class="tag">Solo</span>
                    <span class="tag">Thèmes variés</span>
                </div>
                <button class="game-button" onclick="alert('Bientôt disponible!')">
                    <i class="fas fa-clock"></i> Bientôt disponible
                </button>
            </div>

            <div class="game-card">
                <div class="game-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <h3>Conjugaison Express</h3>
                <p>Maîtrisez la conjugaison française avec des exercices chronométrés et des défis quotidiens.</p>
                <div class="game-tags">
                    <span class="tag">Chronomètre</span>
                    <span class="tag">Défis quotidiens</span>
                </div>
                <button class="game-button" onclick="alert('Bientôt disponible!')">
                    <i class="fas fa-clock"></i> Bientôt disponible
                </button>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <h2 class="section-title">Pourquoi PerfRan ?</h2>
        <p style="text-align: center; color: #b0c4de; margin-bottom: 60px; font-size: 18px;">Une expérience d'apprentissage unique et personnalisée</p>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <h3>Gamification</h3>
                <p>Gagnez des points, débloquez des succès et montez dans le classement</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Multijoueur</h3>
                <p>Défiez vos amis ou joueurs du monde entier en temps réel</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>Suivi des progrès</h3>
                <p>Visualisez votre progression avec des statistiques détaillées</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3>Responsive</h3>
                <p>Jouez sur ordinateur, tablette ou smartphone</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>© 2025 PerfRan - Tous droits réservés | <a href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/login.php">Connexion</a></p>
    </footer>

    <script>
        function scrollToGames() {
            document.getElementById('games').scrollIntoView({ behavior: 'smooth' });
        }
    </script>
    
    <script src="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/assets/js/theme-toggle.js"></script>
    
    <!-- Deferred JavaScript loading for better performance -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
    
    <!-- Deferred JavaScript loading for better performance -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
    <script src="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/assets/js/theme-toggle.js" defer></script>
    
    <!-- Interactive Owl Mascot (robust loading) -->
    <script>
        window.PERFRAN_AI_API_URL = '<?= PERFRAN_AI_API_URL ?>';
        (function(){
            function loadMascot(){
                if(window.__perfRanMascotLoaded) return;
                window.__perfRanMascotLoaded = true;
                var s = document.createElement('script');
                s.src = '<?= BASE_URL ?>PerFranMVC/View/FrontOffice/assets/js/mascot.js';
                s.async = false;
                document.body.appendChild(s);
            }
            // Try to load mascot as soon as DOM is interactive
            if(document.readyState === 'complete' || document.readyState === 'interactive'){
                setTimeout(loadMascot, 1000);
            } else {
                window.addEventListener('DOMContentLoaded', function(){ setTimeout(loadMascot, 1000); });
            }
            // Fallback: force-load mascot if not loaded after 3 seconds
            setTimeout(function(){
                if(!window.__perfRanMascotLoaded) loadMascot();
            }, 3000);
        })();
    </script>
    <!-- Reliable fallback: load mascot.js directly if dynamic inject failed -->
    <script>
        try{
            if(!window.__perfRanMascotLoaded){
                var s = document.createElement('script');
                s.src = '<?= BASE_URL ?>PerFranMVC/View/FrontOffice/assets/js/mascot.js';
                s.defer = true;
                document.body.appendChild(s);
            }
        }catch(e){ console.warn('mascot fallback load failed', e); }
    </script>
</body>
</html>
<?php
ob_end_flush();
?>
