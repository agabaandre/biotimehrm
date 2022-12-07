<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jobs extends MX_Controller {

	
	public function __Construct(){

		parent::__Construct();

		$this->load->model('jobs_mdl','jobMdl');

	}


	public function getJobs($job_id=FALSE){

		$jobs=$this->jobMdl->getJobs($job_id);

    return $jobs;


	}
}
