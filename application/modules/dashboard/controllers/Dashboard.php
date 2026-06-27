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
	public function dashboardData()
	{
		$this->output->set_content_type('application/json');

		if (!$this->session->userdata('isLoggedIn')) {
			return $this->output->set_status_header(401)->set_output(json_encode(['error' => 'unauthorized']));
		}

		$this->load->library('dashboard_cache_store', null, 'dash_cache');
		$this->config->load('dashboard_cache', true, true);
		$cfg = $this->config->item('dashboard_cache');
		$stats_ttl = is_array($cfg) && isset($cfg['stats_ttl']) ? (int) $cfg['stats_ttl'] : 60;

		$facility = (string) $this->session->userdata('facility');
		$year = (string) ($this->session->userdata('year') ?: date('Y'));
		$months = $this->dash_mdl->dashboardMonthsFromSession();
		$empid = (string) ($this->session->userdata('dashboard_empid') ?: '');
		$ver = $this->dash_cache->facilityVersion($facility);
		$cache_key = 'stats_' . md5(implode('|', [$facility, $year, implode(',', $months), $empid, $ver]));

		$cached = $this->dash_cache->read($cache_key);
		if (is_array($cached)) {
			$cached['cached'] = true;
			return $this->output->set_output(json_encode($cached));
		}

		$data = $this->dash_mdl->stats();
		$data['cached'] = false;
		$data['generated_at'] = date('c');
		$data['cache_layer'] = $this->dash_cache->availability();
		$this->dash_cache->write($cache_key, $data, $stats_ttl);

		return $this->output->set_output(json_encode($data));
	}

	/**
	 * Near-real-time attendance pulse (Redis/Memcached backed, short TTL).
	 */
	public function dashboardLivePulse()
	{
		$this->output->set_content_type('application/json');

		if (!$this->session->userdata('isLoggedIn')) {
			return $this->output->set_status_header(401)->set_output(json_encode([
				'live' => false,
				'error' => 'unauthorized',
			]));
		}

		$facility = trim((string) (
			$this->session->userdata('dashboard_facility')
			?: $this->session->userdata('facility')
			?: $this->session->userdata('facility_id')
			?: ''
		));
		if ($facility === '') {
			return $this->output->set_output(json_encode([
				'live' => false,
				'error' => 'no_facility',
				'message' => 'No facility selected in your session.',
			]));
		}

		$year = (string) ($this->session->userdata('year') ?: date('Y'));
		$month = (string) ($this->session->userdata('month') ?: date('m'));
		$empid = (string) ($this->session->userdata('dashboard_empid') ?: '');
		$current_month = date('Y-m');
		$selected_month = substr($year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT), 0, 7);

		if ($selected_month !== $current_month) {
			return $this->output->set_output(json_encode([
				'live' => false,
				'reason' => 'historical',
				'generated_at' => date('c'),
			]));
		}

		try {
			$data = $this->dash_mdl->livePulse();
			if (!is_array($data)) {
				$data = ['live' => false, 'error' => 'pulse_failed'];
			}

			$data['cached'] = false;
			$data['cache_layer'] = ['redis' => false, 'memcached' => false];

			try {
				$this->load->library('dashboard_cache_store', null, 'dash_cache');
				$this->config->load('dashboard_cache', true, true);
				$cfg = $this->config->item('dashboard_cache');
				$live_ttl = is_array($cfg) && isset($cfg['live_ttl']) ? (int) $cfg['live_ttl'] : 10;

				$ver = $this->dash_cache->facilityVersion($facility);
				$cache_key = 'live_' . md5(implode('|', [$facility, $empid, $ver, date('Y-m-d')]));
				$cached = $this->dash_cache->read($cache_key);
				if (is_array($cached)) {
					$cached['cached'] = true;
					return $this->output->set_output(json_encode($cached, JSON_UNESCAPED_UNICODE));
				}

				$data['cache_layer'] = $this->dash_cache->availability();
				$this->dash_cache->write($cache_key, $data, $live_ttl);
			} catch (Throwable $cacheError) {
				log_message('error', 'dashboardLivePulse cache: ' . $cacheError->getMessage());
			}

			return $this->output->set_output(json_encode($data, JSON_UNESCAPED_UNICODE));
		} catch (Throwable $e) {
			log_message('error', 'dashboardLivePulse: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
			return $this->output->set_status_header(500)->set_output(json_encode([
				'live' => false,
				'error' => 'server_error',
				'message' => 'Could not load live attendance feed.',
			]));
		}
	}

	/**
	 * Set only the year in session; month is set to current month.
	 * Used by side nav "Year" select to switch period without changing month or other filters.
	 */
	public function setSessionYear() {
		$this->output->set_content_type('application/json');
		$year = (int) $this->input->post('year');
		$current_year = (int) date('Y');
		if ($year < 2000 || $year > ($current_year + 2)) {
			$year = $current_year;
		}
		$month = (int) date('m');
		$this->session->set_userdata('month', str_pad((string) $month, 2, '0', STR_PAD_LEFT));
		$this->session->set_userdata('year', (string) $year);
		return $this->output->set_output(json_encode([
			'status' => 'success',
			'month'  => str_pad((string) $month, 2, '0', STR_PAD_LEFT),
			'year'   => (string) $year
		]));
	}

	/**
	 * Persist dashboard filters (month/year/employee) in session for use by stats + charts.
	 */
	public function setDashboardFilters() {
		$this->output->set_content_type('application/json');

		$month = (int) $this->input->post('month');
		$months_raw = $this->input->post('months');
		$year = (int) $this->input->post('year');
		$empid = trim((string) $this->input->post('empid'));
		$facility_id = trim((string) $this->input->post('facility_id'));
		$region = trim((string) $this->input->post('region'));
		$district = trim((string) $this->input->post('district'));
		$institution_type = trim((string) $this->input->post('institution_type'));
		$cadre = trim((string) $this->input->post('cadre'));
		$national_facility = trim((string) $this->input->post('national_facility_id'));

		$months = [];
		if (is_array($months_raw)) {
			foreach ($months_raw as $m) {
				$m = (int) $m;
				if ($m >= 1 && $m <= 12) {
					$months[] = $m;
				}
			}
		} elseif (is_string($months_raw) && trim($months_raw) !== '') {
			foreach (explode(',', $months_raw) as $m) {
				$m = (int) trim($m);
				if ($m >= 1 && $m <= 12) {
					$months[] = $m;
				}
			}
		}
		if (empty($months)) {
			$months = $this->dash_mdl->dashboardDefaultFyMonthNumbers();
		}
		$months = array_values(array_unique($months));
		sort($months);
		$month = $months[count($months) - 1];

		$permissions = $this->session->userdata('permissions') ?: [];
		$user_role = (string) $this->session->userdata('role');
		$is_role10 = in_array('10', $permissions) || ($user_role === 'District Admin') || ($user_role === 'Regional Admin');

		if ($month < 1 || $month > 12) {
			$month = (int) date('m');
		}
		if ($year < 2000 || $year > ((int) date('Y') + 2)) {
			$year = (int) date('Y');
		}
		if ($empid === '' || $empid === 'all') {
			$empid = '';
		}

		$month_strings = array_map(function ($m) {
			return str_pad((string) $m, 2, '0', STR_PAD_LEFT);
		}, $months);

		$this->session->set_userdata('dashboard_months', $month_strings);
		$this->session->set_userdata('month', str_pad((string) $month, 2, '0', STR_PAD_LEFT));
		$this->session->set_userdata('year', (string) $year);
		$this->session->set_userdata('dashboard_empid', $empid);
		$this->session->set_userdata('dashboard_region', $region);
		$this->session->set_userdata('dashboard_district', $district);
		$this->session->set_userdata('dashboard_institution_type', $institution_type);
		$this->session->set_userdata('dashboard_cadre', $cadre);
		$this->session->set_userdata('dashboard_facility_filter', $national_facility);

		if ($is_role10) {
			if ($facility_id === '' || $facility_id === 'all') {
				$facility_id = '';
			}
			$this->session->set_userdata('dashboard_facility', $facility_id);

			if (!empty($facility_id)) {
				$this->session->set_userdata('facility', $facility_id);
				$q = $this->db->query("SELECT DISTINCT facility FROM ihrisdata WHERE facility_id = ? LIMIT 1", [$facility_id]);
				if ($q && $q->num_rows() > 0) {
					$row = $q->row();
					if (!empty($row->facility)) {
						$this->session->set_userdata('facility_name', $row->facility);
					}
				}
			}
		}

		return $this->output->set_output(json_encode([
			'status' => 'success',
			'month' => str_pad((string) $month, 2, '0', STR_PAD_LEFT),
			'months' => $month_strings,
			'period_label' => $this->dash_mdl->dashboardPeriodLabel($year, $months),
			'year' => (string) $year,
			'empid' => $empid,
			'facility_id' => $is_role10 ? $facility_id : '',
			'region' => $region,
			'district' => $district,
			'institution_type' => $institution_type,
			'cadre' => $cadre,
			'national_facility_id' => $national_facility,
		]));
	}

	/**
	 * Cached filter dropdown options (regions, districts, institution types, cadres).
	 */
	public function filterOptions() {
		$this->output->set_content_type('application/json');
		if (!$this->session->userdata('isLoggedIn')) {
			return $this->output->set_status_header(401)->set_output(json_encode(['error' => 'unauthorized']));
		}
		$this->load->library('ihris_filter_cache', null, 'ihris_filters');
		$opts = $this->ihris_filters->get_options();
		return $this->output->set_output(json_encode($opts));
	}

	/**
	 * National attendance / absenteeism rates + chart series (Redis-backed).
	 */
	public function nationalAnalytics() {
		$this->output->set_content_type('application/json');
		if (!$this->session->userdata('isLoggedIn')) {
			return $this->output->set_status_header(401)->set_output(json_encode(['error' => 'unauthorized']));
		}

		$scope = $this->dash_mdl->dashboardScopeFromSession();
		$year = (int) ($this->session->userdata('year') ?: date('Y'));
		$months = $this->dash_mdl->dashboardMonthsFromSession();
		$empid = (string) ($this->session->userdata('dashboard_empid') ?: '');

		$national = $this->dash_mdl->nationalAttendanceRates($scope, $year, $months);
		$charts = $this->dash_mdl->analyticsCharts($scope, $year, $months, $empid);

		return $this->output->set_output(json_encode([
			'national' => $national,
			'charts' => $charts,
			'scope' => $scope,
		]));
	}

	/**
	 * Select2 endpoint: search employees for dashboard Name filter (facility- or district-scoped).
	 */
	public function searchEmployees() {
		$this->output->set_content_type('application/json');

		if (!$this->session->userdata('isLoggedIn')) {
			return $this->output->set_status_header(401)->set_output(json_encode(['results' => []]));
		}

		$term = trim((string) $this->input->get('term'));
		$facility_param = trim((string) $this->input->get('facility_id'));
		$district_param = trim((string) $this->input->get('district'));

		$facility = $this->_dashboardFacility($facility_param);
		if ($facility === '') {
			$facility = trim((string) $this->session->userdata('dashboard_facility_filter'));
		}
		if ($facility === '') {
			$facility = trim((string) ($this->session->userdata('facility') ?: ''));
		}

		$district_id = $this->_dashboardDistrictId();
		$district_name = $district_param !== '' ? $district_param : $this->_dashboardDistrictName();

		$scope = $this->dash_mdl->dashboardScopeFromSession();
		if ($district_param !== '') {
			$scope['district'] = $district_param;
		}
		if ($facility_param !== '') {
			$scope['facility_id'] = $facility_param;
			$facility = $facility_param;
		} elseif ($facility !== '') {
			$scope['facility_id'] = $facility;
		}

		$this->load->library('dashboard_staff_cache', null, 'dash_staff');

		if ($facility !== '') {
			$results = $this->dash_staff->search($facility, '', $term, 50, '');
		} elseif ($district_id !== '' || $district_name !== '') {
			$results = $this->dash_staff->search('', $district_id, $term, 50, $district_name);
		} elseif ($this->_scopeHasStaffFilters($scope)) {
			$results = $this->dash_staff->searchByScope($scope, $term, 50);
		} else {
			$results = [];
		}

		return $this->output->set_output(json_encode(['results' => $results]));
	}

	private function _scopeHasStaffFilters(array $scope)
	{
		foreach (['region', 'district', 'facility_id', 'institution_type', 'cadre'] as $key) {
			if (!empty($scope[$key])) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Select2 endpoint: search facilities for role-10 dashboard filter (district-scoped).
	 */
	public function searchFacilities() {
		$this->output->set_content_type('application/json');

		if (!$this->session->userdata('isLoggedIn')) {
			return $this->output->set_status_header(401)->set_output(json_encode(['results' => []]));
		}

		$term = trim((string) $this->input->get('term'));
		$district_name = trim((string) $this->input->get('district'));
		if ($district_name === '') {
			$district_name = $this->_dashboardDistrictName();
		}
		$district_id = $this->_dashboardDistrictId();

		$this->load->library('facility_switch_cache', null, 'fsc');
		$facilities = [];

		if ($district_name !== '') {
			foreach ($this->fsc->get_data(false, false)['districts'] as $drow) {
				if (strcasecmp(trim((string) $drow['district']), $district_name) === 0) {
					$district_id = $drow['district_id'];
					break;
				}
			}
		}

		if ($district_id !== '') {
			$facilities = $this->fsc->get_facilities_for_district($district_id);
		} elseif ($district_name !== '') {
			$names = $this->fsc->get_district_names_in_group($district_name);
			if (empty($names)) {
				$names = [$district_name];
			}
			$placeholders = implode(',', array_fill(0, count($names), '?'));
			$rows = $this->db->query(
				"SELECT TRIM(facility_id) AS facility_id, TRIM(facility) AS facility
				 FROM ihrisdata
				 WHERE TRIM(facility) != '' AND TRIM(facility_id) != ''
				   AND TRIM(district) IN ({$placeholders})
				 GROUP BY TRIM(facility_id), TRIM(facility)
				 ORDER BY TRIM(facility) ASC",
				$names
			)->result();
			foreach ($rows as $r) {
				$o = new stdClass();
				$o->facility_id = $r->facility_id;
				$o->facility = $r->facility;
				$facilities[] = $o;
			}
		} else {
			return $this->output->set_output(json_encode(['results' => []]));
		}

		$needle = strtolower($term);
		$results = [];
		foreach ($facilities as $f) {
			$id = (string) $f->facility_id;
			$text = (string) $f->facility;
			if ($needle !== '' && strpos(strtolower($text . ' ' . $id), $needle) === false) {
				continue;
			}
			$results[] = ['id' => $id, 'text' => $text];
			if (count($results) >= 20) {
				break;
			}
		}

		return $this->output->set_output(json_encode(['results' => $results]));
	}

	/**
	 * @param string $override GET facility_id from role-10 filter
	 */
	private function _dashboardFacility($override = '')
	{
		$override = trim((string) $override);
		if ($override !== '') {
			return $override;
		}

		foreach (['dashboard_facility', 'facility', 'facility_id'] as $key) {
			$value = $this->session->userdata($key);
			if ($value !== null && $value !== false && trim((string) $value) !== '') {
				return trim((string) $value);
			}
		}

		if (isset($_SESSION['facility']) && trim((string) $_SESSION['facility']) !== '') {
			return trim((string) $_SESSION['facility']);
		}

		return '';
	}

	private function _dashboardDistrictId()
	{
		$value = $this->session->userdata('district_id');
		if ($value !== null && $value !== false && trim((string) $value) !== '') {
			return trim((string) $value);
		}

		if (isset($_SESSION['district_id']) && trim((string) $_SESSION['district_id']) !== '') {
			return trim((string) $_SESSION['district_id']);
		}

		return '';
	}

	private function _dashboardDistrictName()
	{
		foreach (['dashboard_district', 'district'] as $key) {
			$value = $this->session->userdata($key);
			if ($value !== null && $value !== false && trim((string) $value) !== '') {
				return trim((string) $value);
			}
		}
		return '';
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
		$this->load->library('dashboard_cache_store', null, 'dash_cache');
		$this->dash_cache->invalidateFacility($facility);
		log_message('debug', 'Dashboard cache invalidated for facility: ' . $facility);
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

		if (!$this->session->userdata('isLoggedIn')) {
			echo json_encode(['error' => 'unauthorized']);
			return;
		}

		set_time_limit(60);
		ini_set('memory_limit', '256M');

		try {
			$scope = $this->dash_mdl->dashboardScopeFromSession();
			$year = (int) ($this->session->userdata('year') ?: date('Y'));
			$months = $this->dash_mdl->dashboardMonthsFromSession();
			$empid = (string) ($this->session->userdata('dashboard_empid') ?: '');

			$charts = $this->dash_mdl->analyticsCharts($scope, $year, $months, $empid);
			$graph = [
				'period' => $charts['period'],
				'data'   => $charts['avg_daily_workers'],
				'meta'   => [
					'year'         => $year,
					'months'       => $charts['months'] ?? [],
					'period_label' => $charts['period_label'] ?? '',
					'mode'         => ($empid !== '') ? 'person' : 'scoped',
					'empid'        => $empid,
				],
			];

			echo json_encode([
				'graph'                  => $graph,
				'attendance_rate'        => $charts['attendance_rate'],
				'absenteeism_rate'       => $charts['absenteeism_rate'],
				'schedule_present'       => $charts['schedule_present'] ?? [],
				'schedule_off'           => $charts['schedule_off'] ?? [],
				'schedule_leave'         => $charts['schedule_leave'] ?? [],
				'schedule_official'      => $charts['schedule_official'] ?? [],
				'schedule_holiday'       => $charts['schedule_holiday'] ?? [],
				'schedule_unaccounted'   => $charts['schedule_unaccounted'] ?? [],
				'fy_label'               => $charts['period_label'] ?? ($charts['fy_label'] ?? ''),
				'period_label'           => $charts['period_label'] ?? '',
				'cached'                 => !empty($charts['cached']),
			]);
		} catch (Exception $e) {
			log_message('error', 'graphsData error: ' . $e->getMessage());
			echo json_encode([
				'error' => $e->getMessage(),
				'graph' => ['period' => [], 'data' => []],
			]);
		}
	}
	
	// Removed avgHoursOnly endpoint (Average Monthly Hours gauge removed from dashboard)

	



}
