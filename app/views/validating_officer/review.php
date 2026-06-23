<?php $pageTitle = $pageTitle ?? 'Review Applicant'; ?>
<?php ob_start(); ?>

<div class="row g-4">
    <!-- Left: Applicant Profile -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-dark text-white py-3">
                <h6 class="mb-0"><i class="bi bi-person-badge me-2"></i>Applicant Profile</h6>
            </div>
            <div class="card-body small">
                <?php $vs = $applicant['validation_status']; ?>
                <?php $vsColors = ['not_submitted'=>'secondary','pending'=>'warning text-dark','approved'=>'success','rejected'=>'danger','resubmit'=>'info text-dark']; ?>
                <div class="mb-3">
                    <span class="badge bg-<?= $vsColors[$vs] ?? 'secondary' ?> fs-6 mb-2">
                        <?= ucfirst($vs) ?>
                    </span>
                    <?php if (!empty($applicant['validator_remarks'])): ?>
                    <div class="alert alert-light border py-2 mt-2 small">
                        <strong>Previous Remarks:</strong><br>
                        <?= nl2br(htmlspecialchars($applicant['validator_remarks'], ENT_QUOTES)) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <?php
                $fields = [
                    'Name'             => strtoupper($applicant['surname']) . ', ' . $applicant['firstname'] . (!empty($applicant['middlename']) ? ' ' . $applicant['middlename'] : '') . (!empty($applicant['suffix']) ? ' ' . $applicant['suffix'] : ''),
                    'Email'            => $applicant['email'] ?? '—',
                    'Date of Birth'    => !empty($applicant['date_of_birth']) ? date('F d, Y', strtotime($applicant['date_of_birth'])) : '—',
                    'Sex'              => ucfirst($applicant['sex'] ?? '—'),
                    'Civil Status'     => ucfirst(str_replace('_', '-', $applicant['civil_status'] ?? '—')),
                    'Address'          => $applicant['present_address'] ?? '—',
                    'Cellphone'        => $applicant['cellphone'] ?? '—',
                    'GSIS/SSS No.'     => $applicant['gsis_sss_no'] ?? '—',
                    'PAG-IBIG No.'     => $applicant['pag_ibig_no'] ?? '—',
                    'PhilHealth No.'   => $applicant['philhealth_no'] ?? '—',
                    'Disability'       => $applicant['disability'] ?: 'None',
                    'Employment'       => $applicant['employment_status'] ?? '—',
                    'Preferred Job'    => $applicant['preferred_occupation'] ?? '—',
                    'Expected Salary'  => !empty($applicant['expected_salary']) ? 'PHP ' . $applicant['expected_salary'] : '—',
                    'Education'        => $applicant['educational_bg'] ?? '—',
                    'Work Experience'  => $applicant['work_experience'] ?? '—',
                    'Skills'           => $applicant['other_skills'] ?? '—',
                ];
                foreach ($fields as $label => $val): ?>
                <div class="row mb-1">
                    <div class="col-5 text-muted"><?= $label ?></div>
                    <div class="col-7 fw-semibold text-break"><?= nl2br(htmlspecialchars($val, ENT_QUOTES)) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Right: Documents + Validation Action -->
    <div class="col-lg-7">
        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($success, ENT_QUOTES) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
        <?php endif; ?>

        <!-- Documents -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-folder me-2 text-primary"></i>Uploaded Documents (<?= count($documents) ?>)</h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($documents)): ?>
                    <div class="text-center py-4 text-muted small">No documents uploaded yet.</div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Type</th><th>File</th><th>Size</th><th>Uploaded</th><th>View</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($documents as $d): ?>
                        <tr>
                            <td><span class="badge bg-secondary"><?= ucfirst($d['doc_type']) ?></span></td>
                            <td class="small"><?= htmlspecialchars($d['original_name'], ENT_QUOTES) ?></td>
                            <td class="small text-muted"><?= round($d['file_size']/1024) ?>KB</td>
                            <td class="small text-muted"><?= date('M d', strtotime($d['uploaded_at'])) ?></td>
                            <td>
                                <a href="<?= APP_URL . htmlspecialchars($d['file_path'], ENT_QUOTES) ?>"
                                   target="_blank" class="btn btn-xs btn-outline-primary" style="padding:2px 8px;font-size:.75rem;">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Validation Action -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-shield-check me-2 text-success"></i>Validation Decision</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/validating-officer/applicants/<?= $applicant['id'] ?>/validate">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Remarks / Notes (optional)</label>
                        <textarea name="remarks" class="form-control form-control-sm" rows="3"
                                  placeholder="Leave feedback for the applicant..."><?= htmlspecialchars($applicant['validator_remarks'] ?? '', ENT_QUOTES) ?></textarea>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" name="action" value="approve"
                                class="btn btn-success"
                                onclick="return confirm('Approve this applicant\'s documents?')">
                            <i class="bi bi-check-circle me-1"></i>Approve
                        </button>
                        <button type="submit" name="action" value="resubmit"
                                class="btn btn-info"
                                onclick="return confirm('Request resubmission from this applicant?')">
                            <i class="bi bi-arrow-clockwise me-1"></i>Request Resubmission
                        </button>
                        <button type="submit" name="action" value="reject"
                                class="btn btn-danger"
                                onclick="return confirm('Reject this applicant\'s documents?')">
                            <i class="bi bi-x-circle me-1"></i>Reject
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-3">
            <a href="<?= APP_URL ?>/validating-officer/applicants" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Back to List
            </a>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
