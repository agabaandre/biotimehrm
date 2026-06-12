<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * District / facility dropdown cache: Redis → Memcached → file → database fallback.
 */
class Facility_switch_cache {

	/** @var CI_Controller */
	protected $ci;

	/** @var string */
	protected $cache_file;

	/** @var int */
	protected $max_age_seconds;

	/** @var string */
	protected $timezone;

	/** @var string */
	protected $cache_key;

	/** @var Dropdown_cache_store */
	protected $store;

	public function __construct()
	{
		$this->ci =& get_instance();
		$this->ci->config->load('facility_switch_cache', true, true);
		$cfg = $this->ci->config->item('facility_switch_cache');
		if (function_exists('is_education_deployment') && is_education_deployment()) {
			$this->cache_file = APPPATH . 'cache/facility_switch_education.json';
			$this->cache_key = 'facility_switch_education';
		} else {
			$this->cache_file = isset($cfg['cache_file'])
				? $cfg['cache_file']
				: APPPATH . 'cache/facility_switch_ihris.json';
			$this->cache_key = 'facility_switch_ihris';
		}
		$this->max_age_seconds = isset($cfg['max_age_seconds']) ? (int) $cfg['max_age_seconds'] : 604800;
		$this->timezone = isset($cfg['timezone']) ? $cfg['timezone'] : 'Africa/Kampala';
		$key_prefix = isset($cfg['cache_key_prefix']) ? $cfg['cache_key_prefix'] : 'attend_df_';
		$this->ci->load->library('dropdown_cache_store', ['key_prefix' => $key_prefix], 'dropdown_cache');
		$this->store = $this->ci->dropdown_cache;
	}

	/**
	 * Rebuild cache from ihrisdata and write JSON file.
	 *
	 * @return array{status:string,districts:int,facilities:int,generated_at:string,cache_file:string}
	 */
	public function rebuild()
	{
		$payload = $this->_build_payload_from_db();
		$this->_persist_payload($payload);

		$facility_count = 0;
		foreach ($payload['facilities_by_district'] as $rows) {
			$facility_count += count($rows);
		}

		return [
			'status'        => 'success',
			'districts'     => count($payload['districts']),
			'facilities'    => $facility_count,
			'generated_at'  => $payload['generated_at_iso'],
			'cache_file'    => $this->cache_file,
			'cache_key'     => $this->cache_key,
			'availability'  => $this->store->availability(),
		];
	}

	protected function _build_payload_from_db()
	{
		if (function_exists('is_education_deployment') && is_education_deployment()) {
			return $this->_build_education_payload();
		}

		return $this->_build_moh_payload();
	}

	protected function _build_moh_payload()
	{
		$tz = new DateTimeZone($this->timezone);
		$now = new DateTime('now', $tz);

		$this->ci->load->helper('mysql8_ihris');
		$district_rows = $this->ci->db->query(
			"SELECT TRIM(district_id) AS district_id, TRIM(district) AS district
			 FROM ihrisdata
			 WHERE " . mysql8_nonempty_sql('district_id') . " AND " . mysql8_nonempty_sql('district') . "
			 GROUP BY TRIM(district_id), TRIM(district)
			 ORDER BY TRIM(district) ASC"
		)->result();

		$facility_rows = $this->ci->db->query(
			"SELECT TRIM(district_id) AS district_id, TRIM(facility_id) AS facility_id, TRIM(facility) AS facility
			 FROM ihrisdata
			 WHERE " . mysql8_nonempty_sql('district_id') . "
			   AND " . mysql8_nonempty_sql('facility_id') . "
			   AND " . mysql8_nonempty_sql('facility') . "
			 GROUP BY TRIM(district_id), TRIM(facility_id), TRIM(facility)
			 ORDER BY TRIM(district_id) ASC, TRIM(facility) ASC"
		)->result();

		$districts = [];
		$seen_districts = [];
		foreach ($district_rows as $row) {
			$id = trim((string) $row->district_id);
			if ($id === '' || isset($seen_districts[$id])) {
				continue;
			}
			$seen_districts[$id] = true;
			$districts[] = [
				'district_id' => $id,
				'district'    => trim((string) $row->district),
			];
		}

		$raw_facilities_by_district = [];
		$district_names = [];
		foreach ($districts as $d) {
			$district_names[$d['district_id']] = $d['district'];
		}

		$seen_facilities = [];
		foreach ($facility_rows as $row) {
			$district_id = trim((string) $row->district_id);
			$facility_id = trim((string) $row->facility_id);
			if ($district_id === '' || $facility_id === '') {
				continue;
			}
			$dedupe = $district_id . '|' . $facility_id;
			if (isset($seen_facilities[$dedupe])) {
				continue;
			}
			$seen_facilities[$dedupe] = true;
			if (!isset($raw_facilities_by_district[$district_id])) {
				$raw_facilities_by_district[$district_id] = [];
			}
			$raw_facilities_by_district[$district_id][] = [
				'facility_id' => $facility_id,
				'facility'    => trim((string) $row->facility),
			];
			if (!isset($district_names[$district_id])) {
				$district_names[$district_id] = trim((string) $row->district);
			}
		}

		$merged = $this->_merge_district_groups($districts, $raw_facilities_by_district, $district_names);

		return [
			'generated_at'           => $now->getTimestamp(),
			'generated_at_iso'       => $now->format('Y-m-d H:i:s'),
			'timezone'               => $this->timezone,
			'districts'              => $merged['districts'],
			'facilities_by_district' => $merged['facilities_by_district'],
			'district_id_aliases'    => $merged['district_id_aliases'],
		];
	}

	protected function _build_education_payload()
	{
		$tz = new DateTimeZone($this->timezone);
		$now = new DateTime('now', $tz);

		$district_rows = $this->ci->db->query(
			"SELECT CAST(id AS CHAR) AS district_id, TRIM(name) AS district
			 FROM employee_districts
			 WHERE TRIM(name) != ''
			 ORDER BY TRIM(name) ASC"
		)->result();

		$districts = [];
		foreach ($district_rows as $row) {
			$districts[] = [
				'district_id' => trim((string) $row->district_id),
				'district'    => trim((string) $row->district),
			];
		}

		$facilities_by_district = [];
		$facility_rows = $this->ci->db->query(
			"SELECT CAST(district_id AS CHAR) AS district_id,
			        TRIM(facility_id) AS facility_id,
			        TRIM(facility) AS facility
			 FROM employee_facility
			 WHERE district_id IS NOT NULL
			   AND TRIM(facility_id) != ''
			   AND TRIM(facility) != ''
			 ORDER BY district_id ASC, TRIM(facility) ASC"
		)->result();

		foreach ($facility_rows as $row) {
			$district_id = trim((string) $row->district_id);
			if ($district_id === '') {
				continue;
			}
			if (!isset($facilities_by_district[$district_id])) {
				$facilities_by_district[$district_id] = [];
			}
			$facilities_by_district[$district_id][] = [
				'facility_id' => trim((string) $row->facility_id),
				'facility'    => trim((string) $row->facility),
			];
		}

		return [
			'generated_at'           => $now->getTimestamp(),
			'generated_at_iso'       => $now->format('Y-m-d H:i:s'),
			'timezone'               => $this->timezone,
			'districts'              => $districts,
			'facilities_by_district' => $facilities_by_district,
			'district_id_aliases'    => [],
		];
	}

	protected function _persist_payload(array $payload)
	{
		return $this->store->write(
			$this->cache_key,
			$payload,
			$this->max_age_seconds,
			$this->cache_file
		);
	}

	protected function _is_valid_payload($data)
	{
		return is_array($data)
			&& isset($data['districts'], $data['facilities_by_district'])
			&& is_array($data['districts'])
			&& is_array($data['facilities_by_district']);
	}

	/**
	 * District rows for Switch Facility dropdown (objects like DB query).
	 *
	 * @return array<int, object>
	 */
	public function get_districts()
	{
		$data = $this->get_data();
		$out = [];
		foreach ($data['districts'] as $row) {
			$o = new stdClass();
			$o->district_id = $row['district_id'];
			$o->district = $row['district'];
			$out[] = $o;
		}
		return $out;
	}

	/**
	 * All district names in the same group (e.g. KAMPALA + KAMPALA City).
	 *
	 * @param string|null $selected_district_name
	 * @return array<int, string>
	 */
	public function get_district_names_in_group($selected_district_name)
	{
		$selected = trim((string) $selected_district_name);
		if ($selected === '') {
			return [];
		}
		foreach ($this->_district_groups_map() as $names) {
			foreach ($names as $name) {
				if (strcasecmp(trim((string) $name), $selected) === 0) {
					return array_values(array_unique($names));
				}
			}
		}
		return [$selected];
	}

	/**
	 * @return array<string, array<int, string>>
	 */
	protected function _district_groups_map()
	{
		$data = $this->get_data();
		$map = [];
		foreach ($data['districts'] as $row) {
			$district_id = isset($row['district_id']) ? $row['district_id'] : '';
			$district_name = isset($row['district']) ? $row['district'] : '';
			$gk = $this->normalize_district_group_key($district_id, $district_name);
			if ($gk === '') {
				continue;
			}
			if (!isset($map[$gk])) {
				$map[$gk] = [];
			}
			$name = trim((string) $district_name);
			if ($name !== '' && !in_array($name, $map[$gk], true)) {
				$map[$gk][] = $name;
			}
		}
		return $map;
	}

	/**
	 * Facilities in a district; optionally restrict to one facility_id (session user).
	 *
	 * @param string      $district_id
	 * @param string|null $only_facility_id
	 * @return array<int, object>
	 */
	public function get_facilities_for_district($district_id, $only_facility_id = null)
	{
		$data = $this->get_data();
		$district_id = $this->resolve_district_id($district_id, $data);
		$list = isset($data['facilities_by_district'][$district_id])
			? $data['facilities_by_district'][$district_id]
			: [];

		if (empty($list)) {
			$list = $this->_fetch_facilities_for_district_from_db($district_id, $data);
		}

		$out = [];
		foreach ($list as $row) {
			if ($only_facility_id !== null && $only_facility_id !== '' && $row['facility_id'] !== $only_facility_id) {
				continue;
			}
			$o = new stdClass();
			$o->facility_id = $row['facility_id'];
			$o->facility = $row['facility'];
			$out[] = $o;
		}
		return $out;
	}

	/**
	 * @param bool $auto_rebuild_if_missing
	 * @param bool $auto_rebuild_if_stale
	 * @return array
	 */
	public function get_data($auto_rebuild_if_missing = true, $auto_rebuild_if_stale = true)
	{
		$data = $this->store->read($this->cache_key, $this->cache_file);

		if (!$this->_is_valid_payload($data)) {
			if ($auto_rebuild_if_missing) {
				$this->rebuild();
				$data = $this->store->read($this->cache_key, $this->cache_file);
			}
			if (!$this->_is_valid_payload($data)) {
				$data = $this->_build_payload_from_db();
			}
		}

		if (!$this->_is_valid_payload($data)) {
			return $this->_empty_payload();
		}

		if ($auto_rebuild_if_stale && $this->is_stale($data)) {
			$this->rebuild();
			$fresh = $this->store->read($this->cache_key, $this->cache_file);
			if ($this->_is_valid_payload($fresh)) {
				$data = $fresh;
			} else {
				$data = $this->_build_payload_from_db();
			}
		}

		return $this->_is_valid_payload($data) ? $data : $this->_empty_payload();
	}

	/**
	 * @param array|null $data
	 */
	public function is_stale($data = null)
	{
		if ($data === null) {
			$data = $this->store->read($this->cache_key, $this->cache_file);
		}
		if (!is_array($data) || empty($data['generated_at'])) {
			return true;
		}
		return (time() - (int) $data['generated_at']) > $this->max_age_seconds;
	}

	/**
	 * @return array{status:string,generated_at?:string,is_stale:bool,cache_file:string}
	 */
	public function status()
	{
		$data = $this->store->read($this->cache_key, $this->cache_file);
		if (!$this->_is_valid_payload($data)) {
			return [
				'status'       => 'missing',
				'is_stale'     => true,
				'cache_file'   => $this->cache_file,
				'cache_key'    => $this->cache_key,
				'read_source'  => null,
				'availability' => $this->store->availability(),
			];
		}

		return [
			'status'       => 'ok',
			'generated_at' => isset($data['generated_at_iso']) ? $data['generated_at_iso'] : null,
			'is_stale'     => $this->is_stale($data),
			'cache_file'   => $this->cache_file,
			'cache_key'    => $this->cache_key,
			'read_source'  => $this->store->read_source($this->cache_key, $this->cache_file),
			'availability' => $this->store->availability(),
		];
	}

	/**
	 * Invalidate all cache tiers and rebuild from the database.
	 *
	 * @return array
	 */
	public function invalidate()
	{
		$this->store->delete($this->cache_key, $this->cache_file);
		return $this->rebuild();
	}

	/**
	 * DB fallback for a single district's facility dropdown (when cache slice is empty).
	 *
	 * @param string $district_id
	 * @param array|null $data
	 * @return array<int, array{facility_id:string,facility:string}>
	 */
	protected function _fetch_facilities_for_district_from_db($district_id, $data = null)
	{
		$district_id = $this->resolve_district_id($district_id, $data);
		if ($district_id === '') {
			return [];
		}

		if (function_exists('is_education_deployment') && is_education_deployment()) {
			$rows = $this->ci->db->query(
				"SELECT TRIM(facility_id) AS facility_id, TRIM(facility) AS facility
				 FROM employee_facility
				 WHERE CAST(district_id AS CHAR) = ?
				   AND TRIM(facility_id) != ''
				   AND TRIM(facility) != ''
				 ORDER BY TRIM(facility) ASC",
				[$district_id]
			)->result();
		} else {
			$this->ci->load->helper('mysql8_ihris');
			$rows = $this->ci->db->query(
				"SELECT TRIM(facility_id) AS facility_id, TRIM(facility) AS facility
				 FROM ihrisdata
				 WHERE TRIM(district_id) = ?
				   AND " . mysql8_nonempty_sql('facility_id') . "
				   AND " . mysql8_nonempty_sql('facility') . "
				 GROUP BY TRIM(facility_id), TRIM(facility)
				 ORDER BY TRIM(facility) ASC",
				[$district_id]
			)->result();
		}

		$list = [];
		foreach ($rows as $row) {
			$list[] = [
				'facility_id' => trim((string) $row->facility_id),
				'facility'    => trim((string) $row->facility),
			];
		}

		return $list;
	}

	protected function _empty_payload()
	{
		return [
			'generated_at'           => 0,
			'generated_at_iso'       => null,
			'districts'              => [],
			'facilities_by_district' => [],
			'district_id_aliases'    => [],
		];
	}

	/**
	 * Normalize district label so "KAMPALA" and "KAMPALA City" merge into one group.
	 *
	 * @param string $district_id
	 * @param string $district_name
	 * @return string
	 */
	public function normalize_district_group_key($district_id, $district_name = '')
	{
		$label = trim((string) $district_name);
		if ($label === '') {
			$label = trim((string) $district_id);
		}
		$key = strtolower($label);
		$key = preg_replace('/\s+city\s*$/i', '', $key);
		$key = preg_replace('/\s+/', ' ', $key);
		return $key;
	}

	/**
	 * Resolve district_id for facility lookup (alias or direct key).
	 *
	 * @param string $district_id
	 * @param array|null $data
	 * @return string
	 */
	public function resolve_district_id($district_id, $data = null)
	{
		$district_id = trim((string) $district_id);
		if ($district_id === '') {
			return '';
		}
		if ($data === null) {
			$data = $this->get_data();
		}
		if (isset($data['facilities_by_district'][$district_id])) {
			return $district_id;
		}
		$aliases = isset($data['district_id_aliases']) && is_array($data['district_id_aliases'])
			? $data['district_id_aliases']
			: [];
		while (isset($aliases[$district_id]) && $aliases[$district_id] !== $district_id) {
			$district_id = $aliases[$district_id];
		}
		return $district_id;
	}

	/**
	 * Merge ihrisdata district_id variants that share the same normalized district name.
	 *
	 * @param array<int, array{district_id:string,district:string}> $districts
	 * @param array<string, array<int, array{facility_id:string,facility:string}>> $raw_facilities_by_district
	 * @param array<string, string> $district_names
	 * @return array{districts:array,facilities_by_district:array,district_id_aliases:array,facility_count:int}
	 */
	protected function _merge_district_groups(array $districts, array $raw_facilities_by_district, array $district_names)
	{
		$groups = [];
		foreach ($district_names as $district_id => $district_name) {
			$group_key = $this->normalize_district_group_key($district_id, $district_name);
			if ($group_key === '') {
				continue;
			}
			if (!isset($groups[$group_key])) {
				$groups[$group_key] = [
					'members' => [],
				];
			}
			$count = isset($raw_facilities_by_district[$district_id])
				? count($raw_facilities_by_district[$district_id])
				: 0;
			$groups[$group_key]['members'][$district_id] = [
				'district_id'   => $district_id,
				'district'      => $district_name,
				'facility_count' => $count,
			];
		}

		$facilities_by_district = [];
		$district_id_aliases = [];
		$facility_count = 0;
		$seen_facilities = [];

		foreach ($groups as $group_key => $group) {
			$members = $group['members'];
			if (empty($members)) {
				continue;
			}

			$canonical_id = null;
			$best_count = -1;
			foreach ($members as $member) {
				if ($member['facility_count'] > $best_count) {
					$best_count = $member['facility_count'];
					$canonical_id = $member['district_id'];
				}
			}
			if ($canonical_id === null) {
				$first = reset($members);
				$canonical_id = $first['district_id'];
			}

			$bucket = [];
			foreach ($members as $member_id => $member) {
				$rows = isset($raw_facilities_by_district[$member_id])
					? $raw_facilities_by_district[$member_id]
					: [];
				foreach ($rows as $row) {
					$fid = $row['facility_id'];
					if ($fid === '' || isset($seen_facilities[$group_key . '|' . $fid])) {
						continue;
					}
					$seen_facilities[$group_key . '|' . $fid] = true;
					$bucket[] = $row;
					$facility_count++;
				}
			}
			usort($bucket, function ($a, $b) {
				return strcasecmp($a['facility'], $b['facility']);
			});

			// Same merged facility list for every district_id in this group (KAMPALA + KAMPALA City).
			foreach ($members as $member_id => $member) {
				$facilities_by_district[$member_id] = $bucket;
				if ($member_id !== $canonical_id) {
					$district_id_aliases[$member_id] = $canonical_id;
				}
			}
		}

		// Districts with no facilities still appear in the dropdown.
		foreach ($districts as $d) {
			$id = $d['district_id'];
			if (!isset($facilities_by_district[$id])) {
				$facilities_by_district[$id] = [];
			}
		}

		usort($districts, function ($a, $b) {
			return strcasecmp($a['district'], $b['district']);
		});

		return [
			'districts'              => $districts,
			'facilities_by_district' => $facilities_by_district,
			'district_id_aliases'    => $district_id_aliases,
			'facility_count'         => $facility_count,
		];
	}
}
