<?php $pageTitle = $pageTitle ?? 'View Report'; ?>
<?php ob_start(); ?>

<style>
@media print { .no-print { display:none!important; } @page { size:A4; margin:10mm 12mm; } }
</style>

<div class="no-print d-flex gap-2 mb-4 flex-wrap align-items-center">
    <a href="<?= APP_URL ?>/reporting-officer/reports" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Reports List
    </a>
    <?php if ($report['report_status'] === 'draft'): ?>
    <form method="POST" action="<?= APP_URL ?>/reporting-officer/reports/<?= $report['id'] ?>/submit" class="d-inline">
        <?= csrfField() ?>
        <button type="submit" class="btn btn-primary btn-sm"
                onclick="return confirm('Submit this report to Supervising Labor?')">
            <i class="bi bi-send me-1"></i>Send to Supervising Labor
        </button>
    </form>
    <?php else: ?>
    <span class="badge bg-<?= $report['report_status']==='reviewed'?'success':'primary' ?> py-2 px-3">
        <?= ucfirst($report['report_status']) ?>
        <?= !empty($report['submitted_at']) ? ' — ' . date('M d, Y', strtotime($report['submitted_at'])) : '' ?>
    </span>
    <?php endif; ?>
    <button onclick="window.print()" class="btn btn-outline-dark btn-sm ms-auto">
        <i class="bi bi-printer me-1"></i>Print / Download
    </button>
</div>

<!-- Header -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-dark text-white text-center py-3">
        <div class="fw-bold small opacity-75">REPUBLIC OF THE PHILIPPINES — DEPARTMENT OF LABOR AND EMPLOYMENT (DOLE)</div>
        <h4 class="mb-1 fw-bold mt-1">JOB FAIR SUMMARY REPORT</h4>
        <div class="small opacity-75">PESO — <?= htmlspecialchars($report['fair_title'], ENT_QUOTES) ?></div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-5">
                <span class="text-muted small">Job Fair Title</span>
                <div class="fw-bold"><?= htmlspecialchars($report['fair_title'], ENT_QUOTES) ?></div>
            </div>
            <div class="col-md-3">
                <span class="text-muted small">Date</span>
                <div class="fw-bold"><?= !empty($report['requested_date']) ? date('F d, Y', strtotime($report['requested_date'])) : '—' ?></div>
            </div>
            <div class="col-md-4">
                <span class="text-muted small">Venue</span>
                <div class="fw-bold"><?= htmlspecialchars($report['venue'] ?? '—', ENT_QUOTES) ?></div>
            </div>
            <div class="col-md-4">
                <span class="text-muted small">Reporting Officer</span>
                <div class="fw-semibold"><?= htmlspecialchars($report['generated_by_name'] ?? '—', ENT_QUOTES) ?></div>
            </div>
            <div class="col-md-4">
                <span class="text-muted small">Date Generated</span>
                <div class="fw-semibold"><?= date('F d, Y', strtotime($report['generated_at'])) ?></div>
            </div>
            <div class="col-md-4">
                <span class="text-muted small">Status</span>
                <?php $sColors=['draft'=>'secondary','submitted'=>'primary','reviewed'=>'success']; ?>
                <div><span class="badge bg-<?= $sColors[$report['report_status']] ?>"><?= ucfirst($report['report_status']) ?></span></div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row g-3 mb-4">
    <?php
    $statCards = [
        ['Total Registered',         $summary['total_applicants'],   'secondary', 'bi-people-fill'],
        ['Validated',                $summary['total_validated'],    'info',      'bi-patch-check-fill'],
        ['Interviewed',              $summary['total_interviewed'],  'warning',   'bi-camera-video-fill'],
        ['Qualified (For Contact)',  $summary['total_qualified'],    'success',   'bi-person-check'],
        ['Waitlisted',               $summary['total_waitlisted'],   'info',      'bi-person-lines-fill'],
        ['Awaiting Requirements',    $summary['total_awaiting_reqs'],'warning',   'bi-folder'],
        ['First Day Scheduled',      $summary['total_scheduled'],    'primary',   'bi-calendar-check'],
        ['Officially Hired',         $summary['total_hired'],        'success',   'bi-trophy-fill'],
        ['Not Qualified',            $summary['total_not_hired'],    'danger',    'bi-x-circle-fill'],
        ['Agencies',                 $summary['total_agencies'],     'primary',   'bi-building'],
        ['Vacancies Offered',        $summary['total_vacancies'],    'secondary', 'bi-briefcase-fill'],
        ['Employment Rate',          $summary['employment_rate'].'%','success',   'bi-graph-up-arrow'],
    ];
    foreach ($statCards as [$label, $val, $color, $icon]): ?>
    <div class="col-6 col-sm-4 col-lg-2">
        <div class="card border-0 shadow-sm text-center py-2 h-100">
            <div class="fw-bold" style="font-size:1.5rem;color:var(--bs-<?= $color ?>);"><?= $val ?></div>
            <div class="small text-muted" style="font-size:.7rem;"><?= $label ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Agency Performance -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-2">
        <h6 class="mb-0 fw-bold"><i class="bi bi-building me-2 text-primary"></i>Agency Performance Summary</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>#</th><th>Agency</th>
                    <th class="text-center">Vacancies</th>
                    <th class="text-center">Slots</th>
                    <th class="text-center">Interviewed</th>
                    <th class="text-center">Hired</th>
                    <th class="text-center">Hire Rate</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($agencies as $i => $ag):
                $rate = $ag['interviewed_count'] > 0
                    ? round(($ag['hired_count']/$ag['interviewed_count'])*100, 0) : 0;
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

<!-- Remarks / Observations / Recommendations -->
<?php if (!empty($report['overall_remarks']) || !empty($report['observations']) || !empty($report['recommendations'])): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-2">
        <h6 class="mb-0 fw-bold"><i class="bi bi-chat-quote me-2 text-success"></i>Reporting Officer's Remarks</h6>
    </div>
    <div class="card-body small">
        <?php if (!empty($report['overall_remarks'])): ?>
        <div class="mb-3">
            <div class="fw-bold text-muted mb-1">Overall Assessment</div>
            <p><?= nl2br(htmlspecialchars($report['overall_remarks'], ENT_QUOTES)) ?></p>
        </div>
        <?php endif; ?>
        <?php if (!empty($report['observations'])): ?>
        <div class="mb-3">
            <div class="fw-bold text-muted mb-1">Observations</div>
            <p><?= nl2br(htmlspecialchars($report['observations'], ENT_QUOTES)) ?></p>
        </div>
        <?php endif; ?>
        <?php if (!empty($report['recommendations'])): ?>
        <div class="mb-0">
            <div class="fw-bold text-muted mb-1">Recommendations</div>
            <p class="mb-0"><?= nl2br(htmlspecialchars($report['recommendations'], ENT_QUOTES)) ?></p>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($report['reviewer_remarks'])): ?>
<div class="card border-success border-0 shadow-sm mb-4">
    <div class="card-header bg-success text-white py-2">
        <h6 class="mb-0 small fw-bold"><i class="bi bi-shield-check me-2"></i>Supervising Labor Review Remarks</h6>
    </div>
    <div class="card-body small">
        <?= nl2br(htmlspecialchars($report['reviewer_remarks'], ENT_QUOTES)) ?>
        <?php if (!empty($report['reviewed_at'])): ?>
        <div class="text-muted mt-1">Reviewed on <?= date('F d, Y', strtotime($report['reviewed_at'])) ?></div>
        <?php endif; ?>
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

<div class="mt-3 no-print">
    <a href="<?= APP_URL ?>/reporting-officer/reports" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back to Reports
    </a>
</div>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
