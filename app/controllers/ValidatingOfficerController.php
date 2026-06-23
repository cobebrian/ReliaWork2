<?php
/**
 * ReliaWork2 ValidatingOfficerController
 * Reviews applicant profiles and uploaded documents,
 * then approves/rejects/requests resubmission.
 */

class ValidatingOfficerController
{
    private Database $db;
    private NotificationModel $notifModel;

    public function __construct()
    {
        $this->db         = Database::getInstance();
        $this->notifModel = new NotificationModel();
    }

    // GET /validating-officer/dashboard
    public function dashboard(): void
    {
        requireRole('validating_officer');

        $stats = [
            'pending'   => (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM applicants WHERE validation_status = 'pending'"
            ),
            'approved'  => (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM applicants WHERE validation_status = 'approved'"
            ),
            'rejected'  => (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM applicants WHERE validation_status IN ('rejected','resubmit')"
            ),
        ];

        $pendingApplicants = $this->db->fetchAll(
            "SELECT a.*, u.email,
                    COUNT(d.id) AS doc_count
             FROM applicants a
             LEFT JOIN users u ON u.id = a.user_id
             LEFT JOIN applicant_documents d ON d.applicant_id = a.id
             WHERE a.validation_status = 'pending'
             GROUP BY a.id
             ORDER BY a.updated_at ASC"
        );

        $pageTitle = 'Validating Officer Dashboard';
        include VIEW_PATH . '/validating_officer/dashboard.php';
    }

    // GET /validating-officer/applicants
    public function applicants(): void
    {
        requireRole('validating_officer');

        $status = $_GET['status'] ?? 'pending';
        $search = trim($_GET['search'] ?? '');

        $sql = "SELECT a.*, u.email, COUNT(d.id) AS doc_count
                FROM applicants a
                LEFT JOIN users u ON u.id = a.user_id
                LEFT JOIN applicant_documents d ON d.applicant_id = a.id
                WHERE 1=1";
        $params = [];

        if ($status && $status !== 'all') {
            $sql .= " AND a.validation_status = ?";
            $params[] = $status;
        }
        if ($search) {
            $sql .= " AND (a.surname LIKE ? OR a.firstname LIKE ? OR u.email LIKE ?)";
            $like = '%' . $search . '%';
            $params = array_merge($params, [$like, $like, $like]);
        }
        $sql .= " GROUP BY a.id ORDER BY a.updated_at DESC";

        $applicants = $this->db->fetchAll($sql, $params);
        $pageTitle  = 'Applicant List';
        include VIEW_PATH . '/validating_officer/applicants.php';
    }

    // GET /validating-officer/applicants/{id}/review
    public function review(int $id): void
    {
        requireRole('validating_officer');

        $applicant = $this->db->fetch(
            "SELECT a.*, u.email
             FROM applicants a
             LEFT JOIN users u ON u.id = a.user_id
             WHERE a.id = ?",
            [$id]
        );
        if (!$applicant) {
            flash('error', 'Applicant not found.');
            redirect(APP_URL . '/validating-officer/applicants');
        }

        $documents = $this->db->fetchAll(
            "SELECT * FROM applicant_documents WHERE applicant_id = ? ORDER BY doc_type, uploaded_at",
            [$id]
        );

        $success = getFlash('success');
        $error   = getFlash('error');
        $pageTitle = 'Review Applicant: ' . strtoupper($applicant['surname']) . ', ' . $applicant['firstname'];
        include VIEW_PATH . '/validating_officer/review.php';
    }

    // POST /validating-officer/applicants/{id}/validate
    public function validate(int $id): void
    {
        requireRole('validating_officer');
        verifyCsrf();

        $action  = $_POST['action'] ?? '';   // approve | reject | resubmit
        $remarks = trim($_POST['remarks'] ?? '');

        $statusMap = [
            'approve'   => 'approved',
            'reject'    => 'rejected',
            'resubmit'  => 'resubmit',
        ];
        $newStatus = $statusMap[$action] ?? null;
        if (!$newStatus) {
            flash('error', 'Invalid action.');
            redirect(APP_URL . '/validating-officer/applicants/' . $id . '/review');
        }

        $applicant = $this->db->fetch(
            "SELECT a.*, u.id AS uid FROM applicants a LEFT JOIN users u ON u.id = a.user_id WHERE a.id = ?",
            [$id]
        );
        if (!$applicant) {
            flash('error', 'Applicant not found.');
            redirect(APP_URL . '/validating-officer/applicants');
        }

        $officerId = (int)currentUser()['id'];
        $this->db->execute(
            "UPDATE applicants
             SET validation_status = ?, validated_by = ?, validated_at = NOW(), validator_remarks = ?
             WHERE id = ?",
            [$newStatus, $officerId, $remarks ?: null, $id]
        );

        // Notify the applicant
        $uid = (int)$applicant['uid'];
        if ($uid) {
            $msgMap = [
                'approved'  => 'Your documents have been approved! You are now eligible for agency interviews.',
                'rejected'  => 'Your documents were rejected.' . ($remarks ? " Reason: {$remarks}" : ''),
                'resubmit'  => 'Please resubmit your requirements.' . ($remarks ? " Note: {$remarks}" : ''),
            ];
            $titleMap = [
                'approved' => 'Documents Approved ✓',
                'rejected' => 'Documents Rejected',
                'resubmit' => 'Resubmission Required',
            ];
            $this->notifModel->create(
                $uid,
                'validation_' . $newStatus,
                $titleMap[$newStatus],
                $msgMap[$newStatus],
                APP_URL . '/applicant/requirements'
            );
        }

        // If approved, notify all confirmed agencies for active job fairs
        if ($newStatus === 'approved') {
            $name = strtoupper($applicant['surname']) . ', ' . $applicant['firstname'];
            $agencies = $this->db->fetchAll(
                "SELECT DISTINCT u.id AS uid
                 FROM participating_agencies pa
                 JOIN users u ON u.id = pa.user_id
                 JOIN job_fair_requests jfr ON jfr.id = pa.job_fair_request_id
                 WHERE pa.status = 'confirmed' AND jfr.status = 'approved' AND jfr.requested_date >= CURDATE()"
            );
            foreach ($agencies as $ag) {
                $this->notifModel->create(
                    (int)$ag['uid'],
                    'applicant_validated',
                    'Applicant Validated',
                    "Applicant {$name} has been validated and is eligible for interview.",
                    APP_URL . '/agency/interviews'
                );
            }
        }

        auditLog('validate_applicant', 'applicants', "Validating officer {$officerId} set applicant {$id} to {$newStatus}.");
        flash('success', 'Applicant marked as ' . ucfirst($newStatus) . '.');
        redirect(APP_URL . '/validating-officer/applicants/' . $id . '/review');
    }
}
