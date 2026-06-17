<?php ob_start(); ?>

<?php if (!empty($success)): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-collection me-2 text-primary"></i>My Advertisements</h5>
    <a href="<?= APP_URL ?>/bedo/compose" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i>New Advertisement
    </a>
</div>

<?php if (empty($posts)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-megaphone display-4 d-block mb-2 opacity-50"></i>
        No advertisements yet. <a href="<?= APP_URL ?>/bedo/compose">Create one now.</a>
    </div>
</div>
<?php else: ?>
<div class="row g-3">
<?php foreach ($posts as $p):
    $isPublished = $p['status'] === 'published';
    $borderColor = $isPublished ? 'success' : 'secondary';
?>
<div class="col-12">
    <div class="card border-0 shadow-sm border-start border-<?= $borderColor ?> border-3">
        <div class="card-body">
            <div class="row align-items-start">
                <div class="col">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <?= statusBadge($p['status']) ?>
                        <?php if ($isPublished): ?>
                        <span class="badge bg-success-subtle text-success">
                            <i class="bi bi-broadcast me-1"></i>Live on Landing Page
                        </span>
                        <?php endif; ?>
                    </div>
                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($p['title'], ENT_QUOTES, 'UTF-8') ?></h6>
                    <div class="text-muted small mb-2">
                        <i class="bi bi-calendar-event me-1"></i>
                        <?= $p['event_date'] ? formatDate($p['event_date']) : '—' ?>
                        <?php if ($p['venue']): ?>
                        &bull; <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($p['venue'], ENT_QUOTES, 'UTF-8') ?>
                        <?php endif; ?>
                        &bull; <i class="bi bi-clipboard me-1"></i><?= htmlspecialchars($p['fair_title'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <?php if ($p['description']): ?>
                    <p class="small text-muted mb-0" style="white-space:pre-wrap;max-height:80px;overflow:hidden;">
                        <?= htmlspecialchars(substr($p['description'], 0, 200), ENT_QUOTES, 'UTF-8') ?>
                        <?= strlen($p['description']) > 200 ? '...' : '' ?>
                    </p>
                    <?php endif; ?>
                </div>
                <div class="col-auto d-flex gap-1 flex-wrap justify-content-end">
                    <?php if (!$isPublished): ?>
                    <form method="POST" action="<?= APP_URL ?>/bedo/posts/<?= $p['id'] ?>/publish">
                        <?= csrfField() ?>
                        <button type="submit" class="btn btn-sm btn-success fw-semibold">
                            <i class="bi bi-broadcast me-1"></i>Publish
                        </button>
                    </form>
                    <?php endif; ?>
                    <a href="<?= APP_URL ?>/?preview=<?= $p['id'] ?>" target="_blank"
                       class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye me-1"></i>Preview
                    </a>
                    <form method="POST" action="<?= APP_URL ?>/bedo/posts/<?= $p['id'] ?>/delete"
                          onsubmit="return confirm('Delete this advertisement?')">
                        <?= csrfField() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
