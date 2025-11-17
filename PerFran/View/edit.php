<?php
require_once __DIR__ . '/../Controller/ReportC.php';
require_once __DIR__ . '/../Model/Report.php';

$reportController = new ReportC();
$error = null;
$success = null;
$report = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rid'])) {
    try {
        $rid = (int)$_POST['rid'];
        $reportObj = new Report(
            $_POST['description'] ?? '',
            (int)($_POST['reporterID'] ?? 0),
            (int)($_POST['reportedID'] ?? 0),
            (int)($_POST['gid'] ?? 0),
            (int)($_POST['status'] ?? 0),
            (int)($_POST['aid'] ?? 0),
            (int)($_POST['pid'] ?? 0)
        );
        $reportController->editReport($reportObj, $rid);
        $success = 'Report updated successfully.';
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
if (isset($_GET['rid'])) {
    try {
        $result = $reportController->displayReports();
        $allReports = $result->fetchAll(PDO::FETCH_ASSOC);
        $rid = (int)$_GET['rid'];
        
        foreach ($allReports as $row) {
            if ($row['rid'] === $rid) {
                $report = $row;
                break;
            }
        }
        
        if (!$report) {
            $error = "Report not found.";
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
    <title>Edit Report</title>
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
            <p>Report Management</p>
        </div>
    </div>

    <div class="container">
        <div class="form-container">
            <h2>Edit Report</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if (isset($report)): ?>
                <div class="report-info">
                    <strong>Report ID:</strong> <?php echo htmlspecialchars($report['rid']); ?>
                </div>
                
                <form method="POST" action="">
                    <input type="hidden" name="rid" value="<?php echo htmlspecialchars($report['rid']); ?>">
                    
                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea id="description" name="description" placeholder="Enter report description"><?php echo htmlspecialchars($report['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="reporterID">Reporter ID:</label>
                            <input type="number" id="reporterID" name="reporterID" placeholder="Enter reporter ID" value="<?php echo htmlspecialchars($report['reporterID'] ?? ''); ?>" min="1">
                        </div>
                        
                        <div class="form-group">
                            <label for="reportedID">Reported ID:</label>
                            <input type="number" id="reportedID" name="reportedID" placeholder="Enter reported ID" value="<?php echo htmlspecialchars($report['reportedID'] ?? ''); ?>" min="1">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="gid">Game Log ID (gid):</label>
                            <input type="number" id="gid" name="gid" placeholder="Enter game log ID" value="<?php echo htmlspecialchars($report['gid'] ?? ''); ?>" min="0">
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Status:</label>
                            <input type="number" id="status" name="status" placeholder="Enter status" value="<?php echo htmlspecialchars($report['status'] ?? ''); ?>" min="0">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="aid">Admin ID (aid):</label>
                            <input type="number" id="aid" name="aid" placeholder="Enter admin ID" value="<?php echo htmlspecialchars($report['aid'] ?? ''); ?>" min="0">
                        </div>
                        
                        <div class="form-group">
                            <label for="pid">Punishment ID (pid):</label>
                            <input type="number" id="pid" name="pid" placeholder="Enter punishment ID" value="<?php echo htmlspecialchars($report['pid'] ?? ''); ?>" min="0">
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-submit">Update Report</button>
                        <button type="reset" class="btn btn-cancel">Reset</button>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert alert-error">Report not found or not loaded. <a href="displayReports.php">View all reports</a></div>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 PerFran Report Management System. All rights reserved.</p>
    </footer>
</body>
</html>
