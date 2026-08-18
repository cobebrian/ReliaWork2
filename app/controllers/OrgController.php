<?php
/**
 * ReliaWork2 OrgController
 * Organization-Based RBAC (Option A — parallel system).
 *
 * Org Role → Global Role sync map (applied on invitation acceptance):
 *   punong_barangay  → barangay_captain   (gets Barangay Captain dashboard)
 *   bedo_officer     → bedo               (gets BEDO dashboard)
 *   agency           → agency             (gets Agency dashboard)
 *   organization_admin → normal_user      (Secretary manages org, keeps normal_user globally)
 *   member           → normal_user        (basic access, no legacy dashboard)
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

    public function dashboard(): void
    {
        requireRole('normal_user');
        $userId = (int)currentUser()['id'];

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

        $application = $this->db->fetch(
            "SELECT * FROM organization_applications
             WHERE applicant_user_id = ?
             ORDER BY submitted_at DESC LIMIT 1",
            [$userId]
        );

        $notifications = $this->notifModel->getUnread($userId);
        $pageTitle = 'Dashboard';
        include VIEW_PATH . '/org/dashboard.php';
    }

    // ── Secretary: Apply for Organization ────────────────────────────────────

    public function showApply(): void
    {
        requireRole('normal_user');
        $userId = (int)currentUser()['id'];

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

        $error     = getFlash('error');
        $old       = $_SESSION['old_input'] ?? [];
        unset($_SESSION['old_input']);
        $pageTitle = 'Apply for Barangay Organization';
        include VIEW_PATH . '/org/apply.php';
    }

    public function storeApply(): void
    {
        requireRole('normal_user');
        verifyCsrf();

        $userId       = (int)currentUser()['id'];
        $orgName      = trim($_POST['org_name']       ?? '');
        $barangay     = trim($_POST['barangay']        ?? '');
        $municipality = trim($_POST['municipality']    ?? '');
        $province     = trim($_POST['province']        ?? '');
        $address      = trim($_POST['address']         ?? '');
        $email        = trim($_POST['contact_email']   ?? '');
        $phone        = trim($_POST['contact_phone']   ?? '');

        if (empty($orgName) || empty($barangay)) {
            $_SESSION['old_input'] = $_POST;
            flash('error', 'Organization name and barangay are required.');
            redirect(APP_URL . '/org/apply');
        }

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
        foreach (['barangay_form','appointment_proof','barangay_certification','valid_id','other'] as $type) {
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

        $this->db->execute(
            "INSERT INTO org_application_history (org_application_id, from_status, to_status, changed_by, notes)
             VALUES (?, NULL, 'pending', ?, 'Application submitted.')",
            [$appId, $userId]
        );

        $admins = $this->db->fetchAll("SELECT id FROM users WHERE role = 'admin' AND status = 'approved'");
        $user   = $this->db->fetch("SELECT name FROM users WHERE id = ?", [$userId]);
        foreach ($admins as $a) {
            $this->notifModel->create(
                (int)$a['id'], 'org_application', 'New Organization Application',
                "{$user['name']} applied to create \"{$orgName}\" (Barangay {$barangay}).",
                APP_URL . '/admin/org-applications/' . $appId . '/review'
            );
        }

        auditLog('org_apply', 'organization_applications', "User {$userId} applied for org: {$orgName}");
        flash('success', 'Application submitted! The Super Admin will review your documents shortly.');
        redirect(APP_URL . '/org/dashboard');
    }

    public function applicationStatus(int $appId): void
    {
        requireRole('normal_user');
        $userId      = (int)currentUser()['id'];
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
            "SELECT h.*, u.name AS changed_by_name FROM org_application_history h
             LEFT JOIN users u ON u.id = h.changed_by
             WHERE h.org_application_id = ? ORDER BY h.changed_at ASC",
            [$appId]
        );
        $pageTitle = 'Application Status';
        include VIEW_PATH . '/org/application_status.php';
    }

    // ── Admin: Review Organization Applications ────────────────────────────────

    public function adminApplications(): void
    {
        requireRole('admin');
        $status = $_GET['status'] ?? 'pending';
        $sql    = "SELECT oa.*, u.name AS applicant_name, u.email AS applicant_email
                   FROM organization_applications oa
                   JOIN users u ON u.id = oa.applicant_user_id WHERE 1=1";
        $params = [];
        if ($status && $status !== 'all') { $sql .= " AND oa.status = ?"; $params[] = $status; }
        $sql .= " ORDER BY oa.submitted_at DESC";
        $applications = $this->db->fetchAll($sql, $params);
        $pageTitle    = 'Organization Applications';
        $success      = getFlash('success');
        $error        = getFlash('error');
        include VIEW_PATH . '/org/admin_applications.php';
    }

    public function adminReview(int $appId): void
    {
        requireRole('admin');
        $application = $this->db->fetch(
            "SELECT oa.*, u.name AS applicant_name, u.email AS applicant_email
             FROM organization_applications oa
             JOIN users u ON u.id = oa.applicant_user_id WHERE oa.id = ?",
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
            "SELECT h.*, u.name AS changed_by_name FROM org_application_history h
             LEFT JOIN users u ON u.id = h.changed_by
             WHERE h.org_application_id = ? ORDER BY h.changed_at ASC",
            [$appId]
        );
        $success   = getFlash('success');
        $error     = getFlash('error');
        $pageTitle = 'Review Application — ' . $application['org_name'];
        include VIEW_PATH . '/org/admin_review.php';
    }

    public function adminDecide(int $appId): void
    {
        requireRole('admin');
        verifyCsrf();

        $action  = $_POST['action'] ?? '';
        $notes   = trim($_POST['review_notes'] ?? '');
        $adminId = (int)currentUser()['id'];

        if (!in_array($action, ['approve','reject','needs_revision','under_review'])) {
            flash('error', 'Invalid action.');
            redirect(APP_URL . '/admin/org-applications/' . $appId . '/review');
        }

        $application = $this->db->fetch(
            "SELECT oa.*, u.id AS uid, u.name AS applicant_name
             FROM organization_applications oa
             JOIN users u ON u.id = oa.applicant_user_id WHERE oa.id = ?",
            [$appId]
        );
        if (!$application) {
            flash('error', 'Application not found.');
            redirect(APP_URL . '/admin/org-applications');
        }

        $statusMap = ['approve'=>'approved','reject'=>'rejected',
                      'needs_revision'=>'needs_revision','under_review'=>'under_review'];
        $newStatus = $statusMap[$action];
        $oldStatus = $application['status'];

        $pdo = $this->db->getPdo();
        $pdo->beginTransaction();
        try {
            if ($action === 'approve') {
                $this->db->execute(
                    "INSERT INTO organizations
                     (name, barangay, municipality, province, address,
                      contact_email, contact_phone, status, created_by, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?, 'active', ?, NOW())",
                    [$application['org_name'], $application['barangay'],
                     $application['municipality'], $application['province'],
                     $application['address'], $application['contact_email'],
                     $application['contact_phone'], $adminId]
                );
                $orgId = (int)$this->db->lastInsertId();

                $orgAdminRoleId = (int)$this->db->fetchColumn(
                    "SELECT id FROM org_roles WHERE name = 'organization_admin'"
                );
                $this->db->execute(
                    "INSERT INTO organization_memberships
                     (user_id, organization_id, org_role_id, status, invited_by, joined_at)
                     VALUES (?, ?, ?, 'active', ?, NOW())",
                    [$application['applicant_user_id'], $orgId, $orgAdminRoleId, $adminId]
                );
                // Secretary stays normal_user globally; org manage link appears on their dashboard

                $this->db->execute(
                    "UPDATE organization_applications
                     SET status='approved', reviewed_by=?, reviewed_at=NOW(),
                         review_notes=?, organization_id=? WHERE id=?",
                    [$adminId, $notes ?: null, $orgId, $appId]
                );
            } else {
                $this->db->execute(
                    "UPDATE organization_applications
                     SET status=?, reviewed_by=?, reviewed_at=NOW(), review_notes=? WHERE id=?",
                    [$newStatus, $adminId, $notes ?: null, $appId]
                );
            }

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

        $msgs   = [
            'approve'        => "Your org application for \"{$application['org_name']}\" was APPROVED! You are now Organization Admin.",
            'reject'         => "Your org application was rejected." . ($notes ? " Reason: {$notes}" : ''),
            'needs_revision' => "Your org application needs revision." . ($notes ? " Notes: {$notes}" : ''),
            'under_review'   => "Your org application is now under review.",
        ];
        $titles = [
            'approve'        => 'Organization Application Approved ✓',
            'reject'         => 'Organization Application Rejected',
            'needs_revision' => 'Revision Required',
            'under_review'   => 'Application Under Review',
        ];
        $this->notifModel->create(
            (int)$application['uid'], 'org_decision',
            $titles[$action] ?? 'Application Update',
            $msgs[$action]   ?? 'Your application has been updated.',
            APP_URL . '/org/dashboard'
        );

        auditLog('org_decide', 'organization_applications', "Admin {$adminId} {$action} app {$appId}.");
        flash('success', "Application {$newStatus}." .
            ($action === 'approve' ? ' Organization created.' : ''));
        redirect(APP_URL . '/admin/org-applications/' . $appId . '/review');
    }

    // ── Organization Admin: Manage Members ────────────────────────────────────

    public function manage(): void
    {
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

        $orgId   = (int)$membership['org_id'];
        $members = $this->db->fetchAll(
            "SELECT om.*, u.name, u.email, u.id AS user_id,
                    r.label AS role_label, r.name AS role_name
             FROM organization_memberships om
             JOIN users u ON u.id = om.user_id
             JOIN org_roles r ON r.id = om.org_role_id
             WHERE om.organization_id = ? ORDER BY om.joined_at ASC",
            [$orgId]
        );
        $orgRoles = $this->db->fetchAll("SELECT * FROM org_roles ORDER BY id");
        $pending  = array_filter($members, fn($m) => $m['status'] === 'invited');
        $active   = array_filter($members, fn($m) => $m['status'] === 'active');

        $success   = getFlash('success');
        $error     = getFlash('error');
        $pageTitle = 'Manage Organization — ' . $membership['org_name'];
        include VIEW_PATH . '/org/manage.php';
    }

    public function invite(): void
    {
        requireLogin();
        verifyCsrf();

        $userId     = (int)currentUser()['id'];
        $membership = $this->db->fetch(
            "SELECT om.organization_id FROM organization_memberships om
             JOIN org_roles r ON r.id = om.org_role_id
             WHERE om.user_id = ? AND r.name = 'organization_admin' AND om.status = 'active'",
            [$userId]
        );
        if (!$membership) { redirect(APP_URL . '/org/dashboard'); }

        $orgId  = (int)$membership['organization_id'];
        $email  = trim($_POST['email'] ?? '');
        $roleId = (int)($_POST['org_role_id'] ?? 0);

        if (empty($email) || !$roleId) {
            flash('error', 'Email and role are required.');
            redirect(APP_URL . '/org/manage');
        }

        $invitee = $this->db->fetch("SELECT id, name FROM users WHERE email = ?", [$email]);
        if (!$invitee) {
            flash('error', 'No user found with that email. They must register first.');
            redirect(APP_URL . '/org/manage');
        }

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

        $orgRow  = $this->db->fetch("SELECT name, barangay FROM organizations WHERE id = ?", [$orgId]);
        $roleRow = $this->db->fetch("SELECT label FROM org_roles WHERE id = ?", [$roleId]);
        $this->notifModel->create(
            (int)$invitee['id'], 'org_invite', 'Organization Invitation',
            "You have been invited to join \"{$orgRow['name']}\" (Barangay {$orgRow['barangay']}) as {$roleRow['label']}.",
            APP_URL . '/org/invitation'
        );

        flash('success', "{$invitee['name']} invited as {$roleRow['label']}. They will be notified.");
        redirect(APP_URL . '/org/manage');
    }

    public function updateMemberRole(): void
    {
        requireLogin();
        verifyCsrf();

        $userId     = (int)currentUser()['id'];
        $membership = $this->db->fetch(
            "SELECT om.organization_id FROM organization_memberships om
             JOIN org_roles r ON r.id = om.org_role_id
             WHERE om.user_id = ? AND r.name = 'organization_admin' AND om.status = 'active'",
            [$userId]
        );
        if (!$membership) { redirect(APP_URL . '/org/dashboard'); }

        $orgId     = (int)$membership['organization_id'];
        $memberId  = (int)($_POST['membership_id'] ?? 0);
        $newRoleId = (int)($_POST['org_role_id'] ?? 0);

        $mem = $this->db->fetch(
            "SELECT om.*, u.id AS user_id
             FROM organization_memberships om
             JOIN users u ON u.id = om.user_id
             WHERE om.id = ? AND om.organization_id = ?",
            [$memberId, $orgId]
        );
        $newRole = $newRoleId ? $this->db->fetch("SELECT * FROM org_roles WHERE id = ?", [$newRoleId]) : null;

        if (!$mem || !$newRole) {
            flash('error', 'Invalid request.');
            redirect(APP_URL . '/org/manage');
        }

        $this->db->execute(
            "UPDATE organization_memberships SET org_role_id = ?, updated_at = NOW() WHERE id = ?",
            [$newRoleId, $memberId]
        );

        // Sync global role → existing dashboard access
        $this->syncGlobalRole((int)$mem['user_id'], $newRole['name']);

        flash('success', "Role updated to \"{$newRole['label']}\". Member's dashboard access has been updated.");
        redirect(APP_URL . '/org/manage');
    }

    public function removeMember(): void
    {
        requireLogin();
        verifyCsrf();

        $userId     = (int)currentUser()['id'];
        $membership = $this->db->fetch(
            "SELECT om.organization_id FROM organization_memberships om
             JOIN org_roles r ON r.id = om.org_role_id
             WHERE om.user_id = ? AND r.name = 'organization_admin' AND om.status = 'active'",
            [$userId]
        );
        if (!$membership) { redirect(APP_URL . '/org/dashboard'); }

        $orgId    = (int)$membership['organization_id'];
        $memberId = (int)($_POST['membership_id'] ?? 0);

        // Get member's user_id before deactivating
        $mem = $this->db->fetch(
            "SELECT om.user_id FROM organization_memberships om
             WHERE om.id = ? AND om.organization_id = ?",
            [$memberId, $orgId]
        );

        $this->db->execute(
            "UPDATE organization_memberships SET status = 'inactive', updated_at = NOW()
             WHERE id = ? AND organization_id = ?",
            [$memberId, $orgId]
        );

        // Revert global role to normal_user on removal
        if ($mem) {
            $this->syncGlobalRole((int)$mem['user_id'], 'member');
        }

        flash('success', 'Member removed and access reverted to Normal User.');
        redirect(APP_URL . '/org/manage');
    }

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
        $success   = getFlash('success');
        $pageTitle = 'Organization Invitations';
        include VIEW_PATH . '/org/invitation.php';
    }

    public function respondInvitation(int $membershipId): void
    {
        requireLogin();
        verifyCsrf();

        $userId = (int)currentUser()['id'];
        $action = $_POST['action'] ?? 'accept';

        $mem = $this->db->fetch(
            "SELECT om.*, r.name AS role_name, o.name AS org_name
             FROM organization_memberships om
             JOIN org_roles r ON r.id = om.org_role_id
             JOIN organizations o ON o.id = om.organization_id
             WHERE om.id = ? AND om.user_id = ? AND om.status = 'invited'",
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
            // KEY: sync global role so the user immediately gets the right dashboard
            $this->syncGlobalRole($userId, $mem['role_name']);

            $globalRole = $this->getGlobalRoleFor($mem['role_name']);
            flash('success', "You have joined \"{$mem['org_name']}\"! Your dashboard has been updated to " .
                (ROLE_LABELS[$globalRole] ?? ucfirst($globalRole)) . ".");

            // Redirect to their new dashboard
            redirect(roleDashboardUrl($globalRole));
        } else {
            $this->db->execute(
                "UPDATE organization_memberships SET status = 'inactive', updated_at = NOW() WHERE id = ?",
                [$membershipId]
            );
            flash('info', 'Invitation declined.');
            redirect(APP_URL . '/org/dashboard');
        }
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Map org role name to global users.role and update the DB + session.
     */
    private function syncGlobalRole(int $userId, string $orgRoleName): void
    {
        $globalRole = $this->getGlobalRoleFor($orgRoleName);

        $this->db->execute(
            "UPDATE users SET role = ?, status = 'approved' WHERE id = ?",
            [$globalRole, $userId]
        );

        // If this is the currently logged-in user, update session immediately
        if ((int)(currentUser()['id'] ?? 0) === $userId) {
            $updatedUser = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
            if ($updatedUser) $_SESSION['user'] = $updatedUser;
        }

        auditLog('rbac_sync', 'users',
            "Org role '{$orgRoleName}' synced to global role '{$globalRole}' for user {$userId}.");
    }

    private function getGlobalRoleFor(string $orgRoleName): string
    {
        return match($orgRoleName) {
            'punong_barangay'    => 'barangay_captain',
            'bedo_officer'       => 'bedo',
            'agency'             => 'agency',
            'organization_admin' => 'normal_user',
            default              => 'normal_user',
        };
    }
}
