<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Attendance extends MX_Controller {
	
	
	public  function __construct(){
		
		
		parent:: __construct();
        
		$this->load->model('attendance_model');
		$this->departments=Modules::run("departments/getDepartments");
		$this->attendModule="attendance";
		$this->watermark=FCPATH."assets/img/MOH.png";
		  //requires a join on ihrisdata
		$this->filters=Modules::run('filters/sessionfilters');
		//doesnt require a join on ihrisdata
		$this->ufilters=Modules::run('filters/universalfilters');
		// requires a join on ihrisdata with district level
		$this->distfilters=Modules::run('filters/districtfilters');
		$this->load->library('pagination');
		
	}
	public function attrosta($date_range,$person){
		$per=urldecode($person);
		$data=$this->attendance_model->attrosta($date_range,$per);
		
	return $data;
	}
			
   public function getWidgetData(){

		$widgets=$this->attendance_model->widget_data();

		return $widgets;
	}
	

	Public function attendance_summary(){	
	 	    
		$month=$this->input->post('month');
		$year=$this->input->post('year');
		$empid=$this->input->post('empid');
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
		
        
		$config=array();
	    $config['base_url']=base_url()."attendance/attendance_summary";
	    $config['total_rows']=$this->attendance_model->countAttendanceSummary($date,$this->filters);
	    $config['per_page']=30; //records per page
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
		$data['sums']=$this->attendance_model->attendance_summary($date,$this->filters,$config['per_page'],$page,$empid);
		$data['view']='attendance_summary';
		$data['title']='Attendance Form Summary';
		$data['uptitle']='Attendance Form Summary';
		$data['module']=$this->attendModule;
		echo Modules::run('templates/main',$data);

	}
	


	Public function print_attsummary($date)
	{	

        $data['dates']=$date;
        
		$this->load->library('ML_pdf');

		//$data['username']=$this->username;

		$data['sums']=$this->attendance_model->attendance_summary($date,$this->filters,$config['per_page']=FALSE,$page=FALSE,$empid=FALSE);



		$html=$this->load->view('att_summary',$data,true);

		// $fac=$_SESSION['facility'];
		// $filename=$fac."att_summary_report_".$date.".pdf";
 		// ini_set('max_execution_time',0);
 		// $PDFContent = mb_convert_encoding($html, 'UTF-8', 'UTF-8');
		//  $this->ml_pdf->pdf->SetWatermarkImage($this->watermark);
		//  $this->ml_pdf->pdf->showWatermarkImage = true;
 		// ini_set('max_execution_time',0);
		// $this->ml_pdf->pdf->WriteHTML($PDFContent); //ml_pdf because we loaded the library ml_pdf for landscape format not m_pdf
 
		// //download it D save F.
		// $this->ml_pdf->pdf->Output($filename,'I');


	}
	

	public function attsums_csv($valid_range)
	{
	$datas=$this->attendance_model->attendance_summary($valid_range,$this->filters,$config['per_page']=NULL,$page=NULL,$empid=FALSE);
    $csv_file = "Monthy_Attendance_Summary" . date('Y-m-d') .'_'.$_SESSION['facility'] .".csv";	
	header("Content-Type: text/csv");
	header("Content-Disposition: attachment; filename=\"$csv_file\"");	
	$fh = fopen( 'php://output', 'w' );
    $records=array();//output each row of the data, format line as csv and write to file pointer
     foreach($datas as $data){
		$roster=Modules::run('attendance/attrosta',$valid_range,urlencode($data['ihris_pid']));
		if(!empty($data['P'])){$present=$data['P']; } else{$present=0;}
		if(!empty($data['O'])){$off=$data['O']; } else{$off=0;};
		if(!empty($data['L'])){$leave=$data['L']; } else{$leave=0;};
		if(!empty($data['R'])){$request=$data['R']; } else{$request=0;};
		if(!empty($data['P'])){$present=$data['P']; } else{$present=0;};
		if(!empty($data['H'])){$holiday=$data['H']; } else{$holiday=0;};
        $eve=$roster['Evening'][0]->days;
		$day=$roster['Day'][0]->days;
		$night=$roster['Night'][0]->days;
		$scheduled=$eve+$night+$night;
		$absent=days_absent_helper($present,$r_days);
		$per= round(($present/($day+$night+$eve))*100,1); if(is_infinite($per)||is_nan($per)){ $per = 0; } else{  $per; }
        $days =array("Name"=>$data['fullname'],"Job"=>$data['job'],"Department"=>$data['department'], "Present"=>$present, "Off
		Duty"=>$off,
		"Official
		Request"=>$request, "Leave"=>$leave,"Holiday"=>$holiday,"Absent"=>'$absent', "Day Schedule"=>$day, "Evening Schedule"=>$eve,"Night Schedule"=>$night,"% Present"=>$per);
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


	//data importing
	
	public function import_csv(){
		 
		$postdata=$this->input->post(); 
				
		$day=$_SESSION['fetch_date'];
		$year=date("m_Y");

	    if(empty($day)){
	        
	        $day=20;
	    }
	
	
		$file_name="Biometric_Users_".$day."_".$year.".csv";

	
		$this->load->library('excel');
		
		$type = PHPExcel_IOFactory::identify('uploads/'.$file_name);
		
		$objReader=PHPExcel_IOFactory::createReader($type);     //For excel 2003 
		
          //Set to read only
        // $objReader->setReadDataOnly(true); 		  
        //Load excel file
		$objPHPExcel=$objReader->load(strip_tags(FCPATH.'uploads/'.$file_name));	
		
		 
        $totalrows=$objPHPExcel->setActiveSheetIndex(0)->getHighestRow();   //Count Numbe of rows avalable in excel      	 
        $objWorksheet=$objPHPExcel->setActiveSheetIndex(0);  
         
         //truncate table if data file contains data
        if($totalrows>0){
             
            $this->db->query("truncate ihrisdata");
             
        }
 	
		$rowsnow=0;
		 //loop from first data untill last data
        for($i=2;$i<=$totalrows;$i++){
              
			$person_id= $objWorksheet->getCellByColumnAndRow(0,$i)->getValue();			
            $district_id= $objWorksheet->getCellByColumnAndRow(1,$i)->getValue(); //Excel Column 1
			$district= $objWorksheet->getCellByColumnAndRow(2,$i)->getValue(); //Excel Column 2
			$nin=$objWorksheet->getCellByColumnAndRow(3,$i)->getValue(); //Excel Column 3 parent fname
			$ipps=$objWorksheet->getCellByColumnAndRow(4,$i)->getValue(); //Excel Column 4 
			$facility_type_id=$objWorksheet->getCellByColumnAndRow(5,$i)->getValue(); //Excel Column 5 
			$facility_id=$objWorksheet->getCellByColumnAndRow(6,$i)->getValue(); //Excel Column 6
			$facility=$objWorksheet->getCellByColumnAndRow(7,$i)->getValue(); //Excel Column 7 
			$department=$objWorksheet->getCellByColumnAndRow(8,$i)->getValue(); //Excel Column 8 
			$job_id=$objWorksheet->getCellByColumnAndRow(9,$i)->getValue(); //Excel Column 9 
			$job_title=$objWorksheet->getCellByColumnAndRow(10,$i)->getValue(); //Excel Column 11 
			$surname=$objWorksheet->getCellByColumnAndRow(11,$i)->getValue(); //Excel Column 12 
			$firstname=$objWorksheet->getCellByColumnAndRow(12,$i)->getValue(); //Excel Column 13 
			$othername=$objWorksheet->getCellByColumnAndRow(13,$i)->getValue(); //Excel Column 14 
			$mobile_phone=$objWorksheet->getCellByColumnAndRow(14,$i)->getValue(); //Excel Column 15 
			$telephone=$objWorksheet->getCellByColumnAndRow(15,$i)->getValue(); //Excel Column 16 
			  

			$excel_data=array('ihris_pid'=>$person_id,'district_id'=>$district, 'district'=>$district ,'nin'=>$nin ,'ipps'=>$ipps , 'facility_type_id'=>$facility_type_id
			 , 'facility_id'=>$facility_id,'facility'=>$facility,'department'=>$department, 'job_id'=>$job_id, 'job'=>$job_title, 'surname'=>$surname
			 , 'firstname'=>$firstname, 'othername'=>$othername, 'mobile'=>$mobile_phone, 'telephone'=>$telephone);
			 
			 
			 
			$this->attendance_model->read_employee_csv($excel_data);
			 
			$rowsnow+=1; 
			 //print_r($excel_data);
 
			   //echo $rowsnow;
		}
			//unlink('././uploads/'.$file_name); //File Deleted After uploading in database .			 
             //redirect(base_url() . "put link were you want to redirect");     
			// }	
			
			
		echo $rowsnow;	
	}


   public function upload_rosta(){

		$data['username']=$this->username;
		$data['checks']=$this->checks;
		$data['facilities']=$this->attendance_model->get_facility();
		$this->load->view('upload_rosta',$data);

	}


	public function machinedata(){

			$data['username']=$this->username;
			$data['checks']=$this->checks;
			$data['facilities']=$this->attendance_model->get_facility();
			$this->load->view('machine_upload',$data);

	}
		
	//generating a rota upload template for download

	public function excel_template(){

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

        $this->excel->getActiveSheet()->setCellValue('C1', 'Duty Date');

        $this->excel->getActiveSheet()->setCellValue('D1', 'Duty');


        //retrive contries table data

        $rs = $this->attendance_model->template_data();
        
        $exceldata="";
        
        $month_days=date('t');

			foreach ($rs as $row){

				for($y=0;$y<$month_days;$y++){  //repeat each person for the no. of days in a month

            		$exceldata[] = $row;
				}
			}

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



	public function upload_rota(){

		$facility=$this->facility;

		$config['upload_path']          = FCPATH.'uploads/';

	    $config['allowed_types']        = 'csv|xls|xlsx';
	    $config['max_size']             = 20000;
	    $config['file_name']            ="rota_file";

	    //$file_name=$config['file_name'];

	    $this->load->library('upload', $config);

	    $this->upload->initialize($config);

	    if ( ! $this->upload->do_upload('rota')){
            $error = array('error' => $this->upload->display_errors());

            echo $error['error'];
	    }
	    else{
	            
	        $file_name= $this->upload->data('file_name');  
  
			$this->load->library('excel');
			
			$type = PHPExcel_IOFactory::identify('uploads/'.$file_name);
			
			$objReader=PHPExcel_IOFactory::createReader($type);     //For excel 2003 
			
	          //Set to read only
	        // $objReader->setReadDataOnly(true); 		  
	        //Load excel file
			$objPHPExcel=$objReader->load(FCPATH.'uploads/'.$file_name);	
			 
	        $totalrows=$objPHPExcel->setActiveSheetIndex(0)->getHighestRow();   //Count Numbe of rows avalable in excel      	 
	        $objWorksheet=$objPHPExcel->setActiveSheetIndex(0);                
	        
			$people=$this->attendance_model->get_employees();

			$schedulez=$this->attendance_model->get_schedules();

			$schedules=array();//holds sched id, letter pair

			foreach ($schedulez as $schedule){

				$schedules["'".$schedule['letter']."'"]=$schedule['schedule_id'];

			}
			
			$person_ids=array();
			$facility_ids=array();
			
			foreach($people as $person){
			
				array_push($person_ids,$person['ihris_pid']);
				
			}
		
			$rowsnow=0;
			 //loop from first data untill last data
	        for($i=2;$i<=$totalrows;$i++){
	              	  
				$person_id= $objWorksheet->getCellByColumnAndRow(0,$i)->getValue();	//col 1		
	         	$name= $objWorksheet->getCellByColumnAndRow(1,$i)->getValue(); //Excel Column 2 
	         	 
				$fro = $objWorksheet->getCellByColumnAndRow(2,$i);
				  
				$from= $fro->getValue();

				$fromy = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($from)); 

				$oneday="+1 day";//1 day

				$sdate = strtotime($fromy);

				$too = date('Y-m-d',strtotime($oneday, $sdate)); //add one to from to get end of duty
							  
							  
				$duty_letter=$objWorksheet->getCellByColumnAndRow(3,$i)->getValue(); //Excel Column 5 


				$duty=$schedules["'".$duty_letter."'"];// schedule_id depending on duty letter

				$entry=$fromy.$person_id;

		
				if((in_array($person_id,$person_ids)) and ($fromy!="" and $fromy!="1970")){
					
					 	$excel_data=array('entry_id'=>$entry,'facility_id'=>$facility,'ihris_pid'=>$person_id,'schedule_id'=>$duty,'color'=>'#000','duty_date'=>$fromy,'end'=>$too,'allDay'=>'true');
						 
						 
					 	$this->attendance_model->save_upload($excel_data);//insertion
						 
						 $rowsnow+=1;
					 
						 //print_r($excel_data);	  			  
				}
		    }
	    }//else for upload
	} // end of function - upload_rota



	function getDistricts(){
	    
	    $districts=$this->attendance_model->get_districts();
	    
	    $districts_array=array();
	    
	    foreach($districts as $district):
	        
	        $districts_array[$district['district']]=$district['district_id'];
	        
	        
	    endforeach;
	    
	    //print_r($districts_array);
	    
	    return $districts_array;
	}


	public function manualUpload(){
    
    
        $config['upload_path'] = FCPATH.'uploads/';

        $config['allowed_types']        = 'csv|xls|xlsx';
        $config['max_size']             = 2000000;
        $config['file_name']            ="hrisdata_file";

        //$file_name=$config['file_name'];
        
        $districts=$this->attendance_model->get_districts();
        
        $prefix=$this->input->post('category');
        

        $this->load->library('upload', $config);

        $this->upload->initialize($config);

        if (!$this->upload->do_upload('ihrisdata')){

                $error =$this->upload->display_errors();

				$this->session->set_flashdata('alert',$error);
	
                redirect(base_url() . "admin/settings");
        }
        else{
                          
	        $file_name= $this->upload->data('file_name'); 
	       
	       	$this->load->library('excel');
			
			$type = PHPExcel_IOFactory::identify('uploads/'.$file_name);
			
			$objReader=PHPExcel_IOFactory::createReader($type);     //For excel 2003 
			
	          //Set to read only
	        //$objReader->setReadDataOnly(true); 		  
	        //Load excel file
			$objPHPExcel=$objReader->load(strip_tags(FCPATH.'uploads/'.$file_name));	
			
			 
	        $totalrows=$objPHPExcel->setActiveSheetIndex(0)->getHighestRow();   //Count Numbe of rows avalable in excel      	 
	        $objWorksheet=$objPHPExcel->setActiveSheetIndex(0);  
         
         
         
		     //truncate table if data file contains data
		    if($totalrows>0){
		         
		         $this->db->query("delete from ihrisdata where ihris_pid like '%$prefix%'");
		        // $this->db->query("truncate ihrisdata");
		         
		    }
        

    		$districts=$this->getDistricts();
		
			$rowsnow=0;
		 	//loop from first data untill last data
            for($i=2;$i<=$totalrows;$i++){
              
				  
				  $person_id= $prefix.$objWorksheet->getCellByColumnAndRow(0,$i)->getValue();
				  $district= $objWorksheet->getCellByColumnAndRow(2,$i)->getValue(); //Excel Column 2
				  			
				  			
	              $district_id= $districts[$district]; // district name from built array
	              
				  $nin=$objWorksheet->getCellByColumnAndRow(3,$i)->getValue(); //Excel Column 3 parent fname
				  $ipps=$objWorksheet->getCellByColumnAndRow(4,$i)->getValue(); //Excel Column 4 
				  $facility_type_id=$objWorksheet->getCellByColumnAndRow(5,$i)->getValue(); //Excel Column 5 
				  $facility_id=$prefix.$objWorksheet->getCellByColumnAndRow(6,$i)->getValue(); //Excel Column 6
				  $facility=$objWorksheet->getCellByColumnAndRow(7,$i)->getValue(); //Excel Column 7 
				  $department=$objWorksheet->getCellByColumnAndRow(8,$i)->getValue(); //Excel Column 8 
				  $job_id=$objWorksheet->getCellByColumnAndRow(9,$i)->getValue(); //Excel Column 9 
				  $job_title=$objWorksheet->getCellByColumnAndRow(10,$i)->getValue(); //Excel Column 11 
				  $surname=$objWorksheet->getCellByColumnAndRow(11,$i)->getValue(); //Excel Column 12 
				  $firstname=$objWorksheet->getCellByColumnAndRow(12,$i)->getValue(); //Excel Column 13 
				  $othername=$objWorksheet->getCellByColumnAndRow(13,$i)->getValue(); //Excel Column 14 
				  $mobile_phone=$objWorksheet->getCellByColumnAndRow(14,$i)->getValue(); //Excel Column 15 
				  $telephone=$objWorksheet->getCellByColumnAndRow(15,$i)->getValue(); //Excel Column 16 
				  
		    
				 $excel_data=array('ihris_pid'=>$person_id,'district_id'=>$district, 'district'=>$district ,'nin'=>$nin ,'ipps'=>$ipps , 'facility_type_id'=>$facility_type_id
				 , 'facility_id'=>$facility_id,'facility'=>$facility,'department'=>$department, 'job_id'=>$job_id, 'job'=>$job_title, 'surname'=>$surname
				 , 'firstname'=>$firstname, 'othername'=>$othername, 'mobile'=>$mobile_phone, 'telephone'=>$telephone);

				 	 $this->attendance_model->read_employee_csv($excel_data);
				 
				 $rowsnow+=1;
				 //print_r($excel_data);
 		    }
      
      

			unlink(FCPATH.'uploads/'.$file_name); //File Deleted After uploading
			
			$alert='<div class="alert alert-info alert-dismissable"><a href="" class="pull-right" data-dismiss="modal">&times;</a><h5>'.$rowsnow.'</h5> records have been imported into the database</div>';
			
			$this->session->set_flashdata('alert',$alert);
			
             redirect(base_url() . "admin/settings");              
        }//else for upload error
    }//end of manualUpload
    
    
    
    
	public function timeLogReport(){
	    
	    $search_data=$this->input->post();
	    
	    if($search_data){
			$data['from']=$search_data['date_from'];
			$data['to']=$search_data['date_to'];
			$data['name']=$search_data['name'];
		}
		
		else{
		    
			$data['from']=date('Y-m-').'01';
			$data['to']=date('Y-m-d');
			$data['name']="";	    
		}
	    
	    $config=array();
	    $config['base_url']=base_url()."attendance/timeLogReport";
	    $config['total_rows']=$this->attendance_model->count_timelogs();
	    $config['per_page']=20; //records per page
	    $config['uri_segment']=3; //segment in url
	    
	    //pagination links styling
	    $config['full_tag_open'] = "<ul class='pagination'>";
        $config['full_tag_close'] = '</ul>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="active"><a href="#">';
        $config['cur_tag_close'] = '</a></li>';
        $config['prev_tag_open'] = '<li>';
        $config['prev_tag_close'] = '</li>';
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';
    
    
    
        $config['prev_link'] = '<i class="fa fa-long-arrow-left"></i>';
        $config['prev_tag_open'] = '<li>';
        $config['prev_tag_close'] = '</li>';
    
    
        $config['next_link'] = '<i class="fa fa-long-arrow-right"></i>';
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';
        
	    $this->pagination->initialize($config);
	    
	    $page=($this->uri->segment(3))? $this->uri->segment(3):0; //default starting point for limits
	    
	    $data['timelogs']=$this->attendance_model->fetchTimeLogs($config['per_page'],$page,$search_data);
	    
	   
		
	
		
		$data['links']=$this->pagination->create_links();
		
	    $this->load->view('timelogs_report',$data);
	}


	public function importMachineCSv(){
     
		$prefix=$this->input->post('category');
		  
		$filename=$_FILES["machine_file"]["tmp_name"];
 
		if($_FILES["machine_file"]["size"] > 0){  //check whether file has data
	

			$file = fopen($filename, "r");

			$count = 0;  

			$allData=array();

			$rowsnow=0;

			while (($machineData = fgetcsv($file, 10000, ",")) !== FALSE){
			  
			    $count++;
			                                          // add this line
				if($count>1){
		    
		    
					$person_id= $prefix."person|".$machineData[0];

					///////start processing data

					$clockin='';         
					$clockout='';  
						 
						 //date_default_timezone_set("Africa/Kampala");

					$time_in=$machineData[5]; //rawtimein

					$time_out=$machineData[6]; //raw timeout

					if(!empty($time_in)){
					    
						$time_in=date_create($machineData[5]); //rawtimein

						$clockin= date_format($time_in,"H:i"); //converted clock in time

					}
				
					else{

					    $clockin=NULL;
					    
					}

					if(!empty($time_out)){
						$time_out=date_create($machineData[6]);
						$clockout=date_format($time_out,"H:i"); //converted clock out time

					}
					else{

						$clockout=NULL;

					}
					//1527552000

					if($prefix!=='person|'){ 
						     
						$facility_id=$prefix.$machineData[4]; //Excel Column 4 ;
					}
						 
					else{

						$facility_id=$machineData[4]; //Excel Column 4 ;

					}
						     

					$mydate=date_create($machineData[7]);

					$date=date_format($mydate,"Y-m-d"); //Excel Column 7
			  
					$entry_id=$date.$person_id;

					$excel_data=array('ihris_pid'=>$person_id,'time_in'=>$clockin, 'time_out'=>$clockout ,'date'=>$date ,'entry_id'=>$entry_id, 'facility_id'=>$facility_id);
								 
					$insert=$this->attendance_model->read_machine_csv($excel_data);
								
					if($insert){
								     
		     	        $rowsnow+=1;
		     	    }

					////////
					    
					// array_push($allData,$machineData);
				}

			}//end while

			if($rowsnow>0){

				$msg="<font color='green'>".$rowsnow. "records Imported successfully</font>";
			}

			else{
			    
			    $msg="<font color='red'>Import unsuccessful</font>";
			    
			}
	        
		}//end file size check

		else{
	    
	    
	    $msg="<font color='red'>No Data in FIle</font>";
	    
		}
	                
	    $this->session->set_flashdata("msg",$msg);
	    
	    redirect(base_url() . "attendance/machinedata");

	} // end of function importMachineCSv()




	public function machineCsv($from,$to){
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
		$this->excel->getActiveSheet()->getStyle("A1:F1")->applyFromArray(array("font" => array("bold" => true)));

		$this->excel->getActiveSheet()->setCellValue('A1', 'NAME');

		$this->excel->getActiveSheet()->setCellValue('B1', 'FACILITY');

		$this->excel->getActiveSheet()->setCellValue('C1', 'TIME IN');

		$this->excel->getActiveSheet()->setCellValue('D1', 'TIME OUT');

		$this->excel->getActiveSheet()->setCellValue('E1', 'HOURS WORKED');

		$this->excel->getActiveSheet()->setCellValue('F1', 'DATE');

		       
			                //retrive contries table data
		$rs = $this->attendance_model->getMachineCsvData($from,$to);
		                
		                
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



	public function importAttCSv(){

		$filename=$_FILES["att_file"]["tmp_name"];
		 
		if($_FILES["att_file"]["size"] > 0){  //check whether file has data

			$file = fopen($filename, "r");

			$count = 0;  

			$allData=array();

			$rowsnow=0;

			while (($machineData = fgetcsv($file, 10000, ",")) !== FALSE){
			  
				$count++;                                      // add this line

				if($count>1){

				  ///////start processing data

					$districtid=$machineData[0]; 
					$district=$machineData[1];
					$personid=$machineData[2];
					$job_id=$machineData[10];
					$job=$machineData[11];
					$surname=$machineData[3];
					$firstname=$machineData[4];
					$salary=$machineData[5];
					$facility_type_id=$machineData[6];
					$facility_type=$machineData[7];
					$facility_id=$machineData[8];
					$facility=$machineData[9];
					$absent=$machineData[12];
					$leavedays=$machineData[13];
					$offduty=$machineData[14];
					$request=$machineData[15];
					$present=$machineData[16];
					$month=$machineData[17];
					$year=$machineData[18];

					    
					 $excel_data=array(
					 'district_id'=>$districtid,
					 'district'=>$district,
					 'person_id'=>$personid ,
					 'job_id'=>$job_id,
					 'job'=>$job,
					 'surname'=>$surname,
					 'firstname'=>$firstname,
					 'salary'=>$salary,
					 'facility_type_id'=>$facility_type_id,
					 'facility_type'=>$facility_type,
					 'facility'=>$facility,
					 'absent'=>$absent,
					 'leavedays'=>$leavedays,
					 'offduty'=>$offduty,
					 'request'=>$request,
					 'present'=>$present,
					 'month'=>$month,
					 'year'=>$year
					 );

					//print_r($excel_data);

					$insert=$this->attendance_model->read_attendance_csv($excel_data);
						
					if($insert){
					     
					    $rowsnow+=1;
					}
			    
					// array_push($allData,$machineData);
				}
			}//end while

			if($rowsnow>0){

				$msg="<font color='green'>".$rowsnow. "records Imported successfully</font>";
			}
			else{
			    
			    $msg="<font color='red'>Import unsuccessful</font>";
			    
			}
	    }//end file size check

		else{
		     
		    $msg="<font color='red'>No Data in FIle</font>";
		    
		}

	    $this->session->set_flashdata("msg",$msg);
	                
	    redirect(base_url() . "attendance/auditupload");

	}






}//end of class

