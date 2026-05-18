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

		$this->ci->db->select('DISTINCT district_id, district', false);
		$this->ci->db->where('district_id IS NOT NULL', null, false);
		$this->ci->db->where('district_id !=', '');
		$this->ci->db->where('district IS NOT NULL', null, false);
		$this->ci->db->where('district !=', '');
		$this->ci->db->order_by('district', 'ASC');
		$district_rows = $this->ci->db->get('ihrisdata')->result();

		$this->ci->db->select('DISTINCT district_id, facility_id, facility', false);
		$this->ci->db->where('district_id IS NOT NULL', null, false);
		$this->ci->db->where('district_id !=', '');
		$this->ci->db->where('facility_id IS NOT NULL', null, false);
		$this->ci->db->where('facility_id !=', '');
		$this->ci->db->where('facility IS NOT NULL', null, false);
		$this->ci->db->where('facility !=', '');
		$this->ci->db->order_by('district_id', 'ASC');
		$this->ci->db->order_by('facility', 'ASC');
		$facility_rows = $this->ci->db->get('ihrisdata')->result();

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

		$facilities_by_district = [];
		$facility_count = 0;
		$seen_facilities = [];
		foreach ($facility_rows as $row) {
			$district_id = trim((string) $row->district_id);
			$facility_id = trim((string) $row->facility_id);
			if ($district_id === '' || $facility_id === '') {
				continue;
			}
			$key = $district_id . '|' . $facility_id;
			if (isset($seen_facilities[$key])) {
				continue;
			}
			$seen_facilities[$key] = true;
			if (!isset($facilities_by_district[$district_id])) {
				$facilities_by_district[$district_id] = [];
			}
			$facilities_by_district[$district_id][] = [
				'facility_id' => $facility_id,
				'facility'    => trim((string) $row->facility),
			];
			$facility_count++;
		}

		$payload = [
			'generated_at'           => $now->getTimestamp(),
			'generated_at_iso'       => $now->format('Y-m-d H:i:s'),
			'timezone'               => $this->timezone,
			'districts'              => $districts,
			'facilities_by_district' => $facilities_by_district,
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
	 * Facilities in a district; optionally restrict to one facility_id (session user).
	 *
	 * @param string      $district_id
	 * @param string|null $only_facility_id
	 * @return array<int, object>
	 */
	public function get_facilities_for_district($district_id, $only_facility_id = null)
	{
		$data = $this->get_data();
		$district_id = trim((string) $district_id);
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
		];
	}
}
