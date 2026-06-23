<?php
/**
 * ReliaWork2 Front Controller
 */

declare(strict_types=1);

// ── Bootstrap ─────────────────────────────────────────────────────────────────
define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/app/config/config.php';
// Enable error display in development for debugging
if (defined('APP_DEBUG') && APP_DEBUG) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}
require_once ROOT_PATH . '/app/config/Database.php';
require_once ROOT_PATH . '/app/helpers/auth_helper.php';
require_once ROOT_PATH . '/app/helpers/flash_helper.php';
require_once ROOT_PATH . '/app/helpers/view_helper.php';

// ── Models ────────────────────────────────────────────────────────────────────
require_once APP_PATH . '/models/UserModel.php';
require_once APP_PATH . '/models/ScheduleModel.php';
require_once APP_PATH . '/models/JobFairRequestModel.php';
require_once APP_PATH . '/models/AgencyModel.php';
require_once APP_PATH . '/models/CompanyModel.php';
require_once APP_PATH . '/models/VacancyModel.php';
require_once APP_PATH . '/models/JobFairPostModel.php';
require_once APP_PATH . '/models/ResourceModel.php';
require_once APP_PATH . '/models/ApplicantModel.php';
require_once APP_PATH . '/models/ApplicationModel.php';
require_once APP_PATH . '/models/AnnouncementModel.php';
require_once APP_PATH . '/models/TechVocModel.php';
require_once APP_PATH . '/models/NotificationModel.php';

// ── Controllers ───────────────────────────────────────────────────────────────
require_once APP_PATH . '/controllers/AuthController.php';
require_once APP_PATH . '/controllers/BedoController.php';
require_once APP_PATH . '/controllers/AdminController.php';
require_once APP_PATH . '/controllers/SupervisingLaborController.php';
require_once APP_PATH . '/controllers/BarangayCaptainController.php';
require_once APP_PATH . '/controllers/SecretaryController.php';
require_once APP_PATH . '/controllers/AgencyController.php';
require_once APP_PATH . '/controllers/ApplicantController.php';
require_once APP_PATH . '/controllers/TechVocController.php';
require_once APP_PATH . '/controllers/NotificationController.php';

// ── Session ───────────────────────────────────────────────────────────────────
ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_samesite', 'Lax');

session_name(SESSION_NAME);
session_start();

// ── Request Parsing ───────────────────────────────────────────────────────────
$requestUri    = $_SERVER['REQUEST_URI'] ?? '/';
$scriptName    = dirname($_SERVER['SCRIPT_NAME']);
$basePath      = rtrim($scriptName, '/');

// Strip base path and query string
$path = $requestUri;
if ($basePath !== '' && str_starts_with($path, $basePath)) {
    $path = substr($path, strlen($basePath));
}
$path = strtok($path, '?');
$path = '/' . trim($path, '/');

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

// ── Router ────────────────────────────────────────────────────────────────────
/**
 * Simple pattern-based router.
 * Patterns: /path/{id} where {id} captures an integer.
 */
function matchRoute(string $pattern, string $path): array|false
{
    $regex = preg_replace('/\{[a-z_]+\}/', '(\d+)', $pattern);
    $regex = '#^' . $regex . '$#';
    if (preg_match($regex, $path, $matches)) {
        array_shift($matches);
        return $matches;
    }
    return false;
}

$routes = [
    // ── Auth ──────────────────────────────────────────────────────────────────
    ['GET',  '/',                                   'AuthController',             'showLanding'],
    ['GET',  '/login',                              'AuthController',             'showLogin'],
    ['POST', '/login',                              'AuthController',             'login'],
    ['GET',  '/register',                           'AuthController',             'showRegister'],
    ['POST', '/register',                           'AuthController',             'register'],
    ['GET',  '/logout',                             'AuthController',             'logout'],

    // ── Dashboard redirect ────────────────────────────────────────────────────
    ['GET',  '/dashboard',                          null,                         null],

    // ── Admin ─────────────────────────────────────────────────────────────────
    ['GET',  '/admin/dashboard',                    'AdminController',            'dashboard'],
    ['GET',  '/admin/users',                        'AdminController',            'users'],
    ['POST', '/admin/users/{id}/approve',           'AdminController',            'approveUser'],
    ['POST', '/admin/users/{id}/reject',            'AdminController',            'rejectUser'],
    ['POST', '/admin/users/{id}/role',              'AdminController',            'updateRole'],

    // ── Supervising Labor ─────────────────────────────────────────────────────
    ['GET',  '/supervising-labor/dashboard',        'SupervisingLaborController', 'dashboard'],
    ['GET',  '/supervising-labor/schedules',        'SupervisingLaborController', 'schedules'],
    ['GET',  '/supervising-labor/schedules/create', 'SupervisingLaborController', 'createSchedule'],
    ['POST', '/supervising-labor/schedules/store',  'SupervisingLaborController', 'storeSchedule'],
    ['GET',  '/supervising-labor/requests',         'SupervisingLaborController', 'requests'],
    ['GET',  '/supervising-labor/requests/{id}/validate', 'SupervisingLaborController', 'validateRequest'],
    ['POST', '/supervising-labor/requests/{id}/approve',  'SupervisingLaborController', 'approveRequest'],
    ['POST', '/supervising-labor/requests/{id}/reject',   'SupervisingLaborController', 'rejectRequest'],
    ['GET',  '/supervising-labor/agencies',         'SupervisingLaborController', 'agencies'],
    ['POST', '/supervising-labor/agencies/invite',  'SupervisingLaborController', 'inviteAgency'],
    ['POST', '/supervising-labor/agencies/bulk-invite', 'SupervisingLaborController', 'bulkInvite'],
    ['GET',  '/supervising-labor/companies',        'SupervisingLaborController', 'companies'],
    ['POST', '/supervising-labor/companies/store',  'SupervisingLaborController', 'storeCompany'],
    ['POST', '/supervising-labor/companies/{id}/delete', 'SupervisingLaborController', 'deleteCompany'],
    ['GET',  '/supervising-labor/vacancies',        'SupervisingLaborController', 'vacancies'],
    ['POST', '/supervising-labor/vacancies/store',  'SupervisingLaborController', 'storeVacancy'],
    ['GET',  '/supervising-labor/vacancies/review', 'SupervisingLaborController', 'vacanciesReview'],
    ['POST', '/supervising-labor/vacancies/{id}/remarks', 'SupervisingLaborController', 'addVacancyRemarks'],
    ['POST', '/supervising-labor/vacancies/{id}/accept',  'SupervisingLaborController', 'acceptVacancy'],
    ['POST', '/supervising-labor/vacancies/{id}/reject',  'SupervisingLaborController', 'rejectVacancy'],
    ['GET',  '/supervising-labor/registration-forms',     'SupervisingLaborController', 'registrationForms'],
    ['GET',  '/supervising-labor/registration-form/{id}', 'SupervisingLaborController', 'registrationForm'],
    ['POST', '/supervising-labor/registration-form/{id}/store', 'SupervisingLaborController', 'storeRegistrationForm'],

    // ── Barangay Captain ──────────────────────────────────────────────────────
    ['GET',  '/barangay-captain/dashboard',         'BarangayCaptainController',  'dashboard'],
    ['GET',  '/barangay-captain/create-request',    'BarangayCaptainController',  'createRequest'],
    ['POST', '/barangay-captain/store-request',     'BarangayCaptainController',  'storeRequest'],
    ['GET',  '/barangay-captain/my-requests',       'BarangayCaptainController',  'myRequests'],
    ['GET',  '/api/check-date',                     'BarangayCaptainController',  'checkDate'],
    ['GET',  '/api/booked-dates',                   'BarangayCaptainController',  'bookedDates'],

    // ── Secretary ─────────────────────────────────────────────────────────────
    ['GET',  '/secretary/dashboard',                'SecretaryController',        'dashboard'],
    ['GET',  '/secretary/resources',                'SecretaryController',        'resources'],
    ['POST', '/secretary/resources/store',          'SecretaryController',        'storeResource'],
    ['POST', '/secretary/resources/confirm',        'SecretaryController',        'confirmResources'],
    ['POST', '/secretary/resources/allocate',       'SecretaryController',        'allocateResource'],
    ['POST', '/secretary/resources/{id}/update',    'SecretaryController',        'updateResource'],
    ['GET',  '/secretary/requests',                 'SecretaryController',        'requests'],
    ['GET',  '/secretary/requests/{id}/confirm',    'SecretaryController',        'confirmRequestForm'],
    ['POST', '/secretary/requests/{id}/confirm',    'SecretaryController',        'confirmRequest'],

    // ── Agency ────────────────────────────────────────────────────────────────
    ['GET',  '/agency/dashboard',                   'AgencyController',           'dashboard'],
    ['GET',  '/agency/setup',                       'AgencyController',           'showSetup'],
    ['POST', '/agency/setup',                       'AgencyController',           'saveSetup'],
    ['POST', '/agency/confirm/{id}',                'AgencyController',           'confirmParticipation'],
    ['GET',  '/agency/vacancies',                   'AgencyController',           'vacancies'],
    ['POST', '/agency/vacancies/store',             'AgencyController',           'storeVacancy'],

    // ── Applicant ─────────────────────────────────────────────────────────────
    ['GET',  '/applicant/dashboard',                'ApplicantController',        'dashboard'],
    ['GET',  '/applicant/register',                 'ApplicantController',        'register'],
    ['POST', '/applicant/register/store',           'ApplicantController',        'storeRegistration'],
    ['GET',  '/applicant/vacancies',                'ApplicantController',        'vacancies'],
    ['POST', '/applicant/apply/{id}',               'ApplicantController',        'apply'],
    ['GET',  '/applicant/my-applications',          'ApplicantController',        'myApplications'],
    ['GET',  '/applicant/job-fairs',                'ApplicantController',        'jobFairs'],
    ['GET',  '/applicant/job-fairs/{id}/register',  'ApplicantController',        'showFairRegistration'],
    ['POST', '/applicant/job-fairs/{id}/register',  'ApplicantController',        'storeFairRegistration'],
    ['GET',  '/applicant/job-fairs/{id}/confirmation', 'ApplicantController',     'registrationConfirmation'],
    ['GET',  '/applicant/job-fairs/{id}/pdf',       'ApplicantController',        'downloadPdf'],

    // ── BEDO Officer ──────────────────────────────────────────────────────────
    ['GET',  '/bedo/dashboard',                     'BedoController',             'dashboard'],
    ['GET',  '/bedo/compose',                       'BedoController',             'compose'],
    ['GET',  '/bedo/compose/preview/{id}',          'BedoController',             'previewJobFair'],
    ['POST', '/bedo/posts/store',                   'BedoController',             'store'],
    ['GET',  '/bedo/posts',                         'BedoController',             'posts'],
    ['POST', '/bedo/posts/{id}/publish',            'BedoController',             'publish'],
    ['POST', '/bedo/posts/{id}/delete',             'BedoController',             'deletePost'],

    // ── TECH-VOC Supervisor ───────────────────────────────────────────────────
    ['GET',  '/techvoc/dashboard',                  'TechVocController',          'dashboard'],
    ['GET',  '/techvoc/class/{id}',                 'TechVocController',          'classDetail'],
    ['POST', '/techvoc/class/{id}/add-student',     'TechVocController',          'addStudent'],
    ['POST', '/techvoc/class/{id}/delete-student',  'TechVocController',          'deleteStudent'],
    ['GET',  '/techvoc/class/{id}/attendance',      'TechVocController',          'attendance'],
    ['POST', '/techvoc/class/{id}/attendance/save', 'TechVocController',          'saveAttendance'],

    // ── Notifications ─────────────────────────────────────────────────────────
    ['POST', '/notifications/{id}/read',            'NotificationController',     'markRead'],
    ['POST', '/notifications/read-all',             'NotificationController',     'markAllRead'],
];

// ── Dispatch ──────────────────────────────────────────────────────────────────
$dispatched = false;

// Special: /dashboard → role-based redirect. Guests see public landing.
if ($method === 'GET' && $path === '/dashboard') {
    if (isLoggedIn()) {
        redirect(roleDashboardUrl(currentUser()['role']));
    } else {
        redirect(APP_URL . '/');
    }
}

foreach ($routes as [$routeMethod, $pattern, $controller, $action]) {
    if ($controller === null) continue;

    $params = matchRoute($pattern, $path);
    if ($params === false) continue;
    if ($routeMethod !== $method) continue;

    $dispatched = true;
    $ctrl = new $controller();
    if (!empty($params)) {
        $ctrl->$action(...array_map('intval', $params));
    } else {
        $ctrl->$action();
    }
    break;
}

if (!$dispatched) {
    http_response_code(404);
    include VIEW_PATH . '/errors/404.php';
}
