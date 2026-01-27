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
        
        // BioTime last sync - Get most recent punch_time from biotime_data (synced from PostgreSQL)
        // This represents the most recent attendance data that was synced from PostgreSQL
        $fac = $this->db->query("Select max(punch_time) as date from biotime_data");
        $result = $fac->result();
        if (!empty($result) && isset($result[0]->date) && !empty($result[0]->date)) {
            $data['biotime_last'] = date('j F, Y H:i:s', strtotime($result[0]->date));
        } else {
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
        $date = $userdata['year'] . '-' . $userdata['month'];
        $today = date('Y-m-d');
        
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
                (SELECT MAX(punch_time) FROM biotime_data) as biotime_last
        ";
        
        $dates_result = $this->db->query($dates_query, [$facility]);
        $dates = $dates_result->row();
        
        $data['ihris_sync'] = $dates->ihris_sync ? date('j F, Y H:i:s', strtotime($dates->ihris_sync)) : 'N/A';
        $data['attendance'] = $dates->attendance ? date('j F, Y H:i:s', strtotime($dates->attendance)) : 'No Data Available';
        $data['roster'] = $dates->roster ? date('j F, Y H:i:s', strtotime($dates->roster)) : 'No Data Available';
        // Use punch_time from biotime_data as last sync indicator (data synced from PostgreSQL)
        $data['biotime_last'] = $dates->biotime_last ? date('j F, Y H:i:s', strtotime($dates->biotime_last)) : 'N/A';
        
        // Optimize attendance status queries with a single query
        $attendance_query = "
            SELECT 
                schedule_id,
                COUNT(*) as count
            FROM actuals 
            WHERE facility_id = ? AND date = ?
            GROUP BY schedule_id
        ";
        
        $attendance_result = $this->db->query($attendance_query, [$facility, $today]);
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
        
        // Optimize requests query
        $requests_query = "SELECT COUNT(*) as count FROM requests WHERE date LIKE ?";
        $requests_result = $this->db->query($requests_query, [$today . '%']);
        $data['requesting'] = $requests_result->row()->count;
        
        return $data;
    }
    public function avghours()
    {
        $userdata = $this->session->userdata();
        $date = $userdata['year'] . '-' . $userdata['month'];
        $facility = $_SESSION['facility'];


        $query = "SELECT (SUM(time_diff)/COUNT(pid)) as avg FROM clk_diff WHERE facility_id=? AND DATE_FORMAT(date,'%Y-%m')=?";
        $fac = $this->db->query($query, [$facility, $date]);
        $data['avg_hours'] = $fac->row()->avg;

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
