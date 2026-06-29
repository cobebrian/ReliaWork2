<?php $pageTitle = $pageTitle ?? 'Qualified Applicants'; ?>
<?php ob_start(); ?>

<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show">
    <?= htmlspecialchars($success, ENT_QUOTES) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Stats -->
<div class="row g-3 mb-4">
    <?php
    $statCards = [
        ['qualified',  'Qualified (For Contact)', 'success', 'bi-person-check-fill'],
        ['waitlisted', 'Waitlisted',               'info',    'bi-person-lines-fill'],
        ['awaiting',   'Awaiting/Submitted Docs',  'warning', 'bi-folder-check'],
        ['scheduled',  'First Day Scheduled',      'primary', 'bi-calendar-check'],
        ['hired',      'Officially Hired',         'dark',    'bi-trophy-fill'],
    ];
    foreach ($statCards as [$key, $label, $color, $icon]): ?>
    <div class="col-6 col-sm-4 col-lg">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-2 py-2">
                <div class="stat-icon bg-<?= $color ?>-subtle text-<?= $color ?>" style="width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                    <i class="bi <?= $icon ?>"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size:1.4rem;line-height:1;"><?= $stats[$key] ?></div>
                    <div class="text-muted" style="font-size:.72rem;"><?= $label ?></div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Applicants Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-people-fill me-2 text-success"></i>
            Qualified Applicants (<?= count($applicants) ?>)
        </h6>
    </div>
    <div class="card-body p-0">
        <?php if (empty($applicants)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-people display-3 d-block mb-3 opacity-50"></i>
            <h5>No Qualified Applicants Yet</h5>
            <p class="small">Applicants appear here after interviews are completed.</p>
            <a href="<?= APP_URL ?>/agency/interviews" class="btn btn-primary btn-sm">Go to Interviews</a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Applicant</th>
                        <th>Position</th>
                        <th>Job Fair</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Msgs</th>
                        <th class="text-center">Docs</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($applicants as $ap):
                    $statusCfg = [
                        'qualified_for_contact'  => ['success', 'Qualified – For Contact'],
                        'waitlisted'             => ['info',    'Waitlisted'],
                        'awaiting_requirements'  => ['warning', 'Awaiting Docs'],
                        'requirements_submitted' => ['primary', 'Docs Submitted'],
                        'first_day_scheduled'    => ['dark',    'First Day Scheduled'],
                        'hired'                  => ['success', '🏆 Hired'],
                    ];
                    [$sColor, $sLabel] = $statusCfg[$ap['status']] ?? ['secondary', ucfirst($ap['status'])];
                ?>
                <tr>
                    <td>
                        <div class="fw-semibold small">
                            <?= htmlspecialchars(strtoupper($ap['surname']) . ', ' . $ap['firstname'], ENT_QUOTES) ?>
                        </div>
                        <div class="text-muted" style="font-size:.72rem;">
                            <?= htmlspecialchars($ap['cellphone'] ?? '', ENT_QUOTES) ?>
                            <?= !empty($ap['applicant_email']) ? ' · ' . htmlspecialchars($ap['applicant_email'], ENT_QUOTES) : '' ?>
                        </div>
                    </td>
                    <td class="small"><?= htmlspecialchars($ap['position'], ENT_QUOTES) ?></td>
                    <td class="small text-muted"><?= htmlspecialchars($ap['fair_title'] ?? '—', ENT_QUOTES) ?></td>
                    <td class="text-center">
                        <span class="badge bg-<?= $sColor ?>"><?= $sLabel ?></span>
                    </td>
                    <td class="text-center">
                        <?php if ($ap['unread_msgs'] > 0): ?>
                        <span class="badge bg-danger"><?= $ap['unread_msgs'] ?> new</span>
                        <?php else: ?>
                        <span class="text-muted small">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if ($ap['emp_doc_count'] > 0): ?>
                        <span class="badge bg-primary"><?= $ap['emp_doc_count'] ?></span>
                        <?php else: ?>
                        <span class="text-muted small">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= APP_URL ?>/agency/hiring/<?= $ap['id'] ?>"
                           class="btn btn-sm btn-primary">
                            <i class="bi bi-person-badge me-1"></i>View
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
