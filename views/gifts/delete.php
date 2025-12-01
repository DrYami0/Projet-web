<?php
ob_start();
?>
<div class="card border">
    <div class="card-header border-bottom">
        <h4 class="card-header-title mb-0">Delete Gift</h4>
    </div>
    <div class="card-body">
        <p>Are you sure you want to delete the gift <strong><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></strong>?</p>
        <form method="post">
            <a href="index.php?controller=gifts&action=index" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-danger">Delete</button>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
