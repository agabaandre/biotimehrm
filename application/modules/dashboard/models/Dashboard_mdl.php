<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard_mdl extends CI_Model
{

    protected $department;
    

    public function __Construct()
    {

        parent::__Construct();
        $this->department = $this->session->userdata('department_id');
    }

    /**
     * @return string
     */
    protected function sessionFacility()
    {
        $dashboard_facility = $this->session->userdata('dashboard_facility');
        if ($dashboard_facility !== null && $dashboard_facility !== false && trim((string) $dashboard_facility) !== '') {
            return trim((string) $dashboard_facility);
        }

        $facility = $this->session->userdata('facility');
        if ($facility !== null && $facility !== false && trim((string) $facility) !== '') {
            return trim((string) $facility);
        }

        $facility_id = $this->session->userdata('facility_id');
        return ($facility_id !== null && $facility_id !== false) ? trim((string) $facility_id) : '';
    }

    /**
     * @param string $sql
     * @param array  $params
     * @return object|null
     */
    protected function safeQuery($sql, array $params = [])
    {
        $q = $this->db->query($sql, $params);
        return $q ? $q : null;
    }

    /**
     * @return bool
     */
    protected function isEducationDeployment()
    {
        $this->load->helper('deployment_helper');
        return function_exists('is_education_deployment') && is_education_deployment();
    }

    public function getData()
    {
        $facility = $this->sessionFacility();
        //count health workers
        $userdata = $this->session->userdata();
        $date = $userdata['year'] . '-' . $userdata['month'];
        $today = date('Y-m-d');

        $staff = $this->db->query("Select distinct(ihris_pid) from ihrisdata");
        $data['workers'] = $staff->num_rows();
        //count facilities
        $fac = $this->db->query("Select * from facilities");
        $data['facilities'] = $fac->num_rows();
        //departments
        $fac = $this->db->query("Select distinct(department) from ihrisdata");
        $data['departments'] = $fac->num_rows();
        //jobs
        $fac = $this->db->query("Select * from jobs");
        $data['jobs'] = $fac->num_rows();

        //curent _facility_staff

        $fac = $this->db->query("Select ihris_pid from ihrisdata where facility_id='$facility'");
        $mystaff = $data['mystaff'] = $fac->num_rows();

        //number of biotime devs
        $fac = $this->db->query("Select *  from biotime_devices");
        $data['biometrics'] = $fac->num_rows();

        // iHRIS sync date
        $fac = $this->db->query("Select max(last_update) as date  from ihrisdata where facility_id='$facility'");
        $result = $fac->result();
        if (!empty($result) && isset($result[0]->date) && !empty($result[0]->date)) {
            $data['ihris_sync'] = date('j F, Y H:i:s', strtotime($result[0]->date));
        } else {
            $data['ihris_sync'] = 'N/A';
        }
        
        // Attendance generation date
        $fac = $this->db->query("Select max(last_gen) as date  from person_att_final");
        $result = $fac->result();
        if (!empty($result) && isset($result[0]->date) && !empty($result[0]->date)) {
            $data['attendance'] = date('j F, Y H:i:s', strtotime($result[0]->date));
        } else {
            $data['attendance'] = 'No Data Available';
        }
        
        // Roster generation date
        $fac = $this->db->query("Select max(last_gen) as date  from person_dut_final");
        $result = $fac->result();
        if (!empty($result) && isset($result[0]->date) && !empty($result[0]->date)) {
            $data['roster'] = date('j F, Y H:i:s', strtotime($result[0]->date));
        } else {
            $data['roster'] = 'No Data Available';
        }
        
        // BioTime last sync - Get from biotime_devices.last_activity (updated when sync completes)
        // Note: biotime_data gets truncated after processing, so we use last_activity from devices
        // which is updated to the end_date when sync completes successfully
        try {
            // Get the most recent last_activity from any device (represents last sync time)
            $devices = $this->db->query("SELECT MAX(last_activity) as date FROM biotime_devices WHERE last_activity IS NOT NULL AND last_activity != '' AND last_activity != '0000-00-00 00:00:00'");
            
            if ($devices && $devices->num_rows() > 0) {
                $device_row = $devices->row();
                if (isset($device_row->date) && $device_row->date !== NULL && $device_row->date !== '' && $device_row->date !== '0000-00-00 00:00:00') {
                    // Handle both date and datetime formats
                    $sync_date = $device_row->date;
                    $timestamp = strtotime($sync_date);
                    
                    if ($timestamp !== false && $timestamp > 0) {
                        // If it's just a date (Y-m-d), add time component for display
                        if (strlen($sync_date) == 10) {
                            $data['biotime_last'] = date('j F, Y', $timestamp) . ' (Date synced)';
                        } else {
                            $data['biotime_last'] = date('j F, Y H:i:s', $timestamp);
                        }
                    } else {
                        // Fallback: try biotime_data if it has data (before truncation)
                        $fac = $this->db->query("SELECT MAX(punch_time) as date FROM biotime_data WHERE punch_time IS NOT NULL");
                        if ($fac && $fac->num_rows() > 0) {
                            $row = $fac->row();
                            if (isset($row->date) && $row->date !== NULL && $row->date !== '' && $row->date !== '0000-00-00 00:00:00') {
                                $timestamp = strtotime($row->date);
                                if ($timestamp !== false && $timestamp > 0) {
                                    $data['biotime_last'] = date('j F, Y H:i:s', $timestamp);
                                } else {
                                    $data['biotime_last'] = 'N/A';
                                }
                            } else {
                                $data['biotime_last'] = 'N/A';
                            }
                        } else {
                            $data['biotime_last'] = 'N/A';
                        }
                    }
                } else {
                    $data['biotime_last'] = 'N/A';
                }
            } else {
                // Fallback: try biotime_data if it has data (before truncation)
                $fac = $this->db->query("SELECT MAX(punch_time) as date FROM biotime_data WHERE punch_time IS NOT NULL");
                if ($fac && $fac->num_rows() > 0) {
                    $row = $fac->row();
                    if (isset($row->date) && $row->date !== NULL && $row->date !== '' && $row->date !== '0000-00-00 00:00:00') {
                        $timestamp = strtotime($row->date);
                        if ($timestamp !== false && $timestamp > 0) {
                            $data['biotime_last'] = date('j F, Y H:i:s', $timestamp);
                        } else {
                            $data['biotime_last'] = 'N/A';
                        }
                    } else {
                        $data['biotime_last'] = 'N/A';
                    }
                } else {
                    $data['biotime_last'] = 'N/A';
                }
            }
        } catch (Exception $e) {
            log_message('error', 'Dashboard biotime_last sync date error: ' . $e->getMessage());
            $data['biotime_last'] = 'N/A';
        }

        $fac = $this->db->query("SELECT * FROM actuals WHERE  schedule_id = 22 AND actuals.date = '$today' and facility_id='$facility'");
        $data['present'] =  $fac->num_rows();

        $fac = $this->db->query("SELECT * FROM actuals WHERE  schedule_id = 24 AND actuals.date = '$today' and facility_id='$facility'");
        $data['offduty'] =  $fac->num_rows();

        $fac = $this->db->query("SELECT * FROM actuals WHERE  schedule_id = 25 AND actuals.date = '$today' and facility_id='$facility'");
        $data['leave'] =  $fac->num_rows();

        $fac = $this->db->query("SELECT * FROM actuals WHERE  schedule_id = 23 AND actuals.date = '$today' and facility_id='$facility'");
        $data['request'] = $fac->num_rows();
        //people requesting for offical requests

        $fac = $this->db->query("Select *  from requests where date like'$today%'");
        $data['requesting'] = $fac->num_rows();

       


		//dd($this->db);
        return $data;
    }


    public function stats()
    {
        $facility = $this->sessionFacility();
        if ($facility === '') {
            return [
                'workers' => 0,
                'facilities' => 0,
                'departments' => 0,
                'jobs' => 0,
                'mystaff' => 0,
                'biometrics' => 0,
                'cadres' => 0,
                'present' => 0,
                'offduty' => 0,
                'leave' => 0,
                'request' => 0,
                'absent' => 0,
                'error' => 'no_facility',
            ];
        }
        $userdata = $this->session->userdata();
        $year = isset($userdata['year']) && $userdata['year'] ? (int) $userdata['year'] : (int) date('Y');
        $months = $this->dashboardMonthsFromSession();
        $range = $this->dashboardDateRange($year, $months);
        $today = date('Y-m-d');
        $dashboard_empid = (string) $this->session->userdata('dashboard_empid');

        $month_start = $range['start'];
        $month_end = $range['end'];
        $current_ym = substr($today, 0, 7);
        $selected_yms = [];
        foreach ($months as $m) {
            $selected_yms[] = $year . '-' . str_pad((string) $m, 2, '0', STR_PAD_LEFT);
        }
        $max_month = max($months);
        $last_selected_end = date('Y-m-t', strtotime($year . '-' . str_pad((string) $max_month, 2, '0', STR_PAD_LEFT) . '-01'));

        // Daily status: today when current month is in the selection, else last day of latest selected month.
        $status_date = in_array($current_ym, $selected_yms, true) ? $today : $last_selected_end;
        
        // Use a single optimized query for all counts instead of multiple queries
        $counts_query = "
            SELECT 
                (SELECT COUNT(DISTINCT ihris_pid) FROM ihrisdata) as workers,
                (SELECT COUNT(*) FROM facilities) as facilities,
                (SELECT COUNT(DISTINCT department) FROM ihrisdata) as departments,
                (SELECT COUNT(*) FROM jobs) as jobs,
                (SELECT COUNT(*) FROM ihrisdata WHERE facility_id = ?) as mystaff,
                (SELECT COUNT(*) FROM biotime_devices) as biometrics,
                (SELECT COUNT(DISTINCT cadre) FROM ihrisdata WHERE cadre IS NOT NULL AND cadre != '') as cadres
        ";
        
        $counts_result = $this->db->query($counts_query, [$facility]);
        $counts = $counts_result->row();
        
        $data = [
            'workers' => $counts->workers,
            'facilities' => $counts->facilities,
            'departments' => $counts->departments,
            'jobs' => $counts->jobs,
            'mystaff' => $counts->mystaff,
            'biometrics' => $counts->biometrics,
            'cadres' => $counts->cadres
        ];
        
        // Optimize date queries by combining them
        $dates_query = "
            SELECT 
                (SELECT MAX(last_update) FROM ihrisdata WHERE facility_id = ?) as ihris_sync,
                (SELECT MAX(last_gen) FROM person_att_final) as attendance,
                (SELECT MAX(last_gen) FROM person_dut_final) as roster,
                (SELECT MAX(last_activity) FROM biotime_devices WHERE last_activity IS NOT NULL AND last_activity != '' AND last_activity != '0000-00-00 00:00:00') as biotime_last
        ";
        
        $dates_result = $this->db->query($dates_query, [$facility]);
        
        if ($dates_result && $dates_result->num_rows() > 0) {
        $dates = $dates_result->row();
        
        $data['ihris_sync'] = $dates->ihris_sync ? date('j F, Y H:i:s', strtotime($dates->ihris_sync)) : 'N/A';
        $data['attendance'] = $dates->attendance ? date('j F, Y H:i:s', strtotime($dates->attendance)) : 'No Data Available';
        $data['roster'] = $dates->roster ? date('j F, Y H:i:s', strtotime($dates->roster)) : 'No Data Available';
            
            // Use last_activity from biotime_devices as last sync indicator
            // This is updated when sync completes and represents the date synced to
            if (isset($dates->biotime_last) && $dates->biotime_last !== NULL && $dates->biotime_last !== '' && $dates->biotime_last !== '0000-00-00 00:00:00') {
                $timestamp = strtotime($dates->biotime_last);
                if ($timestamp !== false && $timestamp > 0) {
                    // Handle both date and datetime formats
                    if (strlen($dates->biotime_last) == 10) {
                        $data['biotime_last'] = date('j F, Y', $timestamp) . ' (Date synced)';
                    } else {
                        $data['biotime_last'] = date('j F, Y H:i:s', $timestamp);
                    }
                } else {
                    // Fallback: try biotime_data if it has data
                    $fallback = $this->db->query("SELECT MAX(punch_time) as date FROM biotime_data WHERE punch_time IS NOT NULL");
                    if ($fallback && $fallback->num_rows() > 0) {
                        $fallback_row = $fallback->row();
                        if (isset($fallback_row->date) && $fallback_row->date !== NULL && $fallback_row->date !== '') {
                            $timestamp = strtotime($fallback_row->date);
                            if ($timestamp !== false && $timestamp > 0) {
                                $data['biotime_last'] = date('j F, Y H:i:s', $timestamp);
                            } else {
                                $data['biotime_last'] = 'N/A';
                            }
                        } else {
                            $data['biotime_last'] = 'N/A';
                        }
                    } else {
                        $data['biotime_last'] = 'N/A';
                    }
                }
            } else {
                // Fallback: try biotime_data if it has data
                $fallback = $this->db->query("SELECT MAX(punch_time) as date FROM biotime_data WHERE punch_time IS NOT NULL");
                if ($fallback && $fallback->num_rows() > 0) {
                    $fallback_row = $fallback->row();
                    if (isset($fallback_row->date) && $fallback_row->date !== NULL && $fallback_row->date !== '') {
                        $timestamp = strtotime($fallback_row->date);
                        if ($timestamp !== false && $timestamp > 0) {
                            $data['biotime_last'] = date('j F, Y H:i:s', $timestamp);
                        } else {
                            $data['biotime_last'] = 'N/A';
                        }
                    } else {
                        $data['biotime_last'] = 'N/A';
                    }
                } else {
                    $data['biotime_last'] = 'N/A';
                }
            }
        } else {
            $data['ihris_sync'] = 'N/A';
            $data['attendance'] = 'No Data Available';
            $data['roster'] = 'No Data Available';
            $data['biotime_last'] = 'N/A';
        }
        
        // Daily attendance status (unique staff per schedule for selected day)
        $attendance_query = "
            SELECT 
                schedule_id,
                COUNT(DISTINCT ihris_pid) as count
            FROM actuals 
            WHERE facility_id = ? AND date = ?
        ";
        $params = [$facility, $status_date];
        if (!empty($dashboard_empid)) {
            $attendance_query .= " AND ihris_pid = ? ";
            $params[] = $dashboard_empid;
        }
        $attendance_query .= " GROUP BY schedule_id";

        $attendance_result = $this->db->query($attendance_query, $params);
        $attendance_data = $attendance_result->result();
        
        // Initialize with defaults
        $data['present'] = 0;
        $data['offduty'] = 0;
        $data['leave'] = 0;
        $data['request'] = 0;
        
        // Map schedule IDs to data keys
        $schedule_mapping = [
            22 => 'present',
            24 => 'offduty', 
            25 => 'leave',
            23 => 'request'
        ];
        
        foreach ($attendance_data as $row) {
            if (isset($schedule_mapping[$row->schedule_id])) {
                $data[$schedule_mapping[$row->schedule_id]] = $row->count;
            }
        }
        
        // Absent = total active staff - (present + off duty + workshop/request + leave)
        $sum_accounted = (int) $data['present'] + (int) $data['offduty'] + (int) $data['request'] + (int) $data['leave'];
        $total_active = (int) $data['mystaff'];
        $data['absent'] = max(0, $total_active - $sum_accounted);
        $data['daily_avg_hours'] = $this->averageDailyHours($facility, $status_date, $dashboard_empid);
        
        // Monthly attendance stats (staff-days, de-duplicated by staff+date)
        $monthly_query = "
            SELECT 
                schedule_id,
                COUNT(DISTINCT CONCAT(ihris_pid,'|',date)) as cnt
            FROM actuals
            WHERE facility_id = ?
              AND date >= ?
              AND date <= ?
        ";
        $mparams = [$facility, $month_start, $month_end];
        if (!empty($dashboard_empid)) {
            $monthly_query .= " AND ihris_pid = ? ";
            $mparams[] = $dashboard_empid;
        }
        $monthly_query .= " GROUP BY schedule_id";

        $monthly_result = $this->db->query($monthly_query, $mparams);
        $monthly_data = $monthly_result ? $monthly_result->result() : [];

        $data['monthly_present'] = 0;
        $data['monthly_offduty'] = 0;
        $data['monthly_leave'] = 0;
        $data['monthly_request'] = 0;
        foreach ($monthly_data as $row) {
            if (isset($schedule_mapping[$row->schedule_id])) {
                $key = 'monthly_' . $schedule_mapping[$row->schedule_id];
                $data[$key] = (int) $row->cnt;
            }
        }

        $data['dashboard_months'] = array_map(function ($m) {
            return str_pad((string) $m, 2, '0', STR_PAD_LEFT);
        }, $months);
        $data['dashboard_month'] = $data['dashboard_months'][count($data['dashboard_months']) - 1];
        $data['dashboard_year'] = (string) $year;
        $data['dashboard_period_label'] = $this->dashboardPeriodLabel($year, $months);
        $data['dashboard_empid'] = $dashboard_empid;
        $data['status_date'] = $status_date;
        $data['avg_hours'] = $this->averageHoursForFacilityRange($facility, $month_start, $month_end, $dashboard_empid);
        
        return $data;
    }

    /**
     * Average hours worked per staff member for a facility date range.
     *
     * @param string $facility
     * @param string $start_date
     * @param string $end_date
     * @param string $empid
     * @return float
     */
    public function averageHoursForFacilityRange($facility, $start_date, $end_date, $empid = '')
    {
        if (!$this->db->table_exists('clk_diff') || $facility === '') {
            return 0.0;
        }

        $sql = "SELECT COALESCE(SUM(time_diff) / NULLIF(COUNT(DISTINCT pid), 0), 0) AS avg_hours
            FROM clk_diff
            WHERE facility_id = ? AND date >= ? AND date <= ?
              AND time_diff IS NOT NULL AND time_diff > 0";
        $params = [$facility, $start_date, $end_date];
        if ($empid !== '') {
            $sql .= ' AND pid = ?';
            $params[] = $empid;
        }

        $row = $this->safeQuery($sql, $params);
        $row = $row ? $row->row() : null;

        return ($row && $row->avg_hours !== null) ? round((float) $row->avg_hours, 1) : 0.0;
    }

    /**
     * Average hours worked per staff member for a single day (facility-scoped).
     *
     * @param string $facility
     * @param string $date
     * @param string $empid
     * @return float
     */
    public function averageDailyHours($facility, $date, $empid = '')
    {
        if (!$this->db->table_exists('clk_diff') || $facility === '' || $date === '') {
            return 0.0;
        }

        $sql = "SELECT COALESCE(SUM(time_diff) / NULLIF(COUNT(DISTINCT pid), 0), 0) AS avg_hours
            FROM clk_diff
            WHERE facility_id = ? AND date = ?
              AND time_diff IS NOT NULL AND time_diff > 0";
        $params = [$facility, $date];
        if ($empid !== '') {
            $sql .= ' AND pid = ?';
            $params[] = $empid;
        }

        $row = $this->safeQuery($sql, $params);
        $row = $row ? $row->row() : null;

        return ($row && $row->avg_hours !== null) ? round((float) $row->avg_hours, 1) : 0.0;
    }

    public function avghours()
    {
        $userdata = $this->session->userdata();
        $year = isset($userdata['year']) ? (int) $userdata['year'] : (int) date('Y');
        $months = $this->dashboardMonthsFromSession();
        $range = $this->dashboardDateRange($year, $months);
        $facility = $_SESSION['facility'];

        $data['avg_hours'] = $this->averageHoursForFacilityRange(
            $facility,
            $range['start'],
            $range['end'],
            (string) $this->session->userdata('dashboard_empid')
        );

        return $data;
    }
    
    /**
     * Get only essential statistics for fast dashboard loading
     */
    public function getEssentialStats()
    {
        $facility = $_SESSION['facility'];
        $today = date('Y-m-d');
        
        // Get only the most critical data with optimized queries
        $essential_query = "
            SELECT 
                (SELECT COUNT(DISTINCT ihris_pid) FROM ihrisdata) as workers,
                (SELECT COUNT(*) FROM ihrisdata WHERE facility_id = ?) as mystaff,
                (SELECT COUNT(*) FROM actuals WHERE facility_id = ? AND date = ? AND schedule_id = 22) as present,
                (SELECT COUNT(*) FROM actuals WHERE facility_id = ? AND date = ? AND schedule_id = 24) as offduty,
                (SELECT COUNT(*) FROM actuals WHERE facility_id = ? AND date = ? AND schedule_id = 25) as leave,
                (SELECT COUNT(*) FROM actuals WHERE facility_id = ? AND date = ? AND schedule_id = 23) as request
        ";
        
        $result = $this->db->query($essential_query, [
            $facility, $facility, $today, $facility, $today, 
            $facility, $today, $facility, $today
        ]);
        
        $data = $result->row();
        
        return [
            'workers' => $data->workers,
            'mystaff' => $data->mystaff,
            'present' => $data->present,
            'offduty' => $data->offduty,
            'leave' => $data->leave,
            'request' => $data->request
        ];
    }

    /**
     * Lightweight live snapshot: today's counts + recent check-ins.
     *
     * @return array<string, mixed>
     */
    public function livePulse()
    {
        $facility = $this->sessionFacility();
        if ($facility === '') {
            return ['live' => false, 'error' => 'no_facility'];
        }

        $userdata = $this->session->userdata();
        $year = isset($userdata['year']) && $userdata['year'] ? (int) $userdata['year'] : (int) date('Y');
        $month = isset($userdata['month']) && $userdata['month'] ? (int) $userdata['month'] : (int) date('m');
        if ($month < 1 || $month > 12) {
            $month = (int) date('m');
        }
        $dashboard_empid = (string) $this->session->userdata('dashboard_empid');
        $today = date('Y-m-d');
        $month_start = $year . '-' . str_pad((string) $month, 2, '0', STR_PAD_LEFT) . '-01';
        $month_end = date('Y-m-t', strtotime($month_start));
        $status_date = (substr($today, 0, 7) === substr($month_start, 0, 7)) ? $today : $month_end;

        $mystaff = 0;
        $staff_q = $this->safeQuery('SELECT COUNT(*) AS c FROM ihrisdata WHERE facility_id = ?', [$facility]);
        if ($staff_q && $staff_q->num_rows() > 0) {
            $mystaff = (int) $staff_q->row()->c;
        }

        $data = [
            'live' => true,
            'present' => 0,
            'offduty' => 0,
            'leave' => 0,
            'request' => 0,
            'absent' => 0,
            'mystaff' => $mystaff,
            'status_date' => $status_date,
            'generated_at' => date('c'),
            'recent' => [],
            'clock_ins_today' => 0,
        ];

        if ($this->isEducationDeployment()) {
            $this->_applyEducationLiveCounts($data, $facility, $status_date, $dashboard_empid);
        } else {
            $this->_applyActualsLiveCounts($data, $facility, $status_date, $dashboard_empid);
        }

        $sum_accounted = (int) $data['present'] + (int) $data['offduty'] + (int) $data['request'] + (int) $data['leave'];
        $data['absent'] = max(0, $mystaff - $sum_accounted);

        if ($dashboard_empid === '' && $status_date === $today) {
            $data['recent'] = $this->_recentClockIns($facility, $today, 10);
        }

        return $data;
    }

    /**
     * MOH: duty status from actuals (BioTime / roster pipeline).
     */
    protected function _applyActualsLiveCounts(array &$data, $facility, $status_date, $dashboard_empid)
    {
        if (!$this->db->table_exists('actuals')) {
            $data['clock_ins_today'] = $this->_countMobileClockIns($facility, $status_date, $dashboard_empid);
            $data['present'] = $data['clock_ins_today'];
            return;
        }

        $attendance_query = '
            SELECT schedule_id, COUNT(DISTINCT ihris_pid) AS count
            FROM actuals
            WHERE facility_id = ? AND date = ?
        ';
        $params = [$facility, $status_date];
        if ($dashboard_empid !== '') {
            $attendance_query .= ' AND ihris_pid = ?';
            $params[] = $dashboard_empid;
        }
        $attendance_query .= ' GROUP BY schedule_id';

        $schedule_mapping = [
            22 => 'present',
            24 => 'offduty',
            25 => 'leave',
            23 => 'request',
        ];

        $attendance_result = $this->safeQuery($attendance_query, $params);
        if ($attendance_result) {
            foreach ($attendance_result->result() as $row) {
                if (isset($schedule_mapping[$row->schedule_id])) {
                    $data[$schedule_mapping[$row->schedule_id]] = (int) $row->count;
                }
            }
        }

        $data['clock_ins_today'] = (int) $data['present'];
    }

    /**
     * Education: mobile clock-ins (no BioTime / actuals pipeline).
     */
    protected function _applyEducationLiveCounts(array &$data, $facility, $status_date, $dashboard_empid)
    {
        $clock_ins = $this->_countMobileClockIns($facility, $status_date, $dashboard_empid);
        $data['present'] = $clock_ins;
        $data['clock_ins_today'] = $clock_ins;
    }

    /**
     * @param string $facility
     * @param string $date
     * @param string $dashboard_empid
     * @return int
     */
    protected function _countMobileClockIns($facility, $date, $dashboard_empid = '')
    {
        if (!$this->db->table_exists('mobileclk_log')) {
            return 0;
        }

        $sql = "
            SELECT COUNT(DISTINCT ihris_pid) AS c
            FROM mobileclk_log
            WHERE facility_id = ? AND date = ? AND time_in IS NOT NULL
        ";
        $params = [$facility, $date];
        if ($dashboard_empid !== '') {
            $sql .= ' AND ihris_pid = ?';
            $params[] = $dashboard_empid;
        }

        $q = $this->safeQuery($sql, $params);
        if ($q && $q->num_rows() > 0) {
            return (int) $q->row()->c;
        }

        return 0;
    }

    /**
     * @param string $facility
     * @param string $date
     * @param int    $limit
     * @return array<int, array<string, string>>
     */
    private function _recentClockIns($facility, $date, $limit = 10)
    {
        $items = [];
        $limit = max(1, (int) $limit);

        if ($this->db->table_exists('mobileclk_log')) {
            $q = $this->safeQuery("
                SELECT m.ihris_pid, m.time_in, 'mobile' AS source,
                    TRIM(CONCAT(COALESCE(i.surname,''), ' ', COALESCE(i.firstname,''))) AS staff_name
                FROM mobileclk_log m
                LEFT JOIN ihrisdata i ON i.ihris_pid = m.ihris_pid
                WHERE m.facility_id = ? AND m.date = ? AND m.time_in IS NOT NULL
                ORDER BY m.time_in DESC
                LIMIT ?
            ", [$facility, $date, $limit]);
            if ($q) {
                foreach ($q->result() as $r) {
                    $name = trim((string) $r->staff_name);
                    $items[] = [
                        'ihris_pid' => (string) $r->ihris_pid,
                        'name' => $name !== '' ? $name : (string) $r->ihris_pid,
                        'time' => $this->_formatClockTime($r->time_in),
                        'time_raw' => (string) $r->time_in,
                        'source' => 'mobile',
                    ];
                }
            }
        }

        if (count($items) < $limit && $this->db->table_exists('clk_log')) {
            $seen = array_column($items, 'ihris_pid');
            $need = $limit - count($items);
            $q = $this->safeQuery("
                SELECT cl.ihris_pid, cl.time_in, COALESCE(cl.source, 'device') AS source,
                    TRIM(CONCAT(COALESCE(i.surname,''), ' ', COALESCE(i.firstname,''))) AS staff_name
                FROM clk_log cl
                LEFT JOIN ihrisdata i ON i.ihris_pid = cl.ihris_pid
                WHERE cl.facility_id = ? AND cl.date = ? AND cl.time_in IS NOT NULL
                ORDER BY cl.time_in DESC
                LIMIT ?
            ", [$facility, $date, $need + count($seen)]);
            if ($q) {
                foreach ($q->result() as $r) {
                    if (in_array((string) $r->ihris_pid, $seen, true)) {
                        continue;
                    }
                    $name = trim((string) $r->staff_name);
                    $source = strtolower((string) $r->source);
                    $items[] = [
                        'ihris_pid' => (string) $r->ihris_pid,
                        'name' => $name !== '' ? $name : (string) $r->ihris_pid,
                        'time' => $this->_formatClockTime($r->time_in),
                        'time_raw' => (string) $r->time_in,
                        'source' => ($source === 'bio-time') ? 'biotime' : 'device',
                    ];
                    if (count($items) >= $limit) {
                        break;
                    }
                }
            }
        }

        usort($items, function ($a, $b) {
            return strcmp((string) $b['time_raw'], (string) $a['time_raw']);
        });

        $items = array_slice($items, 0, $limit);
        foreach ($items as &$item) {
            unset($item['time_raw']);
        }
        unset($item);

        return $items;
    }

    /**
     * @param mixed $time
     * @return string
     */
    private function _formatClockTime($time)
    {
        $time = trim((string) $time);
        if ($time === '') {
            return '';
        }
        $ts = strtotime($time);
        return $ts ? date('H:i', $ts) : $time;
    }

    /**
     * Selected dashboard months (1–12) from session; defaults to current month.
     *
     * @return array<int, int>
     */
    public function dashboardMonthsFromSession()
    {
        $raw = $this->session->userdata('dashboard_months');
        $months = [];
        if (is_array($raw)) {
            foreach ($raw as $m) {
                $m = (int) $m;
                if ($m >= 1 && $m <= 12) {
                    $months[] = $m;
                }
            }
        } elseif (is_string($raw) && trim($raw) !== '') {
            foreach (explode(',', $raw) as $m) {
                $m = (int) trim($m);
                if ($m >= 1 && $m <= 12) {
                    $months[] = $m;
                }
            }
        }
        if (empty($months)) {
            $months = [(int) ($this->session->userdata('month') ?: date('m'))];
        }
        $months = array_values(array_unique($months));
        sort($months);

        return $months;
    }

    /**
     * @param int        $year
     * @param array<int, int> $months
     * @return array{start: string, end: string}
     */
    public function dashboardDateRange($year, array $months)
    {
        $year = (int) $year;
        $months = array_values(array_unique(array_map('intval', $months)));
        sort($months);
        if (empty($months)) {
            $months = [(int) date('m')];
        }
        $first = $year . '-' . str_pad((string) $months[0], 2, '0', STR_PAD_LEFT) . '-01';
        $last_ym = $year . '-' . str_pad((string) $months[count($months) - 1], 2, '0', STR_PAD_LEFT) . '-01';

        return [
            'start' => $first,
            'end'   => date('Y-m-t', strtotime($last_ym)),
        ];
    }

    /**
     * Human-readable label for selected month(s) in a calendar year.
     *
     * @param int               $year
     * @param array<int, int>   $months
     * @return string
     */
    public function dashboardPeriodLabel($year, array $months)
    {
        $year = (int) $year;
        $months = array_values(array_unique(array_map('intval', $months)));
        sort($months);
        if (count($months) === 12) {
            return (string) $year . ' (all months)';
        }
        if (count($months) === 1) {
            return date('M Y', strtotime($year . '-' . str_pad((string) $months[0], 2, '0', STR_PAD_LEFT) . '-01'));
        }
        $names = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $first = $names[$months[0] - 1];
        $last = $names[$months[count($months) - 1] - 1];

        return $first . '–' . $last . ' ' . $year;
    }

    /**
     * @param array<int, int> $months
     * @param int             $year
     * @param array<int, mixed> $params
     * @param string          $dateColumn
     * @return string
     */
    protected function dashboardMonthSqlIn(array $months, $year, array &$params, $dateColumn = 'a.date')
    {
        $year = (int) $year;
        $placeholders = [];
        foreach ($months as $m) {
            $placeholders[] = '?';
            $params[] = $year . '-' . str_pad((string) (int) $m, 2, '0', STR_PAD_LEFT);
        }

        return "DATE_FORMAT({$dateColumn},'%Y-%m') IN (" . implode(',', $placeholders) . ")";
    }

    /**
     * National / scoped dashboard filters from session.
     *
     * @return array<string, string>
     */
    public function dashboardScopeFromSession()
    {
        return [
            'region'            => trim((string) $this->session->userdata('dashboard_region')),
            'district'          => trim((string) $this->session->userdata('dashboard_district')),
            'facility_id'       => trim((string) ($this->session->userdata('dashboard_facility_filter') ?: $this->session->userdata('dashboard_facility') ?: '')),
            'institution_type'  => trim((string) $this->session->userdata('dashboard_institution_type')),
            'cadre'             => trim((string) $this->session->userdata('dashboard_cadre')),
        ];
    }

    /**
     * Build SQL WHERE fragments for scoped analytics (actuals + ihrisdata).
     *
     * @param array<string, string> $scope
     * @param array<int, mixed>     $params
     * @return string
     */
    protected function buildScopeSql(array $scope, array &$params, $actualsAlias = 'a', $ihrisAlias = 'i')
    {
        $parts = [];

        if (!empty($scope['facility_id'])) {
            $parts[] = $actualsAlias . '.facility_id = ?';
            $params[] = $scope['facility_id'];
        }

        if (!empty($scope['region']) && $this->db->field_exists('region', 'ihrisdata')) {
            $parts[] = 'TRIM(' . $ihrisAlias . '.region) = ?';
            $params[] = $scope['region'];
        }

        if (!empty($scope['district'])) {
            $this->load->library('facility_switch_cache', null, 'fsc');
            $names = $this->fsc->get_district_names_in_group($scope['district']);
            if (count($names) > 1) {
                $placeholders = implode(',', array_fill(0, count($names), '?'));
                $parts[] = 'TRIM(' . $ihrisAlias . ".district) IN ({$placeholders})";
                foreach ($names as $n) {
                    $params[] = $n;
                }
            } else {
                $parts[] = 'TRIM(' . $ihrisAlias . '.district) = ?';
                $params[] = $scope['district'];
            }
        }

        $inst_col = ihris_institution_type_column($this->db);
        if (!empty($scope['institution_type']) && $inst_col !== null) {
            $parts[] = 'TRIM(' . $ihrisAlias . '.' . mysql8_quote_ident($inst_col) . ') = ?';
            $params[] = $scope['institution_type'];
        }

        if (!empty($scope['cadre']) && $this->db->field_exists('cadre', 'ihrisdata')) {
            $parts[] = 'TRIM(' . $ihrisAlias . '.cadre) = ?';
            $params[] = $scope['cadre'];
        }

        return empty($parts) ? '1=1' : implode(' AND ', $parts);
    }

    /**
     * National attendance & absenteeism rates for selected calendar month(s).
     *
     * @param array<string, string> $scope
     * @param int                   $year
     * @param array<int, int>       $months
     * @return array<string, mixed>
     */
    public function nationalAttendanceRates(array $scope, $year, array $months)
    {
        $year = (int) $year;
        $months = array_values(array_unique(array_map('intval', $months)));
        sort($months);
        if (empty($months)) {
            $months = [(int) date('m')];
        }
        $params = [];
        $month_sql = $this->dashboardMonthSqlIn($months, $year, $params);
        $scope_sql = $this->buildScopeSql($scope, $params);
        $period_label = $this->dashboardPeriodLabel($year, $months);
        $range = $this->dashboardDateRange($year, $months);

        $sql = "SELECT
            SUM(t.present) AS present,
            SUM(t.off) AS off_days,
            SUM(t.own_leave) AS own_leave,
            SUM(t.official) AS official,
            SUM(t.holiday) AS holiday,
            SUM(GREATEST(0, t.base_line - (t.off + t.own_leave + t.official + t.holiday))) AS days_supposed,
            SUM(GREATEST(0, GREATEST(0, t.base_line - (t.off + t.own_leave + t.official + t.holiday)) - t.present)) AS days_absent
        FROM (
            SELECT
                a.ihris_pid,
                DATE_FORMAT(a.date,'%Y-%m') AS ym,
                SUM(CASE WHEN s.letter = 'P' THEN 1 ELSE 0 END) AS present,
                SUM(CASE WHEN s.letter = 'O' THEN 1 ELSE 0 END) AS off,
                SUM(CASE WHEN s.letter = 'L' THEN 1 ELSE 0 END) AS own_leave,
                SUM(CASE WHEN s.letter = 'R' THEN 1 ELSE 0 END) AS official,
                SUM(CASE WHEN s.letter = 'H' THEN 1 ELSE 0 END) AS holiday,
                MAX(CAST(DAY(LAST_DAY(CONCAT(DATE_FORMAT(a.date,'%Y-%m'),'-01'))) AS UNSIGNED)) AS base_line
            FROM actuals a
            LEFT JOIN schedules s ON s.schedule_id = a.schedule_id
            LEFT JOIN ihrisdata i ON i.ihris_pid = a.ihris_pid
            WHERE {$month_sql}
              AND a.ihris_pid IS NOT NULL AND TRIM(a.ihris_pid) <> ''
              AND {$scope_sql}
            GROUP BY a.ihris_pid, DATE_FORMAT(a.date,'%Y-%m')
        ) t";

        $row = $this->safeQuery($sql, $params);
        $row = $row ? $row->row() : null;
        $days_supposed = $row ? (int) $row->days_supposed : 0;
        $present = $row ? (int) $row->present : 0;
        $days_absent = $row ? (int) $row->days_absent : 0;

        $attendance_rate = ($days_supposed > 0) ? round(($present / $days_supposed) * 100, 1) : 0.0;
        $absenteeism_rate = ($days_supposed > 0) ? round(($days_absent / $days_supposed) * 100, 1) : 0.0;
        $avg_hours = $this->averageHoursForScopeRange($scope, $range['start'], $range['end']);

        return [
            'month'             => $period_label,
            'period_label'      => $period_label,
            'months'            => array_map(function ($m) {
                return str_pad((string) $m, 2, '0', STR_PAD_LEFT);
            }, $months),
            'attendance_rate'   => $attendance_rate,
            'absenteeism_rate'  => $absenteeism_rate,
            'days_supposed'     => $days_supposed,
            'present'           => $present,
            'days_absent'       => $days_absent,
            'avg_hours'         => $avg_hours,
        ];
    }

    /**
     * Average hours worked per staff member for scoped filters (date range).
     *
     * @param array<string, string> $scope
     * @return float
     */
    public function averageHoursForScopeRange(array $scope, $start_date, $end_date)
    {
        if (!$this->db->table_exists('clk_diff')) {
            return 0.0;
        }

        $params = [$start_date, $end_date];
        $scope_sql = $this->buildScopeSql($scope, $params, 'c', 'i');

        $sql = "SELECT COALESCE(SUM(c.time_diff) / NULLIF(COUNT(DISTINCT c.pid), 0), 0) AS avg_hours
            FROM clk_diff c
            LEFT JOIN ihrisdata i ON i.ihris_pid = c.pid
            WHERE c.date >= ? AND c.date <= ?
              AND c.time_diff IS NOT NULL AND c.time_diff > 0
              AND {$scope_sql}";

        $row = $this->safeQuery($sql, $params);
        $row = $row ? $row->row() : null;

        return ($row && $row->avg_hours !== null) ? round((float) $row->avg_hours, 1) : 0.0;
    }

    /**
     * @deprecated Use averageHoursForScopeRange()
     */
    public function averageHoursForScope(array $scope, $year, $month)
    {
        $range = $this->dashboardDateRange((int) $year, [(int) $month]);

        return $this->averageHoursForScopeRange($scope, $range['start'], $range['end']);
    }

    /**
     * Redis-backed analytics bundle for dashboard charts.
     *
     * @param array<string, string> $scope
     * @param int                   $year
     * @param array<int, int>       $months
     * @return array<string, mixed>
     */
    public function analyticsCharts(array $scope, $year, array $months, $empid = '')
    {
        $this->load->library('dashboard_cache_store', null, 'dash_cache');
        $this->config->load('dashboard_cache', true, true);
        $cfg = $this->config->item('dashboard_cache');
        $ttl = is_array($cfg) && isset($cfg['stats_ttl']) ? max(60, (int) $cfg['stats_ttl']) : 120;

        $year = (int) $year;
        $months = array_values(array_unique(array_map('intval', $months)));
        sort($months);
        if (empty($months)) {
            $months = [(int) date('m')];
        }
        $range = $this->dashboardDateRange($year, $months);
        $period_label = $this->dashboardPeriodLabel($year, $months);

        $key = 'analytics_' . md5(json_encode([
            'scope'  => $scope,
            'y'      => $year,
            'months' => $months,
            'emp'    => (string) $empid,
        ]));

        $cached = $this->dash_cache->read($key);
        if (is_array($cached)) {
            $cached['cached'] = true;
            return $cached;
        }

        $params = [$range['start'], $range['end']];
        $scope_sql = $this->buildScopeSql($scope, $params);
        $empid = trim((string) $empid);
        $emp_sql = '';
        if ($empid !== '') {
            $emp_sql = ' AND a.ihris_pid = ? ';
            $params[] = $empid;
        }

        // Average daily working staff per month (Present schedule)
        $avg_sql = "SELECT DATE_FORMAT(a.date,'%Y-%m') AS ym,
            COUNT(DISTINCT CONCAT(a.ihris_pid,'|',a.date)) AS uniq_emp_days,
            COUNT(DISTINCT a.date) AS uniq_days
            FROM actuals a
            LEFT JOIN ihrisdata i ON i.ihris_pid = a.ihris_pid
            WHERE a.schedule_id = 22
              AND a.date >= ? AND a.date <= ?
              AND {$scope_sql}{$emp_sql}
            GROUP BY DATE_FORMAT(a.date,'%Y-%m')
            ORDER BY ym ASC";
        $avg_rows = $this->safeQuery($avg_sql, $params);
        $avg_map = [];
        if ($avg_rows) {
            foreach ($avg_rows->result() as $r) {
                $days = (int) $r->uniq_days;
                $avg_map[$r->ym] = ($days > 0) ? (int) round((int) $r->uniq_emp_days / $days) : 0;
            }
        }

        // Monthly attendance / absenteeism rates for selected months
        $rate_params = [$range['start'], $range['end']];
        $rate_scope = $this->buildScopeSql($scope, $rate_params);
        $rate_emp = '';
        if ($empid !== '') {
            $rate_emp = ' AND a.ihris_pid = ? ';
            $rate_params[] = $empid;
        }
        $rate_sql = "SELECT t.ym,
            SUM(t.present) AS present,
            SUM(t.off) AS off_days,
            SUM(t.own_leave) AS own_leave,
            SUM(t.official) AS official,
            SUM(t.holiday) AS holiday,
            SUM(t.base_line) AS calendar_days,
            SUM(GREATEST(0, t.base_line - (t.off + t.own_leave + t.official + t.holiday))) AS days_supposed,
            SUM(GREATEST(0, GREATEST(0, t.base_line - (t.off + t.own_leave + t.official + t.holiday)) - t.present)) AS days_absent
            FROM (
                SELECT DATE_FORMAT(a.date,'%Y-%m') AS ym, a.ihris_pid,
                    SUM(CASE WHEN s.letter = 'P' THEN 1 ELSE 0 END) AS present,
                    SUM(CASE WHEN s.letter = 'O' THEN 1 ELSE 0 END) AS off,
                    SUM(CASE WHEN s.letter = 'L' THEN 1 ELSE 0 END) AS own_leave,
                    SUM(CASE WHEN s.letter = 'R' THEN 1 ELSE 0 END) AS official,
                    SUM(CASE WHEN s.letter = 'H' THEN 1 ELSE 0 END) AS holiday,
                    MAX(CAST(DAY(LAST_DAY(CONCAT(DATE_FORMAT(a.date,'%Y-%m'),'-01'))) AS UNSIGNED)) AS base_line
                FROM actuals a
                LEFT JOIN schedules s ON s.schedule_id = a.schedule_id
                LEFT JOIN ihrisdata i ON i.ihris_pid = a.ihris_pid
                WHERE a.date >= ? AND a.date <= ?
                  AND {$rate_scope}{$rate_emp}
                GROUP BY DATE_FORMAT(a.date,'%Y-%m'), a.ihris_pid
            ) t
            GROUP BY t.ym ORDER BY t.ym ASC";

        $rate_rows = $this->safeQuery($rate_sql, $rate_params);
        $att_rate_map = [];
        $abs_rate_map = [];
        $sched_present_map = [];
        $sched_off_map = [];
        $sched_leave_map = [];
        $sched_official_map = [];
        $sched_holiday_map = [];
        $sched_unaccounted_map = [];
        if ($rate_rows) {
            foreach ($rate_rows->result() as $r) {
                $sup = (int) $r->days_supposed;
                $cal = (int) $r->calendar_days;
                $att_rate_map[$r->ym] = ($sup > 0) ? round(((int) $r->present / $sup) * 100, 1) : 0;
                $abs_rate_map[$r->ym] = ($sup > 0) ? round(((int) $r->days_absent / $sup) * 100, 1) : 0;
                if ($cal > 0) {
                    $sched_present_map[$r->ym] = round(((int) $r->present / $cal) * 100, 1);
                    $sched_off_map[$r->ym] = round(((int) $r->off_days / $cal) * 100, 1);
                    $sched_leave_map[$r->ym] = round(((int) $r->own_leave / $cal) * 100, 1);
                    $sched_official_map[$r->ym] = round(((int) $r->official / $cal) * 100, 1);
                    $sched_holiday_map[$r->ym] = round(((int) $r->holiday / $cal) * 100, 1);
                    $sched_unaccounted_map[$r->ym] = round(((int) $r->days_absent / $cal) * 100, 1);
                } else {
                    $sched_present_map[$r->ym] = 0;
                    $sched_off_map[$r->ym] = 0;
                    $sched_leave_map[$r->ym] = 0;
                    $sched_official_map[$r->ym] = 0;
                    $sched_holiday_map[$r->ym] = 0;
                    $sched_unaccounted_map[$r->ym] = 0;
                }
            }
        }

        $period = [];
        $avg_daily = [];
        $att_trend = [];
        $abs_trend = [];
        $sched_present = [];
        $sched_off = [];
        $sched_leave = [];
        $sched_official = [];
        $sched_holiday = [];
        $sched_unaccounted = [];
        foreach ($months as $m) {
            $ym = $year . '-' . str_pad((string) $m, 2, '0', STR_PAD_LEFT);
            $period[] = date('M Y', strtotime($ym . '-01'));
            $avg_daily[] = isset($avg_map[$ym]) ? $avg_map[$ym] : 0;
            $att_trend[] = isset($att_rate_map[$ym]) ? $att_rate_map[$ym] : 0;
            $abs_trend[] = isset($abs_rate_map[$ym]) ? $abs_rate_map[$ym] : 0;
            $sched_present[] = isset($sched_present_map[$ym]) ? $sched_present_map[$ym] : 0;
            $sched_off[] = isset($sched_off_map[$ym]) ? $sched_off_map[$ym] : 0;
            $sched_leave[] = isset($sched_leave_map[$ym]) ? $sched_leave_map[$ym] : 0;
            $sched_official[] = isset($sched_official_map[$ym]) ? $sched_official_map[$ym] : 0;
            $sched_holiday[] = isset($sched_holiday_map[$ym]) ? $sched_holiday_map[$ym] : 0;
            $sched_unaccounted[] = isset($sched_unaccounted_map[$ym]) ? $sched_unaccounted_map[$ym] : 0;
        }

        $payload = [
            'year'                  => $year,
            'months'                => array_map(function ($m) {
                return str_pad((string) $m, 2, '0', STR_PAD_LEFT);
            }, $months),
            'period_label'          => $period_label,
            'fy_label'              => $period_label,
            'period'                => $period,
            'avg_daily_workers'     => $avg_daily,
            'attendance_rate'       => $att_trend,
            'absenteeism_rate'      => $abs_trend,
            'schedule_present'      => $sched_present,
            'schedule_off'          => $sched_off,
            'schedule_leave'        => $sched_leave,
            'schedule_official'     => $sched_official,
            'schedule_holiday'      => $sched_holiday,
            'schedule_unaccounted'  => $sched_unaccounted,
            'cached'                => false,
        ];

        $this->dash_cache->write($key, $payload, $ttl);
        return $payload;
    }

   }
