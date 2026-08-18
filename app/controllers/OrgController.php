<?php
/**
 * ReliaWork2 OrgController
 * Handles the organization-based RBAC workflow:
 *  1. Normal User dashboard — shows status + apply-for-org button
 *  2. Secretary applies to create a Barangay Organization
 *  3. Super Admin (admin) reviews/approves/rejects applications
 *  4. On approval → Secretary becomes Organization Admin
 *  5. Organization Admin manages members + assigns org roles
 */

class OrgController
{
    private Database $db;
    private NotificationModel $notifModel;

    public function __construct()
    {
        $this->db       = Database::getInstance();
        $this->notifModel = new NotificationModel();
    }

    // ── Normal User Dashboard ─────────────────────────────────────────────────

    // GET /org/dashboard
    public function dashboard(): void
    {
        requireRole('normal_user');
        $userId = (int)currentUser()['id'];

        // Check if user has an active org membership
        $membership = $this->db->fetch(
            "SELECT om.*, o.name AS org_name, o.barangay,
                    r.label AS role_label, r.name AS role_name
             FROM organization_memberships om
             JOIN organizations o ON o.id = om.organization_id
             JOIN org_roles r ON r.id = om.org_role_id
             WHERE om.user_id = ? AND om.status = 'active'
             LIMIT 1",
            [$userId]
        );

        // Check pending org application
        $application = $this->db->fetch(
            "SELECT * FROM organization_applications
             WHERE applicant_user_id = ?
             ORDER BY submitted_at DESC LIMIT 1",
            [$userId]
        );

        // Unread notifications
        $notifications = $this->notifModel->getUnread($userId);

        $pageTitle = 'Dashboard';
        include VIEW_PATH . '/org/dashboard.php';
    }

    // ── Secretary: Apply for Organization ────────────────────────────────────

    // GET /org/apply
    public function showApply(): void
    {
        requireRole('normal_user');
        $userId = (int)currentUser()['id'];

        // Block if already has approved/pending application
        $existing = $this->db->fetch(
            "SELECT * FROM organization_applications
             WHERE applicant_user_id = ? AND status IN ('pending','under_review','approved')
             LIMIT 1",
            [$userId]
        );
        if ($existing) {
            flash('info', 'You already have an active or pending organization application.');
            redirect(APP_URL . '/org/dashboard');
        }

        $error   = getFlash('error');
        $old     = $_SESSION['old_input'] ?? [];
        unset($_SESSION['old_input']);
        $pageTitle = 'Apply for Barangay Organization';
        include VIEW_PATH . '/org/apply.php';
    }

    // POST /org/apply
    public function storeApply(): void
    {
        requireRole('normal_user');
        verifyCsrf();

        $userId = (int)currentUser()['id'];

        $orgName      = trim($_POST['org_name'] ?? '');
        $barangay     = trim($_POST['barangay'] ?? '');
        $municipality = trim($_POST['municipality'] ?? '');
        $province     = trim($_POST['province'] ?? '');
        $address      = trim($_POST['address'] ?? '');
        $email        = trim($_POST['contact_email'] ?? '');
        $phone        = trim($_POST['contact_phone'] ?? '');

        if (empty($orgName) || empty($barangay)) {
            $_SESSION['old_input'] = $_POST;
            flash('error', 'Organization name and barangay are required.');
            redirect(APP_URL . '/org/apply');
        }

        // Create the application
        $this->db->execute(
            "INSERT INTO organization_applications
             (applicant_user_id, org_name, barangay, municipality, province,
              address, contact_email, contact_phone, status, submitted_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())",
            [$userId, $orgName, $barangay, $municipality ?: null,
             $province ?: null, $address ?: null, $email ?: null, $phone ?: null]
        );
        $appId = (int)$this->db->lastInsertId();

        // Upload supporting documents
        $uploadDir  = PUBLIC_PATH . '/uploads/org-docs/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $allowedExt = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        $docTypes   = ['barangay_form', 'appointment_proof', 'barangay_certification', 'valid_id', 'other'];

        foreach ($docTypes as $type) {
            if (empty($_FILES[$type]['name'])) continue;
            $file     = $_FILES[$type];
            $origName = basename($file['name']);
            $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExt) || $file['size'] > 5*1024*1024) continue;
            $stored = 'org_' . $appId . '_' . $type . '_' . time() . '.' . $ext;
            if (!move_uploaded_file($file['tmp_name'], $uploadDir . $stored)) continue;
            $this->db->execute(
                "INSERT INTO org_application_documents
                 (org_application_id, doc_type, original_name, stored_name, file_path, file_size, mime_type)
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$appId, $type, $origName, $stored, '/uploads/org-docs/' . $stored,
                 $file['size'], $file['type']]
            );
        }

        // Log history
        $this->db->execute(
            "INSERT INTO org_application_history (org_application_id, from_status, to_status, changed_by, notes)
             VALUES (?, NULL, 'pending', ?, 'Application submitted.')",
            [$appId, $userId]
        );

        // Notify admins
        $admins = $this->db->fetchAll("SELECT id FROM users WHERE role = 'admin' AND status = 'approved'");
        $user   = $this->db->fetch("SELECT name FROM users WHERE id = ?", [$userId]);
        foreach ($admins as $a) {
            $this->notifModel->create(
                (int)$a['id'],
                'org_application',
                'New Organization Application',
                "{$user['name']} has applied to create organization \"{$orgName}\" (Barangay {$barangay}).",
                APP_URL . '/admin/org-applications/' . $appId . '/review'
            );
        }

        auditLog('org_apply', 'organization_applications', "User {$userId} applied for org: {$orgName}");
        flash('success', 'Application submitted! The Super Admin will review your documents shortly.');
        redirect(APP_URL . '/org/dashboard');
    }

    // GET /org/application/{id}/status — applicant views their own application status
    public function applicationStatus(int $appId): void
    {
        requireRole('normal_user');
        $userId = (int)currentUser()['id'];

        $application = $this->db->fetch(
            "SELECT * FROM organization_applications WHERE id = ? AND applicant_user_id = ?",
            [$appId, $userId]
        );
        if (!$application) {
            flash('error', 'Application not found.');
            redirect(APP_URL . '/org/dashboard');
        }

        $documents = $this->db->fetchAll(
            "SELECT * FROM org_application_documents WHERE org_application_id = ? ORDER BY doc_type",
            [$appId]
        );
        $history = $this->db->fetchAll(
            "SELECT h.*, u.name AS changed_by_name
             FROM org_application_history h
             LEFT JOIN users u ON u.id = h.changed_by
             WHERE h.org_application_id = ? ORDER BY h.changed_at ASC",
            [$appId]
        );

        $pageTitle = 'Application Status';
        include VIEW_PATH . '/org/application_status.php';
    }

    // ── Admin: Review Organization Applications ────────────────────────────────

    // GET /admin/org-applications
    public function adminApplications(): void
    {
        requireRole('admin');
        $status = $_GET['status'] ?? 'pending';

        $sql = "SELECT oa.*, u.name AS applicant_name, u.email AS applicant_email
                FROM organization_applications oa
                JOIN users u ON u.id = oa.applicant_user_id
                WHERE 1=1";
        $params = [];
        if ($status && $status !== 'all') {
            $sql .= " AND oa.status = ?"; $params[] = $status;
        }
        $sql .= " ORDER BY oa.submitted_at DESC";

        $applications = $this->db->fetchAll($sql, $params);
        $pageTitle    = 'Organization Applications';
        $success      = getFlash('success');
        $error        = getFlash('error');
        include VIEW_PATH . '/org/admin_applications.php';
    }

    // GET /admin/org-applications/{id}/review
    public function adminReview(int $appId): void
    {
        requireRole('admin');

        $application = $this->db->fetch(
            "SELECT oa.*, u.name AS applicant_name, u.email AS applicant_email
             FROM organization_applications oa
             JOIN users u ON u.id = oa.applicant_user_id
             WHERE oa.id = ?",
            [$appId]
        );
        if (!$application) {
            flash('error', 'Application not found.');
            redirect(APP_URL . '/admin/org-applications');
        }

        $documents = $this->db->fetchAll(
            "SELECT * FROM org_application_documents WHERE org_application_id = ? ORDER BY doc_type",
            [$appId]
        );
        $history = $this->db->fetchAll(
            "SELECT h.*, u.name AS changed_by_name
             FROM org_application_history h
             LEFT JOIN users u ON u.id = h.changed_by
             WHERE h.org_application_id = ? ORDER BY h.changed_at ASC",
            [$appId]
        );

        $success = getFlash('success');
        $error   = getFlash('error');
        $pageTitle = 'Review Application — ' . $application['org_name'];
        include VIEW_PATH . '/org/admin_review.php';
    }

    // POST /admin/org-applications/{id}/decide
    public function adminDecide(int $appId): void
    {
        requireRole('admin');
        verifyCsrf();

        $action = $_POST['action'] ?? '';  // approve | reject | needs_revision | under_review
        $notes  = trim($_POST['review_notes'] ?? '');
        $adminId = (int)currentUser()['id'];

        $validActions = ['approve', 'reject', 'needs_revision', 'under_review'];
        if (!in_array($action, $validActions)) {
            flash('error', 'Invalid action.');
            redirect(APP_URL . '/admin/org-applications/' . $appId . '/review');
        }

        $application = $this->db->fetch(
            "SELECT oa.*, u.id AS uid, u.name AS applicant_name
             FROM organization_applications oa
             JOIN users u ON u.id = oa.applicant_user_id
             WHERE oa.id = ?",
            [$appId]
        );
        if (!$application) {
            flash('error', 'Application not found.');
            redirect(APP_URL . '/admin/org-applications');
        }

        $statusMap = [
            'approve'        => 'approved',
            'reject'         => 'rejected',
            'needs_revision' => 'needs_revision',
            'under_review'   => 'under_review',
        ];
        $newStatus = $statusMap[$action];
        $oldStatus = $application['status'];

        $pdo = $this->db->getPdo();
        $pdo->beginTransaction();
        try {
            $orgId = null;

            if ($action === 'approve') {
                // 1. Create the organization
                $this->db->execute(
                    "INSERT INTO organizations
                     (name, barangay, municipality, province, address,
                      contact_email, contact_phone, status, created_by, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?, 'active', ?, NOW())",
                    [
                        $application['org_name'],
                        $application['barangay'],
                        $application['municipality'],
                        $application['province'],
                        $application['address'],
                        $application['contact_email'],
                        $application['contact_phone'],
                        $adminId,
                    ]
                );
                $orgId = (int)$this->db->lastInsertId();

                // 2. Get organization_admin role ID
                $orgAdminRoleId = (int)$this->db->fetchColumn(
                    "SELECT id FROM org_roles WHERE name = 'organization_admin'"
                );

                // 3. Create membership — applicant becomes Organization Admin
                $this->db->execute(
                    "INSERT INTO organization_memberships
                     (user_id, organization_id, org_role_id, status, invited_by, joined_at)
                     VALUES (?, ?, ?, 'active', ?, NOW())",
                    [$application['applicant_user_id'], $orgId, $orgAdminRoleId, $adminId]
                );

                // 4. Update application with organization_id
                $this->db->execute(
                    "UPDATE organization_applications
                     SET status = 'approved', reviewed_by = ?, reviewed_at = NOW(),
                         review_notes = ?, organization_id = ?
                     WHERE id = ?",
                    [$adminId, $notes ?: null, $orgId, $appId]
                );
            } else {
                $this->db->execute(
                    "UPDATE organization_applications
                     SET status = ?, reviewed_by = ?, reviewed_at = NOW(), review_notes = ?
                     WHERE id = ?",
                    [$newStatus, $adminId, $notes ?: null, $appId]
                );
            }

            // Log history
            $this->db->execute(
                "INSERT INTO org_application_history
                 (org_application_id, from_status, to_status, changed_by, notes, changed_at)
                 VALUES (?, ?, ?, ?, ?, NOW())",
                [$appId, $oldStatus, $newStatus, $adminId, $notes ?: 'Admin decision.']
            );

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            flash('error', 'Operation failed: ' . $e->getMessage());
            redirect(APP_URL . '/admin/org-applications/' . $appId . '/review');
        }

        // Notify the applicant
        $msgs = [
            'approved'       => "Your organization application for \"{$application['org_name']}\" has been APPROVED! You are now the Organization Admin.",
            'rejected'       => "Your organization application for \"{$application['org_name']}\" was rejected." . ($notes ? " Reason: {$notes}" : ''),
            'needs_revision' => "Your organization application needs revision." . ($notes ? " Notes: {$notes}" : ''),
            'under_review'   => "Your organization application is now under review.",
        ];
        $titles = [
            'approved'       => 'Organization Application Approved ✓',
            'rejected'       => 'Organization Application Rejected',
            'needs_revision' => 'Revision Required for Your Application',
            'under_review'   => 'Application Under Review',
        ];
        $this->notifModel->create(
            (int)$application['uid'],
            'org_decision',
            $titles[$action],
            $msgs[$action],
            APP_URL . '/org/dashboard'
        );

        auditLog('org_decide', 'organization_applications',
            "Admin {$adminId} {$action}d org application {$appId}.");
        flash('success', "Application {$newStatus}." .
            ($action === 'approve' ? " Organization created and applicant is now Organization Admin." : ''));
        redirect(APP_URL . '/admin/org-applications/' . $appId . '/review');
    }

    // ── Organization Admin: Manage Members ────────────────────────────────────

    // GET /org/manage
    public function manage(): void
    {
        // Allow organization_admin level users (they keep normal_user global role
        // but have organization_admin org_role)
        requireLogin();
        $userId = (int)currentUser()['id'];

        $membership = $this->db->fetch(
            "SELECT om.*, o.name AS org_name, o.barangay, o.id AS org_id
             FROM organization_memberships om
             JOIN organizations o ON o.id = om.organization_id
             JOIN org_roles r ON r.id = om.org_role_id
             WHERE om.user_id = ? AND r.name = 'organization_admin' AND om.status = 'active'",
            [$userId]
        );
        if (!$membership) {
            flash('error', 'You must be an Organization Admin to access this page.');
            redirect(APP_URL . '/org/dashboard');
        }

        $orgId = (int)$membership['org_id'];
        $members = $this->db->fetchAll(
            "SELECT om.*, u.name, u.email, r.label AS role_label, r.name AS role_name
             FROM organization_memberships om
             JOIN users u ON u.id = om.user_id
             JOIN org_roles r ON r.id = om.org_role_id
             WHERE om.organization_id = ?
             ORDER BY om.joined_at ASC",
            [$orgId]
        );
        $orgRoles = $this->db->fetchAll("SELECT * FROM org_roles ORDER BY id");

        // Pending member invitations
        $pending = array_filter($members, fn($m) => $m['status'] === 'invited');
        $active  = array_filter($members, fn($m) => $m['status'] === 'active');

        $success   = getFlash('success');
        $error     = getFlash('error');
        $pageTitle = 'Manage Organization — ' . $membership['org_name'];
        include VIEW_PATH . '/org/manage.php';
    }

    // POST /org/manage/invite
    public function invite(): void
    {
        requireLogin();
        verifyCsrf();

        $userId = (int)currentUser()['id'];
        $membership = $this->db->fetch(
            "SELECT om.organization_id FROM organization_memberships om
             JOIN org_roles r ON r.id = om.org_role_id
             WHERE om.user_id = ? AND r.name = 'organization_admin' AND om.status = 'active'",
            [$userId]
        );
        if (!$membership) { redirect(APP_URL . '/org/dashboard'); }

        $orgId     = (int)$membership['organization_id'];
        $email     = trim($_POST['email'] ?? '');
        $roleId    = (int)($_POST['org_role_id'] ?? 0);

        if (empty($email) || !$roleId) {
            flash('error', 'Email and role are required.');
            redirect(APP_URL . '/org/manage');
        }

        // Find user by email
        $invitee = $this->db->fetch("SELECT id, name, role FROM users WHERE email = ?", [$email]);
        if (!$invitee) {
            flash('error', 'No user found with that email. They must register first.');
            redirect(APP_URL . '/org/manage');
        }

        // Check already a member
        $existing = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM organization_memberships WHERE user_id = ? AND organization_id = ?",
            [(int)$invitee['id'], $orgId]
        );
        if ($existing) {
            flash('error', 'This user is already a member of your organization.');
            redirect(APP_URL . '/org/manage');
        }

        $this->db->execute(
            "INSERT INTO organization_memberships
             (user_id, organization_id, org_role_id, status, invited_by, joined_at)
             VALUES (?, ?, ?, 'invited', ?, NOW())",
            [(int)$invitee['id'], $orgId, $roleId, $userId]
        );

        $orgRow = $this->db->fetch("SELECT name, barangay FROM organizations WHERE id = ?", [$orgId]);
        $roleRow = $this->db->fetch("SELECT label FROM org_roles WHERE id = ?", [$roleId]);
        $this->notifModel->create(
            (int)$invitee['id'],
            'org_invite',
            'Organization Invitation',
            "You have been invited to join \"{$orgRow['name']}\" (Barangay {$orgRow['barangay']}) as {$roleRow['label']}.",
            APP_URL . '/org/invitation'
        );

        flash('success', "{$invitee['name']} invited as {$roleRow['label']}.");
        redirect(APP_URL . '/org/manage');
    }

    // POST /org/manage/update-role
    public function updateMemberRole(): void
    {
        requireLogin();
        verifyCsrf();

        $userId = (int)currentUser()['id'];
        $membership = $this->db->fetch(
            "SELECT om.organization_id FROM organization_memberships om
             JOIN org_roles r ON r.id = om.org_role_id
             WHERE om.user_id = ? AND r.name = 'organization_admin' AND om.status = 'active'",
            [$userId]
        );
        if (!$membership) { redirect(APP_URL . '/org/dashboard'); }

        $orgId      = (int)$membership['organization_id'];
        $memberId   = (int)($_POST['membership_id'] ?? 0);
        $newRoleId  = (int)($_POST['org_role_id'] ?? 0);

        // Verify this membership belongs to our org
        $mem = $this->db->fetch(
            "SELECT * FROM organization_memberships WHERE id = ? AND organization_id = ?",
            [$memberId, $orgId]
        );
        if (!$mem || !$newRoleId) {
            flash('error', 'Invalid request.');
            redirect(APP_URL . '/org/manage');
        }

        $this->db->execute(
            "UPDATE organization_memberships SET org_role_id = ?, updated_at = NOW() WHERE id = ?",
            [$newRoleId, $memberId]
        );
        flash('success', 'Role updated successfully.');
        redirect(APP_URL . '/org/manage');
    }

    // POST /org/manage/remove
    public function removeMember(): void
    {
        requireLogin();
        verifyCsrf();

        $userId = (int)currentUser()['id'];
        $membership = $this->db->fetch(
            "SELECT om.organization_id FROM organization_memberships om
             JOIN org_roles r ON r.id = om.org_role_id
             WHERE om.user_id = ? AND r.name = 'organization_admin' AND om.status = 'active'",
            [$userId]
        );
        if (!$membership) { redirect(APP_URL . '/org/dashboard'); }

        $orgId    = (int)$membership['organization_id'];
        $memberId = (int)($_POST['membership_id'] ?? 0);

        $this->db->execute(
            "UPDATE organization_memberships SET status = 'inactive', updated_at = NOW()
             WHERE id = ? AND organization_id = ?",
            [$memberId, $orgId]
        );
        flash('success', 'Member removed.');
        redirect(APP_URL . '/org/manage');
    }

    // GET /org/invitation — member accepts/declines invitation
    public function invitation(): void
    {
        requireLogin();
        $userId = (int)currentUser()['id'];

        $invitations = $this->db->fetchAll(
            "SELECT om.*, o.name AS org_name, o.barangay, r.label AS role_label
             FROM organization_memberships om
             JOIN organizations o ON o.id = om.organization_id
             JOIN org_roles r ON r.id = om.org_role_id
             WHERE om.user_id = ? AND om.status = 'invited'",
            [$userId]
        );

        $success = getFlash('success');
        $pageTitle = 'Organization Invitations';
        include VIEW_PATH . '/org/invitation.php';
    }

    // POST /org/invitation/{id}/respond
    public function respondInvitation(int $membershipId): void
    {
        requireLogin();
        verifyCsrf();

        $userId = (int)currentUser()['id'];
        $action = $_POST['action'] ?? 'accept'; // accept | decline

        $mem = $this->db->fetch(
            "SELECT * FROM organization_memberships WHERE id = ? AND user_id = ? AND status = 'invited'",
            [$membershipId, $userId]
        );
        if (!$mem) {
            flash('error', 'Invitation not found.');
            redirect(APP_URL . '/org/invitation');
        }

        if ($action === 'accept') {
            $this->db->execute(
                "UPDATE organization_memberships SET status = 'active', updated_at = NOW() WHERE id = ?",
                [$membershipId]
            );
            flash('success', 'You have joined the organization!');
        } else {
            $this->db->execute(
                "UPDATE organization_memberships SET status = 'inactive', updated_at = NOW() WHERE id = ?",
                [$membershipId]
            );
            flash('info', 'Invitation declined.');
        }
        redirect(APP_URL . '/org/dashboard');
    }
}
