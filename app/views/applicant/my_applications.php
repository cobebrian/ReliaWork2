<?php $pageTitle = 'My Applications'; ?>
<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><i class="bi bi-file-earmark-text me-2 text-primary"></i>My Applications</h4>
    <a href="<?= APP_URL ?>/applicant/job-fairs" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i>Apply to More Jobs
    </a>
</div>

<?php if (empty($applications)): ?>
<div class="text-center py-5 text-muted">
    <i class="bi bi-file-earmark-x display-3 d-block mb-3 opacity-50"></i>
    <h5>No Applications Yet</h5>
    <p>Register for a Job Fair and apply to companies to get started.</p>
    <a href="<?= APP_URL ?>/applicant/job-fairs" class="btn btn-primary">Browse Job Fairs</a>
</div>
<?php else: ?>

<!-- Status Legend -->
<div class="d-flex gap-2 flex-wrap mb-3 small">
    <span class="badge bg-warning text-dark py-1 px-2">Pending Validation</span>
    <span class="badge bg-success py-1 px-2">Approved</span>
    <span class="badge bg-danger py-1 px-2">Rejected</span>
    <span class="badge bg-info text-dark py-1 px-2">Resubmit</span>
    <span class="badge bg-primary py-1 px-2">Ready for Interview</span>
    <span class="badge bg-dark py-1 px-2">Interviewed</span>
</div>

<div class="row g-4">
    <?php foreach ($applications as $app):
        $vsColors = [
            'pending_validation' => ['warning', 'bi-hourglass-split',       'Pending Validation',  'Your documents are being reviewed.'],
            'approved'           => ['success', 'bi-check-circle-fill',     'Approved',            'Your documents were approved. Waiting for interview schedule.'],
            'rejected'           => ['danger',  'bi-x-circle-fill',         'Rejected',            'Your documents were rejected. See remarks below.'],
            'resubmit'           => ['info',    'bi-arrow-clockwise',       'Resubmission Required','Please resubmit your documents.'],
        ];
        $vs = $app['validation_status'] ?? 'pending_validation';
        [$vColor, $vIcon, $vLabel, $vDesc] = $vsColors[$vs] ?? $vsColors['pending_validation'];

        $sColors = ['pending'=>'secondary','shortlisted'=>'primary','hired'=>'success','rejected'=>'danger'];
        $sColor  = $sColors[$app['status']] ?? 'secondary';
    ?>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center py-2">
                <div class="small fw-bold">
                    <?= htmlspecialchars($app['position'] ?? '—', ENT_QUOTES) ?>
                    <span class="fw-normal text-muted"> at </span>
                    <?= htmlspecialchars($app['company_name'] ?? $app['agency_name'] ?? '—', ENT_QUOTES) ?>
                </div>
                <span class="badge bg-<?= $vColor ?> flex-shrink-0">
                    <i class="bi <?= $vIcon ?> me-1"></i><?= $vLabel ?>
                </span>
            </div>
            <div class="card-body py-2">
                <div class="small text-muted mb-2">
                    <?php if (!empty($app['fair_title'])): ?>
                    <i class="bi bi-calendar-event me-1"></i><?= htmlspecialchars($app['fair_title'], ENT_QUOTES) ?><br>
                    <?php endif; ?>
                    <i class="bi bi-clock me-1"></i>Applied: <?= date('M d, Y', strtotime($app['applied_at'])) ?>
                </div>
                <div class="alert alert-<?= $vColor ?> py-2 px-3 small mb-0">
                    <i class="bi <?= $vIcon ?> me-1"></i><?= $vDesc ?>
                    <?php if (!empty($app['validator_remarks'])): ?>
                    <div class="mt-1 fw-semibold">
                        Remarks: "<?= htmlspecialchars($app['validator_remarks'], ENT_QUOTES) ?>"
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-footer bg-transparent d-flex gap-2 justify-content-between align-items-center py-2">
                <span class="badge bg-<?= $sColor ?> small"><?= ucfirst($app['status']) ?></span>
                <?php if ($vs === 'resubmit' || $vs === 'rejected'): ?>
                <a href="<?= APP_URL ?>/applicant/my-applications/<?= $app['id'] ?>/resubmit"
                   class="btn btn-sm btn-warning">
                    <i class="bi bi-arrow-clockwise me-1"></i>Resubmit Documents
                </a>
                <?php endif; ?>
                <?php if (in_array($app['status'], ['qualified_for_contact','waitlisted','awaiting_requirements','requirements_submitted','first_day_scheduled','hired'])): ?>
                <a href="<?= APP_URL ?>/applicant/messages/<?= $app['id'] ?>"
                   class="btn btn-sm btn-primary">
                    <i class="bi bi-chat-dots me-1"></i>Messages
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
