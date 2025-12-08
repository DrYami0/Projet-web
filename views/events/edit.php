<?php
ob_start();
?>
<div class="card border">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h4 class="card-header-title mb-0">Edit Event</h4>
        <a href="index.php?controller=events&action=index" class="btn btn-sm btn-outline-secondary">Back</a>
    </div>
    <div class="card-body">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <form method="post" class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($event->getTitle(), ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">End Date (YYYY-MM-DD HH:MM:SS)</label>
                <input type="text" name="endDate" class="form-control" value="<?= htmlspecialchars($event->getEndDate(), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Gift1 (int)</label>
                <input type="number" name="gift1" class="form-control" value="<?= htmlspecialchars((string)$event->getGift1(), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Gift2</label>
                <input type="text" name="gift2" class="form-control" value="<?= htmlspecialchars($event->getGift2(), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Gift3</label>
                <input type="text" name="gift3" class="form-control" value="<?= htmlspecialchars($event->getGift3(), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
