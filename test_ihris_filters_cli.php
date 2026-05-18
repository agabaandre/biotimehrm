<?php
/**
 * CLI smoke test for All iHRIS Staff filter queries (run: php test_ihris_filters_cli.php)
 */
$envFile = __DIR__ . '/.env';
if (is_readable($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
            continue;
        }
        list($k, $v) = explode('=', $line, 2);
        $_ENV[trim($k)] = trim($v, " \t\"'");
    }
}

$mysqli = new mysqli(
    $_ENV['DB_HOST'] ?? 'localhost',
    $_ENV['DB_USER'] ?? 'root',
    $_ENV['DB_PASS'] ?? '',
    $_ENV['DB_NAME'] ?? 'attend'
);
if ($mysqli->connect_error) {
    fwrite(STDERR, "DB connect failed: {$mysqli->connect_error}\n");
    exit(1);
}

$queries = [
    'districts' => "SELECT COUNT(DISTINCT TRIM(district)) c FROM ihrisdata WHERE district IS NOT NULL AND TRIM(district) != ''",
    'facilities' => "SELECT COUNT(DISTINCT TRIM(facility_id), TRIM(facility)) c FROM ihrisdata WHERE facility_id IS NOT NULL AND TRIM(facility_id) != ''",
    'jobs' => "SELECT COUNT(DISTINCT TRIM(job_id)) c FROM ihrisdata WHERE job_id IS NOT NULL AND TRIM(job_id) != ''",
    'institution_types' => "SELECT COUNT(DISTINCT TRIM(institutiontype_name)) c FROM ihrisdata WHERE institutiontype_name IS NOT NULL AND TRIM(institutiontype_name) != ''",
    'facility_types' => "SELECT COUNT(DISTINCT TRIM(facility_type_id)) c FROM ihrisdata WHERE facility_type_id IS NOT NULL AND TRIM(facility_type_id) != ''",
];

echo "ihrisdata filter counts:\n";
foreach ($queries as $label => $sql) {
    $res = $mysqli->query($sql);
    $row = $res ? $res->fetch_assoc() : ['c' => 'ERR'];
    echo "  {$label}: {$row['c']}\n";
}

echo "OK\n";
