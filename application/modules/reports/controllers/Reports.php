<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends MX_Controller {

	
	public function __Construct(){

		parent::__Construct();

		$this->load->model('reports_mdl');
		$this->module="reports";
		$this->title="Reports";


	}

	public function index(){

		//$data['requests']=$this->requests;
		$data['title']=$this->title;
		$data['uptitle']="Reports";
		
		$data['view']='reports';
		$data['module']=$this->module;
		echo Modules::run('templates/main', $data);

	}


	



	


}
