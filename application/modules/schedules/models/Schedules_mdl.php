<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Schedules_mdl extends CI_Model {

	
	public function __Construct(){

		parent::__Construct();
		$this->table="schedules";

	}


public function getrotaSchedules(){
	    
		$this->db->where("purpose",'r');
		$this->db->where("status",'1');
		$query= $this->db->get('schedules');
		return $query->result();
	}
	
 public function getattSchedules(){
	    
		$this->db->where("purpose",'a');
		$this->db->where("status",'1');
		$query= $this->db->get('schedules');
		return $query->result();
   }
   public function getleaveSchedules(){
	$this->db->select('letter,schedule_id');
	$this->db->where("purpose",'r');
	$this->db->like("schedule",'leave','both');
	$query= $this->db->get('schedules');
	$res=array();
	foreach($query->result() as $row){
		$leaveschedules=$res[$row->letter]=$row->schedule_id;
	}
	return $leaveschedules;
}

   public function getattSchedules2(){
	$this->db->select('letter,schedule_id');
	$this->db->where("purpose",'a');
	$this->db->where("status",'1');
	$query= $this->db->get('schedules');
	$res=array();
	foreach($query->result() as $row){
		$res[$row->letter]=$row->schedule_id;
	}
	return $res;
}
   public function getrosterKey(){
	    
	$this->db->where("purpose",'r');
	$this->db->where("status",'1');
	$query= $this->db->get('schedules');
	return $query->result();
}
public function getleaverosterKey(){
	    
	$this->db->where("purpose",'r');
	$this->db->where("status",'1');
	$this->db->like('schedules.schedule','leave','both');
	$query= $this->db->get('schedules');
	return $query->result();
}
public function getattKey(){
	    
	$this->db->where("purpose",'a');
	$this->db->where("status",'1');
	$query= $this->db->get('schedules');
	return $query->result();
}

public function add_schedule(){

	$data=array(
		'schedule'=>$this->input->post('schedule'),
		'letter'=>$this->input->post('letter'),
		'starts'=>$this->input->post('starts'),
		'ends'=>$this->input->post('ends'),
		'purpose'=>$this->input->post('purpose')

	);

	$done=$this->db->insert('schedules',$data);

	if($done){

		$message="Schedule Added";

	} else{

		$message=$data['purpose'];
			//$message="Operation Failed";
	}  

	return $message;
		
}

public function delete_attschedules($id){
		
		$att_schdl=$id['schedule_id'];
			$this->db->where('schedule_id',$att_schdl);
			$query= $this->db->delete($this->table,$id);
		if($query){

			return "Schedule Deleted";
		}   
}
	
	public function	update_attschedule($post_data){

			$att_schdl=$post_data['schedule_id'];
			$this->db->where('schedule_id',$att_schdl);
			$query= $this->db->update($this->table,$post_data);
			
			if($query){

				$msg="Schedule Updated";
			} 
			else{

					$msg="Operation failed, Try again";
				}

			
			return $msg;
	}

	


	public function add_rosterschedule(){

	$data=array(
		'schedule'=>$this->input->post('schedule'),
		'letter'=>$this->input->post('letter'),
		'starts'=>$this->input->post('starts'),
		'ends'=>$this->input->post('ends'),
		'purpose'=>$this->input->post('purpose')

	);

	$done=$this->db->insert('schedules',$data);

	if($done){

		$message="Duty roster Schedule Added";

	} else{

		$message=$data['purpose'];
			//$message="Operation Failed";
	}  

	return $message;
		
}

public function delete_rosterschedule($id){
		
		$att_schdl=$id['schedule_id'];
			$this->db->where('schedule_id',$att_schdl);
			$query= $this->db->delete($this->table,$id);
		if($query){

			return "Schedule Deleted";
		}   
}
	
	public function	update_rosterschedule($post_data){

			$att_schdl=$post_data['schedule_id'];
			$this->db->where('schedule_id',$att_schdl);
			$query= $this->db->update($this->table,$post_data);
			
			if($query){

				$msg="Schedule Updated";
			} 
			else{

					$msg="Operation failed, Try again";
				}

			
			return $msg;
	}



	public function get_publicHoliday(){
		
		$query= $this->db->get('public_holiday');
		return $query->result();
	}

	public function addRequest(){
		$name=$this->input->post('holidayname');
		$year=$this->input->post('year');
		$type=$this->input->post('type');
		$date=$this->input->post('date');
		$entryId=$name.$year;

		$data=array('id'=>$entryId,
					 'holiday_name'=>$name,
					 'type'=>$type,
					 'holidaydate'=>$date,
					 'year'=>$year



	);


	$done=$this->db->insert('public_holiday',$data);

	if($done){

		$message="Holiday added Successfuly";
		$this->session->set_flashdata('msg', $message);

	} else{

		$message="Operation Failed";
		$this->session->set_flashdata('msg', $message);
			//$message="Operation Failed";
	}  

	
		
}

	public function	update_publicHoliday($post_data){

			$this->db->where('id',$post_data['id']);
			$query= $this->db->update('public_holiday',$post_data);
			
			if($query){

				$msg="Holiday Updated";
			} 
			else{

					$msg="Operation failed, Try again";
				}

			
			return $msg;
	}

	public function delete_publicHoliday($id){
			
			$this->db->where('rid',$id);
			$query= $this->db->delete('public_holiday');
			if($query){

				$this->session->set_flashdata('msg', 'Deletion Successful');
				
			}   
	}






	
}
