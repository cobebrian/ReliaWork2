<?php $pageTitle = $pageTitle ?? 'Interviews'; ?>
<?php ob_start(); ?>

<?php if ($success): ?><div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($success, ENT_QUOTES) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES) ?></div><?php endif; ?>

<div class="row g-4">
    <!-- Left: Interview Queue (approved applications) -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-people-fill me-2 text-success"></i>Interview Queue
                </h6>
                <span class="badge bg-success"><?= count($readyApplicants) ?></span>
            </div>
            <div class="card-body p-0" style="max-height:500px;overflow-y:auto;">
                <?php if (empty($readyApplicants)): ?>
                    <div class="text-center py-5 text-muted small">
                        <i class="bi bi-people display-4 d-block mb-2 opacity-50"></i>
                        No validated applicants yet.<br>
                        Applicants appear here after the Validating Officer approves their documents.
                    </div>
                <?php else: ?>
                <?php foreach ($readyApplicants as $ap):
                    $name = strtoupper($ap['surname']) . ', ' . $ap['firstname'];
                ?>
                <div class="d-flex align-items-start gap-3 px-3 py-3 border-bottom">
                    <div class="flex-grow-1">
                        <div class="fw-semibold small"><?= htmlspecialchars($name, ENT_QUOTES) ?></div>
                        <div class="text-muted" style="font-size:.72rem;">
                            <?= htmlspecialchars($ap['position'], ENT_QUOTES) ?> &bull;
                            <?= htmlspecialchars($ap['fair_title'] ?? '', ENT_QUOTES) ?>
                        </div>
                        <?php if (!empty($ap['preferred_occupation'])): ?>
                        <div class="text-muted" style="font-size:.7rem;">
                            Preference: <?= htmlspecialchars($ap['preferred_occupation'], ENT_QUOTES) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($ap['has_interview'] > 0): ?>
                        <span class="badge bg-success flex-shrink-0 mt-1">Scheduled</span>
                    <?php else: ?>
                        <button class="btn btn-sm btn-primary flex-shrink-0"
                                data-bs-toggle="modal"
                                data-bs-target="#scheduleModal"
                                data-appid="<?= $ap['application_id'] ?>"
                                data-agencyid="<?= $ap['agency_id'] ?>"
                                data-vacancyid="<?= $ap['vacancy_id'] ?>"
                                data-name="<?= htmlspecialchars($name, ENT_QUOTES) ?>"
                                data-position="<?= htmlspecialchars($ap['position'], ENT_QUOTES) ?>">
                            <i class="bi bi-camera-video me-1"></i>Interview
                        </button>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right: My Interviews list -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-list-check me-2 text-primary"></i>My Interviews (<?= count($myInterviews) ?>)
                </h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($myInterviews)): ?>
                    <div class="text-center py-5 text-muted small">No interviews yet.</div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Applicant</th>
                                <th>Position</th>
                                <th class="text-center">Q&amp;A</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Outcome</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($myInterviews as $iv):
                            $sColors = ['scheduled'=>'warning text-dark','in_progress'=>'primary','completed'=>'success','cancelled'=>'secondary'];
                            $sColor  = $sColors[$iv['status']] ?? 'secondary';
                            $oColors = ['hired'=>'success','not_hired'=>'danger','for_consideration'=>'info text-dark','pending'=>'warning text-dark'];
                            $oColor  = $oColors[$iv['hiring_outcome'] ?? 'pending'] ?? 'secondary';
                        ?>
                        <tr>
                            <td class="fw-semibold">
                                <?= htmlspecialchars(strtoupper($iv['surname']) . ', ' . $iv['firstname'], ENT_QUOTES) ?>
                            </td>
                            <td class="text-muted"><?= htmlspecialchars($iv['position'] ?? '—', ENT_QUOTES) ?></td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">
                                    <?= (int)$iv['evaluated_count'] ?>/<?= (int)$iv['question_count'] ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-<?= $sColor ?>"><?= ucfirst($iv['status']) ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-<?= $oColor ?>">
                                    <?= ucfirst(str_replace('_',' ', $iv['hiring_outcome'] ?? 'pending')) ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= APP_URL ?>/agency/interviews/<?= $iv['id'] ?>/evaluate"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil-square"></i>
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
                <h6 class="modal-title fw-bold">
                    <i class="bi bi-camera-video me-2"></i>Schedule Interview
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= APP_URL ?>/agency/interviews/create">
                <?= csrfField() ?>
                <input type="hidden" name="application_id"  id="modalAppId">
                <input type="hidden" name="agency_id"        id="modalAgencyId">
                <input type="hidden" name="job_vacancy_id"   id="modalVacancyId">
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="fw-semibold" id="modalApplicantName"></div>
                        <div class="text-muted small" id="modalPosition"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Schedule Date &amp; Time</label>
                        <input type="datetime-local" name="scheduled_at" class="form-control form-control-sm">
                        <div class="form-text">Leave blank if not yet scheduled.</div>
                    </div>
                    <div class="alert alert-info small py-2 mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        10 default interview questions will be automatically loaded.
                        You can add job-specific questions after.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-send me-1"></i>Start Interview
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('scheduleModal').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('modalAppId').value      = btn.dataset.appid;
    document.getElementById('modalAgencyId').value   = btn.dataset.agencyid;
    document.getElementById('modalVacancyId').value  = btn.dataset.vacancyid;
    document.getElementById('modalApplicantName').textContent = btn.dataset.name;
    document.getElementById('modalPosition').textContent      = btn.dataset.position;
});
</script>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
