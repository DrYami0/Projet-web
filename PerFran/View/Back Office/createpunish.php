<?php
require_once __DIR__ . '/../../Config.php';
require_once __DIR__ . '/../../Controller/ReportC.php';
require_once __DIR__ . '/../../Model/Report.php';
require_once __DIR__ . '/../../Model/Punishment.php';
require_once __DIR__ . '/../../Utils/PunishmentValidation.php';

$reportController = new ReportC();
$error = null;
$success = null;
$report = null;

if (isset($_GET['rid'])) {
    try {
        $result = $reportController->displayReports(null);
        $allReports = $result->fetchAll(PDO::FETCH_ASSOC);
        $rid = (int)$_GET['rid'];
        
        foreach ($allReports as $row) {
            if ($row['rid'] === $rid) {
                $report = $row;
                break;
            }
        }
        
        if (!$report) {
            $error = "Rapport non trouvé.";
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rid'])) {
    $rid = (int)$_POST['rid'];
    $nomj = trim($_POST['nomj'] ?? '');
    $banReason = trim($_POST['banReason'] ?? '');
    $banDuration = (int)($_POST['banDuration'] ?? 0);
    $banType = trim($_POST['banType'] ?? 'temporaire');
    
    $validationError = PunishmentValidation::validateAll($banReason, $banDuration, $banType);
    
    if ($validationError === null) {
        try {
            $punishment = new Punishment($nomj, $banReason, $banDuration, $banType);
            if (isset($report)) {
                $db = \Config::getConnexion();
                $insertPunishment = $db->prepare("INSERT INTO punishments (punishedID, reason, duration, banType, rid) VALUES (:punishedID, :reason, :duration, :banType, :rid)");
                $insertPunishment->execute([
                    ':punishedID' => $nomj,
                    ':reason' => $banReason,
                    ':duration' => $banDuration,
                    ':banType' => $banType,
                    ':rid' => $rid
                ]);
                $pid = $db->lastInsertId();
                $updatedReport = new Report(
                    $report['description'],
                    $report['reporterID'],
                    $report['nomj'],
                    $report['gid'],
                    1, 
                    (int)$pid
                );
                $reportController->editReport($updatedReport, $rid);
            }
            
            $success = "Le joueur a été banni avec succès pour une durée de {$banDuration} jour(s). Redirection en cours...";
            echo '<meta http-equiv="refresh" content="2;url=displayReports.php">';
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } else {
        $error = $validationError;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bannir un Joueur</title>
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
            background: linear-gradient(135deg, #c41e3a, #a01530);
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
            color: #c41e3a;
            margin-top: 0;
            margin-bottom: 24px;
            font-size: 20px;
        }
        .report-info {
            background: linear-gradient(180deg, #ffe6e6, #ffcccc);
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #c41e3a;
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
            border-color: #c41e3a;
            box-shadow: 0 0 0 3px rgba(196, 30, 58, 0.1);
        }
        .error-message {
            color: #c41e3a;
            font-size: 12px;
            margin-top: 4px;
            display: none;
        }
        .error-message.show {
            display: block;
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
            background: linear-gradient(135deg, #c41e3a, #a01530);
            color: white;
        }
        .btn-submit:hover {
            box-shadow: 0 4px 12px rgba(196, 30, 58, 0.3);
            transform: translateY(-2px);
        }
        .btn-cancel {
            background: #ffffff;
            color: #c41e3a;
            border: 1px solid #e0e0e0;
        }
        .btn-cancel:hover {
            background: #f8f9fa;
            border-color: #c41e3a;
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
            background: linear-gradient(135deg, #c41e3a, #a01530);
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
            .MainTitle.smallHeader h1 {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="MainTitle smallHeader">
        <div class="brand">
            <h1>⛔ Ban Joueur</h1>
            <p>Système de Bannissement</p>
        </div>
    </div>

    <div class="container">
        <div class="form-container">
            <h2>Bannir un Joueur</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if (isset($report)): ?>
                <div class="report-info">
                    <strong>Rapport ID:</strong> <?php echo htmlspecialchars($report['rid']); ?><br>
                    <strong>Joueur Signalé:</strong> <?php echo htmlspecialchars($report['nomj']); ?><br>
                    <strong>Raison du Rapport:</strong> <?php echo htmlspecialchars(substr($report['description'] ?? 'N/A', 0, 100)); ?>...
                </div>
                
                <form method="POST" action="">
                    <input type="hidden" name="rid" value="<?php echo htmlspecialchars($report['rid']); ?>">
                    
                    <div class="form-group">
                        <label for="nomj">Nom du Joueur à Bannir: <span style="color: #c41e3a;">*</span></label>
                        <input type="text" id="nomj" name="nomj" required placeholder="Nom du joueur" readonly value="<?php echo htmlspecialchars($report['nomj']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description du Rapport: <span style="color: #c41e3a;">*</span></label>
                        <textarea id="description" name="description" readonly placeholder="Description"><?php echo htmlspecialchars($report['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="banReason">Raison du Ban: <span style="color: #c41e3a;">*</span></label>
                        <textarea id="banReason" name="banReason" placeholder="Expliquer la raison du bannissement (10-500 caractères)"><?php echo htmlspecialchars($_POST['banReason'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="banDuration">Durée du Ban (jours): <span style="color: #c41e3a;">*</span></label>
                            <input type="number" id="banDuration" name="banDuration" placeholder="Nombre de jours" value="<?php echo htmlspecialchars($_POST['banDuration'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="banType">Type de Ban:</label>
                            <select id="banType" name="banType" style="width: 100%; padding: 10px 14px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
                                <option value="permanent" <?php echo (isset($_POST['banType']) && $_POST['banType'] === 'permanent') ? 'selected' : ''; ?>>Permanent</option>
                                <option value="temporaire" <?php echo (isset($_POST['banType']) && $_POST['banType'] === 'temporaire') ? 'selected' : 'selected'; ?>>Temporaire</option>
                                <option value="temporaire">Bannir des evenements</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-submit">Bannir le Joueur</button>
                        <a href="displayReports.php" class="btn btn-cancel" style="text-decoration: none; text-align: center;">Annuler</a>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert alert-error">
                    Rapport non trouvé. <a href="displayReports.php">Retour aux rapports</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 PerFran</p>
    </footer>
</body>
</html>
