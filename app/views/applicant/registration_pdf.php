<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NSRP Form 1 — Job Fair Registration</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Arial, Helvetica, sans-serif; font-size: 10pt; color: #000; background: #fff; }

    .print-btn {
        position: fixed; top: 12px; right: 16px; z-index: 9999;
        background: #0d6efd; color: #fff; border: none; padding: 8px 20px;
        border-radius: 4px; cursor: pointer; font-size: 13px;
    }
    .print-btn:hover { background: #0b5ed7; }

    @media print {
        .print-btn { display: none !important; }
        @page { size: A4; margin: 12mm 14mm; }
    }

    .page { max-width: 210mm; margin: 0 auto; padding: 14mm; }

    /* Header */
    .nsrp-header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 10px; }
    .nsrp-header .gov-logo { font-weight: bold; font-size: 11pt; }
    .nsrp-header .form-title { font-size: 14pt; font-weight: 900; text-transform: uppercase; margin: 4px 0 2px; }
    .nsrp-header .form-subtitle { font-size: 9pt; }
    .nsrp-header .form-number { font-size: 9pt; color: #444; }

    .fair-info { border: 1px solid #000; padding: 6px 10px; margin-bottom: 10px; font-size: 9pt; }
    .fair-info table { width: 100%; border-collapse: collapse; }
    .fair-info td { padding: 2px 6px; }
    .fair-info .label { font-weight: bold; width: 120px; }

    /* Sections */
    .section { margin-bottom: 10px; }
    .section-title {
        background: #1e2a3a; color: #fff; font-weight: bold;
        padding: 3px 8px; font-size: 9pt; text-transform: uppercase;
        letter-spacing: .5px; margin-bottom: 6px;
    }
    .field-row { display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 4px; }
    .field { flex: 1; min-width: 120px; }
    .field .f-label { font-size: 8pt; color: #555; border-bottom: 1px solid #bbb; padding-bottom: 1px; margin-bottom: 1px; }
    .field .f-val { font-size: 10pt; font-weight: bold; min-height: 16px; padding: 1px 0; }
    .field.w100 { flex: 0 0 100%; }
    .field.w50  { flex: 0 0 calc(50% - 4px); }
    .field.w33  { flex: 0 0 calc(33.33% - 4px); }
    .field.w25  { flex: 0 0 calc(25% - 4px); }
    .field.w20  { flex: 0 0 calc(20% - 4px); }

    .checkbox-row { display: flex; gap: 14px; margin: 4px 0; font-size: 9pt; }
    .cb { display: flex; align-items: center; gap: 4px; }
    .cb-box { width: 12px; height: 12px; border: 1px solid #000; display: inline-block; text-align: center; line-height: 11px; font-size: 9pt; }
    .checked { background: #000; color: #fff; }

    .signature-row { margin-top: 20px; display: flex; justify-content: space-between; }
    .sig-block { text-align: center; width: 200px; }
    .sig-line { border-top: 1px solid #000; margin-top: 30px; padding-top: 3px; font-size: 8pt; }

    .footer-note { margin-top: 8px; font-size: 7.5pt; color: #555; border-top: 1px solid #ccc; padding-top: 5px; }
</style>
</head>
<body>

<button class="print-btn" onclick="window.print()">🖨 Print / Save as PDF</button>

<div class="page">

    <!-- Header -->
    <div class="nsrp-header">
        <div class="gov-logo">Republic of the Philippines</div>
        <div class="gov-logo">Department of Labor and Employment (DOLE)</div>
        <div class="form-title">NSRP Form 1</div>
        <div class="form-subtitle">NATIONAL SKILLS REGISTRATION PROGRAM</div>
        <div class="form-subtitle" style="font-weight:bold">JOB SEEKER'S REGISTRATION FORM</div>
        <div class="form-number">For Job Fair / PESO Registration</div>
    </div>

    <!-- Job Fair Info -->
    <div class="fair-info">
        <table>
            <tr>
                <td class="label">Job Fair:</td>
                <td><?= htmlspecialchars($detail['post_title'] ?? $detail['fair_title'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="label">Date:</td>
                <td><?= !empty($detail['event_date']) ? date('F d, Y', strtotime($detail['event_date'])) : '—' ?>
                    <?= !empty($detail['event_time']) ? ' at ' . htmlspecialchars($detail['event_time']) : '' ?></td>
            </tr>
            <tr>
                <td class="label">Venue:</td>
                <td colspan="3"><?= htmlspecialchars($detail['post_venue'] ?? $detail['fair_venue'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
                <td class="label">Date Registered:</td>
                <td><?= !empty($detail['registered_at']) ? date('F d, Y', strtotime($detail['registered_at'])) : '' ?></td>
                <td class="label">Form No.:</td>
                <td><?= str_pad($detail['id'] ?? '', 6, '0', STR_PAD_LEFT) ?></td>
            </tr>
        </table>
    </div>

    <!-- A. Personal Information -->
    <div class="section">
        <div class="section-title">A. Personal Information</div>
        <div class="field-row">
            <div class="field w25">
                <div class="f-label">Surname</div>
                <div class="f-val"><?= htmlspecialchars(strtoupper($detail['surname'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="field w25">
                <div class="f-label">First Name</div>
                <div class="f-val"><?= htmlspecialchars($detail['firstname'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="field w25">
                <div class="f-label">Middle Name</div>
                <div class="f-val"><?= htmlspecialchars($detail['middlename'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="field w20" style="min-width:60px;max-width:80px">
                <div class="f-label">Suffix</div>
                <div class="f-val"><?= htmlspecialchars($detail['suffix'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
        <div class="field-row">
            <div class="field w25">
                <div class="f-label">Date of Birth</div>
                <div class="f-val"><?= !empty($detail['date_of_birth']) ? date('m/d/Y', strtotime($detail['date_of_birth'])) : '' ?></div>
            </div>
            <div class="field w33">
                <div class="f-label">Place of Birth</div>
                <div class="f-val"><?= htmlspecialchars($detail['place_of_birth'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="field w20" style="min-width:70px">
                <div class="f-label">Sex</div>
                <div class="f-val"><?= ucfirst($detail['sex'] ?? '') ?></div>
            </div>
            <div class="field w20">
                <div class="f-label">Civil Status</div>
                <div class="f-val"><?= ucfirst(str_replace('_', '-', $detail['civil_status'] ?? '')) ?></div>
            </div>
        </div>
        <div class="field-row">
            <div class="field w33">
                <div class="f-label">Religion</div>
                <div class="f-val"><?= htmlspecialchars($detail['religion'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="field w20" style="min-width:80px">
                <div class="f-label">Height (cm)</div>
                <div class="f-val"><?= htmlspecialchars($detail['height'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
        <div class="field-row">
            <div class="field w100">
                <div class="f-label">Present Address</div>
                <div class="f-val"><?= htmlspecialchars($detail['present_address'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
    </div>

    <!-- B. Contact & IDs -->
    <div class="section">
        <div class="section-title">B. Contact Information &amp; Government IDs</div>
        <div class="field-row">
            <div class="field w25">
                <div class="f-label">Cellphone No.</div>
                <div class="f-val"><?= htmlspecialchars($detail['cellphone'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="field w25">
                <div class="f-label">Landline No.</div>
                <div class="f-val"><?= htmlspecialchars($detail['landline'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="field w33">
                <div class="f-label">Email Address</div>
                <div class="f-val"><?= htmlspecialchars($detail['email'] ?? $detail['user_email'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
        <div class="field-row">
            <div class="field w25">
                <div class="f-label">GSIS / SSS No.</div>
                <div class="f-val"><?= htmlspecialchars($detail['gsis_sss_no'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="field w25">
                <div class="f-label">PAG-IBIG No.</div>
                <div class="f-val"><?= htmlspecialchars($detail['pag_ibig_no'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="field w25">
                <div class="f-label">PhilHealth No.</div>
                <div class="f-val"><?= htmlspecialchars($detail['philhealth_no'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="field w25">
                <div class="f-label">TIN</div>
                <div class="f-val"><?= htmlspecialchars($detail['tin'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
        <div class="field-row">
            <div class="field w25">
                <div class="f-label">Passport No.</div>
                <div class="f-val"><?= htmlspecialchars($detail['passport_no'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
    </div>

    <!-- C. Disability -->
    <div class="section">
        <div class="section-title">C. Disability / Special Needs (PWD)</div>
        <div class="field-row">
            <div class="field w50">
                <div class="f-label">Type of Disability</div>
                <div class="f-val"><?= !empty($detail['disability']) ? htmlspecialchars($detail['disability'], ENT_QUOTES, 'UTF-8') : 'None / Not Applicable' ?></div>
            </div>
        </div>
    </div>

    <!-- D. Employment & Preference -->
    <div class="section">
        <div class="section-title">D. Employment Status &amp; Job Preference</div>
        <div class="field-row">
            <div class="field w33">
                <div class="f-label">Employment Status</div>
                <div class="f-val"><?= htmlspecialchars($detail['employment_status'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="field w33">
                <div class="f-label">Preferred Occupation</div>
                <div class="f-val"><?= htmlspecialchars($detail['preferred_occupation'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="field w33">
                <div class="f-label">Expected Monthly Salary</div>
                <div class="f-val"><?= !empty($detail['expected_salary']) ? 'PHP ' . htmlspecialchars($detail['expected_salary'], ENT_QUOTES, 'UTF-8') : '' ?></div>
            </div>
        </div>
        <div class="field-row">
            <div class="field w33">
                <div class="f-label">Preferred Work Location</div>
                <div class="f-val"><?= htmlspecialchars($detail['preferred_location'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
        <div class="checkbox-row">
            <div class="cb">
                <span class="cb-box <?= $detail['actively_looking'] ? 'checked' : '' ?>"><?= $detail['actively_looking'] ? '✓' : '&nbsp;' ?></span>
                Actively Looking for Work
            </div>
            <div class="cb">
                <span class="cb-box <?= $detail['willing_immediate'] ? 'checked' : '' ?>"><?= $detail['willing_immediate'] ? '✓' : '&nbsp;' ?></span>
                Willing to Start Immediately
            </div>
            <div class="cb">
                <span class="cb-box <?= $detail['is_4ps'] ? 'checked' : '' ?>"><?= $detail['is_4ps'] ? '✓' : '&nbsp;' ?></span>
                4Ps Beneficiary
                <?= !empty($detail['household_id']) ? '(Household ID: ' . htmlspecialchars($detail['household_id'], ENT_QUOTES, 'UTF-8') . ')' : '' ?>
            </div>
        </div>
    </div>

    <!-- E. Education -->
    <div class="section">
        <div class="section-title">E. Educational Background</div>
        <div class="field-row">
            <div class="field w100">
                <div class="f-label">Highest Educational Attainment</div>
                <div class="f-val"><?= nl2br(htmlspecialchars($detail['educational_bg'] ?? '', ENT_QUOTES, 'UTF-8')) ?></div>
            </div>
        </div>
        <div class="field-row">
            <div class="field w100">
                <div class="f-label">Eligibility / Licensure Examination Passed</div>
                <div class="f-val"><?= htmlspecialchars($detail['eligibility'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
    </div>

    <!-- F. Work Experience & Skills -->
    <div class="section">
        <div class="section-title">F. Work Experience, Trainings &amp; Skills</div>
        <div class="field-row">
            <div class="field w100">
                <div class="f-label">Work Experience</div>
                <div class="f-val"><?= nl2br(htmlspecialchars($detail['work_experience'] ?? '', ENT_QUOTES, 'UTF-8')) ?></div>
            </div>
        </div>
        <div class="field-row">
            <div class="field w100">
                <div class="f-label">Trainings / Seminars Attended</div>
                <div class="f-val"><?= nl2br(htmlspecialchars($detail['trainings'] ?? '', ENT_QUOTES, 'UTF-8')) ?></div>
            </div>
        </div>
        <div class="field-row">
            <div class="field w100">
                <div class="f-label">Other Skills</div>
                <div class="f-val"><?= htmlspecialchars($detail['other_skills'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
    </div>

    <!-- Signature -->
    <div class="signature-row">
        <div class="sig-block">
            <div class="sig-line">Date: <?= date('F d, Y') ?></div>
        </div>
        <div class="sig-block">
            <div class="sig-line">Signature of Applicant over Printed Name</div>
        </div>
        <div class="sig-block">
            <div class="sig-line">Signature of PESO Officer</div>
        </div>
    </div>

    <div class="footer-note">
        This form is generated by ReliaWork2 — PESO Job Fair Registration System.
        Registered on <?= !empty($detail['registered_at']) ? date('F d, Y', strtotime($detail['registered_at'])) : '' ?>.
        Form Reference No.: <?= str_pad($detail['id'] ?? '', 6, '0', STR_PAD_LEFT) ?>
    </div>

</div>

<script>
// Auto-trigger print dialog when opened in new tab
window.onload = function() {
    // Small delay so page is fully rendered
    setTimeout(() => { window.print(); }, 400);
};
</script>
</body>
</html>
