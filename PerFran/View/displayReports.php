<?php
require_once __DIR__ . '/../Controller/ReportC.php';
require_once __DIR__ . '/../Model/Report.php';

$reportController = new ReportC();
$reports = [];
$error = null;

try {
    $result = $reportController->displayReports();
    $reports = $result->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Reports</title>
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
        .controls {
            display: flex;
            gap: 12px;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }
        #filterInput {
            flex: 1;
            min-width: 220px;
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        }
        .actions {
            display: flex;
            gap: 8px;
        }
        .btn {
            display: inline-block;
            padding: 10px 14px;
            border-radius: 8px;
            background: linear-gradient(135deg, #0195a8, #017a8a);
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn:hover {
            box-shadow: 0 4px 12px rgba(1, 149, 168, 0.3);
            transform: translateY(-2px);
        }
        .btn.secondary {
            background: #ffffff;
            color: #017a8a;
            border: 1px solid #e0e0e0;
        }
        .btn.secondary:hover {
            background: #f8f9fa;
            border-color: #017a8a;
        }
        .tableWrap {
            background: #fff;
            border-radius: 12px;
            padding: 14px;
            border: 1px solid #ececec;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.03);
        }
        .wordsTable {
            width: 100%;
            border-collapse: collapse;
            font-size: 15px;
        }
        .wordsTable caption {
            caption-side: top;
            text-align: left;
            padding-bottom: 8px;
            font-weight: 700;
            color: #017a8a;
        }
        .wordsTable thead th {
            background: linear-gradient(180deg, #f6f9fa, #eef6f8);
            padding: 12px 10px;
            text-align: left;
            border-bottom: 1px solid #e6eef0;
            font-weight: 700;
            color: #017a8a;
        }
        .wordsTable tbody td {
            padding: 12px 10px;
            border-bottom: 1px dashed #f0f4f5;
        }
        .wordsTable tbody tr:hover {
            background: linear-gradient(90deg, rgba(1, 149, 168, 0.04), transparent);
        }
        .empty {
            padding: 28px;
            text-align: center;
            color: #666;
            font-weight: 600;
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
            .controls {
                flex-direction: column;
                align-items: stretch;
            }
            .actions {
                justify-content: flex-end;
            }
            .MainTitle.smallHeader img {
                height: 44px;
            }
            .wordsTable thead th, .wordsTable tbody td {
                padding: 10px 8px;
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
        <div class="controls">
            <input type="text" id="filterInput" placeholder="Search reports...">
            <div class="actions">
                <a href="create.php" class="btn">+ Create New Report</a>
            </div>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($reports)): ?>
            <div class="tableWrap">
                <table class="wordsTable">
                    <caption>All Reports</caption>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Description</th>
                            <th>Reporter</th>
                            <th>Reported</th>
                            <th>Game Log</th>
                            <th>Status</th>
                            <th>Admin</th>
                            <th>Punishment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $report): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($report['rid'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars(substr($report['description'] ?? 'N/A', 0, 50)); ?></td>
                                <td><?php echo htmlspecialchars($report['reporterID'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($report['reportedID'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($report['gid'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($report['status'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($report['aid'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($report['pid'] ?? 'N/A'); ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="edit.php?rid=<?php echo htmlspecialchars($report['rid']); ?>" class="btn btn.secondary">Edit</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="tableWrap">
                <div class="empty">No reports found. <a href="create.php">Create one now</a></div>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; 2025 PerFran Report Management System. All rights reserved.</p>
    </footer>
</body>
</html>
