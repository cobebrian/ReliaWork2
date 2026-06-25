<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/config/config.php';
require_once ROOT_PATH . '/app/config/Database.php';
$db = Database::getInstance();

echo "=== All Applicants ===\n";
$applicants = $db->fetchAll("SELECT id, surname, firstname, validation_status, updated_at FROM applicants ORDER BY id DESC LIMIT 20");
foreach ($applicants as $a) {
    echo "  [{$a['id']}] {$a['surname']}, {$a['firstname']} — status: {$a['validation_status']} — updated: {$a['updated_at']}\n";
}

echo "\n=== Applicant Documents ===\n";
$docs = $db->fetchAll("SELECT d.id, d.applicant_id, d.doc_type, d.original_name, d.uploaded_at FROM applicant_documents d ORDER BY d.id DESC LIMIT 20");
foreach ($docs as $d) {
    echo "  [doc {$d['id']}] applicant_id:{$d['applicant_id']} — {$d['doc_type']}: {$d['original_name']} — {$d['uploaded_at']}\n";
}

echo "\n=== validation_status column check ===\n";
$col = $db->fetch("SHOW COLUMNS FROM applicants LIKE 'validation_status'");
if ($col) {
    echo "  Type: {$col['Type']} | Default: {$col['Default']} | Null: {$col['Null']}\n";
} else {
    echo "  COLUMN DOES NOT EXIST!\n";
}

echo "\n=== Applicants with docs but not pending ===\n";
$mismatch = $db->fetchAll(
    "SELECT a.id, a.surname, a.firstname, a.validation_status, COUNT(d.id) as doc_count
     FROM applicants a
     LEFT JOIN applicant_documents d ON d.applicant_id = a.id
     GROUP BY a.id
     HAVING doc_count > 0 AND a.validation_status != 'pending'"
);
foreach ($mismatch as $m) {
    echo "  [{$m['id']}] {$m['surname']}, {$m['firstname']} — status:{$m['validation_status']} — docs:{$m['doc_count']}\n";
}
if (empty($mismatch)) echo "  None (all applicants with docs are already pending)\n";
