<?php $pageTitle = $pageTitle ?? 'Choose a Position'; ?>
<?php ob_start(); ?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= APP_URL ?>/applicant/job-fairs/<?= (int)$post['id'] ?>/companies" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back to Companies
    </a>
    <div>
        <h4 class="mb-0 fw-bold"><i class="bi bi-briefcase me-2 text-primary"></i><?= htmlspecialchars($agency['agency_name'], ENT_QUOTES) ?></h4>
        <small class="text-muted"><?= htmlspecialchars($agency['fair_title'], ENT_QUOTES) ?></small>
    </div>
</div>

<!-- Step Indicator -->
<div class="d-flex gap-2 align-items-center mb-4 small">
    <span class="badge bg-success py-2 px-3">✓ Registered</span>
    <i class="bi bi-chevron-right text-muted"></i>
    <span class="badge bg-success py-2 px-3">✓ Company Selected</span>
    <i class="bi bi-chevron-right text-muted"></i>
    <span class="badge bg-primary py-2 px-3">3 Select Vacancy</span>
    <i class="bi bi-chevron-right text-muted"></i>
    <span class="badge bg-light text-dark border py-2 px-3">4 Upload Requirements</span>
</div>

<?php if (empty($vacancies)): ?>
<div class="text-center py-5 text-muted">
    <i class="bi bi-briefcase-x display-3 d-block mb-3 opacity-50"></i>
    <h5>No Open Vacancies</h5>
    <p>This company has no open positions at this time.</p>
    <a href="<?= APP_URL ?>/applicant/job-fairs/<?= (int)$post['id'] ?>/companies" class="btn btn-outline-primary">
        Choose Another Company
    </a>
</div>
<?php else: ?>
<div class="row g-4">
    <?php foreach ($vacancies as $v):
        $alreadyApplied = in_array($v['id'], $appliedVacancyIds);
    ?>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100 <?= $alreadyApplied ? 'border-success' : 'border-0' ?>">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="fw-bold mb-0"><?= htmlspecialchars($v['position'], ENT_QUOTES) ?></h5>
                    <?php if ($alreadyApplied): ?>
                    <span class="badge bg-success">Applied</span>
                    <?php else: ?>
                    <span class="badge bg-primary"><?= $v['available_slots'] ?> slot(s)</span>
                    <?php endif; ?>
                </div>
                <p class="text-muted small mb-2">
                    <i class="bi bi-building me-1"></i><?= htmlspecialchars($agency['agency_name'], ENT_QUOTES) ?>
                    <?php if (!empty($v['company_location'])): ?>
                    &nbsp;&bull;&nbsp;<i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($v['company_location'], ENT_QUOTES) ?>
                    <?php endif; ?>
                </p>
                <?php if (!empty($v['qualifications'])): ?>
                <div class="bg-light rounded p-2 mb-3 small">
                    <strong>Qualifications:</strong><br>
                    <?= nl2br(htmlspecialchars($v['qualifications'], ENT_QUOTES)) ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($v['mobile_number']) || !empty($v['gmail_address'])): ?>
                <div class="small text-muted">
                    <?php if ($v['mobile_number']): ?>
                    <i class="bi bi-phone me-1"></i><?= htmlspecialchars($v['mobile_number'], ENT_QUOTES) ?>
                    <?php endif; ?>
                    <?php if ($v['gmail_address']): ?>
                    &nbsp;<i class="bi bi-envelope me-1"></i><?= htmlspecialchars($v['gmail_address'], ENT_QUOTES) ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-transparent">
                <?php if ($alreadyApplied): ?>
                <button class="btn btn-success btn-sm w-100" disabled>
                    <i class="bi bi-check-circle me-1"></i>Already Applied
                </button>
                <?php else: ?>
                <a href="<?= APP_URL ?>/applicant/job-fairs/<?= (int)$post['id'] ?>/apply/<?= (int)$v['id'] ?>"
                   class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-send me-1"></i>Apply for This Position
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
