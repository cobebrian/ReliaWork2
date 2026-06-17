<?php $pageTitle = $pageTitle ?? 'Upcoming Job Fairs'; ?>
<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-calendar-event me-2 text-primary"></i>Upcoming Job Fairs</h4>
    <a href="<?= APP_URL ?>/applicant/vacancies" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-briefcase me-1"></i>Browse All Vacancies
    </a>
</div>

<?php $flash = getFlash('info') ?: getFlash('success') ?: getFlash('error'); ?>
<?php if ($flash): ?>
    <div class="alert alert-info alert-dismissible fade show">
        <?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (empty($posts)): ?>
    <div class="text-center py-5 text-muted">
        <i class="bi bi-calendar-x display-3 d-block mb-3"></i>
        <h5>No Job Fairs Available Yet</h5>
        <p>Check back soon — the BEDO Officer will post upcoming job fairs here.</p>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($posts as $post): ?>
            <?php
                $isRegistered = $applicant
                    ? in_array($post['id'], $registeredPostIds)
                    : false;
                $isPast = !empty($post['event_date']) && strtotime($post['event_date']) < strtotime('today');
            ?>
            <div class="col-lg-6">
                <div class="card h-100 shadow-sm <?= $isRegistered ? 'border-success' : '' ?>">
                    <?php if ($isRegistered): ?>
                        <div class="card-header bg-success text-white py-2">
                            <i class="bi bi-check-circle-fill me-1"></i> You are registered for this job fair
                        </div>
                    <?php elseif ($isPast): ?>
                        <div class="card-header bg-secondary text-white py-2">
                            <i class="bi bi-clock-history me-1"></i> Past Event
                        </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?></h5>
                        <?php if (!empty($post['description'])): ?>
                            <p class="card-text text-muted"><?= nl2br(htmlspecialchars($post['description'], ENT_QUOTES, 'UTF-8')) ?></p>
                        <?php endif; ?>

                        <ul class="list-unstyled mb-3 small">
                            <?php if (!empty($post['event_date'])): ?>
                                <li><i class="bi bi-calendar3 me-2 text-primary"></i>
                                    <strong>Date:</strong>
                                    <?= date('F d, Y', strtotime($post['event_date'])) ?>
                                    <?= !empty($post['event_time']) ? ' at ' . htmlspecialchars($post['event_time']) : '' ?>
                                </li>
                            <?php endif; ?>
                            <?php $venue = $post['venue'] ?: $post['fair_venue'] ?? ''; ?>
                            <?php if ($venue): ?>
                                <li><i class="bi bi-geo-alt me-2 text-danger"></i>
                                    <strong>Venue:</strong> <?= htmlspecialchars($venue, ENT_QUOTES, 'UTF-8') ?>
                                </li>
                            <?php endif; ?>
                            <li><i class="bi bi-building me-2 text-info"></i>
                                <strong>Companies:</strong> <?= (int)$post['company_count'] ?>
                            </li>
                            <li><i class="bi bi-briefcase me-2 text-success"></i>
                                <strong>Total Vacancies:</strong> <?= (int)$post['vacancy_count'] ?>
                                (<?= (int)$post['total_slots'] ?> slots)
                            </li>
                        </ul>
                    </div>
                    <div class="card-footer bg-transparent d-flex gap-2">
                        <?php if ($isRegistered): ?>
                            <a href="<?= APP_URL ?>/applicant/job-fairs/<?= $post['id'] ?>/confirmation"
                               class="btn btn-success btn-sm">
                                <i class="bi bi-file-earmark-person me-1"></i>View / Download Form
                            </a>
                        <?php elseif (!$isPast): ?>
                            <?php if ($applicant): ?>
                                <a href="<?= APP_URL ?>/applicant/job-fairs/<?= $post['id'] ?>/register"
                                   class="btn btn-primary btn-sm">
                                    <i class="bi bi-pencil-square me-1"></i>Register Online
                                </a>
                                <a href="<?= APP_URL ?>/applicant/job-fairs/<?= $post['id'] ?>/pdf"
                                   class="btn btn-outline-secondary btn-sm" target="_blank">
                                    <i class="bi bi-download me-1"></i>Download Form
                                </a>
                            <?php else: ?>
                                <a href="<?= APP_URL ?>/applicant/register"
                                   class="btn btn-warning btn-sm">
                                    <i class="bi bi-person-plus me-1"></i>Complete Profile to Register
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted small"><i class="bi bi-clock-history me-1"></i>Registration closed</span>
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
?>
