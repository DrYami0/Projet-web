<?php
require_once __DIR__ . '/../../Controller/ReportC.php';
require_once __DIR__ . '/../../Model/Report.php';

$reportController = new ReportC();
$error = null;
$success = null;
$report = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        $rid = (int)($_POST['rid'] ?? 0);
        
        if ($rid <= 0) {
            throw new Exception('Invalid report ID.');
        }
        
        $report_data = $reportController->getReportById($rid);
        if (!$report_data) {
            throw new Exception('Rapport non trouvé.');
        }
        
        if ($_POST['action'] === 'resolve') {
            $updatedReport = new Report(
                $report_data['description'],
                $report_data['reporterID'],
                $report_data['nomj'],
                $report_data['gid'],
                1, 
                NULL 
            );
            $reportController->editReport($updatedReport, $rid);
            $success = "Le rapport a été marqué comme résolu. Redirection en cours...";
            echo '<meta http-equiv="refresh" content="2;url=displayReports.php">';
        } elseif ($_POST['action'] === 'updateBanReason') {
            $pid = (int)($_POST['pid'] ?? 0);
            $banReason = trim($_POST['banReason'] ?? '');
            
            if ($pid > 0 && !empty($banReason)) {
                require_once __DIR__ . '/../../Config.php';
                $db = \Config::getConnexion();
                $updateBan = $db->prepare("UPDATE punishments SET reason = :reason WHERE pid = :pid");
                $updateBan->execute([
                    ':reason' => $banReason,
                    ':pid' => $pid
                ]);
                $success = "La raison du ban a été mise à jour.";
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
if (isset($_GET['rid'])) {
    try {
        $result = $reportController->displayReports(null); 
        $allReports = $result->fetchAll(PDO::FETCH_ASSOC);
        $rid = (int)$_GET['rid'];
        
        foreach ($allReports as  $row) {
            if ($row['rid'] === $rid) {
                $report = $row;
                break;
            }
        }
        
        if (!$report) {
            $error = "Report not found.";
        } else {
            if (!is_null($report['pid'])) {
                require_once __DIR__ . '/../../Config.php';
                $db = \Config::getConnexion();
                $getBan = $db->prepare("SELECT * FROM punishments WHERE pid = :pid");
                $getBan->execute([':pid' => $report['pid']]);
                $banData = $getBan->fetch(PDO::FETCH_ASSOC);
                if ($banData) {
                    $report['banReason'] = $banData['reason'];
                }
            }
        }
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
    <title>Examiner Rapport</title>
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
            background: linear-gradient(135deg, #0195a8, #017a8a);
            color: #fff;
        }
        .MainTitle.smallHeader .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: inherit;
        }
        .MainTitle.smallHeader img {
            height: 56px;
            max-width: 120px;
            object-fit: contain;
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
            color: #017a8a;
            margin-top: 0;
            margin-bottom: 24px;
            font-size: 20px;
        }
        .report-info {
            background: linear-gradient(180deg, #f6f9fa, #eef6f8);
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #0195a8;
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
            transition: border-color 0.3s;
        }
        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea:focus {
            outline: none;
            border-color: #0195a8;
            box-shadow: 0 0 0 3px rgba(1, 149, 168, 0.1);
        }
        textarea {
            resize: vertical;
            min-height: 100px;
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
        }
        .btn-submit {
            background: linear-gradient(135deg, #0195a8, #017a8a);
            color: white;
        }
        .btn-submit:hover {
            box-shadow: 0 4px 12px rgba(1, 149, 168, 0.3);
            transform: translateY(-2px);
        }
        .btn-cancel {
            background: #ffffff;
            color: #017a8a;
            border: 1px solid #e0e0e0;
        }
        .btn-cancel:hover {
            background: #f8f9fa;
            border-color: #017a8a;
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
            background: linear-gradient(135deg, #0195a8, #017a8a);
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
            .MainTitle.smallHeader img {
                height: 44px;
            }
        }
    </style>
</head>
<body>
    <div class="MainTitle smallHeader">
        <div class="brand">
            <h1>PerFran</h1>
            <p>Examiner le Rapport</p>
        </div>
    </div>

    <div class="container">
        <div class="form-container">
            <h2>Examiner le Rapport</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if (isset($report)): ?>
                <div class="report-info">
                    <strong>Rapport ID:</strong> <?php echo htmlspecialchars($report['rid']); ?>
                </div>
                
                <div>
                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea id="description" name="description" readonly><?php echo htmlspecialchars($report['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nomj">Nom du Joueur Signalé:</label>
                            <input type="text" id="nomj" name="nomj" value="<?php echo htmlspecialchars($report['nomj'] ?? ''); ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="etat">État:</label>
                            <input type="text" id="etat" name="etat" value="<?php echo ($report['status'] == 0) ? 'Actif' : 'Fermé'; ?>" readonly>
                        </div>
                    </div>
                    
                    <?php if (!is_null($report['pid']) && isset($report['banReason'])): ?>
                        <form method="POST" action="">
                            <input type="hidden" name="rid" value="<?php echo htmlspecialchars($report['rid']); ?>">
                            <input type="hidden" name="pid" value="<?php echo htmlspecialchars($report['pid']); ?>">
                            <input type="hidden" name="action" value="updateBanReason">
                            
                            <div class="form-group">
                                <label for="banReason">Raison du Ban:</label>
                                <textarea id="banReason" name="banReason"><?php echo htmlspecialchars($report['banReason'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-submit">Mettre à jour la raison</button>
                            </div>
                        </form>
                    <?php endif; ?>
                    
                    <div class="form-actions">
                        <form method="POST" action="" style="flex: 1;">
                            <input type="hidden" name="rid" value="<?php echo htmlspecialchars($report['rid']); ?>">
                            <input type="hidden" name="action" value="resolve">
                            <button type="submit" class="btn btn-submit" <?php echo ($report['status'] == 1) ? 'disabled' : ''; ?> style="<?php echo ($report['status'] == 1) ? 'opacity: 0.5; cursor: not-allowed;' : ''; ?>">Résoudre</button>
                        </form>
                        <a href="displayReports.php" class="btn btn-cancel" style="text-align:center; text-decoration:none;">Retour</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-error">Rapport non trouvé. <a href="displayReports.php">Voir tous les rapports</a></div>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 PerFran </p>
    </footer>
</body>
</html>
