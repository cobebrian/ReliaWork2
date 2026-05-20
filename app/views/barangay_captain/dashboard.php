<?php ob_start(); ?>
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-list-check"></i></div>
                <div>
                    <div class="stat-value"><?= $stats['total'] ?></div>
                    <div class="stat-label">Total Requests</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="stat-value"><?= $stats['pending'] ?></div>
                    <div class="stat-label">Pending</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-check-circle"></i></div>
                <div>
                    <div class="stat-value"><?= $stats['approved'] ?></div>
                    <div class="stat-label">Approved</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-danger-subtle text-danger"><i class="bi bi-x-circle"></i></div>
                <div>
                    <div class="stat-value"><?= $stats['rejected'] ?></div>
                    <div class="stat-label">Rejected</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-5">
                <i class="bi bi-plus-circle display-3 text-primary mb-3 d-block"></i>
                <h5 class="fw-bold">Create Job Fair Request</h5>
                <p class="text-muted">Submit a new job fair request for your barangay.</p>
                <a href="<?= APP_URL ?>/barangay-captain/create-request" class="btn btn-primary px-4">
                    <i class="bi bi-plus-circle me-2"></i>New Request
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i>Recent Requests</h6>
                <a href="<?= APP_URL ?>/barangay-captain/my-requests" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentRequests)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox d-block display-4 mb-2"></i>No requests yet.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Title</th><th>Date</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentRequests as $r): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= htmlspecialchars($r['title'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="text-muted"><?= formatDate($r['requested_date']) ?></td>
                                        <td><?= statusBadge($r['status']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
