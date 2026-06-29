<?php
/**
 * ReliaWork2 HiringController
 * Manages post-interview hiring workflow:
 * qualification → messaging → employment docs → first-day scheduling → final hire
 */

class HiringController
{
    private Database $db;
    private NotificationModel $notifModel;

    public function __construct()
    {
        $this->db       = Database::getInstance();
        $this->notifModel = new NotificationModel();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function logStatusChange(int $appId, string $from, string $to, ?int $userId, ?string $remarks = null): void
    {
        $this->db->execute(
            "INSERT INTO application_status_history
             (application_id, from_status, to_status, changed_by, remarks, changed_at)
             VALUES (?,?,?,?,?,NOW())",
            [$appId, $from, $to, $userId, $remarks]
        );
    }

    private function getApplicationForAgency(int $appId, int $agencyUserId): array|false
    {
        return $this->db->fetch(
            "SELECT app.*,
                    a.surname, a.firstname, a.middlename, a.cellphone,
                    a.present_address, a.email AS applicant_email_field,
                    a.preferred_occupation, a.educational_bg, a.work_experience, a.other_skills,
                    a.gsis_sss_no, a.pag_ibig_no, a.philhealth_no,
                    u.email AS applicant_email, u.id AS applicant_user_id,
                    jv.position, jv.company_name, jv.company_location,
                    pa.agency_name, pa.id AS agency_id, pa.user_id AS agency_user_id,
                    jfr.title AS fair_title
             FROM applications app
             JOIN applicants a  ON a.id  = app.applicant_id
             LEFT JOIN users u  ON u.id  = a.user_id
             JOIN job_vacancies jv ON jv.id = app.job_vacancy_id
             JOIN participating_agencies pa ON pa.id = jv.participating_agency_id
             LEFT JOIN job_fair_requests jfr ON jfr.id = pa.job_fair_request_id
             WHERE app.id = ? AND pa.user_id = ?",
            [$appId, $agencyUserId]
        );
    }

    // ── Agency: Qualified Applicants Dashboard ────────────────────────────────

    // GET /agency/hiring
    public function dashboard(): void
    {
        requireRole('agency');
        $userId = (int)currentUser()['id'];

        $qualifiedStatuses = ['qualified_for_contact','waitlisted','awaiting_requirements',
                              'requirements_submitted','first_day_scheduled','hired'];
        $ph = implode(',', array_fill(0, count($qualifiedStatuses), '?'));

        $applicants = $this->db->fetchAll(
            "SELECT app.*,
                    a.surname, a.firstname, a.middlename, a.cellphone,
                    a.present_address, a.preferred_occupation,
                    u.email AS applicant_email,
                    jv.position, pa.agency_name, pa.id AS agency_id,
                    jfr.title AS fair_title,
                    iv.hiring_outcome, iv.score_summary,
                    (SELECT COUNT(*) FROM messages m WHERE m.application_id = app.id AND m.is_read = 0 AND m.sender_role != 'agency') AS unread_msgs,
                    (SELECT COUNT(*) FROM employment_documents ed WHERE ed.application_id = app.id) AS emp_doc_count
             FROM applications app
             JOIN applicants a  ON a.id  = app.applicant_id
             LEFT JOIN users u  ON u.id  = a.user_id
             JOIN job_vacancies jv ON jv.id = app.job_vacancy_id
             JOIN participating_agencies pa ON pa.id = jv.participating_agency_id
             LEFT JOIN job_fair_requests jfr ON jfr.id = pa.job_fair_request_id
             LEFT JOIN interviews iv ON iv.application_id = app.id
             WHERE pa.user_id = ? AND app.status IN ($ph)
             ORDER BY app.updated_at DESC",
            array_merge([$userId], $qualifiedStatuses)
        );

        $stats = [
            'qualified'   => count(array_filter($applicants, fn($a) => $a['status'] === 'qualified_for_contact')),
            'waitlisted'  => count(array_filter($applicants, fn($a) => $a['status'] === 'waitlisted')),
            'awaiting'    => count(array_filter($applicants, fn($a) => in_array($a['status'], ['awaiting_requirements','requirements_submitted']))),
            'scheduled'   => count(array_filter($applicants, fn($a) => $a['status'] === 'first_day_scheduled')),
            'hired'       => count(array_filter($applicants, fn($a) => $a['status'] === 'hired')),
        ];

        $pageTitle = 'Qualified Applicants';
        $success   = getFlash('success');
        include VIEW_PATH . '/agency/hiring_dashboard.php';
    }

    // GET /agency/hiring/{appId}  — Applicant profile + full actions
    public function profile(int $appId): void
    {
        requireRole('agency');
        $userId = (int)currentUser()['id'];

        $app = $this->getApplicationForAgency($appId, $userId);
        if (!$app) {
            flash('error', 'Applicant not found.');
            redirect(APP_URL . '/agency/hiring');
        }

        $appDocs  = $this->db->fetchAll(
            "SELECT * FROM application_documents WHERE application_id = ? ORDER BY doc_type",
            [$appId]
        );
        $empDocs  = $this->db->fetchAll(
            "SELECT * FROM employment_documents WHERE application_id = ? ORDER BY doc_type",
            [$appId]
        );
        $messages = $this->db->fetchAll(
            "SELECT m.*, u.name AS sender_name, u.role AS sender_role_actual
             FROM messages m
             JOIN users u ON u.id = m.sender_id
             WHERE m.application_id = ?
             ORDER BY m.sent_at ASC",
            [$appId]
        );
        $interview = $this->db->fetch(
            "SELECT iv.*, COUNT(iq.id) AS q_count,
                    SUM(CASE WHEN iq.answer_status='excellent' THEN 4
                             WHEN iq.answer_status='good' THEN 3
                             WHEN iq.answer_status='fair' THEN 2
                             WHEN iq.answer_status='poor' THEN 1
                             ELSE 0 END) AS total_score,
                    COUNT(iq.id) * 4 AS max_score
             FROM interviews iv
             LEFT JOIN interview_questions iq ON iq.interview_id = iv.id
             WHERE iv.application_id = ?
             GROUP BY iv.id",
            [$appId]
        );
        $history = $this->db->fetchAll(
            "SELECT ash.*, u.name AS changed_by_name
             FROM application_status_history ash
             LEFT JOIN users u ON u.id = ash.changed_by
             WHERE ash.application_id = ?
             ORDER BY ash.changed_at ASC",
            [$appId]
        );

        // Mark messages as read
        $this->db->execute(
            "UPDATE messages SET is_read = 1
             WHERE application_id = ? AND sender_role != 'agency'",
            [$appId]
        );

        $success = getFlash('success');
        $error   = getFlash('error');
        $pageTitle = 'Applicant Profile — ' . strtoupper($app['surname']) . ', ' . $app['firstname'];
        include VIEW_PATH . '/agency/applicant_profile.php';
    }

    // POST /agency/hiring/{appId}/send-message
    public function sendMessage(int $appId): void
    {
        requireRole('agency');
        verifyCsrf();

        $userId = (int)currentUser()['id'];
        $app    = $this->getApplicationForAgency($appId, $userId);
        if (!$app) { redirect(APP_URL . '/agency/hiring'); }

        $message = trim($_POST['message'] ?? '');
        if (empty($message)) {
            flash('error', 'Message cannot be empty.');
            redirect(APP_URL . '/agency/hiring/' . $appId);
        }

        $this->db->execute(
            "INSERT INTO messages (application_id, sender_id, sender_role, message, sent_at)
             VALUES (?, ?, 'agency', ?, NOW())",
            [$appId, $userId, $message]
        );

        // In-app notification to applicant
        if ($app['applicant_user_id']) {
            $this->notifModel->create(
                (int)$app['applicant_user_id'],
                'new_message',
                'New Message from ' . $app['agency_name'],
                substr($message, 0, 100) . (strlen($message) > 100 ? '...' : ''),
                APP_URL . '/applicant/messages/' . $appId
            );
        }

        flash('success', 'Message sent.');
        redirect(APP_URL . '/agency/hiring/' . $appId);
    }

    // POST /agency/hiring/{appId}/request-requirements
    public function requestRequirements(int $appId): void
    {
        requireRole('agency');
        verifyCsrf();

        $userId = (int)currentUser()['id'];
        $app    = $this->getApplicationForAgency($appId, $userId);
        if (!$app) { redirect(APP_URL . '/agency/hiring'); }

        $oldStatus = $app['status'];
        $msg = trim($_POST['message'] ?? "Congratulations! You passed the interview. Please submit your employment requirements: SSS ID, PhilHealth ID, and TIN.");

        $this->db->execute(
            "UPDATE applications SET status = 'awaiting_requirements', updated_at = NOW() WHERE id = ?",
            [$appId]
        );
        $this->logStatusChange($appId, $oldStatus, 'awaiting_requirements', $userId,
            'Agency requested employment requirements.');

        // Send in-app message
        $this->db->execute(
            "INSERT INTO messages (application_id, sender_id, sender_role, message, sent_at)
             VALUES (?, ?, 'agency', ?, NOW())",
            [$appId, $userId, $msg]
        );

        if ($app['applicant_user_id']) {
            $this->notifModel->create(
                (int)$app['applicant_user_id'],
                'requirements_requested',
                'Employment Requirements Required',
                $msg,
                APP_URL . '/applicant/messages/' . $appId
            );
        }

        flash('success', 'Applicant notified to submit employment requirements.');
        redirect(APP_URL . '/agency/hiring/' . $appId);
    }

    // POST /agency/hiring/{appId}/schedule-first-day
    public function scheduleFirstDay(int $appId): void
    {
        requireRole('agency');
        verifyCsrf();

        $userId = (int)currentUser()['id'];
        $app    = $this->getApplicationForAgency($appId, $userId);
        if (!$app) { redirect(APP_URL . '/agency/hiring'); }

        $date     = trim($_POST['first_day_date'] ?? '');
        $time     = trim($_POST['first_day_time'] ?? '') ?: null;
        $location = trim($_POST['first_day_location'] ?? '');
        $notes    = trim($_POST['first_day_notes'] ?? '');

        if (empty($date) || empty($location)) {
            flash('error', 'Date and reporting location are required.');
            redirect(APP_URL . '/agency/hiring/' . $appId);
        }

        $oldStatus = $app['status'];
        $this->db->execute(
            "UPDATE applications
             SET status = 'first_day_scheduled',
                 first_day_date = ?, first_day_time = ?, first_day_location = ?,
                 first_day_notes = ?, scheduled_at = NOW(), updated_at = NOW()
             WHERE id = ?",
            [$date, $time, $location, $notes ?: null, $appId]
        );
        $this->logStatusChange($appId, $oldStatus, 'first_day_scheduled', $userId,
            "First day scheduled: {$date} at {$location}");

        $schedMsg = "Your first day has been scheduled!\n"
            . "Date: " . date('F d, Y', strtotime($date))
            . ($time ? " at " . date('g:i A', strtotime($time)) : '') . "\n"
            . "Report to: {$location}"
            . ($notes ? "\nNotes: {$notes}" : '');

        // Send in-app message
        $this->db->execute(
            "INSERT INTO messages (application_id, sender_id, sender_role, message, sent_at)
             VALUES (?, ?, 'agency', ?, NOW())",
            [$appId, $userId, $schedMsg]
        );

        if ($app['applicant_user_id']) {
            $this->notifModel->create(
                (int)$app['applicant_user_id'],
                'first_day_scheduled',
                'First Day Scheduled',
                "Your first day at {$app['agency_name']} is " . date('F d, Y', strtotime($date)) .
                ($time ? " at " . date('g:i A', strtotime($time)) : '') . ". Report to: {$location}",
                APP_URL . '/applicant/messages/' . $appId
            );
        }

        flash('success', 'First day scheduled and applicant has been notified.');
        redirect(APP_URL . '/agency/hiring/' . $appId);
    }

    // POST /agency/hiring/{appId}/mark-hired
    public function markHired(int $appId): void
    {
        requireRole('agency');
        verifyCsrf();

        $userId = (int)currentUser()['id'];
        $app    = $this->getApplicationForAgency($appId, $userId);
        if (!$app || $app['status'] !== 'first_day_scheduled') {
            flash('error', 'Cannot mark as hired — first day must be scheduled first.');
            redirect(APP_URL . '/agency/hiring/' . $appId);
        }

        $oldStatus = $app['status'];
        $this->db->execute(
            "UPDATE applications SET status = 'hired', hired_at = NOW(), updated_at = NOW() WHERE id = ?",
            [$appId]
        );
        $this->logStatusChange($appId, $oldStatus, 'hired', $userId, 'Officially hired.');

        // Send in-app message
        $hiredMsg = "Congratulations! You are now officially HIRED at {$app['agency_name']} for the position of {$app['position']}. Welcome aboard!";
        $this->db->execute(
            "INSERT INTO messages (application_id, sender_id, sender_role, message, sent_at)
             VALUES (?, ?, 'agency', ?, NOW())",
            [$appId, $userId, $hiredMsg]
        );

        if ($app['applicant_user_id']) {
            $this->notifModel->create(
                (int)$app['applicant_user_id'],
                'hired',
                'You Are Officially Hired! 🎉',
                $hiredMsg,
                APP_URL . '/applicant/messages/' . $appId
            );
        }

        // Notify reporting officers
        $reporters = $this->db->fetchAll(
            "SELECT id FROM users WHERE role = 'reporting_officer' AND status = 'approved'"
        );
        $name = strtoupper($app['surname']) . ', ' . $app['firstname'];
        foreach ($reporters as $r) {
            $this->notifModel->create(
                (int)$r['id'], 'hired',
                'New Hire Confirmed',
                "{$name} is now officially hired by {$app['agency_name']} for {$app['position']}.",
                APP_URL . '/reporting-officer/dashboard'
            );
        }

        auditLog('mark_hired', 'applications', "Application {$appId} marked hired by agency user {$userId}.");
        flash('success', 'Applicant officially marked as HIRED. Congratulations!');
        redirect(APP_URL . '/agency/hiring/' . $appId);
    }

    // ── Applicant: Messages + Employment Docs ─────────────────────────────────

    // GET /applicant/messages/{appId}
    public function applicantMessages(int $appId): void
    {
        requireRole('applicant');
        $userId    = (int)currentUser()['id'];
        $applicant = $this->db->fetch("SELECT * FROM applicants WHERE user_id = ?", [$userId]);
        if (!$applicant) { redirect(APP_URL . '/applicant/dashboard'); }

        $app = $this->db->fetch(
            "SELECT app.*,
                    jv.position, pa.agency_name, pa.id AS agency_id,
                    jfr.title AS fair_title
             FROM applications app
             JOIN job_vacancies jv ON jv.id = app.job_vacancy_id
             JOIN participating_agencies pa ON pa.id = jv.participating_agency_id
             LEFT JOIN job_fair_requests jfr ON jfr.id = pa.job_fair_request_id
             WHERE app.id = ? AND app.applicant_id = ?",
            [$appId, $applicant['id']]
        );
        if (!$app) {
            flash('error', 'Application not found.');
            redirect(APP_URL . '/applicant/my-applications');
        }

        $messages = $this->db->fetchAll(
            "SELECT m.*, u.name AS sender_name
             FROM messages m
             JOIN users u ON u.id = m.sender_id
             WHERE m.application_id = ?
             ORDER BY m.sent_at ASC",
            [$appId]
        );

        // Mark applicant messages as read
        $this->db->execute(
            "UPDATE messages SET is_read = 1 WHERE application_id = ? AND sender_role = 'agency'",
            [$appId]
        );

        $empDocs  = $this->db->fetchAll(
            "SELECT * FROM employment_documents WHERE application_id = ? ORDER BY doc_type",
            [$appId]
        );

        $success = getFlash('success');
        $error   = getFlash('error');
        $pageTitle = 'Messages — ' . $app['agency_name'];
        include VIEW_PATH . '/applicant/messages.php';
    }

    // POST /applicant/messages/{appId}/reply
    public function applicantReply(int $appId): void
    {
        requireRole('applicant');
        verifyCsrf();

        $userId    = (int)currentUser()['id'];
        $applicant = $this->db->fetch("SELECT * FROM applicants WHERE user_id = ?", [$userId]);
        if (!$applicant) { redirect(APP_URL . '/applicant/dashboard'); }

        $app = $this->db->fetch(
            "SELECT app.*, pa.user_id AS agency_user_id, pa.agency_name,
                    jv.position
             FROM applications app
             JOIN job_vacancies jv ON jv.id = app.job_vacancy_id
             JOIN participating_agencies pa ON pa.id = jv.participating_agency_id
             WHERE app.id = ? AND app.applicant_id = ?",
            [$appId, $applicant['id']]
        );
        if (!$app) { redirect(APP_URL . '/applicant/my-applications'); }

        $message = trim($_POST['message'] ?? '');
        if (empty($message)) {
            flash('error', 'Message cannot be empty.');
            redirect(APP_URL . '/applicant/messages/' . $appId);
        }

        $this->db->execute(
            "INSERT INTO messages (application_id, sender_id, sender_role, message, sent_at)
             VALUES (?, ?, 'applicant', ?, NOW())",
            [$appId, $userId, $message]
        );

        // Notify agency
        if ($app['agency_user_id']) {
            $name = strtoupper($applicant['surname']) . ', ' . $applicant['firstname'];
            $this->notifModel->create(
                (int)$app['agency_user_id'], 'applicant_message',
                "Reply from {$name}",
                substr($message, 0, 100),
                APP_URL . '/agency/hiring/' . $appId
            );
        }

        flash('success', 'Message sent.');
        redirect(APP_URL . '/applicant/messages/' . $appId);
    }

    // POST /applicant/messages/{appId}/upload-employment-docs
    public function uploadEmploymentDocs(int $appId): void
    {
        requireRole('applicant');
        verifyCsrf();

        $userId    = (int)currentUser()['id'];
        $applicant = $this->db->fetch("SELECT * FROM applicants WHERE user_id = ?", [$userId]);
        if (!$applicant) { redirect(APP_URL . '/applicant/dashboard'); }

        $app = $this->db->fetch(
            "SELECT app.*, pa.user_id AS agency_user_id, pa.agency_name
             FROM applications app
             JOIN job_vacancies jv ON jv.id = app.job_vacancy_id
             JOIN participating_agencies pa ON pa.id = jv.participating_agency_id
             WHERE app.id = ? AND app.applicant_id = ? AND app.status = 'awaiting_requirements'",
            [$appId, $applicant['id']]
        );
        if (!$app) {
            flash('error', 'Cannot upload requirements at this time.');
            redirect(APP_URL . '/applicant/messages/' . $appId);
        }

        $uploadDir  = PUBLIC_PATH . '/uploads/employment/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $allowedExt = ['pdf','doc','docx','jpg','jpeg','png'];
        $uploaded   = 0;

        foreach (['sss_id','philhealth_id','tin','nbi_clearance','medical','other'] as $type) {
            if (empty($_FILES[$type]['name'])) continue;
            $file     = $_FILES[$type];
            $origName = basename($file['name']);
            $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExt) || $file['size'] > 5*1024*1024) continue;
            $storedName = 'emp_' . $appId . '_' . $type . '_' . time() . '.' . $ext;
            if (!move_uploaded_file($file['tmp_name'], $uploadDir . $storedName)) continue;
            $this->db->execute(
                "INSERT INTO employment_documents
                 (application_id, applicant_id, doc_type, original_name, stored_name, file_path, file_size, mime_type)
                 VALUES (?,?,?,?,?,?,?,?)",
                [$appId, $applicant['id'], $type, $origName, $storedName,
                 '/uploads/employment/' . $storedName, $file['size'], $file['type']]
            );
            $uploaded++;
        }

        if ($uploaded === 0) {
            flash('error', 'Please upload at least one document.');
            redirect(APP_URL . '/applicant/messages/' . $appId);
        }

        // Update status
        $this->db->execute(
            "UPDATE applications SET status = 'requirements_submitted', updated_at = NOW() WHERE id = ?",
            [$appId]
        );
        $this->logStatusChange($appId, 'awaiting_requirements', 'requirements_submitted',
            $userId, "Uploaded {$uploaded} employment document(s).");

        // Notify agency
        if ($app['agency_user_id']) {
            $name = strtoupper($applicant['surname']) . ', ' . $applicant['firstname'];
            $this->notifModel->create(
                (int)$app['agency_user_id'], 'employment_docs_submitted',
                'Employment Documents Submitted',
                "{$name} has submitted {$uploaded} employment document(s). You can now schedule their first day.",
                APP_URL . '/agency/hiring/' . $appId
            );
        }

        // Auto-send in-app message
        $this->db->execute(
            "INSERT INTO messages (application_id, sender_id, sender_role, message, sent_at)
             VALUES (?, ?, 'applicant', ?, NOW())",
            [$appId, $userId, "I have submitted my employment requirements ({$uploaded} document(s))."]
        );

        flash('success', "{$uploaded} employment document(s) submitted. The company will review and schedule your first day.");
        redirect(APP_URL . '/applicant/messages/' . $appId);
    }
}
