<?php ob_start(); ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">Confirm Details - <?= htmlspecialchars($request['title'], ENT_QUOTES, 'UTF-8') ?></h6>
        <a href="<?= APP_URL ?>/secretary/requests" class="btn btn-sm btn-outline-secondary">Back</a>
    </div>
    <div class="card-body">
        <?php $error = getFlash('error'); $success = getFlash('success'); ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

        <form method="POST" action="<?= APP_URL ?>/secretary/requests/<?= $request['id'] ?>/confirm">
            <?= csrfField() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Date / Time</label>
                    <input type="datetime-local" name="date_time" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Recipient</label>
                    <input type="text" name="recipient" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Mobile No.</label>
                    <input type="text" name="mobile_no" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Signature (Printed Name)</label>
                    <input type="text" name="signature" class="form-control">
                </div>
                <div class="col-12">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="mt-3">
                <button class="btn btn-success">Confirm and Notify Barangay Captain</button>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
