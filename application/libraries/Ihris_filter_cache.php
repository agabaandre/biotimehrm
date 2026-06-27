<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cached ihrisdata filter dropdowns: Redis → Memcached → DB.
 */
class Ihris_filter_cache {

	protected $ci;
	protected $store;
	protected $ttl = 86400;
	protected $cache_key = 'ihris_filter_options';

	public function __construct()
	{
		$this->ci =& get_instance();
		$this->ci->load->helper(['mysql8_ihris', 'ihris_filter']);
		$this->ci->load->library('dropdown_cache_store', ['key_prefix' => 'attend_df_'], 'ihris_filter_store');
		$this->store = $this->ci->ihris_filter_store;
	}

	/**
	 * @return array{regions:array,districts:array,institution_types:array,cadres:array}
	 */
	public function get_options()
	{
		$cached = $this->store->read($this->cache_key, '');
		if (is_array($cached) && isset($cached['regions'], $cached['districts'])) {
			return $cached;
		}

		$payload = $this->_build_from_db();
		$this->store->write($this->cache_key, $payload, $this->ttl, '');

		return $payload;
	}

	protected function _build_from_db()
	{
		$regions = [];
		if ($this->ci->db->field_exists('region', 'ihrisdata')) {
			$q = $this->ci->db->query(
				"SELECT " . mysql8_trim_expr('region') . " AS val
				 FROM ihrisdata
				 WHERE " . mysql8_nonempty_sql('region') . "
				 GROUP BY " . mysql8_trim_expr('region') . "
				 ORDER BY " . mysql8_trim_expr('region') . " ASC"
			);
			foreach ($q->result() as $row) {
				$v = trim((string) $row->val);
				if ($v !== '') {
					$regions[] = ['value' => $v, 'label' => $v];
				}
			}
		}

		$this->ci->load->library('facility_switch_cache', null, 'fsc');
		$districts = [];
		foreach ($this->ci->fsc->get_districts() as $row) {
			$name = trim((string) $row->district);
			if ($name !== '') {
				$districts[] = ['value' => $name, 'label' => $name];
			}
		}

		$institution_types = [];
		$inst_col = ihris_institution_type_column($this->ci->db);
		if ($inst_col !== null) {
			$q = $this->ci->db->query(
				"SELECT " . mysql8_trim_expr($inst_col) . " AS val
				 FROM ihrisdata
				 WHERE " . mysql8_nonempty_sql($inst_col) . "
				 GROUP BY " . mysql8_trim_expr($inst_col) . "
				 ORDER BY " . mysql8_trim_expr($inst_col) . " ASC"
			);
			foreach ($q->result() as $row) {
				$v = trim((string) $row->val);
				if ($v !== '') {
					$institution_types[] = ['value' => $v, 'label' => $v];
				}
			}
		}

		$cadres = [];
		if ($this->ci->db->field_exists('cadre', 'ihrisdata')) {
			$q = $this->ci->db->query(
				"SELECT " . mysql8_trim_expr('cadre') . " AS val
				 FROM ihrisdata
				 WHERE " . mysql8_nonempty_sql('cadre') . "
				 GROUP BY " . mysql8_trim_expr('cadre') . "
				 ORDER BY " . mysql8_trim_expr('cadre') . " ASC"
			);
			foreach ($q->result() as $row) {
				$v = trim((string) $row->val);
				if ($v !== '') {
					$cadres[] = ['value' => $v, 'label' => $v];
				}
			}
		}

		return [
			'regions'            => $regions,
			'districts'          => $districts,
			'institution_types'  => $institution_types,
			'cadres'             => $cadres,
			'generated_at'       => time(),
		];
	}
}
