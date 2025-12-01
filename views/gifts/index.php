<?php
ob_start();
?>
<div class="card border">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h4 class="card-header-title mb-0">Gifts</h4>
        <a href="index.php?controller=gifts&action=create" class="btn btn-sm btn-primary">Create Gift</a>
    </div>
    <div class="card-body">
        <?php if (empty($gifts)): ?>
            <p class="mb-0">No gifts found.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Points</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($gifts as $gift): ?>
                        <tr>
                            <td><?= htmlspecialchars((string)$gift->getId(), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($gift->getName(), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)$gift->getPoints(), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="d-flex gap-1">
                                <a href="index.php?controller=gifts&action=edit&id=<?= $gift->getId() ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                <a href="index.php?controller=gifts&action=delete&id=<?= $gift->getId() ?>" class="btn btn-sm btn-outline-danger">Delete</a>
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
