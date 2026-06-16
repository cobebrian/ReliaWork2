<?php
/**
 * ReliaWork2 AgencyController
 * Full workflow:
 *  1. Agency registers → admin approves → agency sets up profile (company name, location)
 *  2. Supervising Labor sees agency in Step 2 list and invites them
 *  3. Agency receives notification → accepts or rejects on dashboard
 *  4. If accepted → agency submits vacancies
 *  5. Supervising Labor is notified → reviews vacancies + adds remarks
 */

class AgencyController
{
    private AgencyModel   $agencyModel;
    private VacancyModel  $vacancyModel;
    private NotificationModel $notifModel;
    private UserModel     $userModel;

    public function __construct()
    {
        $this->agencyModel = new AgencyModel();
        $this->vacancyModel = new VacancyModel();
        $this->notifModel   = new NotificationModel();
        $this->userModel    = new UserModel();
    }

    // GET /agency/dashboard
    public function dashboard(): void
    {
        requireRole('agency');
        $userId = (int)currentUser()['id'];
        $db     = Database::getInstance();

        // Check if profile is set up
        $user = $this->userModel->find($userId);
        if (empty($user['agency_name']) || empty($user['agency_location'])) {
            redirect(APP_URL . '/agency/setup');
        }

        // Invitations for this user
        $myInvitations = $db->fetchAll(
            "SELECT pa.*, jfr.title AS job_fair_title, jfr.requested_date
             FROM participating_agencies pa
             LEFT JOIN job_fair_requests jfr ON jfr.id = pa.job_fair_request_id
             WHERE pa.user_id = ?
             ORDER BY pa.invited_at DESC",
            [$userId]
        );

        $agencyIds = array_column($myInvitations, 'id');
        $vacancyCount = 0;
        if (!empty($agencyIds)) {
            $ph = implode(',', array_fill(0, count($agencyIds), '?'));
            $vacancyCount = (int)$db->fetchColumn(
                "SELECT COUNT(*) FROM job_vacancies WHERE participating_agency_id IN ($ph)",
                $agencyIds
            );
        }

        // Unread notifications
        $notifications = $this->notifModel->getUnread($userId);

        $stats = [
            'total_invitations' => count($myInvitations),
            'pending'    => count(array_filter($myInvitations, fn($a) => $a['status'] === 'invited')),
            'confirmed'  => count(array_filter($myInvitations, fn($a) => $a['status'] === 'confirmed')),
            'declined'   => count(array_filter($myInvitations, fn($a) => $a['status'] === 'declined')),
            'vacancy_count' => $vacancyCount,
        ];

        $pageTitle = 'Agency Dashboard';
        include VIEW_PATH . '/agency/dashboard.php';
    }

    // GET /agency/setup
    public function showSetup(): void
    {
        requireRole('agency');
        $user    = $this->userModel->find((int)currentUser()['id']);
        $error   = getFlash('error');
        $success = getFlash('success');
        $pageTitle = 'Set Up Your Agency Profile';
        include VIEW_PATH . '/agency/setup.php';
    }

    // POST /agency/setup
    public function saveSetup(): void
    {
        requireRole('agency');
        verifyCsrf();

        $agencyName     = trim($_POST['agency_name']     ?? '');
        $agencyLocation = trim($_POST['agency_location'] ?? '');

        if (empty($agencyName) || empty($agencyLocation)) {
            flash('error', 'Agency name and location are required.');
            redirect(APP_URL . '/agency/setup');
        }

        $userId = (int)currentUser()['id'];
        $db     = Database::getInstance();
        $db->execute(
            "UPDATE users SET agency_name = ?, agency_location = ?, profile_setup = 1 WHERE id = ?",
            [$agencyName, $agencyLocation, $userId]
        );

        // Also update session
        $_SESSION['user']['agency_name']     = $agencyName;
        $_SESSION['user']['agency_location'] = $agencyLocation;

        auditLog('agency_setup', 'agency', "Agency profile set up: {$agencyName}");
        flash('success', 'Agency profile saved. You can now receive invitations.');
        redirect(APP_URL . '/agency/dashboard');
    }

    // POST /agency/confirm/{id}  — Accept or Decline invitation
    public function confirmParticipation(int $id): void
    {
        requireRole('agency');
        verifyCsrf();

        $invitation = $this->agencyModel->find($id);
        if (!$invitation || (int)$invitation['user_id'] !== (int)currentUser()['id']) {
            flash('error', 'Invitation not found.');
            redirect(APP_URL . '/agency/dashboard');
        }

        $action = $_POST['action'] ?? 'confirm';
        $status = $action === 'decline' ? 'declined' : 'confirmed';

        $this->agencyModel->update($id, [
            'status'       => $status,
            'responded_at' => date('Y-m-d H:i:s'),
        ]);

        // Notify all supervising_labor users
        $db          = Database::getInstance();
        $supervisors = $db->fetchAll(
            "SELECT id FROM users WHERE role = 'supervising_labor' AND status = 'approved'"
        );
        $user       = $this->userModel->find((int)currentUser()['id']);
        $agencyName = $user['agency_name'] ?? currentUser()['name'];
        $fairTitle  = $invitation['job_fair_title'] ?? 'the job fair';

        foreach ($supervisors as $sup) {
            $this->notifModel->create(
                $sup['id'],
                $status === 'confirmed' ? 'agency_accepted' : 'agency_declined',
                $status === 'confirmed'
                    ? "Agency Accepted Invitation"
                    : "Agency Declined Invitation",
                "{$agencyName} has {$status} the invitation to \"{$fairTitle}\".",
                APP_URL . '/supervising-labor/agencies?request_id=' . $invitation['job_fair_request_id']
            );
        }

        auditLog('agency_response', 'agencies', "Agency user " . currentUser()['id'] . " {$status} invitation ID {$id}.");
        flash('success', 'Response sent: ' . ucfirst($status));
        redirect(APP_URL . '/agency/dashboard');
    }

    // GET /agency/vacancies
    public function vacancies(): void
    {
        requireRole('agency');
        $userId = (int)currentUser()['id'];
        $db     = Database::getInstance();

        // Check profile setup
        $user = $this->userModel->find($userId);
        if (empty($user['agency_name'])) {
            redirect(APP_URL . '/agency/setup');
        }

        // Only confirmed invitations can post vacancies
        $myAgencies = $db->fetchAll(
            "SELECT pa.*, jfr.title AS job_fair_title FROM participating_agencies pa
             LEFT JOIN job_fair_requests jfr ON jfr.id = pa.job_fair_request_id
             WHERE pa.user_id = ? AND pa.status = 'confirmed'
             ORDER BY pa.invited_at DESC",
            [$userId]
        );

        $agencyIds = array_column($myAgencies, 'id');
        $vacancies = [];
        if (!empty($agencyIds)) {
            $ph = implode(',', array_fill(0, count($agencyIds), '?'));
            $vacancies = $db->fetchAll(
                "SELECT jv.*, pa.agency_name FROM job_vacancies jv
                 LEFT JOIN participating_agencies pa ON pa.id = jv.participating_agency_id
                 WHERE jv.participating_agency_id IN ($ph)
                 ORDER BY jv.created_at DESC",
                $agencyIds
            );
        }

        // Pre-fill company info from profile
        $companyName     = $user['agency_name']     ?? '';
        $companyLocation = $user['agency_location'] ?? '';

        $pageTitle = 'My Vacancies';
        $success   = getFlash('success');
        $error     = getFlash('error');
        include VIEW_PATH . '/agency/vacancies.php';
    }

    // POST /agency/vacancies/store
    public function storeVacancy(): void
    {
        requireRole('agency');
        verifyCsrf();

        $agencyId       = (int)($_POST['participating_agency_id'] ?? 0);
        $companyName    = trim($_POST['company_name']    ?? '');
        $position       = trim($_POST['position']        ?? '');
        $slots          = (int)($_POST['available_slots'] ?? 1);
        $location       = trim($_POST['company_location'] ?? '');
        $mobile         = trim($_POST['mobile_number']   ?? '');
        $gmail          = trim($_POST['gmail_address']   ?? '');
        $qualifications = trim($_POST['qualifications']  ?? '');

        if (!$agencyId || empty($companyName) || empty($position)) {
            flash('error', 'Job fair, company name, and position are required.');
            redirect(APP_URL . '/agency/vacancies');
        }

        // Verify this invitation belongs to the current user
        $invitation = $this->agencyModel->find($agencyId);
        if (!$invitation || (int)$invitation['user_id'] !== (int)currentUser()['id']) {
            flash('error', 'Not authorized.');
            redirect(APP_URL . '/agency/vacancies');
        }
        if ($invitation['status'] !== 'confirmed') {
            flash('error', 'You must accept the invitation before posting vacancies.');
            redirect(APP_URL . '/agency/vacancies');
        }

        $this->vacancyModel->create([
            'participating_agency_id' => $agencyId,
            'company_name'            => $companyName,
            'company_location'        => $location ?: null,
            'mobile_number'           => $mobile   ?: null,
            'gmail_address'           => $gmail    ?: null,
            'position'                => $position,
            'available_slots'         => max(1, $slots),
            'qualifications'          => $qualifications ?: null,
            'submitted_by'            => currentUser()['id'],
            'status'                  => 'open',
        ]);

        // Notify supervising_labor
        $db          = Database::getInstance();
        $supervisors = $db->fetchAll(
            "SELECT id FROM users WHERE role = 'supervising_labor' AND status = 'approved'"
        );
        $agencyName = $invitation['agency_name'];
        foreach ($supervisors as $sup) {
            $this->notifModel->create(
                $sup['id'],
                'new_vacancy',
                'New Vacancy Submitted',
                "{$agencyName} submitted: {$position} at {$companyName} ({$slots} slot/s).",
                APP_URL . '/supervising-labor/vacancies/review'
            );
        }

        auditLog('submit_vacancy', 'vacancies', "Agency submitted '{$position}' at '{$companyName}'.");
        flash('success', "Vacancy '{$position}' submitted. Supervising Labor has been notified.");
        redirect(APP_URL . '/agency/vacancies');
    }
}
