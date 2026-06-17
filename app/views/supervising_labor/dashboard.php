<?php ob_start(); ?>

<!-- Notifications Banner -->
<?php if (!empty($notifications)): ?>
<div class="mb-4">
    <?php foreach ($notifications as $notif): ?>
    <?php
    $iconMap = [
        'agency_accepted'     => ['icon' => 'bi-check-circle-fill',    'color' => 'success'],
        'agency_declined'     => ['icon' => 'bi-x-circle-fill',        'color' => 'danger'],
        'new_vacancy'         => ['icon' => 'bi-briefcase-fill',        'color' => 'primary'],
        'resources_confirmed' => ['icon' => 'bi-box-seam-fill',        'color' => 'info'],
        'default'             => ['icon' => 'bi-bell-fill',             'color' => 'secondary'],
    ];
    $nc = $iconMap[$notif['type']] ?? $iconMap['default'];
    ?>
    <div class="alert alert-<?= $nc['color'] ?> alert-dismissible fade show d-flex align-items-start gap-3 mb-2">
        <i class="bi <?= $nc['icon'] ?> fs-5 flex-shrink-0 mt-1"></i>
        <div class="flex-grow-1">
            <div class="fw-bold"><?= htmlspecialchars($notif['title'], ENT_QUOTES, 'UTF-8') ?></div>
            <div class="small"><?= htmlspecialchars($notif['message'], ENT_QUOTES, 'UTF-8') ?></div>
            <div class="text-muted small mt-1"><?= formatDate($notif['created_at'], 'M d, Y H:i') ?></div>
        </div>
        <?php if ($notif['link']): ?>
        <a href="<?= htmlspecialchars($notif['link'], ENT_QUOTES, 'UTF-8') ?>"
           class="btn btn-sm btn-outline-<?= $nc['color'] ?> flex-shrink-0">
            View <i class="bi bi-arrow-right ms-1"></i>
        </a>
        <?php endif; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="stat-value"><?= $stats['pending_requests'] ?></div>
                    <div class="stat-label">Pending Requests</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-calendar-check"></i></div>
                <div>
                    <div class="stat-value"><?= $stats['approved_fairs'] ?></div>
                    <div class="stat-label">Approved Fairs</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-building"></i></div>
                <div>
                    <div class="stat-value"><?= $stats['total_agencies'] ?></div>
                    <div class="stat-label">Total Agencies</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-person-badge"></i></div>
                <div>
                    <div class="stat-value"><?= $stats['total_applicants'] ?></div>
                    <div class="stat-label">Total Applicants</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-briefcase me-2 text-primary"></i>Agency Vacancies for Review</h6>
        <a href="<?= APP_URL ?>/supervising-labor/vacancies/review" class="btn btn-sm btn-outline-primary">Review All</a>
    </div>
    <div class="card-body text-center py-4">
        <i class="bi bi-briefcase-fill display-5 text-info mb-3 d-block"></i>
        <p class="text-muted mb-3">Review vacancies posted by agencies and add remarks.</p>
        <a href="<?= APP_URL ?>/supervising-labor/vacancies/review" class="btn btn-info px-4">
            <i class="bi bi-eye me-2"></i>Review Vacancies
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-clipboard-check me-2 text-primary"></i>Recent Job Fair Requests</h6>
        <a href="<?= APP_URL ?>/supervising-labor/requests" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($recentRequests)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox display-4 d-block mb-2"></i>No requests yet.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Requested By</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentRequests as $r): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($r['title'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-muted"><?= htmlspecialchars($r['requested_by_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-muted"><?= formatDate($r['requested_date']) ?></td>
                                <td><?= statusBadge($r['status']) ?></td>
                                <td>
                                    <?php if ($r['status'] === 'pending'): ?>
                                        <a href="<?= APP_URL ?>/supervising-labor/requests/<?= $r['id'] ?>/validate"
                                           class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye me-1"></i>Review
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= APP_URL ?>/supervising-labor/registration-form/<?= $r['id'] ?>"
                                       class="btn btn-sm btn-outline-secondary ms-1">
                                        <i class="bi bi-file-earmark-text me-1"></i>Process 7
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
