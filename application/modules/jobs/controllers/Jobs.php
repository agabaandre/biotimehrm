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

        $this->load->helper('deployment_helper');
        $this->load->model('jobs_mdl', 'jobMdl');

        // Restrict master scheduler to CLI only
        if (!$this->input->is_cli_request() && $this->router->method == 'master') {
            exit("CLI only.");
        }

        $this->lockFile = APPPATH . 'logs/jobs_master.lock';
    }

    /**
     * Job titles for employee forms (delegates to lists / employee_jobs).
     *
     * @return array
     */
    public function getJobs()
    {
        return Modules::run('lists/get_all_jobs') ?: [];
    }

    /**
     * CLI helper for deploy scripts: echoes "moh" or "education".
     */
    public function deployment_type()
    {
        if (!$this->input->is_cli_request()) {
            exit("CLI only.");
        }
        echo is_education_deployment() ? "education\n" : "moh\n";
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

        $education = is_education_deployment();

        // TEMP: pause BioTime enrollment/sync while we clean facilities & people first.
        // Set to true to resume cron BioTime jobs.
        $biotimeCronEnabled = true;

        echo "\n============================================\n";
        echo " JOBS MASTER STARTED: ".date('Y-m-d H:i:s')."\n";
        echo " Deployment: ".($education ? 'education' : 'moh')."\n";
        if (!$education && !$biotimeCronEnabled) {
            echo " BioTime cron: PAUSED (cleanup mode)\n";
        }
        echo "============================================\n\n";

        $jobsToRun = [];
        $dow = (int) date('w', $now);

        /* ----------------------------------------------------------
         * MOH ONLY — BioTime / iHRIS sync (not used in education)
         * ---------------------------------------------------------- */
        if (!$education && $biotimeCronEnabled) {
            if ($minute % 20 == 0) $jobsToRun[] = 'biotimejobs terminals';
            if ($minute % 15 == 0) $jobsToRun[] = 'biotimejobs saveEnrolled';
            if ($minute % 45 == 0) $jobsToRun[] = 'biotimejobs biotimeFacilities';
            // enrollment (multiple_new_users) + facility updates (transfer_employees)
            // run every 5 minutes outside the heavy lock (see below)

            if ($hour == 7 && $minute == 5)  $jobsToRun[] = 'biotimejobs biotime_jobs';
            if ($hour == 7 && $minute == 15) $jobsToRun[] = 'biotimejobs biotimedepartments';
            if ($hour == 7 && $minute == 37) $jobsToRun[] = 'biotimejobs rostatoAttend';
            if ($hour == 7 && $minute == 40) $jobsToRun[] = 'biotimejobs biotime_employees';

            if ($hour % 5 == 0 && $minute == 0)
                $jobsToRun[] = 'biotimejobs get_ihrisdata';

            if ($hour == 1 && $minute == 20)
                $jobsToRun[] = 'biotimejobs integrate_mobileclk_log';

            if ($day == 1 && $hour == 0 && $minute == 0)
                $jobsToRun[] = 'cronjobs AutoMohRoster';

            if ($minute == 15)
                $jobsToRun[] = 'cronjobs/DashboardCacheCron/warm';
        } elseif (!$education && !$biotimeCronEnabled) {
            // Still allow non-BioTime MOH cache warm when BioTime is paused
            if ($day == 1 && $hour == 0 && $minute == 0)
                $jobsToRun[] = 'cronjobs AutoMohRoster';
            if ($minute == 15)
                $jobsToRun[] = 'cronjobs/DashboardCacheCron/warm';
        }

        /* ----------------------------------------------------------
         * SHARED — monthly / summary / cache rebuild
         * ---------------------------------------------------------- */

        if ($day == 1 && $hour == 15 && $minute == 0)
            $jobsToRun[] = 'cronjobs publicdaystoAttend';

        if ($hour == 2 && $minute == 0 && $day % 5 == 0)
            $jobsToRun[] = 'cronjobs/DutyRosterSummaryCron/updateDutyRosterSummary';

        if ($hour % 6 == 0 && $minute == 0)
            $jobsToRun[] = 'cronjobs/AttendanceSummaryCron/updateAttendanceSummary';

        if ($dow === 0 && $hour == 0 && $minute == 0)
            $jobsToRun[] = 'cronjobs/FacilitySwitchCacheCron/rebuild';

        /* ============================================================
         * LOCK PROTECTION (Skip heavy overlap)
         * ============================================================ */

        if (file_exists($this->lockFile)) {
            if ((time() - filemtime($this->lockFile)) > 1800) {
                @unlink($this->lockFile); // Remove stale lock (ignore if already gone)
            } else {
                log_message('info', 'Jobs master: heavy jobs locked, skipping scheduled tasks');
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

            if (file_exists($this->lockFile)) {
                @unlink($this->lockFile);
            }
        } else {
            echo "No scheduled heavy jobs this minute.\n";
        }

        /* ============================================================
         * ENROLLMENT + UPDATES (every 40 min, WITHOUT heavy lock)
         * ============================================================ */

        if (!$education && $biotimeCronEnabled && ((int) $minute % 20 === 0)) {
            echo "\nRunning BioTime cleanup + enrollment + transfers (no lock)...\n";
            // multiple_new_users runs cleanup_biotime_employees first (delete API)
            $this->run('biotimejobs multiple_new_users');
            $this->run('biotimejobs transfer_employees');
        } elseif (!$education && !$biotimeCronEnabled && ((int) $minute % 40 === 0)) {
            echo "\nBioTime enrollment/transfers PAUSED — skipping.\n";
        }

        /* ============================================================
         * ATTENDANCE FETCH (Runs WITHOUT lock)
         * ============================================================ */

        if (!$education && $biotimeCronEnabled && $hour % 4 == 0 && $minute == 0) {
            echo "\nRunning attendance fetch (no lock)...\n";
            $this->run('biotimejobs fetch_daily_attendance');
        }

        if (!$education && $minute == 30) {
            echo "\nPushing clk_log to remote Attend (if enabled)...\n";
            $this->run('jobs/push_clk_log_remote');
        }

        echo "\n============================================\n";
        echo " JOBS MASTER COMPLETED\n";
        echo "============================================\n\n";
    }

    /* ============================================================
     * EXECUTE JOB COMMAND
     * Logs job name for easy follow-up in case of errors.
     * Uses URI path (slashes) so CodeIgniter CLI routes module/controller/method correctly.
     * Runs from app root (FCPATH) so index.php and bootstrap are found.
     * ============================================================ */
    private function run($command)
    {
        log_message('info', 'Jobs: starting job [' . $command . ']');
        // Normalize to URI path (spaces -> slashes) for correct CLI routing
        $uri = str_replace(' ', '/', $command);
        $base = defined('FCPATH') ? FCPATH : (getcwd() . DIRECTORY_SEPARATOR);
        $cmd = 'cd ' . escapeshellarg(rtrim($base, '/\\')) . ' && /usr/bin/php index.php ' . escapeshellarg($uri);
        shell_exec($cmd);
        log_message('info', 'Jobs: completed job [' . $command . ']');
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

    /**
     * Push local clk_log rows to a peer Attend server (encrypted).
     * Configure on /svariables:
     *   remote_clk_enabled = 1
     *   remote_clk_url = https://attend.health.go.ug
     *   remote_clk_private_key = (same one-time key on both servers)
     *
     * Usage: php index.php jobs/push_clk_log_remote
     *         php index.php jobs/push_clk_log_remote 200
     *
     * @param int $batch_size
     */
    public function push_clk_log_remote($batch_size = 200)
    {
        if (!$this->input->is_cli_request()) {
            exit("CLI only.\n");
        }
        ignore_user_abort(true);
        set_time_limit(0);

        $this->load->model('svariables/svariables_mdl');
        $this->load->library('clk_log_remote');

        $settings = $this->svariables_mdl->getSettings();
        $enabled = isset($settings->remote_clk_enabled) ? trim((string) $settings->remote_clk_enabled) : '0';
        $url = isset($settings->remote_clk_url) ? rtrim(trim((string) $settings->remote_clk_url), '/') : '';
        $key = isset($settings->remote_clk_private_key) ? trim((string) $settings->remote_clk_private_key) : '';
        $lastId = isset($settings->remote_clk_last_id) ? (int) $settings->remote_clk_last_id : 0;

        echo "═══════════════════════════════════════════════════════\n";
        echo " PUSH clk_log → remote Attend\n";
        echo "═══════════════════════════════════════════════════════\n";
        echo "enabled=" . $enabled . " url=" . ($url !== '' ? $url : '(empty)') . " last_id={$lastId}\n";
        echo "private_key_len=" . strlen($key) . " (hidden)\n";

        if ($enabled !== '1' && strtolower($enabled) !== 'true' && $enabled !== 'yes') {
            echo "Skipped: set remote_clk_enabled=1 on Settings → Remote Clock Sync.\n";
            return;
        }
        if ($url === '' || $key === '') {
            echo "Skipped: set remote_clk_url and remote_clk_private_key on Settings → Remote Clock Sync.\n";
            return;
        }
        if (!$this->db->table_exists('clk_log')) {
            echo "Skipped: clk_log table missing.\n";
            return;
        }

        $batch_size = (int) $batch_size;
        if ($batch_size < 1) {
            $batch_size = 200;
        }

        $this->svariables_mdl->ensureClkLogRemoteStatusColumn();

        $select = ['id', 'entry_id', 'ihris_pid', 'facility_id', 'time_in', 'time_out', 'date'];
        foreach (['status', 'shift', 'location', 'source', 'facility', 'latitude', 'longitude'] as $c) {
            if ($this->db->field_exists($c, 'clk_log')) {
                $select[] = $c;
            }
        }

        $this->db->select(implode(', ', $select));
        $this->db->from('clk_log');
        if ($this->db->field_exists('remote_sync_status', 'clk_log')) {
            $this->db->group_start();
            $this->db->where('remote_sync_status IS NULL', null, false);
            $this->db->or_where('remote_sync_status', '');
            $this->db->or_where('remote_sync_status', 'pending');
            $this->db->or_where('remote_sync_status', 'failed');
            $this->db->group_end();
        } else {
            $this->db->where('id >', $lastId);
        }
        $rows = $this->db->order_by('id', 'ASC')
            ->limit($batch_size)
            ->get()
            ->result_array();

        if (empty($rows)) {
            echo "No pending clk_log rows to send.\n";
            return;
        }

        $ids = [];
        $maxId = 0;
        $payloadRows = [];
        foreach ($rows as $r) {
            $ids[] = (int) $r['id'];
            $maxId = max($maxId, (int) $r['id']);
            unset($r['id'], $r['remote_sync_status'], $r['remote_sync_at']);
            $payloadRows[] = $r;
        }

        $plain = json_encode([
            'sent_at' => date('c'),
            'count' => count($payloadRows),
            'rows' => $payloadRows,
        ]);
        $packet = $this->clk_log_remote->encrypt($plain, $key);

        $endpoint = $url . '/api/clk_log_ingest';
        echo "POST {$endpoint} rows=" . count($payloadRows) . "\n";

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_POSTFIELDS => json_encode($packet),
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT => 60,
        ]);
        $body = curl_exec($ch);
        $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $cerr = curl_error($ch);
        curl_close($ch);

        if ($body === false || $http < 200 || $http >= 300) {
            echo "FAIL http={$http} curl={$cerr} body=" . substr((string) $body, 0, 300) . "\n";
            log_message('error', "push_clk_log_remote failed http={$http} curl={$cerr}");
            if ($this->db->field_exists('remote_sync_status', 'clk_log') && !empty($ids)) {
                $this->db->where_in('id', $ids)->update('clk_log', ['remote_sync_status' => 'failed']);
            }
            return;
        }

        $res = json_decode((string) $body, true);
        if (!is_array($res) || empty($res['status'])) {
            echo "FAIL unexpected response: " . substr((string) $body, 0, 300) . "\n";
            if ($this->db->field_exists('remote_sync_status', 'clk_log') && !empty($ids)) {
                $this->db->where_in('id', $ids)->update('clk_log', ['remote_sync_status' => 'failed']);
            }
            return;
        }

        $this->svariables_mdl->setRemoteClkLastId($maxId);
        if ($this->db->field_exists('remote_sync_status', 'clk_log') && !empty($ids)) {
            $mark = ['remote_sync_status' => 'sent'];
            if ($this->db->field_exists('remote_sync_at', 'clk_log')) {
                $mark['remote_sync_at'] = date('Y-m-d H:i:s');
            }
            $this->db->where_in('id', $ids)->update('clk_log', $mark);
        }
        echo "OK marked_sent=" . count($ids) . " last_id={$maxId} remote_stats="
            . json_encode(isset($res['stats']) ? $res['stats'] : []) . "\n";
    }
}