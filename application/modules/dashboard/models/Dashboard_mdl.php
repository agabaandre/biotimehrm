<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard_mdl extends CI_Model
{


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

        $fac = $this->db->query("Select max(last_update) as date  from ihrisdata where facility_id='$facility'");
        $data['ihris_sync'] = date('j F, Y H:i:s', strtotime($fac->result()[0]->date));
        //Att gen
        $fac = $this->db->query("Select max(last_gen) as date  from person_att_final");
        $data['attendance'] = date('j F, Y H:i:s', strtotime($fac->result()[0]->date));
        //Roster gen
        $fac = $this->db->query("Select max(last_gen) as date  from person_dut_final");
        $data['roster'] = date('j F, Y H:i:s', strtotime($fac->result()[0]->date));
        //Biotime att sync
        $fac = $this->db->query("Select max(last_sync) as date  from biotime_data_history");
        $data['biotime_last'] = date('j F, Y H:i:s', strtotime($fac->result()[0]->date));

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

        $fac = $this->db->query("Select max(last_update) as date  from ihrisdata where facility_id='$facility'");
        $data['ihris_sync'] = date('j F, Y H:i:s', strtotime($fac->result()[0]->date));
        //Att gen
        $fac = $this->db->query("Select max(last_gen) as date  from person_att_final");
        $data['attendance'] = date('j F, Y H:i:s', strtotime($fac->result()[0]->date));
        //Roster gen
        $fac = $this->db->query("Select max(last_gen) as date  from person_dut_final");
        $data['roster'] = date('j F, Y H:i:s', strtotime($fac->result()[0]->date));
        //Biotime att sync
        $fac = $this->db->query("Select max(last_sync) as date  from biotime_data_history");
        $data['biotime_last'] = date('j F, Y H:i:s', strtotime($fac->result()[0]->date));

        // Combine queries for similar data retrieval
        $queries = [
            "SELECT max(last_update) as date FROM ihrisdata WHERE facility_id='$facility'",
            "SELECT max(last_gen) as date FROM person_att_final",
            "SELECT max(last_gen) as date FROM person_dut_final",
            "SELECT max(last_sync) as date FROM biotime_data"
        ];
        foreach ($queries as $query) {
            $fac = $this->db->query($query);
            $data[] = date('j F, Y H:i:s', strtotime($fac->result()[0]->date));
        }

        // Combine similar queries for present, offduty, leave, and request
        $query = "SELECT schedule_id, COUNT(*) as count FROM actuals WHERE facility_id=? AND date=? GROUP BY schedule_id";
        $fac = $this->db->query($query, [$facility, $today]);
        $results = $fac->result();
        $present = $offduty = $leave = $request = 0;
        foreach ($results as $row) {
            switch ($row->schedule_id) {
                case 22:
                    $present = $row->count;
                    break;
                case 24:
                    $offduty = $row->count;
                    break;
                case 25:
                    $leave = $row->count;
                    break;
                case 23:
                    $request = $row->count;
                    break;
            }
        }
        $data['present'] = $present;
        $data['offduty'] = $offduty;
        $data['leave'] = $leave;
        $data['request'] = $request;

        // Count people requesting for official requests
        $fac = $this->db->query("SELECT COUNT(*) as count FROM requests WHERE date LIKE ?", [$today . '%']);
        $data['requesting'] = $fac->row()->count;

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

   }
