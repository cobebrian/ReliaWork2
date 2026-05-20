<?php ob_start(); ?>
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-4">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-box-seam"></i></div>
                <div>
                    <div class="stat-value"><?= $stats['total_resources'] ?></div>
                    <div class="stat-label">Total Resources</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-check-circle"></i></div>
                <div>
                    <div class="stat-value"><?= $stats['available_resources'] ?></div>
                    <div class="stat-label">Available</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-calendar-check"></i></div>
                <div>
                    <div class="stat-value"><?= $stats['approved_fairs'] ?></div>
                    <div class="stat-label">Approved Job Fairs</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-box-seam me-2 text-primary"></i>Resource Inventory</h6>
        <a href="<?= APP_URL ?>/secretary/resources" class="btn btn-sm btn-outline-primary">Manage Resources</a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($resources)): ?>
            <div class="text-center py-4 text-muted">
                <i class="bi bi-box display-4 d-block mb-2"></i>No resources found.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Resource</th><th>Quantity</th><th>Unit</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resources as $r): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= number_format($r['quantity']) ?></td>
                                <td class="text-muted"><?= htmlspecialchars($r['unit'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= statusBadge($r['status']) ?></td>
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
