<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rosta extends MX_Controller {

	function __construct(){

        // Call the Model constructor
        parent::__construct();

        $this->load->model('rosta_model');
        $this->rostamodule="rosta";
        $this->departments=Modules::run("departments/getDepartments");
        $this->watermark=FCPATH."assets/images/watermark.png";
		$this->filters=Modules::run('filters/sessionfilters');
    }

	public function attendance_calenderFormat()
	{
		$data['module']=$this->rostamodule;
		$data['view']="attendance_inCalender";

		echo Modules::run('templates/main',$data);
	}

	Public function index(){
		$data['module']=$this->rostamodule;
		$data['view']="rosta";
		echo Modules::run('templates/main',$data);
	}
    
    function getChecks(){

    	$checks=$this->checks=$this->rosta_model->checks();

    	return $checks;

	}
	function getleaveChecks(){

    	$checks=$this->checks=$this->rosta_model->leavechecks();

    	return $checks;
    }


	  
	 public function leaveRoster(){	
		$month=$this->input->post('month');
		$year=$this->input->post('year');
		$employee=$this->input->post('empid');

		if($this->uri->segment(3) && !$this->input->post()){

			$data['month']=$_SESSION['month'];
			$data['year']=$_SESSION['year'];

		}

		else{

		if($month!=""){

			$data['month']=$month;

			$data['year']=$year;

			$_SESSION['month']=$month;
			$_SESSION['year']=$year;

		}

		else{

			$data['month']=date('m');

			$data['year']=date('Y');

			$_SESSION['month']=date('m');
			$_SESSION['year']=date('Y');

		}

	   }
		$this->load->library('pagination');
		$config=array();
	    $config['base_url']=base_url()."rosta/leaveRoster";
	    $config['total_rows']=Modules::run('employees/count_Staff');
	    $config['per_page']=15; //records per page
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
        $config['use_page_numbers'] = true;
	    $this->pagination->initialize($config);
	    $page=($this->uri->segment(3))? $this->uri->segment(3):0; //default starting point for limits 
	    $data['links']=$this->pagination->create_links();
		$date=date('Y-m');
		$data['schedules']=Modules::run("schedules/getleaveSchedules","r");
		//print_r($data['schedules']);
		$data['checks']=$this->getleaveChecks();
		//print_r($data['checks']);
		$data['departments']=$this->departments;
        
		$data['duties']=$this->rosta_model->fetchleave_tabs($date,$config['per_page'],$page);
		//print_r($data['duties']);

		//echo "My dep".$this->sdepartment;
		$data['matches']=$this->rosta_model->leavematches();
		//print_r($data['matches']);

		$data['tab_schedules']=$this->rosta_model->leavetab_matches();
		//rint_r($data['tab_schedules']);
		
		$data['facilities']=Modules::run("facilities/get_facility");
		
		//$data['switches']=$this->switches();

		$data['view']='leaverota';
		$data['module']=$this->rostamodule;
		
		echo Modules::run("templates/main",$data);
	}

	 public function tabular(){	

		$month=$this->input->post('month');
		$year=$this->input->post('year');
		$employee=$this->input->post('empid');


		
		if($month!=""){

			$data['month']=$month;

			$data['year']=$year;

			$_SESSION['month']=$month;
			$_SESSION['year']=$year;

		}

		else{

			$data['month']=date('m');

			$data['year']=date('Y');

			$_SESSION['month']=date('m');
			$_SESSION['year']=date('Y');

		}

	
		$this->load->library('pagination');
		$config=array();
	    $config['base_url']=base_url()."rosta/tabular";
	    $config['total_rows']=Modules::run('employees/count_Staff');
	    $config['per_page']=100; //records per page
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
        $config['use_page_numbers'] = true;
	    $this->pagination->initialize($config);
	    $page=($this->uri->segment(3))? $this->uri->segment(3):0; //default starting point for limits
	    $data['links']=$this->pagination->create_links();
		$date=date('Y-m');
	    $data['schedules']=Modules::run("schedules/getSchedules","r");
		//$data['checks']=$this->getChecks();
		//$data['departments']=$this->departments;
		$data['duties']=$this->rosta_model->fetch_tabs($date,$config['per_page'],$page,$employee,$this->filters);
		$data['matches']=$this->rosta_model->matches();
		$data['tab_schedules']=$this->rosta_model->tab_matches();
		$data['view']='duty_roster';
		$data['module']=$this->rostamodule;
		$data['uptitle']="Duty Roster";
		$data['title']="Duty Roster";

		//print_r($data);
		
		echo Modules::run("templates/main",$data);
	}






	Public function fetch_report(){

		$month=$this->input->post('month');
		$year=$this->input->post('year');
		$department=$this->input->post('department');


		if($month!=""){

			$data['month']=$month;

			$data['year']=$year;
		}

		else{

			$data['month']=date('m');

			$data['year']=date('Y');

		}

		if($department!=''){

			$data['depart']=$department;

		}

		else{

			$data['depart']='';
		}

		$date=date('Y')."-".date('m');

		
		$this->load->library('pagination');
		$config=array();
	    $config['base_url']=base_url()."rosta/fetch_report";
	    $config['total_rows']=Modules::run('employees/count_Staff');
	    $config['per_page']=15; //records per page
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
        $config['use_page_numbers'] = true;
        
	    $this->pagination->initialize($config);
	    
	    $page=($this->uri->segment(3))? $this->uri->segment(3):0; //default starting point for limits
	    
	    
	    $data['links']=$this->pagination->create_links();

		//$data['username']=$this->username;
		$data['departments']=$this->departments;
       
		$data['duties']=$this->rosta_model->fetch_report($date,$config['per_page'],$page);

		$data['matches']=$this->rosta_model->matches();
		$data['checks']=$this->getChecks();
		
		$data['facilities']=Modules::run("facilities/getFacilities");

		//$data['switches']=$this->switches();
		$data['module']=$this->rostamodule;
		$data['view']="workschedules_report";
		
		echo Modules::run('templates/main',$data);
	}



	Public function print_report($date){	

	    $data['dates']=$date;
	    
		$this->load->library('ML_pdf');

		$data['username']=$this->username;
		$data['checks']=$this->checks;

		$data['duties']=$this->rosta_model->fetch_report($date,10000,0);

		$data['matches']=$this->rosta_model->matches();
		
		$html=$this->load->view('rosta/printable',$data,true);

		$fac=$data['duties'][0]['facility'];
		$date=date('F-Y',strtotime($data['duties'][0]['day1']));

		$filename=$fac."_rota_report_".$date.".pdf";


		ini_set('max_execution_time',0);
		$PDFContent = mb_convert_encoding($html, 'UTF-8', 'UTF-8');

		$this->ml_pdf->pdf->SetWatermarkImage($this->watermark);
		$this->ml_pdf->pdf->showWatermarkImage = true;

		date_default_timezone_set("Africa/Kampala");
		$this->ml_pdf->pdf->SetHTMLFooter("Printed / Accessed on: <b>".date('d F,Y h:i A')."</b>");

		$this->ml_pdf->pdf->SetWatermarkImage($this->watermark);
		$this->ml_pdf->showWatermarkImage = true;
		 

		ini_set('max_execution_time',0);

		$this->ml_pdf->pdf->WriteHTML($PDFContent); //ml_pdf because we loaded the library ml_pdf for landscape format not m_pdf
		 
		//download it D save F.
		$this->ml_pdf->pdf->Output($filename,'I');
	}
	
	

	Public function summary(){	
	    
		$month=$this->input->post('month');
		$year=$this->input->post('year');
		$department=$this->input->post('department');

		//for a dynamic one
		if($month!="")
		{

			$data['month']=$month;

			$data['year']=$year;
		}

		else{

			$data['month']=date('m');

			$data['year']=date('Y');
		}

		if($department){

			$data['depart']=$department;
		}

		$date=$data['year']."-".$data['month'];
			    
		$data['departments']=$this->departments;
		//$data['username']=$this->username;
		$data['dates']=$this->$date;
		$this->load->library('pagination');
		$config=array();
	    $config['base_url']=base_url()."rosta/summary";
	    $config['total_rows']=$this->rosta_model->countrosta_summary($date);
	    $config['per_page']=10; //records per page
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
        $config['use_page_numbers'] = true;
        
	    $this->pagination->initialize($config);
	    
	    $page=($this->uri->segment(3))? $this->uri->segment(3):0; //default starting point for limits
	    $data['links']=$this->pagination->create_links();

		   //$data['facilities']=$this->attendance_model->get_facility();
		$data['facilities']=Modules::run("facilities/getFacilities");
		$data['sums']=$this->rosta_model->getRealSummary($date,$config['per_page'],$page);
		$data['view']='summary_report';
		$data['module']=$this->rostamodule;
		//print_r($data);
		echo Modules::run("templates/main",$data);
		//$this->load->view('summary_report',$data);
	}
	


   


	public function bundleCsv($valid_range){
		    
		$sums=$this->rosta_model->getPrintableRealSummary($valid_range);

		//$fp = fopen(FCPATH.'uploads/summary.csv', 'w');

		$fp = fopen('php://memory', 'w'); 

		//add heading to data
		$heading=array('person' =>"Names" ,'D'=>"Day Duty", 'O' =>"Off Duty", 'A' => "Annual Leave", 'S' => "Study Leave", 'M' => "Maternity Leave", 'Z' =>"Other Leave");

		array_unshift($sums,$heading);

		//print_r($sums);

		foreach ($sums as $sumrow) {
		    
		   fputcsv($fp, $sumrow);
		    
		}


		$filename=$valid_range."_summary_report.csv";

		// reset the file pointer to the start of the file
	    fseek($fp, 0);
	    // tell the browser it's going to be a csv file
	    header('Content-Type: application/csv');
	    // tell the browser we want to save it instead of displaying it
	    header('Content-Disposition: attachment; filename="'.$filename.'";');
	    // make php send the generated csv lines to the browser
	    fpassthru($fp);

		fclose($fp);

	}


	Public function presence(){	

		$month=$this->input->post('month');
		$year=$this->input->post('year');
		$department=$this->input->post('department');


		//for a dynamic one

		if($month!=""){

			$data['month']=$month;

			$data['year']=$year;
			$data['department']=$department;

		}

		else{

			$data['month']=date('m');

			$data['year']=date('Y');

		}

		$date=date('Y')."-".date('m');

		$data['departments']=$this->departments;
		
		//$data['facilities']=$this->attendance_model->get_facility();
		$data['facilities']=Modules::run('attendance/get_facility');

		$data['duties']=$this->rosta_model->fetch_report($date);

		$nonworkables=$this->rosta_model->nonworkables();

		$workeddays=$this->rosta_model->workeddays();

		$data['nonworkables']=$nonworkables;
		$data['workeddays']=$workeddays;

		$data['matches']=$this->rosta_model->matches();
		$data['checks']=$this->checks;
		//$data['switches']=$this->switches();
		
		$this->load->view('presence_report',$data);
	}
	
			
	Public function tracker(){

		$month=$this->input->post('month');
		$year=$this->input->post('year');
		$department=$this->input->post('department');


		//for a dynamic one

		if($month!="")
		{

			$data['month']=$month;

			$data['year']=$year;
			$data['department']=$department;

		}

		else{

			$data['month']=date('m');

			$data['year']=date('Y');

		}

		$date=date('Y')."-".date('m');

		$data['departments']=$this->departments;

		$data['duties']=$this->rosta_model->fetch_report($date);

		$nonworkables=$this->rosta_model->nonworkables();
		
		$data['facilities']=Modules::run('attendance/get_facility');	
		//$data['facilities']=$this->attendance_model->get_facility();

		$workeddays=$this->rosta_model->workeddays();

		$data['nonworkables']=$nonworkables;
		$data['workeddays']=$workeddays;

		$data['matches']=$this->rosta_model->matches();
		$data['checks']=$this->checks;
		//$data['switches']=$this->switches();
		
		$this->load->view('presence_fm',$data);

	}


	
	
	//presense tracking

	public function saveTracker(){

		$pid=$_POST['hpid'];
		$date=$_POST['date'];

		$data= array('ihris_pid' => $pid,'day'=>$date );

		//print_r($data);

		$result=$this->rosta_model->saveTracker($data);
		echo $result;
	}
		
	
	
	Public function print_presence($date){	

        $data['dates']=$date;
        
		$this->load->library('ML_pdf');

		$data['checks']=$this->checks;
	
		$nonworkables=$this->rosta_model->nonworkables();

		$workeddays=$this->rosta_model->workeddays();

		$data['nonworkables']=$nonworkables;
		$data['workeddays']=$workeddays;

		$data['duties']=$this->rosta_model->fetch_report($date);

		$data['matches']=$this->rosta_model->matches();
		
		$html=$this->load->view('printabletracker',$data,true);

		$fac=$data['duties'][0]['facility'];
		$date=date('F-Y',strtotime($data['duties'][0]['day1']));

		$filename=$fac."_tracking_report_".$date.".pdf";


		ini_set('max_execution_time',0);
		$PDFContent = mb_convert_encoding($html, 'UTF-8', 'UTF-8');

		$this->ml_pdf->pdf->SetWatermarkImage($this->watermark);
		$this->ml_pdf->pdf->showWatermarkImage = true;

		date_default_timezone_set("Africa/Kampala");
		$this->ml_pdf->pdf->SetHTMLFooter("Printed / Accessed on: <b>".date('d F,Y h:i A')."</b>");

		$this->ml_pdf->pdf->SetWatermarkImage($this->watermark);
		$this->ml_pdf->showWatermarkImage = true;
		 

		 ini_set('max_execution_time',0);
		$this->ml_pdf->pdf->WriteHTML($PDFContent); //ml_pdf because we loaded the library ml_pdf for landscape format not m_pdf
		 
		//download it D save F.
		$this->ml_pdf->pdf->Output($filename,'I');
	}



	Public function actuals(){	

		$month=$this->input->post('month');
		$year=$this->input->post('year');
		$data['uptitle']="Daily Attendance";
		$data['title']="Daily Attendance";


		//for a dynamic one

		

		if($month!=""){

			$data['month']=$month;

			$data['year']=$year;

			$_SESSION['month']=$month;
			$_SESSION['year']=$year;

		}

		else{

			$data['month']=date('m');

			$data['year']=date('Y');

			$_SESSION['month']=date('m');
			$_SESSION['year']=date('Y');

		}

	   

		$date=$data['year']."-".$data['month'];


		$this->load->library('pagination');
		$config=array();
	    $config['base_url']=base_url()."rosta/actuals";
	    $config['total_rows']=$this->rosta_model->countActuals($date);
	    $config['per_page']=15; //records per page
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
        $config['use_page_numbers'] = true;
        
	    $this->pagination->initialize($config);
	    
	    $page=($this->uri->segment(3))? $this->uri->segment(3):0; //default starting point for limits
	    $data['links']=$this->pagination->create_links();

		$empid=$this->input->post('empid');

		$data['duties']=$this->rosta_model->fetch_report($date,$config['per_page'],$page,$empid,$this->filters);

		$nonworkables=$this->rosta_model->nonworkables();
		$data['facilities']=Modules::run('facilities/getFacilities');	
		//$data['facilities']=$this->attendance_model->get_facility();

		$actualrows=$this->rosta_model->getActuals($date);
		
		$actuals=array();
		
		foreach($actualrows as $actual){
		    
		    $entry=$actual['entry_id'];
		    $duty=$actual['actual']; 
		    $actuals[$entry]=$duty;
		}
		
		$data['actuals']=$actuals;
		//	$data['switches']=$this->switches();
		$data['view']='actuals';
		
		$data['module']=$this->rostamodule;
		//print_r($actualrows);
		echo Modules::run('templates/main',$data);
	}
	
	
	//presense tracking

	function getschedules(){
		
		$this->load->model('schedules/schedules_mdl');
		$actualletters=$this->schedules_mdl->getattSchedules2();
		
		return $actualletters;
	}

	public function saveActual(){

		$pid=$_POST['hpid'];
		$dateFrom=$_POST['date'];
		$dateTo=$_POST['date'];
		$actual=$_POST['duty'];
		$color=$_POST['color'];

		try{
		$facility=$this->session->userdata['facility'];
		$department=$this->session->userdata['department_id'];
		
		$rowid=$_POST['date'].$_POST['hpid'];

        $entry=str_replace(' ','',$rowid);
        //'ihris_pid' => $pid
        
        $actualletters=$this->getschedules();
        
        $duty=$actualletters[$actual];

		$data= array(
			'entry_id'=>$entry,
			'date'=>$dateFrom,
			'end'=>$dateTo,
			'schedule_id'=>$duty,
			'department_id'=>$department,
			'color'=>$color,
			'ihris_pid'=>$pid,
			'facility_id'=>$facility,'
			allDay'=>'true'
		);
		//print_r($data);
	 	$result=$this->rosta_model->saveActual($data);
		//echo $result;
		} catch(Exception $error){

			echo $error->getMessage();

		}
		

		echo  $duty;
	}
	
	
	
	public function updateActual(){

		$pid=$_POST['hpid'];
		$dateFrom=$_POST['date'];
		$dateTo=$_POST['date'];
		$actual=$_POST['duty'];
		$color=$_POST['color'];
		
		$facility=$this->session->userdata['facility'];
		$department=$this->session->userdata['department_id'];
		
		$rowid=$_POST['date'].$_POST['hpid'];

        $entry=str_replace(' ','',$rowid);
        
        $actualletters=$this->rosta_model->attendanceSchedules();
        
        $duty=$actualletters[$actual];

	
		$data= array('entry_id'=>$entry,'date'=>$dateFrom,'end'=>$dateTo,'schedule_id'=>$duty,'department_id'=>$department,'color'=>$color,'ihris_pid'=>$pid,'facility_id'=>$facility);
	//print_r($data);

		$result=$this->rosta_model->updateActual($data);
		//echo $result;

		echo  $duty;
	}
	
		
	Public function actualsreport(){	

		$month=$this->input->post('month');
		$year=$this->input->post('year');
		$department=$this->input->post('department');

		//for a dynamic one

		if($month!="")
		{

			$data['month']=$month;

			$data['year']=$year;

		}

		else{

			$data['month']=date('m');

			$data['year']=date('Y');
		}

		if($department!==''){

			$data['depart']=$department;
		}

		$date=date('Y')."-".date('m');
        $this->load->library('pagination');
		$config=array();
	    $config['base_url']=base_url()."rosta/actualsreport";
	    $config['total_rows']=$this->rosta_model->countActuals($date);
	    $config['per_page']=15; //records per page
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
        $config['use_page_numbers'] = true;
        
	    $this->pagination->initialize($config);
	    
	    $page=($this->uri->segment(3))? $this->uri->segment(3):0; //default starting point for limits
	    $data['links']=$this->pagination->create_links();

		
		$data['departments']=$this->departments; 

		$data['facilities']=Modules::run("facilities/getFacilities");

		$data['duties']=$this->rosta_model->fetch_report($date,$config['per_page'],$page);
		$actualrows=$this->rosta_model->getActuals();
		$actuals=array();
		

		foreach($actualrows as $actual){
		    
		    $entry=$actual['entry_id'];
		    $duty=$actual['actual'];

		    $actuals[$entry]=$duty;
		}

		
		$data['actuals']=$actuals;

		$data['matches']=$this->rosta_model->matches();
		$data['checks']=$this->getChecks();
		//$data['switches']=$this->switches();
		$data['view']="actuals_report";
		$data['module']=$this->rostamodule;
		echo Modules::run('templates/main',$data);
	}
	

	Public function print_actuals($date){	

	    $data['dates']=$date;
	    
		$this->load->library('ML_pdf');


		$data['duties']=$this->rosta_model->fetch_report($date,10000,0);
		
		$actualrows=$this->rosta_model->getActuals();
		$actuals=array();
		
		foreach($actualrows as $actual){
		    
		    $entry=$actual['entry_id'];
		    $duty=$actual['actual'];
		     
		    $actuals[$entry]=$duty;
		}
		
		$data['actuals']=$actuals;
		
		$html=$this->load->view('actual_printable',$data,true);

		$fac=$data['duties'][0]['facility'];
		$date=date('F-Y',strtotime($data['duties'][0]['day1']));

		$filename=$fac."_actuals_report_".$date.".pdf";


		ini_set('max_execution_time',0);
		$PDFContent = mb_convert_encoding($html, 'UTF-8', 'UTF-8');

		$this->ml_pdf->pdf->SetWatermarkImage($this->watermark);
		$this->ml_pdf->pdf->showWatermarkImage = true;

		date_default_timezone_set("Africa/Kampala");
		$this->ml_pdf->pdf->SetHTMLFooter("Printed / Accessed on: <b>".date('d F,Y h:i A')."</b>");

		$this->ml_pdf->pdf->SetWatermarkImage($this->watermark);
		$this->ml_pdf->showWatermarkImage = true;


		ini_set('max_execution_time',0);
		$this->ml_pdf->pdf->WriteHTML($PDFContent); //ml_pdf because we loaded the library ml_pdf for landscape format not m_pdf
		 
		//download it D save F.
		$this->ml_pdf->pdf->Output($filename,'I');
	}	

		
	Public function print_summary($date){	

        $data['dates']=$date;
        
		$this->load->library('ML_pdf');

		$data['sums']=$this->rosta_model->fetch_summary($date);
				
		$html=$this->load->view('printablesummary',$data,true);

		$fac=$_SESSION['facility'];

		$filename=$fac."_summary_report_".$date.".pdf";


		ini_set('max_execution_time',0);
		$PDFContent = mb_convert_encoding($html, 'UTF-8', 'UTF-8');

		$this->ml_pdf->pdf->SetWatermarkImage($this->watermark);
		$this->ml_pdf->pdf->showWatermarkImage = true;

		date_default_timezone_set("Africa/Kampala");
		$this->ml_pdf->pdf->SetHTMLFooter("Printed/ Accessed on: <b>".date('d F,Y h:i A')."</b>");

		$this->ml_pdf->pdf->SetWatermarkImage($this->watermark);
		$this->ml_pdf->showWatermarkImage = true;
		 
		ini_set('max_execution_time',0);
		$this->ml_pdf->pdf->WriteHTML($PDFContent); //ml_pdf because we loaded the library ml_pdf for landscape format not m_pdf
		 
		//download it D save F.
		$this->ml_pdf->pdf->Output($filename,'I');
	}
	
	

	
	Public function addEvent()
	{


		$result=$this->rosta_model->addEvent();
		
		echo $result;
	}


public function excel_template()
 {
					$this->load->library('excel');

	                $this->excel->setActiveSheetIndex(0);

	                //name the worksheet

	                $this->excel->getActiveSheet()->setTitle('Rota_template');

	                //set cell A1 content with some text



$this->excel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$this->excel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$this->excel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$this->excel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);




$this->excel->getActiveSheet()->getStyle("A1:D1")->applyFromArray(array("font" => array("bold" => true)));


	              

	                $this->excel->getActiveSheet()->setCellValue('A1', 'Person ID');

	                $this->excel->getActiveSheet()->setCellValue('B1', 'Names');

	                //$this->excel->getActiveSheet()->setCellValue('C1', 'Duty Date');

	               // $this->excel->getActiveSheet()->setCellValue('D1', 'Duty');

	       
	                //retrive contries table data

	                $rs = $this->rosta_model->template_data();
	                
	                

	                $exceldata="";
	                
	                $month_days=date('t');

	        foreach ($rs as $row){

for($y=0;$y<$month_days;$y++){  //repeat each person for the no. of days in a month
    
	                $exceldata[] = $row;
}
	                

	        }

	        		//print_r($exceldata);
	                //Fill data


	 $this->excel->getActiveSheet()->fromArray($exceldata, null,"A2");
	 


	                $filename='rosta_template.xls'; //save our workbook as this file name

	                header('Content-Type: application/vnd.ms-excel'); //mime type
	                header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name

	                header('Cache-Control: max-age=0'); //no cache

	  

	                //save it to Excel5 format (excel 2003 .XLS file), change this to 'Excel2007' (and adjust the filename extension, also the header mime type)

	                //if you want to save it as .XLSX Excel 2007 format

	                $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5'); 

	                //force user to download the Excel file without writing it to server's HD

	                $objWriter->save('php://output');

	                  

	    }



public function getAllEmployees(){

	$this->db->select('ihris_pid,department_id,facility_id');
	$qry=$this->db->get('ihrisdata');

	$employees=$qry->result();
	return $employees;
}
public function isWeekend($date) {
 $day=intval(date('N', strtotime($date)));
 return ($day>= 6);
}

public function autoFillRosta(){

date_default_timezone_set('Africa/Kampala');
ignore_user_abort(true);
ini_set('max_execution_time',0);

$employees=$this->getAllEmployees();

$year=date('Y');

foreach($employees as $employee){


for($m=1;$m<=12;$m++){
    
$month_days = cal_days_in_month(CAL_GREGORIAN, $m,$year); 

for($d=1;$d<=$month_days;$d++){//

$dayDate = $year."-".$m."-".$d;
$hris_pid=$employee->ihris_pid;
$facility_id=$employee->facility_id;
$entry_id=$dayDate.$hris_pid;
$department_id=$employee->department_id;
$duty_date=$dayDate;
$tommorodate=date_create($dayDate);
date_add($tommorodate,date_interval_create_from_date_string("1 days"));
$end=date_format($tommorodate,'Y-m-d');

if ($this->isWeekend($dayDate)){
    
    $duty="17";
    $color='#d1a110';
}
else{

	$duty="14";
	$color="#297bb2";
}

$data = array(
	'entry_id' =>$entry_id ,
	'facility_id'=>$facility_id,
	'department_id'=>$department_id,
	'ihris_pid'=>$hris_pid,
	'schedule_id'=>$duty,
	'color'=>$color,
	'duty_date'=>$duty_date,
	'end'=>$end,
	'allDay'=>'true'

 );

	$this->db->insert('duty_rosta',$data);
/*$data ="\n<h1>Person". $p."{$dayDate}</h1>";
$data .="\n===================================================";

file_put_contents('log.txt',$data,FILE_APPEND);*/

}//


}

}//3000
}


}

