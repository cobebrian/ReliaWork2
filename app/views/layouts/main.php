<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Dashboard', ENT_QUOTES, 'UTF-8') ?> — ReliaWork2</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/app.css">
</head>
<body class="rw2-layout">

<?php
$user = currentUser();
$role = $user['role'] ?? '';

// Role-specific nav links
$navLinks = [];
switch ($role) {
    case 'admin':
        $navLinks = [
            ['url' => APP_URL . '/admin/dashboard', 'icon' => 'bi-speedometer2', 'label' => 'Dashboard'],
            ['url' => APP_URL . '/admin/users',     'icon' => 'bi-people-fill',  'label' => 'User Management'],
        ];
        break;
    case 'supervising_labor':
        $navLinks = [
            ['url' => APP_URL . '/supervising-labor/dashboard',  'icon' => 'bi-speedometer2',    'label' => 'Dashboard'],
            ['url' => APP_URL . '/supervising-labor/schedules',  'icon' => 'bi-calendar3',        'label' => 'Schedules'],
            ['url' => APP_URL . '/supervising-labor/requests',   'icon' => 'bi-clipboard-check',  'label' => 'Job Fair Requests'],
            ['url' => APP_URL . '/supervising-labor/agencies',   'icon' => 'bi-building',         'label' => 'Agencies'],
            ['url' => APP_URL . '/supervising-labor/vacancies',  'icon' => 'bi-briefcase',        'label' => 'Vacancies'],
            ['url' => APP_URL . '/supervising-labor/reports',    'icon' => 'bi-file-earmark-bar-graph', 'label' => 'Reports'],
        ];
        break;
    case 'barangay_captain':
        $navLinks = [
            ['url' => APP_URL . '/barangay-captain/dashboard',      'icon' => 'bi-speedometer2',  'label' => 'Dashboard'],
            ['url' => APP_URL . '/barangay-captain/create-request', 'icon' => 'bi-plus-circle',   'label' => 'Create Request'],
            ['url' => APP_URL . '/barangay-captain/my-requests',    'icon' => 'bi-list-check',    'label' => 'My Requests'],
        ];
        break;
    case 'secretary':
        $navLinks = [
            ['url' => APP_URL . '/secretary/dashboard',  'icon' => 'bi-speedometer2',  'label' => 'Dashboard'],
            ['url' => APP_URL . '/secretary/resources',  'icon' => 'bi-box-seam',      'label' => 'Resources'],
        ];
        break;
    case 'agency':
        $navLinks = [
            ['url' => APP_URL . '/agency/dashboard',    'icon' => 'bi-speedometer2',    'label' => 'Dashboard'],
            ['url' => APP_URL . '/agency/vacancies',    'icon' => 'bi-briefcase',        'label' => 'My Vacancies'],
            ['url' => APP_URL . '/agency/interviews',   'icon' => 'bi-camera-video',     'label' => 'Interviews'],
            ['url' => APP_URL . '/agency/hiring',       'icon' => 'bi-person-check-fill','label' => 'Qualified Applicants'],
        ];
        break;
    case 'applicant':
        $navLinks = [
            ['url' => APP_URL . '/applicant/dashboard',        'icon' => 'bi-speedometer2',      'label' => 'Dashboard'],
            ['url' => APP_URL . '/applicant/register',         'icon' => 'bi-person-plus',        'label' => 'My Profile'],
            ['url' => APP_URL . '/applicant/requirements',     'icon' => 'bi-folder-check',       'label' => 'Requirements'],
            ['url' => APP_URL . '/applicant/job-fairs',        'icon' => 'bi-calendar-event',     'label' => 'Job Fairs'],
            ['url' => APP_URL . '/applicant/vacancies',        'icon' => 'bi-search',             'label' => 'Browse Jobs'],
            ['url' => APP_URL . '/applicant/my-applications',  'icon' => 'bi-file-earmark-text',  'label' => 'My Applications'],
            ['url' => APP_URL . '/applicant/interviews',       'icon' => 'bi-camera-video',       'label' => 'My Interviews'],
            ['url' => APP_URL . '/applicant/my-applications',  'icon' => 'bi-file-earmark-text',  'label' => 'My Applications'],
        ];
        break;
    case 'validating_officer':
        $navLinks = [
            ['url' => APP_URL . '/validating-officer/dashboard',  'icon' => 'bi-speedometer2',   'label' => 'Dashboard'],
            ['url' => APP_URL . '/validating-officer/applicants', 'icon' => 'bi-people',         'label' => 'Applicants'],
            ['url' => APP_URL . '/validating-officer/applicants?status=pending',  'icon' => 'bi-hourglass-split', 'label' => 'Pending Review'],
            ['url' => APP_URL . '/validating-officer/applicants?status=approved', 'icon' => 'bi-check-circle',    'label' => 'Approved'],
        ];
        break;
    case 'reporting_officer':
        $navLinks = [
            ['url' => APP_URL . '/reporting-officer/dashboard',  'icon' => 'bi-speedometer2',        'label' => 'Dashboard'],
            ['url' => APP_URL . '/reporting-officer/reports',     'icon' => 'bi-archive',              'label' => 'Reports Repository'],
            ['url' => APP_URL . '/reporting-officer/interviews',  'icon' => 'bi-camera-video',        'label' => 'All Interviews'],
            ['url' => APP_URL . '/reporting-officer/interviews?outcome=qualified_for_contact', 'icon' => 'bi-person-check', 'label' => 'Qualified'],
            ['url' => APP_URL . '/reporting-officer/interviews?outcome=hired',                 'icon' => 'bi-trophy',       'label' => 'Hired'],
        ];
        break;
    case 'techvoc_supervisor':
        $navLinks = [
            ['url' => APP_URL . '/techvoc/dashboard',                'icon' => 'bi-speedometer2',    'label' => 'Dashboard'],
            ['url' => APP_URL . '/techvoc/class/1',                  'icon' => 'bi-fire',            'label' => 'Welding Class'],
            ['url' => APP_URL . '/techvoc/class/2',                  'icon' => 'bi-lightning-charge-fill', 'label' => 'Electrical Class'],
            ['url' => APP_URL . '/techvoc/class/1/attendance',       'icon' => 'bi-clipboard-check', 'label' => 'Welding Attendance'],
            ['url' => APP_URL . '/techvoc/class/2/attendance',       'icon' => 'bi-clipboard-check', 'label' => 'Electrical Attendance'],
        ];
        break;
    case 'bedo':
        $navLinks = [
            ['url' => APP_URL . '/bedo/dashboard',    'icon' => 'bi-speedometer2',      'label' => 'Dashboard'],
            ['url' => APP_URL . '/bedo/compose',      'icon' => 'bi-megaphone-fill',     'label' => 'Post Job Fair Ad'],
            ['url' => APP_URL . '/bedo/posts',        'icon' => 'bi-collection',         'label' => 'My Posts'],
        ];
        break;
}

$currentUrl = APP_URL . strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
// Normalize current URL
$scriptBase = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$requestPath = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
if ($scriptBase && str_starts_with($requestPath, $scriptBase)) {
    $requestPath = substr($requestPath, strlen($scriptBase));
}
$requestPath = '/' . trim($requestPath, '/');
?>

<!-- Sidebar -->
<nav class="rw2-sidebar" id="sidebar">
    <div class="sidebar-brand">
        <i class="bi bi-briefcase-fill me-2"></i>
        <span>ReliaWork2</span>
    </div>
    <div class="sidebar-role-badge">
        <?= htmlspecialchars(ROLE_LABELS[$role] ?? ucfirst($role), ENT_QUOTES, 'UTF-8') ?>
    </div>
    <ul class="sidebar-nav">
        <?php foreach ($navLinks as $link): ?>
            <?php
            $isActive = str_contains($requestPath, parse_url($link['url'], PHP_URL_PATH) ?? '');
            ?>
            <li class="sidebar-nav-item">
                <a href="<?= htmlspecialchars($link['url'], ENT_QUOTES, 'UTF-8') ?>"
                   class="sidebar-nav-link <?= $isActive ? 'active' : '' ?>">
                    <i class="bi <?= $link['icon'] ?> me-2"></i>
                    <?= htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
    <div class="sidebar-footer">
        <a href="<?= APP_URL ?>/logout" class="sidebar-nav-link text-danger">
            <i class="bi bi-box-arrow-left me-2"></i> Logout
        </a>
    </div>
</nav>

<!-- Main Content -->
<div class="rw2-main">
    <!-- Top Navbar -->
    <header class="rw2-topbar">
        <button class="btn btn-sm btn-outline-secondary d-lg-none me-2" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <div class="topbar-title"><?= htmlspecialchars($pageTitle ?? 'Dashboard', ENT_QUOTES, 'UTF-8') ?></div>
        <div class="topbar-user ms-auto">
            <i class="bi bi-person-circle me-1"></i>
            <span><?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
            <a href="<?= APP_URL ?>/logout" class="btn btn-sm btn-outline-danger ms-3">
                <i class="bi bi-box-arrow-left"></i> Logout
            </a>
        </div>
    </header>

    <!-- Page Content -->
    <main class="rw2-content">
        <?php renderFlashMessages(); ?>
        <?= $content ?? '' ?>
    </main>

    <footer class="rw2-footer">
        <small>&copy; <?= date('Y') ?> ReliaWork2 Job Fair Management System. All rights reserved.</small>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/app.js"></script>
</body>
</html>
