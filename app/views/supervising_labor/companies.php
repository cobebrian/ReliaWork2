<?php ob_start(); ?>

<?php if (!empty($success)): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (!empty($error)): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">

    <!-- ── Add Company Form ───────────────────────────────────────────────── -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-building-add me-2 text-primary"></i>Add Company
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/supervising-labor/companies/store">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">
                            Company Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" class="form-control form-control-sm"
                               placeholder="e.g. ABC Recruitment Corp" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Industry</label>
                        <input type="text" name="industry" class="form-control form-control-sm"
                               placeholder="e.g. IT, Healthcare, BPO">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Contact Person</label>
                        <input type="text" name="contact_person" class="form-control form-control-sm"
                               placeholder="Full name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Email</label>
                        <input type="email" name="email" class="form-control form-control-sm"
                               placeholder="company@example.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Phone</label>
                        <input type="text" name="phone" class="form-control form-control-sm"
                               placeholder="09XX-XXX-XXXX">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Address</label>
                        <textarea name="address" class="form-control form-control-sm"
                                  rows="2" placeholder="Company address"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-plus-circle me-2"></i>Add to Directory
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ── Company Directory ─────────────────────────────────────────────── -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-buildings me-2 text-primary"></i>
                    Company Directory
                    <span class="badge bg-primary ms-1"><?= count($companies) ?></span>
                </h6>
                <!-- Search -->
                <form method="GET" class="d-flex gap-2" style="width:260px;">
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Search companies..."
                           value="<?= htmlspecialchars($search ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-search"></i>
                    </button>
                    <?php if (!empty($search)): ?>
                    <a href="<?= APP_URL ?>/supervising-labor/companies"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x"></i>
                    </a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="card-body p-0">
                <?php if (empty($companies)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-buildings display-4 d-block mb-2 opacity-50"></i>
                    <?= !empty($search) ? 'No companies match your search.' : 'No companies in directory yet.' ?>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Company</th>
                                <th>Industry</th>
                                <th>Contact</th>
                                <th>Email / Phone</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($companies as $co): ?>
                        <tr>
                            <td class="fw-semibold">
                                <?= htmlspecialchars($co['name'], ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td class="text-muted small">
                                <?= htmlspecialchars($co['industry'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td class="text-muted small">
                                <?= htmlspecialchars($co['contact_person'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td class="text-muted small">
                                <?php if ($co['email']): ?>
                                <div><?= htmlspecialchars($co['email'], ENT_QUOTES, 'UTF-8') ?></div>
                                <?php endif; ?>
                                <?php if ($co['phone']): ?>
                                <div><?= htmlspecialchars($co['phone'], ENT_QUOTES, 'UTF-8') ?></div>
                                <?php endif; ?>
                                <?php if (!$co['email'] && !$co['phone']): ?>—<?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $co['status'] === 'active' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($co['status']) ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST"
                                      action="<?= APP_URL ?>/supervising-labor/companies/<?= $co['id'] ?>/delete"
                                      onsubmit="return confirm('Remove <?= htmlspecialchars(addslashes($co['name']), ENT_QUOTES, 'UTF-8') ?> from directory?')">
                                    <?= csrfField() ?>
                                    <button type="submit" class="btn btn-xs btn-outline-danger"
                                            title="Remove from directory">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <?php if (!empty($companies)): ?>
            <div class="card-footer bg-white text-end">
                <a href="<?= APP_URL ?>/supervising-labor/agencies"
                   class="btn btn-sm btn-primary">
                    <i class="bi bi-send me-1"></i>Go to Invite Companies
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
