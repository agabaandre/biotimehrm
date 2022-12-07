<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reasons extends MX_Controller {

	
	public function __Construct(){

		parent::__Construct();

		$this->load->model('reasons_mdl');

	}
		public function addReason(){

		//$data['requests']=$this->requests;
		$data['title']='Add a Reason';
		$data['view']='add_reason';
		$data['module']="reasons";
		echo Modules::run('templates/main', $data);

	}


	public function saveReason(){

		$sendReason=$this->reasons_mdl->saveReason();


		Modules::run('utility/setFlash',$sendReason);

		redirect('reasons/addReason');

	}

	public function getAll(){
		$reasons=$this->reasons_mdl->getAll();
		return $reasons;
	}

	public function requestById($rq_Id){
		$reasonInfo=$this->reasons_mdl->getById($rq_Id);
		return $reasonInfo;
	}

	


}
