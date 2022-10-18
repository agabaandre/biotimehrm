<?php defined('BASEPATH') or exit('No direct script access allowed');

class Employee_model extends CI_Model
{
    // Get Staff List 
    public function get_staff_list($facilityId)
    {
        // Get staff list from fingerprints table
        $this->db->select('*');
        $this->db->from('ihrisdata');
        $this->db->where('facility_id', $facilityId);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return null;
        }
    }

    public function enroll($data)
    {
        $this->db->insert('fingerprints', $data);
        return $this->db->insert_id();
    }

    public function clock($data)
    {
        $this->db->insert('clk_log', $data);
        return $this->db->insert_id();
    }
}
