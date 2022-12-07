<?php

    /******************************************
    *      Codeigniter 3 Simple Login         *
    *   Developer  :  rudiliucs1@gmail.com    *
    *        Copyright © 2017 Rudi Liu        *
    *******************************************/

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Admin_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }


  function list_scheduled(){
      
      $year=$this->input->post('year');
      $month=$this->input->post('month');
      $district=$this->input->post('district');
      
      $date=$year."-".$month;
      
      if($district=="all"){
          
          $query=$this->db->query("select distinct(ihrisdata.facility),ihrisdata.district from duty_rosta,ihrisdata where duty_rosta.facility_id IN(select duty_rosta.facility_id from duty_rosta where duty_date like '$date%' ) and duty_rosta.facility_id=ihrisdata.facility_id" );
          
      }
      
      else{
      
        $query=$this->db->query("select distinct(ihrisdata.facility),ihrisdata.district from duty_rosta,ihrisdata where duty_rosta.duty_date like '$date%' and ihrisdata.district_id='$district' and duty_rosta.facility_id=ihrisdata.facility_id");
        
      }
      
        return $query->result_array();
    }


    function get_user_list(){
        $this->db->select('*');
        $this->db->from('user');
        //$this->db->where('status', 1);
        $query=$this->db->get();
        return $query->result_array();
    }
    
    
    
    
        function get_vars(){
        $this->db->select('*');
        $this->db->from('variables');
        $query=$this->db->get();
        return $query->result_array();
    }
    
    
         function save_config($postData){
             
        $this->db->select('*');
        $this->db->from('variables');
        $query=$this->db->get();
        
        $vars= $query->result_array();
        
        $changes="<font color='red'>";
        
        foreach($vars as $var){
            
        
           $row=$var['rowid'];
           $value=$postData[$row];
           
          
          $this->db->query("UPDATE `variables` SET `content` = '$value' WHERE `variables`.`rowid`='$row'");
          
          if(!($var['content']==$value)){
          
          $changes .= $var['content']." to ".$value. " | ";
          
        }
            
       }
       
       $changes.="</font>";
        
           
            $module = "System Configuration";
            $activity = "Made Changes to the system variables, changed: ".$changes;
            $this->insert_log($activity, $module);
      
        
        return "Changes Saved";
     
      
        
    }
    
    
    

    function get_user_by_id($userID){
        $this->db->select('*');
        $this->db->from('user');
        $this->db->where('user_id', $userID);
        $query=$this->db->get();
        return $query->result_array();
    }

    function validate_email($postData){
        $this->db->where('email', $postData['email']);
        $this->db->where('status', 1);
        $this->db->from('user');
        $query=$this->db->get();

        if ($query->num_rows() == 0)
            return true;
        else
            return false;
    }

    function insert_user($postData,$uid){

       $validate = $this->validate_email($postData);
       
 if($validate){
            //$password = $this->generate_password();
            
            $password=$postData['password'];
            
            $data = array(
                'email' => $postData['email'],
                'name' => $postData['name'],
                'username' => $postData['username'],
                'role' => $postData['role'],
                'password' => md5($postData['password']),
                'facility_id' => $postData['facility'],
                'district_id' =>$postData['district_id'],
                'created_at' => date('Y\-m\-d\ H:i:s A'),
                'auth_id'=>$uid
            );
            $this->db->insert('user', $data);


            $module = "User Management";
            $activity = "add new user ".$postData['email'];
            $this->insert_log($activity, $module);
            
            return array('status' => 'success', 'message' => '');

        }else{
            return array('status' => 'exist', 'message' => '');
        }

    }

    function update_user_details($postData){

        $oldData = $this->get_user_by_id($postData['id']);

        if($oldData[0]['email'] == $postData['email'])
            $validate = true;
        else
            $validate = $this->validate_email($postData);

        if($validate){
            $data = array(
                'email' => $postData['email'],
                'name' => $postData['name'],
                'role' => $postData['role'],
                'facility_id' => $postData['facility'],
                'username' => $postData['username']
            );
            
            $this->db->where('user_id', $postData['id']);
            $this->db->update('user', $data);

            $record = "(".$oldData[0]['email']." to ".$postData['email'].", ".$oldData[0]['name']." to ".$postData['name'].",".$oldData[0]['role']." to ".$postData['role'].")";

            $module = "User Management";
            $activity = "update user ".$oldData[0]['email']."`s details ".$record;
            $this->insert_log($activity, $module);
            return array('status' => 'success', 'message' => $record);
        }else{
            return array('status' => 'fail', 'message' => '');
        }

    }


    function deactivate_user($username,$id){

        $data = array(
            'status' => 0,
        );

        $this->db->where('user_id', $id);
        $this->db->update('user', $data);

        $module = "User Management";

        $activity = "Block user ".$username;

        $this->insert_log($activity, $module);

        return array('status' => 'success', 'message' => '');

    }



    function activate_user($username,$id){

        $data = array(
            'status' => 1,
        );

        $this->db->where('user_id', $id);
        $this->db->update('user', $data);

        $module = "User Management";

        $activity = "Activate user ".$username;

        $this->insert_log($activity, $module);

        return array('status' => 'success', 'message' => '');

    }


    function reset_user_password($email,$id){

        $password = $this->input->post();
        $data = array(
            'password' => md5($password),
        );
        $this->db->where('user_id', $id);
        $this->db->update('user', $data);

        return array('status' => 'success', 'message' => '');

    }

    function generate_password(){
        $chars = "abcdefghjkmnopqrstuvwxyzABCDEFGHJKMNOPQRSTUVWXYZ023456789!@#$%^&*()_=";
        $password = substr( str_shuffle( $chars ), 0, 10 );

        return $password;
    }

    function insert_log($activity, $module){
        
        $id = $this->session->userdata('user_id');
        

        $data = array(
            'fk_user_id' => $id,
            'activity' => $activity,
            'module' => $module,
            'created_at' => date('Y\-m\-d\ H:i:s A')
        );
        $this->db->insert('activity_log', $data);
    }



   
   
    
    
    public function get_logs(){
        
        $this->db->join("user","user.user_id=activity_log.fk_user_id");
        $query=$this->db->get("activity_log");
        
        return $query->result();
        
    }
    
    public function clearLogs(){
        $this->db->empty_table('activity_log');
        
        $module = "Activity Logs";
           $activity = "Cleared all activity Logs";
        $this->insert_log($activity, $module);
            
    }

    public function addGroup($postdata){

           $this->db->insert('user_groups',$postdata);

           return true;
   
    }

}

/* End of file */
