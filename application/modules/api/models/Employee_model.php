<?php defined('BASEPATH') OR exit('No direct script access allowed');

Class Employee_model extends CI_Model
{
    public function get_employees($facility,$ihris_pid)
    {
        $this->db->select('ihris_pid, firstname, surname, job', 'card_number','nin');
        $this->db->where('ihrisdata.facility_id', urldecode($facility));
        if(!empty($ihris_pid)):
        $this->db->where('ihrisdata.ihris_pid', urldecode(ihris_pid));
        endif;
        $this->db->order_by('ihris_pid', 'asc');
        $this->db->from('ihrisdata');
        $query = $this->db->get();
        if($query->num_rows() > 0) {
            return $query->result();
        } else {
            return array();
        }
    }

    public function clock_user($data) {

        $response = array();
       
        $mydate= $data['clockin_time'];
        $date = date("Y-m-d", strtotime($mydate));

        $entry_id = $date. 'person|' . $data['userId'];

        if(!empty($data['userId'])) {
                $data = array(
                    'entry_id' => $entry_id,
                    'ihris_pid' => "person|".$data['userId'],
                    'facility_id' => $data['facilityId'],
                    'time_in' => $data['clockin_time'],
                    'date' => $date,
                    'location' => $data['location'],
                    'source' => $data['source']
                );

                $query=$this->db->query("SELECT entry_id from clk_log where entry_id='$entry_id'");
                $srows=$query->num_rows();
                if($srows>0){
                $entry_id=$query->result();
                
                foreach($entry_id as $entry)
                {
                    $this->db->set('time_out', "$mydate");
                    $this->db->where("time_in <","$mydate");
                    $this->db->where('entry_id', "$entry->entry_id");
                    $query=$this->db->update('clk_log');

   
                }
               }
               else{

                $this->db->insert('clk_log', $data);
               }


               
                

                if($this->db->affected_rows() > 0) {
                    $response['status'] = 'SUCCESS';
                    $response['message'] = 'User clocked in';
                    $response['error'] = null;
                } else {
                    $response['status'] = 'FAILED';
                    $response['message'] = 'User not clocked in';
                    $response['error'] = null;
                }
            }


      } 
        return $response;
    }

    public function enroll_user($data) {

        $response = array();
        $fingerprint = isset($data['fingerprint']) ? $data['fingerprint'] : NULL;
        $pincode = isset($data['pin']) ? $data['pin'] : NULL;

        $entry_id = $data['facilityId'] . 'person|' . $data['userId'].'b'.$fingerprint.'|fpt';
        $facilityId = $data['facilityId'];
        $queryc=$this->db->query("delete from fingerprints where facilityId='$facilityId'  and fingerprint='$fingerprint'"); 


            if($queryc){
            $this->db->insert('fingerprints', array(
                'entry_id' => $entry_id,
                'fingerprint' => $fingerprint,
                'pin' => $pincode,
                'location' => $data['location'],
                'ihris_pid' => 'person|' . $data['userId'],
                'facilityId' => $data['facilityId'],
                'source' => $data['source']
            ));

            if($this->db->affected_rows() > 0) {
                $response['status'] = 'SUCCESS';
                $response['message'] = 'User record inserted';
                $response['error'] = null;
            } else {
                $response['status'] = 'FAILED';
                $response['message'] = 'User record not inserted';
                $response['error'] = null;
            }
        }

        return $response;
    }

   

}
