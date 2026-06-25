<?php $pageTitle = $pageTitle ?? 'Registration Confirmation'; ?>
<?php ob_start(); ?>

<div class="text-center mb-4">
    <div class="text-success display-4 mb-2"><i class="bi bi-check-circle-fill"></i></div>
    <h4 class="fw-bold">Registration Confirmed!</h4>
    <p class="text-muted">You are now registered for
        <strong><?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?></strong>
        — Free Entry!
    </p>
</div>

<!-- Job Fair Details -->
<div class="card mb-4 shadow-sm">
    <div class="card-header bg-primary text-white">
        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Job Fair Details</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <?php $venue = $post['venue'] ?: $post['fair_venue'] ?? ''; ?>
            <div class="col-md-4">
                <small class="text-muted">Date &amp; Time</small>
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
                    <?= htmlspecialchars(
                        strtoupper($detail['surname']) . ', ' . $detail['firstname'] .
                        (!empty($detail['middlename']) ? ' ' . $detail['middlename'] : ''),
                        ENT_QUOTES, 'UTF-8') ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Next Step CTA -->
<div class="card border-primary shadow-sm mb-4">
    <div class="card-body text-center py-4">
        <i class="bi bi-buildings display-4 text-primary d-block mb-3"></i>
        <h5 class="fw-bold">Next Step: Apply to a Company</h5>
        <p class="text-muted mb-4">
            Select a participating company, choose a vacancy, and submit your requirements.
        </p>
        <a href="<?= APP_URL ?>/applicant/job-fairs/<?= (int)$post['id'] ?>/companies"
           class="btn btn-primary btn-lg px-5">
            <i class="bi bi-arrow-right-circle me-2"></i>Browse Companies &amp; Apply
        </a>
    </div>
</div>

<div class="d-flex gap-3 justify-content-center mb-4">
    <a href="<?= APP_URL ?>/applicant/job-fairs/<?= (int)$post['id'] ?>/pdf"
       target="_blank" class="btn btn-outline-success">
        <i class="bi bi-file-earmark-pdf me-2"></i>Download NSRP Form 1
    </a>
    <a href="<?= APP_URL ?>/applicant/job-fairs" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Job Fairs
    </a>
</div>

<!-- Participating Companies Table -->
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-building me-2"></i>Participating Companies</h6>
        <span class="badge bg-primary"><?= count($companies['companies']) ?> companies</span>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Company</th>
                    <th>Location</th>
                    <th>Open Positions</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($companies['companies'] as $co): ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($co['agency_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-muted small"><?= htmlspecialchars($co['location'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><small><?= htmlspecialchars($co['vacancies_list'] ?? '—', ENT_QUOTES, 'UTF-8') ?></small></td>
                    <td>
                        <a href="<?= APP_URL ?>/applicant/job-fairs/<?= (int)$post['id'] ?>/companies/<?= (int)$co['agency_id'] ?>/vacancies"
                           class="btn btn-sm btn-outline-primary">
                            Apply
                        </a>
                    </td>
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
