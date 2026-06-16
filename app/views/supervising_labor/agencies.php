<?php ob_start(); ?>

<?php if (!empty($success = getFlash('success'))): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (!empty($err = getFlash('error'))): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">

    <!-- ── Left: Invite Panel ─────────────────────────────────────────────── -->
    <div class="col-lg-5">

        <!-- Step 1: Select Job Fair -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-calendar-event me-2 text-primary"></i>
                    Step 1 — Select Job Fair
                </h6>
            </div>
            <div class="card-body">
                <select id="jobFairSelect" class="form-select" onchange="loadJobFair(this.value)">
                    <option value="">— Choose an approved job fair —</option>
                    <?php foreach ($requests as $req): ?>
                    <option value="<?= $req['id'] ?>"
                        <?= (isset($_GET['request_id']) && $_GET['request_id'] == $req['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($req['title'], ENT_QUOTES, 'UTF-8') ?>
                        (<?= formatDate($req['requested_date']) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Step 2: Select Companies to Invite -->
        <div class="card border-0 shadow-sm mb-3" id="companySelectCard"
             style="<?= empty($_GET['request_id']) ? 'opacity:.5;pointer-events:none;' : '' ?>">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-building-check me-2 text-success"></i>
                    Step 2 — Select Companies
                </h6>
                <span class="badge bg-primary" id="selectedCount">0 selected</span>
            </div>
            <div class="card-body p-0">
                <!-- Search -->
                <div class="p-3 border-bottom">
                    <input type="text" id="companySearch" class="form-control form-control-sm"
                           placeholder="Search companies..." oninput="filterCompanies(this.value)">
                </div>
                <!-- Agency user list -->
                <div id="companyList" style="max-height:320px;overflow-y:auto;">
                    <?php if (empty($agencyUsers)): ?>
                    <div class="text-center py-4 text-muted small">
                        <i class="bi bi-people d-block mb-1 fs-4"></i>
                        No agency accounts found.<br>
                        <small>Agencies must register and be approved first.</small>
                    </div>
                    <?php else: ?>
                    <?php foreach ($agencyUsers as $u):
                        // Show company/agency name if profile set up, else user name
                        $displayName = !empty($u['agency_name']) ? $u['agency_name'] : $u['name'];
                        $displaySub  = !empty($u['agency_name']) ? ($u['agency_location'] ?? '') : $u['email'];
                    ?>
                    <label class="company-item d-flex align-items-center gap-3 px-3 py-2 border-bottom"
                           style="cursor:pointer;transition:background .15s;"
                           onmouseover="this.style.background='#f8f9fa'"
                           onmouseout="this.style.background=''">
                        <input type="checkbox" class="form-check-input company-cb flex-shrink-0"
                               value="<?= $u['id'] ?>"
                               data-name="<?= htmlspecialchars(strtolower($displayName), ENT_QUOTES, 'UTF-8') ?>"
                               onchange="updateCount()">
                        <div class="flex-grow-1 min-width-0">
                            <div class="fw-semibold small text-truncate">
                                <?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?>
                            </div>
                            <div class="text-muted" style="font-size:.75rem;">
                                <?= htmlspecialchars($displaySub, ENT_QUOTES, 'UTF-8') ?>
                                <?php if (!empty($u['agency_name']) && !empty($u['email'])): ?>
                                &bull; <?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <i class="bi bi-check-circle-fill text-success d-none check-icon"></i>
                    </label>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <!-- Select all / none -->
                    <div class="p-2 border-top d-flex gap-2">
                    <button type="button" class="btn btn-xs btn-outline-secondary" onclick="selectAll(true)">
                        Select All
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-secondary" onclick="selectAll(false)">
                        Clear
                    </button>
                    <a href="<?= APP_URL ?>/admin/users?role=agency"
                       class="btn btn-xs btn-outline-primary ms-auto">
                        <i class="bi bi-plus me-1"></i>Manage Agency Accounts
                    </a>
                </div>
            </div>
        </div>

        <!-- Step 3: Send Invitations -->
        <form method="POST" action="<?= APP_URL ?>/supervising-labor/agencies/bulk-invite"
              id="bulkInviteForm"
              style="<?= empty($_GET['request_id']) ? 'opacity:.5;pointer-events:none;' : '' ?>">
            <?= csrfField() ?>
            <input type="hidden" name="job_fair_request_id" id="hiddenRequestId"
                   value="<?= (int)($_GET['request_id'] ?? 0) ?>">
            <div id="hiddenCheckboxes"></div>
            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold" id="sendBtn" disabled>
                <i class="bi bi-send me-2"></i>
                Send Invitations
                <span id="sendCount" class="badge bg-white text-primary ms-1">0</span>
            </button>
        </form>

        <!-- Manual invite (add new company not in directory) -->
        <div class="mt-3">
            <button class="btn btn-outline-secondary btn-sm w-100" type="button"
                    data-bs-toggle="collapse" data-bs-target="#manualInvite">
                <i class="bi bi-person-plus me-1"></i>Manually invite a company not in directory
            </button>
                <div class="collapse mt-2" id="manualInvite">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <form method="POST" action="<?= APP_URL ?>/supervising-labor/agencies/invite">
                            <?= csrfField() ?>
                            <input type="hidden" name="job_fair_request_id"
                                   value="<?= (int)($_GET['request_id'] ?? 0) ?>">
                            <div class="mb-2">
                                <input type="text" name="agency_name" class="form-control form-control-sm"
                                       placeholder="Company / Agency name *" required>
                            </div>
                            <div class="mb-2">
                                <input type="text" name="contact_person" class="form-control form-control-sm"
                                       placeholder="Contact person">
                            </div>
                            <div class="mb-2">
                                        <input type="email" name="email" class="form-control form-control-sm"
                                       placeholder="Email address (if this is an agency account, they will be notified)">
                            </div>
                            <div class="mb-2">
                                <input type="text" name="phone" class="form-control form-control-sm"
                                       placeholder="Phone number">
                            </div>
                            <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                                <i class="bi bi-send me-1"></i>Send Manual Invitation
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Right: Invited Agencies List ──────────────────────────────────── -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-building me-2 text-primary"></i>
                    Invited Agencies
                    <?php if ($request): ?>
                    — <span class="text-muted fw-normal small">
                        <?= htmlspecialchars($request['title'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    <?php endif; ?>
                </h6>
                <?php if (!empty($agencies)): ?>
                <span class="badge bg-primary"><?= count($agencies) ?> invited</span>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <?php if (empty($agencies)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-building display-4 d-block mb-2 opacity-50"></i>
                    <?= empty($_GET['request_id'])
                        ? 'Select a job fair to see invited agencies.'
                        : 'No agencies invited yet.' ?>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Agency / Company</th>
                                <th>Email</th>
                                <th>Response</th>
                                <th>Vacancies</th>
                                <th>Invited</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($agencies as $a):
                            $vCount = $vacancyCountByAgency[$a['id']] ?? 0;
                        ?>
                        <tr>
                            <td class="fw-semibold">
                                <?= htmlspecialchars($a['agency_name'], ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td class="text-muted small">
                                <?= htmlspecialchars($a['email'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td>
                                <?= statusBadge($a['status']) ?>
                                <?php if ($a['responded_at']): ?>
                                <div class="text-muted" style="font-size:.7rem;">
                                    <?= formatDate($a['responded_at']) ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($vCount > 0): ?>
                                <span class="badge bg-success"><?= $vCount ?> posted</span>
                                <?php else: ?>
                                <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small"><?= formatDate($a['invited_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Confirmed Resources from Secretary -->
        <?php if (!empty($confirmedResources)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-check2-all me-2 text-success"></i>
                    Confirmed Resources (from Secretary)
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Resource</th><th>Quantity</th><th>Confirmed</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($confirmedResources as $cr): ?>
                        <tr>
                            <td class="fw-semibold small"><?= htmlspecialchars($cr['resource_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span class="badge bg-primary"><?= $cr['quantity_allocated'] ?> <?= htmlspecialchars($cr['unit'] ?? '', ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td class="text-muted small"><?= formatDate($cr['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php elseif (!empty($_GET['request_id'])): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3 text-muted small">
                <i class="bi bi-hourglass me-1"></i>
                No resources confirmed by Secretary yet for this job fair.
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// ── Navigate to job fair ──────────────────────────────────────────────────────
function loadJobFair(id) {
    if (id) {
        window.location.href = '<?= APP_URL ?>/supervising-labor/agencies?request_id=' + id;
    }
}

// ── Company checkbox counter ──────────────────────────────────────────────────
function updateCount() {
    const checked = document.querySelectorAll('.company-cb:checked').length;
    document.getElementById('selectedCount').textContent = checked + ' selected';
    document.getElementById('sendCount').textContent     = checked;
    document.getElementById('sendBtn').disabled          = checked === 0;

    // Sync check icons
    document.querySelectorAll('.company-cb').forEach(cb => {
        const icon = cb.closest('label').querySelector('.check-icon');
        if (icon) icon.classList.toggle('d-none', !cb.checked);
    });

    // Sync hidden checkboxes for form submission
    const container = document.getElementById('hiddenCheckboxes');
    container.innerHTML = '';
    document.querySelectorAll('.company-cb:checked').forEach(cb => {
        const inp = document.createElement('input');
        inp.type  = 'hidden';
        inp.name  = 'user_ids[]';
        inp.value = cb.value;
        container.appendChild(inp);
    });
}

// ── Search filter ─────────────────────────────────────────────────────────────
function filterCompanies(query) {
    const q = query.toLowerCase().trim();
    document.querySelectorAll('.company-item').forEach(item => {
        const name = item.querySelector('.company-cb').dataset.name || '';
        item.style.display = (!q || name.includes(q)) ? '' : 'none';
    });
}

// ── Select all / none ─────────────────────────────────────────────────────────
function selectAll(state) {
    document.querySelectorAll('.company-cb').forEach(cb => {
        if (cb.closest('.company-item').style.display !== 'none') {
            cb.checked = state;
        }
    });
    updateCount();
}

// ── Form submit guard ─────────────────────────────────────────────────────────
document.getElementById('bulkInviteForm')?.addEventListener('submit', function (e) {
    const requestId = document.getElementById('hiddenRequestId').value;
    if (!requestId || requestId === '0') {
        e.preventDefault();
        alert('Please select a job fair first.');
        return;
    }
    const checked = document.querySelectorAll('.company-cb:checked').length;
    if (checked === 0) {
        e.preventDefault();
        alert('Please select at least one company to invite.');
    }
});
</script>

<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
