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
        <form method="post" class="row g-3" id="gift-form-create">
            <div class="col-md-8">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Points (int)</label>
                <input type="number" name="points" class="form-control" value="<?= htmlspecialchars($points, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-12">
                <label class="form-label">Description (AI generated or manual)</label>
                <textarea name="description" class="form-control" rows="3" id="gift-description-create"></textarea>
            </div>
            <div class="col-12 d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-outline-secondary" id="btn-generate-description-create">Generate with Gemini</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
        <script>
        (function () {
            var btn = document.getElementById('btn-generate-description-create');
            if (!btn) return;
            btn.addEventListener('click', function () {
                var form = document.getElementById('gift-form-create');
                if (!form) return;
                var nameInput = form.querySelector('input[name="name"]');
                var pointsInput = form.querySelector('input[name="points"]');
                var descArea = document.getElementById('gift-description-create');
                if (!nameInput || !pointsInput || !descArea) return;

                var name = nameInput.value.trim();
                var points = pointsInput.value.trim();
                if (!name || !points) {
                    return;
                }

                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'index.php?controller=gifts&action=generateDescription');
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            try {
                                var data = JSON.parse(xhr.responseText);
                                if (data.description) {
                                    descArea.value = data.description;
                                }
                            } catch (e) {
                            }
                        }
                    }
                };
                var body = 'name=' + encodeURIComponent(name) + '&points=' + encodeURIComponent(points);
                xhr.send(body);
            });
        })();
        </script>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
