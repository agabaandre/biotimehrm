<?php

   

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Authentication_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }


    function validate_login($postData){
        
        $this->db->select('user.name as names,user.username,user.password,user.status,user.facility_id,user.district_id as district,user.user_id,user.role,user.auth_id, user.changed as password_state');
        $this->db->where('user.username', $postData['username']);
        $this->db->where('user.password', md5($postData['password']));
        $this->db->where('user.status', 1);
        $this->db->from('user');
        $query=$this->db->get();
       
        if ($query->num_rows() == 0)
            return false;
        else
            return $query->result();
    }



    function change_password($postData){
        $this->load->model('admin_model');
        $validate = false;

        $oldData = $this->admin_model->get_user_by_id($this->session->userdata('user_id'));

        if($oldData[0]['password'] == md5($postData['old']))
           
            
            $validate = true;

        if($validate or $postData['changed']=1){
            
            $_SESSION['pass_changed']=date("Y-m-d");
            
            $data = array(
                'password' => md5($postData['new']),
                'changed' =>date("Y-m-d")
            );
            $this->db->where('user_id', $this->session->userdata('user_id'));
            $this->db->update('user', $data);
            
            

            $module = "Change Password";
            $activity = "change its own password";
            $this->admin_model->insert_log($activity, $module);
            return array('status' => 'success', 'message' => '');
        }else{
            return array('status' => 'invalid', 'message' => '');
        }

    }
}

/* End of file  */
