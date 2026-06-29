<?php $pageTitle = $pageTitle ?? 'Interview Evaluation'; ?>
<?php ob_start(); ?>

<style>
.q-card { border-left: 4px solid #dee2e6; border-radius: 0 8px 8px 0; transition: border-color .2s; }
.q-card.excellent  { border-color: #198754; background: #f0fff4; }
.q-card.good       { border-color: #0d6efd; background: #f0f7ff; }
.q-card.fair       { border-color: #fd7e14; background: #fff8f0; }
.q-card.poor       { border-color: #dc3545; background: #fff5f5; }
.q-card.not_answered { border-color: #6c757d; background: #f8f9fa; }
.rating-btn { border-radius: 20px; font-size: .75rem; padding: 3px 12px; }
.score-badge { font-size: .65rem; }
</style>

<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show">
    <?= htmlspecialchars($success, ENT_QUOTES) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
<?php endif; ?>

<div class="row g-4">
    <!-- Left: Applicant Profile -->
    <div class="col-lg-4">
        <!-- Applicant -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-dark text-white py-2">
                <h6 class="mb-0 small"><i class="bi bi-person me-2"></i>Applicant</h6>
            </div>
            <div class="card-body small">
                <p class="fw-bold mb-1 fs-6">
                    <?= htmlspecialchars(strtoupper($interview['surname']) . ', ' . $interview['firstname'], ENT_QUOTES) ?>
                </p>
                <p class="text-muted mb-2 small"><?= htmlspecialchars($interview['applicant_email'] ?? '', ENT_QUOTES) ?></p>
                <?php if (!empty($interview['preferred_occupation'])): ?>
                <div class="mb-1"><span class="text-muted">Preferred:</span> <?= htmlspecialchars($interview['preferred_occupation'], ENT_QUOTES) ?></div>
                <?php endif; ?>
                <?php if (!empty($interview['educational_bg'])): ?>
                <div class="mb-1"><span class="text-muted">Education:</span> <?= nl2br(htmlspecialchars($interview['educational_bg'], ENT_QUOTES)) ?></div>
                <?php endif; ?>
                <?php if (!empty($interview['work_experience'])): ?>
                <div class="mb-1"><span class="text-muted">Experience:</span> <?= nl2br(htmlspecialchars($interview['work_experience'], ENT_QUOTES)) ?></div>
                <?php endif; ?>
                <?php if (!empty($interview['other_skills'])): ?>
                <div><span class="text-muted">Skills:</span> <?= htmlspecialchars($interview['other_skills'], ENT_QUOTES) ?></div>
                <?php endif; ?>
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
                    <span class="flex-grow-1 text-truncate">
                        <?= htmlspecialchars($doc['original_name'], ENT_QUOTES) ?>
                    </span>
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
                <div class="mb-1"><span class="text-muted">Fair:</span> <?= htmlspecialchars($interview['fair_title'], ENT_QUOTES) ?></div>
                <div class="mb-1"><span class="text-muted">Scheduled:</span>
                    <?= !empty($interview['scheduled_at']) ? date('F d, Y g:i A', strtotime($interview['scheduled_at'])) : '—' ?>
                </div>
                <?php
                $sColors = ['scheduled'=>'warning text-dark','in_progress'=>'primary','completed'=>'success','cancelled'=>'secondary'];
                $sColor  = $sColors[$interview['status']] ?? 'secondary';
                ?>
                <div class="mb-1"><span class="text-muted">Status:</span>
                    <span class="badge bg-<?= $sColor ?>"><?= ucfirst($interview['status']) ?></span>
                </div>
                <?php if (!empty($interview['score_summary'])): ?>
                <div class="mt-2 alert alert-light border py-2 small mb-0">
                    <i class="bi bi-graph-up me-1 text-primary"></i>
                    <?= htmlspecialchars($interview['score_summary'], ENT_QUOTES) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right: Questions + Evaluation -->
    <div class="col-lg-8">
        <!-- Add Custom Question -->
        <?php if ($interview['status'] !== 'completed'): ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold small">
                    <i class="bi bi-plus-circle me-2 text-primary"></i>Add Job-Specific Question
                </h6>
                <span class="badge bg-light text-muted border"><?= count($questions) ?> questions</span>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/agency/interviews/<?= $interview['id'] ?>/add-question"
                      class="d-flex gap-2">
                    <?= csrfField() ?>
                    <input type="text" name="question_text" class="form-control form-control-sm"
                           placeholder="e.g. Describe your experience with sales targets..." required>
                    <button type="submit" class="btn btn-primary btn-sm flex-shrink-0">
                        <i class="bi bi-plus me-1"></i>Add
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Rating Legend -->
        <div class="d-flex gap-2 flex-wrap mb-3 small">
            <span class="badge bg-success px-2 py-1">● Excellent (4pts)</span>
            <span class="badge bg-primary px-2 py-1">● Good (3pts)</span>
            <span class="badge bg-warning text-dark px-2 py-1">● Fair (2pts)</span>
            <span class="badge bg-danger px-2 py-1">● Poor (1pt)</span>
            <span class="badge bg-secondary px-2 py-1">● Not Answered (0)</span>
        </div>

        <!-- Questions -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2">
                <h6 class="mb-0 fw-bold small">
                    <i class="bi bi-list-check me-2 text-success"></i>Interview Questions &amp; Evaluation
                </h6>
            </div>
            <div class="card-body">
                <?php if (empty($questions)): ?>
                    <div class="text-center py-3 text-muted small">No questions loaded.</div>
                <?php else: ?>
                <form method="POST"
                      action="<?= APP_URL ?>/agency/interviews/<?= $interview['id'] ?>/save-evaluations">
                    <?= csrfField() ?>
                    <?php
                    $scoreMap   = ['excellent'=>4,'good'=>3,'fair'=>2,'poor'=>1,'not_answered'=>0];
                    $totalScore = 0; $maxScore = count($questions) * 4;
                    foreach ($questions as $q) {
                        $totalScore += $scoreMap[$q['answer_status'] ?? ''] ?? 0;
                    }
                    $pct = $maxScore > 0 ? round(($totalScore/$maxScore)*100) : 0;
                    ?>
                    <!-- Running score bar -->
                    <div class="d-flex align-items-center gap-2 mb-3 small">
                        <span class="text-muted">Current Score:</span>
                        <strong><?= $totalScore ?>/<?= $maxScore ?></strong>
                        <div class="progress flex-grow-1" style="height:8px;">
                            <div class="progress-bar bg-<?= $pct>=75?'success':($pct>=50?'primary':($pct>=25?'warning':'danger')) ?>"
                                 style="width:<?= $pct ?>%"></div>
                        </div>
                        <span class="fw-bold <?= $pct>=75?'text-success':($pct>=50?'text-primary':'text-danger') ?>">
                            <?= $pct ?>%
                        </span>
                    </div>

                    <?php foreach ($questions as $i => $q):
                        $cur      = $q['answer_status'] ?? '';
                        $cardClass = $cur ? str_replace('_','',$cur) : '';
                    ?>
                    <div class="q-card p-3 mb-3 <?= $cur ?>" id="qcard_<?= $q['id'] ?>">
                        <div class="d-flex align-items-start gap-2 mb-2">
                            <span class="badge bg-light text-dark border flex-shrink-0 mt-1">
                                <?= $q['is_default'] ? 'Q'.($i+1) : '★' ?>
                            </span>
                            <div class="fw-semibold small flex-grow-1">
                                <?= htmlspecialchars($q['question_text'], ENT_QUOTES) ?>
                            </div>
                            <?php if ($interview['status'] !== 'completed' && !$q['is_default']): ?>
                            <form method="POST"
                                  action="<?= APP_URL ?>/agency/interviews/questions/<?= $q['id'] ?>/delete"
                                  class="d-inline flex-shrink-0">
                                <?= csrfField() ?>
                                <button type="submit"
                                        class="btn btn-xs btn-outline-danger"
                                        style="padding:1px 6px;font-size:.7rem;"
                                        onclick="return confirm('Remove this question?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>

                        <?php if ($interview['status'] !== 'completed'): ?>
                        <!-- Rating Buttons -->
                        <div class="d-flex flex-wrap gap-1 mb-2">
                            <?php foreach (['excellent'=>['success','4pts'],'good'=>['primary','3pts'],'fair'=>['warning','2pts'],'poor'=>['danger','1pt'],'not_answered'=>['secondary','0']] as $val => [$col, $pts]): ?>
                            <label class="mb-0">
                                <input type="radio" name="answer_status[<?= $q['id'] ?>]"
                                       value="<?= $val ?>"
                                       class="d-none rating-radio"
                                       data-qcard="qcard_<?= $q['id'] ?>"
                                       data-rating="<?= $val ?>"
                                       onchange="rateQuestion(this)"
                                       <?= $cur === $val ? 'checked' : '' ?>>
                                <span class="btn btn-sm rating-btn btn-<?= $cur === $val ? '' : 'outline-' ?><?= $col ?>">
                                    <?= ucfirst(str_replace('_',' ',$val)) ?>
                                    <span class="score-badge"><?= $pts ?></span>
                                </span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <!-- Remarks -->
                        <input type="text" name="remarks[<?= $q['id'] ?>]"
                               class="form-control form-control-sm"
                               placeholder="Remarks / observations..."
                               value="<?= htmlspecialchars($q['remarks'] ?? '', ENT_QUOTES) ?>">
                        <?php else: ?>
                        <!-- Read-only (completed) -->
                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            <?php
                            $rColors = ['excellent'=>'success','good'=>'primary','fair'=>'warning','poor'=>'danger','not_answered'=>'secondary'];
                            $rLabels = ['excellent'=>'Excellent','good'=>'Good','fair'=>'Fair','poor'=>'Poor','not_answered'=>'Not Answered'];
                            $rc = $rColors[$cur] ?? 'secondary';
                            $rl = $rLabels[$cur] ?? '—';
                            $sc = $scoreMap[$cur] ?? 0;
                            ?>
                            <span class="badge bg-<?= $rc ?>">
                                <?= $rl ?> (<?= $sc ?>pts)
                            </span>
                            <?php if (!empty($q['remarks'])): ?>
                            <span class="small text-muted fst-italic">"<?= htmlspecialchars($q['remarks'], ENT_QUOTES) ?>"</span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>

                    <?php if ($interview['status'] !== 'completed'): ?>
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-save me-1"></i>Save All Evaluations
                    </button>
                    <?php endif; ?>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Complete Interview -->
        <?php if ($interview['status'] !== 'completed' && !empty($questions)): ?>
        <div class="card border-success border-0 shadow-sm">
            <div class="card-header bg-success text-white py-2">
                <h6 class="mb-0 fw-bold small">
                    <i class="bi bi-flag-fill me-2"></i>Complete Interview &amp; Record Decision
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/agency/interviews/<?= $interview['id'] ?>/complete">
                    <?= csrfField() ?>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">
                                Final Hiring Decision <span class="text-danger">*</span>
                            </label>
                            <select name="hiring_outcome" class="form-select form-select-sm" required>
                                <option value="">— Select Decision —</option>
                                <option value="qualified_for_contact">✓ Qualified (For Contact)</option>
                                <option value="waitlisted">⏳ Qualified but Waitlisted</option>
                                <option value="not_qualified">✗ Not Qualified</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Hiring Remarks</label>
                            <input type="text" name="hiring_remarks" class="form-control form-control-sm"
                                   placeholder="e.g. Strong candidate, offered position...">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Overall Interview Assessment</label>
                        <textarea name="overall_remarks" class="form-control form-control-sm" rows="2"
                                  placeholder="Overall assessment of the applicant's performance..."
                        ><?= htmlspecialchars($interview['overall_remarks'] ?? '', ENT_QUOTES) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100 fw-semibold"
                            onclick="return confirm('Mark interview as completed? This cannot be undone.')">
                        <i class="bi bi-flag-fill me-2"></i>Mark Interview as Completed &amp; Generate Result
                    </button>
                </form>
            </div>
        </div>

        <?php elseif ($interview['status'] === 'completed'): ?>
        <div class="alert alert-success d-flex align-items-start gap-3">
            <i class="bi bi-check-circle-fill fs-4 flex-shrink-0 mt-1"></i>
            <div>
                <strong>Interview Completed</strong>
                <?= !empty($interview['completed_at']) ? ' on ' . date('F d, Y', strtotime($interview['completed_at'])) : '' ?>
                <?php
                $oColors = ['hired'=>'success','not_hired'=>'danger','for_consideration'=>'info','pending'=>'warning'];
                $oColor  = $oColors[$interview['hiring_outcome']] ?? 'secondary';
                ?>
                <span class="badge bg-<?= $oColor ?> ms-2">
                    <?= ucfirst(str_replace('_', ' ', $interview['hiring_outcome'])) ?>
                </span>
                <?php if (!empty($interview['overall_remarks'])): ?>
                <div class="small mt-1 text-muted">
                    "<?= htmlspecialchars($interview['overall_remarks'], ENT_QUOTES) ?>"
                </div>
                <?php endif; ?>
                <?php if (!empty($interview['score_summary'])): ?>
                <div class="small mt-1 fw-semibold">
                    <i class="bi bi-graph-up me-1"></i><?= htmlspecialchars($interview['score_summary'], ENT_QUOTES) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="mt-3">
            <a href="<?= APP_URL ?>/agency/interviews" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Back to Interviews
            </a>
        </div>
    </div>
</div>

<script>
function rateQuestion(radio) {
    const card = document.getElementById(radio.dataset.qcard);
    const rating = radio.dataset.rating;
    // Update card class
    card.className = card.className.replace(/\b(excellent|good|fair|poor|not_answered)\b/g,'').trim();
    if (rating) card.classList.add(rating);
    // Update button styles
    const btns = card.querySelectorAll('.rating-btn');
    btns.forEach(btn => {
        const inp = btn.previousElementSibling;
        const r   = inp ? inp.value : '';
        const colorMap = {excellent:'success',good:'primary',fair:'warning',poor:'danger',not_answered:'secondary'};
        const c = colorMap[r] || 'secondary';
        if (r === rating) {
            btn.className = `btn btn-sm rating-btn btn-${c}`;
        } else {
            btn.className = `btn btn-sm rating-btn btn-outline-${c}`;
        }
    });
}
// Apply on page load
document.querySelectorAll('.rating-radio:checked').forEach(r => rateQuestion(r));
</script>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
