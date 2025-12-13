<?php
session_start();
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../Controller/UserC.php';

$username = $_SESSION['pending_username'] ?? null;
if (!$username) {
    header('Location: login.php');
    exit;
}

$userRepo = new UserC();
$data = $userRepo->findByUsername($username);
if (!$data) {
    unset($_SESSION['pending_username']);
    header('Location: login.php');
    exit;
}

$creationTs = isset($data['creationDate']) ? strtotime($data['creationDate']) : time();
$tokenHours = isset($data['token']) ? (int)$data['token'] : 48;
$remaining = $tokenHours * 3600 - (time() - $creationTs);

if ($remaining <= 0) {
    $userRepo->refuseByUsername($username);
    unset($_SESSION['pending_username']);
    header('Location: login.php?refused=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>En attente de validation - PerfRan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        /* Animated Background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('<?= BASE_URL ?>PerFranMVC/View/FrontOffice/assets/images/waiting-bg.gif') center center;
            background-size: cover;
            z-index: -2;
        }

        
        body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            z-index: -3;
        }

        /* Dark overlay for better text readability */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(8px);
            z-index: -1;
        }

        .box {
            background: rgba(255, 255, 255, 0.95);
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 500px;
            width: 90%;
            position: relative;
            z-index: 1;
            animation: slideIn 0.6s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .status-icon {
            font-size: 64px;
            margin-bottom: 20px;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }

        .name {
            font-size: 1.8em;
            margin: 15px 0;
            color: #2c3e50;
            font-weight: 700;
        }

        .status-text {
            font-size: 1.1em;
            color: #555;
            margin: 20px 0;
            line-height: 1.6;
        }

        .status-badge {
            display: inline-block;
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.95em;
            margin: 10px 0;
            box-shadow: 0 4px 15px rgba(230, 126, 34, 0.3);
        }

        .timer {
            font-size: 3.5em;
            font-weight: bold;
            background: linear-gradient(135deg, #e67e22, #d35400);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 25px 0;
            font-family: 'Courier New', monospace;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }

        .timer-label {
            font-size: 0.95em;
            color: #7f8c8d;
            margin-bottom: 20px;
        }

        .progress-container {
            width: 100%;
            height: 6px;
            background: #ecf0f1;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 25px;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #e67e22, #f39c12);
            border-radius: 10px;
            transition: width 1s linear;
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% {
                background-position: -100% 0;
            }
            100% {
                background-position: 200% 0;
            }
        }

        .info-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 10px;
            padding: 15px;
            margin-top: 25px;
            font-size: 0.9em;
            color: #856404;
        }

        .info-box strong {
            color: #e67e22;
        }

        /* Floating particles animation */
        .particle {
            position: fixed;
            width: 10px;
            height: 10px;
            background: rgba(255, 255, 255, 0.6);
            border-radius: 50%;
            animation: float 6s infinite ease-in-out;
            z-index: 0;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) translateX(0);
                opacity: 0;
            }
            50% {
                opacity: 1;
            }
        }

        .particle:nth-child(1) { left: 20%; animation-delay: 0s; animation-duration: 8s; }
        .particle:nth-child(2) { left: 40%; animation-delay: 1s; animation-duration: 6s; }
        .particle:nth-child(3) { left: 60%; animation-delay: 2s; animation-duration: 7s; }
        .particle:nth-child(4) { left: 80%; animation-delay: 3s; animation-duration: 9s; }

        @media (max-width: 600px) {
            .box {
                padding: 30px 20px;
            }
            .timer {
                font-size: 2.5em;
            }
            .name {
                font-size: 1.4em;
            }
        }
    </style>
</head>
<body>
    <!-- Background overlay -->
    <div class="overlay"></div>

    <!-- Floating particles -->
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>

    <div class="box">
        <div class="status-icon">⏳</div>
        
        <h1 class="name">
            Bonjour <?= htmlspecialchars(trim(($data['firstName'] ?? '') . ' ' . ($data['lastName'] ?? '')) ?: $username) ?> !
        </h1>
        
        <p class="status-text">Votre compte est actuellement</p>
        <span class="status-badge">⏰ En attente de validation</span>
        
        <p class="status-text" style="font-size: 0.95em; margin-top: 15px;">
            Un administrateur examinera votre demande sous peu.
        </p>

        <div class="timer" id="timer">
            <?= sprintf("%02d:%02d:%02d", floor($remaining/3600), floor(($remaining%3600)/60), $remaining%60) ?>
        </div>
        
        <p class="timer-label">⏱️ Temps restant avant expiration</p>

        <div class="progress-container">
            <div class="progress-bar" id="progressBar"></div>
        </div>

        <div class="info-box">
            <strong>💡 Astuce :</strong> Vous recevrez un email dès que votre compte sera approuvé. 
            Pensez à vérifier vos spams !
        </div>
    </div>

    <script>
        const totalTime = 48 * 3600; // 48 hours in seconds
        let time = <?= $remaining ?>;
        const timer = document.getElementById('timer');
        const progressBar = document.getElementById('progressBar');

        // Update progress bar
        function updateProgress() {
            const percentage = (time / totalTime) * 100;
            progressBar.style.width = percentage + '%';
        }

        updateProgress();

        setInterval(() => {
            if (time <= 0) {
                location.reload();
                return;
            }
            
            const h = String(Math.floor(time / 3600)).padStart(2, '0');
            const m = String(Math.floor((time % 3600) / 60)).padStart(2, '0');
            const s = String(time % 60).padStart(2, '0');
            
            timer.textContent = h + ':' + m + ':' + s;
            time--;
            
            updateProgress();
        }, 1000);

        // Auto-refresh check every 30 seconds
        setInterval(() => {
            fetch(window.location.href, {
                method: 'HEAD'
            }).then(() => {
                // Check if status changed
                location.reload();
            });
        }, 30000);
    </script>
</body>
</html>