<?php
/**
 * ReliaWork2 SupervisingLaborController
 */

class SupervisingLaborController
{
    private ScheduleModel $scheduleModel;
    private JobFairRequestModel $requestModel;
    private AgencyModel $agencyModel;
    private CompanyModel $companyModel;
    private VacancyModel $vacancyModel;
    private ApplicantModel $applicantModel;
    private JobFairPostModel $postModel;
    private UserModel $userModel;
    private NotificationModel $notificationModel;

    public function __construct()
    {
        $this->scheduleModel  = new ScheduleModel();
        $this->requestModel   = new JobFairRequestModel();
        $this->agencyModel    = new AgencyModel();
        $this->companyModel   = new CompanyModel();
        $this->vacancyModel   = new VacancyModel();
        $this->applicantModel = new ApplicantModel();
        $this->postModel      = new JobFairPostModel();
        $this->userModel      = new UserModel();
        $this->notificationModel = new NotificationModel();
    }

    // GET /supervising-labor/dashboard
    public function dashboard(): void
    {
        requireRole('supervising_labor');

        $stats = [
            'pending_requests' => $this->requestModel->countByStatus('pending'),
            'approved_fairs'   => $this->requestModel->countApproved(),
            'total_agencies'   => $this->agencyModel->countTotal(),
            'total_applicants' => $this->applicantModel->countTotal(),
        ];

        $recentRequests = array_slice($this->requestModel->findAll(), 0, 5);
        $pageTitle = 'Supervising Labor Dashboard';
        include VIEW_PATH . '/supervising_labor/dashboard.php';
    }

    // GET /supervising-labor/schedules
    public function schedules(): void
    {
        requireRole('supervising_labor');

        $schedules = $this->scheduleModel->findAll();
        $pageTitle = 'Schedule of Events';
        include VIEW_PATH . '/supervising_labor/schedules.php';
    }

    // GET /supervising-labor/schedules/create
    public function createSchedule(): void
    {
        requireRole('supervising_labor');

        $pageTitle = 'Create Schedule';
        $error     = getFlash('error');
        $old       = $_SESSION['old_input'] ?? [];
        unset($_SESSION['old_input']);
        include VIEW_PATH . '/supervising_labor/create_schedule.php';
    }

    // POST /supervising-labor/schedules/store
    public function storeSchedule(): void
    {
        requireRole('supervising_labor');
        verifyCsrf();

        $title      = trim($_POST['title'] ?? '');
        $eventDate  = trim($_POST['event_date'] ?? '');
        $eventTime  = trim($_POST['event_time'] ?? '');
        $venue      = trim($_POST['venue'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($title) || empty($eventDate)) {
            flash('error', 'Title and event date are required.');
            redirect(APP_URL . '/supervising-labor/schedules/create');
        }

        // Check for date conflict
        if ($this->scheduleModel->isDateBooked($eventDate)) {
            flash('error', 'That date is already booked. Please choose a different date.');
            redirect(APP_URL . '/supervising-labor/schedules/create');
        }

        $this->scheduleModel->create([
            'title'       => $title,
            'event_date'  => $eventDate,
            'event_time'  => $eventTime ?: null,
            'venue'       => $venue ?: null,
            'description' => $description ?: null,
            'status'      => 'available',
            'created_by'  => currentUser()['id'],
        ]);

        auditLog('create_schedule', 'schedules', "Created schedule: {$title} on {$eventDate}.");
        flash('success', 'Schedule created successfully.');
        redirect(APP_URL . '/supervising-labor/schedules');
    }

    // GET /supervising-labor/requests
    public function requests(): void
    {
        requireRole('supervising_labor');

        $requests  = $this->requestModel->findAll();
        $pageTitle = 'Job Fair Requests';
        include VIEW_PATH . '/supervising_labor/requests.php';
    }

    // GET /supervising-labor/requests/{id}/validate
    public function validateRequest(int $id): void
    {
        requireRole('supervising_labor');

        $request = $this->requestModel->find($id);
        if (!$request) {
            flash('error', 'Request not found.');
            redirect(APP_URL . '/supervising-labor/requests');
        }

        $pageTitle = 'Review Job Fair Request';
        include VIEW_PATH . '/supervising_labor/validate_request.php';
    }

    // POST /supervising-labor/requests/{id}/approve
    public function approveRequest(int $id): void
    {
        requireRole('supervising_labor');
        verifyCsrf();

        $request = $this->requestModel->find($id);
        if (!$request) {
            flash('error', 'Request not found.');
            redirect(APP_URL . '/supervising-labor/requests');
        }

        $this->requestModel->update($id, [
            'status'      => 'approved',
            'reviewed_by' => currentUser()['id'],
            'remarks'     => trim($_POST['remarks'] ?? ''),
        ]);

        // Add to schedule_of_events
        $this->scheduleModel->create([
            'title'       => $request['title'],
            'event_date'  => $request['requested_date'],
            'event_time'  => '08:00:00',
            'venue'       => $request['venue'],
            'description' => $request['description'],
            'status'      => 'booked',
            'created_by'  => currentUser()['id'],
        ]);

        auditLog('approve_request', 'requests', "Approved job fair request ID {$id}.");
        flash('success', 'Job fair request approved and added to schedule.');
        redirect(APP_URL . '/supervising-labor/requests');
    }

    // POST /supervising-labor/requests/{id}/reject
    public function rejectRequest(int $id): void
    {
        requireRole('supervising_labor');
        verifyCsrf();

        $remarks = trim($_POST['remarks'] ?? '');
        if (empty($remarks)) {
            flash('error', 'Please provide a reason for rejection.');
            redirect(APP_URL . '/supervising-labor/requests/' . $id . '/validate');
        }

        $this->requestModel->update($id, [
            'status'      => 'rejected',
            'reviewed_by' => currentUser()['id'],
            'remarks'     => $remarks,
        ]);

        auditLog('reject_request', 'requests', "Rejected job fair request ID {$id}. Reason: {$remarks}");
        flash('success', 'Job fair request rejected.');
        redirect(APP_URL . '/supervising-labor/requests');
    }

    // GET /supervising-labor/agencies?request_id=X
    public function agencies(): void
    {
        requireRole('supervising_labor');

        $requestId   = (int)($_GET['request_id'] ?? 0);
        $request     = $requestId ? $this->requestModel->find($requestId) : null;
        $agencies    = $this->agencyModel->findAll($requestId ? ['job_fair_request_id' => $requestId] : []);
        $requests    = $this->requestModel->findAll(['status' => 'approved']);

        // Pull approved agency accounts directly from users table
        $db = Database::getInstance();
        $agencyUsers = $db->fetchAll(
            "SELECT u.id, u.name, u.email, u.status,
                    u.agency_name, u.agency_location,
                    u.firstname, u.lastname
             FROM users u
             WHERE u.role = 'agency' AND u.status = 'approved'
             ORDER BY COALESCE(NULLIF(u.agency_name,''), u.name) ASC"
        );

        // Mark which user IDs are already invited for the selected job fair
        $alreadyInvitedUserIds = [];
        if ($requestId) {
            $rows = $db->fetchAll(
                "SELECT user_id FROM participating_agencies WHERE job_fair_request_id = ? AND user_id IS NOT NULL",
                [$requestId]
            );
            $alreadyInvitedUserIds = array_column($rows, 'user_id');
        }

        // Vacancy count per agency for the right panel
        $vacancyCountByAgency = [];
        if ($requestId) {
            $vcRows = $db->fetchAll(
                "SELECT pa.id, COUNT(jv.id) AS vcount
                 FROM participating_agencies pa
                 LEFT JOIN job_vacancies jv ON jv.participating_agency_id = pa.id AND jv.status = 'open'
                 WHERE pa.job_fair_request_id = ?
                 GROUP BY pa.id",
                [$requestId]
            );
            foreach ($vcRows as $row) {
                $vacancyCountByAgency[$row['id']] = (int)$row['vcount'];
            }
        }

        // Confirmed resources from Secretary for this job fair
        $confirmedResources = [];
        if ($requestId) {
            $confirmedResources = $db->fetchAll(
                "SELECT ra.*, br.name AS resource_name, br.unit
                 FROM resource_allocations ra
                 JOIN barangay_resources br ON br.id = ra.resource_id
                 WHERE ra.job_fair_request_id = ? AND ra.confirmed_at IS NOT NULL
                 ORDER BY br.name",
                [$requestId]
            );
        }

        $pageTitle   = 'Participating Agencies';
        $success     = getFlash('success');
        include VIEW_PATH . '/supervising_labor/agencies.php';
    }

    // POST /supervising-labor/agencies/invite
    public function inviteAgency(): void
    {
        requireRole('supervising_labor');
        verifyCsrf();

        $requestId    = (int)($_POST['job_fair_request_id'] ?? 0);
        $agencyName   = trim($_POST['agency_name'] ?? '');
        $contactPerson = trim($_POST['contact_person'] ?? '');
        $email        = trim($_POST['email'] ?? '');
        $phone        = trim($_POST['phone'] ?? '');
        $address      = trim($_POST['address'] ?? '');

        if (!$requestId || empty($agencyName)) {
            flash('error', 'Job fair and agency name are required.');
            redirect(APP_URL . '/supervising-labor/agencies?request_id=' . $requestId);
        }

        // If email belongs to an existing user with role 'agency', link user_id
        $user = null;
        if (!empty($email)) {
            $user = $this->userModel->findByEmail($email);
        }

        $this->agencyModel->create([
            'job_fair_request_id' => $requestId,
            'user_id'             => $user && ($user['role'] ?? '') === 'agency' ? (int)$user['id'] : null,
            'agency_name'         => $agencyName,
            'contact_person'      => $contactPerson ?: null,
            'email'               => $email ?: null,
            'phone'               => $phone ?: null,
            'address'             => $address ?: null,
        ]);

        // Notify linked agency user if present
        if ($user && ($user['role'] ?? '') === 'agency') {
            $this->notificationModel->create((int)$user['id'], 'info', 'Invitation to participate', "You have been invited to participate in '{$this->requestModel->find($requestId)['title']}'.", APP_URL . '/agency/vacancies');
        }

        auditLog('invite_agency', 'agencies', "Invited agency '{$agencyName}' to request ID {$requestId}.");
        flash('success', "Agency '{$agencyName}' invited successfully.");
        redirect(APP_URL . '/supervising-labor/agencies?request_id=' . $requestId);
    }

    // POST /supervising-labor/agencies/bulk-invite
    public function bulkInvite(): void
    {
        requireRole('supervising_labor');
        verifyCsrf();

        $requestId = (int)($_POST['job_fair_request_id'] ?? 0);
        $userIds   = $_POST['user_ids'] ?? [];

        if (!$requestId) {
            flash('error', 'Please select a job fair first.');
            redirect(APP_URL . '/supervising-labor/agencies');
        }
        if (empty($userIds)) {
            flash('error', 'Please select at least one agency to invite.');
            redirect(APP_URL . '/supervising-labor/agencies?request_id=' . $requestId);
        }

        // Load the job fair details for notification message
        $fairRequest = $this->requestModel->find($requestId);
        $fairTitle   = $fairRequest['title'] ?? 'the upcoming job fair';

        $db      = Database::getInstance();
        $invited = 0;
        $skipped = 0;

        foreach ($userIds as $uid) {
            $uid  = (int)$uid;
            $user = $this->userModel->find($uid);
            if (!$user || $user['role'] !== 'agency') { $skipped++; continue; }

            $email = $user['email'] ?? null;

            // Skip if already invited
            $already = $db->fetchColumn(
                "SELECT COUNT(*) FROM participating_agencies
                 WHERE job_fair_request_id = ? AND (user_id = ? OR (email = ? AND email IS NOT NULL))",
                [$requestId, $uid, $email]
            );
            if ($already) { $skipped++; continue; }

            // Use the agency's profile name if they've set it up, otherwise fall back to their full name
            $agencyName = !empty($user['agency_name'])
                ? $user['agency_name']
                : ($user['name'] ?? trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '')));
            $agencyLocation = $user['agency_location'] ?? null;

            $this->agencyModel->create([
                'job_fair_request_id' => $requestId,
                'user_id'             => $uid,
                'agency_name'         => $agencyName,
                'contact_person'      => $user['name'] ?? null,
                'email'               => $email,
                'phone'               => $user['phone'] ?? null,
                'address'             => $agencyLocation,
            ]);

            // Notify the agency user
            $this->notificationModel->create(
                $uid,
                'info',
                'Invitation to Job Fair',
                "You have been invited to participate in '{$fairTitle}'. Please confirm your participation on your dashboard.",
                APP_URL . '/agency/dashboard'
            );
            $invited++;
        }

        auditLog('bulk_invite', 'agencies', "Bulk invited {$invited} agencies to job fair ID {$requestId}.");
        $msg = "{$invited} agency/agencies invited successfully.";
        if ($skipped) $msg .= " {$skipped} skipped (already invited or invalid).";
        flash('success', $msg);
        redirect(APP_URL . '/supervising-labor/agencies?request_id=' . $requestId);
    }

    // GET /supervising-labor/companies
    public function companies(): void
    {
        requireRole('supervising_labor');
        $search    = trim($_GET['search'] ?? '');
        $companies = $this->companyModel->findAll($search ? ['search' => $search] : []);
        $pageTitle = 'Company Directory';
        $error     = getFlash('error');
        $success   = getFlash('success');
        include VIEW_PATH . '/supervising_labor/companies.php';
    }

    // POST /supervising-labor/companies/store
    public function storeCompany(): void
    {
        requireRole('supervising_labor');
        verifyCsrf();

        $name = trim($_POST['name'] ?? '');
        if (empty($name)) {
            flash('error', 'Company name is required.');
            redirect(APP_URL . '/supervising-labor/companies');
        }

        $this->companyModel->create([
            'name'           => $name,
            'industry'       => trim($_POST['industry'] ?? '') ?: null,
            'contact_person' => trim($_POST['contact_person'] ?? '') ?: null,
            'email'          => trim($_POST['email'] ?? '') ?: null,
            'phone'          => trim($_POST['phone'] ?? '') ?: null,
            'address'        => trim($_POST['address'] ?? '') ?: null,
            'status'         => 'active',
            'created_by'     => currentUser()['id'],
        ]);

        auditLog('create_company', 'companies', "Added company: {$name}");
        flash('success', "Company '{$name}' added to directory.");
        redirect(APP_URL . '/supervising-labor/companies');
    }

    // POST /supervising-labor/companies/{id}/delete
    public function deleteCompany(int $id): void
    {
        requireRole('supervising_labor');
        verifyCsrf();
        $company = $this->companyModel->find($id);
        if ($company) {
            $this->companyModel->delete($id);
            auditLog('delete_company', 'companies', "Deleted company ID {$id}: {$company['name']}");
            flash('success', "Company removed from directory.");
        }
        redirect(APP_URL . '/supervising-labor/companies');
    }

    // GET /supervising-labor/vacancies
    public function vacancies(): void
    {
        requireRole('supervising_labor');
        $vacancies = $this->vacancyModel->findAll();
        $agencies  = $this->agencyModel->findAll(['status' => 'confirmed']);
        $pageTitle = 'Job Vacancies';
        include VIEW_PATH . '/supervising_labor/vacancies.php';
    }

    // POST /supervising-labor/vacancies/store
    public function storeVacancy(): void
    {
        requireRole('supervising_labor');
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
            flash('error', 'Agency, company name, and position are required.');
            redirect(APP_URL . '/supervising-labor/vacancies');
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
            'status'                  => 'open',
        ]);

        auditLog('create_vacancy', 'vacancies', "Created vacancy '{$position}' for '{$companyName}'.");
        flash('success', 'Vacancy added successfully.');
        redirect(APP_URL . '/supervising-labor/vacancies');
    }

    // GET /supervising-labor/vacancies/review
    public function vacanciesReview(): void
    {
        requireRole('supervising_labor');
        $notifModel = new NotificationModel();
        // Mark vacancy notifications as read
        $notifModel->markAllRead(currentUser()['id']);
        $vacancies = $this->vacancyModel->findAll();
        $pageTitle = 'Review Vacancies';
        $success   = getFlash('success');
        include VIEW_PATH . '/supervising_labor/vacancies_review.php';
    }

    // POST /supervising-labor/vacancies/{id}/remarks
    public function addVacancyRemarks(int $id): void
    {
        requireRole('supervising_labor');
        verifyCsrf();
        $remarks = trim($_POST['remarks'] ?? '');
        $status  = trim($_POST['status'] ?? 'open');
        if (empty($remarks)) {
            flash('error', 'Remarks cannot be empty.');
            redirect(APP_URL . '/supervising-labor/vacancies/review');
        }
        $this->vacancyModel->update($id, [
            'remarks'     => $remarks,
            'reviewed_by' => currentUser()['id'],
            'reviewed_at' => date('Y-m-d H:i:s'),
            'status'      => $status,
        ]);
        auditLog('vacancy_remarks', 'vacancies', "Added remarks to vacancy ID {$id}.");
        flash('success', 'Remarks saved successfully.');
        redirect(APP_URL . '/supervising-labor/vacancies/review');
    }

    // POST /supervising-labor/vacancies/{id}/accept
    public function acceptVacancy(int $id): void
    {
        requireRole('supervising_labor');
        verifyCsrf();

        $vacancy = $this->vacancyModel->find($id);
        if (!$vacancy) {
            flash('error', 'Vacancy not found.');
            redirect(APP_URL . '/supervising-labor/vacancies/review');
        }

        $remarks  = trim($_POST['sl_remarks'] ?? '');
        $officerId = (int)currentUser()['id'];

        $this->vacancyModel->update($id, [
            'sl_status'        => 'accepted',
            'sl_remarks'       => $remarks ?: null,
            'sl_processed_by'  => $officerId,
            'sl_processed_at'  => date('Y-m-d H:i:s'),
            'status'           => 'open',   // keep vacancy active / officially added
        ]);

        // Notify the agency user who submitted this vacancy
        if (!empty($vacancy['submitted_by'])) {
            $this->notificationModel->create(
                (int)$vacancy['submitted_by'],
                'vacancy_accepted',
                'Your Vacancy Was Accepted',
                "Supervising Labor accepted your vacancy: \"{$vacancy['position']}\" at {$vacancy['company_name']}." .
                ($remarks ? " Note: {$remarks}" : '') .
                " It is now officially part of the job fair.",
                APP_URL . '/agency/vacancies'
            );
        }

        auditLog('accept_vacancy', 'vacancies', "Accepted vacancy ID {$id}: {$vacancy['position']} at {$vacancy['company_name']}.");
        flash('success', "Vacancy \"{$vacancy['position']}\" accepted and agency has been notified.");
        redirect(APP_URL . '/supervising-labor/vacancies/review');
    }

    // POST /supervising-labor/vacancies/{id}/reject
    public function rejectVacancy(int $id): void
    {
        requireRole('supervising_labor');
        verifyCsrf();

        $vacancy = $this->vacancyModel->find($id);
        if (!$vacancy) {
            flash('error', 'Vacancy not found.');
            redirect(APP_URL . '/supervising-labor/vacancies/review');
        }

        $remarks   = trim($_POST['sl_remarks'] ?? '');
        $officerId = (int)currentUser()['id'];

        if (empty($remarks)) {
            flash('error', 'Please provide a reason for rejection.');
            redirect(APP_URL . '/supervising-labor/vacancies/review');
        }

        $this->vacancyModel->update($id, [
            'sl_status'        => 'rejected',
            'sl_remarks'       => $remarks,
            'sl_processed_by'  => $officerId,
            'sl_processed_at'  => date('Y-m-d H:i:s'),
        ]);

        // Notify the agency user
        if (!empty($vacancy['submitted_by'])) {
            $this->notificationModel->create(
                (int)$vacancy['submitted_by'],
                'vacancy_rejected',
                'Your Vacancy Was Not Accepted',
                "Supervising Labor did not accept your vacancy: \"{$vacancy['position']}\" at {$vacancy['company_name']}. Reason: {$remarks}",
                APP_URL . '/agency/vacancies'
            );
        }

        auditLog('reject_vacancy', 'vacancies', "Rejected vacancy ID {$id}: {$vacancy['position']}. Reason: {$remarks}");
        flash('success', "Vacancy rejected. Agency has been notified.");
        redirect(APP_URL . '/supervising-labor/vacancies/review');
    }

    // GET /supervising-labor/registration-form/{requestId}
    public function registrationForm(int $requestId): void
    {
        requireRole('supervising_labor');

        $request    = $this->requestModel->find($requestId);
        if (!$request) {
            flash('error', 'Job fair request not found.');
            redirect(APP_URL . '/supervising-labor/requests');
        }

        $applicants = $this->applicantModel->findAll(['job_fair_request_id' => $requestId]);
        $pageTitle  = 'Registration Form - ' . $request['title'];
        include VIEW_PATH . '/supervising_labor/registration_form.php';
    }

    // POST /supervising-labor/registration-form/{requestId}/store
    public function storeRegistrationForm(int $requestId): void
    {
        requireRole('supervising_labor');
        verifyCsrf();

        $request = $this->requestModel->find($requestId);
        if (!$request) {
            flash('error', 'Job fair request not found.');
            redirect(APP_URL . '/supervising-labor/requests');
        }

        $surname        = trim($_POST['surname'] ?? '');
        $firstname      = trim($_POST['firstname'] ?? '');
        $middlename     = trim($_POST['middlename'] ?? '');
        $gsis_sss_no    = trim($_POST['gsis_sss_no'] ?? '');
        $pag_ibig_no    = trim($_POST['pag_ibig_no'] ?? '');
        $philhealth_no  = trim($_POST['philhealth_no'] ?? '');
        $disability     = trim($_POST['disability_status'] ?? 'none');

        if (empty($surname) || empty($firstname)) {
            flash('error', 'Lastname and Firstname are required.');
            redirect(APP_URL . '/supervising-labor/registration-form/' . $requestId);
        }

        $applicantId = $this->applicantModel->create([
            'user_id'         => null,
            'surname'         => $surname,
            'firstname'       => $firstname,
            'middlename'      => $middlename ?: null,
            'gsis_sss_no'     => $gsis_sss_no ?: null,
            'pag_ibig_no'     => $pag_ibig_no ?: null,
            'philhealth_no'   => $philhealth_no ?: null,
            'disability_status' => $disability ?: 'none',
        ]);

        // If there is a published job fair post for this request, register the applicant for it
        $publishedPost = $this->postModel->getPublished();
        $linkedPostId = null;
        foreach ($publishedPost as $p) {
            if ((int)$p['job_fair_request_id'] === (int)$requestId) { $linkedPostId = (int)$p['id']; break; }
        }
        if ($linkedPostId) {
            // Register the applicant to the job_fair_registrations table
            $this->postModel->register($linkedPostId, (int)$applicantId, 0);
        }

        auditLog('create_applicant_via_regform', 'applicants', "Created applicant {$surname}, {$firstname} for job fair ID {$requestId}.");
        flash('success', 'Applicant registered successfully.');
        redirect(APP_URL . '/supervising-labor/registration-form/' . $requestId);
    }

    // POST /supervising-labor/registration-form/{id}/generate
    public function generateRegistrationForm(int $requestId): void
    {
        requireRole('supervising_labor');
        verifyCsrf();

        $request = $this->requestModel->find($requestId);
        if (!$request) {
            flash('error', 'Job fair request not found.');
            redirect(APP_URL . '/supervising-labor/requests');
        }

        // Create a job_fair_post that acts as the public registration form
        $title = 'Registration — ' . $request['title'];
        $db = Database::getInstance();
        $db->execute(
            "INSERT INTO job_fair_posts (job_fair_request_id, title, description, venue, event_date, event_time, status, created_by, published_at, created_at)
             VALUES (?, ?, ?, ?, ?, ?, 'published', ?, NOW(), NOW())",
            [
                $requestId,
                $title,
                'Public registration form generated by Supervising Labor.',
                $request['venue'] ?? null,
                $request['requested_date'] ?? null,
                $request['requested_time'] ?? null,
                currentUser()['id'],
            ]
        );

        auditLog('generate_registration_form', 'job_fair_posts', "Generated public registration form for request ID {$requestId}.");
        flash('success', 'Public registration form generated and published.');
        redirect(APP_URL . '/supervising-labor/registration-form/' . $requestId);
    }
}
