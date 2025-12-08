<?php
session_start();

require_once __DIR__ . '/../../Controller/ReportC.php';
require_once __DIR__ . '/../../Model/Report.php';

$reportController = new ReportC();
$error = null;
$success = null;
$report = null;
$uid = $_SESSION['uid'] ?? 1;
$rid = (int)($_GET['rid'] ?? 0);

if ($rid <= 0) {
    $error = "Invalid report ID.";
} else {
    $report_data = $reportController->getReportById($rid);
    if (!$report_data) {
        $error = "Report not found.";
    } elseif ($report_data['reporterID'] != $uid) {
        $error = "You can only edit your own reports.";
    } else {
        $report = $report_data;
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $report) {
    try {
        $description = trim($_POST['description'] ?? '');
        $nomj = trim($_POST['nomj'] ?? '');
        if (empty($description)) {
            throw new Exception('La description est obligatoire.');
        }
        if (strlen($description) < 10) {
            throw new Exception('La description doit contenir au moins 10 caractères.');
        }
        if (strlen($description) > 1000) {
            throw new Exception('La description ne peut pas dépasser 1000 caractères.');
        }
        if (empty($nomj)) {
            throw new Exception('Le nom du joueur est obligatoire.');
        }
        if (strlen($nomj) < 2) {
            throw new Exception('Le nom du joueur doit contenir au moins 2 caractères.');
        }
        
        $updatedReport = new Report(
            $description,
            $uid,
            $nomj,
            $report['gid'],
            $report['status'],
            $report['pid']
        );
        
        $reportController->editReport($updatedReport, $rid);
        $success = "Rapport mis à jour avec succès!";
        $report_data = $reportController->getReportById($rid);
        $report = $report_data;
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
    <title>Modifier le Rapport</title>
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
        .nav-buttons {
            margin-left: auto;
            display: flex;
            gap: 10px;
        }
        .nav-btn {
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .nav-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
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
        textarea {
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
            .nav-buttons {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="MainTitle smallHeader">
        <div class="brand">
            <h1>PerFran</h1>
            <p>Gestion des Rapports</p>
        </div>
        <div class="nav-buttons">
            <a href="display.php" class="nav-btn">← Retour aux Rapports</a>
        </div>
    </div>

    <div class="container">
        <div class="form-container">
            <h2>Modifier le Rapport</h2>
            
            <?php if (isset($error) && $error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if ($report): ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="description">Description: <span style="color: #c41e3a;">*</span></label>
                        <textarea id="description" name="description" placeholder="Entrez la description du rapport (10-1000 caractères)"><?php echo htmlspecialchars($report['description']); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nomj">Nom du Joueur Signalé: <span style="color: #c41e3a;">*</span></label>
                            <input type="text" id="nomj" name="nomj" placeholder="Entrez le nom du joueur" value="<?php echo htmlspecialchars($report['nomj']); ?>">
                        </div>

                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-submit">Enregistrer</button>
                        <a href="display.php" class="btn btn-cancel" style="text-align: center; text-decoration: none;">Retour</a>
                    </div>
                </form>

                <script>
                    const form = document.querySelector('form');
                    const descriptionInput = document.getElementById('description');
                    const nomjInput = document.getElementById('nomj');
                    
                    // Real-time validation feedback
                    descriptionInput.addEventListener('input', () => {
                        const value = descriptionInput.value.trim();
                        if (value.length < 10 || value.length > 1000) {
                            descriptionInput.style.borderColor = '#c41e3a';
                            descriptionInput.title = 'La description doit contenir entre 10 et 1000 caractères';
                        } else {
                            descriptionInput.style.borderColor = '#0195a8';
                            descriptionInput.title = '';
                        }
                    });

                    nomjInput.addEventListener('input', () => {
                        const value = nomjInput.value.trim();
                        if (value.length < 2) {
                            nomjInput.style.borderColor = '#c41e3a';
                            nomjInput.title = 'Le nom du joueur doit contenir au moins 2 caractères';
                        } else {
                            nomjInput.style.borderColor = '#0195a8';
                            nomjInput.title = '';
                        }
                    });

                    form.addEventListener('submit', (e) => {
                        const descValue = descriptionInput.value.trim();
                        const nomjValue = nomjInput.value.trim();

                        let isValid = true;
                        let errorMessage = '';

                        if (descValue.length < 10 || descValue.length > 1000) {
                            isValid = false;
                            errorMessage += 'La description doit contenir entre 10 et 1000 caractères.\n';
                        }

                        if (nomjValue.length < 2) {
                            isValid = false;
                            errorMessage += 'Le nom du joueur doit contenir au moins 2 caractères.\n';
                        }

                        if (!isValid) {
                            e.preventDefault();
                            alert('Validation Error:\n' + errorMessage);
                        }
                    });
                </script>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 PerFran </p>
    </footer>
</body>
</html>
