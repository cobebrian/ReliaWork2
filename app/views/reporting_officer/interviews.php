<?php $pageTitle = $pageTitle ?? 'All Interviews'; ?>
<?php ob_start(); ?>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Search applicant or agency..."
                       value="<?= htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES) ?>">
            </div>
            <div class="col-md-3">
                <select name="outcome" class="form-select form-select-sm">
                    <option value="all"              <?= ($_GET['outcome'] ?? 'all') === 'all'             ? 'selected' : '' ?>>All Outcomes</option>
                    <option value="pending"          <?= ($_GET['outcome'] ?? '') === 'pending'            ? 'selected' : '' ?>>Pending</option>
                    <option value="hired"            <?= ($_GET['outcome'] ?? '') === 'hired'              ? 'selected' : '' ?>>Hired</option>
                    <option value="not_hired"        <?= ($_GET['outcome'] ?? '') === 'not_hired'          ? 'selected' : '' ?>>Not Hired</option>
                    <option value="for_consideration"<?= ($_GET['outcome'] ?? '') === 'for_consideration'  ? 'selected' : '' ?>>For Consideration</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="fair_id" class="form-select form-select-sm">
                    <option value="">All Job Fairs</option>
                    <?php foreach ($jobFairs as $jf): ?>
                    <option value="<?= $jf['id'] ?>" <?= (int)($_GET['fair_id'] ?? 0) === (int)$jf['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($jf['title'], ENT_QUOTES) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-camera-video me-2 text-primary"></i>Interviews (<?= count($interviews) ?>)
        </h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Applicant</th>
                    <th>Agency</th>
                    <th>Job Fair</th>
                    <th class="text-center">Questions</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Outcome</th>
                    <th>Completed</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($interviews)): ?>
                <tr><td colspan="8" class="text-center py-5 text-muted">No interviews found.</td></tr>
            <?php else: ?>
            <?php foreach ($interviews as $iv):
                $sColors = ['scheduled'=>'warning','in_progress'=>'primary','completed'=>'success','cancelled'=>'secondary'];
                $sColor  = $sColors[$iv['status']] ?? 'secondary';
                $oColors = ['hired'=>'success','not_hired'=>'danger','for_consideration'=>'info','pending'=>'warning'];
                $oColor  = $oColors[$iv['hiring_outcome']] ?? 'secondary';
            ?>
            <tr>
                <td class="fw-semibold small">
                    <?= htmlspecialchars(strtoupper($iv['surname']) . ', ' . $iv['firstname'], ENT_QUOTES) ?>
                    <?php if (!empty($iv['email'])): ?>
                    <div class="text-muted" style="font-size:.7rem;"><?= htmlspecialchars($iv['email'], ENT_QUOTES) ?></div>
                    <?php endif; ?>
                </td>
                <td class="small"><?= htmlspecialchars($iv['agency_name'], ENT_QUOTES) ?></td>
                <td class="small text-muted"><?= htmlspecialchars($iv['fair_title'], ENT_QUOTES) ?></td>
                <td class="text-center">
                    <span class="badge bg-light text-dark border">
                        <?= (int)$iv['answered_count'] ?>/<?= (int)$iv['question_count'] ?>
                    </span>
                </td>
                <td class="text-center"><span class="badge bg-<?= $sColor ?>"><?= ucfirst($iv['status']) ?></span></td>
                <td class="text-center">
                    <span class="badge bg-<?= $oColor ?>">
                        <?= ucfirst(str_replace('_', ' ', $iv['hiring_outcome'])) ?>
                    </span>
                </td>
                <td class="small text-muted">
                    <?= !empty($iv['completed_at']) ? date('M d, Y', strtotime($iv['completed_at'])) : '—' ?>
                </td>
                <td>
                    <a href="<?= APP_URL ?>/reporting-officer/interview/<?= $iv['id'] ?>"
                       class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye me-1"></i>View
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
