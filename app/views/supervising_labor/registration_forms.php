<?php $pageTitle = $pageTitle ?? 'Job Fair Registration Forms'; ?>
<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Job Fair Registration Forms</h4>
    <a href="<?= APP_URL ?>/supervising-labor/dashboard" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Dashboard
    </a>
</div>

<?php if (empty($posts)): ?>
    <div class="text-center py-5 text-muted">
        <i class="bi bi-calendar-x display-3 d-block mb-3"></i>
        <h5>No Published Job Fairs Yet</h5>
        <p>Once the BEDO Officer publishes a job fair, it will appear here with its registration list.</p>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($posts as $p): ?>
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><?= htmlspecialchars($p['title'], ENT_QUOTES, 'UTF-8') ?></h6>
                        <span class="badge bg-success"><?= (int)$p['registered_count'] ?> Registered</span>
                    </div>
                    <div class="card-body py-2">
                        <ul class="list-unstyled small mb-0">
                            <?php $venue = $p['venue'] ?: $p['fair_venue'] ?? ''; ?>
                            <li><i class="bi bi-calendar3 me-2 text-primary"></i>
                                <?= !empty($p['event_date']) ? date('F d, Y', strtotime($p['event_date'])) : '—' ?>
                            </li>
                            <?php if ($venue): ?>
                            <li><i class="bi bi-geo-alt me-2 text-danger"></i>
                                <?= htmlspecialchars($venue, ENT_QUOTES, 'UTF-8') ?>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="card-footer bg-transparent">
                        <a href="<?= APP_URL ?>/supervising-labor/registration-form/<?= (int)$p['id'] ?>"
                           class="btn btn-primary btn-sm">
                            <i class="bi bi-eye me-1"></i>View / Print Registration List
                        </a>
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
