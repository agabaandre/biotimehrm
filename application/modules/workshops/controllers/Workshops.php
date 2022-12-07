<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Workshops extends MX_Controller {

	
	public function __Construct(){

		parent::__Construct();

		$this->load->model('workshops_mdl','workshopsHandler');
		$this->user=$this->session->get_userdata();
		$this->department=$this->user['department_id'];
		$this->ihris_pid=$this->user['ihris_pid'];

	}
	public function checkins(){

		//$data['requests']=$this->requests;
		$data['title']='Official Requests';
		$data['view']='workshops';
		$data['module']="workshops";
		echo Modules::run('templates/main', $data);

	}
	public function linkedDevices(){
		$data['title']='Devices';
		$data['view']='linked_devices';
		$data['module']="workshops";
		echo Modules::run('templates/main', $data);
		
	}
	public function getDevices($id=NULL){
		if(empty($id)){
			$checkins=$this->workshopsHandler->getDevices();
			}
			else{
			$checkins=$this->workshopsHandler->getDevices($id);	
			}
	    return $checkins;	
	}
	public function unlinkDevices($person){
		$person=urldecode($person);
		

	 $this->db->where('checkin_devices.personId',$person);
	 $this->db->delete('checkin_devices');
	 $this->session->set_flashdata('msg', 'Unlink Successful');
	 
	 redirect("workshops/linkedDevices");

	}


	public function get_checkins($id=NULL){
		if(empty($id)){
		$checkins=$this->workshopsHandler->getWorkshop();
		}
		else{
		$checkins=$this->workshopsHandler->getWorkshop($id);	
		}
		return $checkins;
	}
	public function getRequests($request_id){
	
		
		if(!empty($request_id)){
		
		$requestdata=$this->workshopsHandler->getRequests($request_id);	
		}
		
		foreach($requestdata as $request){
		$data="Request Date: ".$request->date."<br>Requested Dates: ".$request->dateFrom." - ".$request->dateTo."<br>Remarks: ".$request->remarks ."<br>Status: ".$request->status;
		}

		return $data;

	}
	public function updatemapData($entry_id,$url,$street,$city,$region,$country){
		

		$result=$this->workshopsHandler->updatemapData($entry_id,$url,$street,$city,$region,$country);

		return $result;
	}
	

	


}
