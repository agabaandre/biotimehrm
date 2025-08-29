<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reasons_mdl extends CI_Model {
	protected $table;
	protected $user;

	public function __Construct(){

		parent::__Construct();
		$this->table="reasons";
		$this->user=$this->session->get_userdata();

	}



public function saveReason(){

	$data=$this->input->post();

	$done=$this->db->insert('reasons',$data);

	if($done){

		$message="<font class='alert text-success'><i class='fa fa-check'></i> Reason Saved</font>";

	} else{

			$message="Operation Failed";
	}  

	return $message;
		
}

	public function getAll()
	{
		$table=$this->table;
		$this->db->join("schedules", "schedules.schedule_id=reasons.schedule_id");
		$query=$this->db->get($table);
		
		return $query->result();
	}

	public function getById($id)
	{
		$table=$this->table;
		$this->db->where('r_id',$id);
		$query=$this->db->get($table);
		
		return $query->row();
	}
}