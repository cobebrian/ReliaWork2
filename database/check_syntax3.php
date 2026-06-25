<?php
$files = [
    'app/controllers/InterviewController.php',
    'app/controllers/ReportingOfficerController.php',
    'app/config/config.php',
    'app/helpers/auth_helper.php',
    'public/index.php',
];
$root = dirname(__DIR__);
$ok = true;
foreach ($files as $f) {
    $out = []; $code = 0;
    exec('C:\\xampp1\\php\\php.exe -l ' . escapeshellarg($root . '\\' . str_replace('/', '\\', $f)) . ' 2>&1', $out, $code);
    if ($code !== 0) { echo "FAIL: $f\n  " . implode("\n", $out) . "\n"; $ok = false; }
    else { echo "OK:   $f\n"; }
}
echo $ok ? "\nAll files OK.\n" : "\nSome files have errors.\n";
