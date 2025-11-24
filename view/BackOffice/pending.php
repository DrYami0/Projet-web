<?php
if (!isset($pendings)) $pendings = [];
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Utilisateurs en attente - BackOffice</title>
    <link rel="stylesheet" href="<?=htmlspecialchars(BASE_URL)?>assets/css/bootstrap.min.css">
    <style>
        body { padding: 20px; }
        .btn-sm { padding: 3px 8px; font-size: 0.85rem; }
    </style>
</head>
<body>
<div class="container">
    <h1>Utilisateurs en attente</h1>

    <?php if (!empty($_SESSION['message'])): ?>
        <div class="alert alert-success"><?=htmlspecialchars($_SESSION['message'])?></div>
        <?php unset($_SESSION['message']); endif; ?>

    <?php if (empty($pendings)): ?>
        <p>Aucun utilisateur en attente.</p>
    <?php else: ?>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom d'utilisateur</th>
                    <th>Nom complet</th>
                    <th>Email</th>
                    <th>Date de création</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendings as $u): ?>
                <tr>
                    <td><?=htmlspecialchars($u['id'])?></td>
                    <td><?=htmlspecialchars($u['username'])?></td>
                    <td><?=htmlspecialchars($u['prenom'] . ' ' . $u['nom'])?></td>
                    <td><?=htmlspecialchars($u['email'])?></td>
                    <td><?=htmlspecialchars($u['created_at'])?></td>
                    <td>
                        <a href="<?=BASE_URL?>controller/admin.php?action=approve&token=<?=htmlspecialchars($u['token'])?>" 
                           class="btn btn-success btn-sm">Approuver</a>
                        <a href="<?=BASE_URL?>controller/admin.php?action=reject&token=<?=htmlspecialchars($u['token'])?>" 
                           class="btn btn-danger btn-sm">Refuser</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
