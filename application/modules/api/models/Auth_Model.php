<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth_model extends CI_Model
{
    public function validate_login($userdata)
    {
        $this->load->database();

        $username = $userdata['username'];
        $password = $userdata['password'];
        $this->db->select('user.facility_id, user.username, user.role,facility');
        $this->db->from('user');
        $this->db->join('facilities', 'facilities.facility_id = user.facility_id');
        $this->db->where('username', $username);
        $this->db->where('password', md5($password));
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->row();
        } else {
            return null;
        }
    }
}
