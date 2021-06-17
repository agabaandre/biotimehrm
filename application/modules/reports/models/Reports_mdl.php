<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reports_mdl extends CI_Model {

	
	public function __Construct(){

		parent::__Construct();
		$this->department=$this->session->userdata['department_id'];

	}



	public function getFacilities($district_id)
	{

		$this->db->select('distinct(facility),facility_id');
		$this->db->where('district_id',$district_id);
		$query=$this->db->get('ihrisdata');

		return $query->result();
 
	}



	



	


}
