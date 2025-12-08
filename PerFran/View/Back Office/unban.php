<?php
require_once __DIR__ . '/../../Config.php';
require_once __DIR__ . '/../../Controller/ReportC.php';
require_once __DIR__ . '/../../Model/Report.php';

$reportController = new ReportC();
$error = null;
$success = null;
$report = null;
$punishment = null;

if (isset($_GET['rid'])) {
    try {
        $rid = (int)$_GET['rid'];
        $report = $reportController->getReportById($rid);
        
        if (!$report) {
            $error = "Rapport non trouvé.";
        } else if ($report['status'] != 1) {
            $error = "Ce rapport n'est pas banni.";
        } else {
            $db = \Config::getConnexion();
            $punishStmt = $db->prepare("SELECT * FROM punishments WHERE rid = :rid LIMIT 1");
            $punishStmt->execute([':rid' => $rid]);
            $punishment = $punishStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$punishment) {
                $error = "Aucun enregistrement de punition trouvé.";
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'unban') {
    try {
        $rid = (int)($_POST['rid'] ?? 0);
        
        if ($rid <= 0) {
            throw new Exception('Invalid report ID.');
        }
        
        $report_data = $reportController->getReportById($rid);
        if (!$report_data) {
            throw new Exception('Rapport non trouvé.');
        }
        
        $db = \Config::getConnexion();
        $deletePunish = $db->prepare("DELETE FROM punishments WHERE rid = :rid");
        $deletePunish->execute([':rid' => $rid]);
        $updatedReport = new Report(
            $report_data['description'],
            $report_data['reporterID'],
            $report_data['nomj'],
            $report_data['gid'],
            0, 
            0  
        );
        $reportController->editReport($updatedReport, $rid);
        
        $success = "Le joueur a été débanni avec succès. Redirection en cours...";
        echo '<meta http-equiv="refresh" content="2;url=displayReports.php">';
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Débannir un Joueur</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            margin: 0;
            background: #f8f9fa;
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .MainTitle.smallHeader {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 18px;
            background: linear-gradient(135deg, #17a2b8, #138496);
            color: #fff;
        }
        .MainTitle.smallHeader .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: inherit;
        }
        .MainTitle.smallHeader h1 {
            margin: 0;
            font-size: 18px;
        }
        .MainTitle.smallHeader p {
            margin: 0;
            opacity: 0.95;
            font-size: 13px;
        }
        .container {
            max-width: 1100px;
            margin: 20px auto;
            padding: 18px;
            flex: 1;
            width: 100%;
        }
        .form-container {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            border: 1px solid #ececec;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.03);
        }
        .form-container h2 {
            color: #17a2b8;
            margin-top: 0;
            margin-bottom: 24px;
            font-size: 20px;
        }
        .punishment-info {
            background: linear-gradient(180deg, #d1ecf1, #bee5eb);
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #17a2b8;
            font-size: 14px;
            color: #333;
        }
        .form-group {
            margin-bottom: 18px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        input[type="text"],
        input[type="number"],
        textarea,
        input[type="hidden"] {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
        }
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 24px;
        }
        .btn {
            flex: 1;
            padding: 10px 14px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
            display: inline-block;
        }
        .btn-unban {
            background: linear-gradient(135deg, #17a2b8, #138496);
            color: white;
        }
        .btn-unban:hover {
            box-shadow: 0 4px 12px rgba(23, 162, 184, 0.3);
            transform: translateY(-2px);
        }
        .btn-cancel {
            background: #ffffff;
            color: #17a2b8;
            border: 1px solid #e0e0e0;
        }
        .btn-cancel:hover {
            background: #f8f9fa;
            border-color: #17a2b8;
        }
        .alert {
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-weight: 600;
            font-size: 14px;
        }
        .alert-error {
            color: #c41e3a;
            background-color: #ffe6e6;
            border: 1px solid #ffcccc;
        }
        .alert-success {
            color: #0e6b3d;
            background-color: #e6f7ee;
            border: 1px solid #c8e6d7;
        }
        footer {
            background: linear-gradient(135deg, #17a2b8, #138496);
            color: #fff;
            padding: 18px;
            text-align: center;
            margin-top: 18px;
            border-radius: 8px;
            margin: 18px;
        }
        @media(max-width: 900px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="MainTitle smallHeader">
        <div class="brand">
            <h1>🔓 Débannir Joueur</h1>
            <p>Système de Débannissement</p>
        </div>
    </div>

    <div class="container">
        <div class="form-container">
            <h2>Débannir un Joueur</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if (isset($report) && isset($punishment)): ?>
                <div class="punishment-info">
                    <strong>Rapport ID:</strong> <?php echo htmlspecialchars($report['rid']); ?><br>
                    <strong>Joueur Banni:</strong> <?php echo htmlspecialchars($report['nomj']); ?><br>
                    <strong>ID de Punition:</strong> <?php echo htmlspecialchars($punishment['pid'] ?? 'N/A'); ?>
                </div>
                
                <form method="POST" action="">
                    <input type="hidden" name="rid" value="<?php echo htmlspecialchars($report['rid']); ?>">
                    
                    <div class="form-group">
                        <label for="nomj">Nom du Joueur:</label>
                        <input type="text" id="nomj" name="nomj" readonly value="<?php echo htmlspecialchars($report['nomj'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="reason">Raison du Ban:</label>
                        <textarea id="reason" name="reason" readonly><?php echo htmlspecialchars($punishment['reason'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="duration">Durée du Ban (jours):</label>
                            <input type="number" id="duration" name="duration" readonly value="<?php echo htmlspecialchars($punishment['duration'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="banType">Type de Ban:</label>
                            <input type="text" id="banType" name="banType" readonly value="<?php echo htmlspecialchars($punishment['banType'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="action" value="unban" class="btn btn-unban">Libérer le Joueur</button>
                        <a href="displayReports.php" class="btn btn-cancel">Retour</a>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert alert-error">
                    Rapport ou punition non trouvé(e). <a href="displayReports.php">Retour aux rapports</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 PerFran </p>
    </footer>
</body>
</html>
