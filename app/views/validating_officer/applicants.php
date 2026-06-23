<?php $pageTitle = $pageTitle ?? 'Applicant List'; ?>
<?php ob_start(); ?>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name or email..."
                       value="<?= htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES) ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="all"      <?= ($_GET['status'] ?? '') === 'all'      ? 'selected' : '' ?>>All Statuses</option>
                    <option value="pending"  <?= ($_GET['status'] ?? 'pending') === 'pending'  ? 'selected' : '' ?>>Pending</option>
                    <option value="approved" <?= ($_GET['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="rejected" <?= ($_GET['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    <option value="resubmit" <?= ($_GET['status'] ?? '') === 'resubmit' ? 'selected' : '' ?>>Resubmit</option>
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
        <h6 class="mb-0 fw-bold"><i class="bi bi-people me-2 text-primary"></i>Applicants (<?= count($applicants) ?>)</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Name</th><th>Email</th><th>Docs</th><th>Status</th><th>Updated</th><th>Action</th></tr>
            </thead>
            <tbody>
            <?php if (empty($applicants)): ?>
                <tr><td colspan="6" class="text-center py-5 text-muted">No applicants found.</td></tr>
            <?php else: ?>
            <?php foreach ($applicants as $a): ?>
            <?php
            $vsColors = ['not_submitted'=>'secondary','pending'=>'warning','approved'=>'success','rejected'=>'danger','resubmit'=>'info'];
            $vsColor = $vsColors[$a['validation_status']] ?? 'secondary';
            ?>
            <tr>
                <td class="fw-semibold"><?= htmlspecialchars(strtoupper($a['surname']) . ', ' . $a['firstname'], ENT_QUOTES) ?></td>
                <td class="text-muted small"><?= htmlspecialchars($a['email'] ?? '—', ENT_QUOTES) ?></td>
                <td><span class="badge bg-primary"><?= $a['doc_count'] ?> file(s)</span></td>
                <td><span class="badge bg-<?= $vsColor ?>"><?= ucfirst($a['validation_status']) ?></span></td>
                <td class="text-muted small"><?= !empty($a['updated_at']) ? date('M d, Y', strtotime($a['updated_at'])) : '—' ?></td>
                <td>
                    <a href="<?= APP_URL ?>/validating-officer/applicants/<?= $a['id'] ?>/review"
                       class="btn btn-sm btn-outline-primary">
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
