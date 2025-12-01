<?php
ob_start();
?>
<div class="card border">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h4 class="card-header-title mb-0">Create Gift</h4>
        <a href="index.php?controller=gifts&action=index" class="btn btn-sm btn-outline-secondary">Back</a>
    </div>
    <div class="card-body">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <form method="post" class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Points (int)</label>
                <input type="number" name="points" class="form-control" value="<?= htmlspecialchars($points, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
