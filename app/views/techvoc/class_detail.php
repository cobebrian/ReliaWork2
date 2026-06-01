<?php ob_start();
$isWelding = stripos($class['name'], 'welding') !== false;
$color     = $isWelding ? 'warning' : 'primary';
$icon      = $isWelding ? 'bi-fire' : 'bi-lightning-charge-fill';
$bgGrad    = $isWelding ? 'linear-gradient(135deg,#ff8c00,#ffc107)' : 'linear-gradient(135deg,#0d6efd,#0dcaf0)';
?>

<?php if (!empty($success)): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (!empty($error)): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Class Header -->
<div class="card border-0 shadow-sm mb-4 text-white" style="background:<?= $bgGrad ?>;">
    <div class="card-body py-4">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi <?= $icon ?> fs-1 opacity-75"></i>
                    <div>
                        <h4 class="fw-bold mb-1"><?= htmlspecialchars($class['name'], ENT_QUOTES, 'UTF-8') ?></h4>
                        <div class="small opacity-75">
                            <i class="bi bi-calendar-week me-1"></i><?= htmlspecialchars($class['schedule'], ENT_QUOTES, 'UTF-8') ?>
                            &nbsp;&bull;&nbsp;
                            <i class="bi bi-clock me-1"></i><?= htmlspecialchars($class['duration'], ENT_QUOTES, 'UTF-8') ?>
                            <?php if ($class['start_date']): ?>
                            &nbsp;&bull;&nbsp;
                            <?= date('M d, Y', strtotime($class['start_date'])) ?> –
                            <?= date('M d, Y', strtotime($class['end_date'])) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-auto d-flex gap-2">
                <span class="badge bg-white text-dark fs-6 px-3 py-2">
                    <i class="bi bi-people-fill me-1"></i><?= $class['student_count'] ?> students
                </span>
                <a href="<?= APP_URL ?>/techvoc/class/<?= $class['id'] ?>/attendance"
                   class="btn btn-light btn-sm fw-semibold">
                    <i class="bi bi-clipboard-check me-1"></i>Attendance
                </a>
                <a href="<?= APP_URL ?>/techvoc/dashboard" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">

    <!-- ── Add Student Form ───────────────────────────────────────────────── -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-person-plus me-2 text-<?= $color ?>"></i>Add New Student
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/techvoc/class/<?= $class['id'] ?>/add-student">
                    <?= csrfField() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">
                            Last Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="lastname" class="form-control" required
                               placeholder="dela Cruz">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">
                            First Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="firstname" class="form-control" required
                               placeholder="Juan">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">
                            Middle Name <span class="text-muted fw-normal">(optional)</span>
                        </label>
                        <input type="text" name="middlename" class="form-control" placeholder="Santos">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Age</label>
                            <input type="number" name="age" class="form-control" min="15" max="60" placeholder="18">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="">Select</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Contact Number</label>
                        <input type="text" name="contact_number" class="form-control" placeholder="09XX-XXX-XXXX">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="student@email.com">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Address</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Barangay, City"></textarea>
                    </div>
                    <button type="submit" class="btn btn-<?= $color ?> w-100 fw-semibold">
                        <i class="bi bi-person-plus me-2"></i>Enroll Student
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ── Student List ───────────────────────────────────────────────────── -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-people me-2 text-<?= $color ?>"></i>
                    Enrolled Students
                    <span class="badge bg-<?= $color ?> ms-1"><?= count($students) ?></span>
                </h6>
                <!-- Search -->
                <input type="text" id="studentSearch" class="form-control form-control-sm"
                       style="width:200px;" placeholder="Search student..."
                       oninput="filterStudents(this.value)">
            </div>
            <div class="card-body p-0">
                <?php if (empty($students)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-people display-4 d-block mb-2 opacity-50"></i>
                    No students enrolled yet. Add students using the form on the left.
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="studentTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Age</th>
                                <th>Gender</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($students as $i => $s): ?>
                        <tr class="student-row">
                            <td class="text-muted small"><?= $i + 1 ?></td>
                            <td>
                                <div class="fw-semibold student-name">
                                    <?= htmlspecialchars($s['lastname'] . ', ' . $s['firstname'] . ($s['middlename'] ? ' ' . $s['middlename'] : ''), ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <?php if ($s['address']): ?>
                                <div class="text-muted" style="font-size:.75rem;">
                                    <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($s['address'], ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small"><?= $s['age'] ?? '—' ?></td>
                            <td class="text-muted small"><?= $s['gender'] ? ucfirst($s['gender']) : '—' ?></td>
                            <td class="text-muted small">
                                <?= htmlspecialchars($s['contact_number'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td>
                                <?php
                                $sc = ['active'=>'success','dropped'=>'danger','completed'=>'info'][$s['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $sc ?>"><?= ucfirst($s['status']) ?></span>
                            </td>
                            <td>
                                <form method="POST"
                                      action="<?= APP_URL ?>/techvoc/class/<?= $class['id'] ?>/delete-student"
                                      onsubmit="return confirm('Remove <?= htmlspecialchars(addslashes($s['lastname'] . ', ' . $s['firstname']), ENT_QUOTES) ?> from this class?')">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="student_id" value="<?= $s['id'] ?>">
                                    <button type="submit" class="btn btn-xs btn-outline-danger" title="Remove">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sunday Sessions Preview -->
        <?php if (!empty($sundays)): ?>
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-calendar-week me-2 text-<?= $color ?>"></i>
                    Sunday Sessions (<?= count($sundays) ?> total)
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($sundays as $i => $sunday):
                        $isPast = strtotime($sunday) < strtotime('today');
                        $isToday = $sunday === date('Y-m-d');
                    ?>
                    <a href="<?= APP_URL ?>/techvoc/class/<?= $class['id'] ?>/attendance?date=<?= $sunday ?>"
                       class="badge text-decoration-none <?= $isToday ? 'bg-'.$color.' fs-6' : ($isPast ? 'bg-secondary' : 'bg-light text-dark border') ?>"
                       style="font-size:.78rem;padding:6px 10px;">
                        <?= date('M d', strtotime($sunday)) ?>
                        <?= $isToday ? ' ★' : '' ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <small class="text-muted mt-2 d-block">Click a date to take/view attendance.</small>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function filterStudents(q) {
    q = q.toLowerCase();
    document.querySelectorAll('.student-row').forEach(row => {
        const name = row.querySelector('.student-name')?.textContent.toLowerCase() || '';
        row.style.display = (!q || name.includes(q)) ? '' : 'none';
    });
}
</script>

<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
