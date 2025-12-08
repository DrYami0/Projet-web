<?php
session_start();

require_once __DIR__ . '/../../Controller/ReportC.php';
require_once __DIR__ . '/../../Model/Report.php';

$reportController = new ReportC();
$error = null;
$success = null;
$uid = $_SESSION['uid'] ?? 1;

if ($uid == 0) {
    $error = "You must be logged in to view your reports.";
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    try {
        $rid = (int)($_POST['rid'] ?? 0);
        
        if ($rid <= 0) {
            throw new Exception('Invalid report ID.');
        }
        $report_data = $reportController->getReportById($rid);
        if (!$report_data || $report_data['reporterID'] != $uid) {
            throw new Exception('You can only delete your own reports.');
        }
        
        $reportController->deleteReport($rid);
        $success = "Report deleted successfully!";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
$reports = [];
if ($uid > 0) {
    $liste = $reportController->displayReports($uid);
    $reports = $liste->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reports</title>
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
            max-width: 1200px;
            margin: 20px auto;
            padding: 18px;
            flex: 1;
            width: 100%;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .page-header h2 {
            color: #017a8a;
            margin: 0;
            font-size: 24px;
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
        .table-container {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #ececec;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.03);
            overflow: hidden;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead {
            background: #f5f5f5;
            border-bottom: 2px solid #0195a8;
        }
        th {
            padding: 14px;
            text-align: left;
            font-weight: 600;
            color: #017a8a;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        td {
            padding: 14px;
            border-bottom: 1px solid #ececec;
            font-size: 13px;
        }
        tbody tr:hover {
            background: #f9f9f9;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-active {
            background: #e6f7ee;
            color: #0e6b3d;
        }
        .status-banned {
            background: #ffe6e6;
            color: #c41e3a;
        }
        .description-cell {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .actions {
            display: flex;
            gap: 8px;
        }
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-edit {
            background: #0195a8;
            color: white;
        }
        .btn-edit:hover {
            background: #017a8a;
            box-shadow: 0 2px 8px rgba(1, 149, 168, 0.3);
        }
        .btn-delete {
            background: #c41e3a;
            color: white;
            border: none;
        }
        .btn-delete:hover {
            background: #a01729;
            box-shadow: 0 2px 8px rgba(196, 30, 58, 0.3);
        }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }
        .empty-state h3 {
            color: #666;
            margin-bottom: 10px;
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
            .page-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            .nav-buttons {
                margin-left: 0;
            }
            table {
                font-size: 12px;
            }
            th, td {
                padding: 10px;
            }
            .actions {
                flex-direction: column;
            }
            .btn {
                width: 100%;
                text-align: center;
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
            <a href="createReport.php" class="nav-btn">+ Créer un Nouveau Rapport</a>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h2>Mes Rapports</h2>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if ($uid > 0 && count($reports) > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Description</th>
                            <th>Nom du Joueur Signalé</th>
                            <th>ID Jeu</th>
                            <th>État</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $report): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($report['rid']); ?></td>
                                <td class="description-cell"><?php echo htmlspecialchars(substr($report['description'], 0, 50)); ?></td>
                                <td><?php echo htmlspecialchars($report['nomj']); ?></td>
                                <td><?php echo htmlspecialchars($report['gid']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $report['status'] == 1 ? 'status-banned' : 'status-active'; ?>">
                                        <?php echo $report['status'] == 1 ? 'Fermé' : 'Actif'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="edit.php?rid=<?php echo htmlspecialchars($report['rid']); ?>" class="btn btn-edit">Modifier</a>
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="rid" value="<?php echo htmlspecialchars($report['rid']); ?>">
                                            <button type="submit" class="btn btn-delete" onclick="return confirm('Confirmez-vous la suppression de ce rapport ?')">Supprimer</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="table-container">
                <div class="empty-state">
                    <h3>Aucun rapport pour le moment</h3>
                    <p>Vous n'avez pas encore créé de rapport. <a href="createReport.php">Créez votre premier rapport</a></p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; 2025 PerFran </p>
    </footer>
</body>
</html>
