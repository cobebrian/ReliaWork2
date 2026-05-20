<?php ob_start(); ?>
<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary-subtle text-primary">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <div class="stat-value"><?= number_format($stats['total_users']) ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning-subtle text-warning">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div>
                    <div class="stat-value"><?= number_format($stats['pending_approvals']) ?></div>
                    <div class="stat-label">Pending Approvals</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-success-subtle text-success">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div>
                    <div class="stat-value"><?= number_format($stats['active_job_fairs']) ?></div>
                    <div class="stat-label">Active Job Fairs</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-info-subtle text-info">
                    <i class="bi bi-person-badge"></i>
                </div>
                <div>
                    <div class="stat-value"><?= number_format($stats['total_applicants']) ?></div>
                    <div class="stat-label">Total Applicants</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pending Users -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-bold"><i class="bi bi-hourglass-split text-warning me-2"></i>Pending User Approvals</h6>
        <a href="<?= APP_URL ?>/admin/users" class="btn btn-sm btn-outline-primary">View All Users</a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($pendingUsers)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-check-circle display-4 d-block mb-2 text-success"></i>
                No pending approvals.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Registered</th>
                            <th>Assign Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingUsers as $u): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-circle"><?= strtoupper(substr($u['name'], 0, 1)) ?></div>
                                        <span class="fw-semibold"><?= htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                </td>
                                <td class="text-muted"><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-muted small"><?= formatDate($u['created_at']) ?></td>
                                <td>
                                    <form method="POST" action="<?= APP_URL ?>/admin/users/<?= $u['id'] ?>/approve"
                                          class="d-flex gap-2 align-items-center approve-form">
                                        <?= csrfField() ?>
                                        <select name="role" class="form-select form-select-sm" required style="min-width:160px">
                                            <option value="">— Select Role —</option>
                                            <?php foreach ($roles as $r): ?>
                                                <?php if ($r !== 'admin'): ?>
                                                    <option value="<?= $r ?>"><?= htmlspecialchars($roleLabels[$r] ?? $r, ENT_QUOTES, 'UTF-8') ?></option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-success"
                                                onclick="return confirm('Approve this user?')">
                                            <i class="bi bi-check-lg"></i> Approve
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <form method="POST" action="<?= APP_URL ?>/admin/users/<?= $u['id'] ?>/reject">
                                        <?= csrfField() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Reject this user?')">
                                            <i class="bi bi-x-lg"></i> Reject
                                        </button>
                                    </form>
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
