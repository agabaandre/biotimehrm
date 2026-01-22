
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \utils\HttpUtil;
class Biotime extends MX_Controller{
    private $user;
    private $watermark;
    private $filters;

    public function __construct(){
        parent::__construct();
        $this->user = $this->session->get_userdata();
        $this->load->library('pagination');
        $this->watermark = FCPATH . "assets/img/448px-Coat_of_arms_of_Uganda.svg.png";
        $this->filters = Modules::run('filters/sessionfilters');

        $this->load->model('biotime_model', 'biotime_mdl');
    }
    public function updateTerminals(){
     
         $data['view']='biotime_devices';
         $data['uptitle']="Bio Time Devices";
         $data['title']="Bio Time Devices";
		 $data['module']="biotime";
       
		 echo Modules::run("templates/main",$data);
      
    
    }
    public function tasks(){
    
            $data['view']='biotime_tasks';
            $data['uptitle']="iHRIS & BioTime Tasks";
            $data['title']="iHRIS BioTime Tasks ";
            $data['module']="biotime";
            echo Modules::run("templates/main",$data);
  
       
    }
    public function enrolled(){
    
        $data['view']='enrolled';
        $data['uptitle']="Enrolled Users";
        $data['title']="Enrolled Users ";
        $data['module']="biotime";
        echo Modules::run("templates/main",$data);
  
    }
    public function unenrolled(){
    
        $data['view']='unenrolled_users';
        $data['uptitle']="New Biometric Users";
        $data['title']="New Biometric Users ";
        $data['module']="biotime";
        echo Modules::run("templates/main",$data);
  
    }
    public function get_enrolled(){
        return $this->biotime_mdl->get_enrolled();
    }
    //Department Department code, Department Name

    public function getbioDeps(){
        return $this->biotime_mdl->getbioDeps();

    }
    public function getbiojobs(){
        return $this->biotime_mdl->getbiojobs();

    }
    public function getbiofacilities(){
        return $this->biotime_mdl->getbiofacilities();

    }
    public function getihrisDeps(){
        return $this->biotime_mdl->getihrisDeps();

    }
    public function getihrisjobs(){
        return $this->biotime_mdl->getihrisjobs();
    }
    public function getihrisfacilities(){

        return $this->biotime_mdl->getihrisfacilities();

    }
    public function getihris_users(){

        return $this->biotime_mdl->getihris_users();

    }

    public function bioihriscontrol(){
        $data['biousers']=count($this->get_enrolled());
        $data['ihrisusers']=count($this->getihris_users());
        $data['biojobs']=count($this->getbiojobs());
        $data['biodeps']=count($this->getbioDeps());
        $data['biofacs']=count($this->getbiofacilities());
        $data['ihrisjobs']=$this->getihrisjobs();
        $data['ihrisfacs']=$this->getihrisfacilities();
        $data['ihrisdeps']=$this->getihrisDeps();
        $data['usersgap']=count($this->biotime_mdl->get_new_users());
        $data['jobsgap']=count($this->biotime_mdl->get_new_jobs());
        $data['depsgap']=count($this->biotime_mdl->get_new_deps());
        $data['facsgap']=count($this->biotime_mdl->get_new_facs());
        $data['biouserssync']=$this->get_enrolled()[0]->last_gen;
        $data['ilastsync']=$this->getihris_users()[0]->last_update;
        $data['blastjobssync']=$this->biotime_mdl->getbiojobs()[0]->last_gen;
        $data['blastdepssync']=$this->biotime_mdl->getbioDeps()[0]->last_update;
        $data['blastfacsync']=$this->biotime_mdl->getbiofacilities()[0]->last_gen;;
    return $data;
    }
    public function syncDepartments(){


    }
    public function syncFacilities(){

        
    }
    //position code, Position Name
    public function syncJobs(){

        
    }
    public function syncPersons($facilty){
        

        
    }
    public function getMachines($search=FALSE){
        return $this->biotime_mdl->getMachines($search);
    }

    public function getMachinesAjax(){
        // Set JSON header
        header('Content-Type: application/json');
        
        try {
            $draw = $this->input->post('draw') ? intval($this->input->post('draw')) : 1;
            $start = $this->input->post('start') ? intval($this->input->post('start')) : 0;
            $length = $this->input->post('length') ? intval($this->input->post('length')) : 25;
            
            // Safely get search value
            $search_post = $this->input->post('search');
            $search = '';
            if (!empty($search_post) && isset($search_post['value'])) {
                $search = $search_post['value'];
            }
            
            // Safely get order
            $order_post = $this->input->post('order');
            $order = null;
            if (!empty($order_post) && isset($order_post[0])) {
                $order = $order_post[0];
            }
            
            $total = $this->biotime_mdl->getMachinesCount($search);
            $machines = $this->biotime_mdl->getMachinesPaginated($start, $length, $search, $order);
            
            $data = array();
            foreach($machines as $machine) {
                $lastActivity = isset($machine->last_activity) ? $machine->last_activity : null;
                $status = $this->getMachineStatus($lastActivity);
                $data[] = array(
                    isset($machine->sn) ? $machine->sn : '',
                    isset($machine->area_name) ? $machine->area_name : '',
                    isset($machine->last_activity) ? $machine->last_activity : '',
                    isset($machine->user_count) ? $machine->user_count : 0,
                    isset($machine->ip_address) ? $machine->ip_address : '',
                    $status,
                    $this->getSyncButton(isset($machine->sn) ? $machine->sn : '')
                );
            }
            
            $response = array(
                'draw' => $draw,
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $data
            );
            
            echo json_encode($response);
            exit;
        } catch (Exception $e) {
            $response = array(
                'draw' => isset($draw) ? $draw : 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => array(),
                'error' => 'An error occurred: ' . $e->getMessage()
            );
            echo json_encode($response);
            exit;
        }
    }

    private function getMachineStatus($lastActivity) {
        if (empty($lastActivity)) {
            return '<span class="badge badge-secondary">Unknown</span>';
        }
        
        $today = date('Y-m-d');
        $lastDate = date('Y-m-d', strtotime($lastActivity));
        
        if ($lastDate == $today) {
            return '<span class="badge badge-success">Active</span>';
        } else {
            return '<span class="badge badge-danger">Inactive</span>';
        }
    }

    private function getSyncButton($sn) {
        return '<button type="button" class="btn btn-primary btn-sm sync-machine" data-sn="'.$sn.'" data-toggle="modal" data-target="#syncModal">
                    <i class="fas fa-sync"></i> Sync
                </button>';
    }

    public function get_new_users(){
        return $this->biotime_mdl->get_new_users();
    }
   
   




}

