<?php
ob_start();
?>
<div class="card border">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h4 class="card-header-title mb-0">Gifts</h4>
        <a href="index.php?controller=gifts&action=create" class="btn btn-sm btn-primary">Create Gift</a>
    </div>
    <div class="card-body">
        <form class="row g-2 mb-3" method="get" action="index.php">
            <input type="hidden" name="controller" value="gifts">
            <input type="hidden" name="action" value="index">
            <div class="col-md-5">
                <label class="form-label mb-1">Search by name</label>
                <input type="text" name="q" class="form-control" value="<?= htmlspecialchars($searchTerm ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Enter part of the gift name">
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1">Minimum points</label>
                <input type="number" name="min_points" class="form-control" value="<?= htmlspecialchars($minPoints ?? '', ENT_QUOTES, 'UTF-8') ?>" min="0">
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="index.php?controller=gifts&action=index" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
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
