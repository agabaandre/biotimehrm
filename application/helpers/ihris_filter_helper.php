<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('ihris_institution_type_column')) {
	/**
	 * @param CI_DB_query_builder|null $db
	 * @return string|null
	 */
	function ihris_institution_type_column($db = null)
	{
		if ($db === null) {
			$CI =& get_instance();
			$db = $CI->db;
		}
		if ($db->field_exists('institutiontype_name', 'ihrisdata')) {
			return 'institutiontype_name';
		}
		if ($db->field_exists('institution_type', 'ihrisdata')) {
			return 'institution_type';
		}
		return null;
	}
}

if (!function_exists('financial_year_start_year')) {
	/**
	 * Uganda MOH financial year starts June (month 6).
	 *
	 * @param int|null $year  Calendar year
	 * @param int|null $month Calendar month 1-12
	 * @return int FY start calendar year (June of this year)
	 */
	function financial_year_start_year($year = null, $month = null)
	{
		$year = $year !== null ? (int) $year : (int) date('Y');
		$month = $month !== null ? (int) $month : (int) date('n');
		return ($month >= 6) ? $year : ($year - 1);
	}
}

if (!function_exists('financial_year_label')) {
	function financial_year_label($fy_start_year)
	{
		$fy_start_year = (int) $fy_start_year;
		return 'FY ' . $fy_start_year . '/' . substr((string) ($fy_start_year + 1), -2);
	}
}

if (!function_exists('financial_year_options')) {
	/**
	 * @param int $count Number of FY options including current
	 * @return array<int, array{value:int,label:string}>
	 */
	function financial_year_options($count = 4)
	{
		$current = financial_year_start_year();
		$out = [];
		for ($i = 0; $i < max(1, (int) $count); $i++) {
			$fy = $current - $i;
			$out[] = ['value' => $fy, 'label' => financial_year_label($fy)];
		}
		return $out;
	}
}

if (!function_exists('financial_year_bounds')) {
	/**
	 * @param int $fy_start_year
	 * @return array{start:string,end:string}
	 */
	function financial_year_bounds($fy_start_year)
	{
		$fy_start_year = (int) $fy_start_year;
		return [
			'start' => $fy_start_year . '-06-01',
			'end'     => ($fy_start_year + 1) . '-05-31',
		];
	}
}
