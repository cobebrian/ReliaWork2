<?php $pageTitle = $pageTitle ?? 'Validating Officer Dashboard'; ?>
<?php ob_start(); ?>

<div class="row g-4 mb-4">
    <?php foreach ([
        ['pending_validation', 'bi-hourglass-split', 'warning', 'Pending Review',    $stats['pending']],
        ['approved',           'bi-check-circle',    'success', 'Approved',           $stats['approved']],
        ['rejected',           'bi-x-circle',        'danger',  'Rejected/Resubmit',  $stats['rejected']],
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
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-hourglass-split text-warning me-2"></i>Pending Applications
        </h6>
        <a href="<?= APP_URL ?>/validating-officer/applicants" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($pendingApps)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-check-circle-fill text-success display-4 d-block mb-2"></i>
                No pending applications.
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Applicant</th>
                        <th>Position / Company</th>
                        <th>Job Fair</th>
                        <th class="text-center">Docs</th>
                        <th>Submitted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($pendingApps as $a): ?>
                <tr>
                    <td>
                        <div class="fw-semibold small">
                            <?= htmlspecialchars(strtoupper($a['surname']) . ', ' . $a['firstname'], ENT_QUOTES) ?>
                        </div>
                        <div class="text-muted" style="font-size:.72rem;"><?= htmlspecialchars($a['email'] ?? '', ENT_QUOTES) ?></div>
                    </td>
                    <td class="small">
                        <div class="fw-semibold"><?= htmlspecialchars($a['position'], ENT_QUOTES) ?></div>
                        <div class="text-muted"><?= htmlspecialchars($a['agency_name'], ENT_QUOTES) ?></div>
                    </td>
                    <td class="small text-muted"><?= htmlspecialchars($a['fair_title'] ?? '—', ENT_QUOTES) ?></td>
                    <td class="text-center"><span class="badge bg-primary"><?= $a['doc_count'] ?></span></td>
                    <td class="text-muted small"><?= date('M d, Y', strtotime($a['applied_at'])) ?></td>
                    <td>
                        <a href="<?= APP_URL ?>/validating-officer/applications/<?= $a['id'] ?>/review"
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
