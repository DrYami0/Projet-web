<?php
session_start();
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
$uid = $_SESSION['uid'] ?? null;
$userRole = $_SESSION['role'] ?? 'user'; 
if (!$uid) {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PerFran - Report Management System</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }
            body {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                background: linear-gradient(135deg, #0195a8, #017a8a);
            }
            .container {
                background: white;
                border-radius: 12px;
                padding: 40px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
                max-width: 500px;
                text-align: center;
            }
            h1 {
                color: #017a8a;
                margin-bottom: 20px;
                font-size: 32px;
            }
            p {
                color: #666;
                margin-bottom: 30px;
                font-size: 16px;
                line-height: 1.6;
            }
            .buttons {
                display: flex;
                gap: 15px;
                flex-direction: column;
            }
            .btn {
                padding: 12px 24px;
                border: none;
                border-radius: 8px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                text-decoration: none;
                transition: all 0.3s;
                display: inline-block;
            }
            .btn-user {
                background: linear-gradient(135deg, #0195a8, #017a8a);
                color: white;
            }
            .btn-user:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(1, 149, 168, 0.3);
            }
            .btn-admin {
                background: #f0f0f0;
                color: #333;
                border: 2px solid #0195a8;
            }
            .btn-admin:hover {
                background: #0195a8;
                color: white;
                transform: translateY(-2px);
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🎮 PerFran</h1>
            <p>Report Management System</p>
            <p style="color: #c41e3a; font-size: 14px;">Vous devez vous connecter pour continuer</p>
            <div class="buttons">
                <form method="POST">
                    <input type="hidden" name="action" value="login_user">
                    <button type="submit" class="btn btn-user">📝 Accès Utilisateur</button>
                </form>
                <form method="POST">
                    <input type="hidden" name="action" value="login_admin">
                    <button type="submit" class="btn btn-admin">🔑 Accès Admin</button>
                </form>
            </div>
        </div>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            if ($action === 'login_user') {
                $_SESSION['uid'] = 1; 
                $_SESSION['role'] = 'user';
                header('Location: View/Front Office/createReport.php');
                exit;
            } elseif ($action === 'login_admin') {
                $_SESSION['uid'] = 999; 
                $_SESSION['role'] = 'admin';
                header('Location: View/Back Office/displayReports.php');
                exit;
            }
        }
        ?>
    </body>
    </html>
    <?php
} else {
    if ($userRole === 'admin') {
        header('Location: View/Back Office/displayReports.php');
    } else {
        header('Location: View/Front Office/createReport.php');
    }
    exit;
}
?>
