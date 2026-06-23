<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form — <?= htmlspecialchars($request['title'], ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/app.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 11pt; }
            .table { font-size: 10pt; }
        }
        body { background: #f8f9fa; }
        .form-container { max-width: 900px; margin: 2rem auto; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        .form-header { text-align: center; border-bottom: 2px solid #1e2a3a; padding-bottom: 1rem; margin-bottom: 1.5rem; }
        .form-header h2 { font-size: 1.4rem; font-weight: 700; color: #1e2a3a; }
        .form-header p { color: #666; margin: 0; }
    </style>
</head>
<body>
<div class="form-container">
    <div class="no-print mb-3 d-flex gap-2">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer me-2"></i>Print Form
        </button>
        <a href="<?= APP_URL ?>/supervising-labor/requests" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
    </div>

    <div class="form-header">
        <h2>APPLICANT REGISTRATION FORM</h2>
        <p class="fw-semibold"><?= htmlspecialchars($request['title'], ENT_QUOTES, 'UTF-8') ?></p>
        <p>Date: <?= formatDate($request['requested_date']) ?> &nbsp;|&nbsp; Venue: <?= htmlspecialchars($request['venue'] ?? '—', ENT_QUOTES, 'UTF-8') ?></p>
    </div>

    <?php $error = getFlash('error'); $success = getFlash('success'); ?>
    <?php if ($error): ?><div class="alert alert-danger mx-3"><?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success mx-3"><?= $success ?></div><?php endif; ?>

    <div class="mx-3 mb-4">
        <form method="POST" action="<?= APP_URL ?>/supervising-labor/registration-form/<?= $request['id'] ?>/store" class="row g-3">
            <?= csrfField() ?>
            <div class="col-md-4">
                <label class="form-label">Lastname</label>
                <input type="text" name="surname" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Firstname</label>
                <input type="text" name="firstname" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Middlename</label>
                <input type="text" name="middlename" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">GSIS/SSS NO</label>
                <input type="text" name="gsis_sss_no" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">PAG-IBIG NO</label>
                <input type="text" name="pag_ibig_no" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">PHILHEALTH NO</label>
                <input type="text" name="philhealth_no" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Disability</label>
                <select name="disability_status" class="form-select">
                    <option value="none">None</option>
                    <option value="visual">Visual</option>
                    <option value="hearing">Hearing</option>
                    <option value="physical">Physical</option>
                    <option value="mental">Mental</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="col-12">
                <button class="btn btn-success">Save Registration</button>
            </div>
        </form>
        <form method="POST" action="<?= APP_URL ?>/supervising-labor/registration-form/<?= $request['id'] ?>/generate" class="mt-3">
            <?= csrfField() ?>
            <button type="submit" class="btn btn-outline-primary">Publish Public Registration Form</button>
            <small class="text-muted ms-2">Makes the registration available on the public portal.</small>
        </form>
    </div>

    <?php if (empty($applicants)): ?>
        <div class="text-center py-4 text-muted">
            <i class="bi bi-people display-4 d-block mb-2"></i>
            No registered applicants for this job fair yet.
        </div>
    <?php else: ?>
        <table class="table table-bordered table-sm">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Surname</th>
                    <th>First Name</th>
                    <th>Middle Name</th>
                    <th>GSIS/SSS No.</th>
                    <th>Pag-IBIG No.</th>
                    <th>PhilHealth No.</th>
                    <th>PWD</th>
                    <th>Signature</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applicants as $i => $a): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars(strtoupper($a['surname']), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($a['firstname'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($a['middlename'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($a['gsis_sss_no'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($a['pag_ibig_no'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($a['philhealth_no'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= $a['disability_status'] === 'with_disability' ? 'Yes' : 'No' ?></td>
                        <td style="min-width:120px">&nbsp;</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="mt-4 row">
            <div class="col-6">
                <p class="mb-1 fw-semibold">Total Applicants: <?= count($applicants) ?></p>
            </div>
            <div class="col-6 text-end">
                <p class="mb-1">Prepared by:</p>
                <div style="border-top: 1px solid #000; width: 200px; margin-left: auto; margin-top: 2rem; padding-top: 4px; text-align: center;">
                    <small>Signature over Printed Name</small>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
