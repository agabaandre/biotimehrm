
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Employees extends MX_Controller{
	
	public  function __construct(){
		parent:: __construct();
        $this->load->model('employee_model','empModel');
        $this->user=$this->session->get_userdata();
        $this->load->library('pagination');
        $this->watermark=FCPATH."assets/img/448px-Coat_of_arms_of_Uganda.svg.png";
        $this->filters=Modules::run('filters/sessionfilters');
     
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

    public function machineCsv($from,$to,$ihris_pid){
		$this->load->library('excel');

	    $this->excel->setActiveSheetIndex(0);

	    //name the worksheet

	    $this->excel->getActiveSheet()->setTitle('MachineCsv');
		//set cell A1 content with some text

		$this->excel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
		$this->excel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
		$this->excel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
		$this->excel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
		$this->excel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
		$this->excel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);


		//bold header
		$this->excel->getActiveSheet()->getStyle("A1:H1")->applyFromArray(array("font" => array("bold" => true)));

		$this->excel->getActiveSheet()->setCellValue('A1', 'NAME');

		$this->excel->getActiveSheet()->setCellValue('B1', 'FACILITY');
        $this->excel->getActiveSheet()->setCellValue('C1', 'FACILITY');

		$this->excel->getActiveSheet()->setCellValue('D1', 'TIME IN');

		$this->excel->getActiveSheet()->setCellValue('E1', 'TIME OUT');

		$this->excel->getActiveSheet()->setCellValue('F1', 'HOURS WORKED');

		$this->excel->getActiveSheet()->setCellValue('H1', 'DATE');

		$rs = $this->empModel->getMachineCsvData($from,$to,$ihris_pid);
		                
		                
		if(count($rs)<1){
	        //no data from db
	        echo "<h1 style='margin-top:20%; color:red;'><center> NO DATA IN THIS RANGE</center></h1>";
		                
		}
			                
		else{ //we have some excel data from db

			$exceldata="";

	        foreach ($rs as $row){

	            $exceldata[] = $row;

	        }


			//print_r( $rs);
			                //Fill data

			$this->excel->getActiveSheet()->fromArray($exceldata, null,"A2");
			 

		    $filename='Biometric_report_data.csv'; //save our workbook as this file name

		    header('Content-Type: text/csv'); //mime type
		    header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name

		    header('Cache-Control: max-age=0'); //no cache

		    //if you want to save it as .XLSX Excel 2007 format

		    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'CSV'); 

		    //force user to download the Excel file without writing it to server's HD

		    $objWriter->save('php://output');
			                
		}
	    
	}

    public function viewTimeLogs(){
 
	    $search_data=$this->input->post();
	    
        $config=array();
        $config['base_url']=base_url()."employees/viewTimeLogs";
        $config['total_rows']=$this->empModel->count_timelogs($search_data,$this->filters);
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
        $this->m_pdf->pdf->SetHTMLFooter("Printed/ Accessed on: <b>".date('d F,Y h:i A')."</b>");

        $this->m_pdf->pdf->SetWatermarkImage($this->watermark);
        $this->m_pdf->showWatermarkImage = true;
         
        ini_set('max_execution_time',0);
        $this->m_pdf->pdf->WriteHTML($PDFContent); //ml_pdf because we loaded the library ml_pdf for landscape format not m_pdf
         
        //download it D save F.
        $this->m_pdf->pdf->Output($filename,'I');
    }

    Public function print_timelogs(){  
        
        $this->load->library('M_pdf');

        $data=$this->empModel->get_printableTimeLogs();
                
        $html=$this->load->view('print_time_logs',$data,true);

        //$fac=$_SESSION['facility'];

        $filename="timelogs_report_"."pdf";


        ini_set('max_execution_time',0);
        $PDFContent = mb_convert_encoding($html, 'UTF-8', 'UTF-8');

        $this->m_pdf->pdf->SetWatermarkImage($this->watermark);
        $this->m_pdf->pdf->showWatermarkImage = true;

        date_default_timezone_set("Africa/Kampala");
        $this->m_pdf->pdf->SetHTMLFooter("Printed/ Accessed on: <b>".date('d F,Y h:i A')."</b>");

        $this->m_pdf->pdf->SetWatermarkImage($this->watermark);
        $this->m_pdf->showWatermarkImage = true;
         
        ini_set('max_execution_time',0);
        $this->m_pdf->pdf->WriteHTML($PDFContent); //ml_pdf because we loaded the library ml_pdf for landscape format not m_pdf
         
        //download it D save F.
        $this->m_pdf->pdf->Output($filename,'I');
    }
    
    

        public function test(){

             $staffs = $this->empModel->fetchAllStaff(10,0,'ihris_pid',0);

             print_r($staffs);
        }




    public function timesheet(){  

        $month=$this->input->post('month');
        $year=$this->input->post('year');
       // $department=$this->input->post('department');

        //for a dynamic one

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

        $date=$data['year']."-".$data['month'];


        $this->load->library('pagination');
        $config=array();
        $config['base_url']=base_url()."employees/timesheet";
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
        $data['uptitle']='Monthly Timesheet Report';
        $data['view']='timesheet';
        $data['module']='employees';
        $employee=$this->input->post('empid');
       
        $data['workinghours']=$this->empModel->fetch_TimeSheet($date,$config['per_page'],$page,$employee,$this->filters);
        echo Modules::run('templates/main',$data);

       // print_r($data);
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
            
        $data['from']='10/01/2019';
        $data['to']=date('m/d/Y');
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
            $from=date("m/d/Y", strtotime($from));
            // $to= str_replace('-','/',$to);
            $to=date("m/d/Y", strtotime($to));
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
            $this->m_pdf->pdf->SetHTMLFooter("Printed/ Accessed on: <b>".date('d F,Y h:i A')."</b>");
    
            $this->m_pdf->pdf->SetWatermarkImage($this->watermark);
            $this->m_pdf->showWatermarkImage = true;
             
            ini_set('max_execution_time',0);
            $this->m_pdf->pdf->WriteHTML($PDFContent); //ml_pdf because we loaded the library ml_pdf for landscape format not m_pdf
             
            //download it D save F.
            $this->m_pdf->pdf->Output($filename,'I');
           
            }
    
        





}

