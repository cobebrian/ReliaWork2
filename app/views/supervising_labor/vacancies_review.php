<?php ob_start(); ?>

<?php if (!empty($success)): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Stats strip -->
<?php
$pending  = array_filter($vacancies, fn($v) => ($v['sl_status'] ?? 'pending') === 'pending');
$accepted = array_filter($vacancies, fn($v) => ($v['sl_status'] ?? '') === 'accepted');
$rejected = array_filter($vacancies, fn($v) => ($v['sl_status'] ?? '') === 'rejected');
?>
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm border-start border-warning border-4">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-hourglass-split"></i></div>
                <div><div class="stat-value"><?= count($pending) ?></div><div class="stat-label">Pending Review</div></div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm border-start border-success border-4">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-check-circle-fill"></i></div>
                <div><div class="stat-value"><?= count($accepted) ?></div><div class="stat-label">Accepted</div></div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm border-start border-danger border-4">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-danger-subtle text-danger"><i class="bi bi-x-circle-fill"></i></div>
                <div><div class="stat-value"><?= count($rejected) ?></div><div class="stat-label">Rejected</div></div>
            </div>
        </div>
    </div>
</div>

<!-- Filter tabs -->
<div class="d-flex gap-2 mb-3 flex-wrap">
    <button class="btn btn-sm btn-primary filter-btn active" data-filter="all">All (<?= count($vacancies) ?>)</button>
    <button class="btn btn-sm btn-outline-warning filter-btn"  data-filter="pending">Pending (<?= count($pending) ?>)</button>
    <button class="btn btn-sm btn-outline-success filter-btn" data-filter="accepted">Accepted (<?= count($accepted) ?>)</button>
    <button class="btn btn-sm btn-outline-danger filter-btn"  data-filter="rejected">Rejected (<?= count($rejected) ?>)</button>
    <a href="<?= APP_URL ?>/supervising-labor/dashboard" class="btn btn-sm btn-outline-secondary ms-auto">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-briefcase me-2 text-primary"></i>
            Process 5 — Job Vacancies from Agencies
        </h6>
        <small class="text-muted">Review each vacancy and accept to officially add it to the job fair, or reject with a reason.</small>
    </div>
    <div class="card-body p-0">
        <?php if (empty($vacancies)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-briefcase display-4 d-block mb-2 opacity-50"></i>
            No vacancies submitted by agencies yet.
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="vacancyTable">
                <thead class="table-light">
                    <tr>
                        <th>Agency / Job Fair</th>
                        <th>Company</th>
                        <th>Position</th>
                        <th>Slots</th>
                        <th>Contact</th>
                        <th>SL Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($vacancies as $v):
                    $slStatus = $v['sl_status'] ?? 'pending';
                    $statusCfg = [
                        'pending'  => ['color' => 'warning', 'label' => 'Pending Review', 'icon' => 'bi-hourglass-split'],
                        'accepted' => ['color' => 'success', 'label' => 'Accepted',       'icon' => 'bi-check-circle-fill'],
                        'rejected' => ['color' => 'danger',  'label' => 'Rejected',       'icon' => 'bi-x-circle-fill'],
                    ];
                    $cfg = $statusCfg[$slStatus] ?? $statusCfg['pending'];
                ?>
                <tr class="vacancy-row" data-sl-status="<?= $slStatus ?>">
                    <td>
                        <div class="fw-semibold small"><?= htmlspecialchars($v['agency_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="text-muted" style="font-size:.75rem;">
                            <i class="bi bi-calendar-event me-1"></i>
                            <?= htmlspecialchars($v['job_fair_title'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    </td>
                    <td>
                        <div class="fw-semibold small"><?= htmlspecialchars($v['company_name'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php if ($v['company_location']): ?>
                        <div class="text-muted" style="font-size:.75rem;">
                            <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($v['company_location'], ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="fw-semibold"><?= htmlspecialchars($v['position'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php if ($v['qualifications']): ?>
                        <div class="text-muted" style="font-size:.72rem;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                             title="<?= htmlspecialchars($v['qualifications'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars(substr($v['qualifications'], 0, 60), ENT_QUOTES, 'UTF-8') ?>...
                        </div>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge bg-primary fs-6"><?= $v['available_slots'] ?></span></td>
                    <td class="small text-muted">
                        <?php if ($v['mobile_number']): ?>
                        <div><i class="bi bi-phone me-1"></i><?= htmlspecialchars($v['mobile_number'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                        <?php if ($v['gmail_address']): ?>
                        <div><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($v['gmail_address'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge bg-<?= $cfg['color'] ?>">
                            <i class="bi <?= $cfg['icon'] ?> me-1"></i><?= $cfg['label'] ?>
                        </span>
                        <?php if ($v['sl_remarks']): ?>
                        <div class="text-muted small mt-1" style="font-size:.72rem;" title="<?= htmlspecialchars($v['sl_remarks'], ENT_QUOTES, 'UTF-8') ?>">
                            <i class="bi bi-chat-quote me-1"></i><?= htmlspecialchars(substr($v['sl_remarks'], 0, 40), ENT_QUOTES, 'UTF-8') ?>...
                        </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($slStatus === 'pending'): ?>
                        <!-- Accept Button -->
                        <button type="button" class="btn btn-sm btn-success mb-1 w-100 fw-semibold"
                                data-bs-toggle="modal"
                                data-bs-target="#acceptModal_<?= $v['id'] ?>">
                            <i class="bi bi-check-circle me-1"></i>Accept
                        </button>
                        <!-- Reject Button -->
                        <button type="button" class="btn btn-sm btn-outline-danger w-100"
                                data-bs-toggle="modal"
                                data-bs-target="#rejectModal_<?= $v['id'] ?>">
                            <i class="bi bi-x-circle me-1"></i>Reject
                        </button>
                        <?php else: ?>
                        <button type="button" class="btn btn-xs btn-outline-secondary"
                                data-bs-toggle="modal"
                                data-bs-target="#remarksModal_<?= $v['id'] ?>">
                            <i class="bi bi-pencil"></i> Edit
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>

                <!-- ── ACCEPT MODAL ──────────────────────────────────────────── -->
                <div class="modal fade" id="acceptModal_<?= $v['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    Accept Vacancy — <?= htmlspecialchars($v['position'], ENT_QUOTES, 'UTF-8') ?>
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <!-- Vacancy summary -->
                                <div class="card border-0 bg-light mb-3">
                                    <div class="card-body py-2">
                                        <div class="row g-2 small">
                                            <div class="col-md-6">
                                                <strong>Company:</strong> <?= htmlspecialchars($v['company_name'], ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Position:</strong> <?= htmlspecialchars($v['position'], ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Slots:</strong> <?= $v['available_slots'] ?>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Location:</strong> <?= htmlspecialchars($v['company_location'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                            <?php if ($v['qualifications']): ?>
                                            <div class="col-12">
                                                <strong>Qualifications:</strong><br>
                                                <?= nl2br(htmlspecialchars($v['qualifications'], ENT_QUOTES, 'UTF-8')) ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="alert alert-success">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Accepting this vacancy will <strong>officially add it to the job fair</strong>.
                                    The agency will be notified of your decision.
                                </div>
                                <form method="POST" action="<?= APP_URL ?>/supervising-labor/vacancies/<?= $v['id'] ?>/accept">
                                    <?= csrfField() ?>
                                    <div class="mb-0">
                                        <label class="form-label fw-semibold">Remarks / Notes (optional)</label>
                                        <textarea name="sl_remarks" class="form-control" rows="3"
                                                  placeholder="Add any remarks for the agency..."></textarea>
                                    </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success fw-semibold px-4">
                                    <i class="bi bi-check-circle me-2"></i>Confirm Accept
                                </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── REJECT MODAL ──────────────────────────────────────────── -->
                <div class="modal fade" id="rejectModal_<?= $v['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title">
                                    <i class="bi bi-x-circle-fill me-2"></i>Reject Vacancy
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="<?= APP_URL ?>/supervising-labor/vacancies/<?= $v['id'] ?>/reject">
                                <?= csrfField() ?>
                                <div class="modal-body">
                                    <p class="text-muted small mb-3">
                                        Rejecting <strong><?= htmlspecialchars($v['position'], ENT_QUOTES, 'UTF-8') ?></strong>
                                        at <?= htmlspecialchars($v['company_name'], ENT_QUOTES, 'UTF-8') ?>.
                                        The agency will be notified with your reason.
                                    </p>
                                    <div class="mb-0">
                                        <label class="form-label fw-semibold">
                                            Reason for Rejection <span class="text-danger">*</span>
                                        </label>
                                        <textarea name="sl_remarks" class="form-control" rows="3" required
                                                  placeholder="Explain why this vacancy is being rejected..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-danger fw-semibold">
                                        <i class="bi bi-x-circle me-2"></i>Reject Vacancy
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- ── EDIT REMARKS MODAL (for already-processed) ─────────────── -->
                <div class="modal fade" id="remarksModal_<?= $v['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Remarks</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="<?= APP_URL ?>/supervising-labor/vacancies/<?= $v['id'] ?>/remarks">
                                <?= csrfField() ?>
                                <div class="modal-body">
                                    <textarea name="remarks" class="form-control" rows="4"><?= htmlspecialchars($v['remarks'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Filter tabs
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-btn').forEach(b => {
            b.classList.remove('active','btn-primary','btn-warning','btn-success','btn-danger');
            b.classList.add('btn-outline-' + (b.dataset.filter === 'pending' ? 'warning' :
                             b.dataset.filter === 'accepted' ? 'success' :
                             b.dataset.filter === 'rejected' ? 'danger' : 'primary'));
        });
        this.classList.add('active');
        this.classList.remove('btn-outline-primary','btn-outline-warning','btn-outline-success','btn-outline-danger');
        this.classList.add('btn-' + (this.dataset.filter === 'pending' ? 'warning' :
                            this.dataset.filter === 'accepted' ? 'success' :
                            this.dataset.filter === 'rejected' ? 'danger' : 'primary'));

        const filter = this.dataset.filter;
        document.querySelectorAll('.vacancy-row').forEach(row => {
            row.style.display = (filter === 'all' || row.dataset.slStatus === filter) ? '' : 'none';
        });
    });
});
</script>

<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
