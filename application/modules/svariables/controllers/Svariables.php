<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Svariables extends MX_Controller {

	
	public function __Construct(){

		parent::__Construct();

		$this->load->model('svariables_mdl');
		$this->user=$this->session->get_userdata();

	}


	public function index(){
		$data['title'] = "Settings - Constants & Variables";
		$data['uptitle'] = "Constants & Variables";
		$data['module']='svariables';
		$data['view']="variables";
		$postdata=$this->input->post();
		if($this->input->post('language')){
		$data['message'] = $this->svariables_mdl->update_variables($postdata);
		}
	   echo Modules::run('templates/main',$data);
	}
	public function getSettings(){
	   return $this->svariables_mdl->getSettings();

	}
}
