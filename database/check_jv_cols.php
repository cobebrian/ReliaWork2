<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/config/config.php';
require_once ROOT_PATH . '/app/config/Database.php';
$db = Database::getInstance();
$cols = $db->fetchAll("SHOW COLUMNS FROM job_vacancies");
foreach ($cols as $c) { echo $c['Field'] . " — " . $c['Type'] . "\n"; }
