<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Admin extends MX_Controller {

    protected $user;
    protected $deapartment;
    protected $username;
    protected $user_id;
    protected $module;

    public function __construct() {
        parent::__construct();

        $this->user = $this->session->get_userdata();
        $this->deapartment = $this->user;
        $this->module = 'admin';

        //if(!$this->session->userdata('logged_in')) {
        //     redirect(base_url());


        // }

    

        $this->load->model('admin_model');
//$this->load->model('facilities_mdl');
       // $this->load->model('districts_mdl');
        //$this->load->model('schedules_mdl');
        
        

       $this->username=$this->session->userdata['username'];
       $this->user_id=$this->session->userdata['user_id'];
        
    }
    

    private function ajax_checking(){
        if (!$this->input->is_ajax_request()) {
            redirect(base_url());
        }
    }

    public function user_list(){

        $data = array(
            'title' => 'User Management',
            'users' => $this->admin_model->get_user_list(),
            'facilities'=> Modules::run('lists/getFacilities'),
            'districts' => Modules::run('districts/getDistricts')
   
        );

        $data['view']='users';
        $data['module']="admin";  
        echo Modules::run("templates/main", $data);

        

    }


    public function settings(){

        $data = array(
            'title' => 'System Variables',
            'vars' => $this->admin_model->get_vars(),
            // 'districts' => $this->districts_mdl->getDistricts(),
            // 'facilities' => $this->facilities_mdl->getFacilities()
            'facilities'=> Modules::run('lists/getFacilities'),
            'districts' => Modules::run('districts/getDistricts')
            //'username'=>$this->username
        );

        $data['view']='config';
        $data['module']="admin";
        echo Modules::run("templates/main",$data);

    }


public function configure(){
    
    $postData=$this->input->post();
   
   $res= $this->admin_model->save_config($postData);
   
   print_r($res);
   
    
    
}



    public function showLogs(){
      //  Handle AJAX requests for server-side pagination
        if ($this->input->is_ajax_request()) {
            $this->_handleLogsAjaxRequest();
            return;
        }

        $data = array(
            'title' => 'User Activity Logs',
        );
       // dd($data);

        $data['view']='user_logs';
        $data['module']=$this->module;
        echo Modules::run("templates/main", $data);
    }
    
    /**
     * Handle AJAX requests for logs server-side pagination
     */
    private function _handleLogsAjaxRequest() {
        try {
            $draw = $this->input->post('draw');
            $start = $this->input->post('start');
            $length = $this->input->post('length');
            $search = $this->input->post('search')['value'];
            $order_column = $this->input->post('order')[0]['column'];
            $order_dir = $this->input->post('order')[0]['dir'];
            $user_filter = $this->input->post('user_filter');
            $module_filter = $this->input->post('module_filter');
            $date_from = $this->input->post('date_from');
            $date_to = $this->input->post('date_to');
            
            // Get total count
            $total_records = $this->admin_model->get_logs_count();
            
            // Get filtered data
            $data = $this->admin_model->get_logs_ajax($start, $length, $search, $order_column, $order_dir, $user_filter, $module_filter, $date_from, $date_to);
            
            // Get filtered count
            $filtered_records = $this->admin_model->get_logs_count($search, $user_filter, $module_filter, $date_from, $date_to);
            
            $response = array(
                "draw" => intval($draw),
                "recordsTotal" => $total_records,
                "recordsFiltered" => $filtered_records,
                "data" => $data
            );
            
            $this->output->set_content_type('application/json')->set_output(json_encode($response));
        } catch (Exception $e) {
            log_message('error', 'Logs AJAX Error: ' . $e->getMessage());
            $response = array(
                "draw" => intval($draw),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
                "error" => "An error occurred while processing your request"
            );
            $this->output->set_content_type('application/json')->set_output(json_encode($response));
        }
    }
    
    
    public function clearLogs(){
        
        $this->admin_model->clearLogs();
        
        $this->showLogs();

    }
    
    /**
     * Prune old logs (older than 30 days)
     */
    public function pruneLogs() {
        // Check if user has permission (you can add your permission logic here)
        if (!$this->session->userdata('isLoggedIn')) {
            redirect('auth/login');
        }
        
        $result = $this->admin_model->pruneOldLogs(30); // 30 days
        
        if ($this->input->is_ajax_request()) {
            echo json_encode($result);
            return;
        }
        
        if ($result['status'] === 'success') {
            $this->session->set_flashdata('success', "Successfully pruned {$result['deleted_count']} logs older than 30 days.");
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }
        
        redirect('admin/showLogs');
    }
    
    /**
     * Get log cleanup statistics
     */
    public function getLogCleanupStats() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        $stats = $this->admin_model->getLogCleanupStats();
        echo json_encode($stats);
    }



	
	public function switches(){
	    
	    
	    $facilities['facilities']=Modules::run('facilities/getFacilities');		
		 
  $newArray=array();
  
  $switches="";
  
foreach($facilities as $val){
    
    $newKey=$val['district_id'];
    
    $newArray[$newKey][]=$val;
    
     $switches. "<optgroup label='".$newKey."'>";
    
    for($i=0;$i<count($newArray[$newKey]);$i++){
        
        $switches. "<option value='".$newArray[$newKey][$i]['facility_id']."'>".$newArray[$newKey][$i]['facility']."</option>";
    }
    
    $switches. "</optgroup>";
    
}

return $switches;
	    
	    
	}

    public function select(){


        $data = array(
            'facilities' => Modules::run('facilities/getFacilities'),
            'username'=>$this->username
        );

        $data['view']='select';
        $data['module']="admin";
        echo Modules::run("templates/main",$data);
    }


    public function selector(){
        
        $facility=$this->input->post('facility');
        
    // modify session facility id
    
      //$this->session->set_userdata('facility',$facility);
        
        redirect('facilities');

    }




    function add_user(){
        $this->ajax_checking();

        $postData = $this->input->post();

	if($postData['email']==""){

	$postData['email']=$postData['username']."_noemail@hris.com";
        }
    
       $user= $this->aauth->create_user($postData['email'],$postData['password'],$postData['name']);
        
       $users=$this->aauth->list_users();
       
       foreach($users as $user){
           
           if($user->email==$postData['email']){
               
               $uid=$user->id;
           }
       }
        
       $this->aauth->add_member($uid, $postData['role']);
       
       
         $insert = $this->admin_model->insert_user($postData,$uid);
        
    
        echo json_encode($insert);
       
       
    }


    function edit_user(){
        $this->ajax_checking();

        $postData = $this->input->post();
        $update = $this->admin_model->update_user_details($postData);
        
        if($update['status'] == 'success')

        echo json_encode($update);
    }


    function deactivate_user($username,$id){

       $update = $this->admin_model->deactivate_user($username,$id);
        
        if($update['status'] == 'success')

        echo "User successfully deactivated";
    }


 function activate_user($username,$id){

       
       $update = $this->admin_model->activate_user($username,$id);
        
        if($update['status'] == 'success')

        echo "User successfully activated";
    }


    public function scheduled_report(){

        $data = array(
            'title' => 'Facilities that Scheduled',
            'facilities'=> Modules::run('facilities/getFacilities'),
            'districts' => Modules::run('districts/getDistricts')
            //'username'=>$this->username
        );

        $data['view']='scheduled';
        $data['module']=$this->scheduled_mdl;
        echo Modules::run("templates/main",$data);

    }



 function scheduled(){
     
     
     
    
       $sfacilities = $this->admin_model->list_scheduled();
       
       //print_r($sfacilities);
       
       if($sfacilities[0]['facility']==""){
           
           $data='<table class="table table-striped">';
           
            $data.='<tr>';
           $data.='<td colspan=3><font color="red">No Data from this search</font></td>';
         
           $data.='</tr>';
           $data.='</table>';
           
           
       }
       
       else{
       
       
       $data='<table class="table table-striped">';
        $data.='<thead>';
           $data.='<th>#</th>';
           $data.='<th>District</th>';
           $data.='<th>Health Unit</th>';
           $data.='<th></th>';
           $data.='</thead>';
       
       $no=1;
       
       foreach($sfacilities as $facility){
           
           $data.='<tr>';
            $data.='<td>'.$no.'</td>';
           $data.='<td>'.$facility['district'].'</td>';
           $data.='<td>'.$facility['facility'].'</td>';
           $data.='<td><!--a href="" class="btn btn-info btn-sm">See More</a--></td>';
           $data.='</tr>';
           
           $no++;
           
           
           
       }
       
       $data.='</table>';
       }
       
       print_r($data);
       
       
       
    }



    
public function groups(){
    
        $data['title']='Groups Management';
        $data['uptitle'] = 'Groups Management';
        $data['view']='groups';
        $data['module']="admin";
        echo Modules::run("templates/main",$data);
        //$this->load->view('groups', $data);
    
    
}

public function resetpass($user){
    $variables=$this->admin_model->get_vars();
   
   $pass="";
	foreach($variables as $vars){
	
	
	if($vars['variable']=="Default_password"){
	$pass=$vars['content'];
	}
}

    $data = array(
            'password' => md5($pass)
        );

    $this->db->where('user_id',$user);
    $this->db->update('user',$data);
    
    $this->session->set_flashdata('msg','<div class="alert alert-info alert-dismissable col-md-12"style="width:90%; margin-left:3em;">
  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
   Password has been reset to default. </div>');
    
                  redirect("admin/user_list");
    
}

///user management


    function add_member() {
        
        $group=$this->input->post('group');
        
        $id=$this->input->post('member');


       // $a = $this->aauth->add_member($id, $group);
    }




//create a permission
    function create_perm() {

        //$a = $this->aauth->create_perm("deneme","def");
    }



  
//allow group to do some thing
    function groupAllow() {


$data=$this->input->post();
$group=$this->input->post('group');


$permissions=$_POST['permissions']; 

$this->db->where('group_id',$group);
$this->db->delete('aauth_perm_to_group');

foreach($permissions as $permission)

{
   
$this->aauth->create_perm($permission);

$a=$this->aauth->allow_group($group,$permission);
        
}


if($a){
    
echo 'OK';
}

    }


//

    function deny_group() {

        //$a = $this->aauth->deny_group("deneme","deneme");
    }




    function allow_user() {


//allow user id =9 to do something=deneme
       // $a = $this->aauth->allow_user(9,"deneme");
    }




    function deny_user() {

        //$a = $this->aauth->deny_user(9,"deneme");
    }
    
    
  

function addGroup(){
    
    $postdata=$this->input->post();

    $post=$this->admin_model->addGroup($postdata);
 
   Modules::run("utility/setFlash","<font color='green'>Group Added</font>");
    
    redirect('admin/groups');
}








}

/* End of file */

