<?php $pageTitle = $pageTitle ?? 'Review Application'; ?>
<?php ob_start(); ?>

<div class="row g-4">
    <!-- Left: Applicant Profile + Vacancy Details -->
    <div class="col-lg-5">
        <!-- Application Info -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-primary text-white py-2">
                <h6 class="mb-0 small"><i class="bi bi-briefcase me-2"></i>Application Details</h6>
            </div>
            <div class="card-body small">
                <?php
                $vsColors = [
                    'pending_validation'=>'warning text-dark',
                    'approved'          =>'success',
                    'rejected'          =>'danger',
                    'resubmit'          =>'info text-dark',
                ];
                $vs = $application['validation_status'];
                ?>
                <span class="badge bg-<?= $vsColors[$vs] ?? 'secondary' ?> fs-6 mb-2 d-inline-block">
                    <?= ucfirst(str_replace('_',' ',$vs)) ?>
                </span>
                <?php if (!empty($application['validator_remarks'])): ?>
                <div class="alert alert-light border py-2 small mb-2">
                    <strong>Previous Remarks:</strong><br>
                    <?= nl2br(htmlspecialchars($application['validator_remarks'], ENT_QUOTES)) ?>
                </div>
                <?php endif; ?>

                <div class="mb-2">
                    <div class="text-muted small">Position Applied</div>
                    <div class="fw-bold"><?= htmlspecialchars($application['position'], ENT_QUOTES) ?></div>
                </div>
                <div class="mb-2">
                    <div class="text-muted small">Company / Agency</div>
                    <div class="fw-semibold"><?= htmlspecialchars($application['agency_name'], ENT_QUOTES) ?></div>
                    <?php if (!empty($application['company_location'])): ?>
                    <div class="text-muted"><?= htmlspecialchars($application['company_location'], ENT_QUOTES) ?></div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($application['fair_title'])): ?>
                <div class="mb-2">
                    <div class="text-muted small">Job Fair</div>
                    <div><?= htmlspecialchars($application['fair_title'], ENT_QUOTES) ?>
                        <?= !empty($application['requested_date']) ? '(' . date('M d, Y', strtotime($application['requested_date'])) . ')' : '' ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($application['qualifications'])): ?>
                <hr class="my-2">
                <div class="text-muted small">Required Qualifications</div>
                <div><?= nl2br(htmlspecialchars($application['qualifications'], ENT_QUOTES)) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Applicant Profile -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-dark text-white py-2">
                <h6 class="mb-0 small"><i class="bi bi-person me-2"></i>Applicant Profile</h6>
            </div>
            <div class="card-body small">
                <?php
                $fields = [
                    'Name'           => strtoupper($application['surname']) . ', ' . $application['firstname']
                                        . (!empty($application['middlename']) ? ' ' . $application['middlename'] : ''),
                    'Email'          => $application['applicant_email'] ?? '—',
                    'Date of Birth'  => !empty($application['date_of_birth']) ? date('F d, Y', strtotime($application['date_of_birth'])) : '—',
                    'Sex'            => ucfirst($application['sex'] ?? '—'),
                    'Civil Status'   => ucfirst(str_replace('_','-',$application['civil_status'] ?? '—')),
                    'Contact'        => $application['cellphone'] ?? '—',
                    'Address'        => $application['present_address'] ?? '—',
                    'Disability'     => $application['disability'] ?: 'None',
                    'Employment'     => $application['employment_status'] ?? '—',
                    'Preferred Job'  => $application['preferred_occupation'] ?? '—',
                    'Education'      => $application['educational_bg'] ?? '—',
                    'Experience'     => $application['work_experience'] ?? '—',
                    'Skills'         => $application['other_skills'] ?? '—',
                ];
                foreach ($fields as $lbl => $val): ?>
                <div class="row mb-1">
                    <div class="col-5 text-muted"><?= $lbl ?></div>
                    <div class="col-7 fw-semibold text-break small">
                        <?= nl2br(htmlspecialchars($val, ENT_QUOTES)) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Right: Documents + Validation Decision -->
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

        <!-- Submitted Documents -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-folder me-2 text-primary"></i>Submitted Documents (<?= count($documents) ?>)
                </h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($documents)): ?>
                    <div class="text-center py-4 text-muted small">No documents uploaded yet.</div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Type</th><th>File Name</th><th>Size</th><th>Uploaded</th><th>View</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($documents as $d): ?>
                        <tr>
                            <td>
                                <span class="badge bg-secondary"><?= ucfirst($d['doc_type']) ?></span>
                            </td>
                            <td class="small"><?= htmlspecialchars($d['original_name'], ENT_QUOTES) ?></td>
                            <td class="small text-muted"><?= round($d['file_size']/1024) ?> KB</td>
                            <td class="small text-muted"><?= date('M d, Y', strtotime($d['uploaded_at'])) ?></td>
                            <td>
                                <a href="<?= APP_URL . htmlspecialchars($d['file_path'], ENT_QUOTES) ?>"
                                   target="_blank"
                                   class="btn btn-xs btn-outline-primary" style="padding:2px 8px;font-size:.75rem;">
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

        <!-- Qualification Match Checklist -->
        <?php if (!empty($application['qualifications'])): ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2">
                <h6 class="mb-0 fw-bold small">
                    <i class="bi bi-clipboard-check me-2 text-success"></i>Qualification Requirements
                </h6>
            </div>
            <div class="card-body py-2 small">
                <?= nl2br(htmlspecialchars($application['qualifications'], ENT_QUOTES)) ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Validation Decision -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-shield-check me-2 text-success"></i>Validation Decision
                </h6>
            </div>
            <div class="card-body">
                <form method="POST"
                      action="<?= APP_URL ?>/validating-officer/applications/<?= $application['id'] ?>/validate">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Remarks / Notes (optional)</label>
                        <textarea name="remarks" class="form-control form-control-sm" rows="3"
                                  placeholder="Leave feedback for the applicant or note any missing requirements..."
                        ><?= htmlspecialchars($application['validator_remarks'] ?? '', ENT_QUOTES) ?></textarea>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" name="action" value="approve"
                                class="btn btn-success"
                                onclick="return confirm('Approve this application? The agency will be notified.')">
                            <i class="bi bi-check-circle me-1"></i>Approve — Ready for Interview
                        </button>
                        <button type="submit" name="action" value="resubmit"
                                class="btn btn-info"
                                onclick="return confirm('Request resubmission from this applicant?')">
                            <i class="bi bi-arrow-clockwise me-1"></i>Request Resubmission
                        </button>
                        <button type="submit" name="action" value="reject"
                                class="btn btn-danger"
                                onclick="return confirm('Reject this application?')">
                            <i class="bi bi-x-circle me-1"></i>Reject
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-3">
            <a href="<?= APP_URL ?>/validating-officer/applicants"
               class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Back to List
            </a>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
