<?php $pageTitle = $pageTitle ?? 'Register for Job Fair'; ?>
<?php ob_start(); ?>

<style>
.nsrp-section { background: #f8f9fa; border-left: 4px solid #0d6efd; padding: 1rem 1.25rem; margin-bottom: 1.5rem; border-radius: 0 8px 8px 0; }
.nsrp-section h6 { color: #0d6efd; font-weight: 700; letter-spacing: .5px; text-transform: uppercase; font-size: .8rem; margin-bottom: .75rem; }
.required-star { color: #dc3545; }
</style>

<!-- Job Fair Header -->
<div class="card mb-4 border-primary">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i><?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?></h5>
    </div>
    <div class="card-body py-2">
        <div class="row g-2 small">
            <?php $venue = $post['venue'] ?: $post['fair_venue'] ?? ''; ?>
            <div class="col-md-4">
                <i class="bi bi-calendar3 me-1 text-primary"></i>
                <?= !empty($post['event_date']) ? date('F d, Y', strtotime($post['event_date'])) : '—' ?>
                <?= !empty($post['event_time']) ? ' at ' . htmlspecialchars($post['event_time']) : '' ?>
            </div>
            <div class="col-md-4">
                <i class="bi bi-geo-alt me-1 text-danger"></i>
                <?= htmlspecialchars($venue ?: '—', ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div class="col-md-4">
                <i class="bi bi-building me-1 text-info"></i>
                <?= (int)$post['company_count'] ?> companies &nbsp;|&nbsp;
                <?= (int)$post['vacancy_count'] ?> vacancies
            </div>
        </div>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<!-- Participating Companies -->
<?php if (!empty($companies['companies'])): ?>
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="bi bi-building me-2"></i>Participating Companies & Available Positions</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Company</th>
                    <th>Location</th>
                    <th>Positions Available</th>
                    <th class="text-center">Total Slots</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($companies['companies'] as $co): ?>
                    <tr>
                        <td class="fw-semibold"><?= htmlspecialchars($co['agency_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($co['location'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><small><?= htmlspecialchars($co['vacancies_list'] ?? '—', ENT_QUOTES, 'UTF-8') ?></small></td>
                        <td class="text-center"><?= (int)$co['total_slots'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- NSRP Form 1 -->
<div class="card shadow-sm">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0 text-center">
            <i class="bi bi-person-vcard me-2"></i>
            REGISTRATION FORM — NSRP Form 1 (Job Fair)
        </h5>
        <p class="text-center text-white-50 mb-0 small">Department of Labor and Employment (DOLE)</p>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= APP_URL ?>/applicant/job-fairs/<?= (int)$post['id'] ?>/register">
            <?= csrfField() ?>

            <!-- SECTION A: Personal Information -->
            <div class="nsrp-section">
                <h6>A. Personal Information</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Surname <span class="required-star">*</span></label>
                        <input type="text" name="surname" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($old['surname'] ?? ($applicant['surname'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">First Name <span class="required-star">*</span></label>
                        <input type="text" name="firstname" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($old['firstname'] ?? ($applicant['firstname'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Middle Name</label>
                        <input type="text" name="middlename" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($old['middlename'] ?? ($applicant['middlename'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Suffix</label>
                        <input type="text" name="suffix" class="form-control form-control-sm" placeholder="Jr."
                               value="<?= htmlspecialchars($old['suffix'] ?? ($applicant['suffix'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="date_of_birth" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($old['date_of_birth'] ?? ($applicant['date_of_birth'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Place of Birth</label>
                        <input type="text" name="place_of_birth" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($old['place_of_birth'] ?? ($applicant['place_of_birth'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Sex</label>
                        <select name="sex" class="form-select form-select-sm">
                            <option value="">-- Select --</option>
                            <?php foreach (['male' => 'Male', 'female' => 'Female'] as $v => $l): ?>
                                <option value="<?= $v ?>" <?= ($old['sex'] ?? $applicant['sex'] ?? '') === $v ? 'selected' : '' ?>>
                                    <?= $l ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Religion</label>
                        <input type="text" name="religion" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($old['religion'] ?? ($applicant['religion'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Civil Status</label>
                        <select name="civil_status" class="form-select form-select-sm">
                            <option value="">-- Select --</option>
                            <?php foreach (['single'=>'Single','married'=>'Married','separated'=>'Separated','live_in'=>'Live-in','widowed'=>'Widowed'] as $v => $l): ?>
                                <option value="<?= $v ?>" <?= ($old['civil_status'] ?? $applicant['civil_status'] ?? '') === $v ? 'selected' : '' ?>>
                                    <?= $l ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Height (cm)</label>
                        <input type="text" name="height" class="form-control form-control-sm" placeholder="e.g. 165"
                               value="<?= htmlspecialchars($old['height'] ?? ($applicant['height'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Present Address</label>
                        <textarea name="present_address" class="form-control form-control-sm" rows="2"><?= htmlspecialchars($old['present_address'] ?? ($applicant['present_address'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- SECTION B: Contact Details -->
            <div class="nsrp-section">
                <h6>B. Contact Details</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Cellphone No.</label>
                        <input type="text" name="cellphone" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($old['cellphone'] ?? ($applicant['cellphone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Landline No.</label>
                        <input type="text" name="landline" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($old['landline'] ?? ($applicant['landline'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($old['email'] ?? ($applicant['email'] ?? $applicant['user_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>
            </div>

            <!-- SECTION C: Government IDs -->
            <div class="nsrp-section">
                <h6>C. Government IDs</h6>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">GSIS / SSS No.</label>
                        <input type="text" name="gsis_sss_no" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($old['gsis_sss_no'] ?? ($applicant['gsis_sss_no'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">PAG-IBIG No.</label>
                        <input type="text" name="pag_ibig_no" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($old['pag_ibig_no'] ?? ($applicant['pag_ibig_no'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">PhilHealth No.</label>
                        <input type="text" name="philhealth_no" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($old['philhealth_no'] ?? ($applicant['philhealth_no'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">TIN</label>
                        <input type="text" name="tin" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($old['tin'] ?? ($applicant['tin'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Passport No. (if any)</label>
                        <input type="text" name="passport_no" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($old['passport_no'] ?? ($applicant['passport_no'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>
            </div>

            <!-- SECTION D: Disability -->
            <div class="nsrp-section">
                <h6>D. Disability (PWD)</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Disability / Special Needs (leave blank if none)</label>
                        <select name="disability" class="form-select form-select-sm">
                            <option value="">None / Not Applicable</option>
                            <?php foreach (['Visual','Hearing','Physical','Intellectual/Learning','Mental/Psychosocial','Other'] as $d): ?>
                                <?php $val = strtolower(explode('/', $d)[0]); ?>
                                <option value="<?= $d ?>"
                                    <?= ($old['disability'] ?? $applicant['disability'] ?? '') === $d ? 'selected' : '' ?>>
                                    <?= $d ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- SECTION E: Employment Status -->
            <div class="nsrp-section">
                <h6>E. Employment Status</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Current Employment Status</label>
                        <select name="employment_status" class="form-select form-select-sm">
                            <option value="">-- Select --</option>
                            <?php foreach ([
                                'Unemployed' => 'Unemployed',
                                'Underemployed' => 'Underemployed (wants more work/better pay)',
                                'Self-employed' => 'Self-employed',
                                'Employed' => 'Employed (seeking better opportunity)',
                                'Fresh Graduate' => 'Fresh Graduate',
                                'Student' => 'Student (looking for work)',
                            ] as $v => $l): ?>
                                <option value="<?= $v ?>" <?= ($old['employment_status'] ?? $applicant['employment_status'] ?? '') === $v ? 'selected' : '' ?>>
                                    <?= $l ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-center gap-3 mt-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="actively_looking" id="actively_looking"
                                   <?= ($old['actively_looking'] ?? $applicant['actively_looking'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="actively_looking">Actively Looking</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="willing_immediate" id="willing_immediate"
                                   <?= ($old['willing_immediate'] ?? $applicant['willing_immediate'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="willing_immediate">Willing to Start Immediately</label>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-center gap-3 mt-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_4ps" id="is_4ps"
                                   <?= ($old['is_4ps'] ?? $applicant['is_4ps'] ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_4ps">4Ps Beneficiary</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">4Ps Household ID (if applicable)</label>
                        <input type="text" name="household_id" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($old['household_id'] ?? ($applicant['household_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>
            </div>

            <!-- SECTION F: Job Preference -->
            <div class="nsrp-section">
                <h6>F. Job Preference</h6>
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Preferred Occupation / Position</label>
                        <input type="text" name="preferred_occupation" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($old['preferred_occupation'] ?? ($applicant['preferred_occupation'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Preferred Work Location</label>
                        <input type="text" name="preferred_location" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($old['preferred_location'] ?? ($applicant['preferred_location'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Expected Monthly Salary</label>
                        <input type="text" name="expected_salary" class="form-control form-control-sm" placeholder="e.g. 18000"
                               value="<?= htmlspecialchars($old['expected_salary'] ?? ($applicant['expected_salary'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>
            </div>

            <!-- SECTION G: Education -->
            <div class="nsrp-section">
                <h6>G. Educational Background</h6>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Highest Educational Attainment (School, Course, Year Graduated)</label>
                        <textarea name="educational_bg" class="form-control form-control-sm" rows="2"
                                  placeholder="e.g. Bachelor of Science in Information Technology, Cebu Technological University, 2022"><?= htmlspecialchars($old['educational_bg'] ?? ($applicant['educational_bg'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Eligibility / Licensure Examination Passed</label>
                        <input type="text" name="eligibility" class="form-control form-control-sm"
                               placeholder="e.g. PRC Licensed Teacher (2022), Civil Service Eligibility"
                               value="<?= htmlspecialchars($old['eligibility'] ?? ($applicant['eligibility'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>
            </div>

            <!-- SECTION H: Work Experience & Skills -->
            <div class="nsrp-section">
                <h6>H. Work Experience & Skills</h6>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Work Experience (Position, Company, Inclusive Dates)</label>
                        <textarea name="work_experience" class="form-control form-control-sm" rows="3"
                                  placeholder="e.g. Sales Associate, SM Supermarket, Jan 2020 – Dec 2021"><?= htmlspecialchars($old['work_experience'] ?? ($applicant['work_experience'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Trainings / Seminars Attended</label>
                        <textarea name="trainings" class="form-control form-control-sm" rows="2"
                                  placeholder="e.g. NC II TESDA Welding, 2021"><?= htmlspecialchars($old['trainings'] ?? ($applicant['trainings'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Other Skills</label>
                        <input type="text" name="other_skills" class="form-control form-control-sm"
                               placeholder="e.g. MS Office, Adobe Photoshop, driving"
                               value="<?= htmlspecialchars($old['other_skills'] ?? ($applicant['other_skills'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>
            </div>

            <div class="d-flex gap-3 justify-content-end mt-4">
                <a href="<?= APP_URL ?>/applicant/job-fairs" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-send me-1"></i>Submit Registration
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
?>
