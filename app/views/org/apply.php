<?php $pageTitle = 'Apply for Barangay Organization'; ?>
<?php ob_start(); ?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= APP_URL ?>/org/dashboard" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
    <h5 class="mb-0 fw-bold"><i class="bi bi-building-add me-2 text-primary"></i>Apply for Barangay Organization</h5>
</div>

<?php if ($error): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white py-3">
                <h6 class="mb-0 fw-bold">Organization Application Form</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/org/apply" enctype="multipart/form-data">
                    <?= csrfField() ?>

                    <h6 class="fw-bold text-primary mb-3 small">Organization Information</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">Organization Name <span class="text-danger">*</span></label>
                            <input type="text" name="org_name" class="form-control form-control-sm" required
                                   placeholder="e.g. Barangay Poblacion Organization"
                                   value="<?= htmlspecialchars($old['org_name'] ?? '', ENT_QUOTES) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Barangay <span class="text-danger">*</span></label>
                            <input type="text" name="barangay" class="form-control form-control-sm" required
                                   placeholder="e.g. Poblacion"
                                   value="<?= htmlspecialchars($old['barangay'] ?? '', ENT_QUOTES) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Municipality / City</label>
                            <input type="text" name="municipality" class="form-control form-control-sm"
                                   placeholder="e.g. Toril"
                                   value="<?= htmlspecialchars($old['municipality'] ?? '', ENT_QUOTES) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Province</label>
                            <input type="text" name="province" class="form-control form-control-sm"
                                   placeholder="e.g. Davao del Sur"
                                   value="<?= htmlspecialchars($old['province'] ?? '', ENT_QUOTES) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Full Address</label>
                            <textarea name="address" class="form-control form-control-sm" rows="2"
                                      placeholder="Complete address of the barangay hall"><?= htmlspecialchars($old['address'] ?? '', ENT_QUOTES) ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Contact Email</label>
                            <input type="email" name="contact_email" class="form-control form-control-sm"
                                   value="<?= htmlspecialchars($old['contact_email'] ?? '', ENT_QUOTES) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Contact Phone</label>
                            <input type="text" name="contact_phone" class="form-control form-control-sm"
                                   value="<?= htmlspecialchars($old['contact_phone'] ?? '', ENT_QUOTES) ?>">
                        </div>
                    </div>

                    <h6 class="fw-bold text-primary mb-3 small">Supporting Documents</h6>
                    <p class="small text-muted mb-3">
                        Upload the legal documents required to verify your organization.
                        Accepted formats: PDF, Word, JPG, PNG — Max 5MB each.
                    </p>
                    <?php
                    $docDefs = [
                        'barangay_form'          => ['Barangay/Organizational Application Form', true],
                        'appointment_proof'      => ['Proof of Authority / Appointment of Secretary', true],
                        'barangay_certification' => ['Barangay Certification or Supporting Document', false],
                        'valid_id'               => ['Valid ID / Identification of Applicant', true],
                        'other'                  => ['Other Supporting Documents', false],
                    ];
                    foreach ($docDefs as $field => [$label, $required]): ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">
                            <?= $label ?>
                            <?= $required ? '<span class="text-danger">*</span>' : '<span class="text-muted">(optional)</span>' ?>
                        </label>
                        <input type="file" name="<?= $field ?>" class="form-control form-control-sm"
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                               <?= $required ? 'required' : '' ?>>
                    </div>
                    <?php endforeach; ?>

                    <div class="alert alert-info small d-flex gap-2 mt-4">
                        <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
                        <div>
                            Once submitted, the <strong>Super Admin</strong> will review your application and documents.
                            You will be notified of the decision. If approved, you will automatically become the
                            <strong>Organization Admin</strong> of your Barangay.
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-semibold">
                        <i class="bi bi-send me-2"></i>Submit Organization Application
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-2">
                <h6 class="mb-0 fw-bold small"><i class="bi bi-list-check me-2 text-success"></i>Required Documents</h6>
            </div>
            <ul class="list-group list-group-flush small">
                <?php foreach ($docDefs as $field => [$label, $req]): ?>
                <li class="list-group-item d-flex gap-2 align-items-start py-2">
                    <i class="bi <?= $req ? 'bi-record-circle-fill text-danger' : 'bi-circle text-muted' ?> mt-1 flex-shrink-0"></i>
                    <div>
                        <div class="fw-semibold"><?= $label ?></div>
                        <?= $req ? '<span class="text-danger" style="font-size:.7rem;">Required</span>' : '<span class="text-muted" style="font-size:.7rem;">Optional</span>' ?>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
