<?php $pageTitle = $pageTitle ?? 'Manage Organization'; ?>
<?php ob_start(); ?>

<?php if ($success): ?><div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($success, ENT_QUOTES) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES) ?></div><?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0"><i class="bi bi-building me-2 text-primary"></i><?= htmlspecialchars($membership['org_name'], ENT_QUOTES) ?></h4>
        <small class="text-muted">Barangay <?= htmlspecialchars($membership['barangay'], ENT_QUOTES) ?> — You are the Organization Admin</small>
    </div>
    <span class="badge bg-success py-2 px-3">Organization Active</span>
</div>

<div class="row g-4">
    <!-- Invite Member -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white py-2">
                <h6 class="mb-0 small fw-bold"><i class="bi bi-person-plus me-2"></i>Invite Member</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/org/manage/invite">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Member Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control form-control-sm"
                               placeholder="registered@email.com" required>
                        <div class="form-text">The user must already be registered in the system.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Assign Role <span class="text-danger">*</span></label>
                        <select name="org_role_id" class="form-select form-select-sm" required>
                            <option value="">— Select Role —</option>
                            <?php foreach ($orgRoles as $r): ?>
                            <?php if ($r['name'] !== 'organization_admin'): // Can't invite another org admin ?>
                            <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['label'], ENT_QUOTES) ?></option>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-send me-1"></i>Send Invitation
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Members List -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between">
                <h6 class="mb-0 fw-bold"><i class="bi bi-people me-2 text-success"></i>Members (<?= count($active) ?>)</h6>
                <?php if (!empty($pending)): ?>
                <span class="badge bg-warning text-dark"><?= count($pending) ?> pending invitation(s)</span>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th class="text-center">Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($members as $m):
                        $mColors = ['active'=>'success','invited'=>'warning text-dark','inactive'=>'secondary'];
                        $mColor  = $mColors[$m['status']] ?? 'secondary';
                    ?>
                    <tr>
                        <td class="fw-semibold"><?= htmlspecialchars($m['name'], ENT_QUOTES) ?></td>
                        <td class="text-muted"><?= htmlspecialchars($m['email'], ENT_QUOTES) ?></td>
                        <td>
                            <?php if ($m['role_name'] !== 'organization_admin' && $m['status'] === 'active'): ?>
                            <form method="POST" action="<?= APP_URL ?>/org/manage/update-role" class="d-flex gap-1">
                                <?= csrfField() ?>
                                <input type="hidden" name="membership_id" value="<?= $m['id'] ?>">
                                <select name="org_role_id" class="form-select form-select-sm" style="min-width:150px"
                                        onchange="this.form.submit()">
                                    <?php foreach ($orgRoles as $r): ?>
                                    <?php if ($r['name'] !== 'organization_admin'): ?>
                                    <option value="<?= $r['id'] ?>" <?= $m['org_role_id'] == $r['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($r['label'], ENT_QUOTES) ?>
                                    </option>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                            <?php else: ?>
                            <span class="badge bg-primary"><?= htmlspecialchars($m['role_label'], ENT_QUOTES) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-<?= $mColor ?>"><?= ucfirst($m['status']) ?></span>
                        </td>
                        <td>
                            <?php if ($m['role_name'] !== 'organization_admin'): ?>
                            <form method="POST" action="<?= APP_URL ?>/org/manage/remove" class="d-inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="membership_id" value="<?= $m['id'] ?>">
                                <button type="submit" class="btn btn-xs btn-outline-danger"
                                        style="padding:2px 8px;font-size:.72rem;"
                                        onclick="return confirm('Remove this member?')">
                                    <i class="bi bi-person-x"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
