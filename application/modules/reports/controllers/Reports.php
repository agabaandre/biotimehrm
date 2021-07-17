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

		
		$data['title']=$this->title;
		$data['uptitle']="Attendance Reporting";
		$data['view']='att_rate';
		$data['module']=$this->module;
		echo Modules::run('templates/main', $data);

	}


	



	


}
