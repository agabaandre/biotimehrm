<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class DutyRosterSummaryCron extends MX_Controller {

    public function __construct() {
        parent::__construct();
        
        // Set timezone
        date_default_timezone_set('Africa/Nairobi');
    }

    /**
     * Main cron job method to update duty roster summary table
     * This should be called daily via cron: 0 2 * * * (runs at 2 AM daily)
     */
    public function updateDutyRosterSummary() {
        log_message('info', 'Starting Duty Roster Summary Cron Job');
        
        try {
            
            // Get the date range for processing: first day of previous month to today
            $date_from = date('Y-m-01', strtotime('first day of last month'));
            $date_to = date('Y-m-d'); // Today
            
            // Generate and insert new duty roster summary data (with upsert)
            $inserted_count = $this->_generateDutyRosterSummary($date_from, $date_to);
            
            // Update the last_gen timestamp
            $this->_updateLastGenTimestamp();
            
            log_message('info', "Duty Roster Summary Cron: Successfully processed {$inserted_count} records from {$date_from} to {$date_to}");
            
            echo json_encode([
                'status' => 'success',
                'message' => "Duty roster summary updated successfully. {$inserted_count} records processed.",
                'date_from' => $date_from,
                'date_to' => $date_to,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            log_message('error', 'Duty Roster Summary Cron Error: ' . $e->getMessage());
            
            echo json_encode([
                'status' => 'error',
                'message' => 'Error updating duty roster summary: ' . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Generate duty roster summary data using the optimized query with upsert
     * Based on the logic from rosta_cron.php
     */
    public function _resetautoincrement() {
        $this->db->query("ALTER TABLE person_dut_final AUTO_INCREMENT = 1");
    }
    private function _generateDutyRosterSummary($date_from, $date_to) {
        $this->_resetautoincrement();
        $query = "
            INSERT INTO person_dut_final (
                entry_id, ihris_pid, fullname, othername, facility_id, 
                facility_name, schedule_id, duty_date, job, 
                D, E, N, O, A, S, M, Z, month_days, last_gen
            )
            SELECT
                CONCAT(t.ihris_pid, '-', t.yyyy_mm) AS entry_id,
                t.ihris_pid,
                CONCAT(d.surname, ' ', d.firstname, ' ') AS fullname,
                COALESCE(d.othername, '') AS othername,
                t.facility_id,
                d.facility AS facility_name,
                /* Dominant roster code for the month (tie-break D > E > N > O > A > S > M > Z) */
                CASE
                    WHEN t.D_ct = GREATEST(t.D_ct, t.E_ct, t.N_ct, t.O_ct, t.A_ct, t.S_ct, t.M_ct, t.Z_ct) THEN '14'
                    WHEN t.E_ct = GREATEST(t.D_ct, t.E_ct, t.N_ct, t.O_ct, t.A_ct, t.S_ct, t.M_ct, t.Z_ct) THEN '15'
                    WHEN t.N_ct = GREATEST(t.D_ct, t.E_ct, t.N_ct, t.O_ct, t.A_ct, t.S_ct, t.M_ct, t.Z_ct) THEN '16'
                    WHEN t.O_ct = GREATEST(t.D_ct, t.E_ct, t.N_ct, t.O_ct, t.A_ct, t.S_ct, t.M_ct, t.Z_ct) THEN '17'
                    WHEN t.A_ct = GREATEST(t.D_ct, t.E_ct, t.N_ct, t.O_ct, t.A_ct, t.S_ct, t.M_ct, t.Z_ct) THEN '18'
                    WHEN t.S_ct = GREATEST(t.D_ct, t.E_ct, t.N_ct, t.O_ct, t.A_ct, t.S_ct, t.M_ct, t.Z_ct) THEN '19'
                    WHEN t.M_ct = GREATEST(t.D_ct, t.E_ct, t.N_ct, t.O_ct, t.A_ct, t.S_ct, t.M_ct, t.Z_ct) THEN '20'
                    ELSE '21'
                END AS schedule_id,
                t.yyyy_mm AS duty_date,
                COALESCE(d.job, '') AS job,
                /* Monthly counts by roster letter */
                t.D_ct AS D, t.E_ct AS E, t.N_ct AS N, t.O_ct AS O,
                t.A_ct AS A, t.S_ct AS S, t.M_ct AS M, t.Z_ct AS Z,
                t.month_days,
                NOW() AS last_gen
            FROM (
                /* Aggregate once per person x facility x month for speed */
                SELECT
                    r.ihris_pid,
                    r.facility_id,
                    DATE_FORMAT(r.duty_date, '%Y-%m') AS yyyy_mm,
                    DAY(LAST_DAY(r.duty_date)) AS month_days,
                    SUM(r.schedule_id = 14) AS D_ct,  -- Day
                    SUM(r.schedule_id = 15) AS E_ct,  -- Evening
                    SUM(r.schedule_id = 16) AS N_ct,  -- Night
                    SUM(r.schedule_id = 17) AS O_ct,  -- Off-duty
                    SUM(r.schedule_id = 18) AS A_ct,  -- Annual leave
                    SUM(r.schedule_id = 19) AS S_ct,  -- Study leave
                    SUM(r.schedule_id = 20) AS M_ct,  -- Maternity leave
                    SUM(r.schedule_id = 21) AS Z_ct   -- Other authorised leave
                FROM duty_rosta r
                WHERE r.schedule_id IN (14, 15, 16, 17, 18, 19, 20, 21)
                    AND r.duty_date BETWEEN ? AND ?
                GROUP BY r.ihris_pid, r.facility_id, DATE_FORMAT(r.duty_date, '%Y-%m')
            ) t
            LEFT JOIN ihrisdata d ON d.ihris_pid = t.ihris_pid
            ON DUPLICATE KEY UPDATE
                ihris_pid = VALUES(ihris_pid),
                fullname = VALUES(fullname),
                othername = VALUES(othername),
                facility_id = VALUES(facility_id),
                facility_name = VALUES(facility_name),
                schedule_id = VALUES(schedule_id),
                duty_date = VALUES(duty_date),
                job = VALUES(job),
                D = VALUES(D),
                E = VALUES(E),
                N = VALUES(N),
                O = VALUES(O),
                A = VALUES(A),
                S = VALUES(S),
                M = VALUES(M),
                Z = VALUES(Z),
                month_days = VALUES(month_days),
                last_gen = VALUES(last_gen)
        ";
        
        $result = $this->db->query($query, [$date_from, $date_to]);
        
        if ($result === FALSE) {
            throw new Exception('Failed to insert duty roster summary data: ' . $this->db->error()['message']);
        }
        
        $inserted_count = $this->db->affected_rows();
        return $inserted_count;
    }

    /**
     * Create the person_dut_final table if it doesn't exist
     */
    public function createTable() {
        $create_table_sql = "
            CREATE TABLE IF NOT EXISTS person_dut_final (
                id INT(11) NOT NULL AUTO_INCREMENT,
                entry_id VARCHAR(100) NOT NULL,
                ihris_pid VARCHAR(100) NOT NULL,
                fullname VARCHAR(100) NOT NULL,
                othername VARCHAR(100) NOT NULL,
                facility_id VARCHAR(100) NOT NULL,
                facility_name VARCHAR(150) NOT NULL,
                schedule_id VARCHAR(20) NOT NULL,
                duty_date VARCHAR(100) NOT NULL,
                job VARCHAR(100) NOT NULL,
                D INT(11) NOT NULL,
                E INT(11) NOT NULL,
                N INT(11) NOT NULL,
                O INT(11) NOT NULL,
                A INT(11) NOT NULL,
                S INT(11) NOT NULL,
                M INT(11) NOT NULL,
                Z INT(11) NOT NULL,
                month_days INT DEFAULT 0,
                last_gen TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY entry_id (entry_id),
                INDEX idx_ihris_pid (ihris_pid),
                INDEX idx_facility_id (facility_id),
                INDEX idx_duty_date (duty_date),
                INDEX idx_schedule_id (schedule_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";
        
        $result = $this->db->query($create_table_sql);
        
        if ($result === FALSE) {
            throw new Exception('Failed to create person_dut_final table: ' . $this->db->error()['message']);
        }
        
        return "Duty roster summary table (person_dut_final) created successfully";
    }

    /**
     * Update the last_gen timestamp for tracking
     */
    private function _updateLastGenTimestamp() {
        $this->_createTimestampTable();
        $this->db->set('last_gen', date('Y-m-d H:i:s'));
        $this->db->where('table_name', 'person_dut_final');
        $this->db->update('system_tables_timestamp');
        
        // If the table doesn't exist, create it
        if ($this->db->affected_rows() == 0) {
            $this->_createTimestampTable();
        }
    }

    /**
     * Create timestamp tracking table if it doesn't exist
     */
    private function _createTimestampTable() {
        $create_table_sql = "
            CREATE TABLE IF NOT EXISTS system_tables_timestamp (
                id INT AUTO_INCREMENT PRIMARY KEY,
                table_name VARCHAR(100) NOT NULL UNIQUE,
                last_gen DATETIME NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ";
        
        $this->db->query($create_table_sql);
        
        // Insert initial record for duty roster summary
        $this->db->replace('system_tables_timestamp', [
            'table_name' => 'person_dut_final',
            'last_gen' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Manual trigger method for testing
     */
    public function manualTrigger() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        $this->updateDutyRosterSummary();
    }

    /**
     * Manual trigger with custom date range for testing
     */
    public function manualTriggerCustom($date_from = null, $date_to = null) {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        log_message('info', 'Starting Manual Duty Roster Summary Cron Job with Custom Date Range');
        
        try {
            // Use provided dates or default to previous month to today
            if (!$date_from) {
                $date_from = date('Y-m-01', strtotime('first day of last month'));
            }
            if (!$date_to) {
                $date_to = date('Y-m-d');
            }
            
            // Validate date format
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to)) {
                throw new Exception('Invalid date format. Use YYYY-MM-DD format.');
            }
            
            // Generate and insert new duty roster summary data (with upsert)
            $inserted_count = $this->_generateDutyRosterSummary($date_from, $date_to);
            
            // Update the last_gen timestamp
            $this->_updateLastGenTimestamp();
            
            log_message('info', "Manual Duty Roster Summary Cron: Successfully processed {$inserted_count} records from {$date_from} to {$date_to}");
            
            echo json_encode([
                'status' => 'success',
                'message' => "Duty roster summary updated successfully. {$inserted_count} records processed.",
                'date_from' => $date_from,
                'date_to' => $date_to,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            log_message('error', 'Manual Duty Roster Summary Cron Error: ' . $e->getMessage());
            
            echo json_encode([
                'status' => 'error',
                'message' => 'Error updating duty roster summary: ' . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Get the current date range that will be processed
     */
    public function getDateRange() {
        $current_date = date('Y-m-d');
        $start_date = '2025-05-01';
        
        if (strtotime($current_date) < strtotime($start_date)) {
            echo json_encode([
                'status' => 'info',
                'message' => 'Cron job will not run until after May 1st, 2025',
                'start_date' => $start_date,
                'current_date' => $current_date,
                'will_run' => false
            ]);
            return;
        }
        
        $date_from = date('Y-m-01', strtotime('first day of last month'));
        $date_to = date('Y-m-d');
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Current date range for processing',
            'date_from' => $date_from,
            'date_to' => $date_to,
            'current_date' => $current_date,
            'will_run' => true,
            'days_to_process' => (strtotime($date_to) - strtotime($date_from)) / (60 * 60 * 24)
        ]);
    }

    /**
     * Get status of the last run
     */
    public function getStatus() {
        $this->db->select('last_gen, updated_at');
        $this->db->from('system_tables_timestamp');
        $this->db->where('table_name', 'person_dut_final');
        $result = $this->db->get()->row();
        
        if ($result) {
            echo json_encode([
                'status' => 'success',
                'last_generation' => $result->last_gen,
                'last_updated' => $result->updated_at,
                'current_time' => date('Y-m-d H:i:s')
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No timestamp record found',
                'current_time' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Get summary statistics for monitoring
     */
    public function getSummaryStats() {
        $stats = [];
        
        // Total records
        $total_records = $this->db->count_all('person_dut_final');
        $stats['total_records'] = $total_records;
        
        // Records by month
        $this->db->select('duty_date, COUNT(*) as count');
        $this->db->from('person_dut_final');
        $this->db->group_by('duty_date');
        $this->db->order_by('duty_date DESC');
        $this->db->limit(12);
        $monthly_stats = $this->db->get()->result_array();
        $stats['monthly_breakdown'] = $monthly_stats;
        
        // Top facilities
        $this->db->select('facility_name, COUNT(*) as count');
        $this->db->from('person_dut_final');
        $this->db->group_by('facility_name');
        $this->db->order_by('count DESC');
        $this->db->limit(10);
        $facility_stats = $this->db->get()->result_array();
        $stats['top_facilities'] = $facility_stats;
        
        // Schedule distribution
        $this->db->select('schedule_id, COUNT(*) as count');
        $this->db->from('person_dut_final');
        $this->db->group_by('schedule_id');
        $this->db->order_by('schedule_id');
        $schedule_stats = $this->db->get()->result_array();
        $stats['schedule_distribution'] = $schedule_stats;
        
        header('Content-Type: application/json');
        echo json_encode($stats);
    }

    /**
     * Clean up old records (optional maintenance)
     */
    public function cleanupOldRecords($months_to_keep = 24) {
        $cutoff_date = date('Y-m', strtotime("-{$months_to_keep} months"));
        
        $this->db->where('duty_date <', $cutoff_date);
        $deleted_count = $this->db->delete('person_dut_final');
        
        echo json_encode([
            'status' => 'success',
            'message' => "Cleaned up {$deleted_count} old records before {$cutoff_date}",
            'cutoff_date' => $cutoff_date,
            'deleted_count' => $deleted_count
        ]);
    }
}
