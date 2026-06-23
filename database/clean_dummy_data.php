<?php
/**
 * Clean dummy/sample company data from the database.
 * Removes manually added companies table entries that don't correspond to real agency users.
 * Removes participating_agencies rows that have no user_id link (manual/dummy entries).
 */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/config/config.php';
require_once ROOT_PATH . '/app/config/Database.php';

$db  = Database::getInstance();
$pdo = $db->getPdo();

echo "=== Database Cleanup ===\n\n";

// 1. Show what's in companies table
$companies = $db->fetchAll("SELECT id, name, email FROM companies ORDER BY id");
echo "Companies table (" . count($companies) . " rows):\n";
foreach ($companies as $c) {
    echo "  [{$c['id']}] {$c['name']} — {$c['email']}\n";
}

// 2. Delete ALL rows from companies table (manual dummy data)
$pdo->exec("DELETE FROM companies");
$remaining = (int)$db->fetchColumn("SELECT COUNT(*) FROM companies");
echo "\nDeleted all companies. Remaining: {$remaining}\n";

// 3. Show participating_agencies with no user_id (not linked to real accounts)
$orphans = $db->fetchAll(
    "SELECT id, agency_name, email, job_fair_request_id FROM participating_agencies WHERE user_id IS NULL"
);
echo "\nParticipating agencies with no user account link (" . count($orphans) . "):\n";
foreach ($orphans as $o) {
    echo "  [{$o['id']}] {$o['agency_name']} — {$o['email']} (fair #{$o['job_fair_request_id']})\n";
}

// 4. Delete orphan participating_agencies (no linked user)
$pdo->exec("DELETE FROM participating_agencies WHERE user_id IS NULL");
$remaining2 = (int)$db->fetchColumn("SELECT COUNT(*) FROM participating_agencies WHERE user_id IS NULL");
echo "Deleted orphan participating_agencies. Remaining orphans: {$remaining2}\n";

// 5. Show current agency users in the system
$agencyUsers = $db->fetchAll(
    "SELECT id, name, email, agency_name, status FROM users WHERE role = 'agency' ORDER BY id"
);
echo "\nRegistered agency accounts (" . count($agencyUsers) . "):\n";
foreach ($agencyUsers as $u) {
    $displayName = !empty($u['agency_name']) ? $u['agency_name'] : $u['name'];
    echo "  [{$u['id']}] {$displayName} — {$u['email']} (status: {$u['status']})\n";
}

// 6. Show remaining participating_agencies
$pa = $db->fetchAll(
    "SELECT pa.id, pa.agency_name, pa.email, pa.status, pa.user_id, jfr.title AS fair
     FROM participating_agencies pa
     LEFT JOIN job_fair_requests jfr ON jfr.id = pa.job_fair_request_id
     ORDER BY pa.id"
);
echo "\nRemaining participating_agencies (" . count($pa) . "):\n";
foreach ($pa as $p) {
    echo "  [{$p['id']}] {$p['agency_name']} — {$p['email']} | user_id:{$p['user_id']} | fair: {$p['fair']} | status: {$p['status']}\n";
}

echo "\n=== Cleanup complete. ===\n";
