<?php ob_start();
$isWelding = stripos($class['name'], 'welding') !== false;
$color     = $isWelding ? 'warning' : 'primary';
?>

<?php if (!empty($success)): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">
            <i class="bi bi-clipboard-check me-2 text-<?= $color ?>"></i>
            Attendance — <?= htmlspecialchars($class['name'], ENT_QUOTES, 'UTF-8') ?>
        </h5>
        <p class="text-muted small mb-0">
            <?= htmlspecialchars($class['schedule'], ENT_QUOTES, 'UTF-8') ?>
            &bull; <?= count($sundays) ?> Sunday sessions
        </p>
    </div>
    <a href="<?= APP_URL ?>/techvoc/class/<?= $class['id'] ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back to Class
    </a>
</div>

<div class="row g-4">

    <!-- ── Take Attendance ───────────────────────────────────────────────── -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-pencil-square me-2 text-<?= $color ?>"></i>Take Attendance
                </h6>
                <!-- Date selector -->
                <select id="dateSelect" class="form-select form-select-sm" style="width:auto;"
                        onchange="window.location.href='<?= APP_URL ?>/techvoc/class/<?= $class['id'] ?>/attendance?date='+this.value">
                    <?php foreach ($sundays as $s): ?>
                    <option value="<?= $s ?>" <?= $s === $selectedDate ? 'selected' : '' ?>>
                        <?= date('D, M d, Y', strtotime($s)) ?>
                        <?= $s === date('Y-m-d') ? ' (Today)' : '' ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="card-body p-0">
                <?php if (empty($records)): ?>
                <div class="text-center py-4 text-muted">No students enrolled.</div>
                <?php else: ?>
                <form method="POST" action="<?= APP_URL ?>/techvoc/class/<?= $class['id'] ?>/attendance/save">
                    <?= csrfField() ?>
                    <input type="hidden" name="session_date" value="<?= htmlspecialchars($selectedDate, ENT_QUOTES, 'UTF-8') ?>">

                    <!-- Quick mark all -->
                    <div class="px-3 py-2 border-bottom bg-light d-flex gap-2 align-items-center">
                        <small class="text-muted fw-semibold me-2">Mark all:</small>
                        <button type="button" class="btn btn-xs btn-success" onclick="markAll('present')">Present</button>
                        <button type="button" class="btn btn-xs btn-danger"  onclick="markAll('absent')">Absent</button>
                        <button type="button" class="btn btn-xs btn-warning text-dark" onclick="markAll('late')">Late</button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Student Name</th>
                                    <th class="text-center">Present</th>
                                    <th class="text-center">Late</th>
                                    <th class="text-center">Absent</th>
                                    <th class="text-center">Excused</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($records as $i => $r): ?>
                            <tr>
                                <td class="text-muted small"><?= $i + 1 ?></td>
                                <td class="fw-semibold small">
                                    <?= htmlspecialchars($r['lastname'] . ', ' . $r['firstname'], ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <?php foreach (['present','late','absent','excused'] as $status): ?>
                                <td class="text-center">
                                    <input type="radio"
                                           name="attendance[<?= $r['student_id'] ?>]"
                                           value="<?= $status ?>"
                                           class="form-check-input att-radio"
                                           <?= $r['attendance_status'] === $status ? 'checked' : '' ?>>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3 border-top">
                        <button type="submit" class="btn btn-<?= $color ?> w-100 fw-semibold">
                            <i class="bi bi-save me-2"></i>Save Attendance for <?= date('M d, Y', strtotime($selectedDate)) ?>
                        </button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── Attendance Summary ─────────────────────────────────────────────── -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-bar-chart me-2 text-<?= $color ?>"></i>Attendance Summary
                </h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($summary)): ?>
                <div class="text-center py-4 text-muted small">No data yet.</div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th class="text-center text-success">P</th>
                                <th class="text-center text-warning">L</th>
                                <th class="text-center text-danger">A</th>
                                <th class="text-center">%</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($summary as $s):
                            $total = max(1, (int)$s['total_sessions']);
                            $pct   = round(($s['present'] / $total) * 100);
                            $pctColor = $pct >= 75 ? 'success' : ($pct >= 50 ? 'warning' : 'danger');
                        ?>
                        <tr>
                            <td class="small fw-semibold">
                                <?= htmlspecialchars($s['lastname'] . ', ' . $s['firstname'], ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td class="text-center small text-success fw-bold"><?= $s['present'] ?></td>
                            <td class="text-center small text-warning fw-bold"><?= $s['late'] ?></td>
                            <td class="text-center small text-danger fw-bold"><?= $s['absent'] ?></td>
                            <td class="text-center">
                                <span class="badge bg-<?= $pctColor ?>"><?= $pct ?>%</span>
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

<script>
function markAll(status) {
    document.querySelectorAll('.att-radio[value="' + status + '"]').forEach(r => r.checked = true);
}
</script>

<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
