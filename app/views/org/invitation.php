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
    <div>
        <h4 class="mb-0 fw-bold"><i class="bi bi-envelope me-2 text-warning"></i>Organization Invitations</h4>
        <small class="text-muted">Review and respond to your pending invitations</small>
    </div>
</div>

<?php
// Map org role to what dashboard/access they'll get
$accessMap = [
    'punong_barangay'    => ['Barangay Captain Dashboard',  'success', 'bi-person-badge-fill'],
    'bedo_officer'       => ['BEDO Officer Dashboard',      'primary', 'bi-megaphone-fill'],
    'agency'             => ['Agency Dashboard',            'info',    'bi-building-fill'],
    'organization_admin' => ['Organization Management',     'warning', 'bi-gear-fill'],
    'member'             => ['Normal User Dashboard',       'secondary','bi-person-fill'],
];
?>

<?php if (empty($invitations)): ?>
<div class="text-center py-5 text-muted">
    <i class="bi bi-envelope-open display-3 d-block mb-3 opacity-50"></i>
    <h5>No Pending Invitations</h5>
    <p class="small">When an Organization Admin invites you, it will appear here.</p>
    <a href="<?= APP_URL ?>/org/dashboard" class="btn btn-outline-primary btn-sm">Back to Dashboard</a>
</div>
<?php else: ?>
<div class="row g-4 justify-content-center">
    <?php foreach ($invitations as $inv):
        [$accessLabel, $accessColor, $accessIcon] = $accessMap[$inv['role_name'] ?? 'member'] ?? ['Normal User Dashboard','secondary','bi-person'];
    ?>
    <div class="col-md-8 col-lg-6">
        <div class="card shadow border-0">
            <div class="card-header bg-warning text-dark py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-envelope-fill fs-4"></i>
                    <h5 class="mb-0 fw-bold">Organization Invitation</h5>
                </div>
            </div>
            <div class="card-body">
                <!-- Org details -->
                <div class="mb-4">
                    <h5 class="fw-bold"><?= htmlspecialchars($inv['org_name'], ENT_QUOTES) ?></h5>
                    <p class="text-muted mb-1">
                        <i class="bi bi-geo-alt me-1"></i>
                        Barangay <?= htmlspecialchars($inv['barangay'], ENT_QUOTES) ?>
                    </p>
                </div>

                <!-- Role being assigned -->
                <div class="bg-light rounded p-3 mb-4">
                    <div class="small text-muted mb-1">You are being invited as:</div>
                    <div class="fw-bold fs-5">
                        <span class="badge bg-primary me-2"><?= htmlspecialchars($inv['role_label'], ENT_QUOTES) ?></span>
                    </div>
                </div>

                <!-- What access they'll get -->
                <div class="alert alert-<?= $accessColor ?> d-flex gap-3 align-items-center mb-4">
                    <i class="bi <?= $accessIcon ?> fs-4 flex-shrink-0"></i>
                    <div>
                        <div class="fw-semibold">If you accept:</div>
                        <div class="small">
                            You will be redirected to the <strong><?= $accessLabel ?></strong>
                            and gain access to all features for that role.
                        </div>
                    </div>
                </div>

                <!-- Accept / Decline buttons -->
                <div class="d-flex gap-3">
                    <form method="POST" action="<?= APP_URL ?>/org/invitation/<?= $inv['id'] ?>/respond"
                          class="flex-grow-1">
                        <?= csrfField() ?>
                        <button type="submit" name="action" value="accept"
                                class="btn btn-success w-100 fw-semibold btn-lg"
                                onclick="return confirm('Accept this invitation? You will be redirected to your new dashboard.')">
                            <i class="bi bi-check-circle-fill me-2"></i>Accept Invitation
                        </button>
                    </form>
                    <form method="POST" action="<?= APP_URL ?>/org/invitation/<?= $inv['id'] ?>/respond">
                        <?= csrfField() ?>
                        <button type="submit" name="action" value="decline"
                                class="btn btn-outline-danger btn-lg"
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
