<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Staff lists for dashboard Name filter: Redis → Memcached → lazy DB fill.
 */
class Dashboard_staff_cache {

	/** @var CI_Controller */
	protected $ci;

	/** @var Dropdown_cache_store */
	protected $store;

	/** @var int */
	protected $ttl;

	public function __construct()
	{
		$this->ci =& get_instance();
		$this->ci->config->load('dashboard_staff_cache', true, true);
		$this->ci->load->helper(['ihris_filter', 'mysql8_ihris']);
		$cfg = $this->ci->config->item('dashboard_staff_cache');
		$key_prefix = is_array($cfg) && !empty($cfg['cache_key_prefix'])
			? (string) $cfg['cache_key_prefix']
			: 'attend_df_';
		$this->ttl = is_array($cfg) && isset($cfg['ttl']) ? (int) $cfg['ttl'] : 86400;
		$this->ci->load->library('dropdown_cache_store', ['key_prefix' => $key_prefix], 'dash_staff_store');
		$this->store = $this->ci->dash_staff_store;
	}

	/**
	 * @param string $facility_id
	 * @param string $district_id
	 * @param string $term
	 * @param int    $limit
	 * @return array<int, array{id:string,text:string}>
	 */
	public function search($facility_id, $district_id, $term = '', $limit = 50, $district_name = '')
	{
		$facility_id = trim((string) $facility_id);
		$district_id = trim((string) $district_id);
		$district_name = trim((string) $district_name);
		$term = trim((string) $term);
		$limit = max(1, min(200, (int) $limit));

		if ($facility_id === '' && $district_id === '' && $district_name === '') {
			return [];
		}

		if ($facility_id !== '') {
			$key = 'staff_fac_' . md5($facility_id);
			$rows = $this->_get_list($key, function () use ($facility_id) {
				return $this->_load_from_db_by_facility($facility_id);
			});
		} elseif ($district_id !== '') {
			$key = 'staff_dist_' . md5($district_id);
			$rows = $this->_get_list($key, function () use ($district_id) {
				return $this->_load_from_db_by_district($district_id);
			});
		} else {
			$key = 'staff_dname_' . md5($district_name);
			$rows = $this->_get_list($key, function () use ($district_name) {
				return $this->_load_from_db_by_district_name($district_name);
			});
		}

		return $this->_filter_rows($rows, $term, $limit);
	}

	/**
	 * Staff list scoped by national dashboard filters (region, district, facility, institution, cadre).
	 *
	 * @param array<string, string> $scope
	 * @return array<int, array{id:string,text:string}>
	 */
	public function searchByScope(array $scope, $term = '', $limit = 50)
	{
		$scope = array_map(function ($v) {
			return trim((string) $v);
		}, $scope);
		$key = 'staff_scope_' . md5(json_encode($scope));
		$rows = $this->_get_list($key, function () use ($scope) {
			return $this->_load_from_db_by_scope($scope);
		});

		return $this->_filter_rows($rows, $term, $limit);
	}

	/**
	 * @param array<string, string> $scope
	 * @return array<int, array{id:string,text:string}>
	 */
	protected function _load_from_db_by_scope(array $scope)
	{
		$this->ci->load->helper('ihris_filter');
		$this->ci->db->select('ihris_pid, surname, firstname, othername', false);
		$this->ci->db->from('ihrisdata');

		if (!empty($scope['facility_id'])) {
			$this->ci->db->where('TRIM(facility_id) = ' . $this->ci->db->escape($scope['facility_id']), null, false);
		}
		if (!empty($scope['district'])) {
			$this->ci->load->library('facility_switch_cache', null, 'fsc');
			$names = $this->ci->fsc->get_district_names_in_group($scope['district']);
			if (empty($names)) {
				$names = [$scope['district']];
			}
			if (count($names) > 1) {
				$this->ci->db->where_in('district', $names);
			} else {
				$this->ci->db->where('district', $names[0]);
			}
		}
		if (!empty($scope['region']) && $this->ci->db->field_exists('region', 'ihrisdata')) {
			$this->ci->db->where('TRIM(region) = ' . $this->ci->db->escape($scope['region']), null, false);
		}
		$inst_col = ihris_institution_type_column($this->ci->db);
		if (!empty($scope['institution_type']) && $inst_col !== null) {
			$this->ci->db->where('TRIM(' . mysql8_quote_ident($inst_col) . ') = ' . $this->ci->db->escape($scope['institution_type']), null, false);
		}
		if (!empty($scope['cadre']) && $this->ci->db->field_exists('cadre', 'ihrisdata')) {
			$this->ci->db->where('TRIM(cadre) = ' . $this->ci->db->escape($scope['cadre']), null, false);
		}

		$this->_apply_active_filter();
		$this->ci->db->order_by('surname', 'ASC');
		$this->ci->db->order_by('firstname', 'ASC');
		$this->ci->db->limit(3000);

		return $this->_rows_from_query($this->ci->db->get());
	}

	/**
	 * @return array{redis:bool,memcached:bool}
	 */
	public function availability()
	{
		return $this->store->availability();
	}

	/**
	 * @param string   $key
	 * @param callable $loader
	 * @return array<int, array{id:string,text:string}>
	 */
	protected function _get_list($key, callable $loader)
	{
		$cached = $this->store->read($key, '');
		if ($this->_is_payload($cached)) {
			return $cached['staff'];
		}

		$staff = $loader();
		if (!is_array($staff)) {
			$staff = [];
		}

		$this->store->write($key, [
			'staff'        => $staff,
			'generated_at' => time(),
		], $this->ttl, '');

		return $staff;
	}

	/**
	 * @param mixed $data
	 * @return bool
	 */
	protected function _is_payload($data)
	{
		return is_array($data) && isset($data['staff']) && is_array($data['staff']);
	}

	/**
	 * @param string $facility_id
	 * @return array<int, array{id:string,text:string}>
	 */
	protected function _load_from_db_by_facility($facility_id)
	{
		$this->ci->db->select('ihris_pid, surname, firstname, othername', false);
		$this->ci->db->from('ihrisdata');
		$this->ci->db->where('TRIM(facility_id) = ' . $this->ci->db->escape($facility_id), null, false);
		$this->_apply_active_filter();
		$this->ci->db->order_by('surname', 'ASC');
		$this->ci->db->order_by('firstname', 'ASC');

		return $this->_rows_from_query($this->ci->db->get());
	}

	/**
	 * @param string $district_id
	 * @return array<int, array{id:string,text:string}>
	 */
	protected function _load_from_db_by_district($district_id)
	{
		$district_ids = $this->_district_ids_for_scope($district_id);
		if (empty($district_ids)) {
			return [];
		}

		$this->ci->db->select('ihris_pid, surname, firstname, othername', false);
		$this->ci->db->from('ihrisdata');
		$this->ci->db->where_in('district_id', $district_ids);
		$this->_apply_active_filter();
		$this->ci->db->order_by('surname', 'ASC');
		$this->ci->db->order_by('firstname', 'ASC');

		return $this->_rows_from_query($this->ci->db->get());
	}

	/**
	 * @param string $district_name
	 * @return array<int, array{id:string,text:string}>
	 */
	protected function _load_from_db_by_district_name($district_name)
	{
		$this->ci->load->library('facility_switch_cache', null, 'fsc');
		$names = $this->ci->fsc->get_district_names_in_group($district_name);
		if (empty($names)) {
			$names = [$district_name];
		}

		$this->ci->db->select('ihris_pid, surname, firstname, othername', false);
		$this->ci->db->from('ihrisdata');
		$this->ci->db->where_in('district', $names);
		$this->_apply_active_filter();
		$this->ci->db->order_by('surname', 'ASC');
		$this->ci->db->order_by('firstname', 'ASC');

		return $this->_rows_from_query($this->ci->db->get());
	}

	/**
	 * Include merged district_id aliases (e.g. KAMPALA variants) for district-scoped lists.
	 *
	 * @param string $district_id
	 * @return array<int, string>
	 */
	protected function _district_ids_for_scope($district_id)
	{
		$district_id = trim((string) $district_id);
		if ($district_id === '') {
			return [];
		}

		$ids = [$district_id];
		$this->ci->load->library('facility_switch_cache', null, 'fsc');
		$data = $this->ci->fsc->get_data(false, false);
		$resolved = $this->ci->fsc->resolve_district_id($district_id, $data);
		if ($resolved !== '' && !in_array($resolved, $ids, true)) {
			$ids[] = $resolved;
		}

		$aliases = isset($data['district_id_aliases']) && is_array($data['district_id_aliases'])
			? $data['district_id_aliases']
			: [];
		foreach ($aliases as $from => $to) {
			if ($to === $resolved || $to === $district_id || $from === $district_id || $from === $resolved) {
				if (!in_array((string) $from, $ids, true)) {
					$ids[] = (string) $from;
				}
				if (!in_array((string) $to, $ids, true)) {
					$ids[] = (string) $to;
				}
			}
		}

		return array_values(array_unique(array_filter($ids, 'strlen')));
	}

	protected function _apply_active_filter()
	{
		if ($this->ci->db->field_exists('is_active_employee', 'ihrisdata')) {
			$this->ci->db->where('(COALESCE(is_active_employee, 1) = 1)', null, false);
		}
	}

	/**
	 * @param CI_DB_result|false $q
	 * @return array<int, array{id:string,text:string}>
	 */
	protected function _rows_from_query($q)
	{
		$rows = [];
		if (!$q) {
			return $rows;
		}
		foreach ($q->result() as $r) {
			$fullname = trim(implode(' ', array_filter([$r->surname, $r->firstname, $r->othername], 'strlen')));
			$rows[] = [
				'id'   => (string) $r->ihris_pid,
				'text' => $fullname !== '' ? $fullname : (string) $r->ihris_pid,
			];
		}
		return $rows;
	}

	/**
	 * @param array<int, array{id:string,text:string}> $rows
	 * @param string $term
	 * @param int    $limit
	 * @return array<int, array{id:string,text:string}>
	 */
	protected function _filter_rows(array $rows, $term, $limit)
	{
		if ($term === '') {
			return array_slice($rows, 0, $limit);
		}

		$needle = strtolower($term);
		$out = [];
		foreach ($rows as $row) {
			$hay = strtolower((string) ($row['text'] ?? '') . ' ' . (string) ($row['id'] ?? ''));
			if (strpos($hay, $needle) !== false) {
				$out[] = $row;
				if (count($out) >= $limit) {
					break;
				}
			}
		}
		return $out;
	}
}
