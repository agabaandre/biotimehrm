<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jobs_mdl extends CI_Model {

	/**
	 * Database table name
	 *
	 * @var string
	 */
	protected $table;
	
	public function __Construct(){

		parent::__Construct();

        $this->table="jobs";

	}



	public function getJobs($job_id = null)
	{
		if ($job_id !== null && $job_id !== '') {
			$this->db->where('job_id', $job_id);
		}

		$query = $this->db->get('jobs');

		return $query->result();
	}


	

	


}
