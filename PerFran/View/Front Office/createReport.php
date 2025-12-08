<?php
session_start();

require_once __DIR__ . '/../../Controller/ReportC.php';
require_once __DIR__ . '/../../Model/Report.php';
require_once __DIR__ . '/../../Utils/ReportValidation.php';

$reportController = new ReportC();
$error = null;
$success = null;
$uid = $_SESSION['uid'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = trim($_POST['description'] ?? '');
    $nomj = trim($_POST['nomj'] ?? '');
    $gid = (int)($_POST['gid'] ?? 0);
    
    $validationError = ReportValidation::validateAll($description, $nomj);
    
    if ($validationError === null) {
        try {
            $report = new Report(
                $description,
                (int)($uid),
                $nomj,
                $gid,
                0,
                null
            );
            $rid = $reportController->addReport($report);
            $success = "Rapport créé avec succès!";
            $_POST = [];
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
    <title>Créer un Rapport</title>
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
        .error-message {
            color: #c41e3a;
            font-size: 12px;
            margin-top: 4px;
            display: none;
        }
        .error-message.show {
            display: block;
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
            <p>Report Management</p>
        </div>
        <div class="nav-buttons">
            <a href="display.php" class="nav-btn">📋 My Reports</a>
        </div>
    </div>

    <div class="container">
        <div class="form-container">
            <h2>Créer un Nouveau Rapport</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="description">Description: <span style="color: #c41e3a;">*</span></label>
                    <textarea id="description" name="description" placeholder="Entrez la description du rapport (10-1000 caractères)"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nomj">Nom du Joueur Signalé: <span style="color: #c41e3a;">*</span></label>
                        <input type="text" id="nomj" name="nomj" placeholder="Entrez le nom du joueur" value="<?php echo htmlspecialchars($_POST['nomj'] ?? ''); ?>">
                    </div>
                </div>
                
                <input type="hidden" name="gid" value="21">
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-submit">Créer le Rapport</button>
                    <button type="reset" class="btn btn-cancel">Effacer</button>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 PerFran </p>
    </footer>
</body>
</html>
