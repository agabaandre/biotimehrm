<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Districts_mdl extends CI_Model {

	
	public function __Construct(){

		parent::__Construct();
		$this->table="districts";

	}



	public function getDistricts(){

		$this->db->select('distinct(district_id),district');
	//	$this->db->where("district_id!=''");
		$query=$this->db->get('ihrisdata');

		return $query->result();
 
	}

		// to save in the district database /.....
	public function save_district(){

		$data=array(
		'district_id'=>$this->input->post('district_id'),
		'district'=>$this->input->post('district')
		);

		$qry=$this->db->insert($this->table, $data);
		$rows=$this->db->affected_rows();

		if($rows>0){

			return "District has been Added Successfully";
		}

		else{

			return "Operation failed";
		}

	}

	public function getDistrict($id){

		$this->db->select('district');
		$this->db->where('district_id',$id);
		$query=$this->db->get('ihrisdata');

		$result=$query->row();

		return $result->district;
 
	}
	
	
	public function getFacility($id){

		$this->db->select('facility');
		$this->db->where('facility_id',$id);
		$query=$this->db->get('ihrisdata');

		$result=$query->row();

		return $result->facility;
 
	}


	public function getFacilities($districtid){

		$this->db->select('distinct(facility_id),facility');
		$this->db->where('district_id',$districtid);
		$query=$this->db->get('ihrisdata');

		$result=$query->result();

		return $result;
 
	}

	//this gets all districts from the district table
	public function getAll_Districts(){

		$query=$this->db->get($this->table);

		return $query->result();
 
	}

	
	public function updateDistrict(){

	    $postdata=$this->input->post();

		$this->db->update($this->table);
		$rows=$this->db->affected_rows();

		if($rows>0){

			return "The ".$postdata['district']." "." district has been updated";
		}

		else{

			return "No Operation made, seems like no changes made";
		}
	}
	 

	 public function deleteDistrict(){

	    $data=$this->input->post('d_id');
		$this->db->where('d_id',$data);

		$this->db->delete($this->table,$data);
		$rows=$this->db->affected_rows();

		if($rows>0){

			return "The ".$data['district']." "." district has been updated";
		}

		else{

			return "No Operation made, seems like no changes made";
		}
	}



	


}
