<?php
ob_start();
?>
<div class="card border">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h4 class="card-header-title mb-0">Events</h4>
        <a href="index.php?controller=events&action=create" class="btn btn-sm btn-primary">Create Event</a>
    </div>
    <div class="card-body">
        <form class="row g-2 mb-3" method="get" action="index.php">
            <input type="hidden" name="controller" value="events">
            <input type="hidden" name="action" value="index">
            <div class="col-md-6">
                <label class="form-label mb-1">Search by title</label>
                <input type="text" name="q" class="form-control" value="<?= htmlspecialchars($searchTerm ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Enter part of the event title">
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1">To date (end date)</label>
                <input type="date" name="to_date" class="form-control" value="<?= htmlspecialchars($toDate ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="index.php?controller=events&action=index" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
        <?php if (empty($events)): ?>
            <p class="mb-0">No events found.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>End Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?= htmlspecialchars((string)$event->getEid(), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($event->getTitle(), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($event->getEndDate(), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="d-flex gap-1">
                                <a href="index.php?controller=events&action=edit&id=<?= $event->getEid() ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                <a href="index.php?controller=events&action=delete&id=<?= $event->getEid() ?>" class="btn btn-sm btn-outline-danger">Delete</a>
                                <a href="index.php?controller=eventPlayers&action=index&eid=<?= $event->getEid() ?>" class="btn btn-sm btn-outline-secondary">Players</a>
                                <a href="index.php?controller=eventGifts&action=index&eid=<?= $event->getEid() ?>" class="btn btn-sm btn-outline-success">Gifts</a>
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
