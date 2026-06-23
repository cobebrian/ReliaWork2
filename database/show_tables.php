<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/config/config.php';
require_once ROOT_PATH . '/app/config/Database.php';
$db = Database::getInstance();
$tables = $db->fetchAll('SHOW TABLES');
foreach ($tables as $t) { echo implode('', $t) . "\n"; }
