<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Reports_mdl extends CI_Model
{


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
		foreach ($target->result() as $dt) :
			$staff = $dt->staff;
		endforeach;
		$query = $this->db->query("SELECT distinct(date) as period, round(reporting_rate) as data from attendance_rate WHERE date BETWEEN '$date_from' AND '$date_to' AND facility_id='$facility'");
		foreach ($query->result() as $data) :
			array_push($targets, $staff);
			array_push($period, $data->period);
			array_push($datas, $data->data);

		endforeach;



		return array('period' => $period, 'data' => $datas, 'target' => $targets);
	}

	public function dutygraphData()
	{

		$facility  = $_SESSION['facility'];
		$date_from = date("Y-m", strtotime("-11 month"));
		$date_to = date('Y-m');
		$datas   = array();
		$period  = array();
		$targets = array();
		$target  = $this->db->query("SELECT staff from staffing_rate WHERE date like '$date_to%' AND facility_id='$facility'");

		foreach ($target->result() as $dt) :
			$staff = $dt->staff;
		endforeach;
		$query = $this->db->query("SELECT distinct(date) as period, round(reporting_rate) as data from rosta_rate WHERE date BETWEEN '$date_from' AND '$date_to' AND facility_id='$facility'");

		foreach ($query->result() as $data) :
			array_push($targets, $staff);
			array_push($period, $data->period);
			array_push($datas, $data->data);

		endforeach;

		return array('period' => $period, 'data' => $datas, 'target' => $targets);
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

		$query  = $this->db->query("SELECT distinct(date) as period, round(reporting_rate) as data from dutydays_rate WHERE date BETWEEN '$date_from' AND '$date_to' AND facility_id='$facility'");
		foreach ($query->result() as $data) :
			$rdate     = $data->period;
			$rostadata = $data->data;

			array_push($rdata, $rostadata);
			array_push($rperiod, $rdate);

			$query2    = $this->db->query("SELECT distinct(date) as period, round(reporting_rate) as data from presence_rate WHERE date='$rdate' AND facility_id='$facility'");

			foreach ($query2->result() as $attd) :
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



	public function  attendance_aggregates($filters = null, $limit = NULL, $start = NULL, $group_by = "district")
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
		$this->db->group_by("$group_by");

		$data = $this->db->get()->result();
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

				if (($key !== "rows" && $key !== "group_by" && $key !== "month" && $key !== "year" && $key !== "csv" && $key !== "region" && $key !== "institution_type") && !empty($value)) {
					$this->db->where($key, $value);
				}
				
				
			
			}
			
			if (isset($filters['region'])) {

				$this->db->where_in('region', $filters['region']);
			
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
	public function  person_attendance_all($filters = null, $limit = NULL, $start = NULL)
	{

		if ($limit)
			$this->db->limit($limit, $start);

		$this->apply_aggregation_filter($filters);

		$data = $this->db->get("person_att_final")->result();
		return $data;
	}
}
