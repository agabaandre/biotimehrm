<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MX_Controller {

	protected $dashmodule;
	public  function __construct(){
		parent:: __construct();

			@$this->dashmodule="dashboard";
			$this->load->model("dashboard_mdl",'dash_mdl');
		  


	}

	public function index()
	{
		$data['module']=$this->dashmodule;
		$data['title']="Main Dashboard";
		$data['uptitle']="Main Dashboard";
		$data['view']= "home";
		echo Modules::run('templates/main',$data);
	}
	public function dashboardData(){

    //   if (($this->cache->memcached->get('dashboard')) && ($this->session->userdata('facility')==$this->cache->memcached->get('facility'))) {
    // // Data not found in cache, perform your data retrieval or processing logic here
	// 	$data = $this->cache->memcached->get('dashboard');
    // // Store the processed data in the cache
	// 	} 
	// 	else {
		$data = $this->dash_mdl->stats();
		// $this->cache->memcached->save('dashboard', $data, 10800); //e
				
		// }

		
	echo json_encode($data);
	}

	public function cache_stats(){
		//$data = $this->dash_mdl->getData();
		$data =array();
		// Safely save to cache if memcached is available
		$cached = false;
		if (isset($this->cache) && isset($this->cache->memcached) && is_object($this->cache->memcached)) {
			try {
		$cached = $this->cache->memcached->save('dashboard', $data, 13600); // MemCache for 1 hour
			} catch (Exception $e) {
				log_message('error', 'Failed to save dashboard to cache: ' . $e->getMessage());
			}
		}
		if ($cached){
			echo "Success";
			if (isset($this->cache) && isset($this->cache->memcached) && is_object($this->cache->memcached)) {
			$data = $this->cache->memcached->get('dashboard');
			}
		}
		else{
			echo "failed";
		}

	}
	public function get_dashboard()
	{
		$html_content = $this->load->view('home', NULL, TRUE);
		$response = [
			'html' => $html_content
		];

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($response));
	}

	/**
	 * Clear all cache entries for a specific facility
	 */
	private function _clearFacilityCache($facility) {
		if (!$this->cache->memcached->is_supported()) {
			return;
		}
		
		// Clear all dashboard cache for the facility
		$cache_keys = [
			'dashboard_' . $facility . '_' . date('Y-m-d'),
			'dashboard_essential_' . $facility . '_' . date('Y-m-d'),
			'dashboard_' . $facility . '_' . date('Y-m-d', strtotime('-1 day')),
			'dashboard_essential_' . $facility . '_' . date('Y-m-d', strtotime('-1 day'))
		];
		
		foreach ($cache_keys as $key) {
			$this->cache->memcached->delete($key);
		}
		
		log_message('debug', 'All cache cleared for facility: ' . $facility);
	}
	
	/**
	 * Manual cache clearing for testing/debugging
	 */
	public function clearCache() {
		$facility = $this->session->userdata('facility');
		
		if (!$facility) {
			echo json_encode(['status' => 'error', 'message' => 'No facility in session']);
			return;
		}
		
		$this->_clearFacilityCache($facility);
		echo json_encode(['status' => 'success', 'message' => 'Cache cleared for facility: ' . $facility]);
	}
	
	/**
	 * Debug method to check database tables directly
	 */
	public function debugTables() {
		// Check person_att_final table
		$att_query = "SELECT COUNT(*) as count, MAX(last_gen) as max_last_gen FROM person_att_final";
		$att_result = $this->db->query($att_query);
		$att_data = $att_result->row();
		
		// Check person_dut_final table
		$roster_query = "SELECT COUNT(*) as count, MAX(last_gen) as max_last_gen FROM person_dut_final";
		$roster_result = $this->db->query($roster_query);
		$roster_data = $roster_result->row();
		
		// Check if tables exist
		$att_table_exists = $this->db->table_exists('person_att_final');
		$roster_table_exists = $this->db->table_exists('person_dut_final');
		
		$debug_data = [
			'attendance_table_exists' => $att_table_exists,
			'attendance_count' => $att_data ? $att_data->count : 'N/A',
			'attendance_max_last_gen' => $att_data ? $att_data->max_last_gen : 'N/A',
			'roster_table_exists' => $roster_table_exists,
			'roster_count' => $roster_data ? $roster_data->count : 'N/A',
			'roster_max_last_gen' => $roster_data ? $roster_data->max_last_gen : 'N/A'
		];
		
		echo json_encode($debug_data);
	}

	



}
