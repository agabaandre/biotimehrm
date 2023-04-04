<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Apiauth_model extends CI_Model
{
    // Constructor
    public function __construct()
    {
        parent::__construct();
    }

    // Login
    public function login($username, $password)
    {
        $this->db->select('user_id, email, username, name, role, status, ihris_pid, facility_id, facility, department_id, department, district_id, district');
        $this->db->where("username", $username);
        $this->db->or_where("email", $username);
        $this->db->where("status", 1);
        $this->db->join('user_groups', 'user_groups.group_id=user.role');
        $this->db->from("user");
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            // Validate password
            $person = $query->row();
            if($this->argonhash->check($password, $person->password)) {
                return $person;
            }
            return null;
        } else {
            return null;
        }
    }
    // Get Staff List
    public function get_staff_list($facilityId)
    {
        // Get staff list from fingerprints table
        $this->db->select('*');
        $this->db->from('ihrisdata');
    }
}