<?php $pageTitle = $pageTitle ?? 'Submitted Job Fair Reports'; ?>
<?php ob_start(); ?>

<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show">
    <?= htmlspecialchars($success, ENT_QUOTES) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Search job fair title..."
                       value="<?= htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES) ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="submitted" <?= ($_GET['status'] ?? '') === 'submitted' ? 'selected' : '' ?>>Submitted</option>
                    <option value="reviewed"  <?= ($_GET['status'] ?? '') === 'reviewed'  ? 'selected' : '' ?>>Reviewed</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i>Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-file-earmark-bar-graph me-2 text-primary"></i>Submitted Reports (<?= count($reports) ?>)
        </h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Job Fair</th>
                    <th>Fair Date</th>
                    <th>Reporting Officer</th>
                    <th>Submitted</th>
                    <th class="text-center">Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($reports)): ?>
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox display-4 d-block mb-2 opacity-50"></i>
                        No submitted reports yet.
                    </td>
                </tr>
            <?php else: ?>
            <?php foreach ($reports as $r):
                $sColors = ['submitted'=>'primary','reviewed'=>'success'];
                $sColor  = $sColors[$r['report_status']] ?? 'secondary';
            ?>
            <tr>
                <td class="fw-semibold small"><?= htmlspecialchars($r['fair_title'], ENT_QUOTES) ?></td>
                <td class="small text-muted">
                    <?= !empty($r['requested_date']) ? date('M d, Y', strtotime($r['requested_date'])) : '—' ?>
                </td>
                <td class="small"><?= htmlspecialchars($r['generated_by_name'] ?? '—', ENT_QUOTES) ?></td>
                <td class="small text-muted">
                    <?= !empty($r['submitted_at']) ? date('M d, Y', strtotime($r['submitted_at'])) : '—' ?>
                </td>
                <td class="text-center">
                    <span class="badge bg-<?= $sColor ?>"><?= ucfirst($r['report_status']) ?></span>
                </td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="<?= APP_URL ?>/supervising-labor/reports/<?= $r['id'] ?>"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye me-1"></i>View
                        </a>
                        <?php if ($r['report_status'] === 'submitted'): ?>
                        <button class="btn btn-sm btn-success"
                                data-bs-toggle="modal" data-bs-target="#reviewModal"
                                data-reportid="<?= $r['id'] ?>"
                                data-title="<?= htmlspecialchars($r['fair_title'], ENT_QUOTES) ?>">
                            <i class="bi bi-check-circle me-1"></i>Mark Reviewed
                        </button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Mark Report as Reviewed</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="reviewForm" method="POST" action="">
                <?= csrfField() ?>
                <div class="modal-body">
                    <p class="small text-muted mb-2">Report: <strong id="reviewTitle"></strong></p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Reviewer Remarks (optional)</label>
                        <textarea name="reviewer_remarks" class="form-control form-control-sm" rows="3"
                                  placeholder="Observations, feedback, or acknowledgment..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="bi bi-check-circle me-1"></i>Mark as Reviewed
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('reviewModal').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('reviewTitle').textContent = btn.dataset.title;
    document.getElementById('reviewForm').action =
        '<?= APP_URL ?>/supervising-labor/reports/' + btn.dataset.reportid + '/mark-reviewed';
});
</script>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
