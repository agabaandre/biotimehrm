<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Svariables extends MX_Controller
{
	protected $user;

	public function __Construct()
	{

		parent::__Construct();

		$this->load->model('svariables_mdl');
		$this->user = $this->session->get_userdata();
	}


	public function index()
	{
		$data['title'] = "Settings - Constants & Variables";
		$data['uptitle'] = "Constants & Variables";
		$data['module'] = 'svariables';
		$data['view'] = "variables";
		
		// Handle AJAX requests
		if ($this->input->is_ajax_request()) {
			$this->_handleAjaxRequest();
			return;
		}
		
		$postdata = $this->input->post();
		if ($this->input->post('language')) {
			$result = $this->svariables_mdl->update_variables($postdata);
			if (strpos($result, 'Successful') !== false) {
				$this->session->set_flashdata('success', 'Settings updated successfully!');
			} else {
				$this->session->set_flashdata('error', 'Failed to update settings. Please try again.');
			}
			redirect("svariables/index");
		} else {
			echo Modules::run('templates/main', $data);
		}
	}
	
	/**
	 * Handle AJAX requests for updating variables
	 */
	private function _handleAjaxRequest() {
		// Validate CSRF token
		if (!$this->security->get_csrf_hash() || $this->input->post($this->security->get_csrf_token_name()) !== $this->security->get_csrf_hash()) {
			echo json_encode(['status' => 'error', 'message' => 'Invalid security token']);
			return;
		}
		
		$postdata = $this->input->post();
		$result = $this->svariables_mdl->update_variables($postdata);
		
		if (strpos($result, 'Successful') !== false) {
			echo json_encode([
				'status' => 'success', 
				'message' => 'Settings updated successfully!',
				'csrf_token' => $this->security->get_csrf_hash()
			]);
		} else {
			echo json_encode([
				'status' => 'error', 
				'message' => 'Failed to update settings. Please try again.',
				'csrf_token' => $this->security->get_csrf_hash()
			]);
		}
	}
	public function getSettings()
	{
		return $this->svariables_mdl->getSettings();
	}
	public function readLogs()
	{
		$myfile = fopen("log.txt", "r") or die("Unable to open file!");

		$myfiles = fread($myfile, filesize("log.txt"));

	
		// Escape HTML entities to prevent potential XSS attacks
		return $logContent = htmlspecialchars($myfiles, ENT_QUOTES);

	
	}
	public function logs()
	{
		$data['title'] = "Biotime & System Logs";
		$data['uptitle'] = "Biotime & System Logs";
		$data['module'] = 'svariables';
		$data['view'] = "logs";
		echo Modules::run('templates/main', $data);
	}
}
