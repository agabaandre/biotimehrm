<?php
defined('BASEPATH') or exit('No direct script access allowed');
class 	Attendance_model extends CI_Model
{
	public  function __construct()
	{
		parent::__construct();
		$this->facility = $this->session->facility;
		$this->user = $this->session->get_userdata();
		$this->department = $this->session->userdata['department_id'];
		$this->division = $this->session->userdata['division'];
		$this->unit = $this->session->userdata['unit'];
	}
	public function getFacilities($district_id)
	{
		$this->db->select('distinct(facility),facility_id');
		if ($district_id) {
			$this->db->where('district_id', $district_id);
		}
		$query = $this->db->get('ihrisdata');
		return $query->result();
	}
	public function read_employee_csv($importdata)
	{
		ini_set('max_execution_time', 0);
		// Get employee data from the csv file from HRIS and upload it to the HRIS records table.
		$pid = $importdata['ihris_pid'];
		$district = $importdata['district'];
		$check = $this->db->query("Select * from ihrisdata where ihris_pid='$pid'");
		$rows = $this->db->affected_rows();
		if ($rows < 1) {
			if ($district !== "") {
				$this->db->insert('ihrisdata', $importdata);
			}
		} else {
			if ($district !== "") {
				$this->db->where('ihris_pid', $pid);
				$this->db->update("ihrisdata", $importdata);
			}
		}
	}
	public function read_establishment_csv($importdata)
	{
		ini_set('max_execution_time', 0);
		$save = $this->db->insert('establishment', $importdata);
		return $save;
	}
	public function read_attendance_csv($importdata)
	{
		ini_set('max_execution_time', 0);
		$save = $this->db->insert('ihris_att', $importdata);
		return $save;
	}
	//import rota data
	public function save_upload($importdata)
	{
		ini_set('max_execution_time', 0);
		// Get employee data from the csv file from HRIS and upload it to the HRIS records table.
		$this->db->insert('duty_rosta', $importdata);
	}
	public function get_districts()
	{
		$query = $this->db->query("select distinct district_id,district from ihrisdata order by district_id asc");
		$res = $query->result_array();
		return $res;
	}
	public function attendanceSchedules()
	{
		$this->db->where('purpose', 'a');
		$query = $this->db->get('schedules');
		$rows = $query->result_array();
		$letters = array();
		foreach ($rows as $row) {
			$sid = $row['schedule_id'];
			$letter = $row['letter'];
			$letters[$letter] = $sid;
		}
		return $letters;
	}
	public function template_data()
	{
		$facility = $this->facility;
		$query = $this->db->query("select ihrisdata.ihris_pid,concat(ihrisdata.surname,' ',ihrisdata.surname) as names from ihrisdata where (ihrisdata.facility_id='$facility') group by ihrisdata.ihris_pid ");
		$result = $query->result_array();
		return $result;
	}
	public function widget_data()
	{
		$department = $this->department;
		$facility = $this->facility;
		if ($_SESSION['role'] !== 'sadmin') {
			$query1 = $this->db->query("select count(schedules.schedule_id) as schedules from schedules ");
			$result1 = $query1->result();
			$schedules = $result1[0]->schedules;
			if ($department) {
				$query2 = $this->db->query("select count(user.username) as users from user where department_id='$department' ");
			} else {
				$query2 = $this->db->query("select count(user.username) as users from user ");
			}
			$result2 = $query2->result();
			$users = $result2[0]->users;
			if ($department) {
				$query3 = $this->db->query("select count(ihrisdata.ihris_pid) as staff from ihrisdata where facility_id='$facility' and department_id='$department' ");
			} else {
				$query3 = $this->db->query("select count(ihrisdata.ihris_pid) as staff from ihrisdata where facility_id='$facility' ");
			}
			$result3 = $query3->result();
			$staff = $result3[0]->staff;
			$date = date('Y-m');  // this month for gettting schedules
			if ($department) {
				$query4 = $this->db->query("select count(distinct dutyreport.ihris_pid) as duty from dutyreport where facility_id='$facility' and duty_date like '$date%' and department_id='$department'");
			} else {
				$query4 = $this->db->query("select count(distinct dutyreport.ihris_pid) as duty from dutyreport where facility_id='$facility' and duty_date like '$date%'");
			}
			$result4 = $query4->result();
			$scheduled = $result4[0]->duty;
			$result = array("schedules" => $schedules, "users" => $users, "staff" => $staff, "duty" => $scheduled);
		} else {
			$query1 = $this->db->query("select count(distinct facility_id) as facilities from ihrisdata ");
			$result1 = $query1->result();
			$facilities = $result1[0]->facilities;
			if ($department) {
				$query2 = $this->db->query("select count(user.username) as users from user where department_id='$department'");
			} else {
				$query2 = $this->db->query("select count(user.username) as users from user ");
			}
			$result2 = $query2->result();
			$users = $result2[0]->users;
			if ($department) {
				$query3 = $this->db->query("select count(ihrisdata.ihris_pid) as staff from ihrisdata where department_id='$department'");
			} else {
				$query3 = $this->db->query("select count(ihrisdata.ihris_pid) as staff from ihrisdata ");
			}
			$result3 = $query3->result();
			$staff = $result3[0]->staff;
			$date = date('Y-m');  // this month for gettting schedules
			if ($department) {
				$query4 = $this->db->query("select count(distinct dutyreport.facility_id) as duty from dutyreport where duty_date like '$date%' and department_id='$department'");
			} else {
				$query4 = $this->db->query("select count(distinct dutyreport.facility_id) as duty from dutyreport where duty_date like '$date%'");
			}
			$result4 = $query4->result();
			$scheduled = $result4[0]->duty;
			$result = array("facilities" => $facilities, "users" => $users, "staff" => $staff, "duty" => $scheduled);
		}
		return $result;
	}
	function get_vars()
	{
		$this->db->from("variables");
		$this->db->order_by("variable", "desc");
		$this->db->group_by('rowid');
		$query = $this->db->get();
		return $query->result_array();
	}
	public function machine_facilities()
	{
		$query = $this->db->query("select distinct(facility_id),facility from ihrisdata ");
		$results = $query->result();
		$facilities = array();
		foreach ($results as $facility) {
			$facilities[$facility->facility] = $facility->facility_id;
		}
		return $facilities;
	}
	public function read_machine_csv($importdata)
	{
		ini_set('max_execution_time', 0);
		// Get employee data from the csv file from HRIS and upload it to the HRIS records table.
		$import = $this->db->insert('clk_log', $importdata);
		if ($import) {
			return "ok";
		} else {
			return "failed";
		}
	}
	//get total rows to use in pagination of timelogs 
	public function count_timelogs()
	{
		return  $this->db->count_all('clk_log');
	}
	public function fetchTimeLogs($limit, $start, $search_data = FALSE)
	{
		$facility = $this->facility; //current facility
		if ($search_data) {
			$date_from = $search_data['date_from'];
			$date_to = $search_data['date_to'];
			$name = $search_data['name'];
			if ($name) {
				$ids = $this->getIds($name);
				if (count($ids) > 0) {
					$person = "and  clk_log.ihris_pid in ($ids)";
				} else {
					$person = "";
				}
			}
			$filter = " and date between $date_from AND $date_to";
		} else {
		}
		$query = $this->db->query("SELECT * from clk_log, ihrisdata where ihrisdata.ihris_pid=clk_log.ihris_pid  and clk_log.facility_id='$facility' $filter $person limit $limit,$start");
		return $query->result();
	}
	public function getIds($name)
	{
		$this->db->select('ihris_pid');
		$this->db->where("firstname like '%$name%'");
		$this->db->or_where("othername like '%$name' ");
		$query = $this->db->get('ihrisdata');
		$result = $query->result();
		$ids = array();
		foreach ($result as $row) {
			array_push($ids, $row->ihris_pid);
		}
		return $ids;
	}
	public function getMachineCsvData($date_from = FALSE, $date_to = FALSE)
	{
		$facility = $this->facility; //current facility
		$department = $this->department;
		$this->db->select("concat(ihrisdata.firstname,' ',ihrisdata.surname) as names,ihrisdata.facility,clk_log.time_in,clk_log.time_out,TIMEDIFF(clk_log.time_out,clk_log.time_in) as hours,clk_log.date");
		if ($date_from) {
			$this->db->where("date >= '$date_from' AND date <= '$date_to'");
		}
		$this->db->join("ihrisdata", "ihrisdata.ihris_pid=clk_log.ihris_pid");
		$query = $this->db->get("clk_log");
		$rows = $query->result();
		return $rows;
	}

	public function  attendance_summary($valid_range, $filters, $start = NULL, $limit = NULL,  $district = FALSE,$facility = FALSE, $employee = NULL, $department = FALSE, $endpoint=FALSE)
	{
		$facility_id = $_SESSION['facility'];

		if (!empty($facility_id)&&($endpoint!='api')) {
			$facility = "and facility_id='$facility_id'";
		} else {
			$facility = "";
		}
		if (!empty($facility) && ($endpoint== 'api')) {
			$facility = "and facility_id='$facility'";
		} else {
			$facility = "";
		}
		if (!empty($district) && ($endpoint == 'api')) {
			$district = "and district='$district'";
		} else {
			$district = "";
		}
		if (!empty($employee)&&($endpoint!='api')) {
			$search = "and ihris_pid='$employee'";
		} else {
			$search = "";
		}

		if (!empty($department)) {
			$dep = "and department_id='$department'";
		} else {
			$dep = "";
		}
		if (!empty($start)) {
			$limits = " LIMIT $limit,$start";
		} else {
			$limits = " ";
		}
		$query = $this->db->query("SELECT * from person_att_final  WHERE duty_date='$valid_range' $facility $search $dep  $limits");
		$data = $query->result_array();
		return $data;
	} //summary

	public function countAttendanceSummary($valid_range, $filters, $start = NULL, $limit = NULL, $employee = NULL, $department = NULL)
	{
		$facility = $_SESSION['facility'];
		if (!empty($employee)) {
			$search = "and ihris_pid='$employee'";
		} else {
			$search = "";
		}
		if (!empty($department)) {
			$dep = "and department_id='$department'";
		} else {
			$dep = "";
		}
		if (!empty($start)) {
			$limits = " LIMIT $limit,$start";
		} else {
			$limits = " ";
		}
		$query = $this->db->query("SELECT * from person_att_final WHERE facility_id='$facility'  and duty_date='$valid_range' $search $dep  $limits");
		$data = $query->num_rows();
		return $data;
	} //summary
	public function attrosta($valid_range, $person)
	{
		$day = $this->db->query("select count(ihris_pid) as days from duty_rosta where schedule_id=14 and DATE_FORMAT(duty_rosta.duty_date, '%Y-%m') ='$valid_range' and ihris_pid='$person'")->result();
		$evening = $this->db->query("select count(ihris_pid) as days from duty_rosta where schedule_id=15 and DATE_FORMAT(duty_rosta.duty_date, '%Y-%m') ='$valid_range' and ihris_pid='$person'")->result();
		$night = $this->db->query("select count(ihris_pid) as days from duty_rosta where schedule_id=16 and DATE_FORMAT(duty_rosta.duty_date, '%Y-%m') ='$valid_range' and ihris_pid='$person'")->result();
		$data['Day'] = $day;
		$data['Evening'] = $evening;
		$data['Night'] = $night;
		return $data;
	}
	public function fetch_summary($valid_range)
	{
		$facility = $this->session->userdata['facility'];
		$department = $this->input->post('department');
		if (empty($valid_range)) {
			$valid_range = date('Y-m');
		}
		$s = $this->db->query("select letter,schedule_id from schedules where letter!='H' and purpose='r'");
		$schs = $s->result_array();
		if ($department) {
			$all = $this->db->query("select distinct ihris_pid,concat(ihrisdata.surname,' ',ihrisdata.firstname) as fullname from ihrisdata where facility_id='$facility' and department='$department'");
		} else {
			$all = $this->db->query("select distinct ihris_pid,concat(ihrisdata.surname,' ',ihrisdata.firstname) as fullname from ihrisdata where facility_id='$facility'");
		}
		$rows = $all->result_array();
		$data = array();
		$mydata = array();
		$i = 0;
		foreach ($rows as $row) {
			$id = $row['ihris_pid'];
			$mydata["person"] = $row['fullname'];
			foreach ($schs as $sc) {
				$i++;
				$s_id = $sc['schedule_id'];
				$query = $this->db->query("select ihrisdata.ihris_pid,dutyreport.duty_date, schedules.letter,dutyreport.entry_id,schedules.schedule,ihrisdata.job,ihrisdata.facility,concat(ihrisdata.surname,' ',ihrisdata.firstname) as fullname,count(dutyreport.schedule_id) as days from dutyreport,schedules,ihrisdata WHERE( dutyreport.duty_date like '$valid_range-%' and dutyreport.schedule_id=schedules.schedule_id and dutyreport.ihris_pid=ihrisdata.ihris_pid and dutyreport.facility_id='$facility' and dutyreport.ihris_pid='$id' and schedules.schedule_id='$s_id' and dutyreport.duty_date like '$valid_range%')");
				$rows = $this->db->affected_rows();
				$rowdata = $query->result_array();
				//$mydata=array('person'.$i=>$rowdata[0]['fullname'],'shift'=>$rowdata[0]['schedule'],'days'=>$rowdata[0]['days']);
				$mydata[$rowdata[0]['letter']] = $rowdata[0]['days'];
				$mydata['facility'] = $rowdata[0]['facility'];
			}
			array_push($data, $mydata);
		}
		return $data;
	} //
}
