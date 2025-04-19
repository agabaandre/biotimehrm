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

    public function set_password_reset_token($userId, $token, $expiry) {
        
    }

    public function get_user_by_email($email)
    {
        $this->db->select('*');
        $this->db->where("email", $email);
        $query = $this->db->get('user');
        $user = $query->row();

        if ($user) {
            return $user;
        }

        return null;
    }

    public function save_reset_token($user_id, $token)
    {
        $data = array(
            'user_id' => $user_id,
            'token' => $token,
            'created_at' => date('Y-m-d H:i:s')
        );

        $this->db->insert('password_resets', $data);
    }


    // Get user by email or username
    public function get_user_by_username_or_email($username, $email) {
        $this->db->select('*');
        $this->db->where("email", $email);
        $this->db->or_where("username", $username);
        $query = $this->db->get('user');
        $user = $query->row();

        if ($user) {
            return $user;
        }

        return null;
    }

    // Create the user
    public function create_user($username, $email, $name, $password) {
        $data = [
            'username' => $username,
            'email' => $email,
            'name' => $name,
            'password' => $password
        ];

        return $this->db->insert('user', $data);   
    }
}