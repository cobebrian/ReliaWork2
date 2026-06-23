<?php $pageTitle = $pageTitle ?? 'Validating Officer Dashboard'; ?>
<?php ob_start(); ?>

<div class="row g-4 mb-4">
    <?php foreach ([
        ['pending',  'bi-hourglass-split', 'warning', 'Pending Review',   $stats['pending']],
        ['approved', 'bi-check-circle',    'success', 'Approved',          $stats['approved']],
        ['rejected', 'bi-x-circle',        'danger',  'Rejected/Resubmit', $stats['rejected']],
    ] as [$status, $icon, $color, $label, $count]): ?>
    <div class="col-sm-4">
        <a href="<?= APP_URL ?>/validating-officer/applicants?status=<?= $status ?>" class="text-decoration-none">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-<?= $color ?>-subtle text-<?= $color ?>">
                        <i class="bi <?= $icon ?>"></i>
                    </div>
                    <div>
                        <div class="stat-value"><?= $count ?></div>
                        <div class="stat-label"><?= $label ?></div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-hourglass-split text-warning me-2"></i>Pending Validation</h6>
        <a href="<?= APP_URL ?>/validating-officer/applicants" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($pendingApplicants)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-check-circle-fill text-success display-4 d-block mb-2"></i>
                No pending applicants.
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Applicant</th>
                        <th>Email</th>
                        <th>Documents</th>
                        <th>Submitted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($pendingApplicants as $a): ?>
                <tr>
                    <td class="fw-semibold">
                        <?= htmlspecialchars(strtoupper($a['surname']) . ', ' . $a['firstname'], ENT_QUOTES) ?>
                    </td>
                    <td class="text-muted small"><?= htmlspecialchars($a['email'] ?? '—', ENT_QUOTES) ?></td>
                    <td><span class="badge bg-primary"><?= $a['doc_count'] ?> file(s)</span></td>
                    <td class="text-muted small"><?= !empty($a['updated_at']) ? date('M d, Y', strtotime($a['updated_at'])) : '—' ?></td>
                    <td>
                        <a href="<?= APP_URL ?>/validating-officer/applicants/<?= $a['id'] ?>/review"
                           class="btn btn-sm btn-primary">
                            <i class="bi bi-eye me-1"></i>Review
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

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
