<?php ob_start(); ?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-clipboard-check me-2 text-primary"></i>Approved Job Fair Requests</h6>
        <a href="<?= APP_URL ?>/secretary/dashboard" class="btn btn-sm btn-outline-secondary">Back</a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($requests)): ?>
            <div class="text-center py-4 text-muted">
                <i class="bi bi-inbox display-4 d-block mb-2"></i>No approved requests.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Requested By</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $r): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($r['title'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-muted"><?= htmlspecialchars($r['requested_by_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-muted"><?= formatDate($r['requested_date']) ?></td>
                                <td>
                                    <a href="<?= APP_URL ?>/secretary/requests/<?= $r['id'] ?>/confirm" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil-square me-1"></i>Confirm Details
                                    </a>
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
include VIEW_PATH . '/layouts/main.php';
