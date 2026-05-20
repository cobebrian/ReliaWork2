<?php
/**
 * ReliaWork2 BarangayCaptainController
 */

class BarangayCaptainController
{
    private JobFairRequestModel $requestModel;
    private ScheduleModel $scheduleModel;

    public function __construct()
    {
        $this->requestModel  = new JobFairRequestModel();
        $this->scheduleModel = new ScheduleModel();
    }

    // GET /barangay-captain/dashboard
    public function dashboard(): void
    {
        requireRole('barangay_captain');

        $userId   = currentUser()['id'];
        $myRequests = $this->requestModel->findAll(['requested_by' => $userId]);

        $stats = [
            'total'    => count($myRequests),
            'pending'  => count(array_filter($myRequests, fn($r) => $r['status'] === 'pending')),
            'approved' => count(array_filter($myRequests, fn($r) => $r['status'] === 'approved')),
            'rejected' => count(array_filter($myRequests, fn($r) => $r['status'] === 'rejected')),
        ];

        $recentRequests = array_slice($myRequests, 0, 5);
        $pageTitle = 'Barangay Captain Dashboard';
        include VIEW_PATH . '/barangay_captain/dashboard.php';
    }

    // GET /barangay-captain/create-request
    public function createRequest(): void
    {
        requireRole('barangay_captain');

        $pageTitle = 'Create Job Fair Request';
        $error     = getFlash('error');
        $old       = $_SESSION['old_input'] ?? [];
        unset($_SESSION['old_input']);
        include VIEW_PATH . '/barangay_captain/create_request.php';
    }

    // POST /barangay-captain/store-request
    public function storeRequest(): void
    {
        requireRole('barangay_captain');
        verifyCsrf();

        $title       = trim($_POST['title'] ?? '');
        $requestedDate = trim($_POST['requested_date'] ?? '');
        $venue       = trim($_POST['venue'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($title) || empty($requestedDate)) {
            $_SESSION['old_input'] = $_POST;
            flash('error', 'Title and requested date are required.');
            redirect(APP_URL . '/barangay-captain/create-request');
        }

        // Validate date is not in the past
        if (strtotime($requestedDate) < strtotime('today')) {
            $_SESSION['old_input'] = $_POST;
            flash('error', 'Requested date cannot be in the past.');
            redirect(APP_URL . '/barangay-captain/create-request');
        }

        // Check if date is already booked
        if ($this->scheduleModel->isDateBooked($requestedDate)) {
            $_SESSION['old_input'] = $_POST;
            flash('error', 'The selected date is already booked. Please choose a different date.');
            redirect(APP_URL . '/barangay-captain/create-request');
        }

        $id = $this->requestModel->create([
            'title'          => $title,
            'requested_date' => $requestedDate,
            'venue'          => $venue ?: null,
            'description'    => $description ?: null,
            'requested_by'   => currentUser()['id'],
        ]);

        auditLog('create_request', 'job_fair_requests', "Created job fair request ID {$id}: {$title}.");
        flash('success', 'Job fair request submitted successfully. Awaiting review.');
        redirect(APP_URL . '/barangay-captain/my-requests');
    }

    // GET /barangay-captain/my-requests
    public function myRequests(): void
    {
        requireRole('barangay_captain');

        $userId   = currentUser()['id'];
        $requests = $this->requestModel->findAll(['requested_by' => $userId]);
        $pageTitle = 'My Job Fair Requests';
        include VIEW_PATH . '/barangay_captain/my_requests.php';
    }

    // GET /api/check-date?date=YYYY-MM-DD  (AJAX endpoint)
    public function checkDate(): void
    {
        requireRole('barangay_captain');

        header('Content-Type: application/json');
        $date = trim($_GET['date'] ?? '');

        if (empty($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            echo json_encode(['available' => false, 'message' => 'Invalid date format.']);
            exit;
        }

        $isBooked = $this->scheduleModel->isDateBooked($date);
        echo json_encode([
            'available' => !$isBooked,
            'message'   => $isBooked ? 'This date is already booked.' : 'Date is available.',
        ]);
        exit;
    }

    // GET /api/booked-dates  — returns all booked dates as JSON array
    public function bookedDates(): void
    {
        // Any logged-in user can fetch booked dates (needed for calendar display)
        if (!isLoggedIn()) {
            header('Content-Type: application/json');
            echo json_encode(['booked' => []]);
            exit;
        }
        header('Content-Type: application/json');
        $dates = $this->scheduleModel->getBookedDates();
        echo json_encode(['booked' => array_values($dates)]);
        exit;
    }
}
