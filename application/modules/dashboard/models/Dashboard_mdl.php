<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard_mdl extends CI_Model {

	
	public function __Construct(){

		parent::__Construct();
		$this->department=$this->session->userdata['department_id'];

	}
    public function getData(){
        $facility=$_SESSION['facility'];
        //count health workers
   
        $staff=$this->db->query("Select distinct(ihris_pid) from ihrisdata");
        $data['workers']=$staff->num_rows();
         //count facilities
        $fac=$this->db->query("Select * from facilities");
        $data['facilities']=$fac->num_rows();
        //departments
        $fac=$this->db->query("Select distinct(department) from ihrisdata");
        $data['departments']=$fac->num_rows();
        //jobs
        $fac=$this->db->query("Select * from jobs");
        $data['jobs']=$fac->num_rows();

        //curent _facility_staff

        $fac=$this->db->query("Select distinct(ihris_pid) from ihrisdata where facility_id='$facility'");
        $data['mystaff']=$fac->num_rows();
         //last iHRIS Sync
        $fac=$this->db->query("Select max(last_update) as date  from ihrisdata where facility_id='$facility'");
        $data['ihris_sync']= date('j F, Y H:i:s', strtotime($fac->result()[0]->date));

         //number of biotime devs
        $fac=$this->db->query("Select *  from biotime_devices");
        $data['biometrics']= $fac->num_rows();

        //attendance
        // $date=date('Y-m');
        // $fac=$this->db->query("Select distinct(facility_id) from rosta_rate where date='$date'");
        // $data['roster']=$fac->num_rows();

        // $fac=$this->db->query("Select distinct(facility_id) from attendance_rate where date='$date'");
        // $data['attendance']=$fac->num_rows();

           //get  clock count
        $date=date('Y-m');
        $fac=$this->db->query("SELECT (SUM(time_diff)/COUNT(pid)) as avg FROM clk_diff WHERE facility_id='$facility' and date_format(date,'%Y-%m')='$date'");
        $data['avg_hours']= $fac->result()[0]->avg;




        

        
    return $data;

    }

}
