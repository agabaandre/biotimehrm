<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Schedules extends MX_Controller
{


	public function __Construct()
	{

		parent::__Construct();

		$this->load->model('schedules_mdl', 'scheduleMdl');
	}



	public function all_schedules()
	{

		$data['view'] = 'publicHolidays';
		$data['title'] = 'Public Holiday Schedules ' . date('Y');
		$data['uptitle'] = 'Public Holiday Schedules ' . date('Y');
		$data['module'] = "schedules";
		echo Modules::run("templates/main", $data);
	}

	public function attendance_schedules()
	{
		$data['title'] = 'Approved Attendance Schedules ';
		$data['uptitle'] = 'Approved Attendance Schedules ';
		$data['module'] = "schedules";
		$data['view'] = 'AttendanceSchedule';
		$data['title'] = "Attendance Schedule";
		echo Modules::run("templates/main", $data);
	}


	public function duty_rosta_schedules()
	{
		$data['title'] = 'Approved Duty Roster Schedules ';
		$data['uptitle'] = 'Approved Duty Roster Schedules ';
		$data['view'] = 'DutyRosterSchedules';
		$data['module'] = "schedules";
		echo Modules::run("templates/main", $data);
	}


	public function Public_Holidays()
	{
		$data['title'] = 'Approved Public Holiday Schedules ' . date('Y');
		$data['uptitle'] = 'Approved Public Holiday Schedules ' . date('Y');
		$data['view'] = 'publicHolidays';
		$data['module'] = "schedules";
		echo Modules::run("templates/main", $data);
	}


	public function get_publicHoliday()
	{

		$holiday = $this->scheduleMdl->get_publicHoliday();
		return $holiday;
	}

	public function save_publicHoliday()
	{
		$postdata = $this->input->post();
		$result = $this->scheduleMdl->save_publicHoliday($postdata);

		//echo $result;
		redirect('schedules/Public_Holidays');
	}

	public function edit_holiday()
	{

		$post_data = $this->input->post();
		$result = $this->scheduleMdl->update_publicHoliday($post_data);
		$this->session->set_flashdata('msg', 'Updated Succesfully');


		//echo $result;
		redirect('schedules/Public_Holidays');
	}

	public function delete_publicHoliday($id)
	{
		$result = $this->scheduleMdl->delete_publicHoliday($id);

		//echo $result;

		redirect('schedules/Public_Holidays');
	}
	public function att_rosta_schedules()
	{

		$data['view'] = 'att_and_rosta_schedules';
		$data['module'] = "schedules";
		echo Modules::run("templates/main", $data);
	}

	public function getrotaSchedules()
	{

		$rotaschedules = $this->scheduleMdl->getrotaSchedules();

		return $rotaschedules;
	}
	public function getattSchedules()
	{

		$attschedules = $this->scheduleMdl->getattSchedules();

		return $attschedules;
	}
	public function getleaveSchedules()
	{

		$leaveschedules = $this->scheduleMdl->getleaveSchedules();

		return $leaveschedules;
	}
	public function getrosterKey()
	{

		$rosterkey = $this->scheduleMdl->getrosterKey();

		return $rosterkey;
	}
	public function getleaverosterKey()
	{

		$rosterkey = $this->scheduleMdl->getleaverosterKey();

		return $rosterkey;
	}
	public function getattKey()
	{

		$attkey = $this->scheduleMdl->getattKey();

		return $attkey;
	}


	public function add_schedule()
	{

		$result = $this->scheduleMdl->add_schedule();

		//echo $result;
		redirect('schedules/attendanceSchedules');
	}

	public function update_attschedule()
	{

		$post_data = $this->input->post();
		$result = $this->scheduleMdl->update_attschedule($post_data);

		//echo $result;
		redirect('schedules/attendanceSchedules');
	}



	public function delete_attschedules()
	{
		$id = $this->input->post();
		$result = $this->scheduleMdl->delete_attschedules($id);

		//echo $result;
		redirect('schedules/attendanceSchedules');
	}



	public function add_rosterschedule()
	{

		$result = $this->scheduleMdl->add_rosterschedule();

		//echo $result;
		redirect('schedules/duty_rosta_schedules');
	}

	public function update_rosterschedule()
	{

		$post_data = $this->input->post();
		$result = $this->scheduleMdl->update_rosterschedule($post_data);

		//echo $result;
		redirect('schedules/rotaSchedules');
	}



	public function delete_rosterschedule()
	{
		$id = $this->input->post();
		$result = $this->scheduleMdl->delete_rosterschedule($id);

		//echo $result;
		redirect('schedules/rotaSchedules');
	}
}
