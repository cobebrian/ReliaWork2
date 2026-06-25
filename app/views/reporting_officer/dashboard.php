<?php $pageTitle = $pageTitle ?? 'Reporting Officer Dashboard'; ?>
<?php ob_start(); ?>

<style>
.stat-big { font-size: 2.2rem; font-weight: 900; line-height: 1; }
.rate-circle { width: 90px; height: 90px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; font-weight: 900; }
</style>

<!-- Overall Stats Row -->
<div class="row g-3 mb-4">
    <?php
    $statCards = [
        ['label'=>'Total Job Fairs',    'val'=>$stats['total_job_fairs'],   'color'=>'primary',  'icon'=>'bi-calendar-event'],
        ['label'=>'Applicants',         'val'=>$stats['total_applicants'],  'color'=>'secondary','icon'=>'bi-people'],
        ['label'=>'Validated',          'val'=>$stats['total_validated'],   'color'=>'info',     'icon'=>'bi-patch-check'],
        ['label'=>'Interviewed',        'val'=>$stats['total_interviewed'], 'color'=>'warning',  'icon'=>'bi-camera-video'],
        ['label'=>'Hired',              'val'=>$stats['total_hired'],       'color'=>'success',  'icon'=>'bi-person-check'],
        ['label'=>'Not Hired',          'val'=>$stats['total_not_hired'],   'color'=>'danger',   'icon'=>'bi-person-x'],
        ['label'=>'Agencies',           'val'=>$stats['total_agencies'],    'color'=>'primary',  'icon'=>'bi-building'],
        ['label'=>'Vacancies',          'val'=>$stats['total_vacancies'],   'color'=>'success',  'icon'=>'bi-briefcase'],
    ];
    foreach ($statCards as $s): ?>
    <div class="col-6 col-sm-4 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="stat-icon bg-<?= $s['color'] ?>-subtle text-<?= $s['color'] ?>">
                    <i class="bi <?= $s['icon'] ?>"></i>
                </div>
                <div>
                    <div class="stat-value"><?= number_format($s['val']) ?></div>
                    <div class="stat-label"><?= $s['label'] ?></div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Employment Rate Banner -->
<div class="card border-0 shadow-sm mb-4 bg-gradient" style="background: linear-gradient(135deg,#1e2a3a,#2d4a6e)!important;">
    <div class="card-body d-flex align-items-center gap-4 py-3">
        <div class="rate-circle bg-white text-primary">
            <?= $stats['employment_rate'] ?>%
        </div>
        <div class="text-white">
            <h5 class="mb-0 fw-bold">Overall Employment Rate</h5>
            <div class="small opacity-75">
                <?= number_format($stats['total_hired']) ?> hired out of
                <?= number_format($stats['total_interviewed']) ?> interviewed applicants
            </div>
        </div>
        <div class="ms-auto">
            <a href="<?= APP_URL ?>/reporting-officer/interviews" class="btn btn-light btn-sm">
                <i class="bi bi-list-check me-1"></i>View All Interviews
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Job Fairs Summary -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-calendar-event me-2 text-primary"></i>Job Fair Reports</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Job Fair</th>
                            <th>Date</th>
                            <th class="text-center">Agencies</th>
                            <th class="text-center">Vacancies</th>
                            <th class="text-center">Interviewed</th>
                            <th class="text-center">Hired</th>
                            <th>Rate</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($jobFairs)): ?>
                        <tr><td colspan="8" class="text-center py-5 text-muted">No job fairs yet.</td></tr>
                    <?php else: ?>
                    <?php foreach ($jobFairs as $jf):
                        $rate = $jf['interviewed_count'] > 0
                            ? round(($jf['hired_count'] / $jf['interviewed_count']) * 100, 0) : 0;
                        $rateColor = $rate >= 70 ? 'success' : ($rate >= 40 ? 'warning' : 'danger');
                    ?>
                    <tr>
                        <td class="fw-semibold small"><?= htmlspecialchars($jf['title'], ENT_QUOTES) ?></td>
                        <td class="small text-muted">
                            <?= !empty($jf['requested_date']) ? date('M d, Y', strtotime($jf['requested_date'])) : '—' ?>
                        </td>
                        <td class="text-center"><span class="badge bg-primary"><?= $jf['agency_count'] ?></span></td>
                        <td class="text-center"><span class="badge bg-secondary"><?= $jf['vacancy_count'] ?></span></td>
                        <td class="text-center"><span class="badge bg-info text-dark"><?= $jf['interviewed_count'] ?></span></td>
                        <td class="text-center"><span class="badge bg-success"><?= $jf['hired_count'] ?></span></td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                <div class="progress flex-grow-1" style="height:6px;">
                                    <div class="progress-bar bg-<?= $rateColor ?>" style="width:<?= $rate ?>%"></div>
                                </div>
                                <small class="text-<?= $rateColor ?> fw-bold"><?= $rate ?>%</small>
                            </div>
                        </td>
                        <td>
                            <a href="<?= APP_URL ?>/reporting-officer/job-fairs/<?= $jf['id'] ?>"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-file-earmark-bar-graph me-1"></i>Report
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Completed Interviews -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-camera-video me-2 text-success"></i>Recent Interviews</h6>
                <a href="<?= APP_URL ?>/reporting-officer/interviews" class="btn btn-xs btn-outline-primary" style="padding:2px 8px;font-size:.75rem;">
                    View All
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentInterviews)): ?>
                    <div class="text-center py-4 text-muted small">No completed interviews yet.</div>
                <?php else: ?>
                <?php foreach ($recentInterviews as $iv):
                    $oColors = ['hired'=>'success','not_hired'=>'danger','for_consideration'=>'info','pending'=>'warning'];
                    $oColor  = $oColors[$iv['hiring_outcome']] ?? 'secondary';
                ?>
                <div class="px-3 py-2 border-bottom d-flex align-items-center gap-2">
                    <div class="flex-grow-1">
                        <div class="small fw-semibold">
                            <?= htmlspecialchars(strtoupper($iv['surname']) . ', ' . $iv['firstname'], ENT_QUOTES) ?>
                        </div>
                        <div class="text-muted" style="font-size:.72rem;">
                            <?= htmlspecialchars($iv['agency_name'], ENT_QUOTES) ?> &bull;
                            <?= htmlspecialchars($iv['fair_title'], ENT_QUOTES) ?>
                        </div>
                    </div>
                    <span class="badge bg-<?= $oColor ?> flex-shrink-0" style="font-size:.65rem;">
                        <?= ucfirst(str_replace('_',' ',$iv['hiring_outcome'])) ?>
                    </span>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
