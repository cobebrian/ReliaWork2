<?php ob_start(); ?>
<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="<?= APP_URL ?>/admin/users" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold small">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Name or email..."
                       value="<?= htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Role</label>
                <select name="role" class="form-select">
                    <option value="">All Roles</option>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= $r ?>" <?= ($_GET['role'] ?? '') === $r ? 'selected' : '' ?>>
                            <?= htmlspecialchars($roleLabels[$r] ?? $r, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="pending"  <?= ($_GET['status'] ?? '') === 'pending'  ? 'selected' : '' ?>>Pending</option>
                    <option value="approved" <?= ($_GET['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="rejected" <?= ($_GET['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-people-fill me-2 text-primary"></i>All Users (<?= count($users) ?>)</h6>
    </div>
    <div class="card-body p-0">
        <?php if (empty($users)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-people display-4 d-block mb-2"></i>No users found.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $i => $u): ?>
                            <tr>
                                <td class="text-muted small"><?= $i + 1 ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-circle"><?= strtoupper(substr($u['name'], 0, 1)) ?></div>
                                        <span class="fw-semibold"><?= htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                </td>
                                <td class="text-muted"><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?php if ($u['role']): ?>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($roleLabels[$u['role']] ?? $u['role'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= statusBadge($u['status']) ?></td>
                                <td class="text-muted small"><?= formatDate($u['created_at']) ?></td>
                                <td>
                                    <div class="d-flex gap-1 flex-wrap">
                                        <!-- Update Role -->
                                        <form method="POST" action="<?= APP_URL ?>/admin/users/<?= $u['id'] ?>/role"
                                              class="d-flex gap-1">
                                            <?= csrfField() ?>
                                            <select name="role" class="form-select form-select-sm" style="min-width:130px">
                                                <option value="">— Role —</option>
                                                <?php foreach ($roles as $r): ?>
                                                    <option value="<?= $r ?>" <?= $u['role'] === $r ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($roleLabels[$r] ?? $r, ENT_QUOTES, 'UTF-8') ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-outline-primary"
                                                    onclick="return confirm('Update role?')">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        </form>
                                        <?php if ($u['status'] === 'pending'): ?>
                                            <form method="POST" action="<?= APP_URL ?>/admin/users/<?= $u['id'] ?>/reject">
                                                <?= csrfField() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Reject this user?')">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
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
