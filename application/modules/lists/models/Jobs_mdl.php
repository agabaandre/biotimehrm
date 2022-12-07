<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jobs_mdl extends CI_Model {

	
	public function __Construct(){

		parent::__Construct();
		$this->table="employee_jobs";

	}

	public function getJobs(){
		$this->db->select('job_title,description,id,job_id,created_at');
	    $this->db->order_by('job_title', 'ASC');
		$query=$this->db->get($this->table);
		return $query->result();
 
	}

	public function saveJob($postdata){
		$data=array(
		'job_title'=>$postdata['job_title'],
		'description'=>$postdata['description'],
        'job_id'=>$postdata['job_id']

		);

		$qry=$this->db->insert($this->table, $data);
		$rows=$this->db->affected_rows();

		if($rows>0){

			return "Job has been Added Successfully";
		}

		else{

			return "Operation failed";
		}

	}

	public function updateJob($postdata){

	    $id = $postdata['id'];
		$this->db->where('id',$id);
		$this->db->update($this->table, $postdata);
		$rows=$this->db->affected_rows();

		if($rows>0){

			return "The Job has been updated";
		}

		else{

			return "No Operation made, seems like no changes made";
		}
	}
	 

	 public function deleteJob(){

	    $data=$this->input->post('id');
		$this->db->where('id',$data);
		$this->db->delete($this->table);

		$rows = $this->db->affected_rows();
		if($rows>0){

			return "The Job has been updated";
		}

		else{

			return "No Operation made, seems like no changes made";
		}
	}



	


}
