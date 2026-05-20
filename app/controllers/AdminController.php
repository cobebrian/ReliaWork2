<?php
/**
 * ReliaWork2 AdminController
 */

class AdminController
{
    private UserModel $userModel;
    private JobFairRequestModel $requestModel;
    private ApplicantModel $applicantModel;

    public function __construct()
    {
        $this->userModel      = new UserModel();
        $this->requestModel   = new JobFairRequestModel();
        $this->applicantModel = new ApplicantModel();
    }

    // GET /admin/dashboard
    public function dashboard(): void
    {
        requireRole('admin');

        $stats = [
            'total_users'       => count($this->userModel->findAll()),
            'pending_approvals' => $this->userModel->countByStatus('pending'),
            'active_job_fairs'  => $this->requestModel->countApproved(),
            'total_applicants'  => $this->applicantModel->countTotal(),
        ];

        $pendingUsers = $this->userModel->findAll(['status' => 'pending']);
        $recentUsers  = array_slice($this->userModel->findAll(), 0, 10);

        $pageTitle = 'Admin Dashboard';
        $roles     = ROLES;
        $roleLabels = ROLE_LABELS;
        include VIEW_PATH . '/admin/dashboard.php';
    }

    // GET /admin/users
    public function users(): void
    {
        requireRole('admin');

        $filters = [
            'role'   => $_GET['role']   ?? '',
            'status' => $_GET['status'] ?? '',
            'search' => $_GET['search'] ?? '',
        ];

        $users      = $this->userModel->findAll($filters);
        $pageTitle  = 'User Management';
        $roles      = ROLES;
        $roleLabels = ROLE_LABELS;
        include VIEW_PATH . '/admin/users.php';
    }

    // POST /admin/users/{id}/approve
    public function approveUser(int $id): void
    {
        requireRole('admin');
        verifyCsrf();

        $role = $_POST['role'] ?? null;

        if (empty($role) || !in_array($role, ROLES, true)) {
            flash('error', 'Please select a valid role before approving.');
            redirect(APP_URL . '/admin/dashboard');
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            flash('error', 'User not found.');
            redirect(APP_URL . '/admin/dashboard');
        }

        $this->userModel->update($id, [
            'status' => 'approved',
            'role'   => $role,
        ]);

        auditLog('approve_user', 'admin', "Approved user ID {$id} with role {$role}.");
        flash('success', "User '{$user['name']}' approved with role: " . (ROLE_LABELS[$role] ?? $role));
        redirect(APP_URL . '/admin/dashboard');
    }

    // POST /admin/users/{id}/reject
    public function rejectUser(int $id): void
    {
        requireRole('admin');
        verifyCsrf();

        $user = $this->userModel->find($id);
        if (!$user) {
            flash('error', 'User not found.');
            redirect(APP_URL . '/admin/users');
        }

        $this->userModel->update($id, ['status' => 'rejected']);

        auditLog('reject_user', 'admin', "Rejected user ID {$id}.");
        flash('success', "User '{$user['name']}' has been rejected.");
        redirect(APP_URL . '/admin/users');
    }

    // POST /admin/users/{id}/role
    public function updateRole(int $id): void
    {
        requireRole('admin');
        verifyCsrf();

        $role = $_POST['role'] ?? null;

        if (empty($role) || !in_array($role, ROLES, true)) {
            flash('error', 'Invalid role selected.');
            redirect(APP_URL . '/admin/users');
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            flash('error', 'User not found.');
            redirect(APP_URL . '/admin/users');
        }

        $this->userModel->update($id, ['role' => $role]);

        auditLog('update_role', 'admin', "Updated role for user ID {$id} to {$role}.");
        flash('success', "Role updated to '" . (ROLE_LABELS[$role] ?? $role) . "' for {$user['name']}.");
        redirect(APP_URL . '/admin/users');
    }
}
