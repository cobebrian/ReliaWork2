<?php ob_start(); ?>

<!-- Welcome Banner -->
<div class="card border-0 text-white mb-4" style="background:linear-gradient(135deg,#0a3d62,#1e88e5);">
    <div class="card-body py-4">
        <div class="row align-items-center">
            <div class="col">
                <h4 class="fw-bold mb-1">
                    <i class="bi bi-megaphone-fill me-2"></i>BEDO Officer Dashboard
                </h4>
                <p class="mb-0 opacity-75">
                    Barangay Employment Desk Officer — Advertise job fairs to the community.
                </p>
            </div>
            <div class="col-auto">
                <a href="<?= APP_URL ?>/bedo/compose" class="btn btn-warning btn-lg fw-semibold">
                    <i class="bi bi-plus-circle me-2"></i>Post New Job Fair Ad
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-broadcast"></i></div>
                <div><div class="stat-value"><?= $stats['published'] ?></div><div class="stat-label">Published Ads</div></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-pencil-square"></i></div>
                <div><div class="stat-value"><?= $stats['draft'] ?></div><div class="stat-label">Drafts</div></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-briefcase-fill"></i></div>
                <div><div class="stat-value"><?= $stats['total_vacancies'] ?></div><div class="stat-label">Open Vacancies</div></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-calendar-event"></i></div>
                <div><div class="stat-value"><?= $stats['upcoming_fairs'] ?></div><div class="stat-label">Upcoming Fairs</div></div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Posts -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-collection me-2 text-primary"></i>Recent Advertisements</h6>
        <a href="<?= APP_URL ?>/bedo/posts" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($myPosts)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-megaphone display-4 d-block mb-2 opacity-50"></i>
            No advertisements yet. <a href="<?= APP_URL ?>/bedo/compose">Create your first post.</a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Title</th><th>Job Fair</th><th>Event Date</th><th>Status</th><th>Published</th></tr>
                </thead>
                <tbody>
                <?php foreach ($myPosts as $p): ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($p['title'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-muted small"><?= htmlspecialchars($p['fair_title'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-muted small"><?= $p['event_date'] ? formatDate($p['event_date']) : formatDate($p['requested_date']) ?></td>
                    <td><?= statusBadge($p['status']) ?></td>
                    <td class="text-muted small"><?= $p['published_at'] ? formatDate($p['published_at']) : '—' ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
