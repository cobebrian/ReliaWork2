<?php
/**
 * ReliaWork2 ValidatingOfficerController
 * Reviews applicant applications + uploaded documents,
 * then approves/rejects/requests resubmission.
 */

class ValidatingOfficerController
{
    private Database $db;
    private NotificationModel $notifModel;

    public function __construct()
    {
        $this->db       = Database::getInstance();
        $this->notifModel = new NotificationModel();
    }

    // GET /validating-officer/dashboard
    public function dashboard(): void
    {
        requireRole('validating_officer');

        $stats = [
            'pending'   => (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM applications WHERE validation_status = 'pending_validation'"
            ),
            'approved'  => (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM applications WHERE validation_status = 'approved'"
            ),
            'rejected'  => (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM applications WHERE validation_status IN ('rejected','resubmit')"
            ),
        ];

        $pendingApps = $this->db->fetchAll(
            "SELECT app.id, app.applied_at, app.validation_status,
                    a.surname, a.firstname, a.middlename,
                    u.email,
                    jv.position, pa.agency_name,
                    jfr.title AS fair_title,
                    COUNT(d.id) AS doc_count
             FROM applications app
             JOIN applicants a ON a.id = app.applicant_id
             LEFT JOIN users u ON u.id = a.user_id
             JOIN job_vacancies jv ON jv.id = app.job_vacancy_id
             JOIN participating_agencies pa ON pa.id = jv.participating_agency_id
             LEFT JOIN job_fair_requests jfr ON jfr.id = pa.job_fair_request_id
             LEFT JOIN application_documents d ON d.application_id = app.id
             WHERE app.validation_status = 'pending_validation'
             GROUP BY app.id
             ORDER BY app.applied_at ASC"
        );

        $pageTitle = 'Validating Officer Dashboard';
        include VIEW_PATH . '/validating_officer/dashboard.php';
    }

    // GET /validating-officer/applicants (list all applications)
    public function applicants(): void
    {
        requireRole('validating_officer');

        $status = $_GET['status'] ?? 'pending_validation';
        $search = trim($_GET['search'] ?? '');

        $sql = "SELECT app.id, app.applied_at, app.validation_status, app.validator_remarks,
                       a.surname, a.firstname, u.email,
                       jv.position, pa.agency_name,
                       jfr.title AS fair_title,
                       COUNT(d.id) AS doc_count
                FROM applications app
                JOIN applicants a ON a.id = app.applicant_id
                LEFT JOIN users u ON u.id = a.user_id
                JOIN job_vacancies jv ON jv.id = app.job_vacancy_id
                JOIN participating_agencies pa ON pa.id = jv.participating_agency_id
                LEFT JOIN job_fair_requests jfr ON jfr.id = pa.job_fair_request_id
                LEFT JOIN application_documents d ON d.application_id = app.id
                WHERE 1=1";
        $params = [];

        if ($status && $status !== 'all') {
            $sql .= " AND app.validation_status = ?";
            $params[] = $status;
        }
        if ($search) {
            $sql .= " AND (a.surname LIKE ? OR a.firstname LIKE ? OR jv.position LIKE ? OR pa.agency_name LIKE ?)";
            $like = '%' . $search . '%';
            $params = array_merge($params, [$like, $like, $like, $like]);
        }
        $sql .= " GROUP BY app.id ORDER BY app.applied_at DESC";

        $applicants = $this->db->fetchAll($sql, $params);
        $pageTitle  = 'Application Review List';
        include VIEW_PATH . '/validating_officer/applicants.php';
    }

    // GET /validating-officer/applications/{id}/review
    public function review(int $id): void
    {
        requireRole('validating_officer');

        $application = $this->db->fetch(
            "SELECT app.*,
                    a.surname, a.firstname, a.middlename, a.date_of_birth,
                    a.cellphone, a.present_address, a.sex, a.civil_status,
                    a.gsis_sss_no, a.pag_ibig_no, a.philhealth_no,
                    a.educational_bg, a.work_experience, a.other_skills,
                    a.preferred_occupation, a.disability, a.employment_status,
                    u.email AS applicant_email,
                    jv.position, jv.company_name, jv.qualifications,
                    jv.available_slots, jv.company_location,
                    pa.agency_name, pa.email AS agency_email,
                    jfr.title AS fair_title, jfr.requested_date
             FROM applications app
             JOIN applicants a ON a.id = app.applicant_id
             LEFT JOIN users u ON u.id = a.user_id
             JOIN job_vacancies jv ON jv.id = app.job_vacancy_id
             JOIN participating_agencies pa ON pa.id = jv.participating_agency_id
             LEFT JOIN job_fair_requests jfr ON jfr.id = pa.job_fair_request_id
             WHERE app.id = ?",
            [$id]
        );
        if (!$application) {
            flash('error', 'Application not found.');
            redirect(APP_URL . '/validating-officer/applicants');
        }

        $documents = $this->db->fetchAll(
            "SELECT * FROM application_documents WHERE application_id = ? ORDER BY doc_type, uploaded_at",
            [$id]
        );

        $success = getFlash('success');
        $error   = getFlash('error');
        $pageTitle = 'Review Application: ' . strtoupper($application['surname']) . ', '
            . $application['firstname'] . ' — ' . $application['position'];
        include VIEW_PATH . '/validating_officer/review.php';
    }

    // POST /validating-officer/applications/{id}/validate
    public function validate(int $id): void
    {
        requireRole('validating_officer');
        verifyCsrf();

        $action  = $_POST['action'] ?? '';
        $remarks = trim($_POST['remarks'] ?? '');

        $statusMap = [
            'approve'  => 'approved',
            'reject'   => 'rejected',
            'resubmit' => 'resubmit',
        ];
        $newStatus = $statusMap[$action] ?? null;
        if (!$newStatus) {
            flash('error', 'Invalid action.');
            redirect(APP_URL . '/validating-officer/applications/' . $id . '/review');
        }

        $app = $this->db->fetch(
            "SELECT app.*, a.user_id, a.surname, a.firstname,
                    jv.position, pa.agency_name, pa.user_id AS agency_user_id,
                    jfr.title AS fair_title, pa.job_fair_request_id
             FROM applications app
             JOIN applicants a ON a.id = app.applicant_id
             JOIN job_vacancies jv ON jv.id = app.job_vacancy_id
             JOIN participating_agencies pa ON pa.id = jv.participating_agency_id
             LEFT JOIN job_fair_requests jfr ON jfr.id = pa.job_fair_request_id
             WHERE app.id = ?",
            [$id]
        );
        if (!$app) {
            flash('error', 'Application not found.');
            redirect(APP_URL . '/validating-officer/applicants');
        }

        $officerId = (int)currentUser()['id'];
        $this->db->execute(
            "UPDATE applications
             SET validation_status = ?, validated_by = ?, validated_at = NOW(),
                 validator_remarks = ?, updated_at = NOW()
             WHERE id = ?",
            [$newStatus, $officerId, $remarks ?: null, $id]
        );

        $name = strtoupper($app['surname']) . ', ' . $app['firstname'];

        // Notify applicant
        if ($app['user_id']) {
            $msgs = [
                'approved'  => "Your application for \"{$app['position']}\" at {$app['agency_name']} has been APPROVED! The company will schedule your interview.",
                'rejected'  => "Your application for \"{$app['position']}\" at {$app['agency_name']} was rejected." . ($remarks ? " Reason: {$remarks}" : ''),
                'resubmit'  => "Please resubmit your documents for \"{$app['position']}\" at {$app['agency_name']}." . ($remarks ? " Note: {$remarks}" : ''),
            ];
            $titles = [
                'approved' => 'Application Approved ✓',
                'rejected' => 'Application Rejected',
                'resubmit' => 'Resubmission Required',
            ];
            $this->notifModel->create(
                (int)$app['user_id'], 'validation_' . $newStatus,
                $titles[$newStatus], $msgs[$newStatus],
                APP_URL . '/applicant/my-applications'
            );
        }

        // If approved → notify the agency
        if ($newStatus === 'approved' && $app['agency_user_id']) {
            $this->notifModel->create(
                (int)$app['agency_user_id'],
                'applicant_ready_for_interview',
                'Applicant Ready for Interview',
                "Applicant {$name} has been validated and approved for \"{$app['position']}\". They are now in your Interview Queue.",
                APP_URL . '/agency/interviews'
            );
        }

        auditLog('validate_application', 'applications',
            "Validating officer {$officerId} set application {$id} to {$newStatus}.");
        flash('success', 'Application marked as ' . ucfirst($newStatus) . '.');
        redirect(APP_URL . '/validating-officer/applications/' . $id . '/review');
    }
}
