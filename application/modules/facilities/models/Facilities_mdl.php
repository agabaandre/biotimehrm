<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Facilities_mdl extends CI_Model {

	
	public function __Construct(){

		parent::__Construct();

        $this->table="facilities";

	}



	public function getFacilities($district_id=FALSE)
	{

		$this->db->select('distinct(facility),facility_id');

		if($district_id){
		$this->db->where('district_id',$district_id);
		}
		
		$query=$this->db->get('ihrisdata');

		return $query->result();
 
	}


	

	public function get_facility()
	{
		$district=$_SESSION['district'];
		
		if($district!==""){
		$query=$this->db->query("select distinct facility_id,facility,district_id from ihrisdata where district_id='$district' order by facility asc");
		
		}
		
		else
		{
		  $query=$this->db->query("select distinct facility_id,facility,district_id from ihrisdata order by facility asc");  
		    
		}

		$res=$query->result_array();

		return $res;
   
	}

	//THIS gets All facilities from the facility table
	public function getAll_Facilities(){
      
		   $this->db->order_by('facility','ASC');
      $qry=$this->db->get($this->table);

      return $qry->result();

    } 	

    	// to save in the district database /.....
	public function saveFacility(){

		$data=array(
		'facility_id'=>$this->input->post('facility_id'),
		'facility'=>$this->input->post('facility')
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

	    $data=$this->input->post('f_id');
		$this->db->where('f_id',$data);

		$this->db->update($this->table);
		$rows=$this->db->affected_rows();

		if($rows>0){

			return "The ".$data['facility']." "." Facility has been updated";
		}

		else{

			return "No Operation made, seems like no changes made";
		}
	}

	


	public function deleteFacility(){

	    $data=$this->input->post('f_id');
		$this->db->where('f_id',$data);

		$this->db->delete($this->table);
		$rows=$this->db->affected_rows();

		if($rows>0){

			return "The ".$data['facility']." "." district has been updated";
		}

		else{

			return "No Operation made, seems like no changes made";
		}
	}
	


}
