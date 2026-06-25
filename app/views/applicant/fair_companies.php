<?php $pageTitle = $pageTitle ?? 'Select a Company'; ?>
<?php ob_start(); ?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= APP_URL ?>/applicant/job-fairs" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Job Fairs
    </a>
    <div>
        <h4 class="mb-0 fw-bold"><i class="bi bi-buildings me-2 text-primary"></i>Select a Company</h4>
        <small class="text-muted"><?= htmlspecialchars($post['title'], ENT_QUOTES) ?></small>
    </div>
</div>

<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show">
    <?= htmlspecialchars($success, ENT_QUOTES) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Step Indicator -->
<div class="d-flex gap-2 align-items-center mb-4 small">
    <span class="badge bg-success py-2 px-3">✓ Registered</span>
    <i class="bi bi-chevron-right text-muted"></i>
    <span class="badge bg-primary py-2 px-3">2 Select Company</span>
    <i class="bi bi-chevron-right text-muted"></i>
    <span class="badge bg-light text-dark border py-2 px-3">3 Select Vacancy</span>
    <i class="bi bi-chevron-right text-muted"></i>
    <span class="badge bg-light text-dark border py-2 px-3">4 Upload Requirements</span>
</div>

<?php if (empty($companies)): ?>
<div class="text-center py-5 text-muted">
    <i class="bi bi-building-x display-3 d-block mb-3 opacity-50"></i>
    <h5>No Companies Available Yet</h5>
    <p>Check back later when companies have confirmed their participation.</p>
</div>
<?php else: ?>
<div class="row g-4">
    <?php foreach ($companies as $co):
        $alreadyApplied = in_array($co['agency_id'], $appliedAgencyIds);
    ?>
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm <?= $alreadyApplied ? 'border-success' : 'border-0' ?>">
            <?php if ($alreadyApplied): ?>
            <div class="card-header bg-success text-white py-2 small">
                <i class="bi bi-check-circle-fill me-1"></i>Application Submitted
            </div>
            <?php endif; ?>
            <div class="card-body">
                <h5 class="fw-bold mb-1"><?= htmlspecialchars($co['agency_name'], ENT_QUOTES) ?></h5>
                <?php if (!empty($co['location'])): ?>
                <p class="text-muted small mb-2">
                    <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($co['location'], ENT_QUOTES) ?>
                </p>
                <?php endif; ?>
                <div class="d-flex gap-2 mb-3">
                    <span class="badge bg-primary">
                        <i class="bi bi-briefcase me-1"></i><?= $co['vacancy_count'] ?> position(s)
                    </span>
                    <span class="badge bg-secondary">
                        <i class="bi bi-people me-1"></i><?= $co['total_slots'] ?? 0 ?> slots
                    </span>
                </div>
                <?php if (!empty($co['vacancies_list'])): ?>
                <p class="small text-muted mb-0">
                    <strong>Positions:</strong> <?= htmlspecialchars($co['vacancies_list'], ENT_QUOTES) ?>
                </p>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-transparent">
                <?php if ($alreadyApplied): ?>
                <button class="btn btn-success btn-sm w-100" disabled>
                    <i class="bi bi-check-circle me-1"></i>Applied
                </button>
                <?php else: ?>
                <a href="<?= APP_URL ?>/applicant/job-fairs/<?= (int)$post['id'] ?>/companies/<?= (int)$co['agency_id'] ?>/vacancies"
                   class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-arrow-right-circle me-1"></i>View Vacancies &amp; Apply
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
