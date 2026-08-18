<?php $pageTitle = 'Organization Invitations'; ?>
<?php ob_start(); ?>

<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show">
    <?= htmlspecialchars($success, ENT_QUOTES) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= APP_URL ?>/org/dashboard" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Dashboard
    </a>
    <h5 class="mb-0 fw-bold"><i class="bi bi-envelope me-2 text-primary"></i>Organization Invitations</h5>
</div>

<?php if (empty($invitations)): ?>
<div class="text-center py-5 text-muted">
    <i class="bi bi-envelope-open display-3 d-block mb-3 opacity-50"></i>
    <h5>No Pending Invitations</h5>
    <p class="small">When an Organization Admin invites you, it will appear here.</p>
    <a href="<?= APP_URL ?>/org/dashboard" class="btn btn-outline-primary btn-sm">Back to Dashboard</a>
</div>
<?php else: ?>
<div class="row g-4">
    <?php foreach ($invitations as $inv): ?>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-primary text-white py-2">
                <h6 class="mb-0 small fw-bold">
                    <i class="bi bi-building me-2"></i><?= htmlspecialchars($inv['org_name'], ENT_QUOTES) ?>
                </h6>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-2">
                    <i class="bi bi-geo-alt me-1"></i>Barangay <?= htmlspecialchars($inv['barangay'], ENT_QUOTES) ?>
                </p>
                <p class="mb-3">
                    You have been invited as:
                    <span class="badge bg-primary ms-1"><?= htmlspecialchars($inv['role_label'], ENT_QUOTES) ?></span>
                </p>
                <div class="d-flex gap-2">
                    <form method="POST" action="<?= APP_URL ?>/org/invitation/<?= $inv['id'] ?>/respond">
                        <?= csrfField() ?>
                        <button type="submit" name="action" value="accept"
                                class="btn btn-success btn-sm"
                                onclick="return confirm('Accept invitation to join <?= htmlspecialchars($inv['org_name'], ENT_QUOTES) ?>?')">
                            <i class="bi bi-check-circle me-1"></i>Accept
                        </button>
                    </form>
                    <form method="POST" action="<?= APP_URL ?>/org/invitation/<?= $inv['id'] ?>/respond">
                        <?= csrfField() ?>
                        <button type="submit" name="action" value="decline"
                                class="btn btn-outline-danger btn-sm"
                                onclick="return confirm('Decline this invitation?')">
                            <i class="bi bi-x-circle me-1"></i>Decline
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
