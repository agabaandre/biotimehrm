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
    
    // Get global search
    $globalSearch = $this->input->post('globalSearch');
    
    // Get total count
    $total_records = $this->empModel->get_employees_count($this->filters);
    
    // Get filtered data
    $data = $this->empModel->get_employees_ajax(
      $this->filters,
      $start, 
      $length, 
      $search, 
      $order_column, 
      $order_dir,
      $globalSearch
    );
    
    // Get filtered count for search
    $filtered_records = $this->empModel->get_employees_count($this->filters, $globalSearch);
    
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
    
    // Get filters
    $job = $this->input->post('job');
    $facility = $this->input->post('facility');
    
    // Get total count
    $total_records = $this->empModel->district_employees($_SESSION['district'], $job, $facility, 'count', 0, 0, FALSE);
    
    // Get filtered data
    $data = $this->empModel->district_employees_ajax(
      $_SESSION['district'], 
      $job, 
      $facility, 
      $start, 
      $length, 
      $search, 
      $order_column, 
      $order_dir
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
    $date_from = $this->input->post('date_from');
    $date_to = $this->input->post('date_to');
    $search_data['name'] = $this->input->post('name');
    if (!empty($date_from)) {
      $_SESSION['date_from'] = $date_from;
      $_SESSION['date_to'] = $date_to;
      $search_data['date_from'] = $_SESSION['date_from'];
      $search_data['date_to'] = $_SESSION['date_to'];
    }

    if (!empty($_SESSION['date_from'])) {
      $search_data['date_from'] = $_SESSION['date_from'];
      $search_data['date_to'] = $_SESSION['date_to'];
    } else {
      $date_from = date("Y-m-d", strtotime("-1 month"));
      $date_to = date('Y-m-d');
      $_SESSION['date_from'] = $date_from;
      $_SESSION['date_to'] = $date_to;
      $search_data['date_from'] = $_SESSION['date_from'];
      $search_data['date_to'] = $_SESSION['date_to'];
    }
    $config = array();
    $config['base_url'] = base_url() . "employees/viewTimeLogs";
    $config['total_rows'] = $this->empModel->count_timelogs($search_data, $this->filters);
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
    $data['timelogs'] = $this->empModel->getTimeLogs($config['per_page'], $page, $search_data, $this->filters);
    $data['title'] = "Staff Time Log Report";
    $data['uptitle'] = "Staff Time Log Report";
    $data['view'] = 'time_logs';
    $data['module'] = "employees";
    echo Modules::run("templates/main", $data);
  }
  public function groupedTimeLogs()
  {
    $search_data = $this->input->post();
    $config = array();
    $config['base_url'] = base_url() . "employees/groupedTimeLogs";
    $config['total_rows'] = $this->empModel->count_monthlytimelogs($search_data, $this->ufilters);
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
    $data['timelogs'] = $this->empModel->groupmonthlyTimeLogs($config['per_page'], $page, $search_data, $this->ufilters);
    $data['title'] = "Monthly Time Log Report";
    $data['uptitle'] = "Monthly Time Log Report";
    $data['view'] = 'groupedtime_logs';
    $data['module'] = "employees";
    echo Modules::run("templates/main", $data);
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
    $data['logs'] = $this->empModel->timelogscsv($datef, $datet, str_replace("person", "", $person), str_replace("position-", "", urldecode(str_replace('_', ' ', $job))), $this->filters);
    $this->load->library('ML_pdf');
    $fac = $_SESSION['facility_name'];
    $filename = $fac . " Staff_Timelog_Report_" . ".pdf";
    ini_set('max_execution_time', 0);
    $html = $this->load->view('print_time_logs', $data, true);
    $PDFContent = mb_convert_encoding($html, 'UTF-8', 'UTF-8');
    $this->ml_pdf->pdf->SetWatermarkImage($this->watermark);
    date_default_timezone_set("Africa/Kampala");
    $this->ml_pdf->pdf->SetHTMLFooter("Printed/ Accessed on: <b>" . date('d F,Y h:i A') . "</b><br style='font-size: 9px !imporntant;'>" . " Source: iHRIS - HRM Attend " . base_url());
    $this->ml_pdf->pdf->SetWatermarkImage($this->watermark);
    ini_set('max_execution_time', 0);

    // Chunked WriteHTML to avoid pcre.backtrack_limit issues for big timelog PDFs
    $parts = preg_split('/(<\/tr>)/i', $PDFContent, -1, PREG_SPLIT_DELIM_CAPTURE);
    $buffer = '';
    $chunkLimit = 80000;
    foreach ($parts as $part) {
      $buffer .= $part;
      if (strlen($buffer) >= $chunkLimit) {
        $this->ml_pdf->pdf->WriteHTML($buffer);
        $buffer = '';
      }
    }
    if (trim($buffer) !== '') {
      $this->ml_pdf->pdf->WriteHTML($buffer);
    }
    //download it D save F.
    $this->ml_pdf->pdf->Output($filename, 'I');
  }
  public function print_timesheet($month, $year, $employee, $job)
  {
    $this->load->library('ML_pdf');
    $date = $year . '-' . $month;
    $data['year'] = $year;
    $data['month'] = $month;
    $data['date'] = $date;
    if (empty($date)) {
      $date = date('Y-m');
    }
    $data['workinghours'] = $this->empModel->fetch_TimeSheet($date, $perpage = FALSE, $page = FALSE, str_replace("emp", "", urldecode($employee)), $this->filters, str_replace("job", "", $job));

    // Optimized: prefetch all clk_log rows for this month + set of employees,
    // so the PDF view does not call gettimedata() per cell.
    $pids = array();
    foreach ($data['workinghours'] as $row) {
      if (!empty($row['ihris_pid'])) {
        $pids[] = $row['ihris_pid'];
      }
    }
    $logs_by_pid_date = array();
    if (!empty($pids)) {
      $startDate = date('Y-m-01', strtotime($date . '-01'));
      $endDate = date('Y-m-t', strtotime($date . '-01'));
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
        $d = $log->date;
        if (!isset($logs_by_pid_date[$pid])) {
          $logs_by_pid_date[$pid] = array();
        }
        $logs_by_pid_date[$pid][$d] = $log;
      }
    }
    $data['logs_by_pid_date'] = $logs_by_pid_date;
    $this->load->library('ML_pdf');
    $fac = $_SESSION['facility_name'];
    $filename = $fac . " Timesheet_Report_" . $date . ".pdf";
    ini_set('max_execution_time', 0);
    $html = $this->load->view('print_timesheet', $data, true);
    $PDFContent = mb_convert_encoding($html, 'UTF-8', 'UTF-8');
    $this->ml_pdf->pdf->SetWatermarkImage($this->watermark);
    date_default_timezone_set("Africa/Kampala");
    $this->ml_pdf->pdf->SetHTMLFooter("<p style='font-size:8px'>Printed/ Accessed on: " . date('d F,Y h:i A') . ", Source: iHRIS - HRM Attend " . base_url() . "</p>");
    $this->ml_pdf->pdf->SetWatermarkImage($this->watermark);
    ini_set('max_execution_time', 0);

    // Chunked WriteHTML for monthly timesheet
    $parts = preg_split('/(<\/tr>)/i', $PDFContent, -1, PREG_SPLIT_DELIM_CAPTURE);
    $buffer = '';
    $chunkLimit = 80000;
    foreach ($parts as $part) {
      $buffer .= $part;
      if (strlen($buffer) >= $chunkLimit) {
        $this->ml_pdf->pdf->WriteHTML($buffer);
        $buffer = '';
      }
    }
    if (trim($buffer) !== '') {
      $this->ml_pdf->pdf->WriteHTML($buffer);
    }
    //download it D save F.
    $this->ml_pdf->pdf->Output($filename, 'I');
  }
  public function csv_timesheet($month, $year, $employee, $job)
  {
    $date = $year . '-' . $month;
    $data['year'] = $year;
    $data['month'] = $month;
    $data['date'] = $date;
    if (empty($date)) {
      $date = date('Y-m');
    }
    ini_set('max_execution_time', 0);
    $datas = $data['workinghours'] = $this->empModel->fetch_TimeSheet($date, $perpage = FALSE, $page = FALSE, str_replace("emp", "", urldecode($employee)), $this->filters, str_replace("job", "", $job));

    // Optimized: prefetch all clk_log rows for this month + employees
    $pids = array();
    foreach ($datas as $row) {
      if (!empty($row['ihris_pid'])) {
        $pids[] = $row['ihris_pid'];
      }
    }
    $logs_by_pid_date = array();
    if (!empty($pids)) {
      $startDate = date('Y-m-01', strtotime($date . '-01'));
      $endDate = date('Y-m-t', strtotime($date . '-01'));
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
        $d = $log->date;
        if (!isset($logs_by_pid_date[$pid])) {
          $logs_by_pid_date[$pid] = array();
        }
        $logs_by_pid_date[$pid][$d] = $log;
      }
    }
    $fac = $_SESSION['facility_name'];
    $csv_file = $fac . " Attend_TimeLogs" . date('Y-m-d') . '_' . $_SESSION['facility'] . ".csv";
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"$csv_file\"");
    $fh = fopen('php://output', 'w');
    $records = array(); //output each row of the data, format line as csv and write to file pointer
    $month_days = cal_days_in_month(CAL_GREGORIAN, $month, $year); //days in a month
    foreach ($datas as $data) {
      $personhrs = array();
      $days_worked = array();
      for ($i = 1; $i <= $month_days; $i++) { // repeating td
        $day = "day" . $i;  //changing day 
        $date_d = $year . "-" . $month . "-" . (($i < 10) ? "0" . $i : $i);
        $date = $year . "-" . $month;
        //  print_r($hours_data);
        $timedata = isset($logs_by_pid_date[$data['ihris_pid']][$date_d]) ? $logs_by_pid_date[$data['ihris_pid']][$date_d] : null;
        if (!empty($timedata)) {
          $starTime = @$timedata->time_in;
          $endTime = @$timedata->time_out;
          $initial_time = strtotime($starTime) / 3600;
          $final_time = strtotime($endTime) / 3600;
          if (empty($initial_time) || empty($final_time)) {
            $hours_worked = 0;
          } elseif ($initial_time == $final_time) {
            $hours_worked = 0;
          } else {
            $hours_worked = round(($final_time - $initial_time), 1);
          }
          if ($hours_worked < 0) {
            $hours_worked = $hours_worked * -1;
          } elseif ($hours_worked == -0) {
            $hours_worked = 0;
          } else {
            $hours_worked;
          }
          if (!empty($starTime)) {
            $wdays = 1;
            array_push($days_worked, $wdays);
          }
          array_push($personhrs, $hours_worked);
        }
      }


      $roster = Modules::run('attendance/attrosta', $date, urlencode($data['ihris_pid']));
      $day = $roster['Day'][0]->days;
      $eve = $roster['Evening'][0]->days;
      $night = $roster['Night'][0]->days;
      $days_scheduled = $day + $eve + $night;

      // print_r($data['fullname']);
      $days = array("NAME" => $data['fullname'], "JOB" => $data['job'], "FACILITY" => $data['facility'], "DEPARTMENT" => $data['department'], "PERIOD" => $date, "DAYS WORKED" => array_sum($days_worked), "DAYS SCHEDULED" => $days_scheduled, "HOURS WORKED" => array_sum($personhrs));
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

  /**
   * Print timesheet for a date range (max 31 days).
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
    $daysDiff = (int)floor((strtotime($endDate) - strtotime($startDate)) / 86400) + 1;
    if ($daysDiff > 31) {
      $endDate = date('Y-m-d', strtotime($startDate . ' +30 days'));
      $daysDiff = 31;
    }

    $data = array();
    $data['date_from'] = $startDate;
    $data['date_to'] = $endDate;
    $data['month'] = date('m', strtotime($startDate));
    $data['year'] = date('Y', strtotime($startDate));

    $data['workinghours'] = $this->empModel->fetch_TimeSheet(
      $startDate, // date_range (not used heavily in model)
      $perpage = FALSE,
      $page = FALSE,
      str_replace("emp", "", urldecode($employee)),
      $this->filters,
      str_replace("job", "", urldecode($job))
    );

    // Precompute scheduled days per employee for the range
    $pids = array();
    foreach ($data['workinghours'] as $row) {
      if (!empty($row['ihris_pid'])) {
        $pids[] = $row['ihris_pid'];
      }
    }
    $scheduledDaysByPid = array();
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
    $data['scheduledDaysByPid'] = $scheduledDaysByPid;

    $fac = $_SESSION['facility_name'];
    $filename = $fac . " Timesheet_Report_" . $startDate . "_to_" . $endDate . ".pdf";
    ini_set('max_execution_time', 0);

    $html = $this->load->view('print_timesheet', $data, true);
    $PDFContent = mb_convert_encoding($html, 'UTF-8', 'UTF-8');

    $this->ml_pdf->pdf->SetWatermarkImage($this->watermark);
    date_default_timezone_set("Africa/Kampala");
    $this->ml_pdf->pdf->SetHTMLFooter("<p style='font-size:8px'>Printed/ Accessed on: " . date('d F,Y h:i A') . ", Source: iHRIS - HRM Attend " . base_url() . "</p>");

    // Chunked WriteHTML for range timesheet (avoid backtrack limit)
    $parts = preg_split('/(<\/tr>)/i', $PDFContent, -1, PREG_SPLIT_DELIM_CAPTURE);
    $buffer = '';
    $chunkLimit = 80000;
    foreach ($parts as $part) {
      $buffer .= $part;
      if (strlen($buffer) >= $chunkLimit) {
        $this->ml_pdf->pdf->WriteHTML($buffer);
        $buffer = '';
      }
    }
    if (trim($buffer) !== '') {
      $this->ml_pdf->pdf->WriteHTML($buffer);
    }
    $this->ml_pdf->pdf->Output($filename, 'I');
  }

  /**
   * CSV timesheet summary for a date range (max 31 days).
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
    $daysDiff = (int)floor((strtotime($endDate) - strtotime($startDate)) / 86400) + 1;
    if ($daysDiff > 31) {
      $endDate = date('Y-m-d', strtotime($startDate . ' +30 days'));
      $daysDiff = 31;
    }

    ini_set('max_execution_time', 0);
    $datas = $this->empModel->fetch_TimeSheet(
      $startDate,
      $perpage = FALSE,
      $page = FALSE,
      str_replace("emp", "", urldecode($employee)),
      $this->filters,
      str_replace("job", "", urldecode($job))
    );

    $fac = $_SESSION['facility_name'];
    $csv_file = $fac . "_Timesheet_" . $startDate . "_to_" . $endDate . ".csv";
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"$csv_file\"");
    $fh = fopen('php://output', 'w');

    $pids = array();
    foreach ($datas as $row) {
      if (!empty($row['ihris_pid'])) {
        $pids[] = $row['ihris_pid'];
      }
    }

    // Scheduled days per pid
    $scheduledDaysByPid = array();
    if (!empty($pids)) {
      $rows = $this->db
        ->select('ihris_pid, COUNT(*) as days')
        ->from('duty_rosta')
        ->where_in('ihris_pid', $pids)
        ->where_in('schedule_id', array(14, 15, 16))
        ->where('duty_date >=', $startDate)
        ->where('duty_date <=', $endDate)
        ->group_by('ihris_pid')
        ->get()
        ->result();
      foreach ($rows as $r) {
        $scheduledDaysByPid[(string)$r->ihris_pid] = (int)$r->days;
      }
    }

    // Days worked + hours worked per pid from clk_log
    $workStatsByPid = array(); // pid => ['days_worked'=>int, 'hours_worked'=>float]
    if (!empty($pids)) {
      $stats = $this->db->query("
        SELECT ihris_pid,
               COUNT(DISTINCT date) as days_worked,
               SUM(CASE
                     WHEN time_in IS NOT NULL AND time_out IS NOT NULL AND time_in != '' AND time_out != ''
                     THEN ABS(TIMESTAMPDIFF(MINUTE, time_in, time_out)) / 60
                     ELSE 0
                   END) as hours_worked
        FROM clk_log
        WHERE ihris_pid IN (" . implode(',', array_map(array($this->db, 'escape'), $pids)) . ")
          AND date >= " . $this->db->escape($startDate) . "
          AND date <= " . $this->db->escape($endDate) . "
        GROUP BY ihris_pid
      ")->result();

      foreach ($stats as $s) {
        $workStatsByPid[(string)$s->ihris_pid] = array(
          'days_worked' => (int)$s->days_worked,
          'hours_worked' => round((float)$s->hours_worked, 1)
        );
      }
    }

    $records = array();
    foreach ($datas as $row) {
      $pid = (string)$row['ihris_pid'];
      $scheduled = isset($scheduledDaysByPid[$pid]) ? (int)$scheduledDaysByPid[$pid] : 0;
      $daysWorked = isset($workStatsByPid[$pid]) ? (int)$workStatsByPid[$pid]['days_worked'] : 0;
      $hoursWorked = isset($workStatsByPid[$pid]) ? (float)$workStatsByPid[$pid]['hours_worked'] : 0.0;

      $records[] = array(
        "NAME" => $row['fullname'],
        "JOB" => $row['job'],
        "PERIOD" => $startDate . " to " . $endDate,
        "DAYS WORKED" => $daysWorked,
        "DAYS SCHEDULED" => $scheduled,
        "HOURS WORKED" => $hoursWorked
      );
    }

    $is_header = true;
    foreach ($records as $rec) {
      if ($is_header) {
        fputcsv($fh, array_keys($rec));
        $is_header = false;
      }
      fputcsv($fh, array_values($rec));
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

        $scheduled = isset($scheduledDaysByPid[$pid]) ? (int)$scheduledDaysByPid[$pid] : 0;
        $row[] = round($totalHours, 1);
        $row[] = $daysWorked . '/' . $scheduled;
        $percent = ($scheduled > 0) ? round(($daysWorked / $scheduled) * 100, 0) : 0;
        $row[] = $percent . '%';

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


  public function printindividualTimeLogs($ihris_pid=FALSE, $from = false, $to = false, $flag=FALSE)
  {
    if ($from) {
      // $from= str_replace('-','/',$from);
      $from = date("Y-m-d", strtotime($from));
      // $to= str_replace('-','/',$to);
      $to = date("Y-m-d", strtotime($to));
      $search_data2['date_from'] = $from;
      $search_data2['date_to'] = $to;
      //print_r($search_data2);
    } else {
      $search_data2['from'] = date('Y-m-') . '01';
      $search_data2['to'] = date('Y-m-d');
    }
    $this->load->library('M_pdf');
    $filename = "individual_timelogs_report_" . ".pdf";
    ini_set('max_execution_time', 0);
    $dbresult = $this->empModel->getEmployeeTimeLogs(urldecode($ihris_pid), 100000, 0, $search_data = NULL, $search_data2);
    $data['timelogs'] = $dbresult['timelogs'];
    $data['employee'] = $dbresult['employee'];
    $data['leaves'] = $dbresult['leaves'];
    $data['offs'] = $dbresult['offs'];
    $data['workdays'] = $dbresult['dutydays'];
    $data['requests'] = $dbresult['requests'];
    $data['to'] = $search_data2['to'];
    $data['from'] = $search_data2['from'];
    $data['links'] = $this->pagination->create_links();
    $data['title'] = "Health  Staff Individual Time Logs";
    if ($flag == 1) {
      $view = 'print_individual_time_logs';
    } else {
      $view = 'printdetailslog';
    }
    $html = $this->load->view($view, $data, true);
    $PDFContent = mb_convert_encoding($html, 'UTF-8', 'UTF-8');
    $this->m_pdf->pdf->SetWatermarkImage($this->watermark);
    date_default_timezone_set("Africa/Kampala");
    $this->m_pdf->pdf->SetHTMLFooter("Printed/ Accessed on: <b>" . date('d F,Y h:i A') . "</b><br style='font-size: 9px !imporntant;'>" . " Source: iHRIS - HRM Attend " . base_url());
    $this->m_pdf->pdf->SetWatermarkImage($this->watermark);
    ini_set('max_execution_time', 0);
    $this->m_pdf->pdf->WriteHTML($PDFContent); //ml_pdf because we loaded the library ml_pdf for landscape format not m_pdf
    //download it D save F.
    $this->m_pdf->pdf->Output($filename, 'I');
  }
}
