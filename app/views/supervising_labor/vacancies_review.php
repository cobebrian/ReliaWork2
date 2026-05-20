<?php ob_start(); ?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-briefcase me-2 text-primary"></i>Vacancies from Agencies</h6>
        <a href="<?= APP_URL ?>/supervising-labor/dashboard" class="btn btn-sm btn-outline-secondary">Back</a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($vacancies)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-briefcase display-4 d-block mb-2"></i>No vacancies posted by agencies yet.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Agency</th>
                            <th>Company</th>
                            <th>Position</th>
                            <th>Slots</th>
                            <th>Location</th>
                            <th>Mobile / Email</th>
                            <th>Posted</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vacancies as $v): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($v['agency_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($v['company_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($v['position'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><span class="badge bg-primary"><?= $v['available_slots'] ?></span></td>
                                <td class="text-muted small"><?= htmlspecialchars($v['company_location'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-muted small">
                                    <?php if ($v['mobile_number']): ?><?= htmlspecialchars($v['mobile_number'], ENT_QUOTES, 'UTF-8') ?><br><?php endif; ?>
                                    <?php if ($v['gmail_address']): ?><?= htmlspecialchars($v['gmail_address'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
                                </td>
                                <td class="text-muted small"><?= formatDate($v['created_at']) ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#remarksModal_<?= $v['id'] ?>">
                                        <i class="bi bi-pencil-square me-1"></i>Add Remarks
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal for adding remarks -->
                            <div class="modal fade" id="remarksModal_<?= $v['id'] ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Remarks — <?= htmlspecialchars($v['company_name'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($v['position'], ENT_QUOTES, 'UTF-8') ?>)</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST" action="<?= APP_URL ?>/supervising-labor/vacancies/<?= $v['id'] ?>/remarks">
                                            <?= csrfField() ?>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Requirements / Qualifications</label>
                                                    <div class="alert alert-light border">
                                                        <?php if (!empty($v['qualifications'])): ?>
                                                            <?= nl2br(htmlspecialchars($v['qualifications'], ENT_QUOTES, 'UTF-8')) ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">No qualifications specified.</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">My Remarks</label>
                                                    <textarea name="remarks" class="form-control" rows="4" placeholder="Enter any remarks or feedback for this vacancy..."><?= htmlspecialchars($v['remarks'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                                                    <small class="text-muted">Provide feedback, recommendations, or concerns about this vacancy.</small>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Save Remarks</button>
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
<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
