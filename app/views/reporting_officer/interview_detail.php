<?php $pageTitle = $pageTitle ?? 'Interview Detail'; ?>
<?php ob_start(); ?>

<div class="row g-4">
    <!-- Left: Applicant Info -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-dark text-white py-2">
                <h6 class="mb-0 small"><i class="bi bi-person me-2"></i>Applicant</h6>
            </div>
            <div class="card-body small">
                <p class="fw-bold mb-1 fs-6">
                    <?= htmlspecialchars(strtoupper($interview['surname']) . ', ' . $interview['firstname'], ENT_QUOTES) ?>
                    <?php if (!empty($interview['middlename'])): ?>
                    <?= htmlspecialchars($interview['middlename'], ENT_QUOTES) ?>
                    <?php endif; ?>
                </p>
                <p class="text-muted mb-2"><?= htmlspecialchars($interview['applicant_email'] ?? '', ENT_QUOTES) ?></p>
                <?php
                $fields = [
                    'Validation'  => ucfirst($interview['validation_status']),
                    'Preferred'   => $interview['preferred_occupation'] ?? '—',
                    'Education'   => $interview['educational_bg'] ?? '—',
                    'Experience'  => $interview['work_experience'] ?? '—',
                    'Skills'      => $interview['other_skills'] ?? '—',
                    'GSIS/SSS'    => $interview['gsis_sss_no'] ?? '—',
                    'PAG-IBIG'    => $interview['pag_ibig_no'] ?? '—',
                    'PhilHealth'  => $interview['philhealth_no'] ?? '—',
                ];
                foreach ($fields as $lbl => $val): ?>
                <div class="row mb-1">
                    <div class="col-5 text-muted"><?= $lbl ?></div>
                    <div class="col-7 fw-semibold text-break"><?= nl2br(htmlspecialchars($val, ENT_QUOTES)) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Documents -->
        <?php if (!empty($documents)): ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2">
                <h6 class="mb-0 small fw-bold"><i class="bi bi-folder me-2"></i>Documents</h6>
            </div>
            <div class="card-body p-0">
                <?php foreach ($documents as $doc): ?>
                <a href="<?= APP_URL . htmlspecialchars($doc['file_path'], ENT_QUOTES) ?>"
                   target="_blank"
                   class="d-flex align-items-center gap-2 px-3 py-2 border-bottom text-decoration-none text-dark small">
                    <i class="bi bi-file-earmark-text text-primary"></i>
                    <span class="flex-grow-1 text-truncate"><?= htmlspecialchars($doc['original_name'], ENT_QUOTES) ?></span>
                    <span class="badge bg-secondary"><?= ucfirst($doc['doc_type']) ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Interview Meta -->
        <div class="card border-0 shadow-sm">
            <div class="card-body small">
                <div class="mb-1"><span class="text-muted">Agency:</span> <?= htmlspecialchars($interview['agency_name'], ENT_QUOTES) ?></div>
                <div class="mb-1"><span class="text-muted">Job Fair:</span> <?= htmlspecialchars($interview['fair_title'], ENT_QUOTES) ?></div>
                <div class="mb-1"><span class="text-muted">Scheduled:</span>
                    <?= !empty($interview['scheduled_at']) ? date('F d, Y g:i A', strtotime($interview['scheduled_at'])) : '—' ?>
                </div>
                <div class="mb-1"><span class="text-muted">Completed:</span>
                    <?= !empty($interview['completed_at']) ? date('F d, Y', strtotime($interview['completed_at'])) : '—' ?>
                </div>
                <?php
                $sColors = ['scheduled'=>'warning','in_progress'=>'primary','completed'=>'success','cancelled'=>'secondary'];
                $sColor  = $sColors[$interview['status']] ?? 'secondary';
                ?>
                <div><span class="text-muted">Status:</span>
                    <span class="badge bg-<?= $sColor ?>"><?= ucfirst($interview['status']) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Questions + Outcome Management -->
    <div class="col-lg-8">
        <!-- Interview Questions (read-only) -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-list-check me-2 text-success"></i>Interview Questions (<?= count($questions) ?>)
                </h6>
            </div>
            <div class="card-body">
                <?php if (empty($questions)): ?>
                    <div class="text-center py-3 text-muted small">No questions recorded.</div>
                <?php else: ?>
                <?php foreach ($questions as $i => $q):
                    $evalColors = ['answered'=>'success','needs_improvement'=>'warning','not_answered'=>'danger'];
                    $evalLabels = ['answered'=>'✓ Answered Satisfactorily','needs_improvement'=>'⚠ Needs Improvement','not_answered'=>'✗ Not Answered'];
                    $ec = $evalColors[$q['answer_status']] ?? 'secondary';
                    $el = $evalLabels[$q['answer_status']] ?? 'Not Evaluated';
                ?>
                <div class="border rounded p-3 mb-3 bg-light">
                    <div class="d-flex gap-2 mb-2">
                        <span class="badge bg-light text-dark border flex-shrink-0">Q<?= $i+1 ?></span>
                        <div class="fw-semibold small flex-grow-1">
                            <?= htmlspecialchars($q['question_text'], ENT_QUOTES) ?>
                        </div>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <span class="badge bg-<?= $ec ?>"><?= $el ?></span>
                        <?php if (!empty($q['remarks'])): ?>
                        <small class="text-muted fst-italic">"<?= htmlspecialchars($q['remarks'], ENT_QUOTES) ?>"</small>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>

                <?php if (!empty($interview['overall_remarks'])): ?>
                <div class="alert alert-light border mt-3 mb-0">
                    <strong class="small">Agency's Overall Remarks:</strong><br>
                    <em>"<?= nl2br(htmlspecialchars($interview['overall_remarks'], ENT_QUOTES)) ?>"</em>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Hiring Outcome Management -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-2">
                <h6 class="mb-0 fw-bold"><i class="bi bi-flag-fill me-2 text-primary"></i>Hiring Outcome</h6>
            </div>
            <div class="card-body">
                <?php
                $oColors = ['hired'=>'success','not_hired'=>'danger','for_consideration'=>'info','pending'=>'warning'];
                $oColor  = $oColors[$interview['hiring_outcome']] ?? 'secondary';
                ?>
                <div class="mb-3 d-flex align-items-center gap-3">
                    <span class="badge bg-<?= $oColor ?> fs-6 px-3 py-2">
                        <?= ucfirst(str_replace('_',' ', $interview['hiring_outcome'])) ?>
                    </span>
                    <?php if (!empty($interview['hiring_remarks'])): ?>
                    <span class="small text-muted fst-italic">
                        "<?= htmlspecialchars($interview['hiring_remarks'], ENT_QUOTES) ?>"
                    </span>
                    <?php endif; ?>
                </div>

                <form method="POST" action="<?= APP_URL ?>/reporting-officer/interview/<?= $interview['id'] ?>/update-outcome">
                    <?= csrfField() ?>
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold small">Update Outcome</label>
                            <select name="hiring_outcome" class="form-select form-select-sm">
                                <?php foreach (['pending'=>'Pending','hired'=>'Hired','not_hired'=>'Not Hired','for_consideration'=>'For Consideration'] as $v => $l): ?>
                                <option value="<?= $v ?>" <?= $interview['hiring_outcome'] === $v ? 'selected' : '' ?>>
                                    <?= $l ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label fw-semibold small">Remarks</label>
                            <input type="text" name="hiring_remarks" class="form-control form-control-sm"
                                   value="<?= htmlspecialchars($interview['hiring_remarks'] ?? '', ENT_QUOTES) ?>"
                                   placeholder="Optional notes...">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-sm"
                                    onclick="return confirm('Update hiring outcome?')">
                                <i class="bi bi-save me-1"></i>Update Outcome &amp; Notify Applicant
                            </button>
                        </div>
                    </div>
                </form>

                <?php if (!empty($interview['reported_at'])): ?>
                <div class="text-muted small mt-2">
                    Last updated <?= date('F d, Y g:i A', strtotime($interview['reported_at'])) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-3">
            <a href="<?= APP_URL ?>/reporting-officer/interviews" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Back to Interviews
            </a>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
