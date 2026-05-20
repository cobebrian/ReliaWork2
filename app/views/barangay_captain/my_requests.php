<?php ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    <a href="<?= APP_URL ?>/barangay-captain/create-request" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>New Request
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold"><i class="bi bi-list-check me-2 text-primary"></i>My Job Fair Requests</h6>
    </div>
    <div class="card-body p-0">
        <?php if (empty($requests)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox display-4 d-block mb-2"></i>
                You haven't submitted any requests yet.
                <br>
                <a href="<?= APP_URL ?>/barangay-captain/create-request" class="btn btn-primary mt-3">
                    <i class="bi bi-plus-circle me-2"></i>Create First Request
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Requested Date</th>
                            <th>Venue</th>
                            <th>Status</th>
                            <th>Remarks</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $r): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($r['title'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi bi-calendar me-1"></i><?= formatDate($r['requested_date']) ?>
                                    </span>
                                </td>
                                <td class="text-muted small"><?= htmlspecialchars($r['venue'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= statusBadge($r['status']) ?></td>
                                <td class="text-muted small">
                                    <?php if ($r['remarks']): ?>
                                        <span class="text-<?= $r['status'] === 'rejected' ? 'danger' : 'muted' ?>">
                                            <?= htmlspecialchars($r['remarks'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small"><?= formatDate($r['created_at']) ?></td>
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
