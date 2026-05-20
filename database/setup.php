<?php
/**
 * ReliaWork2 Database Setup Script
 * Run this once to initialize the database.
 * Access via: http://localhost/currentsystem/ReliaWork2/database/setup.php
 */

// Only allow CLI or localhost
$allowedHosts = ['127.0.0.1', '::1', 'localhost'];
if (php_sapi_name() !== 'cli' && !in_array($_SERVER['REMOTE_ADDR'] ?? '', $allowedHosts)) {
    http_response_code(403);
    die('Access denied. Run from localhost only.');
}

$host     = '127.0.0.1';
$port     = 3306;
$dbName   = 'reliawork2_db';
$username = 'root';
$password = '';

$schemaFile = __DIR__ . '/schema.sql';
$seedFile   = __DIR__ . '/seed.sql';

echo "<pre>\n";
echo "=== ReliaWork2 Database Setup ===\n\n";

// Step 1: Connect without selecting a database
try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    echo "[OK] Connected to MySQL server.\n";
} catch (PDOException $e) {
    die("[FAIL] Cannot connect to MySQL: " . $e->getMessage() . "\n</pre>");
}

// Step 2: Create database
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "[OK] Database '{$dbName}' created or already exists.\n";
} catch (PDOException $e) {
    die("[FAIL] Cannot create database: " . $e->getMessage() . "\n</pre>");
}

// Step 3: Select database
try {
    $pdo->exec("USE `{$dbName}`");
    echo "[OK] Using database '{$dbName}'.\n\n";
} catch (PDOException $e) {
    die("[FAIL] Cannot select database: " . $e->getMessage() . "\n</pre>");
}

// Step 4: Run schema.sql
echo "--- Running schema.sql ---\n";
if (!file_exists($schemaFile)) {
    die("[FAIL] schema.sql not found at: {$schemaFile}\n</pre>");
}

$schemaSql = file_get_contents($schemaFile);

// Remove CREATE DATABASE and USE statements (already handled above)
$schemaSql = preg_replace('/CREATE DATABASE[^;]+;/i', '', $schemaSql);
$schemaSql = preg_replace('/USE\s+`[^`]+`\s*;/i', '', $schemaSql);

// Split by semicolons, skip empty/comment-only statements
$rawStatements = explode(';', $schemaSql);
$statements = [];
foreach ($rawStatements as $stmt) {
    $stmt = trim($stmt);
    // Skip empty or pure comment lines
    $lines = array_filter(explode("\n", $stmt), fn($l) => !empty(trim($l)) && !preg_match('/^\s*--/', $l));
    $clean = trim(implode("\n", $lines));
    if (!empty($clean)) {
        $statements[] = $clean;
    }
}

foreach ($statements as $stmt) {
    try {
        $pdo->exec($stmt);
    } catch (PDOException $e) {
        // Ignore "already exists" errors
        if (strpos($e->getMessage(), 'already exists') === false) {
            echo "[WARN] Schema statement issue: " . substr($stmt, 0, 80) . "\n";
            echo "       Reason: " . $e->getMessage() . "\n";
        }
    }
}
echo "[OK] Schema applied successfully.\n\n";

// Step 5: Generate correct bcrypt hash for Admin@123
$adminPassword = 'Admin@123';
$adminHash = password_hash($adminPassword, PASSWORD_BCRYPT, ['cost' => 12]);

// Step 6: Run seed.sql with correct hash
echo "--- Running seed.sql ---\n";
if (!file_exists($seedFile)) {
    die("[FAIL] seed.sql not found at: {$seedFile}\n</pre>");
}

// Check if admin already exists
$stmt = $pdo->prepare("SELECT COUNT(*) FROM `users` WHERE `email` = ?");
$stmt->execute(['admin@reliawork2.com']);
$adminExists = (int)$stmt->fetchColumn();

if ($adminExists > 0) {
    echo "[SKIP] Admin user already exists. Skipping seed.\n";
} else {
    // Insert admin with correct hash
    $stmt = $pdo->prepare(
        "INSERT INTO `users` (`name`, `email`, `password`, `role`, `status`, `created_at`) 
         VALUES (?, ?, ?, 'admin', 'approved', NOW())"
    );
    $stmt->execute(['System Administrator', 'admin@reliawork2.com', $adminHash]);
    $adminId = $pdo->lastInsertId();
    echo "[OK] Admin user created (ID: {$adminId}).\n";

    // Insert admin profile
    $stmt = $pdo->prepare(
        "INSERT INTO `profiles` (`user_id`, `phone`, `address`, `organization`, `position`, `created_at`) 
         VALUES (?, '09000000000', 'City Hall, Main Street', 'ReliaWork2 System', 'System Administrator', NOW())"
    );
    $stmt->execute([$adminId]);
    echo "[OK] Admin profile created.\n";

    // Insert barangay resources
    $resources = [
        ['Monobloc Chairs', 'White plastic monobloc chairs for events', 200, 'pieces'],
        ['Folding Tables', '6-foot folding tables for booths and registration', 50, 'pieces'],
        ['Event Tents', '10x10 ft canopy tents for outdoor coverage', 20, 'units'],
        ['PA System / Speakers', 'Public address system with microphone and amplifier', 5, 'sets'],
        ['Microphones', 'Wireless handheld microphones', 10, 'pieces'],
    ];
    $stmt = $pdo->prepare(
        "INSERT INTO `barangay_resources` (`name`, `description`, `quantity`, `unit`, `status`, `created_at`) 
         VALUES (?, ?, ?, ?, 'available', NOW())"
    );
    foreach ($resources as $r) {
        $stmt->execute($r);
    }
    echo "[OK] 5 barangay resources inserted.\n";

    // Insert announcements
    $announcements = [
        [
            'Welcome to ReliaWork2 Job Fair System',
            'The ReliaWork2 Job Fair Management System is now live. This platform connects job seekers with employers through organized job fair events. Barangay captains can submit job fair requests, agencies can post vacancies, and applicants can browse and apply for jobs. Please register to get started.',
            'general', 'published'
        ],
        [
            'Upcoming Job Fair Registration Open',
            'Registration for the upcoming community job fair is now open. Qualified applicants are encouraged to register and complete their profiles. Participating companies will be posting vacancies soon.',
            'job_opportunity', 'published'
        ],
        [
            'Important: System Maintenance Notice',
            'The ReliaWork2 system will undergo scheduled maintenance. During this period, some features may be temporarily unavailable. We apologize for any inconvenience.',
            'emergency', 'published'
        ],
    ];
    $stmt = $pdo->prepare(
        "INSERT INTO `announcements` (`title`, `content`, `type`, `status`, `created_by`, `created_at`) 
         VALUES (?, ?, ?, ?, ?, NOW())"
    );
    foreach ($announcements as $a) {
        $stmt->execute([$a[0], $a[1], $a[2], $a[3], $adminId]);
    }
    echo "[OK] 3 sample announcements inserted.\n";

    // Insert sample schedules
    $schedules = [
        ['Community Job Fair 2025 - Q1', date('Y-m-d', strtotime('+30 days')), '08:00:00', 'Barangay Hall Plaza', 'First quarter community job fair event'],
        ['Skills Training & Job Fair', date('Y-m-d', strtotime('+60 days')), '09:00:00', 'Municipal Gymnasium', 'Combined skills training and job fair event'],
        ['Annual Job Fair 2025', date('Y-m-d', strtotime('+90 days')), '08:00:00', 'City Sports Complex', 'Annual large-scale job fair event'],
    ];
    $stmt = $pdo->prepare(
        "INSERT INTO `schedule_of_events` (`title`, `event_date`, `event_time`, `venue`, `description`, `status`, `created_by`, `created_at`) 
         VALUES (?, ?, ?, ?, ?, 'available', ?, NOW())"
    );
    foreach ($schedules as $s) {
        $stmt->execute([$s[0], $s[1], $s[2], $s[3], $s[4], $adminId]);
    }
    echo "[OK] 3 sample schedule events inserted.\n";
}

echo "\n=== Setup Complete ===\n";
echo "Admin Login:\n";
echo "  Email:    admin@reliawork2.com\n";
echo "  Password: Admin@123\n";
echo "\nAccess the system at: http://localhost/currentsystem/ReliaWork2/public/\n";
echo "</pre>\n";
