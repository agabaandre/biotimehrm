<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends MX_Controller {

	
	public function __Construct(){

		parent::__Construct();

		$this->load->model('reports_mdl');
		$this->module="reports";
		$this->title="Reports";
		$this->filters=Modules::run('filters/sessionfilters');
        //doesnt require a join on ihrisdata
        $this->ufilters=Modules::run('filters/universalfilters');
        // requires a join on ihrisdata with district level
        $this->distfilters=Modules::run('filters/districtfilters');


	}

	public function index(){

		//$data['requests']=$this->requests;
		$data['title']=$this->title;
		$data['uptitle']="Reports";
		
		$data['view']='reports';
		$data['module']=$this->module;
		echo Modules::run('templates/main', $data);

	}
	public function rosterRate(){

		//$data['requests']=$this->requests;
		$data['title']=$this->title;
		$data['uptitle']="Duty Roster Reporting";
		
		$data['view']='roster_rate';
		$data['module']=$this->module;
		echo Modules::run('templates/main', $data);

	}
	public function attendanceRate(){

		
		$data['title']='Attendance Reporting Rate';
		$data['uptitle']="Attendance Reporting";
		$data['view']='attendance_rate';
		$data['module']=$this->module;
		echo Modules::run('templates/main', $data);

	}
	public function attendroster(){

		
		$data['title']='Attendance vs Duty Roster';
		$data['uptitle']="Attendance Reporting";
		$data['view']='roster_att';
		$data['module']=$this->module;
		echo Modules::run('templates/main', $data);

	}


	public function graphData(){
		
		 
         $data=$this->reports_mdl->getgraphData();
	return $data;
	}
	public function dutygraphData(){
		$data=$this->reports_mdl->dutygraphData();
   return $data;
   }

	public function  attroData(){
		$data=$this->reports_mdl->attroData();
     //print_r($data);
	echo  json_encode($data,JSON_NUMERIC_CHECK);
	}

	public function  person_attendance_all(){
		$data=$this->reports_mdl->person_attendance_all();
        print_r($data);
		
	
	}
	public function  average_hours(){
		$data=$this->reports_mdl->person_attendance_all();
        print_r($data);
		
	
	}


	



	


}
