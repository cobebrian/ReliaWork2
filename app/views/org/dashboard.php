<?php $pageTitle = 'Dashboard'; ?>
<?php ob_start(); ?>

<div class="mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-person-circle me-2 text-primary"></i>Welcome, <?= htmlspecialchars(currentUser()['name'], ENT_QUOTES) ?></h4>
    <small class="text-muted">Normal User — You can apply to join a Barangay Organization below.</small>
</div>

<?php
// Check for pending invitations
$db = Database::getInstance();
$pendingInvitations = $db->fetchAll(
    "SELECT om.id, o.name AS org_name, o.barangay, r.label AS role_label
     FROM organization_memberships om
     JOIN organizations o ON o.id = om.organization_id
     JOIN org_roles r ON r.id = om.org_role_id
     WHERE om.user_id = ? AND om.status = 'invited'",
    [(int)currentUser()['id']]
);
?>

<?php if (!empty($pendingInvitations)): ?>
<div class="alert alert-warning border-0 shadow-sm mb-4 d-flex align-items-start gap-3">
    <i class="bi bi-envelope-fill text-warning fs-4 flex-shrink-0 mt-1"></i>
    <div class="flex-grow-1">
        <strong>You have <?= count($pendingInvitations) ?> pending organization invitation(s)!</strong>
        <ul class="mb-2 mt-1 small">
            <?php foreach ($pendingInvitations as $inv): ?>
            <li>
                <strong><?= htmlspecialchars($inv['org_name'], ENT_QUOTES) ?></strong>
                (Barangay <?= htmlspecialchars($inv['barangay'], ENT_QUOTES) ?>) —
                Role: <span class="badge bg-primary"><?= htmlspecialchars($inv['role_label'], ENT_QUOTES) ?></span>
            </li>
            <?php endforeach; ?>
        </ul>
        <a href="<?= APP_URL ?>/org/invitation" class="btn btn-warning btn-sm fw-semibold">
            <i class="bi bi-envelope-open me-1"></i>Review &amp; Respond to Invitations
        </a>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($notifications) && empty($pendingInvitations)): ?>
<div class="alert alert-info d-flex gap-2 mb-4">
    <i class="bi bi-bell-fill flex-shrink-0 mt-1"></i>
    <div class="small">You have <strong><?= count($notifications) ?></strong> unread notification(s).
        <a href="<?= APP_URL ?>/org/invitation" class="alert-link ms-1">Check invitations →</a>
    </div>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- Left: Status -->
    <div class="col-lg-6">
        <?php if ($membership): ?>
        <!-- Active membership -->
        <div class="card border-0 shadow-sm mb-4 border-start border-success border-4">
            <div class="card-body">
                <div class="d-flex gap-3 align-items-start">
                    <div class="bg-success-subtle text-success rounded p-2">
                        <i class="bi bi-building-check fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Active Organization Member</h6>
                        <div class="small">
                            <div><strong>Organization:</strong> <?= htmlspecialchars($membership['org_name'], ENT_QUOTES) ?></div>
                            <div><strong>Barangay:</strong> <?= htmlspecialchars($membership['barangay'], ENT_QUOTES) ?></div>
                            <div><strong>Your Role:</strong>
                                <span class="badge bg-primary"><?= htmlspecialchars($membership['role_label'], ENT_QUOTES) ?></span>
                            </div>
                        </div>
                        <?php if ($membership['role_name'] === 'organization_admin'): ?>
                        <a href="<?= APP_URL ?>/org/manage" class="btn btn-success btn-sm mt-2">
                            <i class="bi bi-gear me-1"></i>Manage Organization
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php elseif ($application): ?>
        <!-- Pending/reviewed application -->
        <?php
        $statusCfg = [
            'pending'       => ['warning', 'bi-hourglass-split',   'Application Pending',       'Your application is waiting for Super Admin review.'],
            'under_review'  => ['primary', 'bi-eye',               'Under Review',              'The Super Admin is reviewing your application.'],
            'needs_revision'=> ['info',    'bi-arrow-clockwise',   'Revision Required',         'Please re-apply with the requested changes.'],
            'rejected'      => ['danger',  'bi-x-circle-fill',     'Application Rejected',      'Your application was not approved.'],
            'approved'      => ['success', 'bi-check-circle-fill', 'Application Approved!',     'You are now an Organization Admin.'],
        ];
        [$c, $ic, $t, $d] = $statusCfg[$application['status']] ?? ['secondary','bi-question-circle','Unknown',''];
        ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body d-flex gap-3">
                <div class="bg-<?= $c ?>-subtle text-<?= $c ?> rounded p-2">
                    <i class="bi <?= $ic ?> fs-4"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-1"><?= $t ?></h6>
                    <p class="small text-muted mb-2"><?= $d ?></p>
                    <?php if (!empty($application['review_notes'])): ?>
                    <div class="alert alert-light border py-2 small mb-2">
                        <strong>Admin Notes:</strong> <?= htmlspecialchars($application['review_notes'], ENT_QUOTES) ?>
                    </div>
                    <?php endif; ?>
                    <a href="<?= APP_URL ?>/org/application/<?= $application['id'] ?>/status"
                       class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-eye me-1"></i>View Application
                    </a>
                </div>
            </div>
        </div>
        <?php else: ?>
        <!-- No application yet -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body text-center py-5">
                <i class="bi bi-building-add display-3 text-primary d-block mb-3 opacity-75"></i>
                <h5 class="fw-bold">Join a Barangay Organization</h5>
                <p class="text-muted small mb-4">
                    Apply to create your Barangay Organization. Once approved by the Super Admin,
                    you'll become the <strong>Organization Admin</strong> and can invite members and assign roles.
                </p>
                <a href="<?= APP_URL ?>/org/apply" class="btn btn-primary px-4">
                    <i class="bi bi-send me-2"></i>Apply for Barangay Organization
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Right: How it works -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-map me-2 text-primary"></i>How the Workflow Works</h6>
            </div>
            <div class="card-body p-0">
                <?php
                $steps = [
                    ['Register',                  'Your account starts as Normal User.',                                       'success'],
                    ['Apply for Organization',    'Submit your Barangay organization application with legal documents.',       'primary'],
                    ['Super Admin Review',        'The platform administrator reviews and verifies your documents.',           'warning'],
                    ['Approval',                  'Once approved, you become Organization Admin of your Barangay.',            'success'],
                    ['Invite Members',            'Invite Punong Barangay, BEDO Officer, Agency, and other members.',          'info'],
                    ['Assign Roles',              'Assign organization-level roles to your members.',                          'primary'],
                ];
                foreach ($steps as $i => [$title, $desc, $color]):
                ?>
                <div class="d-flex gap-3 px-3 py-3 border-bottom align-items-start">
                    <div class="bg-<?= $color ?>-subtle text-<?= $color ?> rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:32px;height:32px;font-weight:900;font-size:.85rem;">
                        <?= $i + 1 ?>
                    </div>
                    <div>
                        <div class="fw-semibold small"><?= $title ?></div>
                        <div class="text-muted" style="font-size:.75rem;"><?= $desc ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
