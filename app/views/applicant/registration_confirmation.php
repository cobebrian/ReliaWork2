<?php $pageTitle = $pageTitle ?? 'Registration Confirmation'; ?>
<?php ob_start(); ?>

<div class="text-center mb-4">
    <div class="text-success display-4 mb-2"><i class="bi bi-check-circle-fill"></i></div>
    <h4 class="fw-bold">Registration Confirmed!</h4>
    <p class="text-muted">You are now registered for <strong><?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?></strong></p>
</div>

<div class="card mb-4 shadow-sm">
    <div class="card-header bg-primary text-white">
        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Job Fair Details</h6>
    </div>
    <div class="card-body">
        <div class="row g-2">
            <?php $venue = $post['venue'] ?: $post['fair_venue'] ?? ''; ?>
            <div class="col-md-4">
                <small class="text-muted">Date & Time</small>
                <div class="fw-semibold">
                    <?= !empty($post['event_date']) ? date('F d, Y', strtotime($post['event_date'])) : '—' ?>
                    <?= !empty($post['event_time']) ? ' at ' . htmlspecialchars($post['event_time']) : '' ?>
                </div>
            </div>
            <div class="col-md-4">
                <small class="text-muted">Venue</small>
                <div class="fw-semibold"><?= htmlspecialchars($venue ?: '—', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="col-md-4">
                <small class="text-muted">Your Name</small>
                <div class="fw-semibold">
                    <?= htmlspecialchars(strtoupper($detail['surname']) . ', ' . $detail['firstname'] . (!empty($detail['middlename']) ? ' ' . $detail['middlename'] : ''), ENT_QUOTES, 'UTF-8') ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex gap-3 justify-content-center mb-4">
    <a href="<?= APP_URL ?>/applicant/job-fairs/<?= (int)$post['id'] ?>/pdf"
       target="_blank" class="btn btn-success btn-lg">
        <i class="bi bi-file-earmark-pdf me-2"></i>Download / Print Registration Form (NSRP Form 1)
    </a>
    <a href="<?= APP_URL ?>/applicant/job-fairs" class="btn btn-outline-secondary btn-lg">
        <i class="bi bi-arrow-left me-2"></i>Back to Job Fairs
    </a>
</div>

<!-- Quick summary card -->
<div class="card shadow-sm">
    <div class="card-header">
        <h6 class="mb-0"><i class="bi bi-building me-2"></i>Participating Companies</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead class="table-light">
                <tr><th>Company</th><th>Location</th><th>Open Positions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($companies['companies'] as $co): ?>
                    <tr>
                        <td class="fw-semibold"><?= htmlspecialchars($co['agency_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($co['location'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><small><?= htmlspecialchars($co['vacancies_list'] ?? '—', ENT_QUOTES, 'UTF-8') ?></small></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
?>
