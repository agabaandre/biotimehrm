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
	 * Returns: ['period' => [labels...], 'data' => [counts...]]
	 */
	public function attendanceActualsGraphData($facility = null)
	{
		$facility = $facility ?: $_SESSION['facility'];

		$current_year = (int) date('Y');
		$current_month = (int) date('m');

		// Financial year starts in June (06). FY runs Jun -> May.
		$fy_start_year = ($current_month >= 6) ? $current_year : ($current_year - 1);

		$fy_start = $fy_start_year . '-06-01';
		$fy_end = ($fy_start_year + 1) . '-05-31';

		// Fetch counts per month within FY.
		// We de-duplicate by (employee, date) to avoid inflated results if there are duplicates.
		// Then we compute an *average daily attendance* per month:
		//   avg_daily = unique_employee_days / distinct_days_in_month
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
			[$facility, $fy_start, $fy_end]
		);

		$rows = $query ? $query->result() : [];
		$map = [];
		foreach ($rows as $r) {
			if (!empty($r->ym)) {
				$uniq_emp_days = isset($r->uniq_emp_days) ? (int) $r->uniq_emp_days : 0;
				$uniq_days = isset($r->uniq_days) ? (int) $r->uniq_days : 0;
				$avg_daily = ($uniq_days > 0) ? (int) round($uniq_emp_days / $uniq_days) : 0;
				$map[$r->ym] = $avg_daily;
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

		return ['period' => $period, 'data' => $data];
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

		$facility = $_SESSION['facility'];

		if (!empty($fyear)) {

			$filter = "and date_format(date,'%Y')='$fyear'";
		} else {
			$filter = "";
		}
		$fac = $this->db->query("SELECT (SUM(time_diff)/COUNT(pid)) as avg_hours,facility,date_format(date,'%Y-%m') as month_year FROM clk_diff WHERE facility_id='$facility' $filter group by date_format(date,'%Y-%m') ORDER BY date_format(date,'%Y-%m') DESC ")->result_array();
		return $fac;
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


		if ($limit)
			$this->db->limit($limit, $start);

		$this->apply_aggregation_filter($filters);

		$this->db->select("
			job,
			facility_name,
			facility_type_name,
			cadre,
			gender,
			duty_date,
			district,
			department_id,
			region,
			institution_type,
			sum(P) as present,
			sum(O) as off,
			sum(L) as own_leave,
			sum(R) as official,
			sum(X) as absent,
			sum(H) as holiday,
			sum(base_line)   as days_supposed,
			sum(base_line - (P+O+L+R)) as days_absent
		");

		$this->db->from("person_att_final");
		$this->db->group_by("duty_date");
		$this->db->group_by("$group_by");
		$this->db->order_by("duty_date", 'ASC');
		$this->db->order_by("$group_by", 'ASC');

		$data = $this->db->get()->result();

		$sql = $this->db->last_query();
		dd($sql);

		// Print or log the SQL query
		//dd($sql);

		return $data;
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

				if (($key !== "rows" && $key !== "group_by" && $key !== "month" && $key !== "year" && $key !== "csv" && $key !== "region" && $key !== "institution_type" && $key!=="duty_date" && !empty($value))) {
					$this->db->where($key, $value);
				}
			}

			if (isset($filters['region'])) {

				$this->db->where_in('region', $filters['region']);

			}
			if (isset($filters['duty_date'])) {

				$this->db->where_in('duty_date', $filters['duty_date']);

			}
			if (isset($filters['institution_type'])) {

				$this->db->where_in('institution_type', $filters['institution_type']);
			}
		}

	}

	public function count_person_attendance($filters = null)
	{

		$this->apply_aggregation_filter($filters);

		$this->db->from("person_att_final");
		$query = $this->db->get();

		return $query->num_rows();
	}
	public function person_attendance_all($filters = null, $limit = NULL, $start = NULL)
	{

		if ($limit){
			$this->db->limit($limit, $start);
		}
		// if ($filters['facility_name'] == '') {
		// 	$facility_id = $_SESSION['facility'];
		// 	$this->db->where("facility_id", "$facility_id");
		// }
		//dd($filters);

		$this->apply_aggregation_filter($filters);
				

                $this->db->order_by("facility_name","ASC");
		$data = $this->db->get("person_att_final")->result();
		//dd($data);
		return $data;
	}
}