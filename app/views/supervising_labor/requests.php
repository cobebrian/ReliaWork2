<?php ob_start(); ?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold"><i class="bi bi-clipboard-check me-2 text-primary"></i>Job Fair Requests</h6>
    </div>
    <div class="card-body p-0">
        <?php if (empty($requests)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox display-4 d-block mb-2"></i>No requests found.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Requested By</th>
                            <th>Requested Date</th>
                            <th>Venue</th>
                            <th>Status</th>
                            <th>Remarks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $r): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($r['title'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-muted"><?= htmlspecialchars($r['requested_by_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= formatDate($r['requested_date']) ?></td>
                                <td class="text-muted small"><?= htmlspecialchars($r['venue'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= statusBadge($r['status']) ?></td>
                                <td class="text-muted small"><?= htmlspecialchars($r['remarks'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?php if ($r['status'] === 'pending'): ?>
                                        <a href="<?= APP_URL ?>/supervising-labor/requests/<?= $r['id'] ?>/validate"
                                           class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye me-1"></i>Review
                                        </a>
                                    <?php elseif ($r['status'] === 'approved'): ?>
                                        <a href="<?= APP_URL ?>/supervising-labor/registration-form/<?= $r['id'] ?>"
                                           class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-printer me-1"></i>Form
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
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
