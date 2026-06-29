<?php $pageTitle = $pageTitle ?? 'Applicant Profile'; ?>
<?php ob_start(); ?>

<style>
.msg-bubble { max-width: 75%; }
.msg-agency { background: #0d6efd; color: #fff; border-radius: 16px 16px 4px 16px; }
.msg-applicant { background: #f0f0f0; color: #000; border-radius: 16px 16px 16px 4px; }
.chat-box { max-height: 320px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; }
</style>

<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show">
    <?= htmlspecialchars($success, ENT_QUOTES) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
<?php endif; ?>

<!-- Status + Back -->
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= APP_URL ?>/agency/hiring" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
    <h5 class="mb-0 fw-bold">
        <?= htmlspecialchars(strtoupper($app['surname']) . ', ' . $app['firstname'], ENT_QUOTES) ?>
    </h5>
    <?php
    $statusCfg = [
        'qualified_for_contact'  => ['success', 'Qualified – For Contact'],
        'waitlisted'             => ['info',    'Waitlisted'],
        'awaiting_requirements'  => ['warning', 'Awaiting Requirements'],
        'requirements_submitted' => ['primary', 'Requirements Submitted'],
        'first_day_scheduled'    => ['dark',    'First Day Scheduled'],
        'hired'                  => ['success', '🏆 Officially Hired'],
        'not_qualified'          => ['danger',  'Not Qualified'],
    ];
    [$sColor, $sLabel] = $statusCfg[$app['status']] ?? ['secondary', ucfirst($app['status'])];
    ?>
    <span class="badge bg-<?= $sColor ?> fs-6"><?= $sLabel ?></span>
</div>

<div class="row g-4">
    <!-- Left: Profile + Docs -->
    <div class="col-lg-4">
        <!-- Profile -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-dark text-white py-2">
                <h6 class="mb-0 small"><i class="bi bi-person me-2"></i>Complete Profile</h6>
            </div>
            <div class="card-body small">
                <?php
                $fields = [
                    'Position Applied' => $app['position'],
                    'Contact Number'   => $app['cellphone'] ?? '—',
                    'Email'            => $app['applicant_email'] ?? '—',
                    'Address'          => $app['present_address'] ?? '—',
                    'Education'        => $app['educational_bg'] ?? '—',
                    'Experience'       => $app['work_experience'] ?? '—',
                    'Skills'           => $app['other_skills'] ?? '—',
                    'Preferred Job'    => $app['preferred_occupation'] ?? '—',
                    'SSS/GSIS No.'     => $app['gsis_sss_no'] ?? '—',
                    'PAG-IBIG No.'     => $app['pag_ibig_no'] ?? '—',
                    'PhilHealth No.'   => $app['philhealth_no'] ?? '—',
                ];
                foreach ($fields as $lbl => $val): ?>
                <div class="row mb-1">
                    <div class="col-5 text-muted"><?= $lbl ?></div>
                    <div class="col-7 fw-semibold text-break"><?= nl2br(htmlspecialchars($val, ENT_QUOTES)) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Application Documents -->
        <?php if (!empty($appDocs)): ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2">
                <h6 class="mb-0 small fw-bold"><i class="bi bi-folder me-2 text-primary"></i>Application Documents</h6>
            </div>
            <div class="card-body p-0">
                <?php foreach ($appDocs as $d): ?>
                <a href="<?= APP_URL . htmlspecialchars($d['file_path'], ENT_QUOTES) ?>"
                   target="_blank"
                   class="d-flex align-items-center gap-2 px-3 py-2 border-bottom text-decoration-none text-dark small">
                    <i class="bi bi-file-earmark-text text-primary"></i>
                    <span class="flex-grow-1 text-truncate"><?= htmlspecialchars($d['original_name'], ENT_QUOTES) ?></span>
                    <span class="badge bg-secondary"><?= ucfirst($d['doc_type']) ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Employment Documents -->
        <?php if (!empty($empDocs)): ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-success text-white py-2">
                <h6 class="mb-0 small fw-bold"><i class="bi bi-folder-check me-2"></i>Employment Documents</h6>
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

        <!-- Interview Result -->
        <?php if ($interview): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-2">
                <h6 class="mb-0 small fw-bold"><i class="bi bi-graph-up me-2 text-success"></i>Interview Result</h6>
            </div>
            <div class="card-body small">
                <?php
                $pct = $interview['max_score'] > 0
                    ? round(($interview['total_score'] / $interview['max_score']) * 100) : 0;
                ?>
                <div class="d-flex justify-content-between mb-1">
                    <span>Score</span>
                    <strong><?= $interview['total_score'] ?>/<?= $interview['max_score'] ?> (<?= $pct ?>%)</strong>
                </div>
                <div class="progress mb-2" style="height:8px;">
                    <div class="progress-bar bg-<?= $pct>=75?'success':($pct>=50?'primary':'warning') ?>"
                         style="width:<?= $pct ?>%"></div>
                </div>
                <?php if (!empty($interview['score_summary'])): ?>
                <div class="text-muted"><?= htmlspecialchars($interview['score_summary'], ENT_QUOTES) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Right: Actions + Messaging + Status History -->
    <div class="col-lg-8">
        <!-- Action Buttons based on current status -->
        <?php if ($app['status'] === 'qualified_for_contact'): ?>
        <div class="card border-success border-0 shadow-sm mb-3">
            <div class="card-header bg-success text-white py-2">
                <h6 class="mb-0 small"><i class="bi bi-lightning-fill me-2"></i>Next Step: Request Employment Requirements</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/agency/hiring/<?= $app['id'] ?>/request-requirements">
                    <?= csrfField() ?>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Message to Applicant</label>
                        <textarea name="message" class="form-control form-control-sm" rows="3"
                        >Congratulations! You passed the interview for <?= htmlspecialchars($app['position'], ENT_QUOTES) ?>. Please submit your employment requirements: SSS ID/Number, PhilHealth ID/Number, and TIN Number through the system.</textarea>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm"
                            onclick="return confirm('Send requirements request to applicant?')">
                        <i class="bi bi-send me-1"></i>Send &amp; Request Requirements
                    </button>
                </form>
            </div>
        </div>

        <?php elseif ($app['status'] === 'requirements_submitted'): ?>
        <div class="card border-primary border-0 shadow-sm mb-3">
            <div class="card-header bg-primary text-white py-2">
                <h6 class="mb-0 small">
                    <i class="bi bi-calendar-plus me-2"></i>Next Step: Schedule First Day
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/agency/hiring/<?= $app['id'] ?>/schedule-first-day">
                    <?= csrfField() ?>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">First Day Date <span class="text-danger">*</span></label>
                            <input type="date" name="first_day_date" class="form-control form-control-sm" required
                                   min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Time</label>
                            <input type="time" name="first_day_time" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold">Reporting Location <span class="text-danger">*</span></label>
                            <input type="text" name="first_day_location" class="form-control form-control-sm"
                                   placeholder="e.g. Main office, 2nd floor HR" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Additional Notes</label>
                            <textarea name="first_day_notes" class="form-control form-control-sm" rows="2"
                                      placeholder="Dress code, bring requirements, etc."></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm mt-3"
                            onclick="return confirm('Schedule first day and notify applicant?')">
                        <i class="bi bi-calendar-check me-1"></i>Schedule First Day
                    </button>
                </form>
            </div>
        </div>

        <?php elseif ($app['status'] === 'first_day_scheduled'): ?>
        <div class="card border-dark border-0 shadow-sm mb-3">
            <div class="card-header bg-dark text-white py-2">
                <h6 class="mb-0 small"><i class="bi bi-trophy me-2"></i>Finalize: Mark as Officially Hired</h6>
            </div>
            <div class="card-body">
                <div class="mb-2 small">
                    <strong>First Day:</strong>
                    <?= !empty($app['first_day_date']) ? date('F d, Y', strtotime($app['first_day_date'])) : '—' ?>
                    <?= !empty($app['first_day_time']) ? ' at ' . date('g:i A', strtotime($app['first_day_time'])) : '' ?><br>
                    <strong>Location:</strong> <?= htmlspecialchars($app['first_day_location'] ?? '—', ENT_QUOTES) ?><br>
                    <?php if (!empty($app['first_day_notes'])): ?>
                    <strong>Notes:</strong> <?= htmlspecialchars($app['first_day_notes'], ENT_QUOTES) ?>
                    <?php endif; ?>
                </div>
                <form method="POST" action="<?= APP_URL ?>/agency/hiring/<?= $app['id'] ?>/mark-hired">
                    <?= csrfField() ?>
                    <button type="submit" class="btn btn-dark btn-sm"
                            onclick="return confirm('Mark this applicant as officially HIRED?')">
                        <i class="bi bi-trophy-fill me-1"></i>Mark as Officially Hired
                    </button>
                </form>
            </div>
        </div>

        <?php elseif ($app['status'] === 'hired'): ?>
        <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-trophy-fill fs-4"></i>
            <div>
                <strong>Officially Hired</strong>
                <?= !empty($app['hired_at']) ? ' on ' . date('F d, Y', strtotime($app['hired_at'])) : '' ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Messaging -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2 d-flex justify-content-between">
                <h6 class="mb-0 fw-bold small"><i class="bi bi-chat-dots me-2 text-primary"></i>Messages</h6>
            </div>
            <div class="card-body">
                <!-- Chat bubbles -->
                <div class="chat-box mb-3 p-2" id="chatBox">
                    <?php if (empty($messages)): ?>
                    <div class="text-center text-muted small py-3">No messages yet.</div>
                    <?php else: ?>
                    <?php foreach ($messages as $msg):
                        $isAgency = $msg['sender_role'] === 'agency';
                    ?>
                    <div class="d-flex <?= $isAgency ? 'justify-content-end' : 'justify-content-start' ?>">
                        <div class="msg-bubble px-3 py-2 <?= $isAgency ? 'msg-agency' : 'msg-applicant' ?>">
                            <div class="small" style="white-space:pre-wrap;"><?= htmlspecialchars($msg['message'], ENT_QUOTES) ?></div>
                            <div class="text-<?= $isAgency ? 'white-50' : 'muted' ?>" style="font-size:.65rem;margin-top:2px;">
                                <?= htmlspecialchars($msg['sender_name'], ENT_QUOTES) ?> &bull;
                                <?= date('M d, g:i A', strtotime($msg['sent_at'])) ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <!-- Send message -->
                <form method="POST" action="<?= APP_URL ?>/agency/hiring/<?= $app['id'] ?>/send-message">
                    <?= csrfField() ?>
                    <div class="input-group">
                        <textarea name="message" class="form-control form-control-sm" rows="2"
                                  placeholder="Type a message..." required></textarea>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Status History -->
        <?php if (!empty($history)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-2">
                <h6 class="mb-0 small fw-bold"><i class="bi bi-clock-history me-2 text-muted"></i>Status History</h6>
            </div>
            <div class="card-body p-0">
                <?php foreach (array_reverse($history) as $h): ?>
                <div class="d-flex gap-2 px-3 py-2 border-bottom align-items-start small">
                    <i class="bi bi-arrow-right-circle text-muted flex-shrink-0 mt-1"></i>
                    <div class="flex-grow-1">
                        <span class="text-muted"><?= ucfirst(str_replace('_',' ',$h['from_status'] ?? 'start')) ?></span>
                        <i class="bi bi-arrow-right mx-1 text-muted"></i>
                        <strong><?= ucfirst(str_replace('_',' ',$h['to_status'])) ?></strong>
                        <?php if (!empty($h['remarks'])): ?>
                        <div class="text-muted"><?= htmlspecialchars($h['remarks'], ENT_QUOTES) ?></div>
                        <?php endif; ?>
                    </div>
                    <span class="text-muted flex-shrink-0"><?= date('M d, Y', strtotime($h['changed_at'])) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Auto-scroll chat to bottom
const chatBox = document.getElementById('chatBox');
if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
</script>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
