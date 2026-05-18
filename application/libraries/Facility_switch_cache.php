<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * File cache of districts and facilities from ihrisdata for Switch Facility UI.
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

	public function __construct()
	{
		$this->ci =& get_instance();
		$this->ci->config->load('facility_switch_cache', true, true);
		$cfg = $this->ci->config->item('facility_switch_cache');
		$this->cache_file = isset($cfg['cache_file']) ? $cfg['cache_file'] : APPPATH . 'cache/facility_switch_ihris.json';
		$this->max_age_seconds = isset($cfg['max_age_seconds']) ? (int) $cfg['max_age_seconds'] : 604800;
		$this->timezone = isset($cfg['timezone']) ? $cfg['timezone'] : 'Africa/Kampala';
	}

	/**
	 * Rebuild cache from ihrisdata and write JSON file.
	 *
	 * @return array{status:string,districts:int,facilities:int,generated_at:string,cache_file:string}
	 */
	public function rebuild()
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
		$facilities_by_district = $merged['facilities_by_district'];
		$district_id_aliases = $merged['district_id_aliases'];
		$facility_count = $merged['facility_count'];
		// Keep every district_id from ihrisdata in the dropdown (e.g. KAMPALA and KAMPALA City).

		$payload = [
			'generated_at'           => $now->getTimestamp(),
			'generated_at_iso'       => $now->format('Y-m-d H:i:s'),
			'timezone'               => $this->timezone,
			'districts'              => $districts,
			'facilities_by_district' => $facilities_by_district,
			'district_id_aliases'    => $district_id_aliases,
		];

		$this->_write_cache_file($payload);

		return [
			'status'        => 'success',
			'districts'     => count($districts),
			'facilities'    => $facility_count,
			'generated_at'  => $payload['generated_at_iso'],
			'cache_file'    => $this->cache_file,
		];
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
		if (!is_readable($this->cache_file)) {
			if ($auto_rebuild_if_missing) {
				$this->rebuild();
			} else {
				return $this->_empty_payload();
			}
		}

		$raw = @file_get_contents($this->cache_file);
		$data = $raw ? json_decode($raw, true) : null;
		if (!is_array($data) || !isset($data['districts'], $data['facilities_by_district'])) {
			if ($auto_rebuild_if_missing) {
				$this->rebuild();
				$raw = @file_get_contents($this->cache_file);
				$data = $raw ? json_decode($raw, true) : null;
			}
		}

		if (!is_array($data) || !isset($data['districts'], $data['facilities_by_district'])) {
			return $this->_empty_payload();
		}

		if ($auto_rebuild_if_stale && $this->is_stale($data)) {
			$this->rebuild();
			$raw = @file_get_contents($this->cache_file);
			$data = $raw ? json_decode($raw, true) : $data;
		}

		return $data;
	}

	/**
	 * @param array|null $data
	 */
	public function is_stale($data = null)
	{
		if ($data === null) {
			if (!is_readable($this->cache_file)) {
				return true;
			}
			$raw = @file_get_contents($this->cache_file);
			$data = $raw ? json_decode($raw, true) : null;
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
		if (!is_readable($this->cache_file)) {
			return [
				'status'     => 'missing',
				'is_stale'   => true,
				'cache_file' => $this->cache_file,
			];
		}
		$raw = @file_get_contents($this->cache_file);
		$data = $raw ? json_decode($raw, true) : null;
		return [
			'status'       => 'ok',
			'generated_at' => isset($data['generated_at_iso']) ? $data['generated_at_iso'] : null,
			'is_stale'     => $this->is_stale($data),
			'cache_file'   => $this->cache_file,
		];
	}

	protected function _write_cache_file(array $payload)
	{
		$dir = dirname($this->cache_file);
		if (!is_dir($dir)) {
			@mkdir($dir, 0755, true);
		}
		$json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
		$tmp = $this->cache_file . '.tmp';
		if (@file_put_contents($tmp, $json, LOCK_EX) === false) {
			throw new RuntimeException('Could not write facility switch cache to ' . $tmp);
		}
		if (!@rename($tmp, $this->cache_file)) {
			@unlink($tmp);
			throw new RuntimeException('Could not publish facility switch cache at ' . $this->cache_file);
		}
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
