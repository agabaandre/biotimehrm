<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Facilities_mdl extends CI_Model {

	
	public function __Construct(){

		parent::__Construct();

        $this->table="employee_facility";

	}


	public function getAll()
	{
		$query = $this->db->select('f.id, f.facility_id, f.facility, f.district_id, f.institution_cateegory, f.	institution_type, f.institution_level, d.name,d.region')
     	->from('employee_facility as f')
     	->join('employee_districts as d', 'd.id = f.district_id', 'LEFT')
     	->get();

		return $query->result();
 
	}

	public function getFacilitiesByDistrict($district_id=FALSE)
	{
		$query = $this->db->select('f.id as fac_id, f.facility_id, f.facility, f.district_id, d.name,d.region')
     	->from('employee_facility as f')
		->where('district_id',$district_id)
     	->join('employee_districts as d', 'd.id = f.district_id', 'LEFT')
     	->get();

		return $query->result();
 
	}

	public function get_facility()
	{
		$district=$_SESSION['district'];
		
		if($district!==""){
		$query=$this->db->query("select distinct facility_id,facility,district_id from ihrisdata where district_id='$district' order by facility ASC");
		
		}
		
		else
		{
		  $query=$this->db->query("select distinct facility_id,facility,district_id from ihrisdata order by facility ASC");  
		    
		}

		$res=$query->result_array();

		return $res;
   
	}


    // to save in the facility /.....
	public function saveFacility($postdata){

		$data=array(
		'facility_id'=>$postdata['facility_id'],
		'facility'=>$postdata['facility'],
		'institution_cateegory'=>$postdata['institution_cateegory'],
		'institution_type'=>$postdata['institution_type'],
		'institution_level'=>$postdata['institution_level'],
		'district_id'=>$postdata['district_id']
		);

		$qry=$this->db->insert($this->table, $data);
		$rows=$this->db->affected_rows();

		if($rows>0){

			return "Facility has been Added Successfully";
		}

		else{

			return "Operation failed";
		}

	}

	
	public function updateFacility(){

	    $data=$this->input->post('id');
		$this->db->where('id',$data);

		$this->db->update($this->table);
		$rows=$this->db->affected_rows();

		if($rows>0){

			return "The Facility has been updated";
		}

		else{

			return "No Operation made, seems like no changes made";
		}
	}

	


	public function deleteFacility(){

	    $data=$this->input->post('id');
		$this->db->where('id',$data);

		$this->db->delete($this->table);
		$rows=$this->db->affected_rows();

		if($rows>0){

			return "The facility has been updated";
		}

		else{

			return "No Operation made, seems like no changes made";
		}
	}
	


}
