<?php

class Request extends CI_Model
{

    public function get_requests()
    {
        $this->db->join("ihrisdata","ihrisdata.ihris_pid=requests.ihris_pid");
        $this->db->join("reasons", "reasons.r_id=requests.reason_id");
        $query = $this->db->get("requests");
        return $query->result();
    }

    public function get_request($personId = NULL)
    {
        
        if($personId != null) {
            $this->db->select('entry_id,dateFrom,dateTo,status,reasons.reason');
            $this->db->from("requests");
            $this->db->join("ihrisdata", "ihrisdata.ihris_pid=requests.ihris_pid");
            $this->db->join("reasons", "reasons.r_id=requests.reason_id");
            $this->db->order_by("dateFrom", 'desc');
            $this->db->limit('10');
            $this->db->where("requests.ihris_pid", urldecode($personId));
            $query = $this->db->get();
            return $query->result();
        } else {
            return array();
        }


        
    }

    public function get_pending_requests($personId = null)
    {
        // if(!empty($personId)){
        //     $this->db->select('department_id');
        //     $this->db->from("ihrisdata");
        //     $this->db->where("ihris_pid",$personId);
        //     $query = $this->db->get();
        //     $data=$query->row();
        //     $department_id=$data['department_id'];
           
        // $this->db->select('entry_id,dateFrom,dateTo,status,reasons.reason');
        // $this->db->from("requests");
        // $this->db->where("status",'Pending');
        // $this->db->join("ihrisdata", "ihrisdata.department_id=$department_id");
        // $this->db->join("reasons", "reasons.r_id=requests.reason_id");
        // $query = $this->db->get();

        // return $query->result();
        
        if(!empty($personId)) {
            $this->db->select('department_id');
            $this->db->from("ihrisdata");
            $this->db->where("ihris_pid",urldecode($personId));
            $query = $this->db->get();
            $data=$query->row();
            $department_id=$data->department_id;

            if($department_id) {
                $this->db->select('entry_id,dateFrom,dateTo,status,reasons.reason');
                $this->db->from("requests");
                $this->db->where('status', 'Pending');
                $this->db->where("department_id", $department_id);

                 $this->db->join("reasons", "reasons.r_id=requests.reason_id");
                $query2 = $this->db->get();

                return $query2->result();
            } else {
                return array();
            }
        } else {
            return array();
        }
          
    }

    public function post_request($postData)
    {

        $entry_id = $postData['dateFrom'] . $postData['personId'];
        if(empty($postData['dateTo'])){
            $dateto = date('Y-m-d',strtotime('+1 day', $postData['dateFrom']));
            
        }
        else{
            $dateto=$postData['dateTo'];
        }

        $data = array(
            'entry_id' => $entry_id,
            'reason_id' => $postData['reasonId'],
            'ihris_pid' => $postData['personId'],
            'dateFrom' => $postData['dateFrom'],
            'dateTo' => $dateto,
            'remarks' => $postData['remarks'],
            'facility_id' => $postData['facilityId'],
            'department_id' => $postData['departmentId']
        );
        

        $this->db->select('*');
        $this->db->from('requests');
        $this->db->where('entry_id', $entry_id);
        $query = $this->db->get();
        if($query->num_rows() > 0) {
            return FALSE;
        } else {
            $this->db->insert('requests', $data);
            if($this->db->affected_rows() > 0) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
    }
public function approveRequest($entryid){
		
		$data=array("status"=>"Granted");
        $entryid=urldecode($entryid);
		$this->db->where('entry_id',$entryid);
		$query= $this->db->update('requests',$data);
		if($query){

			return TRUE;
        }
        else{
            return FALSE;
        }
    }
public function rejectRequest($entryid){
		
        $data=array("status"=>"Rejected");
        $entryid=urldecode($entryid);
        $this->db->where('entry_id',$entryid);
        $query= $this->db->update('requests',$data);
        if($query){

            return TRUE;
        }
        else{
            return FALSE;
        }   
    }

}
