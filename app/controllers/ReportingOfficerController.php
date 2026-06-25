<?php
/**
 * ReliaWork2 ReportingOfficerController
 * Consolidated Job Fair reporting, statistics, and hiring outcomes.
 */

class ReportingOfficerController
{
    private Database $db;
    private NotificationModel $notifModel;

    public function __construct()
    {
        $this->db       = Database::getInstance();
        $this->notifModel = new NotificationModel();
    }

    // GET /reporting-officer/dashboard
    public function dashboard(): void
    {
        requireRole('reporting_officer');

        // Overall system stats
        $stats = [
            'total_job_fairs'    => (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM job_fair_requests WHERE status IN ('approved','confirmed')"
            ),
            'total_applicants'   => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM applicants"),
            'total_validated'    => (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM applicants WHERE validation_status = 'approved'"
            ),
            'total_interviewed'  => (int)$this->db->fetchColumn(
                "SELECT COUNT(DISTINCT applicant_id) FROM interviews WHERE status = 'completed'"
            ),
            'total_hired'        => (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM interviews WHERE status = 'completed' AND hiring_outcome = 'hired'"
            ),
            'total_not_hired'    => (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM interviews WHERE status = 'completed' AND hiring_outcome = 'not_hired'"
            ),
            'total_agencies'     => (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM participating_agencies WHERE status = 'confirmed'"
            ),
            'total_vacancies'    => (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM job_vacancies WHERE status = 'open'"
            ),
        ];

        // Employment rate
        $stats['employment_rate'] = $stats['total_interviewed'] > 0
            ? round(($stats['total_hired'] / $stats['total_interviewed']) * 100, 1)
            : 0;

        // Recent completed interviews
        $recentInterviews = $this->db->fetchAll(
            "SELECT iv.*, a.surname, a.firstname,
                    pa.agency_name, jfr.title AS fair_title
             FROM interviews iv
             JOIN applicants a ON a.id = iv.applicant_id
             JOIN participating_agencies pa ON pa.id = iv.agency_id
             JOIN job_fair_requests jfr ON jfr.id = pa.job_fair_request_id
             WHERE iv.status = 'completed'
             ORDER BY iv.completed_at DESC
             LIMIT 10"
        );

        // Job fairs summary list
        $jobFairs = $this->db->fetchAll(
            "SELECT jfr.id, jfr.title, jfr.requested_date, jfr.venue, jfr.status,
                    COUNT(DISTINCT pa.id) AS agency_count,
                    COUNT(DISTINCT jv.id) AS vacancy_count,
                    COUNT(DISTINCT iv.applicant_id) AS interviewed_count,
                    SUM(CASE WHEN iv.hiring_outcome = 'hired' THEN 1 ELSE 0 END) AS hired_count
             FROM job_fair_requests jfr
             LEFT JOIN participating_agencies pa ON pa.job_fair_request_id = jfr.id AND pa.status = 'confirmed'
             LEFT JOIN job_vacancies jv ON jv.participating_agency_id = pa.id
             LEFT JOIN interviews iv ON iv.agency_id = pa.id AND iv.status = 'completed'
             WHERE jfr.status IN ('approved','confirmed')
             GROUP BY jfr.id
             ORDER BY jfr.requested_date DESC"
        );

        // Unread notifications
        $notifications = $this->notifModel->getUnread((int)currentUser()['id']);
        $unreadCount   = count($notifications);

        $pageTitle = 'Reporting Officer Dashboard';
        include VIEW_PATH . '/reporting_officer/dashboard.php';
    }

    // GET /reporting-officer/job-fairs/{id}
    public function jobFairReport(int $fairId): void
    {
        requireRole('reporting_officer');

        $fair = $this->db->fetch(
            "SELECT * FROM job_fair_requests WHERE id = ?",
            [$fairId]
        );
        if (!$fair) {
            flash('error', 'Job fair not found.');
            redirect(APP_URL . '/reporting-officer/dashboard');
        }

        // Applicant participation
        $applicants = $this->db->fetchAll(
            "SELECT a.id, a.surname, a.firstname, a.middlename, a.cellphone,
                    a.preferred_occupation, a.validation_status,
                    u.email,
                    iv.id AS interview_id, iv.status AS interview_status,
                    iv.hiring_outcome, iv.overall_remarks, iv.completed_at,
                    pa.agency_name AS interviewed_by,
                    COUNT(DISTINCT iq.id) AS question_count,
                    SUM(CASE WHEN iq.answer_status = 'answered' THEN 1 ELSE 0 END) AS answered_count
             FROM applicants a
             LEFT JOIN users u ON u.id = a.user_id
             LEFT JOIN interviews iv ON iv.applicant_id = a.id
             LEFT JOIN participating_agencies pa ON pa.id = iv.agency_id
             LEFT JOIN interview_questions iq ON iq.interview_id = iv.id
             WHERE EXISTS (
                 SELECT 1 FROM job_fair_registrations jfr2
                 JOIN job_fair_posts p ON p.id = jfr2.job_fair_post_id
                 WHERE jfr2.applicant_id = a.id AND p.job_fair_request_id = ?
             ) OR EXISTS (
                 SELECT 1 FROM interviews iv2
                 JOIN participating_agencies pa2 ON pa2.id = iv2.agency_id
                 WHERE iv2.applicant_id = a.id AND pa2.job_fair_request_id = ?
             )
             GROUP BY a.id, iv.id
             ORDER BY a.surname, a.firstname",
            [$fairId, $fairId]
        );

        // Agency participation
        $agencies = $this->db->fetchAll(
            "SELECT pa.id, pa.agency_name, pa.email, pa.status,
                    COUNT(DISTINCT jv.id) AS vacancy_count,
                    SUM(jv.available_slots) AS total_slots,
                    COUNT(DISTINCT iv.applicant_id) AS interviewed_count,
                    SUM(CASE WHEN iv.hiring_outcome = 'hired' THEN 1 ELSE 0 END) AS hired_count,
                    SUM(CASE WHEN iv.hiring_outcome = 'not_hired' THEN 1 ELSE 0 END) AS not_hired_count
             FROM participating_agencies pa
             LEFT JOIN job_vacancies jv ON jv.participating_agency_id = pa.id
             LEFT JOIN interviews iv ON iv.agency_id = pa.id AND iv.status = 'completed'
             WHERE pa.job_fair_request_id = ?
             GROUP BY pa.id
             ORDER BY pa.agency_name",
            [$fairId]
        );

        // Summary stats for this fair
        $summary = [
            'total_applicants'  => count($applicants),
            'total_validated'   => count(array_filter($applicants, fn($a) => $a['validation_status'] === 'approved')),
            'total_interviewed' => count(array_filter($applicants, fn($a) => !empty($a['interview_id']))),
            'total_hired'       => count(array_filter($applicants, fn($a) => $a['hiring_outcome'] === 'hired')),
            'total_not_hired'   => count(array_filter($applicants, fn($a) => $a['hiring_outcome'] === 'not_hired')),
            'total_agencies'    => count($agencies),
            'total_vacancies'   => array_sum(array_column($agencies, 'vacancy_count')),
        ];
        $summary['employment_rate'] = $summary['total_interviewed'] > 0
            ? round(($summary['total_hired'] / $summary['total_interviewed']) * 100, 1)
            : 0;

        $pageTitle = 'Job Fair Report — ' . $fair['title'];
        include VIEW_PATH . '/reporting_officer/job_fair_report.php';
    }

    // GET /reporting-officer/interviews
    public function interviews(): void
    {
        requireRole('reporting_officer');

        $status   = $_GET['outcome'] ?? 'all';
        $fairId   = (int)($_GET['fair_id'] ?? 0);
        $search   = trim($_GET['search'] ?? '');

        $sql = "SELECT iv.*, a.surname, a.firstname, a.middlename,
                       a.validation_status, u.email,
                       pa.agency_name, jfr.title AS fair_title, jfr.id AS fair_id,
                       COUNT(iq.id) AS question_count,
                       SUM(CASE WHEN iq.answer_status = 'answered' THEN 1 ELSE 0 END) AS answered_count
                FROM interviews iv
                JOIN applicants a ON a.id = iv.applicant_id
                LEFT JOIN users u ON u.id = a.user_id
                JOIN participating_agencies pa ON pa.id = iv.agency_id
                JOIN job_fair_requests jfr ON jfr.id = pa.job_fair_request_id
                LEFT JOIN interview_questions iq ON iq.interview_id = iv.id
                WHERE 1=1";
        $params = [];

        if ($status !== 'all') {
            $sql .= " AND iv.hiring_outcome = ?";
            $params[] = $status;
        }
        if ($fairId) {
            $sql .= " AND jfr.id = ?";
            $params[] = $fairId;
        }
        if ($search) {
            $sql .= " AND (a.surname LIKE ? OR a.firstname LIKE ? OR pa.agency_name LIKE ?)";
            $like = '%' . $search . '%';
            $params = array_merge($params, [$like, $like, $like]);
        }
        $sql .= " GROUP BY iv.id ORDER BY iv.completed_at DESC, iv.created_at DESC";

        $interviews = $this->db->fetchAll($sql, $params);
        $jobFairs   = $this->db->fetchAll(
            "SELECT id, title FROM job_fair_requests WHERE status IN ('approved','confirmed') ORDER BY requested_date DESC"
        );

        $pageTitle = 'All Interviews';
        include VIEW_PATH . '/reporting_officer/interviews.php';
    }

    // GET /reporting-officer/interview/{id}
    public function interviewDetail(int $id): void
    {
        requireRole('reporting_officer');

        $interview = $this->db->fetch(
            "SELECT iv.*, a.surname, a.firstname, a.middlename, a.date_of_birth,
                    a.present_address, a.cellphone, a.preferred_occupation,
                    a.educational_bg, a.work_experience, a.other_skills,
                    a.validation_status, a.gsis_sss_no, a.pag_ibig_no, a.philhealth_no,
                    u.email AS applicant_email,
                    pa.agency_name, pa.email AS agency_email,
                    jfr.title AS fair_title, jfr.requested_date, jfr.venue
             FROM interviews iv
             JOIN applicants a ON a.id = iv.applicant_id
             LEFT JOIN users u ON u.id = a.user_id
             JOIN participating_agencies pa ON pa.id = iv.agency_id
             JOIN job_fair_requests jfr ON jfr.id = pa.job_fair_request_id
             WHERE iv.id = ?",
            [$id]
        );
        if (!$interview) {
            flash('error', 'Interview record not found.');
            redirect(APP_URL . '/reporting-officer/interviews');
        }

        $questions = $this->db->fetchAll(
            "SELECT * FROM interview_questions WHERE interview_id = ? ORDER BY sort_order, id",
            [$id]
        );

        $documents = $this->db->fetchAll(
            "SELECT * FROM applicant_documents WHERE applicant_id = ? ORDER BY doc_type",
            [$interview['applicant_id']]
        );

        $pageTitle = 'Interview Detail';
        include VIEW_PATH . '/reporting_officer/interview_detail.php';
    }

    // POST /reporting-officer/interview/{id}/update-outcome
    public function updateOutcome(int $id): void
    {
        requireRole('reporting_officer');
        verifyCsrf();

        $outcome  = $_POST['hiring_outcome'] ?? 'pending';
        $remarks  = trim($_POST['hiring_remarks'] ?? '');
        $valid    = ['pending', 'hired', 'not_hired', 'for_consideration'];
        if (!in_array($outcome, $valid)) $outcome = 'pending';

        $this->db->execute(
            "UPDATE interviews SET hiring_outcome = ?, hiring_remarks = ?,
             reported_at = NOW(), reported_by = ? WHERE id = ?",
            [$outcome, $remarks ?: null, (int)currentUser()['id'], $id]
        );

        // Notify applicant of updated outcome
        $interview = $this->db->fetch(
            "SELECT iv.applicant_id, a.user_id, a.surname, a.firstname, pa.agency_name
             FROM interviews iv
             JOIN applicants a ON a.id = iv.applicant_id
             JOIN participating_agencies pa ON pa.id = iv.agency_id
             WHERE iv.id = ?",
            [$id]
        );
        if ($interview && $interview['user_id']) {
            $msg = match($outcome) {
                'hired'            => 'Great news! Your hiring status has been updated to HIRED.',
                'not_hired'        => 'Your hiring outcome has been updated. You were not selected at this time.',
                'for_consideration'=> 'You are being considered for the position.',
                default            => 'Your interview outcome has been updated.',
            };
            $this->notifModel->create(
                (int)$interview['user_id'],
                'outcome_updated',
                'Hiring Outcome Updated',
                $msg . ($remarks ? " Note: {$remarks}" : ''),
                APP_URL . '/applicant/interviews'
            );
        }

        auditLog('update_outcome', 'interviews', "Reporting Officer updated interview {$id} outcome to {$outcome}.");
        flash('success', 'Hiring outcome updated successfully.');
        redirect(APP_URL . '/reporting-officer/interview/' . $id);
    }

    // GET /reporting-officer/generate-report/{fairId}
    public function generateReport(int $fairId): void
    {
        requireRole('reporting_officer');

        $fair = $this->db->fetch("SELECT * FROM job_fair_requests WHERE id = ?", [$fairId]);
        if (!$fair) {
            flash('error', 'Job fair not found.');
            redirect(APP_URL . '/reporting-officer/dashboard');
        }

        // Compute stats
        $totalApplicants = (int)$this->db->fetchColumn(
            "SELECT COUNT(DISTINCT a.id)
             FROM applicants a
             WHERE EXISTS (
                 SELECT 1 FROM interviews iv
                 JOIN participating_agencies pa ON pa.id = iv.agency_id
                 WHERE iv.applicant_id = a.id AND pa.job_fair_request_id = ?
             ) OR EXISTS (
                 SELECT 1 FROM job_fair_registrations jfr
                 JOIN job_fair_posts p ON p.id = jfr.job_fair_post_id
                 WHERE jfr.applicant_id = a.id AND p.job_fair_request_id = ?
             )",
            [$fairId, $fairId]
        );
        $totalValidated   = (int)$this->db->fetchColumn(
            "SELECT COUNT(DISTINCT a.id) FROM applicants a
             JOIN interviews iv ON iv.applicant_id = a.id
             JOIN participating_agencies pa ON pa.id = iv.agency_id
             WHERE pa.job_fair_request_id = ? AND a.validation_status = 'approved'",
            [$fairId]
        );
        $totalInterviewed = (int)$this->db->fetchColumn(
            "SELECT COUNT(DISTINCT iv.applicant_id) FROM interviews iv
             JOIN participating_agencies pa ON pa.id = iv.agency_id
             WHERE pa.job_fair_request_id = ? AND iv.status = 'completed'",
            [$fairId]
        );
        $totalHired       = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM interviews iv
             JOIN participating_agencies pa ON pa.id = iv.agency_id
             WHERE pa.job_fair_request_id = ? AND iv.hiring_outcome = 'hired'",
            [$fairId]
        );
        $totalNotHired    = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM interviews iv
             JOIN participating_agencies pa ON pa.id = iv.agency_id
             WHERE pa.job_fair_request_id = ? AND iv.hiring_outcome = 'not_hired'",
            [$fairId]
        );
        $totalAgencies    = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM participating_agencies WHERE job_fair_request_id = ? AND status = 'confirmed'",
            [$fairId]
        );
        $totalVacancies   = (int)$this->db->fetchColumn(
            "SELECT COUNT(jv.id) FROM job_vacancies jv
             JOIN participating_agencies pa ON pa.id = jv.participating_agency_id
             WHERE pa.job_fair_request_id = ?",
            [$fairId]
        );
        $employmentRate = $totalInterviewed > 0
            ? round(($totalHired / $totalInterviewed) * 100, 2) : 0;

        // Upsert report record
        $existing = $this->db->fetchColumn(
            "SELECT id FROM job_fair_reports WHERE job_fair_request_id = ?",
            [$fairId]
        );
        if ($existing) {
            $this->db->execute(
                "UPDATE job_fair_reports
                 SET total_applicants=?, total_validated=?, total_interviewed=?,
                     total_hired=?, total_not_hired=?, total_agencies=?,
                     total_vacancies=?, employment_rate=?, generated_by=?, generated_at=NOW()
                 WHERE id=?",
                [$totalApplicants, $totalValidated, $totalInterviewed,
                 $totalHired, $totalNotHired, $totalAgencies,
                 $totalVacancies, $employmentRate,
                 (int)currentUser()['id'], $existing]
            );
        } else {
            $this->db->execute(
                "INSERT INTO job_fair_reports
                 (job_fair_request_id, generated_by, total_applicants, total_validated,
                  total_interviewed, total_hired, total_not_hired, total_agencies,
                  total_vacancies, employment_rate, generated_at)
                 VALUES (?,?,?,?,?,?,?,?,?,?,NOW())",
                [$fairId, (int)currentUser()['id'],
                 $totalApplicants, $totalValidated, $totalInterviewed,
                 $totalHired, $totalNotHired, $totalAgencies,
                 $totalVacancies, $employmentRate]
            );
        }

        auditLog('generate_report', 'job_fair_reports', "Report generated for job fair ID {$fairId}.");
        flash('success', 'Report generated successfully.');
        redirect(APP_URL . '/reporting-officer/job-fairs/' . $fairId);
    }
}
