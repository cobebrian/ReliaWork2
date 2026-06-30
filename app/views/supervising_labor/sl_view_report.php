<?php $pageTitle = $pageTitle ?? 'View Report'; ?>
<?php ob_start(); ?>

<style>
@media print { .no-print { display:none!important; } @page { size:A4; margin:10mm 12mm; } }
</style>

<div class="no-print d-flex gap-2 mb-4 flex-wrap align-items-center">
    <a href="<?= APP_URL ?>/supervising-labor/reports" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>All Reports
    </a>
    <?php if ($report['report_status'] === 'submitted'): ?>
    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#reviewModal">
        <i class="bi bi-check-circle me-1"></i>Mark as Reviewed
    </button>
    <?php else: ?>
    <span class="badge bg-success py-2 px-3">Reviewed</span>
    <?php endif; ?>
    <button onclick="window.print()" class="btn btn-outline-dark btn-sm ms-auto">
        <i class="bi bi-printer me-1"></i>Print / Download
    </button>
</div>

<!-- Header -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-dark text-white text-center py-3">
        <div class="small opacity-75 fw-bold">REPUBLIC OF THE PHILIPPINES — DEPARTMENT OF LABOR AND EMPLOYMENT (DOLE)</div>
        <h4 class="mb-1 fw-bold mt-1">JOB FAIR SUMMARY REPORT</h4>
        <div class="small opacity-75"><?= htmlspecialchars($report['fair_title'], ENT_QUOTES) ?></div>
    </div>
    <div class="card-body">
        <div class="row g-3 small">
            <div class="col-md-5">
                <span class="text-muted">Job Fair Title</span>
                <div class="fw-bold"><?= htmlspecialchars($report['fair_title'], ENT_QUOTES) ?></div>
            </div>
            <div class="col-md-3">
                <span class="text-muted">Date</span>
                <div class="fw-bold"><?= !empty($report['requested_date']) ? date('F d, Y', strtotime($report['requested_date'])) : '—' ?></div>
            </div>
            <div class="col-md-4">
                <span class="text-muted">Venue</span>
                <div class="fw-bold"><?= htmlspecialchars($report['venue'] ?? '—', ENT_QUOTES) ?></div>
            </div>
            <div class="col-md-4">
                <span class="text-muted">Reporting Officer</span>
                <div class="fw-semibold"><?= htmlspecialchars($report['generated_by_name'] ?? '—', ENT_QUOTES) ?></div>
            </div>
            <div class="col-md-4">
                <span class="text-muted">Generated</span>
                <div class="fw-semibold"><?= date('F d, Y', strtotime($report['generated_at'])) ?></div>
            </div>
            <div class="col-md-4">
                <span class="text-muted">Submitted</span>
                <div class="fw-semibold"><?= !empty($report['submitted_at']) ? date('F d, Y', strtotime($report['submitted_at'])) : '—' ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row g-3 mb-4">
    <?php
    $statCards = [
        ['Total Registered',        $report['total_applicants'],    'secondary','bi-people-fill'],
        ['Validated',               $report['total_validated'],     'info',     'bi-patch-check-fill'],
        ['Interviewed',             $report['total_interviewed'],   'warning',  'bi-camera-video-fill'],
        ['Qualified (For Contact)', $report['total_qualified'],     'success',  'bi-person-check'],
        ['Waitlisted',              $report['total_waitlisted'],    'info',     'bi-person-lines-fill'],
        ['Awaiting Requirements',   $report['total_awaiting_reqs'],'warning',  'bi-folder'],
        ['First Day Scheduled',     $report['total_scheduled'],     'primary',  'bi-calendar-check'],
        ['Officially Hired',        $report['total_hired'],         'success',  'bi-trophy-fill'],
        ['Not Qualified',           $report['total_not_hired'],     'danger',   'bi-x-circle-fill'],
        ['Agencies',                $report['total_agencies'],      'primary',  'bi-building'],
        ['Vacancies Offered',       $report['total_vacancies'],     'secondary','bi-briefcase-fill'],
        ['Employment Rate',         $report['employment_rate'].'%', 'success',  'bi-graph-up-arrow'],
    ];
    foreach ($statCards as [$label, $val, $color, $icon]): ?>
    <div class="col-6 col-sm-4 col-lg-2">
        <div class="card border-0 shadow-sm text-center py-2 h-100">
            <div class="fw-bold" style="font-size:1.4rem;color:var(--bs-<?= $color ?>);"><?= $val ?></div>
            <div class="small text-muted" style="font-size:.68rem;"><?= $label ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Agency Performance -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-2">
        <h6 class="mb-0 fw-bold"><i class="bi bi-building me-2 text-primary"></i>Agency Performance</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>#</th><th>Agency</th>
                    <th class="text-center">Vacancies</th><th class="text-center">Slots</th>
                    <th class="text-center">Interviewed</th><th class="text-center">Hired</th>
                    <th class="text-center">Hire Rate</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($agencies as $i => $ag):
                $rate = $ag['interviewed_count'] > 0
                    ? round(($ag['hired_count']/$ag['interviewed_count'])*100,0) : 0;
            ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td class="fw-semibold"><?= htmlspecialchars($ag['agency_name'], ENT_QUOTES) ?></td>
                <td class="text-center"><?= $ag['vacancy_count'] ?></td>
                <td class="text-center"><?= $ag['total_slots'] ?? 0 ?></td>
                <td class="text-center"><span class="badge bg-info text-dark"><?= $ag['interviewed_count'] ?></span></td>
                <td class="text-center"><span class="badge bg-success"><?= $ag['hired_count'] ?></span></td>
                <td class="text-center"><span class="badge bg-<?= $rate>=50?'success':'warning' ?> text-dark"><?= $rate ?>%</span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Reporting Officer Remarks -->
<?php if (!empty($report['overall_remarks']) || !empty($report['observations']) || !empty($report['recommendations'])): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-2">
        <h6 class="mb-0 fw-bold"><i class="bi bi-chat-quote me-2 text-success"></i>Reporting Officer's Remarks</h6>
    </div>
    <div class="card-body small">
        <?php foreach (['overall_remarks'=>'Overall Assessment','observations'=>'Observations','recommendations'=>'Recommendations'] as $field => $label): ?>
        <?php if (!empty($report[$field])): ?>
        <div class="mb-3">
            <div class="fw-bold text-muted mb-1"><?= $label ?></div>
            <p class="mb-0"><?= nl2br(htmlspecialchars($report[$field], ENT_QUOTES)) ?></p>
        </div>
        <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- SL Reviewer Remarks -->
<?php if (!empty($report['reviewer_remarks'])): ?>
<div class="card border-success border-0 shadow-sm mb-4">
    <div class="card-header bg-success text-white py-2">
        <h6 class="mb-0 small fw-bold"><i class="bi bi-shield-check me-2"></i>Your Review Remarks</h6>
    </div>
    <div class="card-body small">
        <?= nl2br(htmlspecialchars($report['reviewer_remarks'], ENT_QUOTES)) ?>
        <div class="text-muted mt-1">
            Reviewed on <?= !empty($report['reviewed_at']) ? date('F d, Y', strtotime($report['reviewed_at'])) : '' ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Submission History -->
<?php if (!empty($history)): ?>
<div class="card border-0 shadow-sm no-print">
    <div class="card-header bg-white py-2">
        <h6 class="mb-0 small fw-bold"><i class="bi bi-clock-history me-2 text-muted"></i>Report History</h6>
    </div>
    <div class="card-body p-0">
        <?php foreach ($history as $h): ?>
        <div class="d-flex gap-2 px-3 py-2 border-bottom align-items-center small">
            <i class="bi bi-circle-fill text-primary" style="font-size:.4rem;"></i>
            <span class="fw-semibold"><?= ucfirst($h['action']) ?></span>
            <span class="text-muted">by <?= htmlspecialchars($h['performed_by_name'] ?? '—', ENT_QUOTES) ?></span>
            <span class="ms-auto text-muted"><?= date('M d, Y g:i A', strtotime($h['performed_at'])) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Review Modal -->
<?php if ($report['report_status'] === 'submitted'): ?>
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Mark Report as Reviewed</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= APP_URL ?>/supervising-labor/reports/<?= $report['id'] ?>/mark-reviewed">
                <?= csrfField() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Reviewer Remarks (optional)</label>
                        <textarea name="reviewer_remarks" class="form-control form-control-sm" rows="4"
                                  placeholder="Observations, feedback, acknowledgment..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="bi bi-check-circle me-1"></i>Confirm Review
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
