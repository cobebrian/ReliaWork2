<?php ob_start(); ?>

<?php if (!empty($success = getFlash('success'))): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (!empty($err = getFlash('error'))): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">

    <!-- ── Post Vacancy Form ──────────────────────────────────────────────── -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-briefcase-fill me-2 text-primary"></i>
                    Process 5 — Submit Job Vacancy
                </h6>
                <small class="text-muted">Fill in all vacancy details. Supervising Labor will be notified.</small>
            </div>
            <div class="card-body">
                <?php if (empty($myAgencies)): ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    You have no confirmed agency participation yet.
                    Please wait for the Supervising Labor to invite your agency to a job fair.
                </div>
                <?php else: ?>
                <form method="POST" action="<?= APP_URL ?>/agency/vacancies/store">
                    <?= csrfField() ?>

                    <!-- Agency / Job Fair -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">
                            Job Fair <span class="text-danger">*</span>
                        </label>
                        <select name="participating_agency_id" class="form-select" required>
                            <option value="">— Select Job Fair —</option>
                            <?php foreach ($myAgencies as $a): ?>
                            <option value="<?= $a['id'] ?>">
                                <?= htmlspecialchars($a['job_fair_title'] ?? $a['agency_name'], ENT_QUOTES, 'UTF-8') ?>
                                <span class="text-muted">(<?= ucfirst($a['status']) ?>)</span>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <hr class="my-3">
                    <p class="small fw-bold text-muted mb-3">COMPANY INFORMATION</p>

                    <!-- Company Name -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">
                            Company Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="company_name" class="form-control" required
                               placeholder="e.g. ABC Corporation">
                    </div>

                    <!-- Company Location -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Company Location</label>
                        <input type="text" name="company_location" class="form-control"
                               placeholder="City, Province">
                    </div>

                    <!-- Mobile Number -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Mobile Number</label>
                        <input type="text" name="mobile_number" class="form-control"
                               placeholder="09XX-XXX-XXXX">
                    </div>

                    <!-- Gmail Address -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Gmail Address</label>
                        <input type="email" name="gmail_address" class="form-control"
                               placeholder="company@gmail.com">
                    </div>

                    <hr class="my-3">
                    <p class="small fw-bold text-muted mb-3">VACANCY DETAILS</p>

                    <!-- Position -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">
                            Position / Job Title <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="position" class="form-control" required
                               placeholder="e.g. Customer Service Representative">
                    </div>

                    <!-- Available Slots -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">
                            Available Slots <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="available_slots" class="form-control"
                               min="1" value="1" required>
                    </div>

                    <!-- Qualifications -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Qualifications / Requirements</label>
                        <textarea name="qualifications" class="form-control" rows="4"
                                  placeholder="e.g. College graduate, at least 1 year experience, good communication skills..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="bi bi-send me-2"></i>Submit Vacancy
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── My Submitted Vacancies ─────────────────────────────────────────── -->
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
                            <tr>
                                <th>Company</th>
                                <th>Position</th>
                                <th>Slots</th>
                                <th>Status</th>
                                <th>Remarks</th>
                                <th>Submitted</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($vacancies as $v): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold small">
                                    <?= htmlspecialchars($v['company_name'], ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <?php if ($v['company_location']): ?>
                                <div class="text-muted" style="font-size:.75rem;">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    <?= htmlspecialchars($v['company_location'], ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="small"><?= htmlspecialchars($v['position'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span class="badge bg-primary"><?= $v['available_slots'] ?></span></td>
                            <td><?= statusBadge($v['status']) ?></td>
                            <td class="small">
                                <?php if ($v['remarks']): ?>
                                <span class="text-info" title="<?= htmlspecialchars($v['remarks'], ENT_QUOTES, 'UTF-8') ?>">
                                    <i class="bi bi-chat-text me-1"></i>
                                    <?= htmlspecialchars(substr($v['remarks'], 0, 40), ENT_QUOTES, 'UTF-8') ?>
                                    <?= strlen($v['remarks']) > 40 ? '...' : '' ?>
                                </span>
                                <?php else: ?>
                                <span class="text-muted">—</span>
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
