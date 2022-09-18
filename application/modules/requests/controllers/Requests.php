<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Requests extends MX_Controller
{


	public function __Construct()
	{

		parent::__Construct();

		$this->load->model('requests_mdl');
		$this->user = $this->session->get_userdata();
		$this->department = $this->user['department_id'];
	}

	public function newRequest()
	{

		//$data['requests']=$this->requests;
		$data['title'] = 'Make Request';
		$data['uptitle'] = 'Request';
		$data['view'] = 'Make_requests';
		$data['module'] = "requests";
		echo Modules::run('templates/main', $data);
	}

	public function saveRequest()
	{

		$postdata = $this->input->post();


		$file_n = $this->input->post('files');
		$userfile = $this->user['user_id'] . "_" . time();

		$config['upload_path']          = './assets/files/';
		$config['allowed_types']        = 'pdf|xlsx|docx|jpg|png';
		$config['file_name']            = $file_n . "" . $userfile;
		$config['overwrite']            = TRUE;
		$this->load->library('upload', $config);

		// File data
		if ($this->upload->do_upload('files')) {
			$image_data = $this->upload->data();
			//print_r($this->upload->data());
			$postdata['attachment'] = $image_data['file_name'];
			$res = $this->requests_mdl->saveRequest($postdata);
		} else {
			//	$res=$this->upload->display_errors();
			$res = $this->requests_mdl->saveRequest($postdata);
		}



		Modules::run('utility/setFlash', $res);

		redirect('requests/newRequest');
	}




	public function updateRequest()
	{

		$entry_id = $this->input->post('entry_id');
		$clarification = $this->input->post('clarification');
		$reason_id = $this->input->post('reason_id');
		$userdata = $this->session->get_userdata();
		$name = $userdata['names'];
		$date = date('j F,Y H:i:s');
		$person = $this->input->post('employee');

		$this->db->where('entry_id', $entry_id);
		$query = $this->db->get('requests');
		$oldremarks = $query->result();
		//print_r($oldremarks);
		foreach ($oldremarks as $remark) {
			$oremarks = $remark->remarks;
		}
		$newremarks = $this->input->post('remarks');
		if ($person != 'employee') {
			$remarks = $oremarks . "<hr style='height:2px; margin:1px; border:none;  background-color:#e0e0e0;width:60%;'>
		<p class='speech-bubble' style='background:#263238; font-size:12px; color:#fff;'><br>From: <b>" . $name . "</b>" . "  " . $date . ":<br>" . " " . $newremarks . "</p>";
		} else {

			$remarks = $oremarks . "<hr style='height:1px;  margin:2px; border:none;  background-color:#e0e0e0;width:60%;'>
		<p class='speech-bubble2' style='background:#546e7a; font-size:12px; color:#fff;'><br>From: <b>" . $name . "</b>" . " " . $date . ":<br>" . " " . $newremarks . "</p>";
		}
		$data = array(

			'entry_id' => "$entry_id",
			'remarks' => "$remarks",
			'clarification' => "$clarification",
			'reason_id' => "$reason_id"

		);

		print_r($data);
		$sendRequest = $this->requests_mdl->updateRequest($data, $entry_id);
		Modules::run('utility/setFlash', " Sent");

		$redirect = $this->input->post('direct');
		redirect($redirect);

		// $file_n=$this->input->post('attachment');
		// $userfile=$this->user['user_id']."_".time();

		// $config['upload_path']          = 'assets/files/';
		// $config['allowed_types']        = 'pdf|xlsx|docx|jpg|png';
		// /*$config['min_width']            = 200;
		// $config['min_height']           = 200;
		// $config['max_size']             = 6000;
		// $config['max_width']            = 200;
		// $config['max_height']           = 200;*/
		// $config['file_name']            = $file_n."".$userfile;
		// $config['overwrite']            = TRUE;
		// $this->load->library('upload', $config);

		// if (! $this->upload->do_upload('attachment') && $_FILES['attachment']=""){

		// 	$this->session->set_flashdata('error',$this->upload->display_errors()) ;
		// 	redirect('requests/viewMySubmittedRequests');

		// }
		// else
		// {
		// 	// File data
		// 	if($this->upload->do_upload('attachment')){
		// 		$image_data = $this->upload->data();
		// 		$file=$data['attachment'] =$image_data['file_name'];	


		// 	}



		// $this->db->where('entry_id',$data['entry_id']);
		// $query= $this->db->update('requests',$data);



		// if($query==true){

		// 	$res = "Request Updated";
		// }
		// else{
		// 	$res = "Operation failed";
		// }

		// Modules::run('utility/setFlash',$res);

		// redirect('requests/viewMySubmittedRequests');

	}







	public function acceptRequest($entryid)
	{

		$sendRequest = $this->requests_mdl->acceptRequest($entryid);

		$this->logAsActual($entryid);
		Modules::run('utility/setFlash', "Request Accepted");

		redirect('requests/viewRequests');
	}

	public function clalify($entryid)
	{

		$sendRequest = $this->requests_mdl->clalify($entryid);

		Modules::run('utility/setFlash', "Clalification Request Sent");

		redirect('requests/viewRequests');
	}
	// Add data to attendance
	public function logAsActual($entryid)
	{
		$postdata = $this->requests_mdl->getPending(NULL, NULL, NULL, $entryid); //and ignore state

		$start = strtotime($postdata->dateFrom); // or your date as well
		$end = strtotime($postdata->dateTo);

		$datediff = $end - $start;

		$days = floor($datediff / (60 * 60 * 24));

		if ($days > 1) {
			for ($i = 0; $i <= $days; $i++) {

				$oneday = "+" . $i . " day"; //1 day

				$newdate = date('Y-m-d', strtotime($oneday, $start));
				$enddate = date('Y-m-d', (strtotime('1 day', strtotime($newdate))));

				$data = array(
					'entry_id' => $newdate . $postdata->ihris_pid,
					'schedule_id' => $postdata->schedule_id,
					'ihris_pid' => $postdata->ihris_pid,
					'facility_id' => $postdata->facility_id,
					'date' => $newdate,
					'endate' => $enddate
				);

				//print_r($postdata);
				$sendRequest = $this->requests_mdl->saveIntoActuals($data);
			} //for
		}
	}

	public function rejectRequest($entryid)
	{

		$sendRequest = $this->requests_mdl->rejectRequest($entryid);
		Modules::run('utility/setFlash', "Request Rejected");

		redirect('requests/viewRequests');
	}

	public function getAll()
	{


		$requests = $this->requests_mdl->getAll();
		return $requests;
	}

	public function getPending($user_id = FALSE, $userlimit = NULL, $status = NULL, $entry_id = NULL)
	{
		$requests = $this->requests_mdl->getPending($user_id, $userlimit, $status, $entry_id);

		return $requests;
	}
	public function leaveRequests()
	{

		$this->load->library('pagination');
		$config = array();
		$config['base_url'] = base_url() . "requests/leaveRequests";
		$config['total_rows'] = Modules::run('requests/countleaverequests');
		$config['per_page'] = 15; //records per page
		$config['uri_segment'] = 3; //segment in url  
		//pagination links styling
		$config['full_tag_open'] = "<ul class='pagination'>";
		$config['full_tag_close'] = '</ul>';
		$config['num_tag_open'] = '<li>';
		$config['num_tag_close'] = '</li>';
		$config['cur_tag_open'] = '<li class="active"><a href="#">';
		$config['cur_tag_close'] = '</a></li>';
		$config['prev_tag_open'] = '<li>';
		$config['prev_tag_close'] = '</li>';
		$config['first_tag_open'] = '<li>';
		$config['first_tag_close'] = '</li>';
		$config['last_tag_open'] = '<li>';
		$config['last_tag_close'] = '</li>';



		$config['prev_link'] = '<i class="fa fa-angle-double-left"></i>';
		$config['prev_tag_open'] = '<li>';
		$config['prev_tag_close'] = '</li>';


		$config['next_link'] = '<i class="fa fa-angle-double-right"></i>';
		$config['next_tag_open'] = '<li>';
		$config['next_tag_close'] = '</li>';
		$config['use_page_numbers'] = true;

		$this->pagination->initialize($config);

		$page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0; //default starting point for limits


		$data['links'] = $this->pagination->create_links();


		$data['requests'] = $this->requests_mdl->getleavePending($config['per_page'], $page);
		$data['title'] = 'Pending Leave Requests';
		$data['view'] = 'leaverequest';
		$data['module'] = "requests";
		//print_r($data);

		//$this->load->view('requests/leaverequest');
		echo Modules::run('templates/main', $data);
	}
	public function countleaverequests($user_id = FALSE)
	{
		$requests = $this->requests_mdl->countleavePending();
		return $requests;
	}

	public function get_requests($user_id = FALSE)
	{
		$requests = $this->requests_mdl->get_requests($user_id);
		return $requests;
	}

	public function requestById($rq_Id)
	{
		$requestInfo = $this->requests_mdl->getById($rq_Id);
		return $requestInfo;
	}

	public function viewRequests()
	{

		$data['title'] = 'Pending Requests';
		$data['uptitle'] = 'Pending Requests';
		$data['view'] = 'incoming_requests';
		$data['module'] = "requests";
		echo Modules::run('templates/main', $data);
	}

	public function viewMySubmittedRequests()
	{

		$data['title'] = 'My Submitted Requests';
		$data['view'] = 'view_pending_requests';
		$data['module'] = "requests";
		echo Modules::run('templates/main', $data);
	}

	public function requestReport()
	{

		$data['title'] = 'Requests Report';
		$data['view'] = 'requests_report';
		$data['module'] = "requests";
		echo Modules::run('templates/main', $data);
	}


	public function confirm_request()
	{

		$cofmR = $this->requests_mdl->confirm_request();

		echo $cofmR;
	}
	public function cancelRequest($requestId)
	{

		$cancel = $this->requests_mdl->cancelRequest($requestId);
		$message = $this->session->set_flashdata('msg', 'Cancellation Successful');


		redirect('requests/viewMySubmittedRequests');
	}
	public function getLeave()
	{
		$this->db->select('schedule_id');
		$this->db->like('schedule', 'leave', 'both');
		$query = $this->db->get('schedules');
		$data = $query->result();
		$array = array();
		foreach ($data as $row) {
			$out = $row->schedule_id;
			array_push($array, $out);
		}
		return $array;
	}




	public function print_request_report($p = FALSE)
	{

		$this->load->library('ML_pdf');

		$data['requests'] = $this->requests_mdl->request_report($p);

		$html = $this->load->view('requests_rpt', $data, true);
		$date = date('Y-m-d H:i:s');
		$filename = "requests_report" . $date . ".pdf";


		ini_set('max_execution_time', 0);
		$PDFContent = mb_convert_encoding($html, 'UTF-8', 'UTF-8');

		$this->ml_pdf->pdf->SetWatermarkImage($this->watermark);
		$this->ml_pdf->pdf->showWatermarkImage = true;

		date_default_timezone_set("Africa/Kampala");
		$this->ml_pdf->pdf->SetHTMLFooter("<b>Printed on: </b>" . date('D-d F,Y  h:i A') . "</b>");

		$this->ml_pdf->pdf->SetWatermarkImage($this->watermark);
		$this->ml_pdf->showWatermarkImage = true;

		ini_set('max_execution_time', 0);
		$this->ml_pdf->pdf->WriteHTML($PDFContent); //ml_pdf because we loaded the library ml_pdf for landscape format not m_pdf

		//download it D save F.
		$this->ml_pdf->pdf->Output($filename, 'I');
	}




	public function countPending()
	{

		$count_R = $this->requests_mdl->countPending();

		echo $count_R;
	}

	public function getApprover($person)
	{
		$person = urldecode($person);
		$this->db->select('CONCAT( surname,  " ", firstname,  " ", othername ) as name');
		$this->db->where('ihris_pid', $person);
		$query = $this->db->get('ihrisdata');

		$persons = $query->result();
		foreach ($persons as $pp) {

			$data = $pp->name;


			return $data;
		}
	}
}
