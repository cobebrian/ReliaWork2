<?php
/**
 * ReliaWork2 Flash Message Helper
 * Session-based flash messages with Bootstrap alert rendering.
 */

/**
 * Render all pending flash messages as Bootstrap alerts.
 * Call this in your layout view.
 */
function renderFlashMessages(): void
{
    $types = ['success', 'error', 'warning', 'info'];
    $alertMap = [
        'success' => 'success',
        'error'   => 'danger',
        'warning' => 'warning',
        'info'    => 'info',
    ];

    foreach ($types as $type) {
        $msg = getFlash($type);
        if (!empty($msg)) {
            $alertClass = $alertMap[$type] ?? 'info';
            $icon = match($type) {
                'success' => 'bi-check-circle-fill',
                'error'   => 'bi-x-circle-fill',
                'warning' => 'bi-exclamation-triangle-fill',
                default   => 'bi-info-circle-fill',
            };
            echo '<div class="alert alert-' . $alertClass . ' alert-dismissible fade show flash-message" role="alert">';
            echo '<i class="bi ' . $icon . ' me-2"></i>';
            echo htmlspecialchars($msg, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';
        }
    }
}

/**
 * Check if any flash messages exist.
 */
function hasFlash(): bool
{
    foreach (['success', 'error', 'warning', 'info'] as $type) {
        if (!empty($_SESSION['flash'][$type])) {
            return true;
        }
    }
    return false;
}
