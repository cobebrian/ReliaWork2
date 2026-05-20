<?php ob_start(); ?>
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-4">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-building"></i></div>
                <div>
                    <div class="stat-value"><?= $stats['my_agencies'] ?></div>
                    <div class="stat-label">My Invitations</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-check-circle"></i></div>
                <div>
                    <div class="stat-value"><?= $stats['confirmed'] ?></div>
                    <div class="stat-label">Confirmed</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-briefcase"></i></div>
                <div>
                    <div class="stat-value"><?= $stats['vacancy_count'] ?></div>
                    <div class="stat-label">Posted Vacancies</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-5">
                <i class="bi bi-briefcase-fill display-3 text-primary mb-3 d-block"></i>
                <h5 class="fw-bold">Manage Vacancies</h5>
                <p class="text-muted">Post and manage job vacancies for your agency.</p>
                <a href="<?= APP_URL ?>/agency/vacancies" class="btn btn-primary px-4">
                    <i class="bi bi-briefcase me-2"></i>View Vacancies
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-5">
                <i class="bi bi-envelope-check display-3 text-success mb-3 d-block"></i>
                <h5 class="fw-bold">Participation Status</h5>
                <p class="text-muted">
                    You have <strong><?= $stats['confirmed'] ?></strong> confirmed participation(s)
                    out of <strong><?= $stats['my_agencies'] ?></strong> invitation(s).
                </p>
                <a href="<?= APP_URL ?>/agency/vacancies" class="btn btn-outline-success px-4">
                    <i class="bi bi-arrow-right me-2"></i>Go to Vacancies
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-inbox-fill me-2 text-primary"></i>Invitations</h6>
                <a href="<?= APP_URL ?>/agency/vacancies" class="btn btn-sm btn-outline-secondary">Manage Invitations</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($myAgencies)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox display-4 d-block mb-2"></i>No invitations yet.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Job Fair</th>
                                    <th>Agency</th>
                                    <th>Status</th>
                                    <th>Invited</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($myAgencies as $a): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($a['job_fair_title'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($a['agency_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= statusBadge($a['status']) ?></td>
                                        <td class="text-muted small"><?= formatDate($a['invited_at']) ?></td>
                                        <td>
                                            <?php if ($a['status'] === 'invited'): ?>
                                                <form method="POST" action="<?= APP_URL ?>/agency/confirm/<?= $a['id'] ?>" style="display:inline;">
                                                    <?= csrfField() ?>
                                                    <button type="submit" name="action" value="confirm" class="btn btn-sm btn-success">Accept</button>
                                                    <button type="submit" name="action" value="decline" class="btn btn-sm btn-danger">Reject</button>
                                                </form>
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
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
