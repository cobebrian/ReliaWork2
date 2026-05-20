<?php ob_start(); ?>
<!-- Search -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="<?= APP_URL ?>/applicant/vacancies" class="row g-3 align-items-end">
            <div class="col-md-8">
                <label class="form-label fw-semibold small">Search Vacancies</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control"
                           placeholder="Search by position or company..."
                           value="<?= htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-2"></i>Search
                </button>
            </div>
        </form>
    </div>
</div>

<?php if (!$applicant): ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        You must <a href="<?= APP_URL ?>/applicant/register" class="alert-link">complete your applicant profile</a> before applying for jobs.
    </div>
<?php endif; ?>

<!-- Vacancies Grid -->
<?php if (empty($vacancies)): ?>
    <div class="text-center py-5 text-muted">
        <i class="bi bi-briefcase display-4 d-block mb-2"></i>
        No open vacancies found<?= !empty($_GET['search']) ? ' for "' . htmlspecialchars($_GET['search'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>.
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($vacancies as $v): ?>
            <div class="col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100 vacancy-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="fw-bold mb-0"><?= htmlspecialchars($v['position'], ENT_QUOTES, 'UTF-8') ?></h6>
                            <?= statusBadge($v['status']) ?>
                        </div>
                        <p class="text-primary fw-semibold mb-1">
                            <i class="bi bi-building me-1"></i><?= htmlspecialchars($v['company_name'], ENT_QUOTES, 'UTF-8') ?>
                        </p>
                        <?php if ($v['company_location']): ?>
                            <p class="text-muted small mb-1">
                                <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($v['company_location'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        <?php endif; ?>
                        <p class="text-muted small mb-2">
                            <i class="bi bi-people me-1"></i><?= $v['available_slots'] ?> slot(s) available
                        </p>
                        <?php if ($v['qualifications']): ?>
                            <p class="text-muted small mb-3">
                                <strong>Qualifications:</strong><br>
                                <?= nl2br(htmlspecialchars(substr($v['qualifications'], 0, 120), ENT_QUOTES, 'UTF-8')) ?>
                                <?= strlen($v['qualifications']) > 120 ? '...' : '' ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-transparent border-0 pt-0">
                        <?php if (in_array($v['id'], $appliedIds)): ?>
                            <button class="btn btn-success btn-sm w-100" disabled>
                                <i class="bi bi-check-circle me-1"></i>Applied
                            </button>
                        <?php elseif ($applicant): ?>
                            <form method="POST" action="<?= APP_URL ?>/applicant/apply/<?= $v['id'] ?>">
                                <?= csrfField() ?>
                                <button type="submit" class="btn btn-primary btn-sm w-100"
                                        onclick="return confirm('Apply for <?= htmlspecialchars($v['position'], ENT_QUOTES, 'UTF-8') ?> at <?= htmlspecialchars($v['company_name'], ENT_QUOTES, 'UTF-8') ?>?')">
                                    <i class="bi bi-send me-1"></i>Apply Now
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="<?= APP_URL ?>/applicant/register" class="btn btn-outline-warning btn-sm w-100">
                                <i class="bi bi-person-plus me-1"></i>Register to Apply
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
