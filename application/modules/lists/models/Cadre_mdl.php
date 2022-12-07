<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cadre_mdl extends CI_Model {

	
	public function __Construct(){

		parent::__Construct();
		$this->table="employee_cadre";

	}

	public function getCadres(){

		$this->db->select('cadre, description,id,sector,created_at');
	           $this->db->order_by('cadre', 'ASC');
		$query=$this->db->get($this->table);

		return $query->result();
 
	}

		// to save in the district database /.....
	public function save_Cadre($postdata){

		$data=array(
		'cadre'=>$postdata['cadre'],
		'description'=>$postdata['description'],
        'sector'=>$postdata['sector']

		);

		$qry=$this->db->insert($this->table, $data);
		$rows=$this->db->affected_rows();

		if($rows>0){

			return "Cadre has been Added Successfully";
		}

		else{

			return "Operation failed";
		}

	}

	public function updateCadre($postdata){

	    $id = $postdata['id'];
		$this->db->where('id',$id);
		$this->db->update($this->table, $postdata);
		$rows=$this->db->affected_rows();

		if($rows>0){

			return "The Cadre has been updated";
		}

		else{

			return "No Operation made, seems like no changes made";
		}
	}
	 

	 public function deleteCadre(){

	    $data=$this->input->post('id');
		$this->db->where('id',$data);
		$this->db->delete($this->table);

		$rows = $this->db->affected_rows();
		if($rows>0){

			return "The Cadre has been updated";
		}

		else{

			return "No Operation made, seems like no changes made";
		}
	}



	


}
