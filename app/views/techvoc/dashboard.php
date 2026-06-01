<?php ob_start(); ?>

<?php if (!empty($success)): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-tools me-2 text-warning"></i>TECH-VOC Supervisor
        </h4>
        <p class="text-muted mb-0 small">Manage Technical-Vocational classes and student enrollment</p>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <?php
    $totalStudents = 0;
    foreach ($classes as $c) $totalStudents += (int)$c['student_count'];
    ?>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center"
                     style="width:52px;height:52px;font-size:1.4rem;">
                    <i class="bi bi-journal-text"></i>
                </div>
                <div>
                    <div class="fw-bold fs-3"><?= count($classes) ?></div>
                    <div class="text-muted small">Active Classes</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center"
                     style="width:52px;height:52px;font-size:1.4rem;">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <div class="fw-bold fs-3"><?= $totalStudents ?></div>
                    <div class="text-muted small">Total Students</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center"
                     style="width:52px;height:52px;font-size:1.4rem;">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div>
                    <div class="fw-bold fs-3">Every Sunday</div>
                    <div class="text-muted small">Class Schedule</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Class Cards -->
<div class="row g-4">
    <?php foreach ($classes as $class):
        $isWelding   = stripos($class['name'], 'welding') !== false;
        $isElectrical = stripos($class['name'], 'electrical') !== false;
        $color  = $isWelding ? 'warning' : ($isElectrical ? 'primary' : 'secondary');
        $icon   = $isWelding ? 'bi-fire' : ($isElectrical ? 'bi-lightning-charge-fill' : 'bi-tools');
        $bgGrad = $isWelding
            ? 'linear-gradient(135deg,#ff8c00,#ffc107)'
            : 'linear-gradient(135deg,#0d6efd,#0dcaf0)';
    ?>
    <div class="col-md-6">
        <div class="card border-0 shadow h-100">
            <!-- Class Header -->
            <div class="card-header text-white py-4" style="background:<?= $bgGrad ?>;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <i class="bi <?= $icon ?> fs-1 mb-2 d-block opacity-75"></i>
                        <h5 class="fw-bold mb-1"><?= htmlspecialchars($class['name'], ENT_QUOTES, 'UTF-8') ?></h5>
                        <div class="small opacity-75">
                            <i class="bi bi-calendar-week me-1"></i><?= htmlspecialchars($class['schedule'], ENT_QUOTES, 'UTF-8') ?>
                            &nbsp;&bull;&nbsp;
                            <i class="bi bi-clock me-1"></i><?= htmlspecialchars($class['duration'], ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    </div>
                    <span class="badge bg-white text-dark fs-6 px-3 py-2">
                        <?= $class['student_count'] ?> students
                    </span>
                </div>
            </div>
            <!-- Class Body -->
            <div class="card-body">
                <p class="text-muted small mb-3">
                    <?= htmlspecialchars(substr($class['description'] ?? '', 0, 120), ENT_QUOTES, 'UTF-8') ?>
                    <?= strlen($class['description'] ?? '') > 120 ? '...' : '' ?>
                </p>
                <?php if ($class['start_date']): ?>
                <div class="d-flex gap-3 small text-muted mb-3">
                    <span><i class="bi bi-play-circle me-1 text-success"></i>
                        Start: <?= date('M d, Y', strtotime($class['start_date'])) ?>
                    </span>
                    <span><i class="bi bi-stop-circle me-1 text-danger"></i>
                        End: <?= date('M d, Y', strtotime($class['end_date'])) ?>
                    </span>
                </div>
                <?php endif; ?>
                <div class="d-flex gap-2">
                    <a href="<?= APP_URL ?>/techvoc/class/<?= $class['id'] ?>"
                       class="btn btn-<?= $color ?> flex-fill fw-semibold">
                        <i class="bi bi-person-plus me-2"></i>Manage Students
                    </a>
                    <a href="<?= APP_URL ?>/techvoc/class/<?= $class['id'] ?>/attendance"
                       class="btn btn-outline-<?= $color ?> flex-fill">
                        <i class="bi bi-clipboard-check me-2"></i>Attendance
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
