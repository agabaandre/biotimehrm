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

	/**
	 * Attendance per month graph (Financial Year Jun->May) using `actuals` table.
	 */
	public function attendanceActualsGraphData($year = null, $month = null, $empid = null)
	{
		$data = $this->reports_mdl->attendanceActualsGraphData(null, $year, $month, $empid);
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
		$year = $this->input->post('year');
		if ($year === null || $year === '') {
			$year = $this->input->get('year');
		}
		$data['title'] = 'Average Hours';
		$data['uptitle'] = "Average Monthly Hours";
		$data['view'] = 'average_hours';
		$data['module'] = $this->module;
		$data['year'] = $year !== null && $year !== '' ? $year : '';
		$data['sums'] = array();
		$data['links'] = '';
		echo Modules::run('templates/main', $data);
	}

	/**
	 * Server-side DataTables AJAX endpoint for average hours.
	 */
	public function average_hours_ajax()
	{
		$this->output->set_content_type('application/json');
		$draw = (int) $this->input->post('draw');
		$start = (int) $this->input->post('start');
		$length = (int) $this->input->post('length');
		$search = trim((string) $this->input->post('search')['value']);
		$year = trim((string) $this->input->post('year'));

		try {
			$total = $this->reports_mdl->count_average_hours($year, '');
			$filtered = $this->reports_mdl->count_average_hours($year, $search);
			$rows = $this->reports_mdl->average_hours_fetch($year, $start, $length, $search);

			$data = array();
			$row_num = $start + 1;
			foreach ($rows as $row) {
				$month_fmt = !empty($row['month_year']) ? date('F, Y', strtotime($row['month_year'] . '-01')) : '';
				$data[] = array(
					$row_num++,
					$month_fmt,
					isset($row['avg_hours']) ? number_format((float) $row['avg_hours'], 2) : ''
				);
			}
			echo json_encode(array(
				'draw' => $draw,
				'recordsTotal' => $total,
				'recordsFiltered' => $filtered,
				'data' => $data
			));
		} catch (Throwable $e) {
			log_message('error', 'average_hours_ajax: ' . $e->getMessage());
			echo json_encode(array(
				'draw' => $draw,
				'recordsTotal' => 0,
				'recordsFiltered' => 0,
				'data' => array(),
				'error' => 'Failed to load data'
			));
		}
	}

	/**
	 * PDF export for average hours (streamed batches, logo + watermark).
	 */
	public function print_average()
	{
		$year = trim((string) $this->input->get('year'));
		$this->load->library('M_pdf');
		@set_time_limit(0);
		$batch_size = 80;
		$total = $this->reports_mdl->count_average_hours($year, '');
		$fac = isset($_SESSION['facility_name']) ? $_SESSION['facility_name'] : 'Report';
		$filename = $fac . '_Average_Hours_' . date('Y-m-d') . '.pdf';

		$watermark_path = FCPATH . 'assets/images/watermark.png';
		if (!empty($watermark_path) && is_file($watermark_path)) {
			$this->m_pdf->pdf->SetWatermarkImage($watermark_path);
			$this->m_pdf->pdf->showWatermarkImage = true;
		}
		date_default_timezone_set('Africa/Kampala');
		$this->m_pdf->pdf->SetHTMLFooter('Printed/ Accessed on: <b>' . date('d F,Y h:i A') . '</b><br style="font-size: 9px;">Source: iHRIS - HRM Attend ' . base_url());

		$moh_logo = (defined('FCPATH') && is_file(FCPATH . 'assets/img/MOH.png')) ? FCPATH . 'assets/img/MOH.png' : '';
		$header_data = array('facility_name' => $fac, 'moh_logo_path' => $moh_logo);
		$header_html = $this->load->view('averagehours_pdf_header', $header_data, true);
		$this->m_pdf->pdf->WriteHTML(mb_convert_encoding($header_html, 'UTF-8', 'UTF-8'));

		$row_no = 1;
		for ($start = 0; $start < $total; $start += $batch_size) {
			$batch = $this->reports_mdl->average_hours_fetch($year, $start, $batch_size, '');
			if (empty($batch)) {
				break;
			}
			$rows_data = array('sums' => $batch, 'start_row_no' => $row_no);
			$rows_html = $this->load->view('averagehours_pdf_rows', $rows_data, true);
			$this->m_pdf->pdf->WriteHTML(mb_convert_encoding($rows_html, 'UTF-8', 'UTF-8'));
			$row_no += count($batch);
			unset($batch, $rows_data, $rows_html);
		}
		$footer_html = $this->load->view('averagehours_pdf_footer', array(), true);
		$this->m_pdf->pdf->WriteHTML(mb_convert_encoding($footer_html, 'UTF-8', 'UTF-8'));
		$this->m_pdf->pdf->Output($filename, 'I');
	}


	public function attendance_aggregate()
	{
		// Handle CSV export (streamed, no full load)
		$csv = request_fields('csv');
		if ($csv) {
			$filters = $this->_aggregate_filters_from_request();
			$group_by = (!empty(request_fields('group_by'))) ? request_fields('group_by') : 'district';
			$this->export_aggregates_csv_stream($filters, $group_by);
			return;
		}

		// Handle PDF export (streamed)
		$pdf = request_fields('pdf');
		if ($pdf) {
			$filters = $this->_aggregate_filters_from_request();
			$group_by = (!empty(request_fields('group_by'))) ? request_fields('group_by') : 'district';
			$this->attendance_aggregate_pdf($filters, $group_by);
			return;
		}

		// Default values for view
		$month_year = request_fields('duty_date');
		if (empty($month_year)) {
			$month_year = date('Y-m');
		}

		$data['module'] = $this->module;
		$data['grouped_by'] = (!empty(request_fields('group_by'))) ? request_fields('group_by') : "district";
		$data['period'] = $month_year;
		$data['districts'] = $this->districts_mdl->get_all_Districts();
		$data['regions'] = $this->db->query("SELECT distinct region from ihrisdata WHERE region!='' ORDER BY region asc")->result();
		$data['institutiontypes'] = $this->db->query("SELECT distinct institutiontype_name from ihrisdata ORDER BY institutiontype_name asc")->result();
		$data['facilities'] = $this->facilities_mdl->getAll();
		$data['aggregations'] = ["job", "facility_name", "facility_type_name", "cadre", "institution_type", "district", "region", "department_id", "gender"];

		$data['view'] = 'attendance_aggr';
		$data['title'] = 'Attendance Form Summary';
		$data['uptitle'] = 'Attendance Form Summary';

		echo Modules::run('templates/main', $data);
	}

	/**
	 * Server-side DataTables AJAX endpoint for attendance aggregates.
	 */
	public function attendanceAggregateAjax()
	{
		$this->output->set_content_type('application/json');

		$draw = (int) $this->input->post('draw');
		$start = (int) $this->input->post('start');
		$length = (int) $this->input->post('length');
		$search = trim((string) $this->input->post('search')['value']);

		// Get filters from POST
		$filters = array();
		$filters['district'] = $this->input->post('district');
		$filters['facility_name'] = $this->input->post('facility_name');
		$filters['region'] = $this->input->post('region');
		$filters['institution_type'] = $this->input->post('institution_type');
		$filters['duty_date'] = $this->input->post('duty_date');
		$group_by = $this->input->post('group_by') ?: 'district';

		// Remove empty filters
		$filters = array_filter($filters, function($value) {
			return !empty($value);
		});

		// Default duty_date if not provided
		if (empty($filters['duty_date'])) {
			$filters['duty_date'] = date('Y-m');
		}

		// If duty_date is string, convert to array
		if (is_string($filters['duty_date'])) {
			$filters['duty_date'] = array($filters['duty_date']);
		}

		try {
			// Counts
			$total_records = $this->reports_mdl->countAttendanceAggregatesAjax($filters, $group_by, '');
			$filtered_records = $this->reports_mdl->countAttendanceAggregatesAjax($filters, $group_by, $search);

			// Fetch data
			$records = $this->reports_mdl->fetchAttendanceAggregatesAjax($filters, $group_by, $start, $length, $search);

			// Format data for DataTables
			$data = array();
			$row_num = $start + 1;
			foreach ($records as $row) {
				$supposed_days = $row->days_supposed ?? 0;
				$days_worked = ($supposed_days - ($row->days_absent ?? 0));

				// Prevent division by zero
				if ($supposed_days > 0) {
					$attendance_rate = ($days_worked / $supposed_days) * 100;
					$absentism_rate = (($row->days_absent ?? 0) / $supposed_days) * 100;
					$present = (($row->present ?? 0) / $supposed_days) * 100;
					$on_leave = (($row->own_leave ?? 0) / $supposed_days) * 100;
					$official = (($row->official ?? 0) / $supposed_days) * 100;
					$off = (($row->off ?? 0) / $supposed_days) * 100;
					$holiday = (($row->holiday ?? 0) / $supposed_days) * 100;
					$absent = (($row->absent ?? 0) / $supposed_days) * 100;
				} else {
					$attendance_rate = 0;
					$absentism_rate = 0;
					$present = 0;
					$on_leave = 0;
					$official = 0;
					$off = 0;
					$holiday = 0;
					$absent = 0;
				}

				$grouped_value = $row->{$group_by} ?? 'N/A';

				$data[] = array(
					$row_num++,
					$grouped_value,
					$row->duty_date ?? '',
					number_format($present, 1) . '%',
					number_format($off, 1) . '%',
					number_format($official, 1) . '%',
					number_format($on_leave, 1) . '%',
					number_format($holiday, 1) . '%',
					number_format($absent, 1) . '%',
					number_format($days_worked, 1),
					number_format($supposed_days, 1),
					number_format($attendance_rate, 1) . '%',
					number_format($absentism_rate, 1) . '%'
				);
			}

			echo json_encode(array(
				'draw' => $draw,
				'recordsTotal' => $total_records,
				'recordsFiltered' => $filtered_records,
				'data' => $data
			));
			exit;
		} catch (Throwable $e) {
			log_message('error', 'attendanceAggregateAjax error: ' . $e->getMessage());
			echo json_encode(array(
				'draw' => $draw,
				'recordsTotal' => 0,
				'recordsFiltered' => 0,
				'data' => array(),
				'error' => 'Failed to load attendance aggregate data'
			));
			exit;
		}
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


			// Prevent division by zero
			if ($supposed_days > 0) {
				$attendance_rate = number_format(($days_worked / $supposed_days) * 100, 1);
				$absentism_rate = number_format(($row->days_absent / $supposed_days) * 100, 1);
				$present = number_format(($row->present / $supposed_days) * 100, 1);
				$on_leave = number_format(($row->own_leave / $supposed_days) * 100, 1);
				$official = number_format(($row->official / $supposed_days) * 100, 1);
				$off = number_format(($row->off / $supposed_days) * 100, 1);
				$holiday = number_format(($row->holiday / $supposed_days) * 100, 1);
				$absent = number_format(($row->absent / $supposed_days) * 100, 1);
			} else {
				$attendance_rate = 0;
				$absentism_rate = 0;
				$present = 0;
				$on_leave = 0;
				$official = 0;
				$off = 0;
				$holiday = 0;
				$absent = 0;
			}

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
		//averages - prevent division by zero
		$avg_divisor = ($count > 0) ? $count : 1;
		$footer_row = [
			"Averages: ",
			"Duty Date: ",
			number_format($total_present / $avg_divisor, 1),
			number_format($total_leave / $avg_divisor, 1),
			number_format($total_official / $avg_divisor, 1),
			number_format($total_off / $avg_divisor, 1),
			number_format($total_holiday / $avg_divisor, 1),
			number_format($total_absent / $avg_divisor, 1),
			number_format($total_attendance_rate / $avg_divisor, 1),
			number_format($total_absentism_rate / $avg_divisor, 1)
		];

		array_push($exportable, $footer_row);

		render_csv_data($exportable, "attendance_aggregates_" . time(), false);
	}

	/**
	 * Build filters array for aggregate report from request (GET/POST).
	 */
	private function _aggregate_filters_from_request()
	{
		$filters = array();
		$filters['district'] = request_fields('district');
		$filters['facility_name'] = request_fields('facility_name');
		$filters['region'] = request_fields('region');
		$filters['institution_type'] = request_fields('institution_type');
		$filters['duty_date'] = request_fields('duty_date');
		$filters = array_filter($filters, function ($v) { return $v !== '' && $v !== null && $v !== array(); });
		if (empty($filters['duty_date'])) {
			$filters['duty_date'] = date('Y-m');
		}
		if (is_string($filters['duty_date'])) {
			$filters['duty_date'] = array($filters['duty_date']);
		}
		return $filters;
	}

	/**
	 * Stream CSV export for attendance aggregates (batch fetch, no full load).
	 */
	public function export_aggregates_csv_stream($filters, $grouped_by)
	{
		$batch_size = 200;
		$total = $this->reports_mdl->countAttendanceAggregatesAjax($filters, $grouped_by, '');
		$filename = 'attendance_aggregates_' . date('Y-m-d_His') . '.csv';
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		$fh = fopen('php://output', 'w');
		ini_set('max_execution_time', 0);

		$header = array(
			str_replace('_', ' ', strtoupper($grouped_by)),
			'DUTY DATE',
			'PRESENT',
			'LEAVE',
			'OFFICIAL REQUEST',
			'OFF DUTY',
			'HOLIDAY',
			'ABSENT',
			'% ACCOUNTED',
			'% ABSENTEESM'
		);
		fputcsv($fh, $header);

		$total_present = 0;
		$total_leave = 0;
		$total_official = 0;
		$total_off = 0;
		$total_holiday = 0;
		$total_absent = 0;
		$total_attendance_rate = 0;
		$total_absentism_rate = 0;
		$count = 0;

		for ($start = 0; $start < $total; $start += $batch_size) {
			$records = $this->reports_mdl->fetchAttendanceAggregatesAjax($filters, $grouped_by, $start, $batch_size, '');
			if (empty($records)) {
				break;
			}
			foreach ($records as $row) {
				$supposed_days = isset($row->days_supposed) ? $row->days_supposed : 0;
				$days_worked = $supposed_days - (isset($row->days_absent) ? $row->days_absent : 0);
				if ($supposed_days > 0) {
					$attendance_rate = number_format(($days_worked / $supposed_days) * 100, 1);
					$absentism_rate = number_format((isset($row->days_absent) ? $row->days_absent : 0) / $supposed_days * 100, 1);
					$present = number_format((isset($row->present) ? $row->present : 0) / $supposed_days * 100, 1);
					$on_leave = number_format((isset($row->own_leave) ? $row->own_leave : 0) / $supposed_days * 100, 1);
					$official = number_format((isset($row->official) ? $row->official : 0) / $supposed_days * 100, 1);
					$off = number_format((isset($row->off) ? $row->off : 0) / $supposed_days * 100, 1);
					$holiday = number_format((isset($row->holiday) ? $row->holiday : 0) / $supposed_days * 100, 1);
					$absent = number_format((isset($row->absent) ? $row->absent : 0) / $supposed_days * 100, 1);
				} else {
					$attendance_rate = $absentism_rate = $present = $on_leave = $official = $off = $holiday = $absent = 0;
				}
				$group_val = isset($row->{$grouped_by}) ? $row->{$grouped_by} : 'N/A';
				fputcsv($fh, array($group_val, $row->duty_date ?? '', $present, $on_leave, $official, $off, $holiday, $absent, $attendance_rate, $absentism_rate));
				$total_present += (float) $present;
				$total_leave += (float) $on_leave;
				$total_official += (float) $official;
				$total_off += (float) $off;
				$total_holiday += (float) $holiday;
				$total_absent += (float) $absent;
				$total_attendance_rate += (float) $attendance_rate;
				$total_absentism_rate += (float) $absentism_rate;
				$count++;
			}
			unset($records);
		}

		$avg_divisor = $count > 0 ? $count : 1;
		fputcsv($fh, array(
			'Averages:',
			'Duty Date:',
			number_format($total_present / $avg_divisor, 1),
			number_format($total_leave / $avg_divisor, 1),
			number_format($total_official / $avg_divisor, 1),
			number_format($total_off / $avg_divisor, 1),
			number_format($total_holiday / $avg_divisor, 1),
			number_format($total_absent / $avg_divisor, 1),
			number_format($total_attendance_rate / $avg_divisor, 1),
			number_format($total_absentism_rate / $avg_divisor, 1)
		));
		fclose($fh);
		exit;
	}

	/**
	 * Stream PDF export for attendance aggregates (batch fetch, chunked WriteHTML).
	 */
	public function attendance_aggregate_pdf($filters, $grouped_by)
	{
		$this->load->library('M_pdf');
		$batch_size = 80;
		$total = $this->reports_mdl->countAttendanceAggregatesAjax($filters, $grouped_by, '');
		$period_label = is_array($filters['duty_date']) ? implode(', ', $filters['duty_date']) : (isset($filters['duty_date']) ? $filters['duty_date'] : date('Y-m'));
		$filename = 'attendance_aggregates_' . date('Y-m-d_His') . '.pdf';
		ini_set('max_execution_time', 0);

		$watermark_path = FCPATH . 'assets/images/watermark.png';
		if (!empty($watermark_path) && is_file($watermark_path)) {
			$this->m_pdf->pdf->SetWatermarkImage($watermark_path);
			$this->m_pdf->pdf->showWatermarkImage = true;
		}
		date_default_timezone_set('Africa/Kampala');
		$this->m_pdf->pdf->SetHTMLFooter('Printed / Accessed on: <b>' . date('d F,Y h:i A') . '</b><br style="font-size: 9px;">Source: iHRIS - HRM Attend ' . base_url());

		$moh_logo = (defined('FCPATH') && is_file(FCPATH . 'assets/img/MOH.png')) ? FCPATH . 'assets/img/MOH.png' : '';
		$header_data = array(
			'grouped_by' => $grouped_by,
			'period_label' => $period_label,
			'moh_logo_path' => $moh_logo,
		);
		$header_html = $this->load->view('attendance_aggregate_pdf_header', $header_data, true);
		$this->m_pdf->pdf->WriteHTML(mb_convert_encoding($header_html, 'UTF-8', 'UTF-8'));

		$row_no = 1;
		for ($start = 0; $start < $total; $start += $batch_size) {
			$records = $this->reports_mdl->fetchAttendanceAggregatesAjax($filters, $grouped_by, $start, $batch_size, '');
			if (empty($records)) {
				break;
			}
			$rows_data = array(
				'records' => $records,
				'grouped_by' => $grouped_by,
				'start_row_no' => $row_no,
			);
			$rows_html = $this->load->view('attendance_aggregate_pdf_rows', $rows_data, true);
			$this->m_pdf->pdf->WriteHTML(mb_convert_encoding($rows_html, 'UTF-8', 'UTF-8'));
			$row_no += count($records);
			unset($records, $rows_data, $rows_html);
		}

		$footer_html = $this->load->view('attendance_aggregate_pdf_footer', array(), true);
		$this->m_pdf->pdf->WriteHTML(mb_convert_encoding($footer_html, 'UTF-8', 'UTF-8'));
		$this->m_pdf->pdf->Output($filename, 'I');
	}

	public function person_attendance_all()
	{
		$search = request_fields();
		$year = request_fields('year');
		$month = request_fields('month');
		$csv = request_fields('csv');
		$pdf = request_fields('pdf');

		if (empty($year)) {
			$year = date('Y');
			$month = date('m');
			$search['year'] = $year;
			$search['month'] = $month;
			if (!empty($_SESSION['district'])) {
				$search['district'] = $_SESSION['district'];
			}
		}

		flash_form();

		$valid_rangeto = $year . '-' . $month;
		$search['duty_date'] = $valid_rangeto;

		if ($csv) {
			$this->export_attendance_all_csv_stream($search, $month, $year);
			return;
		}
		if ($pdf) {
			$this->person_attendance_all_pdf($search, $month, $year);
			return;
		}

		$data['module'] = $this->module;
		$data['search'] = (object) $search;
		$data['period'] = $valid_rangeto;
		$data['month'] = $month;
		$data['year'] = $year;
		$data['districts'] = $this->districts_mdl->get_all_Districts();
		$data['facilities'] = $this->facilities_mdl->getAll();
		$data['links'] = '';
		$data['records'] = array();

		$data['view'] = 'person_attendance_all';
		$data['title'] = 'Person Attendance All';
		$data['uptitle'] = 'Person Attendance All';

		echo Modules::run('templates/main', $data);
	}

	/**
	 * Server-side DataTables AJAX endpoint for person attendance all.
	 */
	public function person_attendance_all_ajax()
	{
		$this->output->set_content_type('application/json');

		$draw = (int) $this->input->post('draw');
		$start = (int) $this->input->post('start');
		$length = (int) $this->input->post('length');
		$search = trim((string) $this->input->post('search')['value']);
		$month = trim((string) $this->input->post('month'));
		$year = trim((string) $this->input->post('year'));
		$district = trim((string) $this->input->post('district'));
		$facility_name = trim((string) $this->input->post('facility_name'));

		if (empty($year) || empty($month)) {
			$year = date('Y');
			$month = date('m');
		}
		$duty_date = $year . '-' . $month;
		$filters = array('duty_date' => $duty_date);
		if ($district !== '') {
			$filters['district'] = $district;
		}
		if ($facility_name !== '') {
			$filters['facility_name'] = $facility_name;
		}

		try {
			$total_records = $this->reports_mdl->count_person_attendance($filters, '');
			$filtered_records = $this->reports_mdl->count_person_attendance($filters, $search);
			$rows = $this->reports_mdl->person_attendance_all($filters, $length, $start, $search);

			$month_days = cal_days_in_month(CAL_GREGORIAN, (int) $month, (int) $year);
			$data = array();
			$row_num = $start + 1;
			foreach ($rows as $row) {
				$p = isset($row->P) ? (int) $row->P : 0;
				$o = isset($row->O) ? (int) $row->O : 0;
				$r = isset($row->R) ? (int) $row->R : 0;
				$l = isset($row->L) ? (int) $row->L : 0;
				$h = isset($row->H) ? (int) $row->H : 0;
				$absent = $month_days - ($p + $o + $r + $l);
				$abrate = $month_days > 0 ? number_format(($absent / $month_days) * 100, 1) : 0;
				$data[] = array(
					$row_num++,
					isset($row->fullname) ? $row->fullname : '',
					isset($row->district) ? $row->district : '',
					isset($row->facility_name) ? $row->facility_name : '',
					isset($row->duty_date) ? $row->duty_date : $duty_date,
					$p,
					$o,
					$r,
					$l,
					$h,
					$absent,
					$abrate . '%'
				);
			}

			echo json_encode(array(
				'draw' => $draw,
				'recordsTotal' => $total_records,
				'recordsFiltered' => $filtered_records,
				'data' => $data
			));
			return;
		} catch (Throwable $e) {
			log_message('error', 'person_attendance_all_ajax: ' . $e->getMessage());
			echo json_encode(array(
				'draw' => $draw,
				'recordsTotal' => 0,
				'recordsFiltered' => 0,
				'data' => array(),
				'error' => 'Failed to load data'
			));
		}
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
			$abrate = ($month_days > 0) ? number_format(($absent / $month_days), 1) * 100 : 0;

			$a_total += $absent;
			$ar_total += $abrate;

			$row = [$row->fullname, $row->district, $row->facility_name, $row->duty_date, $row->P, $row->O, $row->R, $row->L, $row->H, $absent, $abrate];

			array_push($exportable, $row);
		}
		$rowfoot = ["Averages", "", "", "", round(($count > 0 ? $p_total / $count : 0), 0), round(($count > 0 ? $o_total / $count : 0), 0), round(($count > 0 ? $r_total / $count : 0), 0), round(($count > 0 ? $l_total / $count : 0), 0), round(($count > 0 ? $h_total / $count : 0), 0), round(($count > 0 ? $a_total / $count : 0), 0), round(($count > 0 ? $ar_total / $count : 0), 0)];

		array_push($exportable, $rowfoot);
		render_csv_data($exportable, "person_attendance_all" . time(), false);
	}

	/**
	 * Stream CSV export for person attendance all (batch fetch, flush to avoid DB locks).
	 */
	public function export_attendance_all_csv_stream($search, $month, $year)
	{
		@set_time_limit(0);
		$batch_size = 80;
		$total = $this->reports_mdl->count_person_attendance($search, '');
		$month_days = cal_days_in_month(CAL_GREGORIAN, (int) $month, (int) $year);
		$filename = 'person_attendance_all_' . date('Y-m-d_His') . '.csv';
		header('Content-Type: text/csv; charset=UTF-8');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Cache-Control: no-cache, must-revalidate');
		$fh = fopen('php://output', 'w');

		fputcsv($fh, array('NAME', 'DISTRICT', 'FACILITY', 'PERIOD', 'PRESENT', 'OFF DUTY', 'OFFICIAL REQUEST', 'LEAVE', 'HOLIDAY', 'ABSENT', '% ABSENTEESM'));
		if (ob_get_level() > 0) ob_flush();
		flush();

		$p_total = $o_total = $r_total = $l_total = $h_total = $a_total = $ar_total = 0;
		$count = 0;

		for ($start = 0; $start < $total; $start += $batch_size) {
			$batch = $this->reports_mdl->person_attendance_all($search, $batch_size, $start, '');
			if (empty($batch)) {
				break;
			}
			foreach ($batch as $row) {
				$count++;
				$p_total += isset($row->P) ? $row->P : 0;
				$o_total += isset($row->O) ? $row->O : 0;
				$r_total += isset($row->R) ? $row->R : 0;
				$l_total += isset($row->L) ? $row->L : 0;
				$h_total += isset($row->H) ? $row->H : 0;
				$absent = $month_days - (isset($row->P) ? $row->P : 0) - (isset($row->O) ? $row->O : 0) - (isset($row->R) ? $row->R : 0) - (isset($row->L) ? $row->L : 0);
				$abrate = $month_days > 0 ? number_format(($absent / $month_days) * 100, 1) : 0;
				$a_total += $absent;
				$ar_total += (float) $abrate;
				fputcsv($fh, array(
					isset($row->fullname) ? $row->fullname : '',
					isset($row->district) ? $row->district : '',
					isset($row->facility_name) ? $row->facility_name : '',
					isset($row->duty_date) ? $row->duty_date : '',
					isset($row->P) ? $row->P : 0,
					isset($row->O) ? $row->O : 0,
					isset($row->R) ? $row->R : 0,
					isset($row->L) ? $row->L : 0,
					isset($row->H) ? $row->H : 0,
					$absent,
					$abrate
				));
			}
			unset($batch);
			if (ob_get_level() > 0) ob_flush();
			flush();
		}

		$avg_div = $count > 0 ? $count : 1;
		fputcsv($fh, array(
			'Averages', '', '', '',
			round($p_total / $avg_div, 0), round($o_total / $avg_div, 0), round($r_total / $avg_div, 0),
			round($l_total / $avg_div, 0), round($h_total / $avg_div, 0), round($a_total / $avg_div, 0),
			round($ar_total / $avg_div, 0)
		));
		fclose($fh);
		exit;
	}

	/**
	 * Stream PDF export for person attendance all (batch fetch, chunked WriteHTML).
	 */
	public function person_attendance_all_pdf($search, $month, $year)
	{
		$this->load->library('M_pdf');
		$batch_size = 80;
		$total = $this->reports_mdl->count_person_attendance($search);
		$month_days = cal_days_in_month(CAL_GREGORIAN, (int) $month, (int) $year);
		$period_label = date('F, Y', strtotime($year . '-' . $month . '-01'));
		$filename = 'person_attendance_all_' . date('Y-m-d_His') . '.pdf';
		ini_set('max_execution_time', 0);

		$watermark_path = FCPATH . 'assets/images/watermark.png';
		if (!empty($watermark_path) && is_file($watermark_path)) {
			$this->m_pdf->pdf->SetWatermarkImage($watermark_path);
			$this->m_pdf->pdf->showWatermarkImage = true;
		}
		date_default_timezone_set('Africa/Kampala');
		$this->m_pdf->pdf->SetHTMLFooter('Printed / Accessed on: <b>' . date('d F,Y h:i A') . '</b><br style="font-size: 9px;">Source: iHRIS - HRM Attend ' . base_url());

		$moh_logo = (defined('FCPATH') && is_file(FCPATH . 'assets/img/MOH.png')) ? FCPATH . 'assets/img/MOH.png' : '';
		$facility_name = isset($_SESSION['facility_name']) ? $_SESSION['facility_name'] : '';
		$header_data = array('period_label' => $period_label, 'moh_logo_path' => $moh_logo, 'facility_name' => $facility_name);
		$header_html = $this->load->view('person_attendance_all_pdf_header', $header_data, true);
		$this->m_pdf->pdf->WriteHTML(mb_convert_encoding($header_html, 'UTF-8', 'UTF-8'));

		$row_no = 1;
		for ($start = 0; $start < $total; $start += $batch_size) {
			$batch = $this->reports_mdl->person_attendance_all($search, $batch_size, $start);
			if (empty($batch)) {
				break;
			}
			$rows_data = array(
				'records' => $batch,
				'month' => $month,
				'year' => $year,
				'start_row_no' => $row_no,
			);
			$rows_html = $this->load->view('person_attendance_all_pdf_rows', $rows_data, true);
			$this->m_pdf->pdf->WriteHTML(mb_convert_encoding($rows_html, 'UTF-8', 'UTF-8'));
			$row_no += count($batch);
			unset($batch, $rows_data, $rows_html);
		}

		$footer_html = $this->load->view('person_attendance_all_pdf_footer', array(), true);
		$this->m_pdf->pdf->WriteHTML(mb_convert_encoding($footer_html, 'UTF-8', 'UTF-8'));
		$this->m_pdf->pdf->Output($filename, 'I');
	}
}