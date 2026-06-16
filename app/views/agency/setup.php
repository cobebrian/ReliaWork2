<?php ob_start(); ?>

<div class="row justify-content-center">
<div class="col-lg-5">
<div class="card border-0 shadow">
    <div class="card-header py-4 text-white" style="background:linear-gradient(135deg,#0d6efd,#0dcaf0);">
        <div class="text-center">
            <i class="bi bi-building-fill fs-1 mb-2 d-block opacity-75"></i>
            <h5 class="fw-bold mb-1">Set Up Your Agency Profile</h5>
            <p class="small opacity-75 mb-0">This information will be visible to Supervising Labor when inviting agencies.</p>
        </div>
    </div>
    <div class="card-body p-4">

        <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?= APP_URL ?>/agency/setup">
            <?= csrfField() ?>

            <div class="mb-4">
                <label class="form-label fw-semibold">
                    Company / Agency Name <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-building"></i></span>
                    <input type="text" name="agency_name" class="form-control form-control-lg"
                           placeholder="e.g. ABC Recruitment Corp"
                           value="<?= htmlspecialchars($user['agency_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           required>
                </div>
                <small class="text-muted">This name will appear in the Supervising Labor's company list.</small>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">
                    Company Location <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                    <input type="text" name="agency_location" class="form-control form-control-lg"
                           placeholder="e.g. Cebu City, Cebu"
                           value="<?= htmlspecialchars($user['agency_location'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-3 fw-semibold fs-6">
                <i class="bi bi-check-circle-fill me-2"></i>Save Profile & Continue
            </button>
        </form>
    </div>
</div>
</div>
</div>

<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
