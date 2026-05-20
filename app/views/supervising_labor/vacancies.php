<?php ob_start(); ?>
<div class="row g-4">
    <!-- Add Vacancy Form -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-briefcase-fill me-2 text-primary"></i>Add Vacancy</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/supervising-labor/vacancies/store">
                    <?= csrfField() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Agency <span class="text-danger">*</span></label>
                        <select name="participating_agency_id" class="form-select" required>
                            <option value="">— Select Agency —</option>
                            <?php foreach ($agencies as $a): ?>
                                <option value="<?= $a['id'] ?>">
                                    <?= htmlspecialchars($a['agency_name'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Company Name <span class="text-danger">*</span></label>
                        <input type="text" name="company_name" class="form-control" required placeholder="Company name">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Position <span class="text-danger">*</span></label>
                        <input type="text" name="position" class="form-control" required placeholder="Job title">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Available Slots</label>
                        <input type="number" name="available_slots" class="form-control" min="1" value="1">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Company Location</label>
                        <input type="text" name="company_location" class="form-control" placeholder="City, Province">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Mobile Number</label>
                        <input type="text" name="mobile_number" class="form-control" placeholder="09XX-XXX-XXXX">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Gmail Address</label>
                        <input type="email" name="gmail_address" class="form-control" placeholder="company@gmail.com">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Qualifications</label>
                        <textarea name="qualifications" class="form-control" rows="3"
                                  placeholder="Required qualifications..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-plus-circle me-2"></i>Add Vacancy
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Vacancies List -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-briefcase me-2 text-primary"></i>All Vacancies (<?= count($vacancies) ?>)</h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($vacancies)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-briefcase display-4 d-block mb-2"></i>No vacancies yet.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Company</th>
                                    <th>Position</th>
                                    <th>Slots</th>
                                    <th>Location</th>
                                    <th>Agency</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($vacancies as $v): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= htmlspecialchars($v['company_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($v['position'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><span class="badge bg-primary"><?= $v['available_slots'] ?></span></td>
                                        <td class="text-muted small"><?= htmlspecialchars($v['company_location'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="text-muted small"><?= htmlspecialchars($v['agency_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= statusBadge($v['status']) ?></td>
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
