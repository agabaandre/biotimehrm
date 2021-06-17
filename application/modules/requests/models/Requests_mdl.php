<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Requests_mdl extends CI_Model {

	
	public function __Construct(){

		parent::__Construct();
		$this->table="requests";
		
		$this->facility=$this->session->userdata['facility'];
        $this->department=$this->session->userdata['department_id'];
        $this->division=$this->session->userdata['division'];
        $this->unit=$this->session->userdata['unit'];
        $this->user=$this->session->get_userdata();

	}


public function saveRequest($sendRequest){
	date_default_timezone_set('Africa/Kampala');
	$dateFrom=$sendRequest['dateFrom'];
	$dateTo=$sendRequest['dateTo'];
	$dateFrom=date('Y-m-d', strtotime($dateFrom));
	$dateTo=date('Y-m-d', strtotime($dateTo));

	//$dateFrom=date('Y-m-d', strtotime($dateFrom));
	//$dateTo=date('Y-m-d', strtotime($dateTo));
	$today=date('Y-m-d');

	if($dateFrom<$today || $dateTo<$today) {
	 return "<font class='alert text-danger'><i class='fa fa-warning'></i> You can not make a request for a date that has past. Please contact the Admin.</font>";
	}
  if ($this->user['ihris_pid']==""){
	return "<font class='alert text-danger'><i class='fa fa-warning'></i> Your employee file is not in the system. Please contact the Admin.</font>";
   }

    
	$entry_id=md5($this->user['ihris_pid'].date('Y-m-d', strtotime($dateFrom)).date('Y-m-d', strtotime($dateTo)).$sendRequest['reason_id']);

	$checkrequest=$this->validateRequest($entry_id);
    
	if($checkrequest>0){

		return "<font class='alert text-danger'><i class='fa fa-warning'></i> Note that you have pending(s) request on same date, request not submitted.</font>";
	}
    if(empty($dateTo)){
		$nextday = date('Y-m-d',strtotime('+1 day', $dateFrom));
		$sendRequest['dateTo']=$nextday;
	}
	//md5($person,datefrom,dateto,reason,)
	$data=array(
		'entry_id'=>$entry_id,
		'reason_id'=>$sendRequest['reason_id'],
		'ihris_pid'=>$this->user['ihris_pid'],
		'date'=>date('Y-m-d H:i:s'),
		'dateFrom'=>$dateFrom,
		'dateTo'=>$dateTo,
		'remarks'=>$sendRequest['remarks'],
		'facility_id'=>$this->user['facility'],
		'attachment'=>$sendRequest['attachment'],
		'department_id'=>$this->department
	    );

	$done=$this->db->replace('requests',$data);

	if($done){

		$message="<font class='alert text-success'><i class='fa fa-check'></i> Request Submitted</font>";

	    } else{

		    //$message=$data['purpose'];
			$message="Operation Failed";
	    }  

	return $message;
		
}

public function validateRequest($entry_id){

	$this->db->where('entry_id',$entry_id);
	$qry=$this->db->get('requests');

	$count=$qry->num_rows();

	return $count;

}

	
	public function getTable()
	{
		
		$table="requests";
		return $table;
	}


	public function getAll()
	{
		
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

		
		$this->db->join("ihrisdata", "ihrisdata.ihris_pid=requests.ihris_pid");
		$this->db->join("reasons", "reasons.r_id=requests.reason_id");
		$query=$this->db->get($table);
		
		return $query->result();
	}


	public function getleavePending($start,$limit)
	{
		$table=$this->table;
        
		$query=$this->db->query("select * from requests join ihrisdata on requests.ihris_pid=ihrisdata.ihris_pid join reasons on requests.reason_id=reasons.r_id and status='pending' and reasons.schedule_id in(select schedule_id from schedules where schedule like '%leave%') LIMIT $limit, $start");
	
	  
		return $query->result();
		
	}
	public function countleavePending()
	{
		$table=$this->table;
        
		$query=$this->db->query("select * from requests join ihrisdata on requests.ihris_pid=ihrisdata.ihris_pid join reasons on requests.reason_id=reasons.r_id and status='pending' and reasons.schedule_id in(select schedule_id from schedules where schedule like '%leave%')");
	
	  
		return $query->num_rows();
		
	}
	public function getPending($user_id,$userlimit,$status,$entry_id)
	{
		$table=$this->table;

		if(($this->user['role']!='sadmin')||($this->user['role']!='Human_Resource')||($this->user['role']!='Top_Manager')){
			$this->db->where('requests.department_id',$this->department);
		}
		if($userlimit!=NULL){
			$this->db->where("requests.ihris_pid",$user_id);
		}
		if($status=='Pending'){
			$this->db->where("requests.status",'Pending');
		}
		if($entry_id!=NULL){
			$this->db->where("requests.entry_id",$entry_id);
		}
		$this->db->join("ihrisdata", "ihrisdata.ihris_pid=requests.ihris_pid");
		$this->db->join("reasons", "reasons.r_id=requests.reason_id");
		$query=$this->db->get($table);
		
		return $query->result();
		
	}
	public function countPending()
	{
		$table=$this->table;
		$this->db->where("status","Pending");

		if($this->user['role']!='sadmin'){
			$this->db->where('requests.department_id',$this->department);
		}
		//$this->db->join("ihrisdata", "ihrisdata.ihris_pid=requests.ihris_pid");
		$query=$this->db->get($table);
		
		return $query->num_rows();
	}


	public function getById($id)
	{
		$table=$this->table;
		$this->db->where('entry_id',$id);
		$query=$this->db->get($table);
		
		return $query->row();
	}




public function acceptRequest($entryid){
	date_default_timezone_set('Africa/Kampala');
	$person=$this->session->userdata['ihris_pid'];
	$data=array("status"=>"Approved","approver"=>$person,'responsedate'=>date('Y-m-d, H:i:s'));
		$this->db->where('entry_id',$entryid);
		$query= $this->db->update('requests',$data);
		if($query){
			

			return "Request Updated";
		}   
}

public function saveIntoActuals($data){

	$query= $this->db->insert('actuals',$data);
	if($query){

		return "Request Updated";
	}   
	return false;
}


public function rejectRequest($entryid){
	date_default_timezone_set('Africa/Kampala');
	$person=$this->session->userdata['ihris_pid'];
		
	$data=array("status"=>"Rejected","approver"=>$person,'responsedate'=>date('Y-m-d, H:i:s'));
	$this->db->where('entry_id',$entryid);
	$query= $this->db->update('requests',$data);
	if($query){

		return "Request Updated";
	}   
}

public function updateRequest($data,$entry_id){
	date_default_timezone_set('Africa/Kampala');
	$this->db->where('entry_id',$entry_id);

	$query= $this->db->update('requests',$data);
	if($query){

		return "Request Updated";
	}   
}


	
			
    Public function request_report(){	

		$data=$this->input->post('requests');
		
		$s=$this->db->get($this->table, $data);
		$rows=$this->db->affected_rows();
		$rows=$s->result();
		
		return $rows;
	}
	public function cancelRequest($requestId){

	$data=array("status"=>"Cancelled");
	$this->db->where('entry_id',$requestId);
	$query= $this->db->update('requests',$data);

	return 'Success';
  }




}
