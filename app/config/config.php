<?php
/**
 * ReliaWork2 Application Configuration
 * Loads .env and defines application constants.
 */

// Load .env file
$envFile = dirname(__DIR__, 2) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || str_starts_with($line, '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);
            // Strip surrounding quotes
            if (preg_match('/^"(.*)"$/', $value, $m) || preg_match("/^'(.*)'$/", $value, $m)) {
                $value = $m[1];
            }
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }
}

// Helper to read env
function env(string $key, mixed $default = null): mixed
{
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

// ── Application ──────────────────────────────────────────────────────────────
define('APP_NAME',    env('APP_NAME', 'ReliaWork2'));
define('APP_URL',     rtrim(env('APP_URL', 'http://localhost/currentsystem/ReliaWork2/public'), '/'));

// ── Paths ─────────────────────────────────────────────────────────────────────
define('BASE_PATH',   dirname(__DIR__, 2));          // ReliaWork2/
define('APP_PATH',    BASE_PATH . '/app');
define('VIEW_PATH',   APP_PATH  . '/views');
define('PUBLIC_PATH', BASE_PATH . '/public');

// ── Database ──────────────────────────────────────────────────────────────────
define('DB_HOST',     env('DB_HOST',     '127.0.0.1'));
define('DB_PORT',     env('DB_PORT',     '3306'));
define('DB_DATABASE', env('DB_DATABASE', 'reliawork2_db'));
define('DB_USERNAME', env('DB_USERNAME', 'root'));
define('DB_PASSWORD', env('DB_PASSWORD', ''));

// ── Security ──────────────────────────────────────────────────────────────────
define('BCRYPT_COST', 12);
define('SESSION_NAME', 'rw2_session');

// ── Roles ─────────────────────────────────────────────────────────────────────
define('ROLES', [
    'admin',
    'supervising_labor',
    'barangay_captain',
    'secretary',
    'agency',
    'applicant',
    'techvoc_supervisor',
]);

// ── Role Labels ───────────────────────────────────────────────────────────────
define('ROLE_LABELS', [
    'admin'              => 'Administrator',
    'supervising_labor'  => 'Supervising Labor',
    'barangay_captain'   => 'Barangay Captain',
    'secretary'          => 'Secretary',
    'agency'             => 'Agency',
    'applicant'          => 'Applicant',
    'techvoc_supervisor' => 'TECH-VOC Supervisor',
]);
