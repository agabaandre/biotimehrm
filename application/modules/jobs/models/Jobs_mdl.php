<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jobs_mdl extends CI_Model {

	
	public function __Construct(){

		parent::__Construct();

        $this->table="jobs";

	}



	public function getJobs($job_id)
	{

		$query=$this->db->get('jobs');

		return $query->result();
 
	}


	

	


}
