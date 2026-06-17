<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form — <?= htmlspecialchars($post['title'] ?? $post['fair_title'] ?? '', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/app.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 10pt; }
            .table { font-size: 9pt; }
            .form-container { box-shadow: none !important; border: none !important; }
        }
        body { background: #f0f2f5; }
        .form-container { max-width: 1100px; margin: 2rem auto; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 16px rgba(0,0,0,0.10); }
        .form-header { text-align: center; border-bottom: 3px solid #1e2a3a; padding-bottom: 10px; margin-bottom: 18px; }
        .form-header h2 { font-size: 1.3rem; font-weight: 900; color: #1e2a3a; text-transform: uppercase; }
        .form-header .sub { color: #555; font-size: .95rem; margin: 2px 0; }
        .stat-badge { background: #e7f3ff; border-radius: 8px; padding: 8px 16px; text-align: center; }
        .stat-badge .num { font-size: 1.8rem; font-weight: 900; color: #0d6efd; }
        .stat-badge .lbl { font-size: .75rem; color: #666; text-transform: uppercase; }
    </style>
</head>
<body>
<div class="form-container">

    <!-- No-print controls -->
    <div class="no-print mb-3 d-flex flex-wrap gap-2 align-items-center">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer me-2"></i>Print List
        </button>
        <a href="<?= APP_URL ?>/supervising-labor/requests" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Requests
        </a>
        <div class="ms-auto text-muted small">
            Total Registered: <strong class="text-dark"><?= count($applicants) ?></strong>
        </div>
    </div>

    <!-- Header -->
    <div class="form-header">
        <p class="sub mb-1">Republic of the Philippines — Department of Labor and Employment (DOLE)</p>
        <h2>NSRP Form 1 — Job Seeker Registration List</h2>
        <p class="sub fw-semibold"><?= htmlspecialchars($post['title'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
        <p class="sub">
            Date:
            <?= !empty($post['event_date']) ? date('F d, Y', strtotime($post['event_date'])) : (!empty($post['requested_date']) ? date('F d, Y', strtotime($post['requested_date'])) : '—') ?>
            &nbsp;|&nbsp;
            Venue: <?= htmlspecialchars($post['venue'] ?? $post['fair_venue'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
        </p>
    </div>

    <!-- Stats (no-print) -->
    <?php if (!($isLegacy ?? false) && !empty($companies['companies'])): ?>
    <div class="no-print row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="stat-badge">
                <div class="num"><?= count($applicants) ?></div>
                <div class="lbl">Registered</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-badge">
                <div class="num"><?= count($companies['companies'] ?? []) ?></div>
                <div class="lbl">Companies</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-badge">
                <div class="num"><?= count($companies['vacancies'] ?? []) ?></div>
                <div class="lbl">Positions</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-badge">
                <div class="num"><?= array_sum(array_column($companies['vacancies'] ?? [], 'available_slots')) ?></div>
                <div class="lbl">Total Slots</div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Flash messages -->
    <?php $err = getFlash('error'); $ok = getFlash('success'); ?>
    <?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <?php if ($ok):  ?><div class="alert alert-success"><?= htmlspecialchars($ok,  ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

    <!-- Walk-in Add Form (SL manual entry) -->
    <?php if (!($isLegacy ?? false)): ?>
    <div class="no-print card border-warning mb-4">
        <div class="card-header bg-warning bg-opacity-10">
            <h6 class="mb-0"><i class="bi bi-person-plus me-2"></i>Add Walk-in Registrant</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= APP_URL ?>/supervising-labor/registration-form/<?= (int)$post['id'] ?>/store" class="row g-2">
                <?= csrfField() ?>
                <div class="col-md-3">
                    <label class="form-label form-label-sm">Lastname <span class="text-danger">*</span></label>
                    <input type="text" name="surname" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label form-label-sm">Firstname <span class="text-danger">*</span></label>
                    <input type="text" name="firstname" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm">Middlename</label>
                    <input type="text" name="middlename" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm">GSIS/SSS No.</label>
                    <input type="text" name="gsis_sss_no" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm">PAG-IBIG No.</label>
                    <input type="text" name="pag_ibig_no" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm">PhilHealth No.</label>
                    <input type="text" name="philhealth_no" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label form-label-sm">Disability</label>
                    <select name="disability" class="form-select form-select-sm">
                        <option value="">None</option>
                        <option>Visual</option>
                        <option>Hearing</option>
                        <option>Physical</option>
                        <option>Intellectual/Learning</option>
                        <option>Mental/Psychosocial</option>
                        <option>Other</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-warning btn-sm w-100">
                        <i class="bi bi-plus-circle me-1"></i>Add
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Registrants Table -->
    <?php if (empty($applicants)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-people display-3 d-block mb-3"></i>
            <h6>No registered applicants yet.</h6>
            <p class="small">Job seekers can register online via the applicant portal, or you can add walk-in registrants above.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center" style="width:40px">#</th>
                        <th>Surname</th>
                        <th>First Name</th>
                        <th>Middle Name</th>
                        <th>GSIS/SSS No.</th>
                        <th>Pag-IBIG No.</th>
                        <th>PhilHealth No.</th>
                        <th>Disability</th>
                        <th>Registered</th>
                        <th class="no-print text-center">Actions</th>
                        <th style="min-width:100px" class="text-center">Signature</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applicants as $i => $a): ?>
                        <tr>
                            <td class="text-center"><?= $i + 1 ?></td>
                            <td class="fw-semibold"><?= htmlspecialchars(strtoupper($a['surname']), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($a['firstname'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($a['middlename'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($a['gsis_sss_no'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($a['pag_ibig_no'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($a['philhealth_no'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($a['disability'] ?? $a['disability_status'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="small text-muted">
                                <?php if (!empty($a['registered_at'])): ?>
                                    <?= date('m/d/Y', strtotime($a['registered_at'])) ?>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="no-print text-center">
                                <?php if (!empty($a['job_fair_post_id'])): ?>
                                <a href="<?= APP_URL ?>/applicant/job-fairs/<?= (int)$a['job_fair_post_id'] ?>/pdf?applicant=<?= (int)$a['applicant_id'] ?>"
                                   target="_blank" class="btn btn-outline-primary btn-sm" title="View PDF">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                            <td style="min-width:100px">&nbsp;</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Summary footer -->
        <div class="mt-4 row align-items-center">
            <div class="col-6">
                <p class="mb-0 fw-semibold">Total Registered Applicants: <span class="text-primary"><?= count($applicants) ?></span></p>
            </div>
            <div class="col-6 text-end">
                <p class="mb-1 small">Prepared by:</p>
                <div style="border-top: 1px solid #000; width: 220px; margin-left: auto; margin-top: 24px; padding-top: 4px; text-align: center;">
                    <small>Signature over Printed Name &amp; Date</small>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
