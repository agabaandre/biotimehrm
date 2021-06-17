<?php


if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Authentication extends MX_Controller {

    public function __Construct() {
        parent::__Construct();
        $this->load->model("authentication_model");
        
        $this->uid=$this->session->userdata['uid'];

    }

    public function index() {
    
        if($this->session->userdata('logged_in')) {
            redirect(base_url("attendance"));
        }else {
            $data = array('alert' => false);
            $this->load->view('sign-in',$data);
        }
    }
	

   

    public function login(){
		
        $postData = $this->input->post();
        $validate = $this->authentication_model->validate_login($postData);

        if ($validate){
            $newdata = array(
                'email'     => $validate[0]->email,
                'role' => $validate[0]->role,
                'user_id' => $validate[0]->user_id,
                'facility'=>$validate[0]->facility_id,
                'names'=>$validate[0]->names,
                'uid'=>$validate[0]->auth_id,
                'logged_in' => TRUE,
                'passo'=>$validate[0]->password,
                'district'=>$validate[0]->district,
                'pass_changed'=>$validate[0]->password_state
              
            );

            $this->session->set_userdata($newdata);
            
            $this->aauth->login($newdata['email'], $newdata['passo'], true);
            
            if($newdata['role']=='District-Officer'){
                
                redirect(base_url("admin/select")); 
                
            }
            else{
                
                 redirect(base_url("attendance")); 
            }
            
           
        }
        
        else{
            
			$data = array('alert' => true);
			
            $this->load->view('sign-in',$data);
        }
     
    }
	
	

    function change_password(){
        $this->ajax_checking();

        $postData = $this->input->post();
        
        $postData['changed']=1;
        
        $update = $this->authentication_model->change_password($postData);
        
        if($update['status'] == 'success')
        {
        
            $this->session->set_flashdata('success', 'Your password has been successfully changed!');
            
            $this->aauth->reset_password($this->uid,md5($postData['new']));//reset aaauth pass

        echo "OK";
        
        }
        
        else {
            echo "Failed";
        }
    }
    
    

    public function logout() {
        
        $this->aauth->logout();
        $this->session->sess_destroy();
        redirect(base_url());
    }


}

/* End of file */
