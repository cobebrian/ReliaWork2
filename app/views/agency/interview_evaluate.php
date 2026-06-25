<?php $pageTitle = $pageTitle ?? 'Interview Evaluation'; ?>
<?php ob_start(); ?>

<style>
.q-card { border-left: 4px solid #dee2e6; transition: border-color .2s; }
.q-card.answered    { border-color: #198754; }
.q-card.needs_improvement { border-color: #fd7e14; }
.q-card.not_answered { border-color: #dc3545; }
</style>

<?php if ($success): ?><div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($success, ENT_QUOTES) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES) ?></div><?php endif; ?>

<div class="row g-4">
    <!-- Left: Applicant Profile Summary -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-dark text-white py-2">
                <h6 class="mb-0 small"><i class="bi bi-person me-2"></i>Applicant</h6>
            </div>
            <div class="card-body small">
                <p class="fw-bold mb-1"><?= htmlspecialchars(strtoupper($interview['surname']) . ', ' . $interview['firstname'], ENT_QUOTES) ?></p>
                <p class="text-muted mb-1"><?= htmlspecialchars($interview['applicant_email'] ?? '', ENT_QUOTES) ?></p>
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
                   target="_blank" class="d-flex align-items-center gap-2 px-3 py-2 border-bottom text-decoration-none small text-dark">
                    <i class="bi bi-file-earmark-text text-primary"></i>
                    <span class="flex-grow-1 text-truncate"><?= htmlspecialchars($doc['original_name'], ENT_QUOTES) ?></span>
                    <span class="badge bg-secondary"><?= ucfirst($doc['doc_type']) ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Interview Info -->
        <div class="card border-0 shadow-sm">
            <div class="card-body small">
                <div class="mb-1"><span class="text-muted">Agency:</span> <?= htmlspecialchars($interview['agency_name'], ENT_QUOTES) ?></div>
                <div class="mb-1"><span class="text-muted">Job Fair:</span> <?= htmlspecialchars($interview['fair_title'], ENT_QUOTES) ?></div>
                <div class="mb-1"><span class="text-muted">Scheduled:</span>
                    <?= !empty($interview['scheduled_at']) ? date('F d, Y g:i A', strtotime($interview['scheduled_at'])) : '—' ?>
                </div>
                <?php $colors = ['scheduled'=>'warning','in_progress'=>'primary','completed'=>'success','cancelled'=>'secondary']; ?>
                <div><span class="text-muted">Status:</span>
                    <span class="badge bg-<?= $colors[$interview['status']] ?? 'secondary' ?>">
                        <?= ucfirst($interview['status']) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Questions & Evaluation -->
    <div class="col-lg-8">
        <!-- Add Question -->
        <?php if ($interview['status'] !== 'completed'): ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2">
                <h6 class="mb-0 fw-bold"><i class="bi bi-plus-circle me-2 text-primary"></i>Add Interview Question</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/agency/interviews/<?= $interview['id'] ?>/add-question" class="d-flex gap-2">
                    <?= csrfField() ?>
                    <input type="text" name="question_text" class="form-control form-control-sm"
                           placeholder="e.g. Tell us about your previous work experience..." required>
                    <button type="submit" class="btn btn-primary btn-sm flex-shrink-0">
                        <i class="bi bi-plus me-1"></i>Add
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Questions List with Evaluation -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-list-check me-2 text-success"></i>Interview Questions (<?= count($questions) ?>)</h6>
            </div>
            <div class="card-body">
                <?php if (empty($questions)): ?>
                    <div class="text-center py-4 text-muted small">No questions added yet. Add questions above.</div>
                <?php else: ?>
                <form method="POST" action="<?= APP_URL ?>/agency/interviews/<?= $interview['id'] ?>/save-evaluations">
                    <?= csrfField() ?>
                    <?php foreach ($questions as $i => $q): ?>
                    <?php $qClass = $q['answer_status'] ? str_replace('_', '-', $q['answer_status']) : ''; /* For CSS border */ ?>
                    <div class="q-card p-3 mb-3 rounded <?= $q['answer_status'] ?? '' ?>" id="qcard-<?= $q['id'] ?>">
                        <div class="d-flex align-items-start gap-2 mb-2">
                            <span class="badge bg-light text-dark border flex-shrink-0 mt-1">Q<?= $i+1 ?></span>
                            <div class="fw-semibold flex-grow-1"><?= htmlspecialchars($q['question_text'], ENT_QUOTES) ?></div>
                            <?php if ($interview['status'] !== 'completed'): ?>
                            <form method="POST" action="<?= APP_URL ?>/agency/interviews/questions/<?= $q['id'] ?>/delete" class="d-inline flex-shrink-0">
                                <?= csrfField() ?>
                                <button type="submit" class="btn btn-xs btn-outline-danger"
                                        onclick="return confirm('Remove this question?')"
                                        style="padding:1px 6px;font-size:.7rem;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>

                        <?php if ($interview['status'] !== 'completed'): ?>
                        <!-- Evaluation Controls -->
                        <div class="row g-2 mb-2">
                            <div class="col-md-5">
                                <select name="answer_status[<?= $q['id'] ?>]"
                                        class="form-select form-select-sm q-status-select"
                                        data-qcard="qcard-<?= $q['id'] ?>"
                                        onchange="updateCard(this)">
                                    <option value="">— Not Evaluated —</option>
                                    <option value="answered"          <?= $q['answer_status'] === 'answered'          ? 'selected' : '' ?>>✓ Answered Satisfactorily</option>
                                    <option value="needs_improvement" <?= $q['answer_status'] === 'needs_improvement' ? 'selected' : '' ?>>⚠ Needs Improvement</option>
                                    <option value="not_answered"      <?= $q['answer_status'] === 'not_answered'      ? 'selected' : '' ?>>✗ Not Answered</option>
                                </select>
                            </div>
                            <div class="col-md-7">
                                <input type="text" name="remarks[<?= $q['id'] ?>]"
                                       class="form-control form-control-sm"
                                       placeholder="Remarks / comments..."
                                       value="<?= htmlspecialchars($q['remarks'] ?? '', ENT_QUOTES) ?>">
                            </div>
                        </div>
                        <?php else: ?>
                        <!-- Read-only for completed -->
                        <div class="d-flex gap-2 flex-wrap mb-1">
                            <?php
                            $evalColors = ['answered'=>'success','needs_improvement'=>'warning','not_answered'=>'danger'];
                            $evalLabels = ['answered'=>'✓ Answered Satisfactorily','needs_improvement'=>'⚠ Needs Improvement','not_answered'=>'✗ Not Answered'];
                            $ec = $evalColors[$q['answer_status']] ?? 'secondary';
                            $el = $evalLabels[$q['answer_status']] ?? 'Not Evaluated';
                            ?>
                            <span class="badge bg-<?= $ec ?>"><?= $el ?></span>
                        </div>
                        <?php if (!empty($q['remarks'])): ?>
                            <div class="small text-muted"><em>"<?= htmlspecialchars($q['remarks'], ENT_QUOTES) ?>"</em></div>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>

                    <?php if ($interview['status'] !== 'completed' && !empty($questions)): ?>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-save me-1"></i>Save All Evaluations
                    </button>
                    <?php endif; ?>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Mark Complete -->
        <?php if ($interview['status'] !== 'completed' && !empty($questions)): ?>
        <div class="card border-success border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-2"><i class="bi bi-flag-fill text-success me-2"></i>Complete Interview</h6>
                <form method="POST" action="<?= APP_URL ?>/agency/interviews/<?= $interview['id'] ?>/complete">
                    <?= csrfField() ?>
                    <div class="row g-3 mb-2">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">
                                Hiring Decision <span class="text-danger">*</span>
                            </label>
                            <select name="hiring_outcome" class="form-select form-select-sm" required>
                                <option value="pending">— Select Outcome —</option>
                                <option value="hired">✓ Hired</option>
                                <option value="not_hired">✗ Not Hired</option>
                                <option value="for_consideration">⏳ For Consideration</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Hiring Remarks (optional)</label>
                            <input type="text" name="hiring_remarks" class="form-control form-control-sm"
                                   placeholder="e.g. Qualified for position...">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Overall Interview Remarks (optional)</label>
                        <textarea name="overall_remarks" class="form-control form-control-sm" rows="2"
                                  placeholder="Overall assessment of the applicant..."><?= htmlspecialchars($interview['overall_remarks'] ?? '', ENT_QUOTES) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-success"
                            onclick="return confirm('Mark this interview as completed? This cannot be undone.')">
                        <i class="bi bi-flag-fill me-2"></i>Mark Interview as Completed
                    </button>
                </form>
            </div>
        </div>
        <?php elseif ($interview['status'] === 'completed'): ?>
        <div class="alert alert-success d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill fs-4"></i>
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
                <div class="small mt-1">Overall Remarks: <em>"<?= htmlspecialchars($interview['overall_remarks'], ENT_QUOTES) ?>"</em></div>
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
function updateCard(sel) {
    const card = document.getElementById(sel.dataset.qcard);
    card.className = card.className.replace(/\b(answered|needs_improvement|not_answered)\b/g, '').trim();
    if (sel.value) card.classList.add(sel.value);
}
// Apply on load
document.querySelectorAll('.q-status-select').forEach(updateCard);
</script>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
