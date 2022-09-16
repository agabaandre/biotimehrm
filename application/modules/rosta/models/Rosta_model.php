<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Rosta_model extends CI_Model
{
	public function __Construct()
	{
		parent::__Construct();
		$this->user = $this->session->get_userdata();
		$this->department = $this->session->userdata['department_id'];
		$this->division = $this->session->userdata['division'];
		$this->unit = $this->session->userdata['unit'];
	}
	/*Create new events */
	public function addEvent()
	{
		$start = $_POST['start']; // or your date as well
		$department = $this->department;
		$newstartdate = $start; //add one to prev date 2017-11-02
		$newenddate = date('Y-m-d', strtotime($start . "+1 days")); //add one to prev date
		$entry = $newstartdate . $_POST['hpid'];
		$facility = $this->session->userdata['facility'];
		$sql = "INSERT INTO duty_rosta (entry_id,facility_id,department_id,ihris_pid,schedule_id,color,duty_date,duty_rosta.end) VALUES (?,?,?,?,?,?,?,?)";
		$done = $this->db->query($sql, array($entry, $facility, $department, $_POST['hpid'], $_POST['duty'], $_POST['color'], $newstartdate, $newenddate));
		if ($done) {
			$rows = $this->db->affected_rows();
		} else if (!$done) {
			$rows = 0;
		}
		return $rows;
	} // end add event
	/*Update  event */
	public function updateEvent()
	{
		$sql = "UPDATE duty_rosta SET ihris_pid = ?, schedule_id = ?, color = ? WHERE entry_id = ?";
		$this->db->query($sql, array($_POST['hpid'], $_POST['duty'], $_POST['color'], $_POST['id']));
		return ($this->db->affected_rows() != 1) ? false : true;
	}
	/*Delete event */
	public function deleteEvent()
	{
		$sql = "DELETE FROM duty_rosta WHERE entry_id = ?";
		$this->db->query($sql, array($_GET['id']));
		return ($this->db->affected_rows() != 1) ? false : true;
	}
	/*Update  event */
	public function dragUpdateEvent()
	{
		//$date=date('Y-m-d h:i:s',strtotime($_POST['date']));
		$start = strtotime($_POST['start']); // or your date as well
		$end = strtotime($_POST['end']);
		$datediff = $end - $start;
		$days = floor($datediff / (60 * 60 * 24));
		if ($days > 1) {
			for ($i = 0; $i < $days; $i++) {
				$oneday = "+" . $i . " day"; //1 day
				$twodays = "+" . ($i + 1) . " day"; //1 other day for end date
				$sdate = $start;
				$newstartdate = date('Y-m-d', strtotime($oneday, $sdate)); //add one to prev date 2017-11-02
				$newenddate = date('Y-m-d', strtotime($twodays, $sdate)); //add one to prev date
				$sql = "UPDATE duty_rosta SET  duty_date = ?, end = ?   WHERE entry_id= ?";
				$this->db->query($sql, array($newstartdate, $newenddate, $_POST['id']));
			} //for
		} //if
		else {
			$sql = "UPDATE duty_rosta SET  duty_date = ?, end = ?   WHERE entry_id= ?";
			$this->db->query($sql, array($_POST['start'], $_POST['end'], $_POST['id']));
		}
		return ($this->db->affected_rows() != 1) ? false : true;
	}
	//get attendance report and form 
	public function fetch_report($valid_range, $start = NULL, $limit = NULL, $employee = NULL, $filters)
	{
		$facility = $this->session->userdata['facility'];
		$employee = $this->input->post('empid');
		if (!empty($employee)) {
			$search = "and ihrisdata.ihris_pid='$employee'";
		} else {
			$search = "";
		}
		if (!empty($employee)) {
			$psearch = $employee;
		} else {
			$psearch = "";
		}
		if (!empty($start)) {
			$limits = " LIMIT $limit,$start";
		} else {
			$limits = " ";
		}
		$all = $this->db->query("select distinct ihrisdata.ihris_pid,CONCAT(
				COALESCE(surname,'','')
				,' ',
				COALESCE(firstname,'','')
				,' ',
				COALESCE(othername,'','')
			) AS fullname,ihrisdata.job from ihrisdata where $filters $search order by surname ASC $limits");
		$data = $all->result_array();
		return $data;
	}
	public function matches($date)
	{
		$facility = $this->session->userdata['facility'];
		$query = $this->db->query("Select duty_rosta.ihris_pid,duty_rosta.schedule_id,duty_rosta.duty_date,schedules.letter from duty_rosta,schedules where schedules.schedule_id=duty_rosta.schedule_id and duty_rosta.facility_id='$facility' and DATE_FORMAT(duty_rosta.duty_date, '%Y-%m') ='$date'");
		$results = $query->result_array();
		$ro = $query->num_rows();
		$matches = array();
		for ($i = 0; $i < $ro; $i++) {
			$matches[$results[$i]['duty_date'] . $results[$i]['ihris_pid']] = $results[$i]['letter'];
		}
		return $matches;
	}
	public function tab_matches()
	{
		$query = $this->db->query("Select schedule_id,letter from schedules where purpose='r'");
		$results = $query->result_array();
		$ro = $query->num_rows();
		$schedules = array();
		for ($i = 0; $i < $ro; $i++) {
			$schedules["'" . $results[$i]['letter'] . "'"] = $results[$i]['schedule_id'];
		}
		return $schedules;
	}
	public function countActuals($valid_range, $start = NULL, $limit = NULL, $employee = NULL, $filters)
	{
		$facility = $this->session->userdata['facility'];
		$employee = $this->input->post('empid');
		if (!empty($employee)) {
			$search = "and ihrisdata.ihris_pid='$employee'";
		} else {
			$search = "";
		}
		if (!empty($employee)) {
			$psearch = $employee;
		} else {
			$psearch = "";
		}
		$all = $this->db->query("select distinct ihrisdata.ihris_pid,CONCAT(
				COALESCE(surname,'','')
				,' ',
				COALESCE(firstname,'','')
				,' ',
				COALESCE(othername,'','')
			) AS fullname,ihrisdata.job from ihrisdata where $filters $search ");
		$data = $all->num_rows();
		return $data;
	}
	public function count_tabs()
	{
		$facility = $this->session->userdata['facility'];
		$all = $this->db->query("select distinct(ihris_pid) from ihrisdata where ihrisdata.facility_id='$facility' ");
		$rows = $all->num_rows();
		return $rows;
	}
	public function fetch_tabs($valid_range, $start, $limit, $employee = FALSE, $filters)
	{
		$facility = $this->session->userdata['facility'];
		$employee = $this->input->post('empid');
		if (!empty($employee)) {
			$search = "and ihrisdata.ihris_pid='$employee'";
		} else {
			$search = "";
		}
		if (!empty($employee)) {
			$psearch = $employee;
		} else {
			$psearch = "";
		}
		if (!empty($start)) {
			$limits = " LIMIT $limit,$start";
		} else {
			$limits = " ";
		}
		$all = $this->db->query("select distinct ihrisdata.ihris_pid,CONCAT(
				COALESCE(surname,'','')
				,' ',
				COALESCE(firstname,'','')
				,' ',
				COALESCE(othername,'','')
			) AS fullname,ihrisdata.job from ihrisdata where $filters $search order by surname ASC $limits");
		$data = $all->result_array();
		return $data;
	}
	public function fetchleave_tabs($date_range, $start, $limit, $employee = NULL)
	{
		$department = $this->department;
		$facility = $this->session->userdata['facility'];
		$division = $this->division;
		$unit = $this->unit;
		if ((!empty($department))) {
			$dep_filter = "and ihrisdata.department_id='$department'";
		} else {
			$dep_filter = "";
		}
		if ((!empty($department))) {
			$depr_filter = "and duty_rosta.department_id='$department'";
		} else {
			$depr_filter = "";
		}
		if ((!empty($division))) {
			$div_filter = "and ihrisdata.division='$division'";
		} else {
			$div_filter = "";
		}
		if ((!empty($unit))) {
			$funit = "and ihrisdata.unit='$unit'";
		} else {
			$funit = "";
		}
		$month = $this->input->post('month');
		$year = $this->input->post('year');
		$employee = $this->input->post('empid');
		if (!empty($month)) {
			$date_range = $year . '-' . $month;
		} else {
			$date_range = date('Y-m');
		}
		if ($department) {
			$this->db->where('department_id', $department);
		}
		if (!empty($this->input->post('empid'))) {
			//$this->db->where('ihris_pid',$employee);
			$search = "and ihrisdata.ihris_pid='" . $employee . "'";
		} else {
			$search = "";
		}
		//$this->db->where('facility_id',$facility);
		$this->db->like('duty_date', $date_range, 'after');
		$qry = $this->db->get('leave_rota');
		$rowno = $qry->num_rows();
		// ihrisdata
		$query = $this->db->get('ihrisdata');
		$data = $query->num_rows();
		if ($rowno < 1000) {
			if ($department) {
				$query = $this->db->query("select distinct ihrisdata.ihris_pid,concat(ihrisdata.surname,' ',ihrisdata.firstname) as fullname,ihrisdata.job from schedules,ihrisdata where ihrisdata.facility_id='$facility' $dep_filter $div_filter $funit $search LIMIT $limit,$start");
			} else {
				$query = $this->db->query("select distinct ihrisdata.ihris_pid,concat(ihrisdata.surname,' ',ihrisdata.firstname) as fullname,ihrisdata.job from schedules,ihrisdata where ihrisdata.facility_id='$facility' $search LIMIT $limit,$start");
			}
			$data = $query->result_array();
		} // if There are no $schedules yet
		else {  // if there are schedules
			if ($department) {
				$all = $this->db->query("select distinct ihrisdata.ihris_pid from ihrisdata,leavereport where ihrisdata.facility_id='$facility' $dep_filter $div_filter $funit $search LIMIT $limit,$start"); //apply limits
			} else {
				$all = $this->db->query("select distinct ihrisdata.ihris_pid from ihrisdata,leavereport where ihrisdata.facility_id='$facility' $search LIMIT $limit,$start");
			}
			$rows = $all->result_array();
			$data = array();
			foreach ($rows as $row) {
				$id = $row['ihris_pid'];
				$query = $this->db->query("select ihrisdata.ihris_pid,leavereport.duty_date, schedules.letter,leavereport.entry_id,schedules.schedule,ihrisdata.job,ihrisdata.facility,concat(ihrisdata.surname,' ',ihrisdata.firstname) as fullname,max(leavereport.day1) as day1,max(leavereport.day2)as day2,max(leavereport.day3)as day3,max(leavereport.day4)as day4,max(leavereport.day5)as day5,max(leavereport.day6)as day6,max(leavereport.day7)as day7,max(leavereport.day8)as day8,max(leavereport.day9)as day9,max(leavereport.day10)as day10,
				max(leavereport.day11)as day11,max(leavereport.day12)as day12,max(leavereport.day13)as day13,max(leavereport.day14)as day14,max(leavereport.day15)as day15,max(leavereport.day16)as day16,max(leavereport.day17)as day17,max(leavereport.day18)as day18,max(leavereport.day19)as day19,
				max(leavereport.day20)as day20,max(leavereport.day21)as day21,max(leavereport.day22)as day22,max(leavereport.day23)as day23,max(leavereport.day24)as day24,max(leavereport.day25)as day25,max(leavereport.day26)as day26,max(leavereport.day27)as day27,max(leavereport.day28)as day28,max(leavereport.day29)as day29,max(leavereport.day30)as day30,max(leavereport.day31)as day31 from leavereport,schedules,ihrisdata 
				WHERE( leavereport.duty_date like '$date_range%' and leavereport.schedule_id in ( select schedules.schedule_id from schedules) and ihrisdata.ihris_pid='$id'
				)");
				$rows = $this->db->affected_rows();
				$rowdata = $query->result_array();
				array_push($data, $rowdata[0]);
			}
		}
		return $data;
	}
	//dashboard data checks
	public function checks()
	{
		$facility = @$this->session->userdata['facility'];
		$date = date('Y-m');
		if ($facility) {
			$this->db->where('facility_id', $facility);
			$this->db->like('duty_date', $date, 'end');
		}
		$rowno = $this->db->count_all_results('duty_rosta');
		if ($facility) {
			$this->db->where('facility_id', $facility);
		}
		$staffs = $this->db->count_all_results('ihrisdata');
		$data = array('workedon' => $rowno, 'staffs' => $staffs);
		return $data;
	}
	public function leavechecks()
	{
		$facility = @$this->session->userdata['facility'];
		$date = date('Y-m');
		if ($facility) {
			$this->db->where('facility_id', $facility);
			$this->db->like('duty_date', $date, 'end');
		}
		$rowno = $this->db->count_all_results('leave_rota');
		if ($facility) {
			$this->db->where('facility_id', $facility);
		}
		$staffs = $this->db->count_all_results('ihrisdata');
		$data = array('workedon' => $rowno, 'staffs' => $staffs);
		return $data;
	}
	public function fetch_summary($valid_range, $filters, $start = NULL, $limit = NULL, $employee = NULL)
	{
		$facility = $_SESSION['facility'];
		if (!empty($employee)) {
			$search = "and ihrisdata.ihris_pid='$employee";
		} else {
			$search = "";
		}
		if (!empty($start)) {
			$limits = " LIMIT $limit,$start";
		} else {
			$limits = " ";
		}
		$query = $this->db->query("SELECT * from person_dut_final WHERE facility_id='$facility'  and duty_date='$valid_range' $search  $limits");
		$data = $query->result_array();
		return $data;
	} //summary
	public function full_summary($limit, $start, $valid_range, $district, $facility)
	{
		if (empty($valid_range)) {
			$valid_range = date('Y-m');
		}
		$department = $this->department;
		$division = @$this->division;
		$unit = @$this->unit;
		$s = $this->db->query("select letter,schedule_id from schedules where letter!='H' and purpose='r'");
		$schs = $s->result_array();
		if (!empty($department)) {
			if ($district && !$facility) {
				$all = $this->db->query("select distinct ihris_pid,concat(ihrisdata.surname,' ',ihrisdata.firstname) as fullname from ihrisdata where ihrisdata.district_id='$district'   and   ihrisdata.ihris_pid IN(select duty_rosta.ihris_pid from duty_rosta where duty_date like '$valid_range%' and department_id='$department')  LIMIT $start,$limit ");
			} else if ($facility) {
				$all = $this->db->query("select distinct ihris_pid,concat(ihrisdata.surname,' ',ihrisdata.firstname) as fullname from ihrisdata where  ihrisdata.district_id='$district' and  ihrisdata.facility_id='$facility'   and   ihrisdata.ihris_pid IN(select duty_rosta.ihris_pid from duty_rosta where duty_date like '$valid_range%' and department_id='$department')  LIMIT $start,$limit");
			} else {
				$all = $this->db->query("select distinct ihris_pid,concat(ihrisdata.surname,' ',ihrisdata.firstname) as fullname from ihrisdata where  ihrisdata.ihris_pid IN(select duty_rosta.ihris_pid from duty_rosta where duty_date like '$valid_range%' and department_id='$department')  LIMIT $start,$limit");
			}
		} //depart is define
		else { //depart undefined
			if ($district && !$facility) {
				$all = $this->db->query("select distinct ihris_pid,concat(ihrisdata.surname,' ',ihrisdata.firstname) as fullname from ihrisdata where ihrisdata.district_id='$district'   and   ihrisdata.ihris_pid IN(select duty_rosta.ihris_pid from duty_rosta where duty_date like '$valid_range%')  LIMIT $start,$limit ");
			} else if ($facility) {
				$all = $this->db->query("select distinct ihris_pid,concat(ihrisdata.surname,' ',ihrisdata.firstname) as fullname from ihrisdata where  ihrisdata.district_id='$district' and  ihrisdata.facility_id='$facility'   and   ihrisdata.ihris_pid IN(select duty_rosta.ihris_pid from duty_rosta where duty_date like '$valid_range%')  LIMIT $start,$limit");
			} else {
				$all = $this->db->query("select distinct ihris_pid,concat(ihrisdata.surname,' ',ihrisdata.firstname) as fullname from ihrisdata where  ihrisdata.ihris_pid IN(select duty_rosta.ihris_pid from duty_rosta where duty_date like '$valid_range%')  LIMIT $start,$limit");
			}
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
				$query = $this->db->query("select ihrisdata.ihris_pid,duty_rosta.duty_date, schedules.letter,duty_rosta.entry_id,schedules.schedule,ihrisdata.job,ihrisdata.facility,concat(ihrisdata.surname,' ',ihrisdata.firstname) as fullname,count(duty_rosta.schedule_id) as days from duty_rosta,schedules,ihrisdata WHERE( duty_rosta.duty_date like '$valid_range-%' and duty_rosta.schedule_id=schedules.schedule_id and duty_rosta.ihris_pid=ihrisdata.ihris_pid  and duty_rosta.ihris_pid='$id' and schedules.schedule_id='$s_id' and duty_rosta.duty_date like '$valid_range%')");
				$rows = $this->db->affected_rows();
				$rowdata = $query->result_array();
				//$mydata=array('person'.$i=>$rowdata[0]['fullname'],'shift'=>$rowdata[0]['schedule'],'days'=>$rowdata[0]['days']);
				$mydata[$rowdata[0]['letter']] = $rowdata[0]['days'];
				$mydata['facility'] = $rowdata[0]['facility'];
			}
			array_push($data, $mydata);
		}
		return $data;
	} //summary
	public function nonworkables()
	{
		$query = $this->db->query("select letter from schedules where letter NOT IN('D','E','N') and schedules.purpose='r' "); //get non working days; leave days
		$results = $query->result_array();
		$ro = $query->num_rows();
		$leaves = array();
		foreach ($results as $leave) {
			$leaves[] = $leave['letter'];
		}
		return $leaves;
	}
	public function workeddays()
	{
		$facility = $this->session->userdata['facility'];
		$query = $this->db->query("select day,ihris_pid from presence where facility_id='$facility'");
		$results = $query->result_array();
		$ro = $query->num_rows();
		$worked = array();
		foreach ($results as $work) {
			$comb = $work['day'] . $work['ihris_pid'];
			$worked[] = $comb;
		}
		return $worked;
	}
	public function saveTracker($data)
	{
		$facility = $this->session->userdata['facility'];
		$entry_id = $data['day'] . $data['ihris_pid'];
		$rowdata = array('day' => $data['day'], 'ihris_pid' => $data['ihris_pid'], 'entry_id' => $entry_id, 'facility_id' => $facility);
		$saved = $this->db->insert('presence', $rowdata);
		if ($saved) {
			return "Tracker Saved";
		} else {
			return "Failed";
		}
	}
	public function saveActual($data)
	{
		$facility = $this->session->userdata['facility'];
		//	$entry_id =$data['day'].$data['ihris_pid'];
		$saved = $this->db->insert('actuals', $data);
		if ($saved) {
			return "Actual Saved";
		} else {
			return "Failed";
		}
	}
	public function updateActual($data)
	{
		$facility = $this->session->userdata['facility'];
		$entry_id = $data['entry_id'];
		$this->db->where('entry_id', $entry_id);
		$saved = $this->db->update('actuals', $data);
		if ($saved) {
			return "Update Finished";
		} else {
			return "Failed";
		}
	}
	public function getActuals($date)
	{
		$facility = $_SESSION['facility'];
		$query = $this->db->query("select actuals.*, schedules.letter as actual from actuals join schedules on actuals.schedule_id=schedules.schedule_id and schedules.purpose='a' and actuals.facility_id='$facility' and DATE_FORMAT(actuals.date, '%Y-%m') = '$date'");
		$result = $query->result_array();
		return $result;
	}
	public function count_summary($valid_range, $district, $facility)
	{
		$department = $this->department;
		if (!empty($department)) {
			if ($district && !$facility) {
				$all = $this->db->query("select count(ihrisdata.ihris_pid) as rows from ihrisdata where ihrisdata.district_id='$district'   and   ihrisdata.ihris_pid IN(select duty_rosta.ihris_pid from duty_rosta where duty_date like '$valid_range%')");
			} else if ($facility) {
				$all = $this->db->query("select count(ihrisdata.ihris_pid) as rows from ihrisdata where  ihrisdata.district_id='$district' and  ihrisdata.facility_id='$facility'   and   ihrisdata.ihris_pid IN(select duty_rosta.ihris_pid from duty_rosta where duty_date like '$valid_range%')");
			} else {
				$all = $this->db->query("select count(ihrisdata.ihris_pid) as rows from ihrisdata where  ihrisdata.ihris_pid IN(select duty_rosta.ihris_pid from duty_rosta where duty_date like '$valid_range%')");
			}
		} else {
			if ($district && !$facility) {
				$all = $this->db->query("select count(ihrisdata.ihris_pid) as rows from ihrisdata where ihrisdata.district_id='$district'   and   ihrisdata.ihris_pid IN(select duty_rosta.ihris_pid from duty_rosta where duty_date like '$valid_range%')");
			} else if ($facility) {
				$all = $this->db->query("select count(ihrisdata.ihris_pid) as rows from ihrisdata where  ihrisdata.district_id='$district' and  ihrisdata.facility_id='$facility'   and   ihrisdata.ihris_pid IN(select duty_rosta.ihris_pid from duty_rosta where duty_date like '$valid_range%')");
			} else {
				$all = $this->db->query("select count(ihrisdata.ihris_pid) as rows from ihrisdata where  ihrisdata.ihris_pid IN(select duty_rosta.ihris_pid from duty_rosta where duty_date like '$valid_range%')");
			}
		}
		$rows = $all->row();
		return $rows->rows;
	}
	public function countrosta_summary($date, $filters)
	{
		$facility = $_SESSION['facility'];
		$query = $this->db->query("SELECT * from person_dut_final WHERE facility_id='$facility'  and duty_date='$date'");
		return $query->num_rows();
	}
	//import rota data
	public function upload_rota($importdata)
	{
		ini_set('max_execution_time', 0);
		// Get employee data from the csv file from HRIS and upload it to the HRIS records table.
		$this->db->replace('duty_rosta', $importdata);
	}
	public function full_att_all()
	{
		$department = $this->department;
		$s = $this->db->query("select letter,schedule_id from schedules where letter!='H' and purpose='a'");
		$schs = $s->result_array();
		if (!empty($department)) {
			$all = $this->db->query("select distinct ihris_pid,concat(ihrisdata.surname,' ',ihrisdata.firstname) as fullname from ihrisdata where  ihrisdata.ihris_pid IN(select duty_rosta.ihris_pid from duty_rosta and department_id='$department')");
		} else {
			$all = $this->db->query("select distinct ihris_pid,concat(ihrisdata.surname,' ',ihrisdata.firstname) as fullname from ihrisdata where  ihrisdata.ihris_pid IN(select duty_rosta.ihris_pid from duty_rosta)");
		}
		$rows = $all->result_array();
		$data = array();
		$mydata = array();
		$i = 0;
		foreach ($rows as $row) {
			$id = $row['ihris_pid'];
			$mydata["person"] = $row['fullname'];
			$mydata["person_id"] = $id;
			foreach ($schs as $sc) {
				$i++;
				$s_id = $sc['schedule_id'];
				$qry = $this->db->query("select schedules.letter,count(actuals.schedule_id) as days from actuals,schedules where actuals.ihris_pid='$id' and actuals.schedule_id='$s_id' and schedules.schedule_id=actuals.schedule_id");
				$rowdata = $qry->result_array();
				if ($rowdata[0]['letter']) {
					$mydata[$rowdata[0]['letter']] = $rowdata[0]['days'];
				} else {
					$mydata[$sc['letter']] = '0';
				}
				//$mydata['facility']=$rows[0]['facility'];
			}
			array_push($data, $mydata);
		}
		return $data;
	} //summary
	public function template_data()
	{
		$facility = $this->session->userdata['facility'];
		$query = $this->db->query("select ihrisdata.ihris_pid,concat(ihrisdata.surname,' ',ihrisdata.surname) as names from ihrisdata where (ihrisdata.facility_id='$facility') group by ihrisdata.ihris_pid ");
		$result = $query->result_array();
		return $result;
	}
	public function find_schedule($id)
	{
		return $this->db->select("letter")
			->where('schedule_id', $id)
			->get('schedules')->row();
	}
	public function get_roster_schedules($person_id, $date)
	{
		$sql    = "SELECT schedule_id FROM duty_rosta WHERE ihris_pid = '$person_id' AND duty_date='$date'";
		$query  = $this->db->query($sql);
		$row 	= $query->row();
		return ($row) ? $this->find_schedule($row->schedule_id)->letter : "";
	}
	public function get_attendance_schedules($person_id, $date)
	{
		$sql    = "SELECT schedule_id FROM actuals WHERE ihris_pid = '$person_id' AND date='$date'";
		$query  = $this->db->query($sql);
		$row 	= $query->row();
		return ($row) ? $this->find_schedule($row->schedule_id)->letter : "";
	}
}
