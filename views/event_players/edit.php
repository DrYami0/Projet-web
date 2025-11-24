<?php
ob_start();
?>
<div class="card border">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h4 class="card-header-title mb-0">Edit Player Score</h4>
        <a href="index.php?controller=eventPlayers&action=index&eid=<?= $player->getEid() ?>" class="btn btn-sm btn-outline-secondary">Back</a>
    </div>
    <div class="card-body">
        <form method="post" class="row g-3">
            <div class="col-md-6">
                <label class="form-label">User ID (uid)</label>
                <input type="number" class="form-control" value="<?= htmlspecialchars((string)$player->getUid(), ENT_QUOTES, 'UTF-8') ?>" disabled>
            </div>
            <div class="col-md-6">
                <label class="form-label">Score</label>
                <input type="number" name="score" class="form-control" value="<?= htmlspecialchars((string)$player->getScore(), ENT_QUOTES, 'UTF-8') ?>">
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
