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

	/**
	 * Persist dashboard filters (month/year/employee) in session for use by stats + charts.
	 */
	public function setDashboardFilters() {
		$this->output->set_content_type('application/json');

		$month = (int) $this->input->post('month');
		$year = (int) $this->input->post('year');
		$empid = trim((string) $this->input->post('empid'));
		$facility_id = trim((string) $this->input->post('facility_id'));

		$permissions = $this->session->userdata('permissions') ?: [];
		$user_role = (string) $this->session->userdata('role');
		$is_role10 = in_array('10', $permissions) || ($user_role === 'District Admin') || ($user_role === 'Regional Admin');

		// Basic validation
		if ($month < 1 || $month > 12) $month = (int) date('m');
		if ($year < 2000 || $year > ((int) date('Y') + 2)) $year = (int) date('Y');
		if ($empid === '' || $empid === 'all') $empid = '';

		$this->session->set_userdata('month', str_pad((string) $month, 2, '0', STR_PAD_LEFT));
		$this->session->set_userdata('year', (string) $year);
		$this->session->set_userdata('dashboard_empid', $empid);

		// Role 10: allow facility selection (chains staff + affects dashboard context)
		if ($is_role10) {
			if ($facility_id === '' || $facility_id === 'all') {
				$facility_id = '';
			}
			$this->session->set_userdata('dashboard_facility', $facility_id);

			// If facility is chosen, set it as current facility for consistent filtering (calendar/sessionfilters etc.)
			if (!empty($facility_id)) {
				$this->session->set_userdata('facility', $facility_id);
				// Attempt to set facility_name for UI
				$name = null;
				$q = $this->db->query("SELECT DISTINCT facility FROM ihrisdata WHERE facility_id = ? LIMIT 1", [$facility_id]);
				if ($q && $q->num_rows() > 0) {
					$row = $q->row();
					$name = isset($row->facility) ? $row->facility : null;
				}
				if ($name) {
					$this->session->set_userdata('facility_name', $name);
				}
			}
		}

		return $this->output->set_output(json_encode([
			'status' => 'success',
			'month' => str_pad((string) $month, 2, '0', STR_PAD_LEFT),
			'year' => (string) $year,
			'empid' => $empid,
			'facility_id' => $is_role10 ? $facility_id : ''
		]));
	}

	/**
	 * Select2 endpoint: search employees for dashboard Name filter (facility-scoped).
	 */
	public function searchEmployees() {
		$this->output->set_content_type('application/json');
		$term = trim((string) $this->input->get('term'));
		$facility_param = trim((string) $this->input->get('facility_id'));
		$facility = $facility_param ?: (string) $this->session->userdata('facility');
		$district_id = (string) $this->session->userdata('district_id');

		// If facility is not set (e.g., district-level users), fall back to district scope so Name filter still works.
		if (!$facility && !$district_id) {
			return $this->output->set_output(json_encode(['results' => []]));
		}

		$this->db->select("ihris_pid, CONCAT(COALESCE(surname,''),' ',COALESCE(firstname,''),' ',COALESCE(othername,'')) as fullname", false);
		$this->db->from('ihrisdata');
		if ($facility) {
			$this->db->where('facility_id', $facility);
		} else {
			$this->db->where('district_id', $district_id);
		}
		if ($term !== '') {
			$this->db->group_start();
			$this->db->like('surname', $term);
			$this->db->or_like('firstname', $term);
			$this->db->or_like('othername', $term);
			$this->db->or_like('ihris_pid', $term);
			$this->db->group_end();
		}
		$this->db->order_by('surname', 'ASC');
		$this->db->limit(20);

		$q = $this->db->get();
		$results = [];
		foreach ($q->result() as $r) {
			$results[] = ['id' => $r->ihris_pid, 'text' => trim($r->fullname)];
		}

		return $this->output->set_output(json_encode(['results' => $results]));
	}

	/**
	 * Select2 endpoint: search facilities for role-10 dashboard filter (district-scoped).
	 */
	public function searchFacilities() {
		$this->output->set_content_type('application/json');
		$term = trim((string) $this->input->get('term'));
		$district_id = (string) $this->session->userdata('district_id');
		if (!$district_id) {
			return $this->output->set_output(json_encode(['results' => []]));
		}

		$this->db->select('DISTINCT facility_id as id, facility as text', false);
		$this->db->from('ihrisdata');
		$this->db->where('district_id', $district_id);
		if ($term !== '') {
			$this->db->group_start();
			$this->db->like('facility', $term);
			$this->db->or_like('facility_id', $term);
			$this->db->group_end();
		}
		$this->db->order_by('facility', 'ASC');
		$this->db->limit(20);

		$q = $this->db->get();
		$results = [];
		foreach ($q->result() as $r) {
			$results[] = ['id' => $r->id, 'text' => $r->text];
		}
		return $this->output->set_output(json_encode(['results' => $results]));
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
	
	/**
	 * Load attendance graphs view
	 */
	public function attendance_graphs() {
		$data = array();
		$this->load->view('dashboards/attendance_graphs', $data);
	}
	
	/**
	 * Load attendance graphs view asynchronously (for AJAX)
	 */
	public function loadGraphs() {
		$data = array();
		$this->load->view('dashboards/attendance_graphs', $data);
	}
	
	/**
	 * Optimized endpoint for attendance graphs data
	 */
	public function graphsData() {
		header('Content-Type: application/json');
		
		// Set execution limits for this endpoint
		set_time_limit(30);
		ini_set('memory_limit', '128M');
		
		try {
			// Attendance per Month graph:
			// - facility mode: Avg Daily Attendance (unique staff)
			// - person mode: Days Present per month for selected person
			$year = $this->session->userdata('year') ?: date('Y');
			$month = $this->session->userdata('month') ?: date('m');
			$empid = $this->session->userdata('dashboard_empid') ?: '';
			$graph = Modules::run("reports/attendanceActualsGraphData", $year, $month, $empid);
			
			$data = array(
				'graph' => $graph
			);
			
			echo json_encode($data);
		} catch (Exception $e) {
			log_message('error', 'graphsData error: ' . $e->getMessage());
			echo json_encode(array(
				'error' => $e->getMessage(),
				'graph' => array('period' => array(), 'data' => array())
			));
		}
	}
	
	// Removed avgHoursOnly endpoint (Average Monthly Hours gauge removed from dashboard)

	



}
