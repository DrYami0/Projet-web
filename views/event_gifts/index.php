<?php
ob_start();
?>
<div class="card border">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h4 class="card-header-title mb-0">Event Gifts</h4>
        <a href="index.php?controller=events&action=index" class="btn btn-sm btn-outline-secondary">Back to Events</a>
    </div>
    <div class="card-body">
        <p class="mb-3">Event ID: <?= htmlspecialchars((string)$eid, ENT_QUOTES, 'UTF-8') ?></p>
        <a href="index.php?controller=eventGifts&action=create&eid=<?= $eid ?>" class="btn btn-sm btn-primary mb-3">Add Gift to Event</a>
        <?php if (empty($eventGifts)): ?>
            <p class="mb-0">No gifts linked to this event.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Gift Name</th>
                            <th>Points</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($eventGifts as $eg): ?>
                        <tr>
                            <td><?= htmlspecialchars((string)$eg['model']->getEgid(), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($eg['giftName'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars(isset($eg['giftPoints']) ? (string)$eg['giftPoints'] : '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <a href="index.php?controller=eventGifts&action=delete&id=<?= $eg['model']->getEgid() ?>" class="btn btn-sm btn-outline-danger">Remove</a>
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
