<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Africa/Kampala');

class Jobs extends MX_Controller {

    private $lockFile;
    private $totalSteps = 0;
    private $currentStep = 0;

    public function __construct()
    {
        parent::__construct();

        $this->load->model('jobs_mdl', 'jobMdl');

        // Restrict master scheduler to CLI only
        if (!$this->input->is_cli_request() && $this->router->method == 'master') {
            exit("CLI only.");
        }

        $this->lockFile = APPPATH . 'logs/jobs_master.lock';
    }

    /* ============================================================
     * MASTER SCHEDULER
     * Runs every minute from cron
     * ============================================================ */
    public function master()
    {
        $now    = time();
        $minute = date('i', $now);
        $hour   = date('H', $now);
        $day    = date('d', $now);

        echo "\n============================================\n";
        echo " JOBS MASTER STARTED: ".date('Y-m-d H:i:s')."\n";
        echo "============================================\n\n";

        $jobsToRun = [];

        /* ----------------------------------------------------------
         * 1️⃣ INTERVAL SYNC JOBS (Light DB usage)
         * ---------------------------------------------------------- */

        if ($minute % 20 == 0) $jobsToRun[] = 'biotimejobs terminals';
        if ($minute % 30 == 0) $jobsToRun[] = 'biotimejobs saveEnrolled';
        if ($minute % 35 == 0) $jobsToRun[] = 'biotimejobs transfer_employees';
        if ($minute % 45 == 0) $jobsToRun[] = 'biotimejobs biotimeFacilities';
        if ($minute % 55 == 0) $jobsToRun[] = 'biotimejobs multiple_new_users';

        /* ----------------------------------------------------------
         * 2️⃣ DAILY STRUCTURE JOBS (Staggered to avoid DB spikes)
         * ---------------------------------------------------------- */

        if ($hour == 7 && $minute == 5)  $jobsToRun[] = 'biotimejobs biotime_jobs';
        if ($hour == 7 && $minute == 15) $jobsToRun[] = 'biotimejobs biotimedepartments';
        if ($hour == 7 && $minute == 37) $jobsToRun[] = 'biotimejobs rostatoAttend';
        if ($hour == 7 && $minute == 40) $jobsToRun[] = 'biotimejobs biotime_employees';

        /* ----------------------------------------------------------
         * 3️⃣ MONTHLY SYSTEM JOBS
         * ---------------------------------------------------------- */

        if ($day == 1 && $hour == 0 && $minute == 0)
            $jobsToRun[] = 'cronjobs AutoMohRoster';

        if ($day == 1 && $hour == 15 && $minute == 0)
            $jobsToRun[] = 'cronjobs publicdaystoAttend';

        /* ----------------------------------------------------------
         * 4️⃣ SUMMARY REPORTS
         * ---------------------------------------------------------- */

        if ($hour == 2 && $minute == 0 && $day % 5 == 0)
            $jobsToRun[] = 'cronjobs DutyRosterSummaryCron updateDutyRosterSummary';

        if ($hour % 6 == 0 && $minute == 0)
            $jobsToRun[] = 'cronjobs AttendanceSummaryCron updateAttendanceSummary';

        /* ----------------------------------------------------------
         * 5️⃣ DASHBOARD CACHE (Low impact)
         * ---------------------------------------------------------- */

        if ($minute == 15)
            $jobsToRun[] = 'biotime_jobs cache_dash_Data';

        /* ============================================================
         * LOCK PROTECTION (Skip heavy overlap)
         * ============================================================ */

        if (file_exists($this->lockFile)) {
            if ((time() - filemtime($this->lockFile)) > 1800) {
                unlink($this->lockFile); // Remove stale lock
            } else {
                echo "Heavy jobs locked. Skipping heavy tasks.\n";
                $jobsToRun = []; // Prevent execution
            }
        }

        if (!empty($jobsToRun)) {
            file_put_contents($this->lockFile, time());

            $this->totalSteps = count($jobsToRun);
            $this->currentStep = 0;

            foreach ($jobsToRun as $job) {
                $this->currentStep++;
                $this->progressBar($this->currentStep, $this->totalSteps, $job);
                $this->run($job);
            }

            unlink($this->lockFile);
        } else {
            echo "No scheduled heavy jobs this minute.\n";
        }

        /* ============================================================
         * ATTENDANCE FETCH (Runs WITHOUT lock)
         * ============================================================ */

        if ($hour % 4 == 0 && $minute == 0) {
            echo "\nRunning attendance fetch (no lock)...\n";
            $this->run('biotimejobs fetch_daily_attendance');
        }
		  if ($hour % 3 == 0 && $minute == 0) {
            echo "\nRunning attendance fetch (no lock)...\n";
            $this->run('biotimejobs markattendance');
        }

        echo "\n============================================\n";
        echo " JOBS MASTER COMPLETED\n";
        echo "============================================\n\n";
    }

    /* ============================================================
     * EXECUTE JOB COMMAND
     * ============================================================ */
    private function run($command)
    {
        shell_exec("/usr/bin/php index.php $command");
    }

    /* ============================================================
     * TERMINAL PROGRESS BAR
     * ============================================================ */
    private function progressBar($current, $total, $jobName)
    {
        $percent = intval(($current / $total) * 100);
        $barLength = 40;
        $filled = intval(($percent / 100) * $barLength);

        $bar = str_repeat("█", $filled);
        $empty = str_repeat("-", $barLength - $filled);

        echo sprintf(
            "\r[%s%s] %d%% | Job %d/%d | %s",
            $bar,
            $empty,
            $percent,
            $current,
            $total,
            $jobName
        );

        if ($current == $total) {
            echo "\n";
        }
    }
}
