<?php $pageTitle = $pageTitle ?? 'Interviews'; ?>
<?php ob_start(); ?>

<?php if ($success): ?><div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($success, ENT_QUOTES) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES) ?></div><?php endif; ?>

<div class="row g-4">
    <!-- Left: Start Interview -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-person-lines-fill me-2 text-primary"></i>Validated Applicants</h6>
            </div>
            <div class="card-body p-0" style="max-height:400px;overflow-y:auto;">
                <?php if (empty($validatedApplicants)): ?>
                    <div class="text-center py-5 text-muted small">
                        <i class="bi bi-people display-4 d-block mb-2 opacity-50"></i>
                        No validated applicants yet.
                    </div>
                <?php else: ?>
                <?php foreach ($validatedApplicants as $ap):
                    $name = strtoupper($ap['surname']) . ', ' . $ap['firstname'];
                ?>
                <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
                    <div class="flex-grow-1">
                        <div class="fw-semibold small"><?= htmlspecialchars($name, ENT_QUOTES) ?></div>
                        <div class="text-muted" style="font-size:.75rem;">
                            <?= htmlspecialchars($ap['preferred_occupation'] ?? 'No preference', ENT_QUOTES) ?>
                        </div>
                    </div>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                            data-bs-target="#scheduleModal"
                            data-id="<?= $ap['id'] ?>"
                            data-name="<?= htmlspecialchars($name, ENT_QUOTES) ?>">
                        <i class="bi bi-camera-video me-1"></i>Interview
                    </button>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right: My Interviews -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-list-check me-2 text-success"></i>My Interviews (<?= count($myInterviews) ?>)</h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($myInterviews)): ?>
                    <div class="text-center py-5 text-muted small">No interviews yet.</div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Applicant</th><th>Job Fair</th><th>Questions</th><th>Status</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($myInterviews as $iv):
                            $colors = ['scheduled'=>'warning','in_progress'=>'primary','completed'=>'success','cancelled'=>'secondary'];
                            $color = $colors[$iv['status']] ?? 'secondary';
                        ?>
                        <tr>
                            <td class="fw-semibold small">
                                <?= htmlspecialchars(strtoupper($iv['surname']) . ', ' . $iv['firstname'], ENT_QUOTES) ?>
                            </td>
                            <td class="small text-muted"><?= htmlspecialchars($iv['fair_title'], ENT_QUOTES) ?></td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border"><?= $iv['answered_count'] ?>/<?= $iv['question_count'] ?></span>
                            </td>
                            <td><span class="badge bg-<?= $color ?>"><?= ucfirst($iv['status']) ?></span></td>
                            <td>
                                <a href="<?= APP_URL ?>/agency/interviews/<?= $iv['id'] ?>/evaluate"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil-square me-1"></i>Evaluate
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
    </div>
</div>

<!-- Schedule Interview Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="bi bi-camera-video me-2"></i>Schedule Interview</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= APP_URL ?>/agency/interviews/create">
                <?= csrfField() ?>
                <div class="modal-body">
                    <input type="hidden" name="applicant_id" id="modalApplicantId">
                    <p class="mb-3">Applicant: <strong id="modalApplicantName"></strong></p>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Select Job Fair / Agency <span class="text-danger">*</span></label>
                        <select name="agency_id" class="form-select form-select-sm" required>
                            <option value="">— Select —</option>
                            <?php foreach ($myAgencies as $ag): ?>
                            <option value="<?= $ag['id'] ?>">
                                <?= htmlspecialchars($ag['agency_name'] . ' — ' . $ag['fair_title'], ENT_QUOTES) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Schedule Date & Time</label>
                        <input type="datetime-local" name="scheduled_at" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-send me-1"></i>Create Interview
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('scheduleModal').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('modalApplicantId').value = btn.dataset.id;
    document.getElementById('modalApplicantName').textContent = btn.dataset.name;
});
</script>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
