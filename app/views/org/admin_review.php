<?php $pageTitle = $pageTitle ?? 'Review Application'; ?>
<?php ob_start(); ?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= APP_URL ?>/admin/org-applications" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>All Applications
    </a>
    <h5 class="mb-0 fw-bold"><?= htmlspecialchars($application['org_name'], ENT_QUOTES) ?></h5>
    <?php
    $sColors = ['pending'=>'warning text-dark','under_review'=>'primary','approved'=>'success','rejected'=>'danger','needs_revision'=>'info text-dark'];
    $sColor  = $sColors[$application['status']] ?? 'secondary';
    ?>
    <span class="badge bg-<?= $sColor ?>"><?= ucfirst(str_replace('_',' ',$application['status'])) ?></span>
</div>

<?php if ($success): ?><div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($success, ENT_QUOTES) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES) ?></div><?php endif; ?>

<div class="row g-4">
    <!-- Left: Application Info + Docs -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-dark text-white py-2">
                <h6 class="mb-0 small"><i class="bi bi-person me-2"></i>Applicant & Organization</h6>
            </div>
            <div class="card-body small">
                <?php
                $fields = [
                    'Applicant Name'  => $application['applicant_name'],
                    'Email'           => $application['applicant_email'],
                    'Org Name'        => $application['org_name'],
                    'Barangay'        => $application['barangay'],
                    'Municipality'    => $application['municipality'] ?? '—',
                    'Province'        => $application['province'] ?? '—',
                    'Address'         => $application['address'] ?? '—',
                    'Contact Email'   => $application['contact_email'] ?? '—',
                    'Contact Phone'   => $application['contact_phone'] ?? '—',
                    'Submitted'       => date('F d, Y', strtotime($application['submitted_at'])),
                ];
                foreach ($fields as $l => $v): ?>
                <div class="row mb-1">
                    <div class="col-4 text-muted"><?= $l ?></div>
                    <div class="col-8 fw-semibold text-break"><?= htmlspecialchars($v, ENT_QUOTES) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2">
                <h6 class="mb-0 small fw-bold"><i class="bi bi-folder me-2 text-primary"></i>Documents (<?= count($documents) ?>)</h6>
            </div>
            <?php if (empty($documents)): ?>
            <div class="card-body text-muted small text-center py-3">No documents uploaded.</div>
            <?php else: ?>
            <div class="card-body p-0">
                <?php foreach ($documents as $d): ?>
                <a href="<?= APP_URL . htmlspecialchars($d['file_path'], ENT_QUOTES) ?>"
                   target="_blank"
                   class="d-flex align-items-center gap-2 px-3 py-2 border-bottom text-decoration-none text-dark small">
                    <i class="bi bi-file-earmark-text text-primary"></i>
                    <span class="flex-grow-1"><?= htmlspecialchars($d['original_name'], ENT_QUOTES) ?></span>
                    <span class="badge bg-secondary"><?= ucfirst(str_replace('_',' ',$d['doc_type'])) ?></span>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- History -->
        <?php if (!empty($history)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-2">
                <h6 class="mb-0 small fw-bold"><i class="bi bi-clock-history me-2 text-muted"></i>History</h6>
            </div>
            <div class="card-body p-0">
                <?php foreach ($history as $h): ?>
                <div class="px-3 py-2 border-bottom small">
                    <span class="fw-semibold"><?= ucfirst(str_replace('_',' ',$h['to_status'])) ?></span>
                    <span class="text-muted ms-1">by <?= htmlspecialchars($h['changed_by_name'] ?? '—', ENT_QUOTES) ?></span>
                    <span class="text-muted ms-2" style="font-size:.65rem;"><?= date('M d, Y', strtotime($h['changed_at'])) ?></span>
                    <?php if ($h['notes']): ?>
                    <div class="text-muted"><?= htmlspecialchars($h['notes'], ENT_QUOTES) ?></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Right: Decision -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-shield-check me-2 text-success"></i>Admin Decision</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/admin/org-applications/<?= $application['id'] ?>/decide">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Admin Notes / Review Comments</label>
                        <textarea name="review_notes" class="form-control form-control-sm" rows="4"
                                  placeholder="Explain your decision or provide instructions to the applicant..."
                        ><?= htmlspecialchars($application['review_notes'] ?? '', ENT_QUOTES) ?></textarea>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" name="action" value="under_review"
                                class="btn btn-primary">
                            <i class="bi bi-eye me-1"></i>Mark Under Review
                        </button>
                        <button type="submit" name="action" value="approve"
                                class="btn btn-success"
                                onclick="return confirm('APPROVE this application? This will create the organization and make the applicant Organization Admin.')">
                            <i class="bi bi-check-circle me-1"></i>Approve
                        </button>
                        <button type="submit" name="action" value="needs_revision"
                                class="btn btn-info"
                                onclick="return confirm('Request revision from the applicant?')">
                            <i class="bi bi-arrow-clockwise me-1"></i>Needs Revision
                        </button>
                        <button type="submit" name="action" value="reject"
                                class="btn btn-danger"
                                onclick="return confirm('REJECT this application?')">
                            <i class="bi bi-x-circle me-1"></i>Reject
                        </button>
                    </div>
                </form>

                <?php if ($application['status'] === 'approved'): ?>
                <div class="alert alert-success mt-3 mb-0">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    This application was approved. Organization has been created.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
