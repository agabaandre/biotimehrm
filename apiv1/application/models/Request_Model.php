<?php 

Class Request_Model extends CI_Model
{

    public function get_pending_requests($personId)
    {
            $this->db->select('*');
            $this->db->from("user");
            $this->db->where("ihris_pid",urldecode($personId));
            $query = $this->db->get();
            if($query->num_rows() > 0) {
                $data=$query->row();
                
                $department_id=$data->department_id;
    
                if(isset($department_id) && $department_id != null) {

                    $this->db->select('entry_id AS requestId,dateFrom,dateTo,reasons.reason,remarks, requests.ihris_pid AS personId, CONCAT_WS(" ",ihrisdata.surname, ihrisdata.firstname) AS personName');
                    $this->db->from("requests");
                    $this->db->where('requests.status', 'Pending');
                    $this->db->where("requests.department_id", $department_id);
                    $this->db->not_like('schedules.schedule',"leave","both");
                    $this->db->join("reasons", "reasons.r_id=requests.reason_id");
                    $this->db->join("ihrisdata", "ihrisdata.ihris_pid=requests.ihris_pid");
                    $this->db->join("schedules", "schedules.schedule_id=reasons.schedule_id");
                    $query2 = $this->db->get();
    
                    if($query2->num_rows() > 0) {
                        $response = array();
                        $response['status'] = 'SUCCESS';
                        $response['message'] = 'Data loaded';
                        $response['error'] = FALSE;
                        $response['pendingRequests'] = $query2->result();
    
                        return $response;
                    } else {
                        $response = array();
                        $response['status'] = 'SUCCESS';
                        $response['message'] = 'No pending requests';
                        $response['pendingRequests'] = array();
                        $response['error'] = FALSE;
    
                        return $response;
                    }
                } else {
                        $response = array();
                        $response['status'] = 'FAILED';
                        $response['message'] = 'Department ID not set';
                        $response['error'] = TRUE;
    
                        return $response;
                } 
            } else {
                $response['status'] = 'SUCCESS';
                $response['message'] = 'No results found';
                $response['error'] = FALSE;

                return $response;
            }
    }

    public function get_leave_requests($personId)
    {
            $this->db->select('*');
            $this->db->from("user");
            $this->db->where("ihris_pid",urldecode($personId));
            $query = $this->db->get();
            if($query->num_rows() > 0) {
                $data=$query->row();
                
                $department_id=$data->department_id;
    
                if(isset($department_id) && $department_id != null) {
                    $this->db->select('entry_id AS requestId,dateFrom,dateTo,reasons.reason,remarks, requests.ihris_pid AS personId, CONCAT_WS(" ",ihrisdata.surname, ihrisdata.firstname) AS personName');
                    $this->db->from("requests");
                    $this->db->where('requests.status', 'Pending');
                    $this->db->where("requests.department_id", $department_id);
                    $this->db->like('schedules.schedule',"leave","both");
                    $this->db->join("reasons", "reasons.r_id=requests.reason_id");
                    $this->db->join("ihrisdata", "ihrisdata.ihris_pid=requests.ihris_pid");
                    $this->db->join("schedules", "schedules.schedule_id=reasons.schedule_id");
                    $query2 = $this->db->get();
    
                    if($query2->num_rows() > 0) {
                        $response = array();
                        $response['status'] = 'SUCCESS';
                        $response['message'] = 'Data loaded';
                        $response['error'] = FALSE;
                        $response['leaveRequests'] = $query2->result();
    
                        return $response;
                    } else {
                        $response = array();
                        $response['status'] = 'SUCCESS';
                        $response['message'] = 'No pending leave requests';
                        $response['leaveRequests'] = array();
                        $response['error'] = FALSE;
    
                        return $response;
                    }
                } else {
                        $response = array();
                        $response['status'] = 'FAILED';
                        $response['message'] = 'Department ID not set';
                        $response['error'] = TRUE;
    
                        return $response;
                } 
            } else {
                $response['status'] = 'SUCCESS';
                $response['message'] = 'No results found';
                $response['error'] = FALSE;

                return $response;
            }
    }

    public function get_request_details($requestId)
    {
       //return urldecode($requestId);
       $this->db->select('entry_id AS requestId,dateFrom,dateTo,reasons.reason,remarks, CONCAT_WS(" ",ihrisdata.surname, ihrisdata.firstname) AS name,ihrisdata.department, requests.ihris_pid AS personId');
       $this->db->where("requests.entry_id",urldecode($requestId));
       $this->db->from('requests');
       $this->db->join("reasons", "reasons.r_id=requests.reason_id");
       $this->db->join("ihrisdata", "ihrisdata.ihris_pid=requests.ihris_pid");
       $query = $this->db->get();

       if($query->num_rows() === 1) {
            $row = $query->row();

            $response['status'] = 'SUCCESS';
            $response['message'] = 'Data loaded';
            $response['error'] = FALSE;
            $response['requestDetails'] = $row;

            return $response;
        } else {
            $response['status'] = 'NO_RESULTS';
            $response['message'] = 'No results found';
            $response['error'] = TRUE;

            return $response;
        }
    }

    public function get_approved_requests($personId)
    {
        $this->db->select('*');
        $this->db->from("user");
        $this->db->where("ihris_pid",urldecode($personId));
        $query = $this->db->get();
        if($query->num_rows() > 0) {
            $data=$query->row();
            
            $department_id=$data->department_id;

            if(isset($department_id) && $department_id != null) {
                $this->db->select('entry_id AS requestId,dateFrom,dateTo,reasons.reason,remarks, requests.ihris_pid AS personId, CONCAT_WS(" ",ihrisdata.surname, ihrisdata.firstname) AS personName');
                $this->db->from("requests");
                $this->db->where('status', 'Approved');
                $this->db->where("requests.department_id", $department_id);
                $this->db->join("reasons", "reasons.r_id=requests.reason_id");
                $this->db->join("ihrisdata", "ihrisdata.ihris_pid=requests.ihris_pid");
                $query2 = $this->db->get();

                if($query2->num_rows() > 0) {
                    $response = array();
                    $response['status'] = 'SUCCESS';
                    $response['message'] = 'Data loaded';
                    $response['error'] = FALSE;
                    $response['approvedRequests'] = $query2->result();

                    return $response;
                } else {
                    $response = array();
                    $response['status'] = 'SUCCESS';
                    $response['message'] = 'No Approved requests';
                    $response['approvedRequests'] = array();
                    $response['error'] = FALSE;

                    return $response;
                }
            } else {
                    $response = array();
                    $response['status'] = 'FAILED';
                    $response['message'] = 'Department ID not set';
                    $response['error'] = TRUE;

                    return $response;
            } 
        } else {
            $response['status'] = 'SUCCESS';
            $response['message'] = 'No results found';
            $response['error'] = FALSE;

            return $response;
        }
    }

    public function post_official_request($userdata)
    {
        $geodata=$userdata['location'];
        $today=date('Y-m-d');
        $datetime=date('Y-m-d H:i:s a');
        $entryid = $today . $userdata['personId'];
    
        $this->db->select('*');
        $this->db->from('workshops');
        $this->db->where('entry_id', $entryid);
        $query = $this->db->get();

        if($query->num_rows() > 0) {
            return NULL;
        } else {
            $data = array(
                'entry_id' => $entryid,
                'ihrispid' => $userdata['personId'],
                'request_id' => $userdata['requestId'],
                'date' =>   $datetime,
                'location' => $geodata,
                'url'=>NULL, 
                'street'=>NULL,
                'city'=>NULL,
                'region'=>NULL,
                'country'=>NULL,
                'status' => 'checked_in'
                
            );
   
            $this->db->insert('workshops', $data);
            
            if ($this->db->affected_rows() > 0) {
                    return false;
            } else {
                return true;
            }

        }
    }

    public function get_workshop_dates($personId)
    {

        // if (!ini_get('date.timezone')) {
        //         date_default_timezone_set('GMT');
        // } 

        $today = date('Y-m-d');

        $this->db->select('*');
        $this->db->from('workshops');
        $this->db->where('status', 'checked_in');
        $this->db->where('ihrispid', urldecode($personId));
        $this->db->where('date', $today);
        $query = $this->db->get();

        if($query->num_rows() > 0) {
            return array();
        } else {
            $this->db->select('dateFrom,dateTo,ihris_pid,entry_id');
            $this->db->from('requests');
            $this->db->where('ihris_pid', urldecode($personId));
            $this->db->where('dateFrom <=', $today);
            $this->db->where('dateTo >=', $today);
            $this->db->where('reasons.schedule_id', 23);
            $this->db->join("reasons", "reasons.r_id=requests.reason_id");
            $query = $this->db->get();
            return $query->row();
        }
    }

    public function post_request($postData)
    {
        $response = array();

        $data = array(
            'entry_id' => md5($postData['dateFrom'] . $postData['dateTo'] . $postData['personId'] .  $postData['reasonId']),
            'reason_id' => $postData['reasonId'],
            'ihris_pid' => $postData['personId'],
            'dateFrom' => $postData['dateFrom'],
            'dateTo' => $postData['dateTo'],
            'remarks' => $postData['remarks'],
            'facility_id' => $postData['facilityId'],
            'department_id' => $postData['departmentId'],
            'status'=> 'Pending'
        );

        $this->db->select('*');
        $this->db->from('requests');
        $this->db->where('entry_id', $data['entry_id']);
        $this->db->where('status', 'Pending');
        $query = $this->db->get();
        if($query->num_rows() > 0) {
            $response['status'] = 'FAILED';
            $response['message'] = 'You have a pending request within matching criteria';
            $response['error'] = TRUE;
        } else {
            $this->db->insert('requests', $data);
            if($this->db->affected_rows() > 0) {
                $response['status'] = 'SUCCESS';
                $response['message'] = 'Request Submitted';
                $response['error'] = FALSE;
            } else {
                $response['status'] = 'FAILED';
                $response['message'] = 'Request submission failed';
                $response['error'] = TRUE;
            }
        }

        return $response;
    }

    public function approveRequest($entryid){
		
		$data=array("status"=>"Approved");
        $entryid=urldecode($entryid);
		$this->db->where('entry_id',$entryid);
		$query= $this->db->update('requests',$data);
		if($this->db->affected_rows() > 0){

			return TRUE;
        }
        else{
            return FALSE;
        }
    }
    
    public function rejectRequest($entryid) {
		
        $data=array("status"=>"Declined");
        $entryid=urldecode($entryid);
        $this->db->where('entry_id',$entryid);
        $query= $this->db->update('requests',$data);
        if($this->db->affected_rows() > 0){
            return TRUE;
        } else {
            return FALSE;
        }   
    }

    public function register_device($userdata) {

        $response = array();

        $this->db->insert('checkin_devices', $userdata);
        if($this->db->affected_rows() > 0) {
    
            $response['status'] = 'SUCCESS';
            $response['message'] = 'Device Registered';
            $response['error'] = FALSE;
        } else {
            $response['status'] = 'FAILED';
            $response['message'] = 'Device registration failed';
            $response['error'] = TRUE;
        }

        return $response;
    }
}