<?php
session_start();
// Modified to point to the correct config location in PerFran-master
require_once __DIR__ . '/../../../database/config.php';

// Define BASE_URL relative to this file (View/FrontOffice/index.php)
// We need to go up to PerFranMVC/
const BASE_URL = '../../';

// Mock user session for design integration purposes
// In a real integration, we would merge UserC.php and use actual session data
$isLoggedIn = isset($_SESSION['user_id']); // This might work if session is shared
$userRole = $_SESSION['role'] ?? 0;

// Temporary mock to prevent errors if UserC is missing
if (!class_exists('UserC')) {
    // $isLoggedIn = false; // Uncomment to force logged out state
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PerFran - Apprenez le français en jouant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- Updated path to use BASE_URL correctly -->
    <link rel="stylesheet" href="<?= BASE_URL ?>View/FrontOffice/assets/css/owl-mascot.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>View/FrontOffice/assets/css/theme.css">
</head>
<body>
    <!-- Navigation -->
    <nav>
        <div class="logo" onclick="window.location.href='<?= BASE_URL ?>View/FrontOffice/index.php'">
            <!-- Updated image path to use BASE_URL -->
            <img src="<?= BASE_URL ?>View/Perfran.png" alt="PerFran Logo" style="height: 60px; width: auto;">
        </div>
        <div class="nav-links">
            <a href="#games">Jeux</a>
            <a href="#features">Fonctionnalités</a>
        </div>
        <div class="nav-buttons">
            <?php if ($isLoggedIn): ?>
                <a href="<?= BASE_URL ?>View/FrontOffice/dashboard.php" class="btn btn-outline">
                    <i class="fas fa-chart-line"></i> Tableau de bord
                </a>
                <?php if ($userRole == 1): ?>
                <a href="<?= BASE_URL ?>View/BackOffice/admin-dashboard.php" class="btn btn-primary">
                    <i class="fas fa-user-shield"></i> Admin
                </a>
                <?php endif; ?>
            <?php else: ?>
                <!-- Links to login.php which we might need to copy later -->
                <a href="<?= BASE_URL ?>View/FrontOffice/login.php" class="btn btn-primary">
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
                <a href="<?= BASE_URL ?>View/FrontOffice/dashboard.php" class="btn btn-outline">
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
                <!-- Updated link to point to existing game1.html if copied, or jeu3.html if we want to link to old game -->
                <!-- For now, keeping as game1.html assuming we might copy it later, or user can update -->
                <button class="game-button primary" onclick="window.location.href='<?= BASE_URL ?>View/FrontOffice/game1.html'">
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
                <button class="game-button" onclick="window.location.href='<?= BASE_URL ?>View/FrontOffice/jeu3.html'">
                    <i class="fas fa-play"></i> Jouer maintenant
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
        <p>© 2025 PerfRan - Tous droits réservés | <a href="<?= BASE_URL ?>View/FrontOffice/login.php">Connexion</a></p>
    </footer>

    <script>
        function scrollToGames() {
            document.getElementById('games').scrollIntoView({ behavior: 'smooth' });
        }
    </script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Interactive Owl Mascot -->
    <script src="<?= BASE_URL ?>View/FrontOffice/assets/js/owl-mascot.js"></script>
</body>
</html>
