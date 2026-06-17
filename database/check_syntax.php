<?php
$files = [
    'app/controllers/ApplicantController.php',
    'app/controllers/SupervisingLaborController.php',
    'app/models/ApplicantModel.php',
    'app/models/JobFairPostModel.php',
    'public/index.php',
];

$root = dirname(__DIR__);
$ok = true;
foreach ($files as $f) {
    $out = [];
    $code = 0;
    exec('C:\\xampp1\\php\\php.exe -l ' . escapeshellarg($root . '\\' . str_replace('/', '\\', $f)) . ' 2>&1', $out, $code);
    $result = implode("\n", $out);
    if ($code !== 0) {
        echo "FAIL: $f\n  $result\n";
        $ok = false;
    } else {
        echo "OK:   $f\n";
    }
}
echo $ok ? "\nAll files OK.\n" : "\nSome files have errors.\n";
