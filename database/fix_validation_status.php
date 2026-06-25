<?php
/**
 * Fix applicants who have uploaded documents but validation_status is still 'not_submitted'.
 * Sets them to 'pending' so the Validating Officer can see them.
 */
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/config/config.php';
require_once ROOT_PATH . '/app/config/Database.php';

$db  = Database::getInstance();
$pdo = $db->getPdo();

// Find applicants with docs but still not_submitted
$toFix = $db->fetchAll(
    "SELECT a.id, a.surname, a.firstname, a.validation_status, COUNT(d.id) AS doc_count
     FROM applicants a
     JOIN applicant_documents d ON d.applicant_id = a.id
     WHERE a.validation_status = 'not_submitted'
     GROUP BY a.id"
);

echo "Applicants to fix: " . count($toFix) . "\n";

foreach ($toFix as $a) {
    $pdo->exec("UPDATE applicants SET validation_status = 'pending', updated_at = NOW() WHERE id = {$a['id']}");
    echo "  Fixed [{$a['id']}] {$a['surname']}, {$a['firstname']} — {$a['doc_count']} doc(s) → pending\n";
}

echo "\nDone.\n";
