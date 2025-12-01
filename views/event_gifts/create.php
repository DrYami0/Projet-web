<?php
ob_start();
?>
<div class="card border">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h4 class="card-header-title mb-0">Add Gift to Event</h4>
        <a href="index.php?controller=eventGifts&action=index&eid=<?= $eid ?>" class="btn btn-sm btn-outline-secondary">Back</a>
    </div>
    <div class="card-body">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <form method="post" class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Gift</label>
                <select name="gid" class="form-select" required>
                    <option value="">-- Select Gift --</option>
                    <?php foreach ($gifts as $gift): ?>
                        <option value="<?= (int)$gift['id'] ?>" <?= ($gid == $gift['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($gift['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
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
