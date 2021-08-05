<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Employees extends MX_Controller{
	public  function __construct(){
		parent:: __construct();
        $this->load->model('employee_model','empModel');
        $this->user=$this->session->get_userdata();
        $this->load->library('pagination');
        $this->watermark=FCPATH."assets/img/MOH.png";
        //requires a join on ihrisdata
        $this->filters=Modules::run('filters/sessionfilters');
        //doesnt require a join on ihrisdata
        $this->ufilters=Modules::run('filters/universalfilters');
        // requires a join on ihrisdata with district level
        $this->distfilters=Modules::run('filters/districtfilters');
	}
    public function filters(){
    print_r ($this->filters);
    }
   public function get_employees()
    {
    return$employees=$this->empModel->get_employees($this->filters);
    }
    public function getEmployee($id){
        $employee=$this->empModel->get_employee($id);
        return  $employee;
    }
	public function index(){
		$data['title']="Staff";
		$data['facilities']=Modules::run("facilities/getFacilities");
		$data['view']='staff';
        $data['uptitle']="Staff List";
		$data['module']="employees";
		echo Modules::run("templates/main",$data);
    }
    public function personlogs(){
		$data['title']="Person Logs";
		$data['view']='personlogs';
		$data['module']="employees";
        $data['uptitle']="Person Attendance";
		echo Modules::run("templates/main",$data);
	}
	public function getStaffDatatable()
    {
		$columns = array(  
                            0=>'ipps',                
                            2=>'ihris_pid', 
                            3 =>'surname',
                            4=> 'firstname',
                            5=> 'othername',
                            6=> 'job',
                            7=>'facility'
                        );
		$limit = $this->input->post('length');
        $start = $this->input->post('start');
        $order = $columns[$this->input->post('order')[0]['column']];
        $dir = $this->input->post('order')[0]['dir'];
        $totalData = $this->empModel->countStaff();
        $totalFiltered = $totalData; 
        if(empty($this->input->post('search')['value']))
        {            
            $staffs = $this->empModel->fetchAllStaff($limit,$start,$order,$dir);
        }
        else {
            $search = $this->input->post('search')['value']; 
            $staffs =  $this->empModel->searchStaff($limit,$start,$search,$order,$dir);
            $totalFiltered = $this->empModel->countforSearch($search);
        }
        $data = array();
        if(!empty($staffs))
        {
            foreach ($staffs as $staff)
            {
                $row['ipps'] = $staff->ipps;
                $row['ihris_pid'] = str_replace("person|","", $staff->ihris_pid);
                $row['surname'] = $staff->surname;
                $row['firstname'] =$staff->firstname;
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
    public function count_Staff(){
        $number=$this->empModel->count_Staff($this->filters);
     return $number;
    }
    public function attCsv($datef,$datet,$person,$job)
	{
    $datas=$this->empModel->timelogscsv($datef,$datet,str_replace("person","",$person),str_replace("position-","",urldecode(str_replace('_',' ',$job))),$this->filters);
    $csv_file = "Attend_TimeLogs" . date('Y-m-d') .'_'.$_SESSION['facility'] .".csv";	
	header("Content-Type: text/csv");
	header("Content-Disposition: attachment; filename=\"$csv_file\"");	
	$fh = fopen( 'php://output', 'w' );
    $records=array();//output each row of the data, format line as csv and write to file pointer
     foreach($datas as $data){
        $time_in= $data->time_in;
        $time_out=$data->time_out;
        $initial_time = strtotime($time_in)/ 3600;
        $final_time = strtotime($time_out)/ 3600;
      if(($initial_time)==0 || ($final_time)==0){ 
        $hours_worked=0; 
      } 
      elseif($initial_time==$final_time){ 
        $hours_worked=0; 
      } 
      else { 
        $hours_worked = round(($final_time - $initial_time), 1);   
      } 
      if ($hours_worked<0){ 
        $hours = ($hours_worked*-1); 
      } 
      else { 
        $hours = $hours_worked;
      } 
      if(!empty($hours_worked)){
      }

      if (!empty($time_out=$data->time_out)) { $dtimeout=date('H:i:s', strtotime($time_out=$data->time_out)); }
       $dtimein=date('H:i:s', strtotime($time_in=$data->time_in));
        $days =array("NAME"=>$data->surname." ".$data->firstname." ". $data->othername,"JOB"=>$data->job, "FACILITY"=>$data->fac,"DEPARTMENT"=>$data->department, "DATE"=>$data->date, "TIME IN"=>$dtimein,"TIME OUT"=>$dtimeout,"HOURS WORKED"=>$hours);
        array_push($records,$days);
    }
    $is_coloumn = true;
	if(!empty($records)) {
	  foreach($records as $record) {
		if($is_coloumn) {		  	  
		  fputcsv($fh, array_keys($record));
		  $is_coloumn = false;
		}		
		fputcsv($fh, array_values($record));
	  }
	   fclose($fh);
	}
	exit;  
	}
    public function viewTimeLogs(){
      $date_from=$this->input->post('date_from');
      $date_to=$this->input->post('date_to');
      $search_data['name']=$this->input->post('name');
      if(!empty($date_from)){
        $_SESSION['date_from']= $date_from;
        $_SESSION['date_to']= $date_to;
        $search_data['date_from']= $_SESSION['date_from'];
        $search_data['date_to']= $_SESSION['date_to'];
        
       }
         
       if (!empty($_SESSION['date_from'])){
          $search_data['date_from']= $_SESSION['date_from'];
          $search_data['date_to']= $_SESSION['date_to'];
       }
       else{
        $date_from=date("Y-m-d",strtotime("-1 month"));
        $date_to=date('Y-m-d');
        $_SESSION['date_from']= $date_from;
        $_SESSION['date_to']= $date_to;
        $search_data['date_from']= $_SESSION['date_from'];
        $search_data['date_to']= $_SESSION['date_to'];
       
        
       }
        $config=array();
        $config['base_url']=base_url()."employees/viewTimeLogs";
        $config['total_rows']=$this->empModel->count_timelogs($search_data,$this->filters);
        $config['per_page']=200; //records per page
        $config['uri_segment']=3; //segment in url  
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
        $page=($this->uri->segment(3))? $this->uri->segment(3):0; //default starting point for limits 
        $data['links']=$this->pagination->create_links();
	    $data['timelogs']=$this->empModel->getTimeLogs($config['per_page'],$page,$search_data,$this->filters);
        $data['title']="Staff Time Log Report";
        $data['uptitle']="Staff Time Log Report";
        $data['view']='time_logs';
        $data['module']="employees";
        echo Modules::run("templates/main",$data);
	} 
    public function groupedTimeLogs(){
	      $search_data=$this->input->post();
        $config=array();
        $config['base_url']=base_url()."employees/groupedTimeLogs";
        $config['total_rows']=$this->empModel->count_monthlytimelogs($search_data,$this->ufilters);
        $config['per_page']=200; //records per page
        $config['uri_segment']=3; //segment in url  
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
        $page=($this->uri->segment(3))? $this->uri->segment(3):0; //default starting point for limits 
        $data['links']=$this->pagination->create_links();
	      $data['timelogs']=$this->empModel->groupmonthlyTimeLogs($config['per_page'],$page,$search_data,$this->ufilters);
        $data['title']="Monthly Time Log Report";
        $data['uptitle']="Monthly Time Log Report";
        $data['view']='groupedtime_logs';
        $data['module']="employees";
        echo Modules::run("templates/main",$data);
	} 
    // public function testing(){
    //     $search_data=$this->input->post();
    //     $data['timelogs']=$this->empModel->getTimeLogs($config['per_page']=1,$page=1,$search_data);
    //  print_r($data);
    // } 
    Public function printStafflist(){  
        $this->load->library('M_pdf');
        $data=$this->empModel->get_employees();
        $html=$this->load->view('employees/printstaff',$data,true);
        //$fac=$_SESSION['facility'];
        $filename="Stafflist_"."pdf";
        ini_set('max_execution_time',0);
        $PDFContent = mb_convert_encoding($html, 'UTF-8', 'UTF-8');
        $this->m_pdf->pdf->SetWatermarkImage($this->watermark);
        $this->m_pdf->pdf->showWatermarkImage = true;
        date_default_timezone_set("Africa/Kampala");
        $this->m_pdf->pdf->SetHTMLFooter("Printed/ Accessed on: <b>".date('d F,Y h:i A')."</b><br style='font-size: 9px !imporntant;'>"."Source: iHRIS - HRM Attend " .base_url());
        $this->m_pdf->pdf->SetWatermarkImage($this->watermark);
        $this->m_pdf->showWatermarkImage = true;
        ini_set('max_execution_time',0);
        $this->m_pdf->pdf->WriteHTML($PDFContent); //ml_pdf because we loaded the library ml_pdf for landscape format not m_pdf
        //download it D save F.
        $this->m_pdf->pdf->Output($filename,'I');
    }
    public function print_timelogs($datef,$datet,$person,$job)
    {
        $data['logs']=$this->empModel->timelogscsv($datef,$datet,str_replace("person","",$person),str_replace("position-","",urldecode(str_replace('_',' ',$job))),$this->filters);
        $this->load->library('ML_pdf');
        $filename="Staff_Timelog_Report_"."pdf";
        ini_set('max_execution_time',0);
        $html=$this->load->view('print_time_logs',$data,true); 
        $PDFContent = mb_convert_encoding($html, 'UTF-8', 'UTF-8');
        $this->ml_pdf->pdf->SetWatermarkImage($this->watermark);
        $this->ml_pdf->pdf->showWatermarkImage = true;
        date_default_timezone_set("Africa/Kampala"); 
        $this->ml_pdf->pdf->SetHTMLFooter("Printed/ Accessed on: <b>".date('d F,Y h:i A')."</b><br style='font-size: 9px !imporntant;'>"." Source: iHRIS - HRM Attend " .base_url());
        $this->ml_pdf->pdf->SetWatermarkImage($this->watermark);
        $this->ml_pdf->showWatermarkImage = true;
        ini_set('max_execution_time',0);
        $this->ml_pdf->pdf->WriteHTML($PDFContent); //ml_pdf because we loaded the library ml_pdf for landscape format not ml_pdf
        //download it D save F.
        $this->ml_pdf->pdf->Output($filename,'I');
    }
    Public function print_timesheet($month,$year,$employee,$job){  
        $this->load->library('ML_pdf');
        $date=$year.'-'.$month;
        $data['year']=$year;
        $data['month']=$month;
        $data['date']=$date;
        if(empty($date)){
            $date=date('Y-m');
        }
        $data['workinghours']=$this->empModel->fetch_TimeSheet($date,$perpage=FALSE,$page=FALSE,str_replace("emp","",urldecode($employee)),$this->filters,str_replace("job","",$job));     
        $this->load->library('ML_pdf');
        $fac=$_SESSION['facility_name'];
        $filename=$fac." Timesheet_Report_".".pdf";
        ini_set('max_execution_time',0);
        $html=$this->load->view('print_timesheet',$data,true); 
        $PDFContent = mb_convert_encoding($html, 'UTF-8', 'UTF-8');
        $this->ml_pdf->pdf->SetWatermarkImage($this->watermark);
        $this->ml_pdf->pdf->showWatermarkImage = true;
        date_default_timezone_set("Africa/Kampala"); 
        $this->ml_pdf->pdf->SetHTMLFooter("Printed/ Accessed on: <b>".date('d F,Y h:i A')."</b><br style='font-size: 9px !imporntant;'>"." Source: iHRIS - HRM Attend " .base_url());
        $this->ml_pdf->pdf->SetWatermarkImage($this->watermark);
        $this->ml_pdf->showWatermarkImage = true;
        ini_set('max_execution_time',0);
        $this->ml_pdf->pdf->WriteHTML($PDFContent); //ml_pdf because we loaded the library ml_pdf for landscape format not ml_pdf
        //download it D save F.
        $this->ml_pdf->pdf->Output($filename,'I');
     
   
        
    }
    Public function csv_timesheet($month,$year,$employee,$job){  
        $date=$year.'-'.$month;
        $data['year']=$year;
        $data['month']=$month;
        $data['date']=$date;
        if(empty($date)){
            $date=date('Y-m');
        }
        ini_set('max_execution_time', 0);
        $datas= $data['workinghours']=$this->empModel->fetch_TimeSheet($date,$perpage=FALSE,$page=FALSE,str_replace("emp","",urldecode($employee)),$this->filters,str_replace("job","",$job));
        $csv_file = "Attend_TimeLogs" . date('Y-m-d') .'_'.$_SESSION['facility'] .".csv";	
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=\"$csv_file\"");	
        $fh = fopen( 'php://output', 'w' );
        $records=array();//output each row of the data, format line as csv and write to file pointer
        $month_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);//days in a month
         foreach($datas as $data){
            $personhrs=array();
            $days_worked=array();
            for($i=1;$i<=$month_days;$i++){// repeating td
                $day="day".$i;  //changing day $i;
                 $hours_data =$data[$day]; 
               //  print_r($hours_data);
                 if(!empty($hours_data))
                 {
                    $Time_data= array();
                    $Time_data=explode("|",$hours_data);
                    $starTime=@$Time_data[0];
                    $endTime=@$Time_data[1];
                    $initial_time = strtotime($starTime)/ 3600;
                    $final_time = strtotime($endTime)/ 3600;
                       if(empty($initial_time)|| empty($final_time)){ 
                        $hours_worked=0; 
                      } 
                      elseif($initial_time==$final_time){ 
                        $hours_worked=0; 
                      } 
                      else{
                        $hours_worked = round(($final_time - $initial_time),1);     
                      }
                    if ($hours_worked<0){ 
                        $hours_worked=$hours_worked*-1; 
                    } 
                    elseif ($hours_worked==-0){ 
                         $hours_worked=0; 
                    } 
                    else { 
                        $hours_worked; 
                    } 
                    if(!empty($hours_data)){
                        $wdays=1;
                    array_push($days_worked,$wdays);
                    }
                    array_push($personhrs,$hours_worked);
                 }
            }
           // print_r($data['fullname']);
            $days =array("NAME"=>$data['fullname'],"JOB"=>$data['job'], "FACILITY"=>$data['facility'],"DEPARTMENT"=>$data['department'], "PERIOD"=>$date, "DAYS WORKED"=>array_sum($days_worked), "HOURS WORKED"=>array_sum($personhrs));
            array_push($records,$days);
        }
        $is_coloumn = true;
        if(!empty($records)) {
          foreach($records as $record) {
            if($is_coloumn) {		  	  
              fputcsv($fh, array_keys($record));
              $is_coloumn = false;
            }		
            fputcsv($fh, array_values($record));
          }
           fclose($fh);
        }
        exit;  
 }
    public function test(){
             $staffs = $this->empModel->fetchAllStaff(10,0,'ihris_pid',0);
             print_r($staffs);
        }
    public function timesheet(){  
      $month=$this->input->post('month');
      $year=$this->input->post('year');
      if(!empty($month)){
        $_SESSION['month']=$month;
        $_SESSION['year']=$year;
        $date=$_SESSION['year'].'-'.$_SESSION['month'];
       }
         if (!empty($_SESSION['year'])){
        $date=$_SESSION['year'].'-'.$_SESSION['month'];
        $data['month']=$_SESSION['month'];
        $data['year']=$_SESSION['year'];
       }
       else{
        $_SESSION['month']=date('m');
        $_SESSION['year']=date('Y');
        $date=$_SESSION['year'].'-'.$_SESSION['month'];
        $data['month']=$_SESSION['month'];
        $data['year']=$_SESSION['year'];
       }
        $this->load->library('pagination');
        $config=array();
        $config['base_url']=base_url()."employees/timesheet/";
        $config['total_rows']=$this->empModel->countTimesheet($date,$this->filters);
        $config['per_page']=20; //records per page
        $config['uri_segment']=3; //segment in url
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
	    $page=($this->uri->segment(3))? $this->uri->segment(3):0; //default starting point for limits 
	    $data['links']=$this->pagination->create_links();
        $data['title']='Timesheet';
        $data['uptitle']='Timesheet Report';
        $data['view']='timesheet';
        $data['module']='employees';
        $employee=$this->input->post('empid');
        $job=$this->input->post('job');
        $data['workinghours']=$this->empModel->fetch_TimeSheet($date,$config['per_page'],$page,$employee,$this->filters,$job);
        echo Modules::run('templates/main',$data);
    }
    public function getdata(){
         //$data['duties'][0]
         $data['duties']=$this->empModel->fetch_TimeSheet();
         print_r($data['duties']);
    }
    public function employeeTimeLogs($ihris_pid=false,$print=false,$from=false,$to=false){

        
      $post=$this->input->post();
    if($post){


      $search_data=$this->input->post();
              
      $data['from']=$search_data['date_from'];
      $data['to']=$search_data['date_to'];
      
      }
      
      else{
          
      $data['from']= date('Y-m-').'01';
      $data['to']=date('Y-m-d');
      $search_data['date_from']= $data['from'];
      $search_data['date_to']= $data['to'];
          
      }
      
      $dbresult=$this->empModel->getEmployeeTimeLogs(urldecode($ihris_pid),10000,0,$search_data);
      $data['timelogs']=$dbresult['timelogs'];
      $data['employee']=$dbresult['employee'];
      $data['leaves']=$dbresult['leaves'];
      $data['offs']=$dbresult['offs'];
      $data['requests']=$dbresult['requests'];
      $data['workdays']=$dbresult['dutydays'];
  
      $data['title']="Health  Staff Individual Time Logs";
      //$data['facilities']=Modules::run("facilities/getFacilities");
      $data['view']='individual_time_logs';
  
      $data['module']="employees";
      echo Modules::run("templates/main",$data);
     
      }
  
    
        public function printindividualTimeLogs($ihris_pid,$from=false,$to=false,$flag){
          if($from){
            // $from= str_replace('-','/',$from);
            $from=date("Y-m-d", strtotime($from));
            // $to= str_replace('-','/',$to);
            $to=date("Y-m-d", strtotime($to));
            $search_data2['date_from']=$from;
            $search_data2['date_to']=$to;
            //print_r($search_data2);
            }
            else{
            $search_data2['from']=date('Y-m-').'01';
            $search_data2['to']=date('Y-m-d');
            } 
            $this->load->library('M_pdf');
            $filename="individual_timelogs_report_"."pdf";
            ini_set('max_execution_time',0);
            $dbresult=$this->empModel->getEmployeeTimeLogs(urldecode($ihris_pid),100000,0,$search_data=NULL,$search_data2);
            $data['timelogs']=$dbresult['timelogs'];
            $data['employee']=$dbresult['employee'];
            $data['leaves']=$dbresult['leaves'];
            $data['offs']=$dbresult['offs'];
            $data['workdays']=$dbresult['dutydays'];
            $data['requests']=$dbresult['requests'];
            $data['to']=$search_data2['to'];
            $data['from']=$search_data2['from'];
            $data['links']=$this->pagination->create_links();
            $data['title']="Health  Staff Individual Time Logs";
            if($flag==1){
                $view='print_individual_time_logs';
            }
            else{
                $view='printdetailslog';
            }
            $html=$this->load->view($view,$data,true);   
            $PDFContent = mb_convert_encoding($html, 'UTF-8', 'UTF-8');
            $this->m_pdf->pdf->SetWatermarkImage($this->watermark);
            $this->m_pdf->pdf->showWatermarkImage = true;
            date_default_timezone_set("Africa/Kampala"); 
            $this->m_pdf->pdf->SetHTMLFooter("Printed/ Accessed on: <b>".date('d F,Y h:i A')."</b><br style='font-size: 9px !imporntant;'>"." Source: iHRIS - HRM Attend " .base_url());
            $this->m_pdf->pdf->SetWatermarkImage($this->watermark);
            $this->m_pdf->showWatermarkImage = true;
            ini_set('max_execution_time',0);
            $this->m_pdf->pdf->WriteHTML($PDFContent); //ml_pdf because we loaded the library ml_pdf for landscape format not m_pdf
            //download it D save F.
            $this->m_pdf->pdf->Output($filename,'I');
            }
}
