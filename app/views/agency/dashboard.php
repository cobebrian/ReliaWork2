<?php ob_start();
$userId = currentUser()['id'] ?? 0;
?>

<?php if (!empty($success = getFlash('success'))): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Unread notifications banner -->
<?php if (!empty($notifications)): ?>
<div class="alert alert-info alert-dismissible fade show d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-bell-fill fs-5"></i>
    <div>
        <strong><?= count($notifications) ?> new notification(s):</strong>
        <?= htmlspecialchars($notifications[0]['title'], ENT_QUOTES, 'UTF-8') ?>
        <?php if (count($notifications) > 1): ?> <em>and <?= count($notifications) - 1 ?> more...</em><?php endif; ?>
    </div>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-envelope-fill"></i></div>
                <div><div class="stat-value"><?= $stats['total_invitations'] ?></div><div class="stat-label">Total Invitations</div></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-hourglass-split"></i></div>
                <div><div class="stat-value"><?= $stats['pending'] ?></div><div class="stat-label">Pending</div></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-check-circle-fill"></i></div>
                <div><div class="stat-value"><?= $stats['confirmed'] ?></div><div class="stat-label">Confirmed</div></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-briefcase-fill"></i></div>
                <div><div class="stat-value"><?= $stats['vacancy_count'] ?></div><div class="stat-label">Vacancies Posted</div></div>
            </div>
        </div>
    </div>
</div>

<!-- Invitations Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-inbox-fill me-2 text-primary"></i>My Invitations
        </h6>
        <?php if ($stats['confirmed'] > 0): ?>
        <a href="<?= APP_URL ?>/agency/vacancies" class="btn btn-sm btn-primary">
            <i class="bi bi-briefcase me-1"></i>Post Vacancies
        </a>
        <?php endif; ?>
    </div>
    <div class="card-body p-0">
        <?php if (empty($myInvitations)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox display-4 d-block mb-2 opacity-50"></i>
            No invitations yet. You will be notified when the Supervising Labor invites your agency.
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Job Fair</th>
                        <th>Date</th>
                        <th>Invited</th>
                        <th>Status</th>
                        <th>Response</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($myInvitations as $inv): ?>
                <tr>
                    <td class="fw-semibold">
                        <?= htmlspecialchars($inv['job_fair_title'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                    </td>
                    <td class="text-muted small">
                        <?= !empty($inv['requested_date']) ? formatDate($inv['requested_date']) : '—' ?>
                    </td>
                    <td class="text-muted small"><?= formatDate($inv['invited_at']) ?></td>
                    <td><?= statusBadge($inv['status']) ?></td>
                    <td>
                        <?php if ($inv['status'] === 'invited'): ?>
                        <form method="POST" action="<?= APP_URL ?>/agency/confirm/<?= $inv['id'] ?>" class="d-flex gap-1">
                            <?= csrfField() ?>
                            <button type="submit" name="action" value="confirm"
                                    class="btn btn-sm btn-success fw-semibold">
                                <i class="bi bi-check-lg me-1"></i>Accept
                            </button>
                            <button type="submit" name="action" value="decline"
                                    class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('Are you sure you want to decline this invitation?')">
                                <i class="bi bi-x-lg me-1"></i>Decline
                            </button>
                        </form>
                        <?php elseif ($inv['status'] === 'confirmed'): ?>
                        <a href="<?= APP_URL ?>/agency/vacancies" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-briefcase me-1"></i>Post Vacancies
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
