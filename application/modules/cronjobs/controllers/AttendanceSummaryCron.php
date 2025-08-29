<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AttendanceSummaryCron extends MX_Controller {

    public function __construct() {
        parent::__construct();
        
        // Load required models
        $this->load->model('dashboard_mdl', 'dash_mdl');
        
        // Set timezone
        date_default_timezone_set('Africa/Nairobi');
    }

    /**
     * Main cron job method to update person_att_final table
     * This should be called daily via cron: 0 1 * * * (runs at 1 AM daily)
     */
    public function updateAttendanceSummary() {
        log_message('info', 'Starting Attendance Summary Cron Job');
        
        try {
            // Check if we should run this job (only after May 1st)
            $current_date = date('Y-m-d');
            $start_date = '2025-05-01';
            
            if (strtotime($current_date) < strtotime($start_date)) {
                log_message('info', 'Attendance Summary Cron: Not yet May 1st, skipping execution');
                return;
            }
            
            // Get the date range for processing
            $date_from = $start_date;
            $date_to = date('Y-12-31'); // End of current year
            
            // Clear existing data for the current year to avoid duplicates
            $this->_clearExistingData($date_from, $date_to);
            
            // Generate and insert new attendance summary data
            $inserted_count = $this->_generateAttendanceSummary($date_from, $date_to);
            
            // Update the last_gen timestamp
            $this->_updateLastGenTimestamp();
            
            log_message('info', "Attendance Summary Cron: Successfully processed {$inserted_count} records");
            
            echo json_encode([
                'status' => 'success',
                'message' => "Attendance summary updated successfully. {$inserted_count} records processed.",
                'date_from' => $date_from,
                'date_to' => $date_to,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            log_message('error', 'Attendance Summary Cron Error: ' . $e->getMessage());
            
            echo json_encode([
                'status' => 'error',
                'message' => 'Error updating attendance summary: ' . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Clear existing data for the specified date range
     */
    private function _clearExistingData($date_from, $date_to) {
        $start_year_month = date('Y-m', strtotime($date_from));
        $end_year_month = date('Y-m', strtotime($date_to));
        
        $this->db->where("DATE_FORMAT(duty_date, '%Y-%m') >=", $start_year_month);
        $this->db->where("DATE_FORMAT(duty_date, '%Y-%m') <=", $end_year_month);
        $this->db->delete('person_att_final');
        
        $deleted_rows = $this->db->affected_rows();
        log_message('info', "Cleared {$deleted_rows} existing records for date range {$date_from} to {$date_to}");
    }

    /**
     * Generate attendance summary data using the optimized query
     */
    private function _generateAttendanceSummary($date_from, $date_to) {
        $query = "
            INSERT INTO person_att_final (
                entry_id, ihris_pid, fullname, othername, gender, facility_id, 
                facility_name, district, institution_type, facility_type_name, 
                cadre, department_id, region, schedule_id, duty_date, job, 
                P, O, L, R, X, H, base_line, last_gen
            )
            SELECT
                CONCAT(t.ihris_pid, '-', t.yyyy_mm) AS entry_id,
                t.ihris_pid,
                CONCAT(d.surname, ' ', d.firstname, ' ') AS fullname,
                COALESCE(d.othername, '') AS othername,
                d.gender,
                t.facility_id,
                COALESCE(t.department_id, d.department_id, '') AS department_id,
                d.facility AS facility_name,
                COALESCE(d.district, '') AS district,
                COALESCE(d.facility_type_id, '') AS facility_type_name,
                COALESCE(d.cadre, '') AS cadre,
                COALESCE(d.institutiontype_name, '') AS institution_type,
                COALESCE(d.region, '') AS region,
                CASE
                    WHEN t.P_ct = GREATEST(t.P_ct, t.O_ct, t.L_ct, t.R_ct, t.X_ct, t.H_ct) THEN 22
                    WHEN t.O_ct = GREATEST(t.P_ct, t.O_ct, t.L_ct, t.R_ct, t.X_ct, t.H_ct) THEN 24
                    WHEN t.L_ct = GREATEST(t.P_ct, t.O_ct, t.L_ct, t.R_ct, t.X_ct, t.H_ct) THEN 25
                    WHEN t.R_ct = GREATEST(t.P_ct, t.O_ct, t.L_ct, t.R_ct, t.X_ct, t.H_ct) THEN 23
                    WHEN t.X_ct = GREATEST(t.P_ct, t.O_ct, t.L_ct, t.R_ct, t.X_ct, t.H_ct) THEN 26
                    ELSE 27
                END AS schedule_id,
                t.yyyy_mm AS duty_date,
                COALESCE(d.job, '') AS job,
                t.P_ct AS P, t.O_ct AS O, t.L_ct AS L, t.R_ct AS R, t.X_ct AS X, t.H_ct AS H,
                t.month_days AS base_line,
                NOW() AS last_gen
            FROM (
                SELECT
                    a.ihris_pid,
                    a.facility_id,
                    a.department_id,
                    DATE_FORMAT(a.`date`, '%Y-%m') AS yyyy_mm,
                    DAY(LAST_DAY(a.`date`)) AS month_days,
                    SUM(a.schedule_id = 22) AS P_ct,
                    SUM(a.schedule_id = 24) AS O_ct,
                    SUM(a.schedule_id = 25) AS L_ct,
                    SUM(a.schedule_id = 23) AS R_ct,
                    SUM(a.schedule_id = 26) AS X_ct,
                    SUM(a.schedule_id = 27) AS H_ct
                FROM actuals a
                WHERE a.schedule_id IN (22,23,24,25,26,27)
                    AND a.`date` BETWEEN ? AND ?
                GROUP BY
                    a.ihris_pid, a.facility_id, a.department_id, DATE_FORMAT(a.`date`, '%Y-%m')
            ) t
            LEFT JOIN ihrisdata d ON d.ihris_pid = t.ihris_pid
        ";
        
        $result = $this->db->query($query, [$date_from, $date_to]);
        
        if ($result === FALSE) {
            throw new Exception('Failed to insert attendance summary data: ' . $this->db->error()['message']);
        }
        
        $inserted_count = $this->db->affected_rows();
        return $inserted_count;
    }

    /**
     * Update the last_gen timestamp for tracking
     */
    private function _updateLastGenTimestamp() {
        $this->db->set('last_gen', date('Y-m-d H:i:s'));
        $this->db->where('table_name', 'person_att_final');
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
        
        // Insert initial record
        $this->db->insert('system_tables_timestamp', [
            'table_name' => 'person_att_final',
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
        
        $this->updateAttendanceSummary();
    }

    /**
     * Get status of the last run
     */
    public function getStatus() {
        $this->db->select('last_gen, updated_at');
        $this->db->from('system_tables_timestamp');
        $this->db->where('table_name', 'person_att_final');
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
}
