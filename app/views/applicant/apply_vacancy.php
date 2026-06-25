<?php $pageTitle = $pageTitle ?? 'Apply for Position'; ?>
<?php ob_start(); ?>

<style>
.upload-box { border: 2px dashed #dee2e6; border-radius: 8px; padding: 1rem; text-align: center; transition: border-color .2s; cursor: pointer; }
.upload-box:hover, .upload-box.has-file { border-color: #0d6efd; background: #f0f7ff; }
.upload-box input[type=file] { position: absolute; opacity: 0; width: 100%; height: 100%; top: 0; left: 0; cursor: pointer; }
.upload-box .rel { position: relative; }
</style>

<!-- Breadcrumb / Steps -->
<div class="d-flex gap-2 align-items-center mb-4 small flex-wrap">
    <a href="<?= APP_URL ?>/applicant/job-fairs/<?= (int)$post['id'] ?>/companies" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
    <span class="badge bg-success py-2 px-3">✓ Registered</span>
    <i class="bi bi-chevron-right text-muted"></i>
    <span class="badge bg-success py-2 px-3">✓ Company</span>
    <i class="bi bi-chevron-right text-muted"></i>
    <span class="badge bg-success py-2 px-3">✓ Vacancy</span>
    <i class="bi bi-chevron-right text-muted"></i>
    <span class="badge bg-primary py-2 px-3">4 Upload &amp; Submit</span>
</div>

<?php if ($error): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
<?php endif; ?>

<div class="row g-4">
    <!-- Left: Vacancy Details -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-primary text-white py-2">
                <h6 class="mb-0"><i class="bi bi-briefcase me-2"></i>Position Details</h6>
            </div>
            <div class="card-body small">
                <h5 class="fw-bold"><?= htmlspecialchars($vacancy['position'], ENT_QUOTES) ?></h5>
                <p class="text-primary fw-semibold mb-1">
                    <i class="bi bi-building me-1"></i><?= htmlspecialchars($vacancy['agency_name'], ENT_QUOTES) ?>
                </p>
                <?php if (!empty($vacancy['agency_location'])): ?>
                <p class="text-muted mb-1">
                    <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($vacancy['agency_location'], ENT_QUOTES) ?>
                </p>
                <?php endif; ?>
                <p class="text-muted mb-2">
                    <i class="bi bi-people me-1"></i><?= $vacancy['available_slots'] ?> slot(s) available
                </p>
                <?php if (!empty($vacancy['qualifications'])): ?>
                <hr class="my-2">
                <strong>Qualifications:</strong>
                <p class="text-muted mb-0"><?= nl2br(htmlspecialchars($vacancy['qualifications'], ENT_QUOTES)) ?></p>
                <?php endif; ?>
                <?php if (!empty($vacancy['mobile_number'])): ?>
                <hr class="my-2">
                <p class="mb-1"><i class="bi bi-phone me-1"></i><?= htmlspecialchars($vacancy['mobile_number'], ENT_QUOTES) ?></p>
                <?php endif; ?>
                <?php if (!empty($vacancy['gmail_address'])): ?>
                <p class="mb-0"><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($vacancy['gmail_address'], ENT_QUOTES) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Requirements checklist -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-2">
                <h6 class="mb-0 small fw-bold"><i class="bi bi-list-check me-2 text-success"></i>What to Upload</h6>
            </div>
            <ul class="list-group list-group-flush small">
                <li class="list-group-item d-flex gap-2 align-items-center">
                    <i class="bi bi-file-earmark-text text-danger"></i>
                    <div><strong>Resume</strong> <span class="text-danger">*</span><br><span class="text-muted">Required</span></div>
                </li>
                <li class="list-group-item d-flex gap-2 align-items-center">
                    <i class="bi bi-file-earmark-person text-primary"></i>
                    <div><strong>Curriculum Vitae (CV)</strong><br><span class="text-muted">Recommended</span></div>
                </li>
                <li class="list-group-item d-flex gap-2 align-items-center">
                    <i class="bi bi-award text-warning"></i>
                    <div><strong>Diploma</strong><br><span class="text-muted">Recommended</span></div>
                </li>
                <li class="list-group-item d-flex gap-2 align-items-center">
                    <i class="bi bi-patch-check text-success"></i>
                    <div><strong>Certificates</strong><br><span class="text-muted">Optional</span></div>
                </li>
            </ul>
            <div class="card-footer text-muted small">
                Accepted: PDF, Word, JPG, PNG &bull; Max 5MB each
            </div>
        </div>
    </div>

    <!-- Right: Upload Form -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-dark text-white py-3">
                <h5 class="mb-0"><i class="bi bi-cloud-upload me-2"></i>Upload Requirements &amp; Submit Application</h5>
            </div>
            <div class="card-body">
                <form method="POST"
                      action="<?= APP_URL ?>/applicant/job-fairs/<?= (int)$post['id'] ?>/apply/<?= (int)$vacancy['id'] ?>/store"
                      enctype="multipart/form-data">
                    <?= csrfField() ?>

                    <div class="row g-3 mb-4">
                        <?php
                        $docDefs = [
                            'resume'      => ['Resume',              'bi-file-earmark-text',   'danger',  true],
                            'cv'          => ['Curriculum Vitae',    'bi-file-earmark-person', 'primary', false],
                            'diploma'     => ['Diploma',             'bi-award',               'warning', false],
                            'certificate' => ['Certificate(s)',      'bi-patch-check',          'success', false],
                        ];
                        foreach ($docDefs as $field => [$label, $icon, $color, $required]):
                            // Pre-fill from existing global documents
                            $existing = null;
                            foreach ($existingDocs as $ed) {
                                if ($ed['doc_type'] === $field) { $existing = $ed; break; }
                            }
                        ?>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">
                                <i class="bi <?= $icon ?> text-<?= $color ?> me-1"></i>
                                <?= $label ?>
                                <?php if ($required): ?><span class="text-danger">*</span><?php endif; ?>
                            </label>
                            <div class="upload-box" id="box_<?= $field ?>">
                                <div class="rel">
                                    <input type="file" name="<?= $field ?>" id="file_<?= $field ?>"
                                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                           onchange="markBox('<?= $field ?>', this)"
                                           <?= $required ? 'required' : '' ?>>
                                    <div id="label_<?= $field ?>">
                                        <?php if ($existing): ?>
                                        <i class="bi bi-check-circle text-success fs-4 d-block mb-1"></i>
                                        <div class="small text-success fw-semibold">Re-upload or use existing</div>
                                        <div class="text-muted" style="font-size:.72rem;"><?= htmlspecialchars($existing['original_name'], ENT_QUOTES) ?></div>
                                        <?php else: ?>
                                        <i class="bi bi-cloud-arrow-up fs-4 text-muted d-block mb-1"></i>
                                        <div class="small text-muted">Click to upload <?= $label ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Additional Notes (optional)</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="2"
                                  placeholder="Any additional information for the company..."></textarea>
                    </div>

                    <div class="alert alert-info small d-flex gap-2 align-items-start mb-3">
                        <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
                        <div>
                            After submitting, a <strong>Validating Officer</strong> will review your documents.
                            Once approved, the company will be notified and you'll be moved into their
                            <strong>Interview Queue</strong>.
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold"
                            onclick="return confirm('Submit application for <?= htmlspecialchars($vacancy['position'], ENT_QUOTES) ?>?')">
                        <i class="bi bi-send-fill me-2"></i>Submit Application &amp; Requirements
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function markBox(field, input) {
    const box   = document.getElementById('box_' + field);
    const label = document.getElementById('label_' + field);
    if (input.files && input.files[0]) {
        box.classList.add('has-file');
        label.innerHTML = '<i class="bi bi-check-circle-fill text-success fs-4 d-block mb-1"></i>'
            + '<div class="small text-success fw-semibold">' + input.files[0].name + '</div>'
            + '<div class="text-muted" style="font-size:.7rem;">' + (input.files[0].size/1024).toFixed(0) + ' KB</div>';
    }
}
</script>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
