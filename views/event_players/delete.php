<?php
ob_start();
?>
<div class="card border">
    <div class="card-header border-bottom">
        <h4 class="card-header-title mb-0">Delete Player</h4>
    </div>
    <div class="card-body">
        <p>Are you sure you want to remove this player (UID: <?= htmlspecialchars((string)$uid, ENT_QUOTES, 'UTF-8') ?>) from the event?</p>
        <form method="post">
            <a href="index.php?controller=eventPlayers&action=index&eid=<?= (int)$eid ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-danger">Delete</button>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
