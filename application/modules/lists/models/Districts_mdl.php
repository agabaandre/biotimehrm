<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Districts_mdl extends CI_Model {

	
	public function __Construct(){

		parent::__Construct();
		$this->table="employee_districts";

	}

	public function getDistricts(){

		$this->db->select('name, region,id, date_added');
	           $this->db->order_by('name', 'ASC');
		$query=$this->db->get('employee_districts');

		return $query->result();
 
	}
	public function get_all_Districts()
	{

		$this->db->select('distinct(district_id),district');
		//	$this->db->where("district_id!=''");
		$this->db->order_by('district', 'ASC');
		$query = $this->db->get('ihrisdata');

		return $query->result();
	}

		// to save in the district database /.....
	public function save_district($postdata){

		$data=array(
		'name'=>$postdata['name'],
		'region'=>$postdata['region']
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

	
	public function updateDistrict($postdata){

	    $id = $postdata['id'];
		$this->db->where('id',$id);
		$this->db->update($this->table, $postdata);
		$rows=$this->db->affected_rows();

		if($rows>0){

			return "The ".$postdata['name']." "." district has been updated";
		}

		else{

			return "No Operation made, seems like no changes made";
		}
	}
	 

	 public function deleteDistrict(){

	    $data=$this->input->post('id');
		$this->db->where('id',$data);
		$this->db->delete($this->table);

		$rows = $this->db->affected_rows();
		if($rows>0){

			return "The district has been updated";
		}

		else{

			return "No Operation made, seems like no changes made";
		}
	}



	


}
