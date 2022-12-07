<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Workshops_mdl extends CI_Model {

	
	public function __Construct(){

		parent::__Construct();
		$this->table="workshops";
		$this->facility=$this->session->userdata['facility'];
        $this->department=$this->session->userdata['department_id'];
        $this->division=$this->session->userdata['division'];
        $this->unit=$this->session->userdata['unit'];
        $userdata=$this->user=$this->session->get_userdata();

	}



	public function getWorkshop($id=NULL)
	{
		$table=$this->table;  
		$userdata=$this->user=$this->session->get_userdata();
		$id=$userdata['ihris_pid'];
	
	 $this->db->select('entry_id,ihrispid,date,location,surname,othername,firstname,request_id,url,street,city,region,country');
	 $this->db->from($table);
	 if ($userdata['ihris_pid']){
		$this->db->where('workshops.ihrispid',$id);
	 }
	 $table=$this->table;
	 $facility=$this->facility;

	 $department=$this->department;
	 $division=$this->division;
	 $unit=$this->unit;

 
   if(($this->user['role']!=='sadmin')||(!empty($department))||(!empty($division))||(!empty($unit))){


			 if(!empty($department)){
				 $this->db->where('ihrisdata.department_id',$department);
				 
			 }
			

			 if(!empty($division)){
				 $this->db->where('ihrisdata.division',$division);
				 
			 }


			 if(!empty($unit)){
				  $this->db->where('ihrisdata.unit',$unit);
				 
			 }
			 if(!empty($facility)){
				 $this->db->where('ihrisdata.facility_id',$facility);
				
			}
		   
			 
			
	 
   }
	 $this->db->join('ihrisdata','ihrisdata.ihris_pid=workshops.ihrispid');
	 $query=$this->db->get();
	 return $query->result();
	}
	public function getDevices($id=NULL)
	{
		
		$userdata=$this->user=$this->session->get_userdata();
		$id=$userdata['ihris_pid'];
	
	 $this->db->select('*');
	 $this->db->distinct('personId');
	 $this->db->from('checkin_devices');
	 if ($userdata['ihris_pid']){
		$this->db->where('checkin_devices.personId',$id);
	 }
	 $facility=$this->facility;

	 $department=$this->department;
	 $division=$this->division;
	 $unit=$this->unit;

 
   if(($this->user['role']!=='sadmin')||(!empty($department))||(!empty($division))||(!empty($unit))){


			 if(!empty($department)){
				 $this->db->where('ihrisdata.department_id',$department);
				 
			 }
			

			 if(!empty($division)){
				 $this->db->where('ihrisdata.division',$division);
				 
			 }


			 if(!empty($unit)){
				  $this->db->where('ihrisdata.unit',$unit);
				 
			 }
			 if(!empty($facility)){
				 $this->db->where('ihrisdata.facility_id',$facility);
				
			}
		   
			 
			
	 
   }
	 $this->db->join('ihrisdata','ihrisdata.ihris_pid=checkin_devices.personId');
	 $query=$this->db->get();
	 return $query->result();
	}
	public function getRequests($request){
		

		$this->db->select('*');
		$this->db->from('requests');
		$this->db->where('entry_id',$request);

		$query=$this->db->get();
		return $query->result();
	}
	public function updatemapData($entry_id,$url,$street,$city,$region,$country){
		
		$this->db->select('*');
		$this->db->from('workshops');
		$this->db->where('entry_id',$entry_id);

		$query=$this->db->get();

		if ($query->num_rows()==1){
			$data=array('url'=>$url, 'street'=> $street, 'city'=>$city, 'region'=>$region, 'country'=>$country);
			
			$this->db->where('entry_id',$entry_id);
            $this->db->update('workshops',$data);
			return true;
		}
		else{
			return false;
		}
		
	}
		



}
