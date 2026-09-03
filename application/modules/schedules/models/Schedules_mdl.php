<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Schedules_mdl extends CI_Model
{

	protected $table;
	protected $user;
	protected $department;
	protected $division;
	protected $unit;
	protected $facility;
	protected $filters;
	protected $ufilters;
	protected $distfilters;

	public function __Construct()
	{

		parent::__Construct();
		$this->table = "schedules";
	}


	public function getrotaSchedules()
	{

		$this->db->where("purpose", 'r');
		$this->db->where("status", '1');
		$query = $this->db->get('schedules');
		return $query->result();
	}

	public function getattSchedules()
	{

		$this->db->where("purpose", 'a');
		$this->db->where("status", '1');
		$query = $this->db->get('schedules');
		return $query->result();
	}
	public function getleaveSchedules()
	{
		$this->db->select('letter,schedule_id');
		$this->db->where("purpose", 'r');
		$this->db->like("schedule", 'leave', 'both');
		$query = $this->db->get('schedules');
		$res = array();
		foreach ($query->result() as $row) {
			$leaveschedules = $res[$row->letter] = $row->schedule_id;
		}
		return $leaveschedules;
	}

	public function getattSchedules2()
	{
		$this->db->select('letter,schedule_id');
		$this->db->where("purpose", 'a');
		$this->db->where("status", '1');
		$query = $this->db->get('schedules');
		$res = array();
		foreach ($query->result() as $row) {
			$res[$row->letter] = $row->schedule_id;
		}
		return $res;
	}
	public function getrosterKey()
	{

		$this->db->where("purpose", 'r');
		$this->db->where("status", '1');
		$query = $this->db->get('schedules');
		return $query->result();
	}
	public function getleaverosterKey()
	{

		$this->db->where("purpose", 'r');
		$this->db->where("status", '1');
		$this->db->like('schedules.schedule', 'leave', 'both');
		$query = $this->db->get('schedules');
		return $query->result();
	}
	public function getattKey()
	{

		$this->db->where("purpose", 'a');
		$this->db->where("status", '1');
		$query = $this->db->get('schedules');
		return $query->result();
	}

	public function add_schedule()
	{

		$data = array(
			'schedule' => $this->input->post('schedule'),
			'letter' => $this->input->post('letter'),
			'starts' => $this->input->post('starts'),
			'ends' => $this->input->post('ends'),
			'purpose' => $this->input->post('purpose')

		);

		$done = $this->db->insert('schedules', $data);

		if ($done) {

			$message = "Schedule Added";
		} else {


			$message = "Operation Failed";
		}

		return $message;
	}

	public function delete_attschedules($id)
	{

		$att_schdl = $id['schedule_id'];
		$this->db->where('schedule_id', $att_schdl);
		$query = $this->db->delete($this->table, $id);
		if ($query) {

			return "Schedule Deleted";
		}
	}

	public function	update_attschedule($post_data)
	{

		$att_schdl = $post_data['schedule_id'];
		$this->db->where('schedule_id', $att_schdl);
		$query = $this->db->update($this->table, $post_data);

		if ($query) {

			$msg = "Schedule Updated";
		} else {

			$msg = "Operation failed, Try again";
		}


		return $msg;
	}




	public function add_rosterschedule()
	{

		$data = array(
			'schedule' => $this->input->post('schedule'),
			'letter' => $this->input->post('letter'),
			'starts' => $this->input->post('starts'),
			'ends' => $this->input->post('ends'),
			'purpose' => $this->input->post('purpose')

		);

		$done = $this->db->insert('schedules', $data);

		if ($done) {

			$message = "Duty roster Schedule Added";
		} else {

			$message = "Operation Failed";
		}

		return $message;
	}

	public function delete_rosterschedule($id)
	{

		$att_schdl = $id['schedule_id'];
		$this->db->where('schedule_id', $att_schdl);
		$query = $this->db->delete($this->table, $id);
		if ($query) {

			return "Schedule Deleted";
		}
	}

	public function	update_rosterschedule($post_data)
	{

		$att_schdl = $post_data['schedule_id'];
		$this->db->where('schedule_id', $att_schdl);
		$query = $this->db->update($this->table, $post_data);

		if ($query) {

			$msg = "Schedule Updated";
		} else {

			$msg = "Operation failed, Try again";
		}


		return $msg;
	}



	public function get_publicHoliday()
	{
		$query = $this->db->get('public_holiday');
		return $query->result();
	}
	
	public function get_publicHoliday_count($search = '')
	{
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('holiday_name', $search);
			$this->db->or_like('type', $search);
			$this->db->or_like('year', $search);
			$this->db->or_like('holidaydate', $search);
			$this->db->group_end();
		}
		
		return $this->db->count_all_results('public_holiday');
	}
	
	public function get_publicHoliday_ajax($start = 0, $length = 10, $search = '', $order_column = 0, $order_dir = 'asc')
	{
		$columns = array('holidaydate', 'holiday_name', 'type', 'year', 'id');
		
		// Apply search
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('holiday_name', $search);
			$this->db->or_like('type', $search);
			$this->db->or_like('year', $search);
			$this->db->or_like('holidaydate', $search);
			$this->db->group_end();
		}
		
		// Apply ordering
		if (isset($columns[$order_column])) {
			$this->db->order_by($columns[$order_column], $order_dir);
		} else {
			$this->db->order_by('holidaydate', 'asc');
		}
		
		// Apply pagination
		$this->db->limit($length, $start);
		
		$query = $this->db->get('public_holiday');
		return $query->result();
	}

	/**
	 * Save a public holiday from the Add New Holiday form.
	 * Accepts: holiday_name, dateFrom (or date), year, type
	 *
	 * @param array $postdata
	 * @return string Flash message
	 */
	public function save_publicHoliday($postdata = [])
	{
		if (!is_array($postdata) || empty($postdata)) {
			$postdata = $this->input->post();
		}

		$name = trim((string) ($postdata['holiday_name'] ?? $postdata['holidayname'] ?? ''));
		$date = trim((string) ($postdata['dateFrom'] ?? $postdata['date'] ?? $postdata['holidaydate'] ?? ''));
		$type = trim((string) ($postdata['type'] ?? ''));
		$year = trim((string) ($postdata['year'] ?? ''));

		if ($name === '' || $date === '' || $type === '') {
			$message = 'Holiday name, date and type are required';
			$this->session->set_flashdata('msg', $message);
			return $message;
		}

		$ts = strtotime($date);
		if ($ts === false) {
			$message = 'Invalid holiday date';
			$this->session->set_flashdata('msg', $message);
			return $message;
		}

		$date = date('Y-m-d', $ts);
		if ($year === '') {
			$year = date('Y', $ts);
		}

		// Match existing unique id pattern: {date}{holiday_name}
		$entryId = $date . $name;

		// Avoid duplicate unique key errors
		$exists = $this->db->where('id', $entryId)->count_all_results('public_holiday');
		if ($exists > 0) {
			$message = 'Holiday already exists for that date';
			$this->session->set_flashdata('msg', $message);
			return $message;
		}

		$data = [
			'id' => $entryId,
			'holiday_name' => $name,
			'type' => $type,
			'holidaydate' => $date,
			'year' => $year,
		];

		$done = $this->db->insert('public_holiday', $data);

		if ($done) {
			$message = 'Holiday added Successfully';
		} else {
			$error = $this->db->error();
			$message = 'Operation Failed' . (!empty($error['message']) ? (': ' . $error['message']) : '');
			log_message('error', 'save_publicHoliday failed: ' . json_encode($error));
		}

		$this->session->set_flashdata('msg', $message);
		return $message;
	}

	public function addRequest()
	{
		return $this->save_publicHoliday($this->input->post());
	}

	public function	update_publicHoliday($post_data)
	{
		if (!is_array($post_data) || empty($post_data['id'])) {
			return 'Missing holiday id';
		}

		$id = $post_data['id'];
		unset($post_data['id']);

		// Only update known columns
		$allowed = ['holiday_name', 'type', 'holidaydate', 'year'];
		$data = [];
		foreach ($allowed as $col) {
			if (isset($post_data[$col]) && $post_data[$col] !== '') {
				$data[$col] = $post_data[$col];
			}
		}
		if (empty($data)) {
			return 'No changes submitted';
		}

		$this->db->where('id', $id);
		$query = $this->db->update('public_holiday', $data);

		if ($query) {
			$msg = 'Holiday Updated Successfully';
		} else {
			$msg = 'Operation failed, Try again';
		}

		return $msg;
	}

	public function delete_publicHoliday($id)
	{
		// Support both rid (numeric PK) and unique id string
		if (ctype_digit((string) $id)) {
			$this->db->where('rid', $id);
		} else {
			$this->db->where('id', $id);
		}
		$query = $this->db->delete('public_holiday');
		if ($query) {
			$this->session->set_flashdata('msg', 'Holiday deleted Successfully');
			return 'Holiday deleted Successfully';
		}
		$this->session->set_flashdata('msg', 'Delete failed');
		return 'Delete failed';
	}
}
