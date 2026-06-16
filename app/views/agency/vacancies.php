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
    <!-- ── Submit Vacancy Form ──────────────────────────────────────────── -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-briefcase-fill me-2 text-primary"></i>Submit Job Vacancy
                </h6>
                <small class="text-muted">Supervising Labor will be notified upon submission.</small>
            </div>
            <div class="card-body">
                <?php if (empty($myAgencies)): ?>
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    You have no <strong>confirmed</strong> invitations yet.
                    Please accept an invitation from the dashboard first.
                    <a href="<?= APP_URL ?>/agency/dashboard" class="alert-link">Go to Dashboard</a>
                </div>
                <?php else: ?>
                <form method="POST" action="<?= APP_URL ?>/agency/vacancies/store">
                    <?= csrfField() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Job Fair <span class="text-danger">*</span></label>
                        <select name="participating_agency_id" class="form-select" required>
                            <option value="">— Select Job Fair —</option>
                            <?php foreach ($myAgencies as $a): ?>
                            <option value="<?= $a['id'] ?>">
                                <?= htmlspecialchars($a['job_fair_title'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <hr class="my-3"><p class="small fw-bold text-muted mb-2">COMPANY INFORMATION</p>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Company Name <span class="text-danger">*</span></label>
                        <input type="text" name="company_name" class="form-control" required
                               value="<?= htmlspecialchars($companyName ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Company Location</label>
                        <input type="text" name="company_location" class="form-control"
                               value="<?= htmlspecialchars($companyLocation ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Mobile Number</label>
                        <input type="text" name="mobile_number" class="form-control" placeholder="09XX-XXX-XXXX">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Gmail Address</label>
                        <input type="email" name="gmail_address" class="form-control" placeholder="company@gmail.com">
                    </div>

                    <hr class="my-3"><p class="small fw-bold text-muted mb-2">VACANCY DETAILS</p>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Position / Job Title <span class="text-danger">*</span></label>
                        <input type="text" name="position" class="form-control" required placeholder="e.g. Customer Service Rep">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Available Slots <span class="text-danger">*</span></label>
                        <input type="number" name="available_slots" class="form-control" min="1" value="1" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Qualifications / Requirements</label>
                        <textarea name="qualifications" class="form-control" rows="3"
                                  placeholder="e.g. College graduate, 1 year experience..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="bi bi-send me-2"></i>Submit Vacancy
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── Submitted Vacancies ──────────────────────────────────────────── -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-briefcase me-2 text-primary"></i>
                    My Submitted Vacancies
                    <span class="badge bg-primary ms-1"><?= count($vacancies) ?></span>
                </h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($vacancies)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-briefcase display-4 d-block mb-2 opacity-50"></i>
                    No vacancies submitted yet.
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Company</th><th>Position</th><th>Slots</th><th>Status</th><th>Remarks from SL</th><th>Date</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($vacancies as $v): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold small"><?= htmlspecialchars($v['company_name'], ENT_QUOTES, 'UTF-8') ?></div>
                                <?php if ($v['company_location']): ?>
                                <div class="text-muted" style="font-size:.75rem;">
                                    <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($v['company_location'], ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="small"><?= htmlspecialchars($v['position'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span class="badge bg-primary"><?= $v['available_slots'] ?></span></td>
                            <td><?= statusBadge($v['status']) ?></td>
                            <td class="small">
                                <?php if (!empty($v['remarks'])): ?>
                                <span class="text-info" title="<?= htmlspecialchars($v['remarks'], ENT_QUOTES, 'UTF-8') ?>">
                                    <i class="bi bi-chat-quote me-1"></i>
                                    <?= htmlspecialchars(substr($v['remarks'], 0, 50), ENT_QUOTES, 'UTF-8') ?>
                                    <?= strlen($v['remarks']) > 50 ? '...' : '' ?>
                                </span>
                                <?php else: ?>
                                <span class="text-muted small">Awaiting review</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small"><?= formatDate($v['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
