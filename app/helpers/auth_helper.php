<?php
/**
 * ReliaWork2 Auth & Utility Helpers
 */

/**
 * Check if a user is logged in.
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get the current logged-in user array.
 */
function currentUser(): array|null
{
    if (!isLoggedIn()) {
        return null;
    }
    return $_SESSION['user'] ?? null;
}

/**
 * Redirect to login if not authenticated.
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        flash('error', 'Please log in to access that page.');
        redirect(APP_URL . '/login');
    }
}

/**
 * Require one of the given roles; redirect to own dashboard if not authorized.
 */
function requireRole(string ...$roles): void
{
    requireLogin();
    $user = currentUser();
    if (!$user || !in_array($user['role'], $roles, true)) {
        // Redirect to their own dashboard instead of a raw 403
        flash('error', 'You do not have permission to access that page.');
        redirect(roleDashboardUrl($user['role'] ?? ''));
    }
}

/**
 * Check if the current user has a specific role.
 */
function hasRole(string $role): bool
{
    $user = currentUser();
    return $user && $user['role'] === $role;
}

/**
 * Generate or retrieve the CSRF token for the current session.
 */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify the CSRF token from POST data; die on failure.
 */
function verifyCsrf(): void
{
    $token = $_POST['_csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('CSRF token mismatch. Please go back and try again.');
    }
}

/**
 * Hash a password using bcrypt.
 */
function hashPassword(string $pw): string
{
    return password_hash($pw, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
}

/**
 * Verify a password against a hash.
 */
function verifyPassword(string $pw, string $hash): bool
{
    return password_verify($pw, $hash);
}

/**
 * Sanitize a value or array of values (XSS prevention).
 */
function sanitize(mixed $input): mixed
{
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars((string)$input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Redirect to a URL and exit.
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * Set a flash message in the session.
 */
function flash(string $key, string $msg): void
{
    $_SESSION['flash'][$key] = $msg;
}

/**
 * Get and clear a flash message from the session.
 */
function getFlash(string $key): string
{
    $msg = $_SESSION['flash'][$key] ?? '';
    unset($_SESSION['flash'][$key]);
    return $msg;
}

/**
 * Get the dashboard URL for a given role.
 */
function roleDashboardUrl(string $role): string
{
    $map = [
        'admin'               => APP_URL . '/admin/dashboard',
        'supervising_labor'   => APP_URL . '/supervising-labor/dashboard',
        'barangay_captain'    => APP_URL . '/barangay-captain/dashboard',
        'secretary'           => APP_URL . '/secretary/dashboard',
        'agency'              => APP_URL . '/agency/dashboard',
        'applicant'           => APP_URL . '/applicant/dashboard',
        'techvoc_supervisor'  => APP_URL . '/techvoc/dashboard',
        'bedo'                => APP_URL . '/bedo/dashboard',
    ];
    return $map[$role] ?? APP_URL . '/login';
}

/**
 * Log an audit entry.
 */
function auditLog(string $action, string $module, string $description = ''): void
{
    try {
        $db = Database::getInstance();
        $userId = $_SESSION['user_id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $db->execute(
            "INSERT INTO audit_logs (user_id, action, module, description, ip_address, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())",
            [$userId, $action, $module, $description, $ip]
        );
    } catch (Exception $e) {
        error_log('Audit log failed: ' . $e->getMessage());
    }
}

/**
 * Output a CSRF hidden input field.
 */
function csrfField(): string
{
    return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Format a date for display.
 */
function formatDate(string $date, string $format = 'M d, Y'): string
{
    if (empty($date)) return '—';
    return date($format, strtotime($date));
}

/**
 * Get a status badge HTML.
 */
function statusBadge(string $status): string
{
    $map = [
        'pending'    => 'warning',
        'approved'   => 'success',
        'rejected'   => 'danger',
        'available'  => 'success',
        'booked'     => 'primary',
        'cancelled'  => 'secondary',
        'invited'    => 'info',
        'confirmed'  => 'success',
        'declined'   => 'danger',
        'open'       => 'success',
        'closed'     => 'secondary',
        'draft'      => 'secondary',
        'published'  => 'success',
        'shortlisted'=> 'info',
        'hired'      => 'success',
        'unavailable'=> 'danger',
    ];
    $color = $map[$status] ?? 'secondary';
    $label = ucfirst(str_replace('_', ' ', $status));
    return "<span class=\"badge bg-{$color}\">{$label}</span>";
}
