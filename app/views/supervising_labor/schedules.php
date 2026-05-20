<?php ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    <a href="<?= APP_URL ?>/supervising-labor/schedules/create" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Create Schedule
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold"><i class="bi bi-calendar3 me-2 text-primary"></i>Schedule of Events</h6>
    </div>
    <div class="card-body p-0">
        <?php if (empty($schedules)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-calendar-x display-4 d-block mb-2"></i>No schedules yet.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Venue</th>
                            <th>Status</th>
                            <th>Created By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedules as $s): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($s['title'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi bi-calendar me-1"></i><?= formatDate($s['event_date']) ?>
                                    </span>
                                </td>
                                <td class="text-muted"><?= $s['event_time'] ? date('h:i A', strtotime($s['event_time'])) : '—' ?></td>
                                <td class="text-muted"><?= htmlspecialchars($s['venue'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= statusBadge($s['status']) ?></td>
                                <td class="text-muted small"><?= htmlspecialchars($s['created_by_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
