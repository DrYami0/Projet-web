<?php
ob_start();
?>
<div class="card border">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h4 class="card-header-title mb-0">Players for event: <?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?></h4>
        <div>
            <a href="index.php?controller=events&action=index" class="btn btn-sm btn-outline-secondary me-2">Back to Events</a>
            <a href="index.php?controller=eventPlayers&action=create&eid=<?= (int)$event['eid'] ?>" class="btn btn-sm btn-primary">Add Player</a>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($players)): ?>
            <p class="mb-0">No players for this event.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>EPID</th>
                            <th>User</th>
                            <th>Score</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($players as $item): ?>
                        <?php $player = $item['model']; $username = $item['username']; ?>
                        <tr>
                            <td><?= htmlspecialchars((string)$player->getEpid(), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php if ($username !== null): ?>
                                    <?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?> (UID: <?= htmlspecialchars((string)$player->getUid(), ENT_QUOTES, 'UTF-8') ?>)
                                <?php else: ?>
                                    UID: <?= htmlspecialchars((string)$player->getUid(), ENT_QUOTES, 'UTF-8') ?>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars((string)$player->getScore(), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <a href="index.php?controller=eventPlayers&action=edit&id=<?= $player->getEpid() ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                <a href="index.php?controller=eventPlayers&action=delete&id=<?= $player->getEpid() ?>" class="btn btn-sm btn-outline-danger">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
