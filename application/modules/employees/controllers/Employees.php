<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Employees extends MX_Controller
{     
  protected $user;
  protected $watermark;
  protected $filters;
  protected $ufilters;
  protected $distfilters;
  
  public  function __construct()
  {
    parent::__construct();
    $this->load->model('employee_model', 'empModel');
    $this->user = $this->session->get_userdata();
    $this->load->library('pagination');
    $this->watermark = FCPATH . "assets/images/watermark.png";
    //requires a join on ihrisdata
    $this->filters = Modules::run('filters/sessionfilters');
    //doesnt require a join on ihrisdata
    $this->ufilters = Modules::run('filters/universalfilters');
    // requires a join on ihrisdata with district level
    $this->distfilters = Modules::run('filters/districtfilters');
  }
  public function filters()
  {
    print_r($this->filters);
  }
  public function get_employees()
  {
    return $employees = $this->empModel->get_employees($this->filters);
  }


  /**
   * All iHRIS Staff (nationwide) with dropdown filters. Permission 15 required.
   */
  public function all_ihris_staff()
  {
    $perms = $this->session->userdata('permissions');
    if (!is_array($perms) || (!in_array('15', $perms) && !in_array(15, $perms))) {
      show_404();
      return;
    }
    if ($this->input->is_ajax_request()) {
      $this->_handleAllIhrisAjaxRequest();
      return;
    }
    $data['filter_options'] = $this->empModel->get_all_ihris_filter_options();
    $data['view'] = 'staff_all_ihris';
    $data['uptitle'] = 'All iHRIS Staff';
    $data['module'] = 'employees';
    $data['can_mark_disabled'] = true;
    echo Modules::run('templates/main', $data);
  }

  private function _handleAllIhrisAjaxRequest()
  {
    $draw = (int) $this->input->post('draw');
    $start = (int) $this->input->post('start');
    $length = (int) $this->input->post('length');
    $search = $this->input->post('search')['value'] ?? '';
    $order_column = (int) $this->input->post('order')[0]['column'];
    $order_dir = $this->input->post('order')[0]['dir'] === 'desc' ? 'desc' : 'asc';
    $district = $this->input->post('district');
    $facility = $this->input->post('facility');
    $job = $this->input->post('job');
    $institution_type = $this->input->post('institution_type');
    $facility_type = $this->input->post('facility_type');
    $globalSearch = $this->input->post('globalSearch');
    $include_inactive = (bool) $this->input->post('includeInactive');
    $search = $globalSearch !== null && $globalSearch !== '' ? $globalSearch : $search;
    if (is_string($facility) && $facility !== '') {
      $facility = array_filter(explode(',', $facility));
    }
    if (is_string($job) && $job !== '') {
      $job = array_filter(explode(',', $job));
    }
    $total_records = $this->empModel->all_ihris_staff_count($district, $facility, $job, $institution_type, $facility_type, $search, $include_inactive);
    $data = $this->empModel->all_ihris_staff_ajax($district, $facility, $job, $institution_type, $facility_type, $start, $length, $search, $order_column, $order_dir, $include_inactive);
    $this->output->set_content_type('application/json')->set_output(json_encode([
      'draw' => $draw,
      'recordsTotal' => $total_records,
      'recordsFiltered' => $total_records,
      'data' => $data
    ]));
  }

  public function district_employees($csv = FALSE)
  {
    // Handle AJAX requests for pagination
    if ($this->input->is_ajax_request()) {
      $this->_handleAjaxRequest();
      return;
    }
    
    $job = $this->input->post('job');
    $facility = $this->input->post('facility');
    $route = "employees/district_employees";

    // Get total count for pagination
    $totals = $this->empModel->district_employees($_SESSION['district'], $job, $facility, $count = 'count', $perPage = 0, $page = 0, $csv);
    
    if ($csv != 1) {
      $data['links'] = paginate($route, $totals, $perPage = 50, $segment = 3);
    }
    
    $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
    $data['staffs'] = $this->empModel->district_employees($_SESSION['district'], $job, $facility, $count = FALSE, $perPage = 50, $page, $csv);
    
    if ($csv == 1) {
      $filename = $_SESSION['district'] . "_employees.csv";
      render_csv_data($data['staffs'], $filename);
    }

    $data['view'] = 'staff_district';
    $data['uptitle'] = "District Employees";
    $data['module'] = "employees";
    $data['total_count'] = $totals;
    $data['current_page'] = $page;
    $data['per_page'] = 50;
    
    echo Modules::run("templates/main", $data);
  }
  
  /**
   * Handle AJAX requests for main employees page server-side pagination
   */
  private function _handleMainEmployeesAjaxRequest() {
    $draw = $this->input->post('draw');
    $start = $this->input->post('start');
    $length = $this->input->post('length');
    $search = $this->input->post('search')['value'];
    $order_column = $this->input->post('order')[0]['column'];
    $order_dir = $this->input->post('order')[0]['dir'];
    
    // Get global search and include inactive filter
    $globalSearch = $this->input->post('globalSearch');
    $include_inactive = (bool) $this->input->post('includeInactive');
    
    // Get total count
    $total_records = $this->empModel->get_employees_count($this->filters, '', $include_inactive);
    
    // Get filtered data
    $data = $this->empModel->get_employees_ajax(
      $this->filters,
      $start, 
      $length, 
      $search, 
      $order_column, 
      $order_dir,
      $globalSearch,
      $include_inactive
    );
    
    // Get filtered count for search
    $filtered_records = $this->empModel->get_employees_count($this->filters, $globalSearch, $include_inactive);
    
    // Prepare response
    $response = [
      'draw' => intval($draw),
      'recordsTotal' => $total_records,
      'recordsFiltered' => $filtered_records,
      'data' => $data
    ];
    
    $this->output->set_content_type('application/json')->set_output(json_encode($response));
  }

  /**
   * Handle AJAX requests for server-side pagination
   */
  private function _handleAjaxRequest() {
    $draw = $this->input->post('draw');
    $start = $this->input->post('start');
    $length = $this->input->post('length');
    $search = $this->input->post('search')['value'];
    $order_column = $this->input->post('order')[0]['column'];
    $order_dir = $this->input->post('order')[0]['dir'];
    
    // Get filters and include inactive
    $job = $this->input->post('job');
    $facility = $this->input->post('facility');
    $include_inactive = (bool) $this->input->post('includeInactive');
    
    // Get total count
    $total_records = $this->empModel->district_employees($_SESSION['district'], $job, $facility, 'count', 0, 0, FALSE, $include_inactive);
    
    // Get filtered data
    $data = $this->empModel->district_employees_ajax(
      $_SESSION['district'], 
      $job, 
      $facility, 
      $start, 
      $length, 
      $search, 
      $order_column, 
      $order_dir,
      $include_inactive
    );
    
    // Prepare response
    $response = [
      'draw' => intval($draw),
      'recordsTotal' => $total_records,
      'recordsFiltered' => $total_records,
      'data' => $data
    ];
    
    $this->output->set_content_type('application/json')->set_output(json_encode($response));
  }
  public function getEmployee($id)
  {
    $employee = $this->empModel->get_employee($id);
    return  $employee;
  }

  /**
   * Mark staff as disabled (Former Staff). Permission 15 required. AJAX: POST ihris_pid.
   */
  public function setStaffDisabled()
  {
    $this->output->set_content_type('application/json');
    $perms = $this->session->userdata('permissions');
    if (!is_array($perms) || !in_array('15', $perms)) {
      $this->output->set_output(json_encode(array('success' => false, 'message' => 'Permission denied.', 'status' => null)));
      return;
    }
    $ihris_pid = $this->input->post('ihris_pid');
    if (empty($ihris_pid)) {
      $this->output->set_output(json_encode(array('success' => false, 'message' => 'ihris_pid required.', 'status' => null)));
      return;
    }
    $ok = $this->empModel->set_staff_status($ihris_pid, 0);
    $this->output->set_output(json_encode(array(
      'success' => $ok,
      'message' => $ok ? 'Marked as Former Staff.' : 'Update failed or is_active_employee column missing.',
      'status' => 0,
      'status_label' => 'Former Staff'
    )));
  }

  /**
   * Mark staff as enabled (Active). Permission 15 required. AJAX: POST ihris_pid.
   */
  public function setStaffEnabled()
  {
    $this->output->set_content_type('application/json');
    $perms = $this->session->userdata('permissions');
    if (!is_array($perms) || !in_array('15', $perms)) {
      $this->output->set_output(json_encode(array('success' => false, 'message' => 'Permission denied.', 'status' => null)));
      return;
    }
    $ihris_pid = $this->input->post('ihris_pid');
    if (empty($ihris_pid)) {
      $this->output->set_output(json_encode(array('success' => false, 'message' => 'ihris_pid required.', 'status' => null)));
      return;
    }
    $ok = $this->empModel->set_staff_status($ihris_pid, 1);
    $this->output->set_output(json_encode(array(
      'success' => $ok,
      'message' => $ok ? 'Marked as Active.' : 'Update failed or is_active_employee column missing.',
      'status' => 1,
      'status_label' => 'Active'
    )));
  }

  /**
   * Stream all staff as CSV (respects current filters, GET: search, includeInactive). Export all rows.
   */
  public function export_staff_csv()
  {
    $search = $this->input->get('search');
    $include_inactive = (bool) $this->input->get('includeInactive');
    $total = $this->empModel->get_employees_count($this->filters, (string) $search, $include_inactive);
    $filename = 'staff_' . date('Y-m-d_His') . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache');
    $fh = fopen('php://output', 'w');
    fprintf($fh, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM for Excel
    $header = array('#', 'Staff ID', 'NIN', 'Full Name', 'Gender', 'Birth Date', 'IPPS', 'Card #', 'Phone', 'Email', 'Department', 'Job', 'Terms', 'Status');
    fputcsv($fh, $header);
    $batch_size = 500;
    $row_no = 1;
    for ($offset = 0; $offset < $total; $offset += $batch_size) {
      $batch = $this->empModel->get_employees_export_batch($this->filters, (string) $search, $include_inactive, $offset, $batch_size);
      foreach ($batch as $row) {
        array_unshift($row, $row_no++);
        fputcsv($fh, $row);
      }
      if (ob_get_level()) {
        ob_flush();
        flush();
      }
    }
    fclose($fh);
    exit;
  }

  /**
   * Stream all staff as Excel (CSV with .xls extension for Excel). GET: search, includeInactive.
   */
  public function export_staff_excel()
  {
    $search = $this->input->get('search');
    $include_inactive = (bool) $this->input->get('includeInactive');
    $total = $this->empModel->get_employees_count($this->filters, (string) $search, $include_inactive);
    $filename = 'staff_' . date('Y-m-d_His') . '.xls';
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache');
    $fh = fopen('php://output', 'w');
    fprintf($fh, chr(0xEF).chr(0xBB).chr(0xBF));
    $header = array('#', 'Staff ID', 'NIN', 'Full Name', 'Gender', 'Birth Date', 'IPPS', 'Card #', 'Phone', 'Email', 'Department', 'Job', 'Terms', 'Status');
    fputcsv($fh, $header, "\t");
    $batch_size = 500;
    $row_no = 1;
    for ($offset = 0; $offset < $total; $offset += $batch_size) {
      $batch = $this->empModel->get_employees_export_batch($this->filters, (string) $search, $include_inactive, $offset, $batch_size);
      foreach ($batch as $row) {
        array_unshift($row, $row_no++);
        fputcsv($fh, $row, "\t");
      }
      if (ob_get_level()) {
        ob_flush();
        flush();
      }
    }
    fclose($fh);
    exit;
  }

  /**
   * Stream all district staff as CSV. GET: search, includeInactive, job, facility.
   */
  public function export_district_staff_csv()
  {
    $search = $this->input->get('search');
    $include_inactive = (bool) $this->input->get('includeInactive');
    $job = $this->input->get('job');
    $facility = $this->input->get('facility');
    if (is_string($job) && $job !== '') {
      $job = array_filter(explode(',', $job));
    }
    if (is_string($facility) && $facility !== '') {
      $facility = array_filter(explode(',', $facility));
    }
    $total = $this->empModel->get_district_employees_export_count($_SESSION['district'], $job, $facility, (string) $search, $include_inactive);
    $filename = 'district_staff_' . date('Y-m-d_His') . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache');
    $fh = fopen('php://output', 'w');
    fprintf($fh, chr(0xEF).chr(0xBB).chr(0xBF));
    $header = array('#', 'Staff ID', 'NIN', 'Full Name', 'Gender', 'Birth Date', 'Phone', 'Email', 'Facility', 'Department', 'Job', 'Terms', 'Card #', 'Status');
    fputcsv($fh, $header);
    $batch_size = 500;
    $row_no = 1;
    for ($offset = 0; $offset < $total; $offset += $batch_size) {
      $batch = $this->empModel->get_district_employees_export_batch($_SESSION['district'], $job, $facility, (string) $search, $include_inactive, $offset, $batch_size);
      foreach ($batch as $row) {
        array_unshift($row, $row_no++);
        fputcsv($fh, $row);
      }
      if (ob_get_level()) {
        ob_flush();
        flush();
      }
    }
    fclose($fh);
    exit;
  }

  /**
   * Stream all district staff as Excel (CSV with .xls). GET: search, includeInactive, job, facility.
   */
  public function export_district_staff_excel()
  {
    $search = $this->input->get('search');
    $include_inactive = (bool) $this->input->get('includeInactive');
    $job = $this->input->get('job');
    $facility = $this->input->get('facility');
    if (is_string($job) && $job !== '') {
      $job = array_filter(explode(',', $job));
    }
    if (is_string($facility) && $facility !== '') {
      $facility = array_filter(explode(',', $facility));
    }
    $total = $this->empModel->get_district_employees_export_count($_SESSION['district'], $job, $facility, (string) $search, $include_inactive);
    $filename = 'district_staff_' . date('Y-m-d_His') . '.xls';
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache');
    $fh = fopen('php://output', 'w');
    fprintf($fh, chr(0xEF).chr(0xBB).chr(0xBF));
    $header = array('#', 'Staff ID', 'NIN', 'Full Name', 'Gender', 'Birth Date', 'Phone', 'Email', 'Facility', 'Department', 'Job', 'Terms', 'Card #', 'Status');
    fputcsv($fh, $header, "\t");
    $batch_size = 500;
    $row_no = 1;
    for ($offset = 0; $offset < $total; $offset += $batch_size) {
      $batch = $this->empModel->get_district_employees_export_batch($_SESSION['district'], $job, $facility, (string) $search, $include_inactive, $offset, $batch_size);
      foreach ($batch as $row) {
        array_unshift($row, $row_no++);
        fputcsv($fh, $row, "\t");
      }
      if (ob_get_level()) {
        ob_flush();
        flush();
      }
    }
    fclose($fh);
    exit;
  }

  public function index()
  {
    // Handle AJAX requests for server-side pagination
    if ($this->input->is_ajax_request()) {
      $this->_handleMainEmployeesAjaxRequest();
      return;
    }
    
    $data['title'] = "Staff";
    $data['facilities'] = Modules::run("facilities/getFacilities");
    $data['jobs'] = Modules::run("jobs/getJobs");
    $data['view'] = 'staff';
    $data['uptitle'] = "Staff List";
    $data['module'] = "employees";
    echo Modules::run("templates/main", $data);
  }


  public function createEmployee()
  {
    $data['title'] = "Staff";
    $data['facilities'] = Modules::run("lists/get_all_Facilities");
    $data['facilities_json'] = json_encode(Modules::run("lists/get_all_Facilities"));
    $data['districts'] = Modules::run('lists/get_all_districts');
    $data['jobs'] = Modules::run('lists/get_all_jobs');
    $data['jobs_json'] = json_encode(Modules::run('lists/get_all_jobs'));
    $data['cadres'] = Modules::run('lists/get_all_cadres');
    $data['view'] = 'create_employee';
    $data['uptitle'] = "Add Employee";
    $data['module'] = "employees";

    echo Modules::run("templates/main", $data);
  }

  public function saveEmployee()
  {
    $data = $this->input->post();
    $this->empModel->save_employee($data);

    redirect('employees/index');
  }

  public function personlogs()
  {
    $data['title'] = "Person Logs";
    $data['view'] = 'personlogs';
    $data['module'] = "employees";
    $data['uptitle'] = "Person Attendance";
    echo Modules::run("templates/main", $data);
  }
  public function getStaffDatatable()
  {
    $columns = array(
      0 => 'ipps',
      2 => 'ihris_pid',
      3 => 'surname',
      4 => 'firstname',
      5 => 'othername',
      6 => 'job',
      7 => 'facility'
    );
    $limit = $this->input->post('length');
    $start = $this->input->post('start');
    $order = $columns[$this->input->post('order')[0]['column']];
    $dir = $this->input->post('order')[0]['dir'];
    $totalData = $this->empModel->countStaff();
    $totalFiltered = $totalData;
    if (empty($this->input->post('search')['value'])) {
      $staffs = $this->empModel->fetchAllStaff($limit, $start, $order, $dir);
    } else {
      $search = $this->input->post('search')['value'];
      $staffs =  $this->empModel->searchStaff($limit, $start, $search, $order, $dir);
      $totalFiltered = $this->empModel->countforSearch($search);
    }
    $data = array();
    if (!empty($staffs)) {
      foreach ($staffs as $staff) {
        $row['ipps'] = $staff->ipps;
        $row['ihris_pid'] = str_replace("person|", "", $staff->ihris_pid);
        $row['surname'] = $staff->surname;
        $row['firstname'] = $staff->firstname;
        $row['othername'] = $staff->othername;
        $row['job'] = $staff->job;
        $row['facility'] = $staff->facility;
        $data[] = $row;
      }
    }
    $json_data = array(
      "draw"            => intval($this->input->post('draw')),
      "recordsTotal"    => intval($totalData),
      "recordsFiltered" => intval($totalFiltered),
      "data"            => $data
    );
    echo json_encode($json_data);
  }
  public function count_Staff()
  {
    $number = $this->empModel->count_Staff($this->filters);
    return $number;
  }
  public function attCsv($datef, $datet, $person, $job)
  {
    $datas = $this->empModel->timelogscsv($datef, $datet, str_replace("person", "", $person), str_replace("position-", "", urldecode(str_replace('_', ' ', $job))), $this->filters);
    $csv_file = "Attend_TimeLogs" . date('Y-m-d') . '_' . $_SESSION['facility_name'] . ".csv";
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"$csv_file\"");
    $fh = fopen('php://output', 'w');
    $records = array(); //output each row of the data, format line as csv and write to file pointer
    foreach ($datas as $data) {
      $time_in = $data->time_in;
      $time_out = $data->time_out;
      $initial_time = strtotime($time_in) / 3600;
      $final_time = strtotime($time_out) / 3600;
      if (($initial_time) == 0 || ($final_time) == 0) {
        $hours_worked = 0;
      } elseif ($initial_time == $final_time) {
        $hours_worked = 0;
      } else {
        $hours_worked = round(($final_time - $initial_time), 1);
      }
      if ($hours_worked < 0) {
        $hours = ($hours_worked * -1);
      } else {
        $hours = $hours_worked;
      }
      if (!empty($hours_worked)) {
      }

      if (!empty($time_out = $data->time_out)) {
        $dtimeout = date('H:i:s', strtotime($time_out = $data->time_out));
      }
      else{
        $dtimeout = "";
      }
      
      $dtimein = date('H:i:s', strtotime($time_in = $data->time_in));
      $days = array("NAME" => $data->surname . " " . $data->firstname . " " . $data->othername, "JOB" => $data->job,"CADRE"=>$data->cadre, "FACILITY" => $data->fac, "DEPARTMENT" => $data->department,"GENDER"=>$data->gender,"BIRTH DATE"=> $data->birth_date, "DATE" => $data->date, "TIME IN" => $dtimein, "TIME OUT" => $dtimeout, "HOURS WORKED" => $hours);
      array_push($records, $days);
    }
    $is_coloumn = true;
    if (!empty($records)) {
      foreach ($records as $record) {
        if ($is_coloumn) {
          fputcsv($fh, array_keys($record));
          $is_coloumn = false;
        }
        fputcsv($fh, array_values($record));
      }
      fclose($fh);
    }
    exit;
  }
  public function viewTimeLogs()
  {
    // Default date range
      $date_from = date("Y-m-d", strtotime("-1 month"));
      $date_to = date('Y-m-d');
    
    $data['date_from'] = $date_from;
    $data['date_to'] = $date_to;
    $data['title'] = "Staff Time Log Report";
    $data['uptitle'] = "Staff Time Log Report";
    $data['view'] = 'time_logs';
    $data['module'] = "employees";
    echo Modules::run("templates/main", $data);
  }

  /**
   * Server-side DataTables AJAX endpoint for time logs.
   */
  public function viewTimeLogsAjax()
  {
    $this->output->set_content_type('application/json');

    $draw = (int) $this->input->post('draw');
    $start = (int) $this->input->post('start');
    $length = (int) $this->input->post('length');
    $search = trim((string) $this->input->post('search')['value']);

    // Filters from form
    $date_from = trim((string) $this->input->post('date_from'));
    $date_to = trim((string) $this->input->post('date_to'));
    $name = trim((string) $this->input->post('name'));
    $job = trim((string) $this->input->post('job'));

    // Default date range if not provided
    if (empty($date_from)) {
      $date_from = date("Y-m-d", strtotime("-1 month"));
    }
    if (empty($date_to)) {
      $date_to = date('Y-m-d');
    }

    $search_data = array(
      'date_from' => $date_from,
      'date_to' => $date_to,
      'name' => $name,
      'job' => $job
    );

    try {
      // Counts
      $total_records = $this->empModel->countTimeLogsAjax($search_data, $this->filters, '');
      $filtered_records = $this->empModel->countTimeLogsAjax($search_data, $this->filters, $search);

      // Fetch data
      $timelogs = $this->empModel->fetchTimeLogsAjax($search_data, $this->filters, $start, $length, $search);

      // Format data for DataTables
      $data = array();
      $row_num = $start + 1;
      foreach ($timelogs as $log) {
        // Calculate hours worked
        $time_in = $log->time_in ?? null;
        $time_out = $log->time_out ?? null;
        $hours_worked = 0;
        
        if ($time_in && $time_out) {
          $initial_time = strtotime($time_in) / 3600;
          $final_time = strtotime($time_out) / 3600;
          if ($initial_time > 0 && $final_time > 0 && $initial_time != $final_time) {
            $hours_worked = round(($final_time - $initial_time), 1);
            if ($hours_worked < 0) {
              $hours_worked = abs($hours_worked);
            }
          }
        }
        
        $pid = isset($log->pid) ? (string) $log->pid : '';
        $nameText = trim(($log->surname ?? '') . ' ' . ($log->firstname ?? ''));
        $nameCell = $nameText;
        if ($pid !== '') {
          $nameCell = '<a href="' . base_url() . 'employees/employeeTimeLogs/' . rawurlencode($pid) . '">' . htmlspecialchars($nameText) . '</a>';
        } else {
          $nameCell = htmlspecialchars($nameText);
        }

        $data[] = array(
          $row_num++,
          $nameCell,
          $log->job ?? '',
          date('j F, Y', strtotime($log->date)),
          $time_in ? date('H:i:s', strtotime($time_in)) : '',
          $time_out ? date('H:i:s', strtotime($time_out)) : '',
          $hours_worked . 'hr(s)'
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
      log_message('error', 'viewTimeLogsAjax error: ' . $e->getMessage());
      echo json_encode(array(
        'draw' => $draw,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => array(),
        'error' => 'Failed to load time log data'
      ));
      exit;
    }
  }
  public function groupedTimeLogs()
  {
    // Default date range
    $date_from = date("Y-m-d", strtotime("-1 month"));
    $date_to = date('Y-m-d');
    
    $data['date_from'] = $date_from;
    $data['date_to'] = $date_to;
    $data['title'] = "Monthly Time Log Report";
    $data['uptitle'] = "Monthly Time Log Report";
    $data['view'] = 'groupedtime_logs';
    $data['module'] = "employees";
    echo Modules::run("templates/main", $data);
  }

  /**
   * Server-side DataTables AJAX endpoint for grouped monthly time logs.
   */
  public function groupedTimeLogsAjax()
  {
    $this->output->set_content_type('application/json');

    $draw = (int) $this->input->post('draw');
    $start = (int) $this->input->post('start');
    $length = (int) $this->input->post('length');
    $search = trim((string) $this->input->post('search')['value']);

    // Filters from form
    $date_from = trim((string) $this->input->post('date_from'));
    $date_to = trim((string) $this->input->post('date_to'));
    $name = trim((string) $this->input->post('name'));
    $job = trim((string) $this->input->post('job'));

    // Default date range if not provided
    if (empty($date_from)) {
      $date_from = date("Y-m-d", strtotime("-1 month"));
    }
    if (empty($date_to)) {
      $date_to = date('Y-m-d');
    }

    $search_data = array(
      'date_from' => $date_from,
      'date_to' => $date_to,
      'name' => $name,
      'job' => $job
    );

    try {
      // Counts
      $total_records = $this->empModel->countGroupedMonthlyTimeLogsAjax($search_data, $this->filters, '');
      $filtered_records = $this->empModel->countGroupedMonthlyTimeLogsAjax($search_data, $this->filters, $search);

      // Fetch data
      $timelogs = $this->empModel->fetchGroupedMonthlyTimeLogsAjax($search_data, $this->filters, $start, $length, $search);

      // Format data for DataTables
      $data = array();
      $row_num = $start + 1;
      foreach ($timelogs as $log) {
        // Format date - log->date should be in Y-m-d format (first day of month from GROUP BY)
        $dateStr = $log->date ?? '';
        if (!empty($dateStr)) {
          try {
            $dateFormatted = date('F, Y', strtotime($dateStr));
          } catch (Exception $e) {
            $dateFormatted = $dateStr;
          }
        } else {
          $dateFormatted = '';
        }
        
        $data[] = array(
          $row_num++,
          trim(($log->surname ?? '') . ' ' . ($log->firstname ?? '')),
          $log->job ?? '',
          $log->facility ?? '',
          $log->department ?? '',
          $dateFormatted,
          number_format($log->m_timediff ?? 0, 2)
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
      log_message('error', 'groupedTimeLogsAjax error: ' . $e->getMessage());
      echo json_encode(array(
        'draw' => $draw,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => array(),
        'error' => 'Failed to load monthly time log data'
      ));
      exit;
    }
  }

  /**
   * CSV export for grouped monthly time logs: stream by batches (same filters as DataTable).
   */
  public function groupedTimeLogsCsv()
  {
    $date_from = trim((string) $this->input->get('date_from'));
    $date_to = trim((string) $this->input->get('date_to'));
    $name = trim((string) $this->input->get('name'));
    $job = trim((string) $this->input->get('job'));
    if (empty($date_from)) {
      $date_from = date("Y-m-d", strtotime("-1 month"));
    }
    if (empty($date_to)) {
      $date_to = date('Y-m-d');
    }
    $search_data = array('date_from' => $date_from, 'date_to' => $date_to, 'name' => $name, 'job' => $job);
    @set_time_limit(0);
    $batch_size = 80;
    $total = $this->empModel->countGroupedMonthlyTimeLogsAjax($search_data, $this->filters, '');
    $fac = isset($_SESSION['facility_name']) ? $_SESSION['facility_name'] : 'export';
    $csv_file = 'Grouped_TimeLogs_' . date('Y-m-d') . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $fac) . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $csv_file . '"');
    header('Cache-Control: no-cache, must-revalidate');
    $fh = fopen('php://output', 'w');
    fputcsv($fh, array('#', 'Name', 'Position', 'Facility', 'Department', 'Date', 'Hours Worked'));
    if (ob_get_level() > 0) {
      ob_flush();
    }
    flush();
    $row_no = 1;
    for ($start = 0; $start < $total; $start += $batch_size) {
      $batch = $this->empModel->fetchGroupedMonthlyTimeLogsAjax($search_data, $this->filters, $start, $batch_size, '');
      if (empty($batch)) {
        break;
      }
      foreach ($batch as $log) {
        $dateStr = $log->date ?? '';
        $dateFormatted = !empty($dateStr) ? date('F, Y', strtotime($dateStr)) : '';
        $nameStr = trim(($log->surname ?? '') . ' ' . ($log->firstname ?? ''));
        fputcsv($fh, array(
          $row_no++,
          $nameStr,
          $log->job ?? '',
          $log->facility ?? '',
          $log->department ?? '',
          $dateFormatted,
          number_format($log->m_timediff ?? 0, 2)
        ));
      }
      unset($batch);
      if (ob_get_level() > 0) {
        ob_flush();
      }
      flush();
    }
    fclose($fh);
    exit;
  }

  /**
   * PDF export for grouped monthly time logs: stream by batches.
   */
  public function print_grouped_timelogs()
  {
    $date_from = trim((string) $this->input->get('date_from'));
    $date_to = trim((string) $this->input->get('date_to'));
    $name = trim((string) $this->input->get('name'));
    $job = trim((string) $this->input->get('job'));
    if (empty($date_from)) {
      $date_from = date("Y-m-d", strtotime("-1 month"));
    }
    if (empty($date_to)) {
      $date_to = date('Y-m-d');
    }
    $search_data = array('date_from' => $date_from, 'date_to' => $date_to, 'name' => $name, 'job' => $job);
    $this->load->library('ML_pdf');
    $batch_size = 80;
    $total = $this->empModel->countGroupedMonthlyTimeLogsAjax($search_data, $this->filters, '');
    $fac = isset($_SESSION['facility_name']) ? $_SESSION['facility_name'] : 'Report';
    $filename = $fac . '_Grouped_TimeLogs_' . date('Y-m-d') . '.pdf';
    @set_time_limit(0);
    if (!empty($this->watermark) && is_file($this->watermark)) {
      $this->ml_pdf->pdf->SetWatermarkImage($this->watermark);
      $this->ml_pdf->pdf->showWatermarkImage = true;
    }
    date_default_timezone_set('Africa/Kampala');
    $this->ml_pdf->pdf->SetHTMLFooter('Printed/ Accessed on: <b>' . date('d F,Y h:i A') . '</b><br style="font-size: 9px;">Source: iHRIS - HRM Attend ' . base_url());
    $moh_logo = (defined('FCPATH') && is_file(FCPATH . 'assets/img/MOH.png')) ? FCPATH . 'assets/img/MOH.png' : '';
    $header_data = array(
      'date_from' => $date_from,
      'date_to' => $date_to,
      'facility_name' => $fac,
      'moh_logo_path' => $moh_logo
    );
    $header_html = $this->load->view('employees/print_grouped_timelogs_header', $header_data, true);
    $this->ml_pdf->pdf->WriteHTML(mb_convert_encoding($header_html, 'UTF-8', 'UTF-8'));
    $row_no = 1;
    for ($start = 0; $start < $total; $start += $batch_size) {
      $batch = $this->empModel->fetchGroupedMonthlyTimeLogsAjax($search_data, $this->filters, $start, $batch_size, '');
      if (empty($batch)) {
        break;
      }
      $rows_data = array('rows' => $batch, 'start_row_no' => $row_no);
      $rows_html = $this->load->view('employees/print_grouped_timelogs_rows', $rows_data, true);
      $this->ml_pdf->pdf->WriteHTML(mb_convert_encoding($rows_html, 'UTF-8', 'UTF-8'));
      $row_no += count($batch);
      unset($batch, $rows_data, $rows_html);
      if (function_exists('gc_collect_cycles')) {
        gc_collect_cycles();
      }
    }
    $footer_html = $this->load->view('employees/print_grouped_timelogs_footer', array(), true);
    $this->ml_pdf->pdf->WriteHTML(mb_convert_encoding($footer_html, 'UTF-8', 'UTF-8'));
    $this->ml_pdf->pdf->Output($filename, 'I');
  }

  // public function testing(){
  //     $search_data=$this->input->post();
  //     $data['timelogs']=$this->empModel->getTimeLogs($config['per_page']=1,$page=1,$search_data);
  //  print_r($data);
  // } 
  public function printStafflist()
  {
    $this->load->library('M_pdf');
    $data = $this->empModel->get_employees();
    $html = $this->load->view('employees/printstaff', $data, true);
    //$fac=$_SESSION['facility'];
    $filename = "Stafflist_" . "pdf";
    ini_set('max_execution_time', 0);
    $PDFContent = mb_convert_encoding($html, 'UTF-8', 'UTF-8');
    $this->m_pdf->pdf->SetWatermarkImage($this->watermark);
    date_default_timezone_set("Africa/Kampala");
    $this->m_pdf->pdf->SetHTMLFooter("Printed/ Accessed on: <b>" . date('d F,Y h:i A') . "</b><br style='font-size: 9px !imporntant;'>" . "Source: iHRIS - HRM Attend " . base_url());
    $this->m_pdf->pdf->SetWatermarkImage($this->watermark);
    ini_set('max_execution_time', 0);
    $this->m_pdf->pdf->WriteHTML($PDFContent); //ml_pdf because we loaded the library ml_pdf for landscape format not m_pdf
    //download it D save F.
    $this->m_pdf->pdf->Output($filename, 'I');
  }
  public function print_timelogs($datef, $datet, $person, $job)
  {
    $name = str_replace('person', '', $person);
    $job_clean = str_replace('position-', '', urldecode(str_replace('_', ' ', $job)));
    $search_data = array(
      'date_from' => $datef,
      'date_to' => $datet,
      'name' => $name,
      'job' => $job_clean
    );
    $this->load->library('ML_pdf');
    $batch_size = 80;
    $total = $this->empModel->countTimeLogsAjax($search_data, $this->filters, '');
    $fac = isset($_SESSION['facility_name']) ? $_SESSION['facility_name'] : 'Report';
    $filename = $fac . '_Staff_Timelog_Report_' . date('Y-m-d') . '.pdf';
    @set_time_limit(0);

    if (!empty($this->watermark) && is_file($this->watermark)) {
    $this->ml_pdf->pdf->SetWatermarkImage($this->watermark);
    $this->ml_pdf->pdf->showWatermarkImage = true;
    }
    date_default_timezone_set('Africa/Kampala');
    $this->ml_pdf->pdf->SetHTMLFooter('Printed/ Accessed on: <b>' . date('d F,Y h:i A') . '</b><br style="font-size: 9px;">Source: iHRIS - HRM Attend ' . base_url());

    $moh_logo = (defined('FCPATH') && is_file(FCPATH . 'assets/img/MOH.png')) ? FCPATH . 'assets/img/MOH.png' : '';
    $header_data = array(
      'date_from' => $datef,
      'date_to' => $datet,
      'facility_name' => $fac,
      'moh_logo_path' => $moh_logo
    );
    $header_html = $this->load->view('employees/print_timelogs_header', $header_data, true);
    $this->ml_pdf->pdf->WriteHTML(mb_convert_encoding($header_html, 'UTF-8', 'UTF-8'));

    $row_no = 1;
    for ($start = 0; $start < $total; $start += $batch_size) {
      $batch = $this->empModel->fetchTimeLogsAjax($search_data, $this->filters, $start, $batch_size, '');
      if (empty($batch)) {
        break;
      }
      $rows_data = array('logs' => $batch, 'start_row_no' => $row_no);
      $rows_html = $this->load->view('employees/print_timelogs_rows', $rows_data, true);
      $this->ml_pdf->pdf->WriteHTML(mb_convert_encoding($rows_html, 'UTF-8', 'UTF-8'));
      $row_no += count($batch);
      unset($batch, $rows_data, $rows_html);
      if (function_exists('gc_collect_cycles')) {
        gc_collect_cycles();
      }
    }
    $footer_html = $this->load->view('employees/print_timelogs_footer', array(), true);
    $this->ml_pdf->pdf->WriteHTML(mb_convert_encoding($footer_html, 'UTF-8', 'UTF-8'));
    $this->ml_pdf->pdf->Output($filename, 'I');
  }
  /**
   * Print timesheet (monthly): stream by employee batches to avoid loading all data into memory.
   */
  public function print_timesheet($month, $year, $employee, $job)
  {
    $this->load->library('ML_pdf');
    $date = $year . '-' . $month;
    if (empty($date)) {
      $date = date('Y-m');
      $month = date('m', strtotime($date));
      $year = date('Y', strtotime($date));
    }
    $emp_filter = str_replace('emp', '', urldecode($employee));
    $job_filter = str_replace('job', '', $job);
    $batch_size = 40;
    $total = $this->empModel->count_TimeSheet_employees($date, $emp_filter, $this->filters, $job_filter);
    $fac = isset($_SESSION['facility_name']) ? $_SESSION['facility_name'] : 'Report';
    $filename = $fac . '_Timesheet_Report_' . $date . '.pdf';
    @set_time_limit(0);

    if (!empty($this->watermark) && is_file($this->watermark)) {
    $this->ml_pdf->pdf->SetWatermarkImage($this->watermark);
    $this->ml_pdf->pdf->showWatermarkImage = true;
    }
    date_default_timezone_set('Africa/Kampala');
    $this->ml_pdf->pdf->SetHTMLFooter('Printed/ Accessed on: <b>' . date('d F,Y h:i A') . '</b><br style="font-size: 9px;">Source: iHRIS - HRM Attend ' . base_url());

    $moh_logo = (defined('FCPATH') && is_file(FCPATH . 'assets/img/MOH.png')) ? FCPATH . 'assets/img/MOH.png' : '';
    $header_data = array(
      'year' => $year,
      'month' => $month,
      'date' => $date,
      'date_from' => '',
      'date_to' => '',
      'dateList' => array(),
      'moh_logo_path' => $moh_logo,
      'facility_name' => $fac,
    );
    $header_html = $this->load->view('print_timesheet_header', $header_data, true);
    $this->ml_pdf->pdf->WriteHTML(mb_convert_encoding($header_html, 'UTF-8', 'UTF-8'));

    $start_date = date('Y-m-01', strtotime($date . '-01'));
    $end_date = date('Y-m-t', strtotime($date . '-01'));
    $row_no = 1;
    for ($offset = 0; $offset < $total; $offset += $batch_size) {
      $batch = $this->empModel->fetch_TimeSheet($date, $offset, $batch_size, $emp_filter, $this->filters, $job_filter);
      if (empty($batch)) {
        break;
      }
      $pids = array();
      foreach ($batch as $row) {
        if (!empty($row['ihris_pid'])) {
          $pids[] = $row['ihris_pid'];
        }
      }
      $logs_by_pid_date = array();
      if (!empty($pids)) {
        $logs = $this->db
          ->select('ihris_pid, date, time_in, time_out')
          ->from('clk_log')
          ->where_in('ihris_pid', $pids)
          ->where('date >=', $start_date)
          ->where('date <=', $end_date)
          ->get()
          ->result();
        foreach ($logs as $log) {
          $pid = (string) $log->ihris_pid;
          if (!isset($logs_by_pid_date[$pid])) {
            $logs_by_pid_date[$pid] = array();
          }
          $logs_by_pid_date[$pid][$log->date] = $log;
        }
      }
      $scheduledDaysByPid = $this->empModel->get_scheduled_days_for_month($pids, $date);
      $rows_data = array(
        'workinghours' => $batch,
        'logs_by_pid_date' => $logs_by_pid_date,
        'scheduledDaysByPid' => $scheduledDaysByPid,
        'month' => $month,
        'year' => $year,
        'dateList' => array(),
        'date_from' => '',
        'date_to' => '',
        'start_row_no' => $row_no,
      );
      $rows_html = $this->load->view('print_timesheet_rows', $rows_data, true);
      $this->ml_pdf->pdf->WriteHTML(mb_convert_encoding($rows_html, 'UTF-8', 'UTF-8'));
      $row_no += count($batch);
      unset($batch, $pids, $logs_by_pid_date, $scheduledDaysByPid, $rows_data, $rows_html);
    }

    $footer_html = $this->load->view('print_timesheet_footer', array(), true);
    $this->ml_pdf->pdf->WriteHTML(mb_convert_encoding($footer_html, 'UTF-8', 'UTF-8'));
    $this->ml_pdf->pdf->Output($filename, 'I');
  }
  /**
   * CSV timesheet (monthly): stream by batches, full report with per-day columns like print.
   */
  public function csv_timesheet($month, $year, $employee, $job)
  {
    $date = $year . '-' . $month;
    if (empty($date)) {
      $date = date('Y-m');
      $month = date('m', strtotime($date));
      $year = date('Y', strtotime($date));
    }
    $emp_filter = str_replace('emp', '', urldecode($employee));
    $job_filter = str_replace('job', '', $job);
    $batch_size = 40;
    $total = $this->empModel->count_TimeSheet_employees($date, $emp_filter, $this->filters, $job_filter);
    $month_days = cal_days_in_month(CAL_GREGORIAN, (int) $month, (int) $year);
    $start_date = date('Y-m-01', strtotime($date . '-01'));
    $end_date = date('Y-m-t', strtotime($date . '-01'));

    $fac = $_SESSION['facility_name'];
    $csv_file = $fac . ' Timesheet_' . $date . '.csv';
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $csv_file . '"');
    $fh = fopen('php://output', 'w');
    ini_set('max_execution_time', 0);

    $header_row = array('#', 'Name', 'Position');
    for ($d = 1; $d <= $month_days; $d++) {
      $header_row[] = (string) $d;
    }
    $header_row[] = 'Hours';
    $header_row[] = 'Days';
    fputcsv($fh, $header_row);

    $row_no = 1;
    for ($offset = 0; $offset < $total; $offset += $batch_size) {
      $batch = $this->empModel->fetch_TimeSheet($date, $offset, $batch_size, $emp_filter, $this->filters, $job_filter);
      if (empty($batch)) {
        break;
      }
      $pids = array();
      foreach ($batch as $row) {
        if (!empty($row['ihris_pid'])) {
          $pids[] = $row['ihris_pid'];
        }
      }
      $logs_by_pid_date = array();
      if (!empty($pids)) {
        $logs = $this->db
          ->select('ihris_pid, date, time_in, time_out')
          ->from('clk_log')
          ->where_in('ihris_pid', $pids)
          ->where('date >=', $start_date)
          ->where('date <=', $end_date)
          ->get()
          ->result();
        foreach ($logs as $log) {
          $pid = (string) $log->ihris_pid;
          if (!isset($logs_by_pid_date[$pid])) {
            $logs_by_pid_date[$pid] = array();
          }
          $logs_by_pid_date[$pid][$log->date] = $log;
        }
      }
      $scheduledDaysByPid = $this->empModel->get_scheduled_days_for_month($pids, $date);

      foreach ($batch as $emp) {
        $pid = isset($emp['ihris_pid']) ? $emp['ihris_pid'] : '';
        $row = array(
          $row_no++,
          isset($emp['fullname']) ? trim($emp['fullname']) : '',
          $this->_position_initials(isset($emp['job']) ? $emp['job'] : ''),
        );
      $personhrs = array();
        for ($i = 1; $i <= $month_days; $i++) {
          $date_d = $year . '-' . $month . '-' . ($i < 10 ? '0' . $i : $i);
          $timedata = isset($logs_by_pid_date[$pid][$date_d]) ? $logs_by_pid_date[$pid][$date_d] : null;
          $hrs = $this->_hours_from_log($timedata);
          $row[] = $hrs !== 0 ? $hrs : '';
          if ($hrs !== 0) {
            $personhrs[] = $hrs;
          }
        }
        $worked = count($personhrs);
        $row[] = array_sum($personhrs);
        $row[] = $worked;
        fputcsv($fh, $row);
      }
      unset($batch, $pids, $logs_by_pid_date, $scheduledDaysByPid);
    }
    fclose($fh);
    exit;
  }

  private function _position_initials($job)
  {
    $words = explode(' ', trim($job));
    $letters = '';
    foreach ($words as $w) {
      $letters .= isset($w[0]) ? $w[0] : '';
    }
    return $letters;
  }

  private function _hours_from_log($timedata)
  {
    if (empty($timedata) || !is_object($timedata)) {
      return 0;
    }
    $in = isset($timedata->time_in) ? $timedata->time_in : null;
    $out = isset($timedata->time_out) ? $timedata->time_out : null;
    if (!$in || !$out) {
      return 0;
    }
    $ti = strtotime($in) / 3600;
    $to = strtotime($out) / 3600;
    if ($ti == $to) {
      return 0;
    }
    $hrs = round($to - $ti, 1);
    return $hrs < 0 ? -$hrs : $hrs;
  }

  /**
   * Print timesheet for a date range (max 31 days). Streams by employee batches.
   */
  public function print_timesheet_range($date_from, $date_to, $employee, $job)
  {
    $this->load->library('ML_pdf');

    $startDate = date('Y-m-d', strtotime($date_from));
    $endDate = date('Y-m-d', strtotime($date_to));
    if (strtotime($startDate) > strtotime($endDate)) {
      $tmp = $startDate;
      $startDate = $endDate;
      $endDate = $tmp;
    }
    $daysDiff = (int) floor((strtotime($endDate) - strtotime($startDate)) / 86400) + 1;
    if ($daysDiff > 31) {
      $endDate = date('Y-m-d', strtotime($startDate . ' +30 days'));
    }

    $dateList = array();
    $s = new DateTime($startDate);
    $e = new DateTime($endDate);
    $e->modify('+1 day');
    $period = new DatePeriod($s, new DateInterval('P1D'), $e);
    foreach ($period as $d) {
      $dateList[] = $d->format('Y-m-d');
    }
    if (count($dateList) > 31) {
      $dateList = array_slice($dateList, 0, 31);
    }

    $emp_filter = str_replace('emp', '', urldecode($employee));
    $job_filter = str_replace('job', '', urldecode($job));
    $batch_size = 40;
    $total = $this->empModel->count_TimeSheet_employees($startDate, $emp_filter, $this->filters, $job_filter);
    $fac = isset($_SESSION['facility_name']) ? $_SESSION['facility_name'] : 'Report';
    $filename = $fac . '_Timesheet_Report_' . $startDate . '_to_' . $endDate . '.pdf';
    @set_time_limit(0);

    if (!empty($this->watermark) && is_file($this->watermark)) {
      $this->ml_pdf->pdf->SetWatermarkImage($this->watermark);
      $this->ml_pdf->pdf->showWatermarkImage = true;
    }
    date_default_timezone_set('Africa/Kampala');
    $this->ml_pdf->pdf->SetHTMLFooter('Printed/ Accessed on: <b>' . date('d F,Y h:i A') . '</b><br style="font-size: 9px;">Source: iHRIS - HRM Attend ' . base_url());

    $moh_logo = (defined('FCPATH') && is_file(FCPATH . 'assets/img/MOH.png')) ? FCPATH . 'assets/img/MOH.png' : '';
    $month = date('m', strtotime($startDate));
    $year = date('Y', strtotime($startDate));
    $header_data = array(
      'year' => $year,
      'month' => $month,
      'date' => $startDate,
      'date_from' => $startDate,
      'date_to' => $endDate,
      'dateList' => $dateList,
      'moh_logo_path' => $moh_logo,
      'facility_name' => $fac,
    );
    $header_html = $this->load->view('print_timesheet_header', $header_data, true);
    $this->ml_pdf->pdf->WriteHTML(mb_convert_encoding($header_html, 'UTF-8', 'UTF-8'));

    $row_no = 1;
    for ($offset = 0; $offset < $total; $offset += $batch_size) {
      $batch = $this->empModel->fetch_TimeSheet($startDate, $offset, $batch_size, $emp_filter, $this->filters, $job_filter);
      if (empty($batch)) {
        break;
      }
      $pids = array();
      foreach ($batch as $row) {
        if (!empty($row['ihris_pid'])) {
          $pids[] = $row['ihris_pid'];
        }
      }
      $logs_by_pid_date = array();
      if (!empty($pids)) {
        $logs = $this->db
          ->select('ihris_pid, date, time_in, time_out')
          ->from('clk_log')
          ->where_in('ihris_pid', $pids)
          ->where('date >=', $startDate)
          ->where('date <=', $endDate)
          ->get()
          ->result();
        foreach ($logs as $log) {
          $pid = (string) $log->ihris_pid;
          if (!isset($logs_by_pid_date[$pid])) {
            $logs_by_pid_date[$pid] = array();
          }
          $logs_by_pid_date[$pid][$log->date] = $log;
        }
      }
      $scheduledDaysByPid = $this->empModel->get_scheduled_days_for_range($pids, $startDate, $endDate);
      $rows_data = array(
        'workinghours' => $batch,
        'logs_by_pid_date' => $logs_by_pid_date,
        'scheduledDaysByPid' => $scheduledDaysByPid,
        'month' => $month,
        'year' => $year,
        'dateList' => $dateList,
        'date_from' => $startDate,
        'date_to' => $endDate,
        'start_row_no' => $row_no,
      );
      $rows_html = $this->load->view('print_timesheet_rows', $rows_data, true);
      $this->ml_pdf->pdf->WriteHTML(mb_convert_encoding($rows_html, 'UTF-8', 'UTF-8'));
      $row_no += count($batch);
      unset($batch, $pids, $logs_by_pid_date, $scheduledDaysByPid, $rows_data, $rows_html);
    }

    $footer_html = $this->load->view('print_timesheet_footer', array(), true);
    $this->ml_pdf->pdf->WriteHTML(mb_convert_encoding($footer_html, 'UTF-8', 'UTF-8'));
    $this->ml_pdf->pdf->Output($filename, 'I');
  }

  /**
   * CSV timesheet for date range (max 31 days): stream by batches, full report with per-day columns.
   */
  public function csv_timesheet_range($date_from, $date_to, $employee, $job)
  {
    $startDate = date('Y-m-d', strtotime($date_from));
    $endDate = date('Y-m-d', strtotime($date_to));
    if (strtotime($startDate) > strtotime($endDate)) {
      $tmp = $startDate;
      $startDate = $endDate;
      $endDate = $tmp;
    }
    $daysDiff = (int) floor((strtotime($endDate) - strtotime($startDate)) / 86400) + 1;
    if ($daysDiff > 31) {
      $endDate = date('Y-m-d', strtotime($startDate . ' +30 days'));
    }

    $dateList = array();
    $s = new DateTime($startDate);
    $e = new DateTime($endDate);
    $e->modify('+1 day');
    $period = new DatePeriod($s, new DateInterval('P1D'), $e);
    foreach ($period as $d) {
      $dateList[] = $d->format('Y-m-d');
    }
    if (count($dateList) > 31) {
      $dateList = array_slice($dateList, 0, 31);
    }

    $emp_filter = str_replace('emp', '', urldecode($employee));
    $job_filter = str_replace('job', '', urldecode($job));
    $batch_size = 40;
    $total = $this->empModel->count_TimeSheet_employees($startDate, $emp_filter, $this->filters, $job_filter);

    $fac = $_SESSION['facility_name'];
    $csv_file = $fac . '_Timesheet_' . $startDate . '_to_' . $endDate . '.csv';
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $csv_file . '"');
    $fh = fopen('php://output', 'w');
    ini_set('max_execution_time', 0);

    $header_row = array('#', 'Name', 'Position');
    foreach ($dateList as $dStr) {
      $header_row[] = (string) (int) date('j', strtotime($dStr));
    }
    $header_row[] = 'Hours';
    $header_row[] = 'Days';
    fputcsv($fh, $header_row);

    $row_no = 1;
    for ($offset = 0; $offset < $total; $offset += $batch_size) {
      $batch = $this->empModel->fetch_TimeSheet($startDate, $offset, $batch_size, $emp_filter, $this->filters, $job_filter);
      if (empty($batch)) {
        break;
      }
      $pids = array();
      foreach ($batch as $row) {
        if (!empty($row['ihris_pid'])) {
          $pids[] = $row['ihris_pid'];
        }
      }
      $logs_by_pid_date = array();
      if (!empty($pids)) {
        $logs = $this->db
          ->select('ihris_pid, date, time_in, time_out')
          ->from('clk_log')
          ->where_in('ihris_pid', $pids)
          ->where('date >=', $startDate)
          ->where('date <=', $endDate)
          ->get()
          ->result();
        foreach ($logs as $log) {
          $pid = (string) $log->ihris_pid;
          if (!isset($logs_by_pid_date[$pid])) {
            $logs_by_pid_date[$pid] = array();
          }
          $logs_by_pid_date[$pid][$log->date] = $log;
        }
      }
      $scheduledDaysByPid = $this->empModel->get_scheduled_days_for_range($pids, $startDate, $endDate);

      foreach ($batch as $emp) {
        $pid = isset($emp['ihris_pid']) ? $emp['ihris_pid'] : '';
        $row = array(
          $row_no++,
          isset($emp['fullname']) ? trim($emp['fullname']) : '',
          $this->_position_initials(isset($emp['job']) ? $emp['job'] : ''),
        );
        $personhrs = array();
        foreach ($dateList as $date_d) {
          $timedata = isset($logs_by_pid_date[$pid][$date_d]) ? $logs_by_pid_date[$pid][$date_d] : null;
          $hrs = $this->_hours_from_log($timedata);
          $row[] = $hrs !== 0 ? $hrs : '';
          if ($hrs !== 0) {
            $personhrs[] = $hrs;
          }
        }
        $worked = count($personhrs);
        $row[] = array_sum($personhrs);
        $row[] = $worked;
        fputcsv($fh, $row);
      }
      unset($batch, $pids, $logs_by_pid_date, $scheduledDaysByPid);
    }
    fclose($fh);
    exit;
  }
  public function test()
  {
    $staffs = $this->empModel->fetchAllStaff(10, 0, 'ihris_pid', 0);
    print_r($staffs);
  }
  public function timesheet()
  {
    // Month/year filters for monthly timesheet
    $month = $this->input->post('month');
    $year = $this->input->post('year');

    if (!empty($month)) {
      $_SESSION['month'] = $month;
      $_SESSION['year'] = $year ?: date('Y');
    }

    if (!empty($_SESSION['year'])) {
      $data['month'] = $_SESSION['month'];
      $data['year'] = $_SESSION['year'];
    } else {
      $_SESSION['month'] = date('m');
      $_SESSION['year'] = date('Y');
      $data['month'] = $_SESSION['month'];
      $data['year'] = $_SESSION['year'];
    }

    // Also expose date_from/date_to for header if needed
    $data['date_from'] = date('Y-m-01', strtotime($data['year'] . '-' . $data['month'] . '-01'));
    $data['date_to'] = date('Y-m-t', strtotime($data['year'] . '-' . $data['month'] . '-01'));
    $data['title'] = 'Timesheet';
    $data['uptitle'] = 'Timesheet Report';
    $data['view'] = 'timesheet';
    $data['module'] = 'employees';
    echo Modules::run('templates/main', $data);
  }

  /**
   * Server-side DataTables AJAX endpoint for the timesheet report.
   * Returns a paginated set of employees with day-by-day hours for the selected month.
   */
  public function timesheetAjax()
  {
    // JSON response
    header('Content-Type: application/json');

    $draw = (int)($this->input->post('draw') ?? 1);
    $start = (int)($this->input->post('start') ?? 0);
    $length = (int)($this->input->post('length') ?? 20);
    if ($length <= 0) {
      $length = 20;
    }

    $search_post = $this->input->post('search');
    $search = '';
    if (!empty($search_post) && isset($search_post['value'])) {
      $search = trim((string)$search_post['value']);
    }

    // Filters
    $employee = (string)($this->input->post('empid') ?? '');
    $job = (string)($this->input->post('job') ?? '');
    $month = (string)($this->input->post('month') ?? ($_SESSION['month'] ?? date('m')));
    $year = (string)($this->input->post('year') ?? ($_SESSION['year'] ?? date('Y')));

    if (strlen($month) === 1) {
      $month = '0' . $month;
    }
    if (empty($year)) {
      $year = date('Y');
    }

    $startDate = date('Y-m-01', strtotime($year . '-' . $month . '-01'));
    $endDate = date('Y-m-t', strtotime($year . '-' . $month . '-01'));

    $daysInMonth = (int)cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);
    $dateList = array();
    for ($d = 1; $d <= $daysInMonth; $d++) {
      $dateList[] = date('Y-m-d', strtotime($year . '-' . $month . '-' . str_pad((string)$d, 2, '0', STR_PAD_LEFT)));
    }

    try {
      // Counts
      $total_records = $this->empModel->countTimesheetAjax($this->filters, $employee, $job, '');
      $filtered_records = $this->empModel->countTimesheetAjax($this->filters, $employee, $job, $search);

      // Page employees
      $employees = $this->empModel->fetchTimesheetEmployeesAjax($this->filters, $employee, $job, $start, $length, $search);
      $pids = array();
      if (!empty($employees)) {
        $pids = array_values(array_filter(array_column($employees, 'ihris_pid')));
      }

      // Bulk fetch time logs for this page/range
      $hoursByPidDate = array(); // [pid][Y-m-d] => hours(float)
      if (!empty($pids)) {
        $logs = $this->db
          ->select('ihris_pid, date, time_in, time_out')
          ->from('clk_log')
          ->where_in('ihris_pid', $pids)
          ->where('date >=', $startDate)
          ->where('date <=', $endDate)
          ->get()
          ->result();

        foreach ($logs as $log) {
          $pid = (string)$log->ihris_pid;
          if (empty($pid) || empty($log->date)) {
            continue;
          }
          $logDate = date('Y-m-d', strtotime($log->date));
          if (!in_array($logDate, $dateList, true)) {
            continue;
          }

          $startTime = isset($log->time_in) ? (string)$log->time_in : '';
          $endTime = isset($log->time_out) ? (string)$log->time_out : '';

          $hours_worked = 0.0;
          if (!empty($startTime) && !empty($endTime)) {
            $initial_time = strtotime($startTime) / 3600;
            $final_time = strtotime($endTime) / 3600;
            if (!empty($initial_time) && !empty($final_time) && $initial_time != $final_time) {
              $hours_worked = round(($final_time - $initial_time), 1);
            }
          }
          if ($hours_worked < 0) {
            $hours_worked = $hours_worked * -1;
          }
          if ($hours_worked == -0.0) {
            $hours_worked = 0.0;
          }

          // If multiple logs exist for the same day, keep the max (closest to prior behavior)
          if (!isset($hoursByPidDate[$pid])) {
            $hoursByPidDate[$pid] = array();
          }
          if (!isset($hoursByPidDate[$pid][$logDate])) {
            $hoursByPidDate[$pid][$logDate] = $hours_worked;
          } else {
            $hoursByPidDate[$pid][$logDate] = max((float)$hoursByPidDate[$pid][$logDate], (float)$hours_worked);
          }
        }
      }

      // Bulk fetch duty roster schedule counts (Day=14, Evening=15, Night=16)
      $scheduledDaysByPid = array(); // [pid] => int
      if (!empty($pids)) {
        $rows = $this->db
          ->select('ihris_pid, schedule_id, COUNT(*) as days')
          ->from('duty_rosta')
          ->where_in('ihris_pid', $pids)
          ->where_in('schedule_id', array(14, 15, 16))
          ->where('duty_date >=', $startDate)
          ->where('duty_date <=', $endDate)
          ->group_by(array('ihris_pid', 'schedule_id'))
          ->get()
          ->result();

        foreach ($rows as $r) {
          $pid = (string)$r->ihris_pid;
          $days = isset($r->days) ? (int)$r->days : 0;
          if (!isset($scheduledDaysByPid[$pid])) {
            $scheduledDaysByPid[$pid] = 0;
          }
          $scheduledDaysByPid[$pid] += $days;
        }
      }

      // Build DataTables rows
      $data = array();
      $rowNo = $start + 1;
      foreach ($employees as $emp) {
        $pid = (string)$emp['ihris_pid'];
        $fullname = (string)$emp['fullname'];
        $position = (string)$emp['job'];

        $row = array();
        $row[] = $rowNo++;
        $row[] = $fullname;
        $row[] = $position;

        $totalHours = 0.0;
        $daysWorked = 0;
        foreach ($dateList as $dStr) {
          $val = '';
          if (isset($hoursByPidDate[$pid]) && array_key_exists($dStr, $hoursByPidDate[$pid])) {
            $val = $hoursByPidDate[$pid][$dStr];
            $totalHours += (float)$val;
            $daysWorked++;
          }
          $row[] = ($val === '' ? '' : $val);
        }

        $row[] = round($totalHours, 1);
        $row[] = $daysWorked;

        $data[] = $row;
      }

      echo json_encode(array(
        'draw' => $draw,
        'recordsTotal' => $total_records,
        'recordsFiltered' => $filtered_records,
        'data' => $data
      ));
      exit;
    } catch (Throwable $e) {
      log_message('error', 'timesheetAjax error: ' . $e->getMessage());
      echo json_encode(array(
        'draw' => $draw,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => array(),
        'error' => 'Failed to load timesheet data'
      ));
      exit;
    }
  }
  public function getdata()
  {
    //$data['duties'][0]
    $data['duties'] = $this->empModel->fetch_TimeSheet();
    // print_r($data['duties']);
  }
  public function employeeTimeLogs($ihris_pid = FALSE, $print = false, $from = false, $to = false)
  {
    // Check if ihris_pid is provided
    if (!$ihris_pid) {
      // Try to get it from URI segment if not provided as parameter
      $ihris_pid = $this->uri->segment(3);
    }
    
    // Decode the URL-encoded parameter
    if ($ihris_pid) {
      $ihris_pid = urldecode($ihris_pid);
    } else {
      show_error('Employee ID is required', 400);
      return;
    }

    $post = $this->input->post();
    if ($post) {
      $search_data = $this->input->post();
      $data['from'] = isset($search_data['date_from']) ? $search_data['date_from'] : date('Y-m-') . '01';
      $data['to'] = isset($search_data['date_to']) ? $search_data['date_to'] : date('Y-m-d');
    } else {
      $data['from'] = date('Y-m-') . '01';
      $data['to'] = date('Y-m-d');
      $search_data = array();
      $search_data['date_from'] = $data['from'];
      $search_data['date_to'] = $data['to'];
    }

    try {
      $dbresult = $this->empModel->getEmployeeTimeLogs($ihris_pid, 10000, 0, $search_data);
      $data['timelogs'] = isset($dbresult['timelogs']) ? $dbresult['timelogs'] : array();
      $data['employee'] = isset($dbresult['employee']) ? $dbresult['employee'] : null;
      $data['leaves'] = isset($dbresult['leaves']) ? $dbresult['leaves'] : array();
      $data['offs'] = isset($dbresult['offs']) ? $dbresult['offs'] : array();
      $data['requests'] = isset($dbresult['requests']) ? $dbresult['requests'] : array();
      $data['workdays'] = isset($dbresult['dutydays']) ? $dbresult['dutydays'] : array();
    } catch (Exception $e) {
      log_message('error', 'Error in employeeTimeLogs: ' . $e->getMessage());
      $data['timelogs'] = array();
      $data['employee'] = null;
      $data['leaves'] = array();
      $data['offs'] = array();
      $data['requests'] = array();
      $data['workdays'] = array();
    }

    $data['title'] = "Health  Staff Individual Time Logs";
    $data['view'] = 'individual_time_logs';
    $data['module'] = "employees";
    echo Modules::run("templates/main", $data);
  }


  /**
   * Print Individual Time Logs: stream by batches to avoid memory lock.
   * Flag 1 = compact view, Flag 2 = detailed view (extra tables for leaves/requests/offs/duty days).
   */
  public function printindividualTimeLogs($ihris_pid = FALSE, $from = false, $to = false, $flag = FALSE)
  {
    $ihris_pid = $ihris_pid ? urldecode($ihris_pid) : '';
    if (!$ihris_pid) {
      show_error('Employee ID is required', 400);
      return;
    }
    if ($from && $to) {
      $date_from = date('Y-m-d', strtotime($from));
      $date_to = date('Y-m-d', strtotime($to));
    } else {
      $date_from = date('Y-m-') . '01';
      $date_to = date('Y-m-d');
    }

    $this->load->library('M_pdf');
    $filename = 'individual_timelogs_report_' . date('Y-m-d') . '.pdf';
    @set_time_limit(0);

    $meta = $this->empModel->get_employee_timelogs_meta($ihris_pid, $date_from, $date_to);
    $employee = $meta['employee'];
    $total_duty = count($meta['dutydays']);
    $total_leaves = count($meta['leaves']);
    $total_requests = count($meta['requests']);
    $total_offs = count($meta['offs']);

    $total = $this->empModel->count_employee_timelogs_range($ihris_pid, $date_from, $date_to);
    $batch_size = 200;
    $wdays_worked = 0;
    $total_hours = 0.0;

    if (!empty($this->watermark) && is_file($this->watermark)) {
    $this->m_pdf->pdf->SetWatermarkImage($this->watermark);
    $this->m_pdf->pdf->showWatermarkImage = true;
    }
    date_default_timezone_set('Africa/Kampala');
    $this->m_pdf->pdf->SetHTMLFooter('Printed/ Accessed on: <b>' . date('d F,Y h:i A') . '</b><br style="font-size: 9px;">Source: iHRIS - HRM Attend ' . base_url());

    $moh_logo = (defined('FCPATH') && is_file(FCPATH . 'assets/img/MOH.png')) ? FCPATH . 'assets/img/MOH.png' : '';
    $header_data = array(
      'employee' => $employee,
      'from' => $date_from,
      'to' => $date_to,
      'summary_label' => ($flag == 2) ? 'HOURS' : 'SUMMARY',
      'moh_logo_path' => $moh_logo,
    );
    $header_html = $this->load->view('itl_print_header', $header_data, true);
    $this->m_pdf->pdf->WriteHTML(mb_convert_encoding($header_html, 'UTF-8', 'UTF-8'));

    for ($offset = 0; $offset < $total; $offset += $batch_size) {
      $batch = $this->empModel->get_employee_timelogs_batch($ihris_pid, $date_from, $date_to, $offset, $batch_size);
      if (empty($batch)) {
        break;
      }
      foreach ($batch as $log) {
        $wdays_worked++;
        $ti = $log->time_in ? strtotime($log->time_in) / 3600 : 0;
        $to_hr = $log->time_out ? strtotime($log->time_out) / 3600 : 0;
        if ($ti != 0 && $to_hr != 0 && $ti != $to_hr) {
          $total_hours += round($to_hr - $ti, 1);
        }
      }
      $rows_data = array('timelogs' => $batch, 'start_row_no' => $wdays_worked - count($batch) + 1);
      $rows_html = $this->load->view('itl_print_rows', $rows_data, true);
      $this->m_pdf->pdf->WriteHTML(mb_convert_encoding($rows_html, 'UTF-8', 'UTF-8'));
      unset($batch, $rows_data, $rows_html);
    }

    if ($flag == 2) {
      $footer_data = array(
        'total_duty' => $total_duty,
        'wdays_worked' => $wdays_worked,
        'leaves' => $meta['leaves'],
        'requests' => $meta['requests'],
        'offs' => $meta['offs'],
        'workdays' => $meta['dutydays'],
      );
      $footer_html = $this->load->view('itl_print_footer_detailed', $footer_data, true);
    } else {
      $footer_data = array(
        'total_duty' => $total_duty,
        'total_leaves' => $total_leaves,
        'total_requests' => $total_requests,
        'total_offs' => $total_offs,
        'wdays_worked' => $wdays_worked,
        'total_hours' => $total_hours,
      );
      $footer_html = $this->load->view('itl_print_footer', $footer_data, true);
    }
    $this->m_pdf->pdf->WriteHTML(mb_convert_encoding($footer_html, 'UTF-8', 'UTF-8'));
    $this->m_pdf->pdf->Output($filename, 'I');
  }
}
