<?php 

Class Reason_Model extends CI_Model
{
    public function get_reasons()
    {
        $this->db->select('r_id AS reasonId,reason');
        $this->db->from('reasons');
        $this->db->order_by('reason','ASC');
        $query = $this->db->get();
        if($query->num_rows() > 0) {
            $response['status'] = 'SUCCESS';
            $response['message'] = 'Data loaded';
            $response['reasons'] = $query->result();

            return $response;
        } else {
            $response['status'] = 'FAILED';
            $response['message'] = 'No data';
            $response['reasons'] = null;

            return $response;
        }
    }
}