<?php ob_start(); ?>
<div class="row g-4">
    <!-- Add/Edit Resource Form -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
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
                        <textarea name="description" class="form-control" rows="2"
                                  placeholder="Brief description..."><?= htmlspecialchars($editResource['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
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
                            <option value="available" <?= ($editResource['status'] ?? '') === 'available' ? 'selected' : '' ?>>Available</option>
                            <option value="unavailable" <?= ($editResource['status'] ?? '') === 'unavailable' ? 'selected' : '' ?>>Unavailable</option>
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-2"></i><?= $editResource ? 'Update' : 'Add Resource' ?>
                        </button>
                        <?php if ($editResource): ?>
                            <a href="<?= APP_URL ?>/secretary/resources" class="btn btn-outline-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Allocate Resource -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-arrow-right-circle me-2 text-success"></i>Allocate Resource</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/secretary/resources/allocate">
                    <?= csrfField() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Job Fair <span class="text-danger">*</span></label>
                        <select name="job_fair_request_id" class="form-select" required>
                            <option value="">— Select Job Fair —</option>
                            <?php foreach ($requests as $req): ?>
                                <option value="<?= $req['id'] ?>">
                                    <?= htmlspecialchars($req['title'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Resource <span class="text-danger">*</span></label>
                        <select name="resource_id" class="form-select" required>
                            <option value="">— Select Resource —</option>
                            <?php foreach ($resources as $res): ?>
                                <?php if ($res['status'] === 'available'): ?>
                                    <option value="<?= $res['id'] ?>">
                                        <?= htmlspecialchars($res['name'], ENT_QUOTES, 'UTF-8') ?>
                                        (<?= $res['quantity'] ?> <?= htmlspecialchars($res['unit'] ?? '', ENT_QUOTES, 'UTF-8') ?>)
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Quantity</label>
                        <input type="number" name="quantity_allocated" class="form-control" min="1" value="1">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-arrow-right-circle me-2"></i>Allocate
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Resources List -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-box-seam me-2 text-primary"></i>Barangay Resources (<?= count($resources) ?>)</h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($resources)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-box display-4 d-block mb-2"></i>No resources yet.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Quantity</th>
                                    <th>Unit</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($resources as $r): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="text-muted small"><?= htmlspecialchars($r['description'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= number_format($r['quantity']) ?></td>
                                        <td class="text-muted"><?= htmlspecialchars($r['unit'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= statusBadge($r['status']) ?></td>
                                        <td>
                                            <a href="<?= APP_URL ?>/secretary/resources?edit=<?= $r['id'] ?>"
                                               class="btn btn-sm btn-outline-primary">
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
    </div>
</div>
<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
