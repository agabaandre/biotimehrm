<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard_mdl extends CI_Model
{

    protected $department;
    

    public function __Construct()
    {

        parent::__Construct();
        @$this->department = $this->session->userdata['department_id'];
    }
    public function getData()
    {
        $facility = $_SESSION['facility'];
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
        $facility = $_SESSION['facility'];
        $userdata = $this->session->userdata();
        $year = isset($userdata['year']) && $userdata['year'] ? (int) $userdata['year'] : (int) date('Y');
        $month = isset($userdata['month']) && $userdata['month'] ? (int) $userdata['month'] : (int) date('m');
        $date = $year . '-' . str_pad((string)$month, 2, '0', STR_PAD_LEFT);
        $today = date('Y-m-d');
        $dashboard_empid = (string) $this->session->userdata('dashboard_empid');

        $month_start = $date . '-01';
        $month_end = date('Y-m-t', strtotime($month_start));

        // For "Daily Attendance Status" we show today's counts if the selected month is current,
        // otherwise we show the last day of the selected month.
        $status_date = (substr($today, 0, 7) === substr($month_start, 0, 7)) ? $today : $month_end;
        
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

        $data['dashboard_month'] = str_pad((string)$month, 2, '0', STR_PAD_LEFT);
        $data['dashboard_year'] = (string) $year;
        $data['dashboard_empid'] = $dashboard_empid;
        $data['status_date'] = $status_date;
        
        return $data;
    }
    public function avghours()
    {
        $userdata = $this->session->userdata();
        $year = isset($userdata['year']) ? $userdata['year'] : date('Y');
        $month = isset($userdata['month']) ? $userdata['month'] : date('m');
        $facility = $_SESSION['facility'];

        // Optimize query: Use date range instead of DATE_FORMAT to allow index usage
        // Calculate start and end dates for the month
        $start_date = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
        $end_date = date('Y-m-t', strtotime($start_date)); // Last day of the month
        
        // Optimized query with date range and proper aggregation
        // Use COALESCE to handle NULL values and ensure we return 0 if no data
        $query = "
            SELECT 
                COALESCE(SUM(time_diff) / NULLIF(COUNT(DISTINCT pid), 0), 0) as avg
            FROM clk_diff 
            WHERE facility_id = ? 
            AND date >= ? 
            AND date <= ?
            AND time_diff IS NOT NULL
            AND time_diff > 0
        ";
        
        $fac = $this->db->query($query, [$facility, $start_date, $end_date]);
        $result = $fac->row();
        
        // Handle null result gracefully
        $data['avg_hours'] = ($result && isset($result->avg) && $result->avg !== null) ? (float)$result->avg : 0;

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

   }
