<?php
/**
 * ReliaWork2 AgencyController
 */

class AgencyController
{
    private AgencyModel $agencyModel;
    private VacancyModel $vacancyModel;
    private NotificationModel $notifModel;

    public function __construct()
    {
        $this->agencyModel  = new AgencyModel();
        $this->vacancyModel = new VacancyModel();
        $this->notifModel   = new NotificationModel();
    }

    // GET /agency/dashboard
    public function dashboard(): void
    {
        requireRole('agency');
        $userId    = currentUser()['id'];
        $db        = Database::getInstance();
        $userEmail = currentUser()['email'];
        $myAgencies = $db->fetchAll(
            "SELECT pa.* FROM participating_agencies pa WHERE pa.email = ?",
            [$userEmail]
        );
        $agencyIds    = array_column($myAgencies, 'id');
        $vacancyCount = 0;
        if (!empty($agencyIds)) {
            $ph = implode(',', array_fill(0, count($agencyIds), '?'));
            $vacancyCount = (int)$db->fetchColumn(
                "SELECT COUNT(*) FROM job_vacancies WHERE participating_agency_id IN ({$ph})",
                $agencyIds
            );
        }
        $stats = [
            'my_agencies'   => count($myAgencies),
            'vacancy_count' => $vacancyCount,
            'confirmed'     => count(array_filter($myAgencies, fn($a) => $a['status'] === 'confirmed')),
        ];
        $pageTitle = 'Agency Dashboard';
        include VIEW_PATH . '/agency/dashboard.php';
    }

    // POST /agency/confirm/{id}
    public function confirmParticipation(int $id): void
    {
        requireRole('agency');
        verifyCsrf();
        $agency = $this->agencyModel->find($id);
        if (!$agency) {
            flash('error', 'Agency record not found.');
            redirect(APP_URL . '/agency/dashboard');
        }
        $action = $_POST['action'] ?? 'confirm';
        $status = $action === 'decline' ? 'declined' : 'confirmed';
        $this->agencyModel->update($id, ['status' => $status, 'responded_at' => date('Y-m-d H:i:s')]);
        auditLog('agency_response', 'agencies', "Agency ID {$id} {$status} participation.");
        flash('success', 'Participation status updated to: ' . ucfirst($status));
        redirect(APP_URL . '/agency/dashboard');
    }

    // GET /agency/vacancies
    public function vacancies(): void
    {
        requireRole('agency');
        $userEmail  = currentUser()['email'];
        $db         = Database::getInstance();
        $myAgencies = $db->fetchAll(
            "SELECT pa.*, jfr.title AS job_fair_title FROM participating_agencies pa
             LEFT JOIN job_fair_requests jfr ON jfr.id = pa.job_fair_request_id
             WHERE pa.email = ?",
            [$userEmail]
        );
        $agencyIds = array_column($myAgencies, 'id');
        $vacancies = [];
        if (!empty($agencyIds)) {
            $ph = implode(',', array_fill(0, count($agencyIds), '?'));
            $vacancies = $db->fetchAll(
                "SELECT jv.*, pa.agency_name FROM job_vacancies jv
                 LEFT JOIN participating_agencies pa ON pa.id = jv.participating_agency_id
                 WHERE jv.participating_agency_id IN ({$ph})
                 ORDER BY jv.created_at DESC",
                $agencyIds
            );
        }
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
        $companyName    = trim($_POST['company_name'] ?? '');
        $position       = trim($_POST['position'] ?? '');
        $slots          = (int)($_POST['available_slots'] ?? 1);
        $location       = trim($_POST['company_location'] ?? '');
        $mobile         = trim($_POST['mobile_number'] ?? '');
        $gmail          = trim($_POST['gmail_address'] ?? '');
        $qualifications = trim($_POST['qualifications'] ?? '');

        if (!$agencyId || empty($companyName) || empty($position)) {
            flash('error', 'Job fair, company name, and position are required.');
            redirect(APP_URL . '/agency/vacancies');
        }

        $agency = $this->agencyModel->find($agencyId);
        if (!$agency || $agency['email'] !== currentUser()['email']) {
            flash('error', 'You are not authorized to post vacancies for this agency.');
            redirect(APP_URL . '/agency/vacancies');
        }

        $this->vacancyModel->create([
            'participating_agency_id' => $agencyId,
            'company_name'            => $companyName,
            'company_location'        => $location ?: null,
            'mobile_number'           => $mobile ?: null,
            'gmail_address'           => $gmail ?: null,
            'position'                => $position,
            'available_slots'         => max(1, $slots),
            'qualifications'          => $qualifications ?: null,
            'submitted_by'            => currentUser()['id'],
            'status'                  => 'open',
        ]);

        // Notify all supervising_labor users
        $db = Database::getInstance();
        $supervisors = $db->fetchAll(
            "SELECT id FROM users WHERE role = 'supervising_labor' AND status = 'approved'"
        );
        foreach ($supervisors as $sup) {
            $this->notifModel->create(
                $sup['id'],
                'new_vacancy',
                'New Vacancy Submitted',
                "{$agency['agency_name']} submitted: {$position} at {$companyName} ({$slots} slot/s).",
                APP_URL . '/supervising-labor/vacancies/review'
            );
        }

        auditLog('create_vacancy', 'vacancies', "Agency submitted vacancy '{$position}' for '{$companyName}'.");
        flash('success', "Vacancy for '{$position}' submitted. Supervising Labor has been notified.");
        redirect(APP_URL . '/agency/vacancies');
    }
}
