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
        $this->db->select('*');
        $this->db->where("username", $username);
        $this->db->or_where("email", $username);
        $this->db->join('user_groups', 'user_groups.group_id=user.role');
        $query = $this->db->get('user');
        $user = $query->row();

        if ($user && $this->argonhash->check($password, $user->password)) {
            unset($user->password);
            return $user;
        }

        return null;
    }
    // Get Staff List
    public function get_staff_list($facilityId)
    {
        // Get staff list from fingerprints table
        $this->db->select('*');
        $this->db->from('ihrisdata');
    }
}
