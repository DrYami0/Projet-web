<?php
session_start();

require_once __DIR__ . '/../../Config.php';
require_once __DIR__ . '/../../Controller/ReportC.php';
require_once __DIR__ . '/../../Model/Report.php';

$reportController = new ReportC();
$reports = [];
$activeReports = [];
$closedReports = [];
$error = null;
$uid = null;
$sortBy = $_GET['sortBy'] ?? 'rid';
$order = $_GET['order'] ?? 'ASC';

try {
    $result = $reportController->displayReports($uid);
    $allReports = $result->fetchAll(PDO::FETCH_ASSOC);
    
    $db = \Config::getConnexion();
    foreach ($allReports as &$report) {
        $punishStmt = $db->prepare("SELECT duration FROM punishments WHERE rid = :rid LIMIT 1");
        $punishStmt->execute([':rid' => $report['rid']]);
        $punishment = $punishStmt->fetch(PDO::FETCH_ASSOC);
        $report['duration'] = $punishment ? $punishment['duration'] : 0;
    }
    unset($report);
    foreach ($allReports as $report) {
        if ($report['status'] == 0) {
            $activeReports[] = $report;
        } else {
            $closedReports[] = $report;
        }
    }
    $sortField = match($sortBy) {
        'rid' => 'rid',
        'nomj' => 'nomj',
        'gid' => 'gid',
        'duration' => 'duration',
        default => 'rid'
    };
    
    usort($activeReports, function($a, $b) use ($sortField, $order) {
        $cmp = strcmp((string)$a[$sortField], (string)$b[$sortField]);
        return $order === 'DESC' ? -$cmp : $cmp;
    });
    
    usort($closedReports, function($a, $b) use ($sortField, $order) {
        $cmp = strcmp((string)$a[$sortField], (string)$b[$sortField]);
        return $order === 'DESC' ? -$cmp : $cmp;
    });
    
    $reports = $allReports;
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
        .btn-ban {
            background: linear-gradient(135deg, #c41e3a, #a01530);
            color: white;
            padding: 8px 12px;
            font-size: 13px;
        }
        .btn-ban:hover {
            box-shadow: 0 2px 8px rgba(196, 30, 58, 0.3);
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
            <p>Gestion des Rapports - Panneau Admin</p>
        </div>

    </div>

    <div class="container">
        <div style="margin-bottom: 20px; padding: 15px; background-color: #f0f6f8; border-radius: 8px; border: 1px solid #d4e8f0;">
            <h3 style="margin-top: 0; color: #017a8a;">Tri et Filtrage</h3>
            <form method="GET" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
                <div>
                    <label for="sortBy" style="display: block; font-weight: 600; margin-bottom: 5px; color: #333;">Trier par:</label>
                    <select name="sortBy" id="sortBy" style="padding: 8px 12px; border-radius: 6px; border: 1px solid #d0d0d0;">
                        <option value="rid" <?= $sortBy === 'rid' ? 'selected' : '' ?>>ID Rapport</option>
                        <option value="nomj" <?= $sortBy === 'nomj' ? 'selected' : '' ?>>Joueur Signalé</option>
                        <option value="gid" <?= $sortBy === 'gid' ? 'selected' : '' ?>>Jeu</option>
                        <option value="duration" <?= $sortBy === 'duration' ? 'selected' : '' ?>>Durée Ban</option>
                    </select>
                </div>
                
                <div>
                    <label for="order" style="display: block; font-weight: 600; margin-bottom: 5px; color: #333;">Ordre:</label>
                    <select name="order" id="order" style="padding: 8px 12px; border-radius: 6px; border: 1px solid #d0d0d0;">
                        <option value="ASC" <?= $order === 'ASC' ? 'selected' : '' ?>>Croissant</option>
                        <option value="DESC" <?= $order === 'DESC' ? 'selected' : '' ?>>Décroissant</option>
                    </select>
                </div>
                
                <button type="submit" class="btn" style="padding: 8px 16px; font-size: 14px;">Appliquer</button>
                <a href="displayReports.php" class="btn secondary" style="padding: 8px 16px; font-size: 14px;">Réinitialiser</a>
            </form>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <h2 style="color: #017a8a; margin-top: 25px; margin-bottom: 15px;">Rapports Actifs (<?= count($activeReports) ?>)</h2>
        <?php if (!empty($activeReports)): ?>
            <div class="tableWrap">
                <table class="wordsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Description</th>
                            <th>Signalé par</th>
                            <th>Joueur Signalé</th>
                            <th>Jeu</th>
                            <th>État</th>
                            <th>Durée Ban</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activeReports as $report): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($report['rid'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars(substr($report['description'] ?? 'N/A', 0, 50)) . (strlen($report['description'] ?? '') > 50 ? '...' : ''); ?></td>
                                <td><?php echo htmlspecialchars($report['reporterID'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($report['nomj'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($report['gid'] ?? 'N/A'); ?></td>
                                <td>
                                    <span style="background: #0195a8; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">Actif</span>
                                </td>
                                <td>
                                    <?php if ($report['duration'] > 0): ?>
                                        <strong><?php echo htmlspecialchars($report['duration']); ?> jour(s)</strong>
                                    <?php else: ?>
                                        <span style="color: #999;">Pas de ban</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="editReport.php?rid=<?php echo htmlspecialchars($report['rid']); ?>" class="btn btn.secondary" style="font-size: 12px; padding: 6px 10px;">Examiner</a>
                                        <a href="createpunish.php?rid=<?php echo htmlspecialchars($report['rid']); ?>" class="btn btn-ban" style="font-size: 12px; padding: 6px 10px;">Bannir</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="tableWrap">
                <div class="empty">Aucun rapport actif.</div>
            </div>
        <?php endif; ?>
        <h2 style="color: #017a8a; margin-top: 40px; margin-bottom: 15px;">Rapports Fermés / Bannis (<?= count($closedReports) ?>)</h2>
        <?php if (!empty($closedReports)): ?>
            <div class="tableWrap">
                <table class="wordsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Description</th>
                            <th>Signalé par</th>
                            <th>Joueur Signalé</th>
                            <th>Jeu</th>
                            <th>État</th>
                            <th>Durée Ban</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($closedReports as $report): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($report['rid'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars(substr($report['description'] ?? 'N/A', 0, 50)) . (strlen($report['description'] ?? '') > 50 ? '...' : ''); ?></td>
                                <td><?php echo htmlspecialchars($report['reporterID'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($report['nomj'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($report['gid'] ?? 'N/A'); ?></td>
                                <td>
                                    <span style="background: #c41e3a; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">Fermé</span>
                                </td>
                                <td>
                                    <?php if ($report['duration'] > 0): ?>
                                        <strong><?php echo htmlspecialchars($report['duration']); ?> jour(s)</strong>
                                    <?php elseif ($report['pid'] !== NULL): ?>
                                        <span style="color: #999;">Permanent</span>
                                    <?php else: ?>
                                        <span style="color: #999;">Pas de ban</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="editReport.php?rid=<?php echo htmlspecialchars($report['rid']); ?>" class="btn btn.secondary" style="font-size: 12px; padding: 6px 10px;">Voir Détails</a>
                                        <?php if ($report['pid'] !== NULL): ?>
                                            <a href="unban.php?rid=<?php echo htmlspecialchars($report['rid']); ?>" class="btn" style="background: #17a2b8; color: white; font-size: 12px; padding: 6px 10px;">Libérer</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="tableWrap">
                <div class="empty">Aucun rapport fermé.</div>
            </div>
        <?php endif; ?>

        <a href="../../index.php" class="btn secondary" style="margin-top: 25px; display: inline-block;">Retour</a>
    </div>

    <footer>
        <p>&copy; 2025 PerFran Report Management System. All rights reserved.</p>
    </footer>
</body>
</html>
