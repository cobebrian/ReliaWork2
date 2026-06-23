<?php $pageTitle = 'My Interviews'; ?>
<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-camera-video me-2 text-primary"></i>My Interviews</h4>
    <a href="<?= APP_URL ?>/applicant/dashboard" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Dashboard
    </a>
</div>

<?php if (empty($interviews)): ?>
    <div class="text-center py-5 text-muted">
        <i class="bi bi-camera-video display-3 d-block mb-3 opacity-50"></i>
        <h5>No Interviews Yet</h5>
        <p class="small">Once an agency schedules an interview with you, it will appear here.</p>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($interviews as $iv):
            $colors = ['scheduled'=>'warning','in_progress'=>'primary','completed'=>'success','cancelled'=>'secondary'];
            $color  = $colors[$iv['status']] ?? 'secondary';
        ?>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-semibold"><?= htmlspecialchars($iv['agency_name'], ENT_QUOTES) ?></span>
                    <span class="badge bg-<?= $color ?>"><?= ucfirst($iv['status']) ?></span>
                </div>
                <div class="card-body small">
                    <div class="mb-1"><span class="text-muted">Job Fair:</span> <?= htmlspecialchars($iv['fair_title'], ENT_QUOTES) ?></div>
                    <div class="mb-1"><span class="text-muted">Scheduled:</span>
                        <?= !empty($iv['scheduled_at']) ? date('F d, Y g:i A', strtotime($iv['scheduled_at'])) : 'TBD' ?>
                    </div>
                    <div><span class="text-muted">Questions:</span> <?= $iv['question_count'] ?></div>
                    <?php if ($iv['status'] === 'completed'): ?>
                        <div class="mt-2 text-success fw-semibold">
                            <i class="bi bi-check-circle-fill me-1"></i>Interview Completed
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
