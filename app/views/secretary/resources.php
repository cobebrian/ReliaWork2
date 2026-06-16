<?php ob_start(); ?>

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

<div class="row g-4">

    <!-- ── Left: Forms ───────────────────────────────────────────────────── -->
    <div class="col-lg-4">

        <!-- Add/Edit Resource -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-<?= $editResource ? 'pencil' : 'plus-circle' ?> me-2 text-primary"></i>
                    <?= $editResource ? 'Edit Resource' : 'Add Resource' ?>
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= $editResource
                    ? APP_URL . '/secretary/resources/' . $editResource['id'] . '/update'
                    : APP_URL . '/secretary/resources/store' ?>">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Resource Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required
                               value="<?= htmlspecialchars($editResource['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="e.g. Monobloc Chairs">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Description</label>
                        <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($editResource['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Quantity</label>
                            <input type="number" name="quantity" class="form-control" min="0"
                                   value="<?= $editResource['quantity'] ?? 0 ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Unit</label>
                            <input type="text" name="unit" class="form-control"
                                   value="<?= htmlspecialchars($editResource['unit'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                   placeholder="pieces, sets...">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Status</label>
                        <select name="status" class="form-select">
                            <option value="available" <?= ($editResource['status'] ?? '') === 'available'   ? 'selected' : '' ?>>Available</option>
                            <option value="unavailable" <?= ($editResource['status'] ?? '') === 'unavailable' ? 'selected' : '' ?>>Unavailable</option>
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i><?= $editResource ? 'Update' : 'Add Resource' ?>
                        </button>
                        <?php if ($editResource): ?>
                        <a href="<?= APP_URL ?>/secretary/resources" class="btn btn-outline-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Confirm Resources for Job Fair (Process 6) -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-clipboard2-check me-2 text-success"></i>
                    Process 6 — Confirm Available Resources
                </h6>
                <small class="text-muted">Check resources needed for a job fair and confirm.</small>
            </div>
            <div class="card-body">
                <?php if (empty($requests)): ?>
                <p class="text-muted small mb-0">No approved job fairs at the moment.</p>
                <?php else: ?>
                <form method="POST" action="<?= APP_URL ?>/secretary/resources/confirm">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Job Fair <span class="text-danger">*</span></label>
                        <select name="job_fair_request_id" class="form-select" required>
                            <option value="">— Select Job Fair —</option>
                            <?php foreach ($requests as $req): ?>
                            <option value="<?= $req['id'] ?>">
                                <?= htmlspecialchars($req['title'], ENT_QUOTES, 'UTF-8') ?>
                                (<?= formatDate($req['requested_date']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <label class="form-label fw-semibold small">
                        Check resources available for this event:
                    </label>

                    <?php foreach ($resources as $res): ?>
                    <?php if ($res['status'] === 'available'): ?>
                    <div class="form-check mb-2 p-3 border rounded bg-light">
                        <input class="form-check-input" type="checkbox"
                               name="resource_ids[]"
                               value="<?= $res['id'] ?>"
                               id="res_<?= $res['id'] ?>">
                        <label class="form-check-label w-100" for="res_<?= $res['id'] ?>">
                            <div class="d-flex justify-content-between">
                                <span class="fw-semibold"><?= htmlspecialchars($res['name'], ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="badge bg-success"><?= number_format($res['quantity']) ?> <?= htmlspecialchars($res['unit'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <?php if ($res['description']): ?>
                            <small class="text-muted"><?= htmlspecialchars($res['description'], ENT_QUOTES, 'UTF-8') ?></small>
                            <?php endif; ?>
                        </label>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>

                    <div class="mt-3">
                        <label class="form-label fw-semibold small">Notes (optional)</label>
                        <textarea name="notes" class="form-control" rows="2"
                                  placeholder="Any additional notes..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-success w-100 mt-3 fw-semibold">
                        <i class="bi bi-send-check me-2"></i>Confirm &amp; Notify Supervising Labor
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── Right: Resources List ──────────────────────────────────────────── -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-box-seam me-2 text-primary"></i>
                    Barangay Resources <span class="badge bg-primary ms-1"><?= count($resources) ?></span>
                </h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($resources)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-box display-4 d-block mb-2 opacity-50"></i>No resources yet.
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Name</th><th>Description</th><th>Quantity</th><th>Unit</th><th>Status</th><th></th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($resources as $r): ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-muted small"><?= htmlspecialchars($r['description'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span class="badge bg-<?= $r['quantity'] > 0 ? 'primary' : 'secondary' ?>"><?= number_format($r['quantity']) ?></span></td>
                            <td class="text-muted small"><?= htmlspecialchars($r['unit'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= statusBadge($r['status']) ?></td>
                            <td>
                                <a href="<?= APP_URL ?>/secretary/resources?edit=<?= $r['id'] ?>"
                                   class="btn btn-xs btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Confirmed resource allocations -->
        <?php if (!empty($confirmations)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-check2-all me-2 text-success"></i>
                    Confirmed Resource Allocations
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Job Fair</th><th>Resource</th><th>Qty</th><th>Confirmed</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($confirmations as $c): ?>
                        <tr>
                            <td class="small"><?= htmlspecialchars($c['job_fair_title'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="small fw-semibold"><?= htmlspecialchars($c['resource_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span class="badge bg-primary"><?= $c['quantity_allocated'] ?></span></td>
                            <td class="text-muted small"><?= formatDate($c['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
