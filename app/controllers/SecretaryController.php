<?php
/**
 * ReliaWork2 SecretaryController
 */

class SecretaryController
{
    private ResourceModel $resourceModel;
    private JobFairRequestModel $requestModel;
    private NotificationModel $notificationModel;

    public function __construct()
    {
        $this->resourceModel = new ResourceModel();
        $this->requestModel  = new JobFairRequestModel();
        $this->notificationModel = new NotificationModel();
    }

    // GET /secretary/requests
    public function requests(): void
    {
        requireRole('secretary');

        $requests  = $this->requestModel->findAll(['status' => 'approved']);
        $pageTitle = 'Job Fair Requests (Confirm Details)';
        include VIEW_PATH . '/secretary/requests.php';
    }

    // GET /secretary/requests/{id}/confirm
    public function confirmRequestForm(int $id): void
    {
        requireRole('secretary');

        $request = $this->requestModel->find($id);
        if (!$request) {
            flash('error', 'Job fair request not found.');
            redirect(APP_URL . '/secretary/requests');
        }

        $pageTitle = 'Confirm Details - ' . $request['title'];
        include VIEW_PATH . '/secretary/confirm_request.php';
    }

    // POST /secretary/requests/{id}/confirm
    public function confirmRequest(int $id): void
    {
        requireRole('secretary');
        verifyCsrf();

        $request = $this->requestModel->find($id);
        if (!$request) {
            flash('error', 'Job fair request not found.');
            redirect(APP_URL . '/secretary/requests');
        }

        $date_time   = trim($_POST['date_time'] ?? '');
        $recipient   = trim($_POST['recipient'] ?? '');
        $mobile_no   = trim($_POST['mobile_no'] ?? '');
        $remarks     = trim($_POST['remarks'] ?? '');
        $signature   = trim($_POST['signature'] ?? '');

        if (empty($date_time) || empty($recipient) || empty($mobile_no)) {
            flash('error', 'Please provide date/time, recipient and mobile number.');
            redirect(APP_URL . '/secretary/requests/' . $id . '/confirm');
        }

        // Save confirmation details into remarks field as structured text
        $confirmText = "Confirmed by Secretary (user_id=" . currentUser()['id'] . ")\n" .
                       "Date/Time: {$date_time}\n" .
                       "Recipient: {$recipient}\n" .
                       "Mobile: {$mobile_no}\n" .
                       "Signature: {$signature}\n" .
                       "Remarks: {$remarks}\n";

        $this->requestModel->update($id, [
            'status' => 'confirmed',
            'remarks' => $confirmText,
            'reviewed_by' => currentUser()['id'],
        ]);

        // Notify the requesting barangay captain (requested_by)
        if (!empty($request['requested_by'])) {
            $title = 'Request details confirmed';
            $message = "Secretary confirmed details for '{$request['title']}'. Please provide your remarks.";
            $link = APP_URL . '/barangay-captain/my-requests';
            $this->notificationModel->create((int)$request['requested_by'], 'info', $title, $message, $link);
        }

        auditLog('confirm_request', 'job_fair_requests', "Secretary confirmed details for request ID {$id}.");
        flash('success', 'Details confirmed and barangay captain notified for remarks.');
        redirect(APP_URL . '/secretary/requests');
    }

    // GET /secretary/dashboard
    public function dashboard(): void
    {
        requireRole('secretary');

        $resources = $this->resourceModel->findAll();
        $stats = [
            'total_resources'     => count($resources),
            'available_resources' => $this->resourceModel->countAvailable(),
            'approved_fairs'      => $this->requestModel->countApproved(),
        ];

        $pageTitle = 'Secretary Dashboard';
        include VIEW_PATH . '/secretary/dashboard.php';
    }

    // GET /secretary/resources
    public function resources(): void
    {
        requireRole('secretary');

        $resources = $this->resourceModel->findAll();
        $requests  = $this->requestModel->findAll(['status' => 'approved']);
        $pageTitle = 'Barangay Resources';
        $error     = getFlash('error');
        $success   = getFlash('success');
        $editResource = null;

        if (!empty($_GET['edit'])) {
            $editResource = $this->resourceModel->find((int)$_GET['edit']);
        }

        include VIEW_PATH . '/secretary/resources.php';
    }

    // POST /secretary/resources/store
    public function storeResource(): void
    {
        requireRole('secretary');
        verifyCsrf();

        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $quantity    = (int)($_POST['quantity'] ?? 0);
        $unit        = trim($_POST['unit'] ?? '');
        $status      = $_POST['status'] ?? 'available';

        if (empty($name)) {
            flash('error', 'Resource name is required.');
            redirect(APP_URL . '/secretary/resources');
        }

        if (!in_array($status, ['available', 'unavailable'], true)) {
            $status = 'available';
        }

        $this->resourceModel->create([
            'name'        => $name,
            'description' => $description ?: null,
            'quantity'    => max(0, $quantity),
            'unit'        => $unit ?: null,
            'status'      => $status,
        ]);

        auditLog('create_resource', 'resources', "Created resource: {$name}.");
        flash('success', "Resource '{$name}' added successfully.");
        redirect(APP_URL . '/secretary/resources');
    }

    // POST /secretary/resources/{id}/update
    public function updateResource(int $id): void
    {
        requireRole('secretary');
        verifyCsrf();

        $resource = $this->resourceModel->find($id);
        if (!$resource) {
            flash('error', 'Resource not found.');
            redirect(APP_URL . '/secretary/resources');
        }

        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $quantity    = (int)($_POST['quantity'] ?? 0);
        $unit        = trim($_POST['unit'] ?? '');
        $status      = $_POST['status'] ?? 'available';

        if (empty($name)) {
            flash('error', 'Resource name is required.');
            redirect(APP_URL . '/secretary/resources?edit=' . $id);
        }

        if (!in_array($status, ['available', 'unavailable'], true)) {
            $status = 'available';
        }

        $this->resourceModel->update($id, [
            'name'        => $name,
            'description' => $description ?: null,
            'quantity'    => max(0, $quantity),
            'unit'        => $unit ?: null,
            'status'      => $status,
        ]);

        auditLog('update_resource', 'resources', "Updated resource ID {$id}: {$name}.");
        flash('success', "Resource '{$name}' updated successfully.");
        redirect(APP_URL . '/secretary/resources');
    }

    // POST /secretary/resources/allocate
    public function allocateResource(): void
    {
        requireRole('secretary');
        verifyCsrf();

        $requestId  = (int)($_POST['job_fair_request_id'] ?? 0);
        $resourceId = (int)($_POST['resource_id'] ?? 0);
        $quantity   = (int)($_POST['quantity_allocated'] ?? 1);
        $notes      = trim($_POST['notes'] ?? '');

        if (!$requestId || !$resourceId) {
            flash('error', 'Job fair and resource are required.');
            redirect(APP_URL . '/secretary/resources');
        }

        $resource = $this->resourceModel->find($resourceId);
        if (!$resource) {
            flash('error', 'Resource not found.');
            redirect(APP_URL . '/secretary/resources');
        }

        if ($quantity > $resource['quantity']) {
            flash('error', "Cannot allocate {$quantity} {$resource['unit']}. Only {$resource['quantity']} available.");
            redirect(APP_URL . '/secretary/resources');
        }

        $this->resourceModel->allocate([
            'job_fair_request_id' => $requestId,
            'resource_id'         => $resourceId,
            'quantity_allocated'  => max(1, $quantity),
            'notes'               => $notes ?: null,
            'allocated_by'        => currentUser()['id'],
        ]);

        auditLog('allocate_resource', 'resources', "Allocated {$quantity} of resource ID {$resourceId} to request ID {$requestId}.");
        flash('success', "Resource allocated successfully.");
        redirect(APP_URL . '/secretary/resources');
    }
}
