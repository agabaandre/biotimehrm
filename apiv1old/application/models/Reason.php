<?php 

class Reason extends CI_Model {

    public function get_reasons()
    {
        $this->db->select('r_id,reason');
        $this->db->from('reasons');
        $query = $this->db->get();
        return $query->result();
    }
}