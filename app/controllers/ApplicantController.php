<?php
/**
 * ReliaWork2 ApplicantController
 */

class ApplicantController
{
    private ApplicantModel $applicantModel;
    private VacancyModel $vacancyModel;
    private ApplicationModel $applicationModel;
    private JobFairPostModel $postModel;

    public function __construct()
    {
        $this->applicantModel   = new ApplicantModel();
        $this->vacancyModel     = new VacancyModel();
        $this->applicationModel = new ApplicationModel();
        $this->postModel        = new JobFairPostModel();
    }

    // ── Dashboard ─────────────────────────────────────────────────────────────

    public function dashboard(): void
    {
        requireRole('applicant');

        $userId    = currentUser()['id'];
        $applicant = $this->applicantModel->findByUserId($userId);

        $applicationCount = 0;
        $registrationCount = 0;
        if ($applicant) {
            $applicationCount  = $this->applicationModel->countByApplicant($applicant['id']);
            $db = Database::getInstance();
            $registrationCount = (int)$db->fetchColumn(
                "SELECT COUNT(*) FROM job_fair_registrations WHERE applicant_id = ?",
                [$applicant['id']]
            );
        }

        $openVacancies = $this->vacancyModel->countOpen();
        $upcomingFairs = count($this->postModel->getPublished());

        $pageTitle = 'Applicant Dashboard';
        include VIEW_PATH . '/applicant/dashboard.php';
    }

    // ── Applicant Registration (NSRP Profile) ─────────────────────────────────

    public function register(): void
    {
        requireRole('applicant');

        $userId    = currentUser()['id'];
        $applicant = $this->applicantModel->findByUserId($userId);

        if ($applicant) {
            flash('info', 'Your applicant profile is already registered.');
            redirect(APP_URL . '/applicant/dashboard');
        }

        $pageTitle = 'Complete Applicant Registration';
        $error     = getFlash('error');
        $old       = $_SESSION['old_input'] ?? [];
        unset($_SESSION['old_input']);
        include VIEW_PATH . '/applicant/register.php';
    }

    public function storeRegistration(): void
    {
        requireRole('applicant');
        verifyCsrf();

        $userId = currentUser()['id'];

        if ($this->applicantModel->findByUserId($userId)) {
            flash('info', 'Your applicant profile is already registered.');
            redirect(APP_URL . '/applicant/dashboard');
        }

        $data = $this->extractNsrpData();

        if (empty($data['surname']) || empty($data['firstname'])) {
            $_SESSION['old_input'] = $_POST;
            flash('error', 'Surname and first name are required.');
            redirect(APP_URL . '/applicant/register');
        }

        $data['user_id'] = $userId;
        $this->applicantModel->create($data);

        auditLog('register_applicant', 'applicants', "User ID {$userId} completed applicant registration.");
        flash('success', 'Profile registered! Please upload your requirements to proceed.');
        redirect(APP_URL . '/applicant/requirements');
    }

    // ── Job Fair Listing ──────────────────────────────────────────────────────

    public function jobFairs(): void
    {
        requireRole('applicant');

        $posts     = $this->postModel->getPublished();
        $userId    = currentUser()['id'];
        $applicant = $this->applicantModel->findByUserId($userId);

        // Build a set of post IDs the applicant has already registered for
        $registeredPostIds = [];
        if ($applicant) {
            $db = Database::getInstance();
            $rows = $db->fetchAll(
                "SELECT job_fair_post_id FROM job_fair_registrations WHERE applicant_id = ?",
                [$applicant['id']]
            );
            $registeredPostIds = array_column($rows, 'job_fair_post_id');
        }

        $pageTitle = 'Upcoming Job Fairs';
        include VIEW_PATH . '/applicant/job_fairs.php';
    }

    // ── Register for a Job Fair ───────────────────────────────────────────────

    public function showFairRegistration(int $postId): void
    {
        requireRole('applicant');

        $post = $this->postModel->find($postId);
        if (!$post || $post['status'] !== 'published') {
            flash('error', 'This job fair is not available.');
            redirect(APP_URL . '/applicant/job-fairs');
        }

        $userId    = currentUser()['id'];
        $applicant = $this->applicantModel->findByUserId($userId);

        // Check already registered
        if ($applicant && $this->postModel->isRegistered($postId, $applicant['id'])) {
            flash('info', 'You are already registered for this job fair.');
            redirect(APP_URL . '/applicant/job-fairs');
        }

        $companies = $this->postModel->getCompaniesAndVacancies($postId);
        $old       = $_SESSION['old_input'] ?? [];
        unset($_SESSION['old_input']);
        $error     = getFlash('error');

        $pageTitle = 'Register for Job Fair';
        include VIEW_PATH . '/applicant/register_for_fair.php';
    }

    public function storeFairRegistration(int $postId): void
    {
        requireRole('applicant');
        verifyCsrf();

        $post = $this->postModel->find($postId);
        if (!$post || $post['status'] !== 'published') {
            flash('error', 'This job fair is not available.');
            redirect(APP_URL . '/applicant/job-fairs');
        }

        $userId    = currentUser()['id'];
        $applicant = $this->applicantModel->findByUserId($userId);

        $data = $this->extractNsrpData();

        if (empty($data['surname']) || empty($data['firstname'])) {
            $_SESSION['old_input'] = $_POST;
            flash('error', 'Surname and first name are required.');
            redirect(APP_URL . '/applicant/job-fairs/' . $postId . '/register');
        }

        // Upsert applicant profile
        if (!$applicant) {
            $data['user_id'] = $userId;
            $applicantId = $this->applicantModel->create($data);
        } else {
            $this->applicantModel->update($applicant['id'], $data);
            $applicantId = $applicant['id'];
        }

        // Check duplicate registration
        if ($this->postModel->isRegistered($postId, $applicantId)) {
            flash('info', 'You are already registered for this job fair.');
            redirect(APP_URL . '/applicant/job-fairs');
        }

        $this->postModel->register($postId, $applicantId, $userId);

        auditLog('register_job_fair', 'job_fair_registrations',
            "Applicant ID {$applicantId} registered for job fair post ID {$postId}.");

        flash('success', 'Registration successful! Now select a company to apply to.');
        redirect(APP_URL . '/applicant/job-fairs/' . $postId . '/companies');
    }

    // ── Registration Confirmation / Download PDF ──────────────────────────────

    public function registrationConfirmation(int $postId): void
    {
        requireRole('applicant');

        $userId    = currentUser()['id'];
        $applicant = $this->applicantModel->findByUserId($userId);

        if (!$applicant) {
            redirect(APP_URL . '/applicant/job-fairs');
        }

        $detail = $this->postModel->getRegistrationDetail($postId, $applicant['id']);
        if (!$detail) {
            flash('error', 'Registration not found.');
            redirect(APP_URL . '/applicant/job-fairs');
        }

        $post      = $this->postModel->find($postId);
        $companies = $this->postModel->getCompaniesAndVacancies($postId);
        $pageTitle = 'Registration Confirmation';
        include VIEW_PATH . '/applicant/registration_confirmation.php';
    }

    // ── Step 2: Select Company ────────────────────────────────────────────────

    public function fairCompanies(int $postId): void
    {
        requireRole('applicant');

        $post = $this->postModel->find($postId);
        if (!$post) {
            flash('error', 'Job fair not found.');
            redirect(APP_URL . '/applicant/job-fairs');
        }

        $userId    = (int)currentUser()['id'];
        $applicant = $this->applicantModel->findByUserId($userId);
        if (!$applicant) {
            flash('error', 'Please complete your profile first.');
            redirect(APP_URL . '/applicant/register');
        }

        // Verify registered
        if (!$this->postModel->isRegistered($postId, $applicant['id'])) {
            flash('error', 'Please register for the job fair first.');
            redirect(APP_URL . '/applicant/job-fairs/' . $postId . '/register');
        }

        $data     = $this->postModel->getCompaniesAndVacancies($postId);
        $companies = $data['companies'] ?? [];

        // Mark which agencies this applicant already applied to in this fair
        $db = Database::getInstance();
        $appliedAgencyIds = [];
        $rows = $db->fetchAll(
            "SELECT jv.participating_agency_id
             FROM applications app
             JOIN job_vacancies jv ON jv.id = app.job_vacancy_id
             WHERE app.applicant_id = ? AND app.job_fair_post_id = ?",
            [$applicant['id'], $postId]
        );
        $appliedAgencyIds = array_column($rows, 'participating_agency_id');

        $success  = getFlash('success');
        $pageTitle = 'Select a Company — ' . $post['title'];
        include VIEW_PATH . '/applicant/fair_companies.php';
    }

    // ── Step 3: Select Vacancy ────────────────────────────────────────────────

    public function fairVacancies(int $postId, int $agencyId): void
    {
        requireRole('applicant');

        $post = $this->postModel->find($postId);
        if (!$post) {
            redirect(APP_URL . '/applicant/job-fairs');
        }

        $userId    = (int)currentUser()['id'];
        $applicant = $this->applicantModel->findByUserId($userId);
        if (!$applicant || !$this->postModel->isRegistered($postId, $applicant['id'])) {
            redirect(APP_URL . '/applicant/job-fairs');
        }

        $db = Database::getInstance();

        $agency = $db->fetch(
            "SELECT pa.*, jfr.title AS fair_title
             FROM participating_agencies pa
             JOIN job_fair_requests jfr ON jfr.id = pa.job_fair_request_id
             WHERE pa.id = ?",
            [$agencyId]
        );
        if (!$agency) {
            flash('error', 'Company not found.');
            redirect(APP_URL . '/applicant/job-fairs/' . $postId . '/companies');
        }

        $vacancies = $db->fetchAll(
            "SELECT jv.* FROM job_vacancies jv
             WHERE jv.participating_agency_id = ? AND jv.status = 'open'
             ORDER BY jv.position",
            [$agencyId]
        );

        // Get already-applied vacancy IDs
        $appliedVacancyIds = array_column(
            $db->fetchAll(
                "SELECT job_vacancy_id FROM applications WHERE applicant_id = ?",
                [$applicant['id']]
            ),
            'job_vacancy_id'
        );

        $pageTitle = 'Choose a Position — ' . $agency['agency_name'];
        include VIEW_PATH . '/applicant/fair_vacancies.php';
    }

    // ── Step 4: Apply + Upload Requirements ───────────────────────────────────

    public function showApply(int $postId, int $vacancyId): void
    {
        requireRole('applicant');

        $userId    = (int)currentUser()['id'];
        $applicant = $this->applicantModel->findByUserId($userId);
        if (!$applicant || !$this->postModel->isRegistered($postId, $applicant['id'])) {
            redirect(APP_URL . '/applicant/job-fairs');
        }

        $db      = Database::getInstance();
        $vacancy = $db->fetch(
            "SELECT jv.*, pa.agency_name, pa.address AS agency_location, pa.id AS agency_id
             FROM job_vacancies jv
             JOIN participating_agencies pa ON pa.id = jv.participating_agency_id
             WHERE jv.id = ? AND jv.status = 'open'",
            [$vacancyId]
        );
        if (!$vacancy) {
            flash('error', 'Vacancy not found or no longer available.');
            redirect(APP_URL . '/applicant/job-fairs/' . $postId . '/companies');
        }

        $post = $this->postModel->find($postId);

        // Already applied?
        if ($this->applicationModel->alreadyApplied($applicant['id'], $vacancyId)) {
            flash('info', 'You have already applied for this position.');
            redirect(APP_URL . '/applicant/job-fairs/' . $postId . '/companies');
        }

        // Existing applicant_documents for pre-fill
        $existingDocs = $db->fetchAll(
            "SELECT * FROM applicant_documents WHERE applicant_id = ? ORDER BY doc_type, uploaded_at DESC",
            [$applicant['id']]
        );

        $error     = getFlash('error');
        $pageTitle = 'Apply — ' . $vacancy['position'] . ' at ' . $vacancy['agency_name'];
        include VIEW_PATH . '/applicant/apply_vacancy.php';
    }

    public function storeApply(int $postId, int $vacancyId): void
    {
        requireRole('applicant');
        verifyCsrf();

        $userId    = (int)currentUser()['id'];
        $applicant = $this->applicantModel->findByUserId($userId);
        if (!$applicant) {
            redirect(APP_URL . '/applicant/job-fairs');
        }

        $db      = Database::getInstance();
        $vacancy = $db->fetch(
            "SELECT jv.*, pa.id AS agency_id, pa.job_fair_request_id
             FROM job_vacancies jv
             JOIN participating_agencies pa ON pa.id = jv.participating_agency_id
             WHERE jv.id = ? AND jv.status = 'open'",
            [$vacancyId]
        );
        if (!$vacancy) {
            flash('error', 'Vacancy is no longer available.');
            redirect(APP_URL . '/applicant/job-fairs/' . $postId . '/companies');
        }

        if ($this->applicationModel->alreadyApplied($applicant['id'], $vacancyId)) {
            flash('info', 'You have already applied for this position.');
            redirect(APP_URL . '/applicant/job-fairs/' . $postId . '/companies');
        }

        // Validate at least one file
        $hasFiles = false;
        foreach (['resume','cv','diploma','certificate','other'] as $type) {
            if (!empty($_FILES[$type]['name'])) { $hasFiles = true; break; }
        }
        if (!$hasFiles) {
            flash('error', 'Please upload at least your Resume before submitting.');
            redirect(APP_URL . '/applicant/job-fairs/' . $postId . '/apply/' . $vacancyId);
        }

        // Create application record
        $pdo = $db->getPdo();
        $pdo->beginTransaction();
        try {
            $appId = $this->applicationModel->create([
                'applicant_id'       => $applicant['id'],
                'job_vacancy_id'     => $vacancyId,
                'job_fair_post_id'   => $postId,
                'job_fair_request_id'=> $vacancy['job_fair_request_id'],
                'notes'              => trim($_POST['notes'] ?? '') ?: null,
            ]);

            // Upload documents for this application
            $uploadDir = PUBLIC_PATH . '/uploads/documents/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $allowedExt = ['pdf','doc','docx','jpg','jpeg','png'];
            foreach (['resume','cv','diploma','certificate','other'] as $type) {
                if (empty($_FILES[$type]['name'])) continue;
                $file     = $_FILES[$type];
                $origName = basename($file['name']);
                $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowedExt)) continue;
                if ($file['size'] > 5 * 1024 * 1024) continue;

                $storedName = 'app_' . $appId . '_' . $type . '_' . time() . '.' . $ext;
                $filePath   = $uploadDir . $storedName;
                if (!move_uploaded_file($file['tmp_name'], $filePath)) continue;

                $db->execute(
                    "INSERT INTO application_documents
                     (application_id, applicant_id, doc_type, original_name, stored_name, file_path, file_size, mime_type)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $appId, $applicant['id'], $type, $origName,
                        $storedName, '/uploads/documents/' . $storedName,
                        $file['size'], $file['type'],
                    ]
                );
            }

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            flash('error', 'Submission failed. Please try again.');
            redirect(APP_URL . '/applicant/job-fairs/' . $postId . '/apply/' . $vacancyId);
        }

        // Notify validating officers
        $officers = $db->fetchAll(
            "SELECT id FROM users WHERE role = 'validating_officer' AND status = 'approved'"
        );
        $nm   = new NotificationModel();
        $name = strtoupper($applicant['surname']) . ', ' . $applicant['firstname'];
        foreach ($officers as $o) {
            $nm->create(
                (int)$o['id'],
                'new_application',
                'New Application Submitted',
                "Applicant {$name} applied for \"{$vacancy['position']}\" at {$vacancy['agency_name']}. Requirements attached.",
                APP_URL . '/validating-officer/applications/' . $appId . '/review'
            );
        }

        auditLog('apply_vacancy', 'applications',
            "Applicant {$applicant['id']} applied for vacancy {$vacancyId} in post {$postId}. App ID: {$appId}");
        flash('success', "Application submitted for \"{$vacancy['position']}\"! A Validating Officer will review your documents.");
        redirect(APP_URL . '/applicant/my-applications');
    }

    public function downloadPdf(int $postId): void
    {
        requireRole('applicant');

        $userId    = currentUser()['id'];
        $applicant = $this->applicantModel->findByUserId($userId);

        if (!$applicant) {
            redirect(APP_URL . '/applicant/job-fairs');
        }

        $detail = $this->postModel->getRegistrationDetail($postId, $applicant['id']);
        if (!$detail) {
            flash('error', 'Registration not found.');
            redirect(APP_URL . '/applicant/job-fairs');
        }

        $post      = $this->postModel->find($postId);
        $companies = $this->postModel->getCompaniesAndVacancies($postId);
        $pageTitle = 'NSRP Registration Form';
        include VIEW_PATH . '/applicant/registration_pdf.php';
    }

    // ── Vacancies (browse all open vacancies) ─────────────────────────────────

    public function vacancies(): void
    {
        requireRole('applicant');

        $search    = trim($_GET['search'] ?? '');
        $vacancies = $this->vacancyModel->findAll([
            'status' => 'open',
            'search' => $search,
        ]);

        $userId    = currentUser()['id'];
        $applicant = $this->applicantModel->findByUserId($userId);

        $appliedIds = [];
        if ($applicant) {
            $myApps     = $this->applicationModel->findAll(['applicant_id' => $applicant['id']]);
            $appliedIds = array_column($myApps, 'job_vacancy_id');
        }

        $pageTitle = 'Browse Job Vacancies';
        include VIEW_PATH . '/applicant/vacancies.php';
    }

    // POST /applicant/apply/{vacancyId}
    public function apply(int $vacancyId): void
    {
        requireRole('applicant');
        verifyCsrf();

        $userId    = currentUser()['id'];
        $applicant = $this->applicantModel->findByUserId($userId);

        if (!$applicant) {
            flash('error', 'Please complete your applicant registration first.');
            redirect(APP_URL . '/applicant/register');
        }

        $vacancy = $this->vacancyModel->find($vacancyId);
        if (!$vacancy || $vacancy['status'] !== 'open') {
            flash('error', 'This vacancy is no longer available.');
            redirect(APP_URL . '/applicant/vacancies');
        }

        if ($this->applicationModel->alreadyApplied($applicant['id'], $vacancyId)) {
            flash('warning', 'You have already applied for this position.');
            redirect(APP_URL . '/applicant/vacancies');
        }

        $this->applicationModel->create([
            'applicant_id'   => $applicant['id'],
            'job_vacancy_id' => $vacancyId,
        ]);

        auditLog('apply_job', 'applications', "Applicant ID {$applicant['id']} applied for vacancy ID {$vacancyId}.");
        flash('success', "Application submitted for '{$vacancy['position']}' at {$vacancy['company_name']}.");
        redirect(APP_URL . '/applicant/my-applications');
    }

    // GET /applicant/my-applications
    public function myApplications(): void
    {
        requireRole('applicant');

        $userId    = (int)currentUser()['id'];
        $applicant = $this->applicantModel->findByUserId($userId);

        $applications = [];
        if ($applicant) {
            $applications = $this->applicationModel->findAll(['applicant_id' => $applicant['id']]);
        }

        $pageTitle = 'My Applications';
        include VIEW_PATH . '/applicant/my_applications.php';
    }

    // GET /applicant/my-applications/{id}/resubmit
    public function resubmitApplication(int $appId): void
    {
        requireRole('applicant');

        $userId    = (int)currentUser()['id'];
        $applicant = $this->applicantModel->findByUserId($userId);
        if (!$applicant) { redirect(APP_URL . '/applicant/dashboard'); }

        $db  = Database::getInstance();
        $app = $db->fetch(
            "SELECT app.*, jv.position, jv.id AS vacancy_id, jv.participating_agency_id AS agency_id,
                    pa.agency_name, pa.address AS agency_location,
                    jv.qualifications, jv.available_slots, jv.mobile_number, jv.gmail_address,
                    jv.company_location
             FROM applications app
             JOIN job_vacancies jv ON jv.id = app.job_vacancy_id
             JOIN participating_agencies pa ON pa.id = jv.participating_agency_id
             WHERE app.id = ? AND app.applicant_id = ?",
            [$appId, $applicant['id']]
        );
        if (!$app || !in_array($app['validation_status'], ['rejected', 'resubmit'])) {
            flash('error', 'Cannot resubmit this application.');
            redirect(APP_URL . '/applicant/my-applications');
        }

        $existingDocs = $this->applicationModel->getDocuments($appId);
        $vacancy      = $app;
        $post         = $this->postModel->find((int)$app['job_fair_post_id']);
        $error        = getFlash('error');
        $pageTitle    = 'Resubmit Documents — ' . $app['position'];
        include VIEW_PATH . '/applicant/apply_vacancy.php';
    }

    // POST /applicant/my-applications/{id}/resubmit
    public function storeResubmit(int $appId): void
    {
        requireRole('applicant');
        verifyCsrf();

        $userId    = (int)currentUser()['id'];
        $applicant = $this->applicantModel->findByUserId($userId);
        if (!$applicant) { redirect(APP_URL . '/applicant/dashboard'); }

        $db  = Database::getInstance();
        $app = $db->fetch(
            "SELECT app.*, jv.participating_agency_id, pa.agency_name, jv.position
             FROM applications app
             JOIN job_vacancies jv ON jv.id = app.job_vacancy_id
             JOIN participating_agencies pa ON pa.id = jv.participating_agency_id
             WHERE app.id = ? AND app.applicant_id = ?",
            [$appId, $applicant['id']]
        );
        if (!$app || !in_array($app['validation_status'], ['rejected', 'resubmit'])) {
            redirect(APP_URL . '/applicant/my-applications');
        }

        // Delete old docs for this application
        $oldDocs = $this->applicationModel->getDocuments($appId);
        foreach ($oldDocs as $od) {
            $path = PUBLIC_PATH . $od['file_path'];
            if (file_exists($path)) @unlink($path);
        }
        $db->execute("DELETE FROM application_documents WHERE application_id = ?", [$appId]);

        // Upload new files
        $uploadDir  = PUBLIC_PATH . '/uploads/documents/';
        $allowedExt = ['pdf','doc','docx','jpg','jpeg','png'];
        $uploaded   = 0;
        foreach (['resume','cv','diploma','certificate','other'] as $type) {
            if (empty($_FILES[$type]['name'])) continue;
            $file     = $_FILES[$type];
            $origName = basename($file['name']);
            $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExt) || $file['size'] > 5*1024*1024) continue;
            $storedName = 'app_' . $appId . '_' . $type . '_' . time() . '.' . $ext;
            if (!move_uploaded_file($file['tmp_name'], $uploadDir . $storedName)) continue;
            $db->execute(
                "INSERT INTO application_documents
                 (application_id, applicant_id, doc_type, original_name, stored_name, file_path, file_size, mime_type)
                 VALUES (?,?,?,?,?,?,?,?)",
                [$appId, $applicant['id'], $type, $origName, $storedName,
                 '/uploads/documents/' . $storedName, $file['size'], $file['type']]
            );
            $uploaded++;
        }

        if ($uploaded === 0) {
            flash('error', 'Please upload at least one document.');
            redirect(APP_URL . '/applicant/my-applications/' . $appId . '/resubmit');
        }

        // Reset validation status to pending
        $db->execute(
            "UPDATE applications SET validation_status = 'pending_validation',
             validated_by = NULL, validated_at = NULL, validator_remarks = NULL,
             updated_at = NOW() WHERE id = ?",
            [$appId]
        );

        // Notify validating officers
        $officers = $db->fetchAll("SELECT id FROM users WHERE role = 'validating_officer' AND status = 'approved'");
        $nm   = new NotificationModel();
        $name = strtoupper($applicant['surname']) . ', ' . $applicant['firstname'];
        foreach ($officers as $o) {
            $nm->create((int)$o['id'], 'resubmission',
                'Documents Resubmitted',
                "Applicant {$name} resubmitted documents for \"{$app['position']}\" at {$app['agency_name']}.",
                APP_URL . '/validating-officer/applications/' . $appId . '/review');
        }

        flash('success', 'Documents resubmitted successfully. A Validating Officer will review them.');
        redirect(APP_URL . '/applicant/my-applications');
    }

    // GET /applicant/nsrp-form-download — blank NSRP form for printing
    public function nsrpFormDownload(): void
    {
        requireRole('applicant');
        $pageTitle = 'NSRP Form 1 — Blank';
        include VIEW_PATH . '/applicant/nsrp_blank_form.php';
    }

    // ── Complying Requirements ────────────────────────────────────────────────

    public function requirements(): void
    {
        requireRole('applicant');

        $userId    = (int)currentUser()['id'];
        $applicant = $this->applicantModel->findByUserId($userId);

        if (!$applicant) {
            flash('info', 'Please complete your profile first.');
            redirect(APP_URL . '/applicant/register');
        }

        $db        = Database::getInstance();
        $documents = $db->fetchAll(
            "SELECT * FROM applicant_documents WHERE applicant_id = ? ORDER BY doc_type, uploaded_at",
            [$applicant['id']]
        );

        $success = getFlash('success');
        $error   = getFlash('error');
        $pageTitle = 'Complying Requirements';
        include VIEW_PATH . '/applicant/requirements.php';
    }

    public function uploadDocument(): void
    {
        requireRole('applicant');
        verifyCsrf();

        $userId    = (int)currentUser()['id'];
        $applicant = $this->applicantModel->findByUserId($userId);
        if (!$applicant) {
            flash('error', 'Profile not found.');
            redirect(APP_URL . '/applicant/requirements');
        }

        $docType  = $_POST['doc_type'] ?? 'other';
        $allowed  = ['resume', 'cv', 'diploma', 'certificate', 'other'];
        if (!in_array($docType, $allowed)) $docType = 'other';

        if (empty($_FILES['document']['name'])) {
            flash('error', 'Please select a file to upload.');
            redirect(APP_URL . '/applicant/requirements');
        }

        $file     = $_FILES['document'];
        $origName = basename($file['name']);
        $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $allowedExt = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

        if (!in_array($ext, $allowedExt)) {
            flash('error', 'Only PDF, Word, or image files are allowed.');
            redirect(APP_URL . '/applicant/requirements');
        }
        if ($file['size'] > 5 * 1024 * 1024) {
            flash('error', 'File size must not exceed 5MB.');
            redirect(APP_URL . '/applicant/requirements');
        }

        $storedName = $docType . '_' . $applicant['id'] . '_' . time() . '.' . $ext;
        $uploadDir  = PUBLIC_PATH . '/uploads/documents/';
        $filePath   = $uploadDir . $storedName;

        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            flash('error', 'File upload failed. Please try again.');
            redirect(APP_URL . '/applicant/requirements');
        }

        $db = Database::getInstance();
        $db->execute(
            "INSERT INTO applicant_documents
             (applicant_id, doc_type, original_name, stored_name, file_path, file_size, mime_type, uploaded_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
            [
                $applicant['id'],
                $docType,
                $origName,
                $storedName,
                '/uploads/documents/' . $storedName,
                $file['size'],
                $file['type'],
            ]
        );

        // Mark as pending_validation when any document is uploaded
        if (in_array($applicant['validation_status'] ?? 'not_submitted', ['not_submitted', 'resubmit', 'rejected'])) {
            // Use direct SQL to guarantee it updates — bypasses any model field filtering
            $db->execute(
                "UPDATE applicants SET validation_status = 'pending', updated_at = NOW() WHERE id = ?",
                [$applicant['id']]
            );

            // Notify all validating officers
            $officers = $db->fetchAll(
                "SELECT id FROM users WHERE role = 'validating_officer' AND status = 'approved'"
            );
            $nm   = new NotificationModel();
            $name = strtoupper($applicant['surname']) . ', ' . $applicant['firstname'];
            foreach ($officers as $o) {
                $nm->create(
                    (int)$o['id'],
                    'new_submission',
                    'New Document Submission',
                    "Applicant {$name} has submitted documents for validation.",
                    APP_URL . '/validating-officer/applicants/' . $applicant['id'] . '/review'
                );
            }
        }

        auditLog('upload_document', 'applicant_documents', "Applicant {$applicant['id']} uploaded {$docType}: {$origName}");
        flash('success', ucfirst($docType) . ' uploaded successfully.');
        redirect(APP_URL . '/applicant/requirements');
    }

    public function deleteDocument(int $docId): void
    {
        requireRole('applicant');
        verifyCsrf();

        $userId    = (int)currentUser()['id'];
        $applicant = $this->applicantModel->findByUserId($userId);
        if (!$applicant) {
            redirect(APP_URL . '/applicant/requirements');
        }

        $db  = Database::getInstance();
        $doc = $db->fetch(
            "SELECT * FROM applicant_documents WHERE id = ? AND applicant_id = ?",
            [$docId, $applicant['id']]
        );
        if (!$doc) {
            flash('error', 'Document not found.');
            redirect(APP_URL . '/applicant/requirements');
        }

        // Delete file
        $fullPath = PUBLIC_PATH . $doc['file_path'];
        if (file_exists($fullPath)) @unlink($fullPath);

        $db->execute("DELETE FROM applicant_documents WHERE id = ?", [$docId]);
        flash('success', 'Document removed.');
        redirect(APP_URL . '/applicant/requirements');
    }

    public function submitRequirements(): void
    {
        requireRole('applicant');
        verifyCsrf();

        $userId    = (int)currentUser()['id'];
        $applicant = $this->applicantModel->findByUserId($userId);
        if (!$applicant) {
            redirect(APP_URL . '/applicant/requirements');
        }

        $db        = Database::getInstance();
        $docCount  = (int)$db->fetchColumn(
            "SELECT COUNT(*) FROM applicant_documents WHERE applicant_id = ?",
            [$applicant['id']]
        );

        if ($docCount === 0) {
            flash('error', 'Please upload at least one document before submitting.');
            redirect(APP_URL . '/applicant/requirements');
        }

        // Direct SQL update — guaranteed to work regardless of model field filtering
        $db->execute(
            "UPDATE applicants SET validation_status = 'pending', updated_at = NOW() WHERE id = ?",
            [$applicant['id']]
        );

        // Notify validating officers
        $officers = $db->fetchAll("SELECT id FROM users WHERE role = 'validating_officer' AND status = 'approved'");
        $nm   = new NotificationModel();
        $name = strtoupper($applicant['surname']) . ', ' . $applicant['firstname'];
        foreach ($officers as $o) {
            $nm->create(
                (int)$o['id'],
                'new_submission',
                'Requirements Submitted for Review',
                "Applicant {$name} has submitted their requirements for validation.",
                APP_URL . '/validating-officer/applicants/' . $applicant['id'] . '/review'
            );
        }

        auditLog('submit_requirements', 'applicants', "Applicant {$applicant['id']} submitted requirements for validation.");
        flash('success', 'Requirements submitted! A Validating Officer will review your documents shortly.');
        redirect(APP_URL . '/applicant/requirements');
    }

    // ── Private helper: extract all NSRP fields from POST ────────────────────

    private function extractNsrpData(): array
    {
        $str = fn(string $k) => trim($_POST[$k] ?? '');
        $int = fn(string $k) => isset($_POST[$k]) ? 1 : 0;

        return [
            'surname'            => $str('surname'),
            'firstname'          => $str('firstname'),
            'middlename'         => $str('middlename') ?: null,
            'suffix'             => $str('suffix') ?: null,
            'date_of_birth'      => $str('date_of_birth') ?: null,
            'place_of_birth'     => $str('place_of_birth') ?: null,
            'sex'                => in_array($_POST['sex'] ?? '', ['male','female']) ? $_POST['sex'] : null,
            'religion'           => $str('religion') ?: null,
            'civil_status'       => in_array($_POST['civil_status'] ?? '', ['single','married','separated','live_in','widowed'])
                                        ? $_POST['civil_status'] : null,
            'present_address'    => $str('present_address') ?: null,
            'height'             => $str('height') ?: null,
            'tin'                => $str('tin') ?: null,
            'email'              => $str('email') ?: null,
            'landline'           => $str('landline') ?: null,
            'cellphone'          => $str('cellphone') ?: null,
            'gsis_sss_no'        => $str('gsis_sss_no') ?: null,
            'pag_ibig_no'        => $str('pag_ibig_no') ?: null,
            'philhealth_no'      => $str('philhealth_no') ?: null,
            'disability'         => $str('disability') ?: null,
            'employment_status'  => $str('employment_status') ?: null,
            'actively_looking'   => isset($_POST['actively_looking']) ? 1 : 0,
            'willing_immediate'  => isset($_POST['willing_immediate']) ? 1 : 0,
            'is_4ps'             => isset($_POST['is_4ps']) ? 1 : 0,
            'household_id'       => $str('household_id') ?: null,
            'preferred_occupation' => $str('preferred_occupation') ?: null,
            'preferred_location'   => $str('preferred_location') ?: null,
            'expected_salary'      => $str('expected_salary') ?: null,
            'passport_no'        => $str('passport_no') ?: null,
            'educational_bg'     => $str('educational_bg') ?: null,
            'trainings'          => $str('trainings') ?: null,
            'eligibility'        => $str('eligibility') ?: null,
            'work_experience'    => $str('work_experience') ?: null,
            'other_skills'       => $str('other_skills') ?: null,
        ];
    }
}
