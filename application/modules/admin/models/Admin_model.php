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
    
    /**
     * Get total count of logs
     */
    public function get_logs_count($search = '', $user_filter = '', $module_filter = '', $date_from = '', $date_to = '')
    {
        $this->db->select('COUNT(*) as total');
        $this->db->from('activity_log');
        $this->db->join('user', 'user.user_id = activity_log.fk_user_id', 'LEFT');
        
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('activity_log.activity', $search);
            $this->db->or_like('activity_log.module', $search);
            $this->db->or_like('activity_log.route', $search);
            $this->db->or_like('user.username', $search);
            $this->db->or_like('user.email', $search);
            $this->db->group_end();
        }
        
        if (!empty($user_filter)) {
            $this->db->where('user.username', $user_filter);
        }
        
        if (!empty($module_filter)) {
            $this->db->where('activity_log.module', $module_filter);
        }
        
        if (!empty($date_from)) {
            $this->db->where('DATE(activity_log.created_at) >=', $date_from);
        }
        
        if (!empty($date_to)) {
            $this->db->where('DATE(activity_log.created_at) <=', $date_to);
        }
        
        $query = $this->db->get();
        $result = $query->row();
        
        return $result ? $result->total : 0;
    }
    
    /**
     * Get logs for AJAX DataTables with pagination
     */
    public function get_logs_ajax($start = 0, $length = 10, $search = '', $order_column = 0, $order_dir = 'asc', $user_filter = '', $module_filter = '', $date_from = '', $date_to = '')
    {
        $this->db->select('activity_log.*, user.username, user.email');
        $this->db->from('activity_log');
        $this->db->join('user', 'user.user_id = activity_log.fk_user_id', 'LEFT');
        
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('activity_log.activity', $search);
            $this->db->or_like('activity_log.module', $search);
            $this->db->or_like('activity_log.route', $search);
            $this->db->or_like('user.username', $search);
            $this->db->or_like('user.email', $search);
            $this->db->group_end();
        }
        
        if (!empty($user_filter)) {
            $this->db->where('user.username', $user_filter);
        }
        
        if (!empty($module_filter)) {
            $this->db->where('activity_log.module', $module_filter);
        }
        
        if (!empty($date_from)) {
            $this->db->where('DATE(activity_log.created_at) >=', $date_from);
        }
        
        if (!empty($date_to)) {
            $this->db->where('DATE(activity_log.created_at) <=', $date_to);
        }
        
        // Apply ordering
        $columns = ['activity_log.log_id', 'activity_log.activity', 'activity_log.created_at', 'user.username', 'activity_log.module'];
        if (isset($columns[$order_column])) {
            $this->db->order_by($columns[$order_column], $order_dir);
        } else {
            $this->db->order_by('activity_log.created_at', 'desc');
        }
        
        // Apply pagination
        $this->db->limit($length, $start);
        
        $query = $this->db->get();
        $result = $query->result();
        
        // Format data for DataTables
        $formatted_data = [];
        foreach ($result as $row) {
            $formatted_data[] = [
                'log_id' => $row->log_id,
                'activity' => $row->activity,
                'module' => $row->module ?: 'N/A',
                'route' => $row->route ?: 'N/A',
                'ip_address' => $row->ip_address ?: 'N/A',
                'created_at' => $row->created_at,
                'username' => $row->username ?: 'Unknown User',
                'email' => $row->email ?: 'N/A'
            ];
        }
        
        return $formatted_data;
    }
    
    public function clearLogs(){
        $this->db->empty_table('activity_log');
        
        $module = "Activity Logs";
           $activity = "Cleared all activity Logs";
        $this->insert_log($activity, $module);
            
    }
    
    /**
     * Prune logs older than specified days
     */
    public function pruneOldLogs($days_old = 30) {
        try {
            $cutoff_date = date('Y-m-d H:i:s', strtotime('-' . $days_old . ' days'));
            
            // Count logs to be deleted
            $this->db->where('created_at <', $cutoff_date);
            $count = $this->db->count_all_results('activity_log');
            
            // Delete old logs
            $this->db->where('created_at <', $cutoff_date);
            $this->db->delete('activity_log');
            
            $deleted_rows = $this->db->affected_rows();
            
            // Log the pruning activity
            if ($deleted_rows > 0) {
                $this->insert_log("Pruned $deleted_rows logs older than $days_old days", "Activity Logs");
            }
            
            return [
                'status' => 'success',
                'deleted_count' => $deleted_rows,
                'total_old_logs' => $count,
                'cutoff_date' => $cutoff_date
            ];
            
        } catch (Exception $e) {
            log_message('error', 'Log pruning error: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Failed to prune logs: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get log statistics for cleanup
     */
    public function getLogCleanupStats() {
        try {
            // Get total logs count
            $total_logs = $this->db->count_all('activity_log');
            
            // Get logs older than 30 days
            $cutoff_date = date('Y-m-d H:i:s', strtotime('-30 days'));
            $this->db->where('created_at <', $cutoff_date);
            $old_logs = $this->db->count_all_results('activity_log');
            
            // Get logs older than 60 days
            $cutoff_date_60 = date('Y-m-d H:i:s', strtotime('-60 days'));
            $this->db->where('created_at <', $cutoff_date_60);
            $very_old_logs = $this->db->count_all_results('activity_log');
            
            // Get oldest log date
            $this->db->select('MIN(created_at) as oldest_log');
            $oldest = $this->db->get('activity_log')->row();
            $oldest_date = $oldest ? $oldest->oldest_log : 'N/A';
            
            return [
                'total_logs' => $total_logs,
                'logs_older_than_30_days' => $old_logs,
                'logs_older_than_60_days' => $very_old_logs,
                'oldest_log_date' => $oldest_date
            ];
            
        } catch (Exception $e) {
            log_message('error', 'Log cleanup stats error: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Failed to get cleanup stats: ' . $e->getMessage()
            ];
        }
    }

    public function addGroup($postdata){

           $this->db->insert('user_groups',$postdata);

           return true;
   
    }

}

/* End of file */
