<?php ob_start(); ?>
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-file-earmark-text"></i></div>
                <div>
                    <div class="stat-value"><?= $applicationCount ?></div>
                    <div class="stat-label">My Applications</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-briefcase"></i></div>
                <div>
                    <div class="stat-value"><?= $openVacancies ?></div>
                    <div class="stat-label">Open Vacancies</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-calendar-event"></i></div>
                <div>
                    <div class="stat-value"><?= $upcomingFairs ?? 0 ?></div>
                    <div class="stat-label">Job Fairs</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-<?= $applicant ? 'success' : 'warning' ?>-subtle text-<?= $applicant ? 'success' : 'warning' ?>">
                    <i class="bi bi-person-badge"></i>
                </div>
                <div>
                    <div class="stat-value"><?= $registrationCount ?? 0 ?></div>
                    <div class="stat-label">Fair Registrations</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!$applicant): ?>
    <div class="alert alert-warning d-flex align-items-center gap-3">
        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
        <div>
            <strong>Complete your profile!</strong> You need to register your applicant details before applying for jobs or job fairs.
            <a href="<?= APP_URL ?>/applicant/register" class="btn btn-warning btn-sm ms-3">
                <i class="bi bi-person-plus me-1"></i>Complete Registration
            </a>
        </div>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-4">
            <div class="card-body">
                <i class="bi bi-person-plus display-4 text-primary mb-3 d-block"></i>
                <h6 class="fw-bold">My Profile</h6>
                <p class="text-muted small">Complete your applicant registration</p>
                <a href="<?= APP_URL ?>/applicant/register" class="btn btn-outline-primary btn-sm">
                    <?= $applicant ? 'View Profile' : 'Register Now' ?>
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-4">
            <div class="card-body">
                <i class="bi bi-calendar-event display-4 text-warning mb-3 d-block"></i>
                <h6 class="fw-bold">Job Fairs</h6>
                <p class="text-muted small"><?= $upcomingFairs ?? 0 ?> upcoming event(s)</p>
                <a href="<?= APP_URL ?>/applicant/job-fairs" class="btn btn-warning btn-sm">View &amp; Register</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-4">
            <div class="card-body">
                <i class="bi bi-search display-4 text-success mb-3 d-block"></i>
                <h6 class="fw-bold">Browse Jobs</h6>
                <p class="text-muted small"><?= $openVacancies ?> open positions available</p>
                <a href="<?= APP_URL ?>/applicant/vacancies" class="btn btn-outline-success btn-sm">Browse Vacancies</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-4">
            <div class="card-body">
                <i class="bi bi-file-earmark-text display-4 text-info mb-3 d-block"></i>
                <h6 class="fw-bold">My Applications</h6>
                <p class="text-muted small"><?= $applicationCount ?> application(s) submitted</p>
                <a href="<?= APP_URL ?>/applicant/my-applications" class="btn btn-outline-info btn-sm">View Applications</a>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
