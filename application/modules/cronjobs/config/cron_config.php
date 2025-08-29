<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Cron Job Configuration
|--------------------------------------------------------------------------
| This file contains configuration for automated cron jobs
|
*/

$config['cron_jobs'] = [
    
    /*
    |--------------------------------------------------------------------------
    | Attendance Summary Cron Job
    |--------------------------------------------------------------------------
    | Updates person_att_final table with monthly attendance summaries
    | Runs daily at 1:00 AM to ensure fresh data
    |
    | Cron Expression: 0 1 * * *
    | - 0 = Minute (0)
    | - 1 = Hour (1 AM)
    | - * = Day of month (every day)
    | - * = Month (every month)
    | - * = Day of week (every day)
    |
    */
    'attendance_summary' => [
        'enabled' => TRUE,
        'cron_expression' => '0 1 * * *',
        'controller' => 'cronjobs/AttendanceSummaryCron/updateAttendanceSummary',
        'description' => 'Update person_att_final table with monthly attendance summaries',
        'start_date' => '2025-05-01',
        'timezone' => 'Africa/Nairobi',
        'max_execution_time' => 300, // 5 minutes
        'log_level' => 'info',
        'retry_attempts' => 3,
        'retry_delay' => 300, // 5 minutes
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Roster Summary Cron Job (Future Implementation)
    |--------------------------------------------------------------------------
    | Updates person_dut_final table with monthly roster summaries
    | Can be enabled when needed
    |
    */
    'roster_summary' => [
        'enabled' => FALSE,
        'cron_expression' => '0 2 * * *',
        'controller' => 'cronjobs/RosterSummaryCron/updateRosterSummary',
        'description' => 'Update person_dut_final table with monthly roster summaries',
        'start_date' => '2025-05-01',
        'timezone' => 'Africa/Nairobi',
        'max_execution_time' => 300,
        'log_level' => 'info',
        'retry_attempts' => 3,
        'retry_delay' => 300,
    ]
];

/*
|--------------------------------------------------------------------------
| Cron Job Execution Settings
|--------------------------------------------------------------------------
|
*/
$config['cron_execution'] = [
    'base_url' => 'http://localhost/attend/', // Update this to your actual base URL
    'timeout' => 30, // HTTP request timeout in seconds
    'user_agent' => 'AttendanceSystem-Cron/1.0',
    'log_directory' => APPPATH . 'logs/cron/',
    'max_log_files' => 30, // Keep logs for 30 days
    'notification_email' => 'admin@example.com', // Update with actual admin email
];

/*
|--------------------------------------------------------------------------
| Cron Job Monitoring
|--------------------------------------------------------------------------
|
*/
$config['cron_monitoring'] = [
    'enabled' => TRUE,
    'check_interval' => 3600, // Check every hour
    'alert_threshold' => 86400, // Alert if job hasn't run in 24 hours
    'notification_channels' => ['email', 'log'],
    'health_check_endpoint' => 'cronjobs/HealthCheck/status',
];
