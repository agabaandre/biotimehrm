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
		// Run update when variables form is submitted (has id = setting row id)
		if ($this->input->post('id') !== null && $this->input->post('id') !== '') {
			$csrf_name = $this->security->get_csrf_token_name();
			if (isset($postdata[$csrf_name])) unset($postdata[$csrf_name]);
			$result = $this->svariables_mdl->update_variables($postdata);
			if ($result) {
				$this->session->set_flashdata('success', 'Settings updated successfully!');
			} else {
				$this->session->set_flashdata('error', 'Failed to update settings. Please try again.');
			}
			redirect("svariables/index");
		}
		echo Modules::run('templates/main', $data);
	}
	
	/**
	 * Handle AJAX requests for updating variables. CSRF validated then stripped before DB update.
	 */
	private function _handleAjaxRequest() {
		$csrf_name = $this->security->get_csrf_token_name();
		$csrf_hash = $this->security->get_csrf_hash();
		$post_token = $this->input->post($csrf_name);
		if (!$csrf_hash || $post_token === null || $post_token === '' || !hash_equals((string) $csrf_hash, (string) $post_token)) {
			$this->output->set_content_type('application/json')->set_output(json_encode([
				'status' => 'error',
				'message' => 'Invalid security token. Please refresh the page and try again.',
				'csrf_name' => $csrf_name,
				'csrf_hash' => $this->security->get_csrf_hash()
			]));
			return;
		}

		$postdata = $this->input->post();
		unset($postdata[$csrf_name]);
		$result = $this->svariables_mdl->update_variables($postdata);

		$new_hash = $this->security->get_csrf_hash();
		if ($result) {
			$this->output->set_content_type('application/json')->set_output(json_encode([
				'status' => 'success',
				'message' => 'Settings updated successfully!',
				'csrf_name' => $csrf_name,
				'csrf_hash' => $new_hash
			]));
		} else {
			$this->output->set_content_type('application/json')->set_output(json_encode([
				'status' => 'error',
				'message' => 'Failed to update settings. Please try again.',
				'csrf_name' => $csrf_name,
				'csrf_hash' => $new_hash
			]));
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
