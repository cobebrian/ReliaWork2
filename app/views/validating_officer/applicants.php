<?php $pageTitle = $pageTitle ?? 'Application Review List'; ?>
<?php ob_start(); ?>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Search name, position, or company..."
                       value="<?= htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES) ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="all"               <?= ($_GET['status'] ?? '') === 'all'               ? 'selected' : '' ?>>All Statuses</option>
                    <option value="pending_validation" <?= ($_GET['status'] ?? 'pending_validation') === 'pending_validation' ? 'selected' : '' ?>>Pending</option>
                    <option value="approved"           <?= ($_GET['status'] ?? '') === 'approved'          ? 'selected' : '' ?>>Approved</option>
                    <option value="rejected"           <?= ($_GET['status'] ?? '') === 'rejected'          ? 'selected' : '' ?>>Rejected</option>
                    <option value="resubmit"           <?= ($_GET['status'] ?? '') === 'resubmit'          ? 'selected' : '' ?>>Resubmit</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-people me-2 text-primary"></i>Applications (<?= count($applicants) ?>)
        </h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Applicant</th>
                    <th>Position / Company</th>
                    <th>Job Fair</th>
                    <th class="text-center">Docs</th>
                    <th class="text-center">Status</th>
                    <th>Submitted</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($applicants)): ?>
                <tr><td colspan="7" class="text-center py-5 text-muted">No applications found.</td></tr>
            <?php else: ?>
            <?php foreach ($applicants as $a):
                $vsColors = [
                    'pending_validation'=>'warning text-dark',
                    'approved'          =>'success',
                    'rejected'          =>'danger',
                    'resubmit'          =>'info text-dark',
                ];
                $vsColor = $vsColors[$a['validation_status']] ?? 'secondary';
            ?>
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
                <td class="text-center">
                    <span class="badge bg-<?= $vsColor ?>">
                        <?= ucfirst(str_replace('_', ' ', $a['validation_status'])) ?>
                    </span>
                </td>
                <td class="text-muted small"><?= date('M d, Y', strtotime($a['applied_at'])) ?></td>
                <td>
                    <a href="<?= APP_URL ?>/validating-officer/applications/<?= $a['id'] ?>/review"
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
