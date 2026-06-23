<?php $pageTitle = 'Complying Requirements'; ?>
<?php ob_start(); ?>

<style>
.doc-type-card { border-left: 4px solid #0d6efd; border-radius: 0 8px 8px 0; background: #f8f9ff; }
.status-badge-not_submitted { background: #6c757d; }
.status-badge-pending       { background: #fd7e14; }
.status-badge-approved      { background: #198754; }
.status-badge-rejected      { background: #dc3545; }
.status-badge-resubmit      { background: #0dcaf0; color: #000; }
</style>

<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <?= htmlspecialchars($error, ENT_QUOTES) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success, ENT_QUOTES) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Status Banner -->
<?php
$vs = $applicant['validation_status'] ?? 'not_submitted';
$banners = [
    'not_submitted' => ['warning',  'bi-clock',            'Not Yet Submitted',   'Upload your required documents below and click Submit for Review.'],
    'pending'       => ['warning',  'bi-hourglass-split',  'Pending Validation',  'Your documents are under review by a Validating Officer. Please wait.'],
    'approved'      => ['success',  'bi-check-circle-fill','Documents Approved!', 'Your requirements have been validated. You are now eligible for agency interviews.'],
    'rejected'      => ['danger',   'bi-x-circle-fill',    'Documents Rejected',  'Your documents were rejected. Please review the remarks and resubmit.'],
    'resubmit'      => ['info',     'bi-arrow-clockwise',  'Resubmission Required','The officer requested resubmission. Upload the corrected documents and resubmit.'],
];
[$bType, $bIcon, $bTitle, $bMsg] = $banners[$vs] ?? $banners['not_submitted'];
?>
<div class="alert alert-<?= $bType ?> d-flex align-items-start gap-3 mb-4">
    <i class="bi <?= $bIcon ?> fs-4 flex-shrink-0 mt-1"></i>
    <div>
        <strong><?= $bTitle ?></strong>
        <div class="small mt-1"><?= $bMsg ?></div>
        <?php if (!empty($applicant['validator_remarks'])): ?>
            <div class="mt-2 p-2 bg-white bg-opacity-50 rounded small">
                <strong>Officer's Remarks:</strong><br>
                <?= nl2br(htmlspecialchars($applicant['validator_remarks'], ENT_QUOTES)) ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <!-- Left: Upload Documents -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-cloud-upload me-2 text-primary"></i>Upload Documents</h6>
            </div>
            <div class="card-body">
                <?php if (in_array($vs, ['not_submitted', 'resubmit', 'rejected'])): ?>
                <form method="POST" action="<?= APP_URL ?>/applicant/requirements/upload" enctype="multipart/form-data">
                    <?= csrfField() ?>
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold small">Document Type <span class="text-danger">*</span></label>
                            <select name="doc_type" class="form-select form-select-sm" required>
                                <option value="resume">Resume</option>
                                <option value="cv">Curriculum Vitae (CV)</option>
                                <option value="diploma">Diploma</option>
                                <option value="certificate">Certificate</option>
                                <option value="other">Other Document</option>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label fw-semibold small">File <span class="text-danger">*</span></label>
                            <input type="file" name="document" class="form-control form-control-sm" required
                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <div class="form-text">PDF, Word, or image. Max 5MB.</div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-upload me-1"></i>Upload Document
                            </button>
                        </div>
                    </div>
                </form>
                <hr>
                <?php endif; ?>

                <!-- Uploaded Documents List -->
                <?php
                $docTypes = ['resume' => 'Resume', 'cv' => 'Curriculum Vitae', 'diploma' => 'Diploma', 'certificate' => 'Certificate', 'other' => 'Other'];
                $byType = [];
                foreach ($documents as $d) { $byType[$d['doc_type']][] = $d; }
                ?>
                <?php if (empty($documents)): ?>
                    <div class="text-center py-4 text-muted small">
                        <i class="bi bi-file-earmark display-4 d-block mb-2 opacity-50"></i>
                        No documents uploaded yet.
                    </div>
                <?php else: ?>
                    <?php foreach ($docTypes as $type => $label): ?>
                        <?php if (!empty($byType[$type])): ?>
                        <div class="doc-type-card p-3 mb-3">
                            <div class="fw-semibold small mb-2 text-primary">
                                <i class="bi bi-file-earmark-text me-1"></i><?= $label ?>
                            </div>
                            <?php foreach ($byType[$type] as $doc): ?>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="bi bi-file-earmark text-muted"></i>
                                <a href="<?= APP_URL . htmlspecialchars($doc['file_path'], ENT_QUOTES) ?>"
                                   target="_blank" class="small text-truncate flex-grow-1">
                                    <?= htmlspecialchars($doc['original_name'], ENT_QUOTES) ?>
                                </a>
                                <span class="text-muted" style="font-size:.7rem;">
                                    <?= round($doc['file_size'] / 1024) ?>KB
                                </span>
                                <?php if (in_array($vs, ['not_submitted', 'resubmit', 'rejected'])): ?>
                                <form method="POST" action="<?= APP_URL ?>/applicant/requirements/<?= $doc['id'] ?>/delete" class="d-inline">
                                    <?= csrfField() ?>
                                    <button type="submit" class="btn btn-xs btn-outline-danger"
                                            onclick="return confirm('Remove this document?')" style="padding:1px 6px;font-size:.7rem;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right: Checklist + Submit -->
    <div class="col-lg-5">
        <!-- Requirements Checklist -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-list-check me-2 text-success"></i>Required Documents</h6>
            </div>
            <div class="card-body p-0">
                <?php
                $uploaded = array_column($documents, 'doc_type');
                $checklist = [
                    'resume'      => ['Resume', 'Your professional resume'],
                    'cv'          => ['Curriculum Vitae (CV)', 'Detailed academic/professional record'],
                    'diploma'     => ['Diploma', 'Highest educational attainment'],
                    'certificate' => ['Certificates', 'TESDA, trainings, etc. (if applicable)'],
                ];
                ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($checklist as $type => [$name, $desc]): ?>
                    <?php $done = in_array($type, $uploaded); ?>
                    <li class="list-group-item d-flex align-items-center gap-3 py-2">
                        <i class="bi <?= $done ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' ?> fs-5 flex-shrink-0"></i>
                        <div>
                            <div class="small fw-semibold"><?= $name ?></div>
                            <div class="text-muted" style="font-size:.72rem;"><?= $desc ?></div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- Submit for Review -->
        <?php if (in_array($vs, ['not_submitted', 'resubmit', 'rejected']) && count($documents) > 0): ?>
        <div class="card border-success border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-2"><i class="bi bi-send me-2 text-success"></i>Submit for Review</h6>
                <p class="small text-muted mb-3">
                    Once submitted, a Validating Officer will review your documents.
                    You won't be able to add or remove files while under review.
                </p>
                <form method="POST" action="<?= APP_URL ?>/applicant/requirements/submit">
                    <?= csrfField() ?>
                    <button type="submit" class="btn btn-success w-100"
                            onclick="return confirm('Submit all documents for validation?')">
                        <i class="bi bi-send me-2"></i>Submit for Validation
                    </button>
                </form>
            </div>
        </div>
        <?php elseif ($vs === 'approved'): ?>
        <div class="card border-0 shadow-sm bg-success bg-opacity-10">
            <div class="card-body text-center py-4">
                <i class="bi bi-check-circle-fill text-success display-4 d-block mb-2"></i>
                <h6 class="fw-bold text-success">All Set!</h6>
                <p class="small text-muted mb-3">Your documents are approved. You can now register for job fairs and apply to vacancies.</p>
                <a href="<?= APP_URL ?>/applicant/job-fairs" class="btn btn-success btn-sm me-2">
                    <i class="bi bi-calendar-event me-1"></i>View Job Fairs
                </a>
                <a href="<?= APP_URL ?>/applicant/vacancies" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-briefcase me-1"></i>Browse Jobs
                </a>
            </div>
        </div>
        <?php elseif ($vs === 'pending'): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-4 text-muted">
                <i class="bi bi-hourglass-split display-4 d-block mb-2 text-warning"></i>
                <h6>Under Review</h6>
                <p class="small">A Validating Officer is reviewing your documents. Please wait for the result.</p>
            </div>
        </div>
        <?php endif; ?>

        <!-- NSRP Download -->
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body">
                <h6 class="fw-bold mb-1"><i class="bi bi-file-earmark-pdf me-2 text-danger"></i>NSRP Form</h6>
                <p class="small text-muted mb-2">Download the official DOLE NSRP Form 1 (hard copy for walk-in).</p>
                <a href="<?= APP_URL ?>/applicant/nsrp-form-download" target="_blank"
                   class="btn btn-outline-danger btn-sm w-100">
                    <i class="bi bi-download me-1"></i>Download NSRP Form (PDF)
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
?>
