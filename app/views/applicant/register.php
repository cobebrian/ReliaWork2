<?php ob_start(); ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-person-plus me-2 text-primary"></i>Applicant Registration</h6>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4">Please fill in your personal information to complete your applicant profile.</p>

                <form method="POST" action="<?= APP_URL ?>/applicant/register/store">
                    <?= csrfField() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Surname <span class="text-danger">*</span></label>
                            <input type="text" name="surname" class="form-control" required
                                   placeholder="dela Cruz"
                                   value="<?= htmlspecialchars($old['surname'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="firstname" class="form-control" required
                                   placeholder="Juan"
                                   value="<?= htmlspecialchars($old['firstname'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Middle Name</label>
                            <input type="text" name="middlename" class="form-control"
                                   placeholder="Santos"
                                   value="<?= htmlspecialchars($old['middlename'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="fw-bold text-muted mb-3">Government IDs (Optional)</h6>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">GSIS / SSS No.</label>
                            <input type="text" name="gsis_sss_no" class="form-control"
                                   placeholder="XX-XXXXXXX-X"
                                   value="<?= htmlspecialchars($old['gsis_sss_no'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Pag-IBIG No.</label>
                            <input type="text" name="pag_ibig_no" class="form-control"
                                   placeholder="XXXX-XXXX-XXXX"
                                   value="<?= htmlspecialchars($old['pag_ibig_no'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">PhilHealth No.</label>
                            <input type="text" name="philhealth_no" class="form-control"
                                   placeholder="XX-XXXXXXXXX-X"
                                   value="<?= htmlspecialchars($old['philhealth_no'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Disability Status</label>
                        <select name="disability_status" class="form-select">
                            <option value="none" <?= ($old['disability_status'] ?? 'none') === 'none' ? 'selected' : '' ?>>No Disability</option>
                            <option value="with_disability" <?= ($old['disability_status'] ?? '') === 'with_disability' ? 'selected' : '' ?>>Person with Disability (PWD)</option>
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-2"></i>Save Profile
                        </button>
                        <a href="<?= APP_URL ?>/applicant/dashboard" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
