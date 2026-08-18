<?php $pageTitle = 'Application Status'; ?>
<?php ob_start(); ?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= APP_URL ?>/org/dashboard" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Dashboard
    </a>
    <h5 class="mb-0 fw-bold">Organization Application — <?= htmlspecialchars($application['org_name'], ENT_QUOTES) ?></h5>
</div>

<?php
$statusCfg = [
    'pending'        => ['warning', 'bi-hourglass-split',   'Pending Review'],
    'under_review'   => ['primary', 'bi-eye-fill',          'Under Review'],
    'needs_revision' => ['info',    'bi-arrow-clockwise',   'Needs Revision'],
    'rejected'       => ['danger',  'bi-x-circle-fill',     'Rejected'],
    'approved'       => ['success', 'bi-check-circle-fill', 'Approved'],
];
[$c, $ic, $t] = $statusCfg[$application['status']] ?? ['secondary','bi-question-circle','Unknown'];
?>

<div class="row g-4">
    <div class="col-lg-7">
        <!-- Application Details -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between">
                <h6 class="mb-0 fw-bold"><i class="bi bi-file-earmark-text me-2"></i>Application Details</h6>
                <span class="badge bg-<?= $c ?> fs-6">
                    <i class="bi <?= $ic ?> me-1"></i><?= $t ?>
                </span>
            </div>
            <div class="card-body small">
                <?php
                $fields = [
                    'Organization Name' => $application['org_name'],
                    'Barangay'          => $application['barangay'],
                    'Municipality'      => $application['municipality'] ?? '—',
                    'Province'          => $application['province'] ?? '—',
                    'Address'           => $application['address'] ?? '—',
                    'Contact Email'     => $application['contact_email'] ?? '—',
                    'Contact Phone'     => $application['contact_phone'] ?? '—',
                    'Submitted'         => date('F d, Y g:i A', strtotime($application['submitted_at'])),
                ];
                foreach ($fields as $lbl => $val): ?>
                <div class="row mb-1">
                    <div class="col-4 text-muted"><?= $lbl ?></div>
                    <div class="col-8 fw-semibold"><?= htmlspecialchars($val, ENT_QUOTES) ?></div>
                </div>
                <?php endforeach; ?>
                <?php if (!empty($application['review_notes'])): ?>
                <div class="alert alert-light border mt-3 mb-0 py-2">
                    <strong>Admin Notes:</strong><br>
                    <?= nl2br(htmlspecialchars($application['review_notes'], ENT_QUOTES)) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Uploaded Documents -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-2">
                <h6 class="mb-0 fw-bold small"><i class="bi bi-folder me-2 text-primary"></i>Submitted Documents (<?= count($documents) ?>)</h6>
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
    </div>

    <!-- Status Timeline -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-2">
                <h6 class="mb-0 fw-bold small"><i class="bi bi-clock-history me-2 text-muted"></i>Application History</h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($history)): ?>
                <div class="text-center py-4 text-muted small">No history yet.</div>
                <?php else: ?>
                <?php foreach ($history as $h): ?>
                <div class="d-flex gap-3 px-3 py-3 border-bottom align-items-start">
                    <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:28px;height:28px;font-size:.65rem;">
                        <i class="bi bi-arrow-right"></i>
                    </div>
                    <div class="small">
                        <div class="fw-semibold">
                            <?= $h['from_status'] ? ucfirst(str_replace('_',' ',$h['from_status'])) . ' → ' : '' ?>
                            <span class="text-primary"><?= ucfirst(str_replace('_',' ',$h['to_status'])) ?></span>
                        </div>
                        <?php if (!empty($h['notes'])): ?>
                        <div class="text-muted"><?= htmlspecialchars($h['notes'], ENT_QUOTES) ?></div>
                        <?php endif; ?>
                        <div class="text-muted" style="font-size:.68rem;">
                            <?= date('M d, Y g:i A', strtotime($h['changed_at'])) ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
