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
    $this->svariables_mdl->ensureRemoteClkColumns();
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

	/**
	 * Dedicated Settings page: Remote Attend clock sync.
	 */
	public function remote_clk()
	{
		$this->svariables_mdl->ensureRemoteClkColumns();
		$data['title'] = "Remote Clock Sync";
		$data['uptitle'] = "Remote Clock Sync";
		$data['module'] = 'svariables';
		$data['view'] = "remote_clk";
		$data['sync_counts'] = $this->svariables_mdl->remoteClkSyncCounts();

		if ($this->input->is_ajax_request()) {
			$this->_handleRemoteClkAjax();
			return;
		}

		$postdata = $this->input->post();
		if ($this->input->post('id') !== null && $this->input->post('id') !== '') {
			$csrf_name = $this->security->get_csrf_token_name();
			if (isset($postdata[$csrf_name])) {
				unset($postdata[$csrf_name]);
			}
			$allowed = ['id', 'remote_clk_enabled', 'remote_clk_url', 'remote_clk_private_key'];
			$save = [];
			foreach ($allowed as $k) {
				if (array_key_exists($k, $postdata)) {
					$save[$k] = $postdata[$k];
				}
			}
			$result = $this->svariables_mdl->update_variables($save);
			if ($result) {
				$this->session->set_flashdata('success', 'Remote clock sync settings saved.');
			} else {
				$this->session->set_flashdata('error', 'Failed to save remote clock sync settings.');
			}
			redirect('svariables/remote_clk');
		}

		echo Modules::run('templates/main', $data);
	}

	private function _handleRemoteClkAjax()
	{
		$csrf_name = $this->security->get_csrf_token_name();
		$postdata = $this->input->post();
		unset($postdata[$csrf_name]);
		$allowed = ['id', 'remote_clk_enabled', 'remote_clk_url', 'remote_clk_private_key'];
		$save = [];
		foreach ($allowed as $k) {
			if (array_key_exists($k, $postdata)) {
				$save[$k] = $postdata[$k];
			}
		}
		$result = $this->svariables_mdl->update_variables($save);
		$new_hash = $this->security->get_csrf_hash();
		$this->output->set_content_type('application/json')->set_output(json_encode([
			'status' => $result ? 'success' : 'error',
			'message' => $result ? 'Remote clock sync settings saved.' : 'Failed to save settings.',
			'csrf_name' => $csrf_name,
			'csrf_hash' => $new_hash
		]));
	}
}
