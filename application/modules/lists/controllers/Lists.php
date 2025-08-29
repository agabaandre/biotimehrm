<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Lists extends MX_Controller
{


	public function __Construct()
	{

		parent::__Construct();

		$this->load->model('districts_mdl');
		$this->load->model('facilities_mdl');
		$this->load->model('cadre_mdl');
		$this->load->model('jobs_mdl');
	}


	//DISTRICTS-----------
	public function getDistricts()
	{
		$data['districts'] = $this->districts_mdl->getDistricts();
		$data['module'] = "lists";
		$data['title'] = "";
		$data['view'] = 'districts/districts';
		echo Modules::run("templates/main", $data);
	}

	public function getDistrict($id)
	{
		$district = $this->districts_mdl->getDistrict($id);
		return $district;
	}

	public function switch_districts()
	{
		return $this->districts_mdl->switch_all_Districts();
	}
	public function get_all_districts()
	{
		return $this->districts_mdl->get_all_Districts();
	}
	public function add_Districts()
	{
		$data['view'] = "add_districts";
		$data['title'] = "Districts";
		$data['module'] = "districts";
		echo Modules::run('templates/main', $data);
	}

	public function save_district()
	{
		$data = $this->input->post();
		$distr = $this->districts_mdl->save_district($data);
		redirect('lists/getDistricts');
	}

	public function updateDistrict()
	{
		$data = $this->input->post();
		$this->districts_mdl->updateDistrict($data);
		redirect('lists/getDistricts');
	}


	public function deleteDistrict()
	{
		$data = $this->input->post();
		$distr_delete = $this->districts_mdl->deleteDistrict($data);
		redirect('lists/getDistricts');
	}

	//end DISTRICTS


	//FACILITIES
	public function getFacilities()
	{
		// Handle AJAX requests for server-side pagination
		if ($this->input->is_ajax_request()) {
			$this->_handleFacilitiesAjaxRequest();
			return;
		}
		
		$data['districts'] = $this->districts_mdl->getDistricts();
		$data['module'] = "lists";
		$data['title'] = "Facilities Management";
		$data['view'] = 'facilities/facilities';
		echo Modules::run("templates/main", $data);
	}
	
	/**
	 * Handle AJAX requests for facilities server-side pagination
	 */
	private function _handleFacilitiesAjaxRequest() {
		try {
			$draw = $this->input->post('draw');
			$start = $this->input->post('start');
			$length = $this->input->post('length');
			$search = $this->input->post('search')['value'];
			$order_column = $this->input->post('order')[0]['column'];
			$order_dir = $this->input->post('order')[0]['dir'];
			$district_filter = $this->input->post('district_filter');
			$category_filter = $this->input->post('category_filter');
			$type_filter = $this->input->post('type_filter');
			
			// Get total count
			$total_records = $this->facilities_mdl->getFacilitiesCount();
			
			// Get filtered data
			$data = $this->facilities_mdl->getFacilitiesAjax($start, $length, $search, $order_column, $order_dir, $district_filter, $category_filter, $type_filter);
			
			// Get filtered count
			$filtered_records = $this->facilities_mdl->getFacilitiesCount($search, $district_filter, $category_filter, $type_filter);
			
			$response = array(
				"draw" => intval($draw),
				"recordsTotal" => $total_records,
				"recordsFiltered" => $filtered_records,
				"data" => $data
			);
			
			$this->output->set_content_type('application/json')->set_output(json_encode($response));
		} catch (Exception $e) {
			log_message('error', 'Facilities AJAX Error: ' . $e->getMessage());
			$response = array(
				"draw" => intval($draw),
				"recordsTotal" => 0,
				"recordsFiltered" => 0,
				"data" => [],
				"error" => "An error occurred while processing your request"
			);
			$this->output->set_content_type('application/json')->set_output(json_encode($response));
		}
	}

	public function get_all_Facilities()
	{
		return $this->facilities_mdl->getAll();
	}

	public function getFacility($id)
	{
		$facility = $this->facilities_mdl->getFacilitiesByDistrict($id);
		return $facility;
	}

	public function saveFacility()
	{
		$data = $this->input->post();
		
		// Validate CSRF token
		$csrf_token_name = $this->security->get_csrf_token_name();
		$csrf_token_hash = $this->security->get_csrf_hash();
		
		// Debug: Log the CSRF validation
		log_message('debug', 'CSRF Token Name: ' . $csrf_token_name);
		log_message('debug', 'CSRF Token Hash: ' . $csrf_token_hash);
		log_message('debug', 'Posted CSRF Token: ' . $this->input->post($csrf_token_name));
		log_message('debug', 'All POST data: ' . json_encode($this->input->post()));
		
		if ($this->input->post($csrf_token_name) !== $csrf_token_hash) {
			log_message('error', 'CSRF validation failed for saveFacility');
			if ($this->input->is_ajax_request()) {
				echo json_encode(['status' => 'error', 'message' => 'Invalid security token']);
				return;
			}
			$this->session->set_flashdata('error', 'Invalid security token');
			redirect('lists/getFacilities');
		}
		
		$result = $this->facilities_mdl->saveFacility($data);
		
		if ($this->input->is_ajax_request()) {
			$response = [
				'status' => strpos($result, 'Successfully') !== false ? 'success' : 'error',
				'message' => $result,
				'csrf_token' => $this->security->get_csrf_hash()
			];
			echo json_encode($response);
			return;
		}
		
		redirect('lists/getFacilities');
	}

	//end FACILITIES

	//CADRE-----------
	public function getCadres()
	{
		$data['cadres'] = $this->cadre_mdl->getCadres();
		$data['module'] = "lists";
		$data['title'] = "";
		$data['view'] = 'cadre/cadre';
		echo Modules::run("templates/main", $data);
	}

	public function getCadre($id)
	{
		$district = $this->cadre_mdl->getCadre($id);
		return $district;
	}

	public function get_all_cadres()
	{
		return $this->cadre_mdl->getCadres();
	}

	public function add_Cadre()
	{
		$data['view'] = "add_cadre";
		$data['title'] = "Cadres";
		$data['module'] = "lists";
		echo Modules::run('templates/main', $data);
	}

	public function save_cadre()
	{
		$data = $this->input->post();
		$distr = $this->cadre_mdl->save_cadre($data);
		redirect('lists/getCadres');
	}

	public function updateCadre()
	{
		$data = $this->input->post();
		$this->cadre_mdl->updateCadre($data);
		redirect('lists/getCadres');
	}


	public function deleteCadre()
	{
		$data = $this->input->post();
		$this->cadre_mdl->deleteCadre($data);
		redirect('lists/getCadres');
	}

	//end CADRE


	//JOBS-----------
	public function getJobs()
	{
		$data['jobs'] = $this->jobs_mdl->getJobs();
		$data['module'] = "lists";
		$data['title'] = "";
		$data['view'] = 'jobs/jobs';
		echo Modules::run("templates/main", $data);
	}

	public function getJob($id)
	{
		$district = $this->jobs_mdl->getJob($id);
		return $district;
	}

	public function get_all_jobs()
	{
		return $this->jobs_mdl->getJobs();
	}

	public function saveJob()
	{
		// Validate CSRF token
		if (!$this->security->get_csrf_hash() || $this->input->post($this->security->get_csrf_token_name()) !== $this->security->get_csrf_hash()) {
			$this->session->set_flashdata('error', 'Invalid security token');
			redirect('lists/getJobs');
		}
		
		$data = $this->input->post();
		
		// Check for duplicate job title
		if ($this->jobs_mdl->isJobTitleDuplicate($data['job_title'])) {
			$this->session->set_flashdata('error', 'Job title already exists. Please use a different title.');
			redirect('lists/getJobs');
		}
		
		// Generate job ID if not provided
		if (empty($data['job_id'])) {
			$data['job_id'] = $this->jobs_mdl->generateJobId();
		}
		
		$result = $this->jobs_mdl->saveJob($data);
		
		if ($this->input->is_ajax_request()) {
			echo json_encode(['status' => 'success', 'message' => $result]);
			return;
		}
		
		redirect('lists/getJobs');
	}
	
	/**
	 * Upload jobs from CSV/Excel file
	 */
	public function uploadJobs()
	{
		// Validate CSRF token
		if (!$this->security->get_csrf_hash() || $this->input->post($this->security->get_csrf_token_name()) !== $this->security->get_csrf_hash()) {
			$this->session->set_flashdata('error', 'Invalid security token');
			redirect('lists/getJobs');
		}
		
		$config['upload_path'] = './uploads/temp/';
		$config['allowed_types'] = 'csv|xlsx|xls';
		$config['max_size'] = 2048; // 2MB
		$config['encrypt_name'] = TRUE;
		
		$this->load->library('upload', $config);
		
		if (!$this->upload->do_upload('jobs_file')) {
			$error = $this->upload->display_errors();
			$this->session->set_flashdata('error', 'Upload failed: ' . $error);
			redirect('lists/getJobs');
		}
		
		$upload_data = $this->upload->data();
		$result = $this->jobs_mdl->processJobsUpload($upload_data['full_path']);
		
		if ($result['status'] === 'success') {
			$this->session->set_flashdata('success', $result['message']);
		} else {
			$this->session->set_flashdata('error', $result['message']);
		}
		
		// Clean up uploaded file
		unlink($upload_data['full_path']);
		
		redirect('lists/getJobs');
	}
	
	/**
	 * Download jobs template
	 */
	public function downloadJobsTemplate()
	{
		$this->load->helper('download');
		
		// Create CSV content
		$csv_data = "job_title,job_id,description\n";
		$csv_data .= "Software Developer,DEV001,Develops software applications\n";
		$csv_data .= "Data Analyst,ANALYST001,Analyzes data and creates reports\n";
		$csv_data .= "Project Manager,PM001,Manages project timelines and resources\n";
		
		$filename = 'jobs_template_' . date('Y-m-d') . '.csv';
		
		force_download($filename, $csv_data);
	}

	public function updateJob()
	{
		$data = $this->input->post();
		$this->jobs_mdl->updateJob($data);
		redirect('lists/getJobs');
	}

	public function deleteJob()
	{
		$data = $this->input->post();
		$this->jobs_mdl->deleteJob($data);
		redirect('lists/getJobs');
	}

	//end JOBS



}
