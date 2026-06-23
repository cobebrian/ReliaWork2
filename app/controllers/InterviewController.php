<?php
/**
 * ReliaWork2 InterviewController
 * Agency-side interview management.
 */

class InterviewController
{
    private Database $db;
    private NotificationModel $notifModel;

    public function __construct()
    {
        $this->db         = Database::getInstance();
        $this->notifModel = new NotificationModel();
    }

    // GET /agency/interviews — list validated applicants available for interview
    public function index(): void
    {
        requireRole('agency');

        $userId = (int)currentUser()['id'];

        // Get agency's confirmed participating_agencies records
        $myAgencies = $this->db->fetchAll(
            "SELECT pa.id, pa.agency_name, jfr.title AS fair_title, jfr.requested_date
             FROM participating_agencies pa
             JOIN job_fair_requests jfr ON jfr.id = pa.job_fair_request_id
             WHERE pa.user_id = ? AND pa.status = 'confirmed'",
            [$userId]
        );
        $agencyIds = array_column($myAgencies, 'id');

        // Validated applicants
        $validatedApplicants = $this->db->fetchAll(
            "SELECT a.*, u.email,
                    (SELECT COUNT(*) FROM interviews iv WHERE iv.applicant_id = a.id AND iv.agency_id IN (" .
            (empty($agencyIds) ? '0' : implode(',', array_map('intval', $agencyIds))) .
            ")) AS has_interview
             FROM applicants a
             LEFT JOIN users u ON u.id = a.user_id
             WHERE a.validation_status = 'approved'
             ORDER BY a.surname, a.firstname"
        );

        // My interviews
        $myInterviews = [];
        if (!empty($agencyIds)) {
            $ph = implode(',', array_fill(0, count($agencyIds), '?'));
            $myInterviews = $this->db->fetchAll(
                "SELECT iv.*, a.surname, a.firstname, a.middlename,
                         pa.agency_name, jfr.title AS fair_title,
                         COUNT(iq.id) AS question_count,
                         SUM(CASE WHEN iq.answer_status IS NOT NULL THEN 1 ELSE 0 END) AS answered_count
                 FROM interviews iv
                 JOIN applicants a ON a.id = iv.applicant_id
                 JOIN participating_agencies pa ON pa.id = iv.agency_id
                 JOIN job_fair_requests jfr ON jfr.id = pa.job_fair_request_id
                 LEFT JOIN interview_questions iq ON iq.interview_id = iv.id
                 WHERE iv.agency_id IN ($ph)
                 GROUP BY iv.id
                 ORDER BY iv.created_at DESC",
                $agencyIds
            );
        }

        $pageTitle = 'Interviews';
        $success   = getFlash('success');
        $error     = getFlash('error');
        include VIEW_PATH . '/agency/interviews.php';
    }

    // POST /agency/interviews/create
    public function create(): void
    {
        requireRole('agency');
        verifyCsrf();

        $applicantId  = (int)($_POST['applicant_id'] ?? 0);
        $agencyId     = (int)($_POST['agency_id'] ?? 0);
        $vacancyId    = (int)($_POST['job_vacancy_id'] ?? 0) ?: null;
        $scheduledAt  = trim($_POST['scheduled_at'] ?? '') ?: null;

        $userId = (int)currentUser()['id'];

        // Verify agency belongs to this user
        $agency = $this->db->fetch(
            "SELECT * FROM participating_agencies WHERE id = ? AND user_id = ?",
            [$agencyId, $userId]
        );
        if (!$agency) {
            flash('error', 'Invalid agency.');
            redirect(APP_URL . '/agency/interviews');
        }

        // Verify applicant is validated
        $applicant = $this->db->fetch(
            "SELECT * FROM applicants WHERE id = ? AND validation_status = 'approved'",
            [$applicantId]
        );
        if (!$applicant) {
            flash('error', 'Applicant is not validated.');
            redirect(APP_URL . '/agency/interviews');
        }

        // Create interview
        $this->db->execute(
            "INSERT INTO interviews (applicant_id, agency_id, job_vacancy_id, scheduled_at, status, created_at)
             VALUES (?, ?, ?, ?, 'scheduled', NOW())",
            [$applicantId, $agencyId, $vacancyId, $scheduledAt]
        );
        $interviewId = (int)$this->db->lastInsertId();

        // Notify applicant
        if ($applicant['user_id']) {
            $this->notifModel->create(
                (int)$applicant['user_id'],
                'interview_scheduled',
                'Interview Scheduled',
                "An interview has been scheduled with {$agency['agency_name']}." .
                ($scheduledAt ? " Date: " . date('F d, Y g:i A', strtotime($scheduledAt)) : ''),
                APP_URL . '/applicant/interviews'
            );
        }

        auditLog('create_interview', 'interviews', "Agency {$agencyId} created interview for applicant {$applicantId}.");
        flash('success', 'Interview created.');
        redirect(APP_URL . '/agency/interviews/' . $interviewId . '/evaluate');
    }

    // GET /agency/interviews/{id}/evaluate
    public function evaluate(int $id): void
    {
        requireRole('agency');

        $userId    = (int)currentUser()['id'];
        $interview = $this->db->fetch(
            "SELECT iv.*, a.surname, a.firstname, a.middlename, a.disability,
                    a.preferred_occupation, a.educational_bg, a.work_experience, a.other_skills,
                    u.email AS applicant_email,
                    pa.agency_name, jfr.title AS fair_title
             FROM interviews iv
             JOIN applicants a ON a.id = iv.applicant_id
             LEFT JOIN users u ON u.id = a.user_id
             JOIN participating_agencies pa ON pa.id = iv.agency_id
             JOIN job_fair_requests jfr ON jfr.id = pa.job_fair_request_id
             WHERE iv.id = ? AND pa.user_id = ?",
            [$id, $userId]
        );
        if (!$interview) {
            flash('error', 'Interview not found.');
            redirect(APP_URL . '/agency/interviews');
        }

        $questions = $this->db->fetchAll(
            "SELECT * FROM interview_questions WHERE interview_id = ? ORDER BY sort_order, id",
            [$id]
        );

        $documents = $this->db->fetchAll(
            "SELECT * FROM applicant_documents WHERE applicant_id = ? ORDER BY doc_type",
            [$interview['applicant_id']]
        );

        $success = getFlash('success');
        $error   = getFlash('error');
        $pageTitle = 'Interview Evaluation';
        include VIEW_PATH . '/agency/interview_evaluate.php';
    }

    // POST /agency/interviews/{id}/add-question
    public function addQuestion(int $id): void
    {
        requireRole('agency');
        verifyCsrf();

        $userId = (int)currentUser()['id'];
        $this->verifyInterviewOwner($id, $userId);

        $text = trim($_POST['question_text'] ?? '');
        if (empty($text)) {
            flash('error', 'Question text is required.');
            redirect(APP_URL . '/agency/interviews/' . $id . '/evaluate');
        }

        $maxOrder = (int)$this->db->fetchColumn(
            "SELECT COALESCE(MAX(sort_order), 0) FROM interview_questions WHERE interview_id = ?",
            [$id]
        );
        $this->db->execute(
            "INSERT INTO interview_questions (interview_id, question_text, sort_order, created_at)
             VALUES (?, ?, ?, NOW())",
            [$id, $text, $maxOrder + 1]
        );

        flash('success', 'Question added.');
        redirect(APP_URL . '/agency/interviews/' . $id . '/evaluate');
    }

    // POST /agency/interviews/{id}/save-evaluations
    public function saveEvaluations(int $id): void
    {
        requireRole('agency');
        verifyCsrf();

        $userId = (int)currentUser()['id'];
        $this->verifyInterviewOwner($id, $userId);

        $statuses = $_POST['answer_status'] ?? [];
        $remarks  = $_POST['remarks']       ?? [];

        foreach ($statuses as $qid => $status) {
            $qid    = (int)$qid;
            $remark = trim($remarks[$qid] ?? '');
            $validStatuses = ['answered', 'needs_improvement', 'not_answered'];
            $status = in_array($status, $validStatuses) ? $status : null;
            $this->db->execute(
                "UPDATE interview_questions SET answer_status = ?, remarks = ? WHERE id = ? AND interview_id = ?",
                [$status, $remark ?: null, $qid, $id]
            );
        }

        flash('success', 'Evaluations saved.');
        redirect(APP_URL . '/agency/interviews/' . $id . '/evaluate');
    }

    // POST /agency/interviews/{id}/complete
    public function complete(int $id): void
    {
        requireRole('agency');
        verifyCsrf();

        $userId = (int)currentUser()['id'];
        $interview = $this->verifyInterviewOwner($id, $userId);

        $overallRemarks = trim($_POST['overall_remarks'] ?? '');

        $this->db->execute(
            "UPDATE interviews SET status = 'completed', overall_remarks = ?, completed_at = NOW() WHERE id = ?",
            [$overallRemarks ?: null, $id]
        );

        // Notify applicant
        $applicant = $this->db->fetch("SELECT * FROM applicants WHERE id = ?", [$interview['applicant_id']]);
        if ($applicant && $applicant['user_id']) {
            $this->notifModel->create(
                (int)$applicant['user_id'],
                'interview_completed',
                'Interview Completed',
                "Your interview with {$interview['agency_name']} has been completed. You will be notified about the hiring decision.",
                APP_URL . '/applicant/interviews'
            );
        }

        auditLog('complete_interview', 'interviews', "Interview {$id} marked complete by agency user {$userId}.");
        flash('success', 'Interview marked as completed. Applicant has been notified.');
        redirect(APP_URL . '/agency/interviews/' . $id . '/evaluate');
    }

    // POST /agency/interviews/questions/{qid}/delete
    public function deleteQuestion(int $qid): void
    {
        requireRole('agency');
        verifyCsrf();

        $q = $this->db->fetch("SELECT * FROM interview_questions WHERE id = ?", [$qid]);
        if (!$q) {
            flash('error', 'Question not found.');
            redirect(APP_URL . '/agency/interviews');
        }
        $interviewId = $q['interview_id'];

        $this->db->execute("DELETE FROM interview_questions WHERE id = ?", [$qid]);
        flash('success', 'Question removed.');
        redirect(APP_URL . '/agency/interviews/' . $interviewId . '/evaluate');
    }

    // GET /applicant/interviews — applicant view of their interviews
    public function applicantInterviews(): void
    {
        requireRole('applicant');

        $userId    = (int)currentUser()['id'];
        $applicant = $this->db->fetch("SELECT * FROM applicants WHERE user_id = ?", [$userId]);

        $interviews = [];
        if ($applicant) {
            $interviews = $this->db->fetchAll(
                "SELECT iv.*, pa.agency_name, jfr.title AS fair_title,
                         COUNT(iq.id) AS question_count
                 FROM interviews iv
                 JOIN participating_agencies pa ON pa.id = iv.agency_id
                 JOIN job_fair_requests jfr ON jfr.id = pa.job_fair_request_id
                 LEFT JOIN interview_questions iq ON iq.interview_id = iv.id
                 WHERE iv.applicant_id = ?
                 GROUP BY iv.id
                 ORDER BY iv.created_at DESC",
                [$applicant['id']]
            );
        }

        $pageTitle = 'My Interviews';
        include VIEW_PATH . '/applicant/interviews.php';
    }

    // ── private helpers ───────────────────────────────────────────────────────

    private function verifyInterviewOwner(int $interviewId, int $userId): array
    {
        $interview = $this->db->fetch(
            "SELECT iv.*, pa.agency_name
             FROM interviews iv
             JOIN participating_agencies pa ON pa.id = iv.agency_id
             WHERE iv.id = ? AND pa.user_id = ?",
            [$interviewId, $userId]
        );
        if (!$interview) {
            flash('error', 'Interview not found or access denied.');
            redirect(APP_URL . '/agency/interviews');
        }
        return $interview;
    }
}
