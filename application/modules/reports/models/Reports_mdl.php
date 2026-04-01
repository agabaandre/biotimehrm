<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Reports_mdl extends CI_Model
{
	protected $department;

	public function __Construct()
	{

		parent::__Construct();
		$this->department = $this->session->userdata['department_id'];
	}

	public function getFacilities($district_id)
	{

		$this->db->select('distinct(facility),facility_id');
		$this->db->where('district_id', $district_id);
		$query = $this->db->get('ihrisdata');

		return $query->result();
	}

	public function getgraphData()
	{
		$facility = $_SESSION['facility'];

		$date_from = date("Y-m", strtotime("-11 month"));
		$date_to = date('Y-m');


		$datas = array();
		$period = array();
		$targets = array();
		$target = $this->db->query("SELECT staff from staffing_rate WHERE date like '$date_to%' AND facility_id='$facility'");
		foreach ($target->result() as $dt):
			$staff = $dt->staff;
		endforeach;
		$query = $this->db->query("SELECT distinct(date) as period, round(reporting_rate) as data from attendance_rate WHERE date BETWEEN '$date_from' AND '$date_to' AND facility_id='$facility'");
		foreach ($query->result() as $data):
			array_push($targets, $staff);
			array_push($period, $data->period);
			array_push($datas, $data->data);

		endforeach;



		return array('period' => $period, 'data' => $datas, 'target' => $targets);
	}

	public function dutygraphData()
	{
		$facility = $_SESSION['facility'];
		
		// Calculate financial year range (June to June)
		// Get current date
		$current_year = (int)date('Y');
		$current_month = (int)date('m');
		
		// Financial year starts in June (month 06)
		// If current month is June or later, we're in the current financial year
		// If current month is before June, we're in the previous financial year
		if ($current_month >= 6) {
			// Financial year: June current_year to May next_year
			$fy_start_year = $current_year;
		} else {
			// Financial year: June previous_year to May current_year
			$fy_start_year = $current_year - 1;
		}
		
		// Get data for last 3 financial years
		$datas = array();
		$period = array();
		$targets = array();
		
		// Loop through last 3 financial years
		for ($i = 2; $i >= 0; $i--) {
			$fy_year = $fy_start_year - $i;
			$fy_start = $fy_year . '-06-01';
			$fy_end = ($fy_year + 1) . '-05-31';
			
			// Format period label as "FY 2024-25"
			$period_label = 'FY ' . $fy_year . '-' . substr(strval($fy_year + 1), 2);
			
			// Query to get average reporting rate for roster (scheduled) data for this financial year
			$roster_query = $this->db->query("
				SELECT 
					ROUND(AVG(reporting_rate)) as data,
					COUNT(DISTINCT date) as month_count
				FROM rosta_rate 
				WHERE facility_id = '$facility'
				AND date >= '$fy_start' 
				AND date <= '$fy_end'
			");
			
			$roster_result = $roster_query->row();
			$roster_data = $roster_result ? (float)$roster_result->data : 0;
			$roster_month_count = $roster_result ? (int)$roster_result->month_count : 0;
			
			// Query to get average reporting rate for attendance data for this financial year
			$attendance_query = $this->db->query("
				SELECT 
					ROUND(AVG(reporting_rate)) as data,
					COUNT(DISTINCT date) as month_count
				FROM attendance_rate 
				WHERE facility_id = '$facility'
				AND date >= '$fy_start' 
				AND date <= '$fy_end'
			");
			
			$attendance_result = $attendance_query->row();
			$attendance_data = $attendance_result ? (float)$attendance_result->data : 0;
			$attendance_month_count = $attendance_result ? (int)$attendance_result->month_count : 0;
			
			// Use the data that has more months, or roster if both are equal
			// This ensures we show data even if one table has partial data
			if ($roster_month_count >= $attendance_month_count && $roster_month_count > 0) {
				$avg_data = $roster_data;
				$month_count = $roster_month_count;
			} elseif ($attendance_month_count > 0) {
				$avg_data = $attendance_data;
				$month_count = $attendance_month_count;
			} else {
				$avg_data = 0;
				$month_count = 0;
			}
			
			// Only include financial years that have data
			if ($month_count > 0) {
				array_push($period, $period_label);
				array_push($datas, $avg_data);
				
				// Get target staff for this financial year (use latest available)
				$target_query = $this->db->query("
					SELECT staff 
					FROM staffing_rate 
					WHERE facility_id = '$facility'
					AND date >= '$fy_start' 
					AND date <= '$fy_end'
					ORDER BY date DESC 
					LIMIT 1
				");
				
				$target_result = $target_query->row();
				$staff = $target_result ? (int)$target_result->staff : 0;
				array_push($targets, $staff);
			}
		}

		return array('period' => $period, 'data' => $datas, 'target' => $targets);
	}

	/**
	 * Attendance per month graph using `actuals` table, ordered by Financial Year (Jun -> May).
	 * Uses schedule_id=22 (Present) as "Attending".
	 *
	 * When NO person is selected: returns facility-wide Avg Daily Attendance (unique staff).
	 * When a person is selected: returns that person's Days Present per month (unique days).
	 *
	 * Returns: ['period' => [...], 'data' => [...], 'meta' => [...]]
	 */
	public function attendanceActualsGraphData($facility = null, $year = null, $month = null, $empid = null)
	{
		$facility = $facility ?: $_SESSION['facility'];
		$year = $year ?: (int) ($this->session->userdata('year') ?: date('Y'));
		$month = $month ?: (int) ($this->session->userdata('month') ?: date('m'));
		$empid = $empid !== null ? trim((string)$empid) : trim((string) $this->session->userdata('dashboard_empid'));

		// Financial year starts in June (06). FY runs Jun -> May.
		$fy_start_year = ((int)$month >= 6) ? (int)$year : ((int)$year - 1);

		$fy_start = $fy_start_year . '-06-01';
		$fy_end = ($fy_start_year + 1) . '-05-31';

		$params = [$facility, $fy_start, $fy_end];
		$meta = [
			'fy_start' => $fy_start,
			'fy_end' => $fy_end,
			'mode' => empty($empid) ? 'facility' : 'person',
			'empid' => $empid
		];

		if (!empty($empid)) {
			// Person selected: plot that person's unique Days Present per month
			$params[] = $empid;
			$query = $this->db->query(
				"SELECT
				    DATE_FORMAT(date,'%Y-%m') as ym,
				    COUNT(DISTINCT date) as days_present
				 FROM actuals
				 WHERE facility_id = ?
				   AND schedule_id = 22
				   AND date >= ?
				   AND date <= ?
				   AND ihris_pid = ?
				 GROUP BY DATE_FORMAT(date,'%Y-%m')
				 ORDER BY ym ASC",
				$params
			);
		} else {
			// Facility-wide: plot Avg Daily Attendance (unique staff)
			$query = $this->db->query(
				"SELECT
				    DATE_FORMAT(date,'%Y-%m') as ym,
				    COUNT(DISTINCT CONCAT(ihris_pid,'|',date)) as uniq_emp_days,
				    COUNT(DISTINCT date) as uniq_days
				 FROM actuals
				 WHERE facility_id = ?
				   AND schedule_id = 22
				   AND date >= ?
				   AND date <= ?
				 GROUP BY DATE_FORMAT(date,'%Y-%m')
				 ORDER BY ym ASC",
				$params
			);
		}

		$rows = $query ? $query->result() : [];
		$map = [];
		foreach ($rows as $r) {
			if (!empty($r->ym)) {
				if (!empty($empid)) {
					$map[$r->ym] = isset($r->days_present) ? (int) $r->days_present : 0;
				} else {
					$uniq_emp_days = isset($r->uniq_emp_days) ? (int) $r->uniq_emp_days : 0;
					$uniq_days = isset($r->uniq_days) ? (int) $r->uniq_days : 0;
					$avg_daily = ($uniq_days > 0) ? (int) round($uniq_emp_days / $uniq_days) : 0;
					$map[$r->ym] = $avg_daily;
				}
			}
		}

		// Build ordered month labels from Jun..May, filling missing with 0.
		$period = [];
		$data = [];
		$start = new DateTime($fy_start);
		for ($i = 0; $i < 12; $i++) {
			$ym = $start->format('Y-m');
			$period[] = $start->format('M Y'); // e.g., Jun 2025
			$data[] = isset($map[$ym]) ? $map[$ym] : 0;
			$start->modify('+1 month');
		}

		return ['period' => $period, 'data' => $data, 'meta' => $meta];
	}

	public function attroData()
	{
		$facility = $_SESSION['facility'];
		$date_from = date("Y-m", strtotime("-11 month"));
		$date_to = date('Y-m');
		$rdata = array();
		$rperiod = array();
		$adata = array();
		$aperiod = array();

		$query = $this->db->query("SELECT distinct(date) as period, round(reporting_rate) as data from dutydays_rate WHERE date BETWEEN '$date_from' AND '$date_to' AND facility_id='$facility'");
		foreach ($query->result() as $data):
			$rdate = $data->period;
			$rostadata = $data->data;

			array_push($rdata, $rostadata);
			array_push($rperiod, $rdate);

			$query2 = $this->db->query("SELECT distinct(date) as period, round(reporting_rate) as data from presence_rate WHERE date='$rdate' AND facility_id='$facility'");

			foreach ($query2->result() as $attd):
				$attdate = $attd->period;
				$attdata = $attd->data;
				array_push($adata, $attdata);
				array_push($aperiod, $attdate);
			endforeach;

		endforeach;

		return array('aperiod' => $aperiod, 'adata' => $adata, 'dperiod' => $rperiod, 'ddata' => $rdata);
	}

	public function average_hours($fyear)
	{
		return $this->average_hours_fetch($fyear, null, null, '');
	}

	/**
	 * Count rows for average hours report (DataTables server-side).
	 */
	public function count_average_hours($fyear, $search = '')
	{
		$facility = $this->db->escape($_SESSION['facility']);
		$filter = "";
		if (!empty($fyear)) {
			$filter = " AND DATE_FORMAT(date,'%Y') = " . $this->db->escape($fyear);
		}
		$having = "";
		if ($search !== '' && trim($search) !== '') {
			$like = $this->db->escape('%' . $this->db->escape_like_str(trim($search)) . '%');
			$having = " HAVING (month_year LIKE $like)";
		}
		$sql = "SELECT COUNT(*) AS cnt FROM (
			SELECT DATE_FORMAT(date,'%Y-%m') AS month_year
			FROM clk_diff
			WHERE facility_id = $facility $filter
			GROUP BY DATE_FORMAT(date,'%Y-%m')
			$having
		) t";
		$row = $this->db->query($sql)->row();
		return (int) ($row->cnt ?? 0);
	}

	/**
	 * Fetch average hours for DataTables (paginated, optional search).
	 */
	public function average_hours_fetch($fyear, $start = null, $length = null, $search = '')
	{
		$facility = $this->db->escape($_SESSION['facility']);
		$filter = "";
		if (!empty($fyear)) {
			$filter = " AND DATE_FORMAT(date,'%Y') = " . $this->db->escape($fyear);
		}
		$having = "";
		if ($search !== '' && trim($search) !== '') {
			$like = $this->db->escape('%' . $this->db->escape_like_str(trim($search)) . '%');
			$having = " HAVING (month_year LIKE $like)";
		}
		$limit_sql = "";
		if ($start !== null && $length !== null && (int) $length > 0) {
			$limit_sql = " LIMIT " . (int) $start . "," . (int) $length;
		}
		/* MySQL 8 ONLY_FULL_GROUP_BY: non-grouped columns must be aggregated */
		$sql = "SELECT (SUM(time_diff) / COUNT(pid)) AS avg_hours,
				MAX(facility) AS facility,
				DATE_FORMAT(date,'%Y-%m') AS month_year
			FROM clk_diff
			WHERE facility_id = $facility $filter
			GROUP BY DATE_FORMAT(date,'%Y-%m')
			$having
			ORDER BY month_year DESC
			$limit_sql";
		return $this->db->query($sql)->result_array();
	}



	public function count_aggregated($filters = null, $group_by = "district")
	{

		$this->apply_aggregation_filter($filters);
		
   
		$this->db->from("person_att_final");
		$this->db->group_by("$group_by");
		$query = $this->db->get();

		return $query->num_rows();
	}



	public function attendance_aggregates($filters = null, $limit = NULL, $start = NULL, $group_by = "district")
	{
		$group_by = $this->_aggregate_group_by_column($group_by);
		if ($limit)
			$this->db->limit($limit, $start);

		$this->apply_aggregation_filter($filters);

		/* MySQL 8 ONLY_FULL_GROUP_BY: non-grouped columns must be aggregated */
		$select = $this->_aggregate_select_sql($group_by);
		$this->db->select($select, false);

		$this->db->from("person_att_final");
		$this->db->group_by("duty_date");
		$this->db->group_by($group_by);
		$this->db->order_by("duty_date", 'ASC');
		$this->db->order_by($group_by, 'ASC');

		$data = $this->db->get()->result();

		return $data;
	}

	/**
	 * Whitelist group_by column for aggregation (MySQL 8 safe).
	 */
	private function _aggregate_group_by_column($group_by)
	{
		$allowed = array('district', 'facility_name', 'job', 'region', 'institution_type', 'department_id', 'cadre', 'gender', 'facility_type_name');
		return in_array($group_by, $allowed) ? $group_by : 'district';
	}

	/**
	 * Build SELECT for attendance aggregates (MySQL 8 ONLY_FULL_GROUP_BY).
	 */
	private function _aggregate_select_sql($group_by)
	{
		$dims = array('job', 'facility_name', 'facility_type_name', 'cadre', 'gender', 'district', 'department_id', 'region', 'institution_type');
		$parts = array('duty_date', $group_by);
		foreach ($dims as $d) {
			if ($d !== 'duty_date' && $d !== $group_by) {
				$parts[] = "MAX($d) AS $d";
			}
		}
		$parts[] = "SUM(P) AS present";
		$parts[] = "SUM(O) AS off";
		$parts[] = "SUM(L) AS own_leave";
		$parts[] = "SUM(R) AS official";
		$parts[] = "SUM(X) AS absent";
		$parts[] = "SUM(H) AS holiday";
		$parts[] = "SUM(base_line) AS days_supposed";
		$parts[] = "SUM(base_line - (P+O+L+R)) AS days_absent";
		return implode(', ', $parts);
	}

	public function aggregate_group_count($column, $value, $period)
	{

		$query = $this->db->where($column, $value)
			->where("duty_date='$period'")
			->get('person_att_final');
		return $query->num_rows();
	}

	public function apply_aggregation_filter($filters)
	{


		if (!empty($filters)) {

			foreach ($filters as $key => $value) {

				if (($key !== "rows" && $key !== "group_by" && $key !== "month" && $key !== "year" && $key !== "csv" && $key !== "pdf" && $key !== "region" && $key !== "institution_type" && $key !== "duty_date" && !empty($value))) {
					$this->db->where($key, $value);
				}
			}

			if (isset($filters['region'])) {

				$this->db->where_in('region', $filters['region']);

			}
			if (isset($filters['duty_date'])) {
				$dd = $filters['duty_date'];
				if (!is_array($dd)) {
					$dd = array($dd);
				}
				$this->db->where_in('duty_date', $dd);
			}
			if (isset($filters['institution_type'])) {

				$this->db->where_in('institution_type', $filters['institution_type']);
			}
		}

	}

	/**
	 * Server-side DataTables count for attendance aggregates.
	 */
	public function countAttendanceAggregatesAjax($filters = null, $group_by = "district", $search = '')
	{
		$sql = $this->buildAttendanceAggregatesSql($filters, $group_by, $search, true, 0, 0);
		$row = $this->db->query($sql)->row();
		return (int) ($row->cnt ?? 0);
	}

	/**
	 * Server-side DataTables fetch for attendance aggregates.
	 */
	public function fetchAttendanceAggregatesAjax($filters = null, $group_by = "district", $start = 0, $length = 200, $search = '')
	{
		$sql = $this->buildAttendanceAggregatesSql($filters, $group_by, $search, false, (int) $start, (int) $length);
		return $this->db->query($sql)->result();
	}

	private function buildAttendanceAggregatesSql($filters = null, $group_by = "district", $search = '', $count_only = false, $start = 0, $length = 200)
	{
		$group_by = $this->_aggregate_group_by_column($group_by);
		$group_expr_map = array(
			'job' => 't.job',
			'facility_name' => 't.facility_name',
			'facility_type_name' => 't.facility_type_name',
			'cadre' => 't.cadre',
			'institution_type' => 't.institution_type',
			'district' => 't.district',
			'region' => 't.region',
			'department_id' => 't.department_id',
			'gender' => 't.gender',
		);
		$group_expr = isset($group_expr_map[$group_by]) ? $group_expr_map[$group_by] : 't.district';

		$duty_dates = array();
		if (isset($filters['duty_date'])) {
			$duty_dates = is_array($filters['duty_date']) ? $filters['duty_date'] : array($filters['duty_date']);
		}
		if (empty($duty_dates)) {
			$duty_dates = array(date('Y-m'));
		}

		$where = array("a.ihris_pid IS NOT NULL", "TRIM(a.ihris_pid) <> ''");
		$ym_escaped = array();
		foreach ($duty_dates as $ym) {
			$ym = trim((string) $ym);
			if ($ym !== '') {
				$ym_escaped[] = $this->db->escape($ym);
			}
		}
		if (!empty($ym_escaped)) {
			$where[] = "DATE_FORMAT(a.date,'%Y-%m') IN (" . implode(',', $ym_escaped) . ")";
		}

		if (!empty($filters['district'])) {
			$where[] = "COALESCE(i.district,'') = " . $this->db->escape($filters['district']);
		}
		if (!empty($filters['facility_name'])) {
			$where[] = "COALESCE(i.facility,'') = " . $this->db->escape($filters['facility_name']);
		}

		if (isset($filters['region']) && is_array($filters['region']) && !empty($filters['region'])) {
			$regions = array();
			foreach ($filters['region'] as $region) {
				if ($region !== '' && $region !== null) {
					$regions[] = $this->db->escape($region);
				}
			}
			if (!empty($regions)) {
				$where[] = "COALESCE(i.region,'') IN (" . implode(',', $regions) . ")";
			}
		}

		if (isset($filters['institution_type']) && is_array($filters['institution_type']) && !empty($filters['institution_type'])) {
			$types = array();
			foreach ($filters['institution_type'] as $type) {
				if ($type !== '' && $type !== null) {
					$types[] = $this->db->escape($type);
				}
			}
			if (!empty($types)) {
				$where[] = "COALESCE(i.institutiontype_name,'') IN (" . implode(',', $types) . ")";
			}
		}

		$base_where = implode(' AND ', $where);

		$sub_sql = "SELECT
				DATE_FORMAT(a.date,'%Y-%m') AS duty_date,
				a.ihris_pid,
				COALESCE(i.job, '') AS job,
				COALESCE(i.facility, '') AS facility_name,
				COALESCE(i.facility_type_id, '') AS facility_type_name,
				COALESCE(i.cadre, '') AS cadre,
				COALESCE(i.gender, '') AS gender,
				COALESCE(i.district, '') AS district,
				COALESCE(i.department_id, '') AS department_id,
				COALESCE(i.region, '') AS region,
				COALESCE(i.institutiontype_name, '') AS institution_type,
				SUM(CASE WHEN s.letter='P' THEN 1 ELSE 0 END) AS present,
				SUM(CASE WHEN s.letter='O' THEN 1 ELSE 0 END) AS off,
				SUM(CASE WHEN s.letter='L' THEN 1 ELSE 0 END) AS own_leave,
				SUM(CASE WHEN s.letter='R' THEN 1 ELSE 0 END) AS official,
				SUM(CASE WHEN s.letter='H' THEN 1 ELSE 0 END) AS holiday,
				CAST(DAY(LAST_DAY(CONCAT(DATE_FORMAT(a.date,'%Y-%m'),'-01'))) AS UNSIGNED) AS base_line
			FROM actuals a
			LEFT JOIN schedules s ON s.schedule_id = a.schedule_id
			LEFT JOIN ihrisdata i ON i.ihris_pid = a.ihris_pid
			WHERE " . $base_where . "
			GROUP BY DATE_FORMAT(a.date,'%Y-%m'), a.ihris_pid, i.job, i.facility, i.facility_type_id, i.cadre, i.gender, i.district, i.department_id, i.region, i.institutiontype_name";

		$search_sql = '';
		if ($search !== '' && trim($search) !== '') {
			$like = $this->db->escape('%' . $this->db->escape_like_str(trim($search)) . '%');
			$search_sql = " WHERE (" . $group_expr . " LIKE " . $like . " OR t.duty_date LIKE " . $like . ")";
		}

		$outer_sql = "SELECT
				t.duty_date,
				" . $group_expr . " AS " . $this->db->protect_identifiers($group_by) . ",
				SUM(t.present) AS present,
				SUM(t.off) AS off,
				SUM(t.own_leave) AS own_leave,
				SUM(t.official) AS official,
				SUM(t.holiday) AS holiday,
				SUM(GREATEST(0, t.base_line - (t.off + t.own_leave + t.official + t.holiday))) AS days_supposed,
				SUM(GREATEST(0, GREATEST(0, t.base_line - (t.off + t.own_leave + t.official + t.holiday)) - t.present)) AS days_absent,
				SUM(GREATEST(0, GREATEST(0, t.base_line - (t.off + t.own_leave + t.official + t.holiday)) - t.present)) AS absent
			FROM (" . $sub_sql . ") t
			" . $search_sql . "
			GROUP BY t.duty_date, " . $group_expr;

		if ($count_only) {
			return "SELECT COUNT(*) AS cnt FROM (" . $outer_sql . ") x";
		}

		$limit_sql = '';
		if ($length > 0) {
			$limit_sql = " LIMIT " . (int) $start . "," . (int) $length;
		}
		return $outer_sql . " ORDER BY t.duty_date ASC, " . $group_expr . " ASC" . $limit_sql;
	}

	public function count_person_attendance($filters = null, $search_like = '')
	{
		$this->apply_person_attendance_all_actuals_filter($filters, $search_like);
		$this->db->select('a.ihris_pid, a.facility_id', false);
		$this->db->group_by('a.ihris_pid');
		$this->db->group_by('a.facility_id');
		$query = $this->db->get();
		return $query->num_rows();
	}

	public function person_attendance_all($filters = null, $limit = NULL, $start = NULL, $search_like = '')
	{
		$this->apply_person_attendance_all_actuals_filter($filters, $search_like);
		$duty_date = '';
		if (isset($filters['duty_date']) && !empty($filters['duty_date'])) {
			$dd = is_array($filters['duty_date']) ? $filters['duty_date'] : array($filters['duty_date']);
			$duty_date = (string) reset($dd);
		}
		$this->db->select("
			a.ihris_pid AS ihris_pid,
			TRIM(MAX(CONCAT(COALESCE(i.surname,''), ' ', COALESCE(i.firstname,''), ' ', COALESCE(i.othername,'')))) AS fullname,
			MAX(COALESCE(i.district, '')) AS district,
			COALESCE(
				MAX(CASE WHEN i.facility_id = a.facility_id THEN i.facility ELSE NULL END),
				MAX(COALESCE(i.facility, '')),
				a.facility_id
			) AS facility_name,
			a.facility_id AS facility_id,
			" . $this->db->escape($duty_date) . " AS duty_date,
			SUM(CASE WHEN s.letter='P' THEN 1 ELSE 0 END) AS P,
			SUM(CASE WHEN s.letter='O' THEN 1 ELSE 0 END) AS O,
			SUM(CASE WHEN s.letter='R' THEN 1 ELSE 0 END) AS R,
			SUM(CASE WHEN s.letter='L' THEN 1 ELSE 0 END) AS L,
			SUM(CASE WHEN s.letter='H' THEN 1 ELSE 0 END) AS H
		", false);
		$this->db->group_by('a.ihris_pid');
		$this->db->group_by('a.facility_id');
		$this->db->order_by('facility_name', 'ASC');
		$this->db->order_by('fullname', 'ASC');
		if ($limit) {
			$this->db->limit($limit, $start);
		}
		$data = $this->db->get()->result();
		return $data;
	}

	private function apply_person_attendance_all_actuals_filter($filters = null, $search_like = '')
	{
		$session_facility = isset($_SESSION['facility']) ? trim((string) $_SESSION['facility']) : '';
		$this->db->from('actuals a');
		$this->db->join('schedules s', 's.schedule_id = a.schedule_id', 'left');
		$this->db->join('ihrisdata i', 'i.ihris_pid = a.ihris_pid', 'left');
		$this->db->where('a.ihris_pid IS NOT NULL', null, false);
		$this->db->where("TRIM(a.ihris_pid) <> ''", null, false);
		if ($session_facility !== '') {
			// Scope by the facility captured in actual attendance records for the month.
			$this->db->where('a.facility_id', $session_facility);
		}

		if (!empty($filters)) {
			if (isset($filters['duty_date']) && !empty($filters['duty_date'])) {
				$dd = is_array($filters['duty_date']) ? $filters['duty_date'] : array($filters['duty_date']);
				$this->db->where_in("DATE_FORMAT(a.date,'%Y-%m')", $dd, false);
			}
			if (isset($filters['district']) && $filters['district'] !== '') {
				$this->db->where('i.district', $filters['district']);
			}
			if (isset($filters['facility_name']) && $filters['facility_name'] !== '') {
				$this->db->where('i.facility', $filters['facility_name']);
			}
		}

		if ($search_like !== '' && trim($search_like) !== '') {
			$term = $this->db->escape_like_str(trim($search_like));
			$this->db->group_start();
			$this->db->like("CONCAT(COALESCE(i.surname,''), ' ', COALESCE(i.firstname,''), ' ', COALESCE(i.othername,''))", $term, 'both', false);
			$this->db->or_like('i.district', $term);
			$this->db->or_like('i.facility', $term);
			$this->db->group_end();
		}
	}
}