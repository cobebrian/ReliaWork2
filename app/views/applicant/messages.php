<?php $pageTitle = $pageTitle ?? 'Messages'; ?>
<?php ob_start(); ?>

<style>
.msg-bubble { max-width: 75%; }
.msg-agency    { background: #0d6efd; color:#fff; border-radius:16px 16px 4px 16px; }
.msg-applicant { background: #f0f0f0; color:#000; border-radius:16px 16px 16px 4px; }
.chat-box { max-height: 400px; overflow-y: auto; display:flex; flex-direction:column; gap:12px; }
</style>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= APP_URL ?>/applicant/my-applications" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>My Applications
    </a>
    <div>
        <h5 class="mb-0 fw-bold"><?= htmlspecialchars($app['agency_name'], ENT_QUOTES) ?></h5>
        <small class="text-muted">
            <?= htmlspecialchars($app['position'], ENT_QUOTES) ?> &bull;
            <?= htmlspecialchars($app['fair_title'] ?? '', ENT_QUOTES) ?>
        </small>
    </div>
    <?php
    $statusCfg = [
        'qualified_for_contact'  => ['success', 'Qualified – For Contact'],
        'waitlisted'             => ['info',    'Waitlisted'],
        'awaiting_requirements'  => ['warning', 'Upload Your Documents'],
        'requirements_submitted' => ['primary', 'Documents Submitted'],
        'first_day_scheduled'    => ['dark',    'First Day Scheduled'],
        'hired'                  => ['success', '🏆 Hired!'],
        'not_qualified'          => ['danger',  'Not Qualified'],
    ];
    [$sColor, $sLabel] = $statusCfg[$app['status']] ?? ['secondary', ucfirst($app['status'])];
    ?>
    <span class="badge bg-<?= $sColor ?> ms-auto"><?= $sLabel ?></span>
</div>

<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show">
    <?= htmlspecialchars($success, ENT_QUOTES) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
<?php endif; ?>

<!-- First Day Info Banner -->
<?php if ($app['status'] === 'first_day_scheduled' && !empty($app['first_day_date'])): ?>
<div class="alert alert-dark d-flex align-items-start gap-3 mb-4">
    <i class="bi bi-calendar-check-fill fs-3 flex-shrink-0"></i>
    <div>
        <strong>Your First Day is Scheduled!</strong><br>
        <span class="fw-semibold"><?= date('F d, Y', strtotime($app['first_day_date'])) ?></span>
        <?= !empty($app['first_day_time']) ? ' at <strong>' . date('g:i A', strtotime($app['first_day_time'])) . '</strong>' : '' ?>
        <?php if (!empty($app['first_day_location'])): ?>
        <br>Report to: <strong><?= htmlspecialchars($app['first_day_location'], ENT_QUOTES) ?></strong>
        <?php endif; ?>
        <?php if (!empty($app['first_day_notes'])): ?>
        <br><em><?= htmlspecialchars($app['first_day_notes'], ENT_QUOTES) ?></em>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-7">
        <!-- Chat -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2">
                <h6 class="mb-0 fw-bold small"><i class="bi bi-chat-dots me-2 text-primary"></i>Messages</h6>
            </div>
            <div class="card-body">
                <div class="chat-box mb-3 p-2" id="chatBox">
                    <?php if (empty($messages)): ?>
                    <div class="text-center text-muted small py-3">No messages yet.</div>
                    <?php else: ?>
                    <?php foreach ($messages as $msg):
                        $isMine = $msg['sender_role'] === 'applicant';
                    ?>
                    <div class="d-flex <?= $isMine ? 'justify-content-end' : 'justify-content-start' ?>">
                        <div class="msg-bubble px-3 py-2 <?= $isMine ? 'msg-applicant' : 'msg-agency' ?>">
                            <div class="small" style="white-space:pre-wrap;"><?= htmlspecialchars($msg['message'], ENT_QUOTES) ?></div>
                            <div class="text-<?= $isMine ? 'muted' : 'white-50' ?>" style="font-size:.65rem;margin-top:2px;">
                                <?= htmlspecialchars($msg['sender_name'], ENT_QUOTES) ?> &bull;
                                <?= date('M d, g:i A', strtotime($msg['sent_at'])) ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <!-- Reply -->
                <form method="POST" action="<?= APP_URL ?>/applicant/messages/<?= $app['id'] ?>/reply">
                    <?= csrfField() ?>
                    <div class="input-group">
                        <textarea name="message" class="form-control form-control-sm" rows="2"
                                  placeholder="Type a reply..." required></textarea>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right: Employment Documents Upload -->
    <div class="col-lg-5">
        <?php if ($app['status'] === 'awaiting_requirements'): ?>
        <div class="card border-warning border-0 shadow-sm mb-3">
            <div class="card-header bg-warning text-dark py-2">
                <h6 class="mb-0 small fw-bold">
                    <i class="bi bi-folder-plus me-2"></i>Submit Employment Requirements
                </h6>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">
                    Please upload the following documents. Accepted: PDF, Word, images (max 5MB each).
                </p>
                <form method="POST"
                      action="<?= APP_URL ?>/applicant/messages/<?= $app['id'] ?>/upload-employment-docs"
                      enctype="multipart/form-data">
                    <?= csrfField() ?>
                    <?php
                    $empDocDefs = [
                        'sss_id'       => ['SSS ID / Number',       'text-danger',   true],
                        'philhealth_id'=> ['PhilHealth ID / Number', 'text-info',     true],
                        'tin'          => ['TIN Number / Document',  'text-success',  true],
                        'nbi_clearance'=> ['NBI Clearance',          'text-warning',  false],
                        'medical'      => ['Medical Certificate',    'text-secondary',false],
                        'other'        => ['Other Document',         'text-muted',    false],
                    ];
                    foreach ($empDocDefs as $field => [$label, $cls, $req]): ?>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold <?= $cls ?>">
                            <?= $label ?><?= $req ? ' <span class="text-danger">*</span>' : '' ?>
                        </label>
                        <input type="file" name="<?= $field ?>" class="form-control form-control-sm"
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                               <?= $req ? 'required' : '' ?>>
                    </div>
                    <?php endforeach; ?>
                    <button type="submit" class="btn btn-warning btn-sm w-100 mt-2"
                            onclick="return confirm('Submit your employment documents?')">
                        <i class="bi bi-cloud-upload me-1"></i>Submit Employment Documents
                    </button>
                </form>
            </div>
        </div>

        <?php elseif (!empty($empDocs)): ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-success text-white py-2">
                <h6 class="mb-0 small fw-bold">
                    <i class="bi bi-folder-check me-2"></i>Submitted Documents (<?= count($empDocs) ?>)
                </h6>
            </div>
            <div class="card-body p-0">
                <?php foreach ($empDocs as $d): ?>
                <a href="<?= APP_URL . htmlspecialchars($d['file_path'], ENT_QUOTES) ?>"
                   target="_blank"
                   class="d-flex align-items-center gap-2 px-3 py-2 border-bottom text-decoration-none text-dark small">
                    <i class="bi bi-file-earmark-check text-success"></i>
                    <span class="flex-grow-1 text-truncate"><?= htmlspecialchars($d['original_name'], ENT_QUOTES) ?></span>
                    <span class="badge bg-secondary"><?= ucfirst(str_replace('_',' ',$d['doc_type'])) ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- What to Expect -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-2">
                <h6 class="mb-0 small fw-bold"><i class="bi bi-map me-2 text-primary"></i>Hiring Journey</h6>
            </div>
            <div class="card-body p-0">
                <?php
                $steps = [
                    ['qualified_for_contact','success','Qualified — Waiting for Contact'],
                    ['awaiting_requirements','warning','Upload Employment Requirements'],
                    ['requirements_submitted','primary','Requirements Under Review'],
                    ['first_day_scheduled','dark','First Day Scheduled'],
                    ['hired','success','Officially Hired 🎉'],
                ];
                foreach ($steps as [$step, $color, $label]):
                    $current = $app['status'] === $step;
                    $past    = array_search($app['status'], array_column($steps, 0)) >
                               array_search($step, array_column($steps, 0));
                ?>
                <div class="d-flex gap-2 align-items-center px-3 py-2 border-bottom small
                            <?= $current ? 'bg-'.$color.'-subtle fw-bold' : '' ?>">
                    <i class="bi <?= $past || $current ? 'bi-check-circle-fill text-'.$color : 'bi-circle text-muted' ?>"></i>
                    <?= $label ?>
                    <?php if ($current): ?>
                    <span class="badge bg-<?= $color ?> ms-auto">Current</span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
const chatBox = document.getElementById('chatBox');
if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
</script>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
