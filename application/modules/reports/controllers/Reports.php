<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Reports extends MX_Controller
{

	protected $title;
	protected $filters;
	protected $ufilters;
	protected $distfilters;
	protected $watermark;

	public function __Construct()
	{

		parent::__Construct();

		$this->load->model('reports_mdl');
		$this->load->model('lists/facilities_mdl');
		$this->load->model('lists/districts_mdl');
		$this->module = "reports";
		$this->title = "Reports";
		$this->filters = Modules::run('filters/sessionfilters');
		//doesnt require a join on ihrisdata
		$this->ufilters = Modules::run('filters/universalfilters');
		// requires a join on ihrisdata with district level
		$this->distfilters = Modules::run('filters/districtfilters');
		$this->watermark = FCPATH . "assets/img/MOH.png";
	}

	public function index()
	{

		//$data['requests']=$this->requests;
		$data['title'] = $this->title;
		$data['uptitle'] = "Reports";

		$data['view'] = 'reports';
		$data['module'] = $this->module;
		echo Modules::run('templates/main', $data);
	}
	public function rosterRate()
	{

		//$data['requests']=$this->requests;
		$data['title'] = $this->title;
		$data['uptitle'] = "Duty Roster Reporting";

		$data['view'] = 'roster_rate';
		$data['module'] = $this->module;
		echo Modules::run('templates/main', $data);
	}
	public function attendanceRate()
	{


		$data['title'] = 'Attendance Reporting Rate';
		$data['uptitle'] = "Attendance Reporting";
		$data['view'] = 'attendance_rate';
		$data['module'] = $this->module;
		echo Modules::run('templates/main', $data);
	}
	public function attendroster()
	{


		$data['title'] = 'Attendance vs Duty Roster';
		$data['uptitle'] = "Attendance Reporting";
		$data['view'] = 'roster_att';
		$data['module'] = $this->module;
		echo Modules::run('templates/main', $data);
	}


	public function graphData()
	{
		$data = $this->reports_mdl->getgraphData();
		return $data;
	}
	public function dutygraphData()
	{
		$data = $this->reports_mdl->dutygraphData();
		return $data;
	}

	public function attroData()
	{
		$data = $this->reports_mdl->attroData();
		//print_r($data);
		echo json_encode($data, JSON_NUMERIC_CHECK);
	}


	public function average_hours($syear = FALSE)
	{
		$data['title'] = 'Average Hours';
		$data['uptitle'] = "Average Monthly Hours";
		$data['view'] = 'average_hours';
		$data['module'] = $this->module;
		$facility = $_SESSION['facility'];

		$year = $this->input->post('year');
		if (!empty($year)) {
			$fyear = $this->input->post('year');
		} else {
			$fyear = "";
		}

		$this->load->library('pagination');
		$config = array();
		$config['base_url'] = base_url() . "employees/viewTimeLogs";
		$config['total_rows'] = $this->db->query("SELECT pid FROM clk_diff WHERE facility_id='$facility' group by date_format(date,'%Y-%m')")->num_rows();
		$config['per_page'] = 200; //records per page
		$config['uri_segment'] = 3; //segment in url  
		//pagination links styling
		$config['full_tag_open'] = '<ul class="pagination">';
		$config['full_tag_close'] = '</ul>';
		$config['attributes'] = ['class' => 'page-link'];
		$config['first_link'] = false;
		$config['last_link'] = false;
		$config['first_tag_open'] = '<li class="page-item">';
		$config['first_tag_close'] = '</li>';
		$config['prev_link'] = '&laquo';
		$config['prev_tag_open'] = '<li class="page-item">';
		$config['prev_tag_close'] = '</li>';
		$config['next_link'] = '&raquo';
		$config['next_tag_open'] = '<li class="page-item">';
		$config['next_tag_close'] = '</li>';
		$config['last_tag_open'] = '<li class="page-item">';
		$config['last_tag_close'] = '</li>';
		$config['cur_tag_open'] = '<li class="page-item active"><a href="#" class="page-link">';
		$config['cur_tag_close'] = '<span class="sr-only">(current)</span></a></li>';
		$config['num_tag_open'] = '<li class="page-item">';
		$config['num_tag_close'] = '</li>';
		$config['use_page_numbers'] = false;
		$this->pagination->initialize($config);
		$page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0; //default starting point for limits 
		$data['links'] = $this->pagination->create_links();
		$data['sums'] = $this->reports_mdl->average_hours($fyear);

		echo Modules::run('templates/main', $data);
	}
	public function print_average($syear = false)
	{

		$this->load->library('M_pdf');
		$data['sums'] = $this->reports_mdl->average_hours($syear);
		$html = $this->load->view('averagehours_pdf', $data, true);
		$fac = $_SESSION['facility_name'];
		$filename = $fac . "_Average_Hours" . "pdf";
		ini_set('max_execution_time', 0);
		$PDFContent = mb_convert_encoding($html, 'UTF-8', 'UTF-8');
		$this->m_pdf->pdf->SetWatermarkImage($this->watermark);
		$this->m_pdf->pdf->showWatermarkImage = true;
		date_default_timezone_set("Africa/Kampala");
		$this->m_pdf->pdf->SetHTMLFooter("Printed/ Accessed on: <b>" . date('d F,Y h:i A') . "</b><br style='font-size: 9px !imporntant;'>" . "Source: iHRIS - HRM Attend " . base_url());
		$this->m_pdf->pdf->SetWatermarkImage($this->watermark);
		$this->m_pdf->showWatermarkImage = true;
		ini_set('max_execution_time', 0);
		$this->m_pdf->pdf->WriteHTML($PDFContent); //ml_pdf because we loaded the library ml_pdf for landscape format not m_pdf
		//download it D save F.
		$this->m_pdf->pdf->Output($filename, 'I');
	}


	public function attendance_aggregate()
	{
		$search = request_fields();
		$group_by = (!empty(request_fields('group_by'))) ? request_fields('group_by') : "district";
		$month_year = request_fields('duty_date');
		$csv = request_fields('csv');


		if (empty($month_year)) {

			$month_year = date('Y-m');
			$search['district'] = $_SESSION['district'];
		}


		flash_form();

		  $search['duty_date'] = $month_year;
		  //dd($month_year);

		$totals = $this->reports_mdl->count_aggregated($search, $group_by);
		$route = "reports/attendance_aggregate";
		$per_page = (request_fields('rows')) ? request_fields('rows') : 144;
		$segment = 3;
		$page = ($this->uri->segment($segment)) ? $this->uri->segment($segment) : 0;

		$data['links'] = paginate($route, $totals, $per_page, $segment);

		if ($csv)
			$per_page = null;

		$data['records'] = $this->reports_mdl->attendance_aggregates($search, $per_page, $page, $group_by);
       // dd($this->db->last_query());
		if ($csv) {

			$this->export_aggregates_csv($data['records'], $group_by);
			return;
		}

		$data['module'] = $this->module;
		$data['search'] = (object) $search;
		$data['grouped_by'] = $group_by;
		$data['period'] = $month_year;
		$data['districts'] = $this->districts_mdl->get_all_Districts();
		$data['districts'] = $this->districts_mdl->get_all_Districts();
		$data['regions'] = $this->db->query("SELECT distinct region from ihrisdata WHERE region!='' ORDER BY region asc")->result();
		$data['institutiontypes'] = $this->db->query("SELECT distinct institutiontype_name from ihrisdata ORDER BY institutiontype_name asc")->result();
		$data['facilities'] = $this->facilities_mdl->getAll();

		$data['view'] = 'attendance_aggr';
		$data['title'] = 'Attendance Form Summary';
		$data['uptitle'] = 'Attendance Form Summary';


		$data['aggregations'] = ["job", "facility_name", "facility_type_name", "cadre", "institution_type", "district", "region", "department_id", "gender"];

		echo Modules::run('templates/main', $data);
	}

	public function export_aggregates_csv($data, $grouped_by)
	{

		$exportable = [
			array(
				str_replace("_", " ", strtoupper($grouped_by)),
				'DUTY DATE',
				'PRESENT',
				'LEAVE',
				'OFFICIAL REQUEST',
				'OFF DUTY',
				'HOLIDAY',
				'ABSENT',
				'% ACCOUNTED',
				'% ABSENTEESM'
			)
		];

		$total_present = 0;
		$total_leave = 0;
		$total_official = 0;
		$total_off = 0;
		$total_holiday = 0;
		$total_absent = 0;
		$total_supposed = 0;

		$total_attendance_rate = 0;
		$total_absentism_rate = 0;

		$count = 0;

		foreach ($data as $row) {

			$count++;

			$supposed_days = $row->days_supposed;
			$days_worked = ($row->days_supposed - $row->days_absent);


			$attendance_rate = number_format(($days_worked / $supposed_days) * 100, 1);
			$absentism_rate = number_format(($row->days_absent / $supposed_days) * 100, 1);


			$present = number_format(($row->present / $supposed_days) * 100, 1);
			$on_leave = number_format(($row->own_leave / $supposed_days) * 100, 1);
			$official = number_format(($row->official / $supposed_days) * 100, 1);
			$off = number_format(($row->off / $supposed_days) * 100, 1);
			$holiday = number_format(($row->holiday / $supposed_days) * 100, 1);
			$absent = number_format(($row->absent / $supposed_days) * 100, 1);

			$row = [$row->{$grouped_by}, $row->duty_date, $present, $on_leave, $official, $off, $holiday, $absent, $attendance_rate, $absentism_rate];

			$total_present += $present;
			$total_leave += $on_leave;
			$total_official += $official;
			$total_off += $off;
			$total_holiday += $holiday;
			$total_absent += $absent;

			$total_attendance_rate += $attendance_rate;
			$total_absentism_rate += $absentism_rate;

			array_push($exportable, $row);
		}
		//averages
		$footer_row = [
			"Averages: ",
			"Duty Date: ",
			number_format($total_present / $count, 1),
			number_format($total_leave / $count, 1),
			number_format($total_official / $count, 1),
			number_format($total_off / $count, 1),
			number_format($total_holiday / $count, 1),
			number_format($total_absent / $count, 1),
			number_format($total_attendance_rate / $count, 1),
			number_format($total_absentism_rate / $count, 1)
		];

		array_push($exportable, $footer_row);

		render_csv_data($exportable, "attendance_aggregates_" . time(), false);
	}
	public function person_attendance_all()
	{
		$search = request_fields();
		$year = request_fields('year');
		$month = request_fields('month');
		$csv = request_fields('csv');


		if (empty($year)) {
			$year = date('Y');
			$month = date('m');

			$search['year'] = $year;
			$search['month'] = $month;
			$search['district'] = $_SESSION['district'];
		}


		flash_form();

		$valid_rangeto = $year . "-" . $month;

		$search['duty_date'] = $valid_rangeto;

		$totals = $this->reports_mdl->count_person_attendance($search);
		$route = "reports/attendance_aggregate";
		$per_page = (request_fields('rows')) ? request_fields('rows') : 140;
		$segment = 3;
		$page = ($this->uri->segment($segment)) ? $this->uri->segment($segment) : 0;

		$data['links'] = paginate($route, $totals, $per_page, $segment);
		$data['records'] = $this->reports_mdl->person_attendance_all($search, $per_page, $page);
		// dd($data['records']);
		$data['csv'] = $this->reports_mdl->person_attendance_all($search);


		if ($csv) {

			$this->export_attendance_all_csv($data['csv'], $month, $year);
			return;
		}

		$data['module'] = $this->module;
		$data['search'] = (object) $search;
		$data['period'] = $valid_rangeto;
		$data['districts'] = $this->districts_mdl->get_all_Districts();
		$data['facilities'] = $this->facilities_mdl->getAll();

		$data['view'] = 'person_attendance_all';
		$data['title'] = 'Attendance Form Summary';
		$data['uptitle'] = 'Attendance Form Summary';


		$data['aggregations'] = ["job", "facility_name", "facility_type_name", "cadre", "institution_type", "district", "facility"];

		echo Modules::run('templates/main', $data);
	}

	public function export_attendance_all_csv($data, $month, $year)
	{

		$exportable = [
			array(
				'NAME',
				'DISTRICT',
				'FACILITY',
				'PERIOD',
				'PRESENT',
				'OFF DUTY',
				'OFFICIAL REQUEST',
				'LEAVE',
				'HOLIDAY',
				'ABSENT',
				'% ABSENTEESM'
			)
		];
		$p_total = 0;
		$o_total = 0;
		$r_total = 0;
		$l_total = 0;
		$h_total = 0;
		$a_total = 0;
		$ar_total = 0;
		$count = 0;
		foreach ($data as $row) {
			$count++;
			$p_total += $row->P;
			$o_total += $row->O;
			$r_total += $row->R;
			$l_total += $row->L;
			$h_total += $row->H;



			$month_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
			$absent = $month_days - ($row->P + $row->O + $row->R + $row->L);
			$abrate = number_format(($absent / $month_days), 1) * 100;

			$a_total += $absent;
			$ar_total += $abrate;

			$row = [$row->fullname, $row->district, $row->facility_name, $row->duty_date, $row->P, $row->O, $row->R, $row->L, $row->H, $absent, $abrate];

			array_push($exportable, $row);
		}
		$rowfoot = ["Averages", "", "", "", round(($p_total / $count), 0), round(($$o_total / $count), 0), round(($$r_total / $count), 0), round(($l_total / $count), 0), round(($h_total / $count), 0), round(($a_total / $count), 0), round(($ar_total / $count), 0)];

		array_push($exportable, $rowfoot);
		render_csv_data($exportable, "person_attendance_all" . time(), false);
	}
}