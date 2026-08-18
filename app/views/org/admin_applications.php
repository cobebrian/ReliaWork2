<?php $pageTitle = $pageTitle ?? 'Organization Applications'; ?>
<?php ob_start(); ?>

<?php if ($success): ?><div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($success, ENT_QUOTES) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES) ?></div><?php endif; ?>

<!-- Filter Tabs -->
<div class="d-flex gap-2 mb-4 flex-wrap">
    <?php foreach (['all'=>'All','pending'=>'Pending','under_review'=>'Under Review','approved'=>'Approved','rejected'=>'Rejected','needs_revision'=>'Needs Revision'] as $s => $l): ?>
    <a href="<?= APP_URL ?>/admin/org-applications?status=<?= $s ?>"
       class="btn btn-sm <?= ($_GET['status'] ?? 'pending') === $s ? 'btn-primary' : 'btn-outline-secondary' ?>">
        <?= $l ?>
    </a>
    <?php endforeach; ?>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-building me-2 text-primary"></i>Organization Applications (<?= count($applications) ?>)
        </h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Applicant</th>
                    <th>Organization Name</th>
                    <th>Barangay</th>
                    <th class="text-center">Status</th>
                    <th>Submitted</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($applications)): ?>
                <tr><td colspan="6" class="text-center py-5 text-muted">No applications found.</td></tr>
            <?php else: ?>
            <?php foreach ($applications as $a):
                $sColors = ['pending'=>'warning text-dark','under_review'=>'primary','approved'=>'success','rejected'=>'danger','needs_revision'=>'info text-dark'];
                $sColor  = $sColors[$a['status']] ?? 'secondary';
            ?>
            <tr>
                <td>
                    <div class="fw-semibold small"><?= htmlspecialchars($a['applicant_name'], ENT_QUOTES) ?></div>
                    <div class="text-muted" style="font-size:.72rem;"><?= htmlspecialchars($a['applicant_email'], ENT_QUOTES) ?></div>
                </td>
                <td class="fw-semibold small"><?= htmlspecialchars($a['org_name'], ENT_QUOTES) ?></td>
                <td class="small text-muted"><?= htmlspecialchars($a['barangay'], ENT_QUOTES) ?></td>
                <td class="text-center">
                    <span class="badge bg-<?= $sColor ?>"><?= ucfirst(str_replace('_',' ',$a['status'])) ?></span>
                </td>
                <td class="small text-muted"><?= date('M d, Y', strtotime($a['submitted_at'])) ?></td>
                <td>
                    <a href="<?= APP_URL ?>/admin/org-applications/<?= $a['id'] ?>/review"
                       class="btn btn-sm btn-primary">
                        <i class="bi bi-eye me-1"></i>Review
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
