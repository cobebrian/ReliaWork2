<?php ob_start(); ?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-file-earmark-text me-2 text-primary"></i>My Applications (<?= count($applications) ?>)</h6>
        <a href="<?= APP_URL ?>/applicant/vacancies" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-search me-1"></i>Browse More Jobs
        </a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($applications)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-file-earmark display-4 d-block mb-2"></i>
                You haven't applied for any jobs yet.
                <br>
                <a href="<?= APP_URL ?>/applicant/vacancies" class="btn btn-primary mt-3">
                    <i class="bi bi-search me-2"></i>Browse Vacancies
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Position</th>
                            <th>Company</th>
                            <th>Agency</th>
                            <th>Status</th>
                            <th>Applied</th>
                            <th>Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($app['position'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($app['company_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-muted small"><?= htmlspecialchars($app['agency_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= statusBadge($app['status']) ?></td>
                                <td class="text-muted small"><?= formatDate($app['applied_at'], 'M d, Y') ?></td>
                                <td class="text-muted small"><?= formatDate($app['updated_at'], 'M d, Y') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
