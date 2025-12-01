<?php
ob_start();
?>
<div class="card border">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h4 class="card-header-title mb-0">Add Player</h4>
        <a href="index.php?controller=eventPlayers&action=index&eid=<?= (int)$event['eid'] ?>" class="btn btn-sm btn-outline-secondary">Back</a>
    </div>
    <div class="card-body">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <form method="post" class="row g-3">
            <div class="col-md-6">
                <label class="form-label">User</label>
                <select name="uid" class="form-select" required>
                    <option value="">Select a user</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= (int)$user['uid'] ?>" <?= ($uid !== '' && (int)$uid === (int)$user['uid']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?> (ID: <?= (int)$user['uid'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Score</label>
                <input type="number" name="score" class="form-control" value="<?= htmlspecialchars($score, ENT_QUOTES, 'UTF-8') ?>">
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
