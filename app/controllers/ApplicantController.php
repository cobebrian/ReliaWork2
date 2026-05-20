<?php
/**
 * ReliaWork2 ApplicantController
 */

class ApplicantController
{
    private ApplicantModel $applicantModel;
    private VacancyModel $vacancyModel;
    private ApplicationModel $applicationModel;

    public function __construct()
    {
        $this->applicantModel   = new ApplicantModel();
        $this->vacancyModel     = new VacancyModel();
        $this->applicationModel = new ApplicationModel();
    }

    // GET /applicant/dashboard
    public function dashboard(): void
    {
        requireRole('applicant');

        $userId    = currentUser()['id'];
        $applicant = $this->applicantModel->findByUserId($userId);

        $applicationCount = 0;
        if ($applicant) {
            $applicationCount = $this->applicationModel->countByApplicant($applicant['id']);
        }

        $openVacancies = $this->vacancyModel->countOpen();
        $pageTitle     = 'Applicant Dashboard';
        include VIEW_PATH . '/applicant/dashboard.php';
    }

    // GET /applicant/register
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

    // POST /applicant/register/store
    public function storeRegistration(): void
    {
        requireRole('applicant');
        verifyCsrf();

        $userId = currentUser()['id'];

        // Check if already registered
        if ($this->applicantModel->findByUserId($userId)) {
            flash('info', 'Your applicant profile is already registered.');
            redirect(APP_URL . '/applicant/dashboard');
        }

        $surname          = trim($_POST['surname'] ?? '');
        $firstname        = trim($_POST['firstname'] ?? '');
        $middlename       = trim($_POST['middlename'] ?? '');
        $gsisSssNo        = trim($_POST['gsis_sss_no'] ?? '');
        $pagIbigNo        = trim($_POST['pag_ibig_no'] ?? '');
        $philhealthNo     = trim($_POST['philhealth_no'] ?? '');
        $disabilityStatus = $_POST['disability_status'] ?? 'none';

        if (empty($surname) || empty($firstname)) {
            $_SESSION['old_input'] = $_POST;
            flash('error', 'Surname and first name are required.');
            redirect(APP_URL . '/applicant/register');
        }

        if (!in_array($disabilityStatus, ['none', 'with_disability'], true)) {
            $disabilityStatus = 'none';
        }

        $this->applicantModel->create([
            'user_id'          => $userId,
            'surname'          => $surname,
            'firstname'        => $firstname,
            'middlename'       => $middlename ?: null,
            'gsis_sss_no'      => $gsisSssNo ?: null,
            'pag_ibig_no'      => $pagIbigNo ?: null,
            'philhealth_no'    => $philhealthNo ?: null,
            'disability_status'=> $disabilityStatus,
        ]);

        auditLog('register_applicant', 'applicants', "User ID {$userId} completed applicant registration.");
        flash('success', 'Applicant profile registered successfully.');
        redirect(APP_URL . '/applicant/dashboard');
    }

    // GET /applicant/vacancies
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

        // Get applied vacancy IDs
        $appliedIds = [];
        if ($applicant) {
            $myApps = $this->applicationModel->findAll(['applicant_id' => $applicant['id']]);
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

        $userId    = currentUser()['id'];
        $applicant = $this->applicantModel->findByUserId($userId);

        $applications = [];
        if ($applicant) {
            $applications = $this->applicationModel->findAll(['applicant_id' => $applicant['id']]);
        }

        $pageTitle = 'My Applications';
        include VIEW_PATH . '/applicant/my_applications.php';
    }
}
