<?php
/**
 * Add job_enddate and is_active columns to ihrisdata.
 *
 * Run: php application/migrations/run_ihrisdata_employee_columns.php
 */

require_once __DIR__ . '/../../index.php';

$CI =& get_instance();
$CI->load->model('employees/employee_model', 'empModel');

echo "Ensuring ihrisdata employee columns...\n";
$CI->empModel->ensureIhrisdataEmployeeColumns();
echo "Done.\n";
