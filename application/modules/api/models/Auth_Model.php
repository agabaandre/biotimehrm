<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth_model extends CI_Model
{
    // Constructor
    public function __construct()
    {
        parent::__construct();
        $this->load->library('Auth_Token', 'auth_token');
    }
    
    // Login
    public function login($username, $password) {
        $this->db->select('user_id, email, username, name, role, status, ihris_pid, facility_id, facility, department_id, department, district_id, district');
        $this->db->where("username", $username);
        $this->db->where("password", md5($password));
        $this->db->from("user");
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->row();
        } else {
            return null;
        }
    } 
    public function generateToken($data) {
        $data_token['user_id'] = $data['user_id'];
        $data_token['username'] = $data['username'];
        $data_token['name'] = $data['name'];
        $data_token['role'] = $data['role'];
        $user_token = $this->auth_token->generateToken($data_token);
        return $user_token;
    } 

    public function validateToken($token) {
        
        $is_valid_token = $this->auth_token->validateToken($token);
        if (!$is_valid_token) {
            return false;
        } else {
            return true;
        }
    }

    // Get Staff List
    public function get_staff_list() {
        // Get staff list from fingerprints table
        $this->db->select('*');
        $this->db->from('ihrisdata');

    }

}