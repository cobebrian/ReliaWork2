<?php ob_start(); ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-clipboard-check me-2 text-primary"></i>Review Job Fair Request</h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Title</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($request['title'], ENT_QUOTES, 'UTF-8') ?></dd>

                    <dt class="col-sm-3">Requested By</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($request['requested_by_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></dd>

                    <dt class="col-sm-3">Requested Date</dt>
                    <dd class="col-sm-9"><?= formatDate($request['requested_date']) ?></dd>

                    <dt class="col-sm-3">Venue</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($request['venue'] ?? '—', ENT_QUOTES, 'UTF-8') ?></dd>

                    <dt class="col-sm-3">Description</dt>
                    <dd class="col-sm-9"><?= nl2br(htmlspecialchars($request['description'] ?? '—', ENT_QUOTES, 'UTF-8')) ?></dd>

                    <dt class="col-sm-3">Status</dt>
                    <dd class="col-sm-9"><?= statusBadge($request['status']) ?></dd>

                    <dt class="col-sm-3">Submitted</dt>
                    <dd class="col-sm-9"><?= formatDate($request['created_at'], 'M d, Y h:i A') ?></dd>
                </dl>
            </div>
        </div>

        <?php if ($request['status'] === 'pending'): ?>
            <div class="row g-3">
                <!-- Approve -->
                <div class="col-md-6">
                    <div class="card border-success border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-success fw-bold mb-3"><i class="bi bi-check-circle me-2"></i>Approve Request</h6>
                            <form method="POST" action="<?= APP_URL ?>/supervising-labor/requests/<?= $request['id'] ?>/approve">
                                <?= csrfField() ?>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Remarks (optional)</label>
                                    <textarea name="remarks" class="form-control" rows="3"
                                              placeholder="Add any notes..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-success w-100"
                                        onclick="return confirm('Approve this job fair request?')">
                                    <i class="bi bi-check-lg me-2"></i>Approve & Schedule
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Reject -->
                <div class="col-md-6">
                    <div class="card border-danger border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-danger fw-bold mb-3"><i class="bi bi-x-circle me-2"></i>Reject Request</h6>
                            <form method="POST" action="<?= APP_URL ?>/supervising-labor/requests/<?= $request['id'] ?>/reject">
                                <?= csrfField() ?>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Reason for Rejection <span class="text-danger">*</span></label>
                                    <textarea name="remarks" class="form-control" rows="3" required
                                              placeholder="Explain why this request is rejected..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-danger w-100"
                                        onclick="return confirm('Reject this job fair request?')">
                                    <i class="bi bi-x-lg me-2"></i>Reject Request
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                This request has already been <strong><?= $request['status'] ?></strong>.
                <?php if ($request['remarks']): ?>
                    <br>Remarks: <?= htmlspecialchars($request['remarks'], ENT_QUOTES, 'UTF-8') ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="mt-3">
            <a href="<?= APP_URL ?>/supervising-labor/requests" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Requests
            </a>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
