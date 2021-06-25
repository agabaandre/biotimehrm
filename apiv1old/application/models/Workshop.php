<?php


class Workshop extends CI_Model
{

    public function post_workshop_data($userdata)
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
                $query=$this->db->query("INSERT INTO actuals (date, entry_id, facility_id, ihris_pid, schedule_id)
		       SELECT clk_log.date, clk_log.entry_id, clk_log.facility_id, clk_log.ihris_pid, schedules.schedule_id
		       FROM clk_log,schedules where clk_log.entry_id NOT IN (select entry_id from actuals) and schedules.schedule_id=22");
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
}
