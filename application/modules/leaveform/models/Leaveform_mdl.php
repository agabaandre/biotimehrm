<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Leaveform_mdl extends CI_Model {

	
	public function __Construct(){

		parent::__Construct();

	}

	public function getLeave($request_id){

		$this->db->join('ihrisdata','ihrisdata.ihris_pid=requests.ihris_pid');
        $this->db->where('requests.entry_id',$request_id);

        $qry=$this->db->get('requests');

        return $qry->row();


	}


	
		



}
