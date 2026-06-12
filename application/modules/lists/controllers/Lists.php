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
		$data['regions'] = $this->districts_mdl->getDistinctRegions();
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

	/**
	 * JSON payload for Switch Facility modal (facilities grouped by district_id).
	 * Restricted users only receive facilities for their current district.
	 */
	public function switch_facility_data()
	{
		if (!$this->session->userdata('user_id')) {
			$this->output->set_status_header(401);
			$this->output->set_content_type('application/json')->set_output(json_encode(['error' => 'Unauthorized']));
			return;
		}

		$this->load->library('facility_switch_cache', null, 'fsc');
		$data = $this->fsc->get_data();

		$permissions = $this->session->userdata('permissions');
		if (!is_array($permissions)) {
			$permissions = [];
		}

		$payload = [
			'generated_at_iso'       => isset($data['generated_at_iso']) ? $data['generated_at_iso'] : null,
			'districts'              => isset($data['districts']) ? $data['districts'] : [],
			'facilities_by_district' => isset($data['facilities_by_district']) ? $data['facilities_by_district'] : [],
			'district_id_aliases'    => isset($data['district_id_aliases']) && is_array($data['district_id_aliases'])
				? $data['district_id_aliases']
				: [],
		];

		if (!in_array('38', $permissions)) {
			$district_id = (string) $this->session->userdata('district_id');
			if ($district_id === '' && isset($_SESSION['district_id'])) {
				$district_id = (string) $_SESSION['district_id'];
			}
			$facility_id = (string) $this->session->userdata('facility');
			if ($facility_id === '' && isset($_SESSION['facility'])) {
				$facility_id = (string) $_SESSION['facility'];
			}
			$list = isset($payload['facilities_by_district'][$district_id])
				? $payload['facilities_by_district'][$district_id]
				: [];
			if ($facility_id !== '') {
				$list = array_values(array_filter($list, function ($row) use ($facility_id) {
					return isset($row['facility_id']) && $row['facility_id'] === $facility_id;
				}));
			}
			$payload['facilities_by_district'] = [$district_id => $list];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($payload));
	}

	/**
	 * Rebuild Switch Facility district/facility cache from ihrisdata (manual refresh).
	 */
	public function rebuild_switch_facility_cache()
	{
		if (!$this->session->userdata('user_id')) {
			show_error('Unauthorized', 401);
		}
		$permissions = $this->session->userdata('permissions');
		if (!is_array($permissions)) {
			$permissions = [];
		}
		if (!in_array('34', $permissions) && !in_array('38', $permissions)) {
			show_error('Forbidden', 403);
		}

		$this->load->library('facility_switch_cache', null, 'fsc');
		try {
			$result = $this->fsc->rebuild();
		} catch (Exception $e) {
			$result = ['status' => 'error', 'message' => $e->getMessage()];
		}

		if ($this->input->is_ajax_request()) {
			$this->output->set_content_type('application/json')->set_output(json_encode($result));
			return;
		}

		$msg = isset($result['status']) && $result['status'] === 'success'
			? 'Facility list cache rebuilt (' . (int) $result['districts'] . ' districts, ' . (int) $result['facilities'] . ' facilities).'
			: 'Facility cache rebuild failed.';
		$this->session->set_flashdata('message', $msg);
		$redirect = $this->input->get('redirect');
		if ($redirect) {
			redirect($redirect);
			return;
		}
		redirect($_SERVER['HTTP_REFERER'] ?: 'dashboard');
	}
	public function get_all_districts()
	{
		return $this->districts_mdl->getDistricts();
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
		$result = $this->districts_mdl->save_district($data);
		$ok = stripos($result, 'success') !== false || stripos($result, 'added') !== false;
		if ($ok && function_exists('invalidate_dropdown_cache')) {
			invalidate_dropdown_cache();
		}
		$this->session->set_flashdata($ok ? 'success' : 'error', $result);
		redirect('lists/getDistricts');
	}

	public function updateDistrict()
	{
		$data = $this->input->post();
		$message = $this->districts_mdl->updateDistrict($data);
		$ok = stripos($message, 'updated') !== false;
		if ($ok && function_exists('invalidate_dropdown_cache')) {
			invalidate_dropdown_cache();
		}
		$this->session->set_flashdata($ok ? 'success' : 'error', $message);
		redirect('lists/getDistricts');
	}


	public function deleteDistrict()
	{
		$this->session->set_flashdata('error', 'Deleting districts is not allowed.');
		redirect('lists/getDistricts');
	}

	//end DISTRICTS


	//FACILITIES
	public function getFacilities()
	{
		// DataTables POST includes draw; some clients omit X-Requested-With.
		if ($this->input->is_ajax_request() || $this->input->post('draw') !== null) {
			$this->_handleFacilitiesAjaxRequest();
			return;
		}
		
		$data['districts'] = $this->districts_mdl->getDistricts();
		$data['module'] = "lists";
		$data['title'] = entity_label('facility', true) . ' Management';
		$data['uptitle'] = entity_label('facility', true) . ' Management';
		$data['import_template_headers'] = $this->facilities_mdl->importTemplateHeaders();
		$data['view'] = 'facilities/facilities';
		echo Modules::run("templates/main", $data);
	}
	
	/**
	 * Handle AJAX requests for facilities server-side pagination
	 */
	private function _handleFacilitiesAjaxRequest() {
		$draw = (int) $this->input->post('draw');
		try {
			$start = (int) $this->input->post('start');
			$length = (int) $this->input->post('length');
			if ($length <= 0) {
				$length = 25;
			}

			$search_post = $this->input->post('search');
			$search = (is_array($search_post) && isset($search_post['value']))
				? trim((string) $search_post['value'])
				: '';

			$order_post = $this->input->post('order');
			$order_column = 1;
			$order_dir = 'asc';
			if (is_array($order_post) && isset($order_post[0]) && is_array($order_post[0])) {
				$order_column = isset($order_post[0]['column']) ? (int) $order_post[0]['column'] : 1;
				$order_dir = isset($order_post[0]['dir']) ? strtolower((string) $order_post[0]['dir']) : 'asc';
			}
			if (!in_array($order_dir, ['asc', 'desc'], true)) {
				$order_dir = 'asc';
			}

			$district_filter = (string) $this->input->post('district_filter');
			$category_filter = (string) $this->input->post('category_filter');
			$type_filter = (string) $this->input->post('type_filter');
			
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
		} catch (Throwable $e) {
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
		return $this->facilities_mdl->getAllForEmployeeForm();
	}

	public function getFacility($id)
	{
		$this->load->library('facility_switch_cache', null, 'fsc');
		return $this->fsc->get_facilities_for_district($id);
	}

	public function saveFacility()
	{
		$data = $this->input->post();
		if (empty($data)) {
			return $this->_facilitySaveResponse('error', 'No data received');
		}

		// CSRF is validated globally by CodeIgniter (token is removed from POST after verify).
		$result = $this->facilities_mdl->saveFacility($data);
		$ok = stripos($result, 'success') !== false || stripos($result, 'added') !== false;
		if ($ok && function_exists('invalidate_dropdown_cache')) {
			invalidate_dropdown_cache();
		}

		return $this->_facilitySaveResponse($ok ? 'success' : 'error', $result);
	}

	/**
	 * Next facility / school ID for the add-facility form.
	 */
	public function nextFacilityId()
	{
		$this->output->set_content_type('application/json')->set_output(json_encode([
			'facility_id' => $this->facilities_mdl->generateNextFacilityId(),
			'csrf_token'  => $this->security->get_csrf_hash(),
		]));
	}

	/**
	 * JSON record for edit facility modal.
	 *
	 * @param int|null $id
	 */
	public function getFacilityRecord($id = null)
	{
		$id = (int) ($id ?: $this->input->post('id'));
		$row = $this->facilities_mdl->getFacilityById($id);

		if (!$row) {
			return $this->output->set_status_header(404)
				->set_content_type('application/json')
				->set_output(json_encode([
					'status'  => 'error',
					'message' => entity_label('entity_not_found'),
				]));
		}

		return $this->output->set_content_type('application/json')->set_output(json_encode([
			'status'   => 'success',
			'facility' => [
				'id'                   => (int) $row->id,
				'facility_id'          => $row->facility_id,
				'facility'             => $row->facility,
				'district_id'          => $row->district_id,
				'institution_category' => $row->institution_category,
				'institution_type'     => $row->institution_type,
				'institution_level'    => $row->institution_level,
			],
			'csrf_token' => $this->security->get_csrf_hash(),
		]));
	}

	public function updateFacility()
	{
		$data = $this->input->post();
		if (empty($data)) {
			return $this->_facilitySaveResponse('error', 'No data received');
		}

		$result = $this->facilities_mdl->updateFacility($data);
		$ok = stripos($result, 'successfully') !== false;
		if ($ok && function_exists('invalidate_dropdown_cache')) {
			invalidate_dropdown_cache();
		}

		return $this->_facilitySaveResponse($ok ? 'success' : 'error', $result);
	}

	public function deleteFacility()
	{
		return $this->_facilitySaveResponse('error', 'Deleting ' . strtolower(entity_label('facility', true)) . ' is not allowed.');
	}

	/**
	 * @param string $status
	 * @param string $message
	 */
	private function _facilitySaveResponse($status, $message)
	{
		$payload = [
			'status'     => $status,
			'message'    => $message,
			'csrf_token' => $this->security->get_csrf_hash(),
		];

		if ($this->input->is_ajax_request()) {
			return $this->output->set_content_type('application/json')->set_output(json_encode($payload));
		}

		$this->session->set_flashdata($status === 'success' ? 'success' : 'error', $message);
		redirect('lists/getFacilities');
	}

	/**
	 * Download CSV template for bulk school/facility import.
	 */
	public function downloadFacilityImportTemplate()
	{
		$districts = $this->districts_mdl->getDistricts();
		$example_district = '';
		if (!empty($districts)) {
			$first = $districts[0];
			$example_district = $first->name ?? '';
		}

		$defaults = $this->facilities_mdl->importDefaultFieldValues();
		$headers = $this->facilities_mdl->importTemplateHeaders();
		$entity_plural = strtolower(entity_label('facility', true));
		$filename = $entity_plural . '_import_template_' . date('Y-m-d') . '.csv';

		header('Content-Type: text/csv; charset=UTF-8');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Cache-Control: no-cache');

		$fh = fopen('php://output', 'w');
		fputcsv($fh, $headers);
		fputcsv($fh, [
			'Example ' . entity_label('facility'),
			$example_district,
			$defaults['institution_category'],
			$defaults['institution_type'],
			$defaults['institution_level'],
		]);
		fclose($fh);
		exit;
	}

	/**
	 * Import schools/facilities from uploaded CSV.
	 */
	public function importFacilities()
	{
		if (empty($_FILES['import_file']['tmp_name'])) {
			return $this->_facilitySaveResponse('error', 'Please choose a CSV file to import.');
		}

		$tmp = $_FILES['import_file']['tmp_name'];
		$ext = strtolower(pathinfo((string) $_FILES['import_file']['name'], PATHINFO_EXTENSION));
		if (!in_array($ext, ['csv', 'txt'], true)) {
			return $this->_facilitySaveResponse('error', 'Only CSV files are supported.');
		}

		$parsed_file = $this->facilities_mdl->parseImportCsvFile($tmp);
		if (!empty($parsed_file['error'])) {
			return $this->_facilitySaveResponse('error', $parsed_file['error']);
		}

		$result = $this->facilities_mdl->importFacilitiesFromRows($parsed_file['rows']);
		if ($result['imported'] > 0 && function_exists('invalidate_dropdown_cache')) {
			invalidate_dropdown_cache();
		}
		$message = 'Imported ' . (int) $result['imported'] . ' ' . strtolower(entity_label('facility', true));
		if ($result['skipped'] > 0) {
			$message .= ', skipped ' . (int) $result['skipped'];
		}
		if (!empty($result['errors'])) {
			$message .= '. ' . implode(' ', array_slice($result['errors'], 0, 5));
			if (count($result['errors']) > 5) {
				$message .= ' (and ' . (count($result['errors']) - 5) . ' more)';
			}
		}

		$status = $result['imported'] > 0 ? 'success' : 'error';
		if ($result['imported'] > 0 && !empty($result['errors'])) {
			$status = 'success';
		}

		return $this->_facilitySaveResponse($status, $message);
	}

	/**
	 * Test CSRF token generation
	 */
	public function testCsrf()
	{
		$csrf_token_name = $this->security->get_csrf_token_name();
		$csrf_token_hash = $this->security->get_csrf_hash();
		
		$response = [
			'csrf_token_name' => $csrf_token_name,
			'csrf_token_hash' => $csrf_token_hash,
			'session_id' => session_id(),
			'timestamp' => time()
		];
		
		echo json_encode($response);
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
		$this->session->set_flashdata('error', 'Deleting cadres is not allowed.');
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
		$data = $this->input->post();
		if (empty($data['job_title'])) {
			$this->session->set_flashdata('error', 'Job title is required.');
			redirect('lists/getJobs');
		}

		$result = $this->jobs_mdl->saveJob($data);
		$ok = stripos($result, 'success') !== false || stripos($result, 'added') !== false;

		if ($this->input->is_ajax_request()) {
			echo json_encode([
				'status'  => $ok ? 'success' : 'error',
				'message' => $result,
			]);
			return;
		}

		$this->session->set_flashdata($ok ? 'success' : 'error', $result);
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
		$message = $this->jobs_mdl->updateJob($data);
		$ok = stripos($message, 'updated') !== false;
		$this->session->set_flashdata($ok ? 'success' : 'error', $message);
		redirect('lists/getJobs');
	}

	public function deleteJob()
	{
		$this->session->set_flashdata('error', 'Deleting jobs is not allowed.');
		redirect('lists/getJobs');
	}

	//end JOBS



}
