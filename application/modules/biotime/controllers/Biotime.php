
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \utils\HttpUtil;
class Biotime extends MX_Controller{
    private $user;
    private $watermark;
    private $filters;

    public function __construct(){
        parent::__construct();
        
        try {
        $this->user = $this->session->get_userdata();
        $this->load->library('pagination');
        $this->watermark = FCPATH . "assets/img/448px-Coat_of_arms_of_Uganda.svg.png";
            
            // Safely get filters
            try {
        $this->filters = Modules::run('filters/sessionfilters');
            } catch (Exception $e) {
                $this->filters = array();
                log_message('error', 'Failed to get session filters: ' . $e->getMessage());
            }

        $this->load->model('biotime_model', 'biotime_mdl');
        } catch (Exception $e) {
            log_message('error', 'Biotime controller constructor error: ' . $e->getMessage());
        }
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
        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json');
        
        // Return immediately and run sync in background
        $result = array(
            'status' => 'initiated',
            'message' => 'Departments sync has been initiated and is running in the background',
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => 'departments',
            'note' => 'Check server logs for completion status.'
        );
        
        echo json_encode($result, JSON_PRETTY_PRINT);
        
        // Close connection to client
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } else {
            ignore_user_abort(true);
            if (ob_get_level()) {
                ob_end_flush();
            }
            flush();
        }
        
        // Run sync in background
        try {
            set_time_limit(0);
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '256M');
            
            $response = Modules::run('biotimejobs/biotimedepartments');
            
            if ($response) {
                log_message('info', 'Departments sync completed successfully');
            } else {
                log_message('error', 'Departments sync completed with errors');
            }
        } catch (Exception $e) {
            log_message('error', 'Sync Departments Error: ' . $e->getMessage());
        } catch (Error $e) {
            log_message('error', 'Sync Departments Fatal Error: ' . $e->getMessage());
        }
        
        exit;
    }
    
    public function syncFacilities(){
        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json');
        
        // Return immediately and run sync in background
        $result = array(
            'status' => 'initiated',
            'message' => 'Facilities sync has been initiated and is running in the background',
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => 'facilities',
            'note' => 'Check server logs for completion status.'
        );
        
        echo json_encode($result, JSON_PRETTY_PRINT);
        
        // Close connection to client
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } else {
            ignore_user_abort(true);
            if (ob_get_level()) {
                ob_end_flush();
            }
            flush();
        }
        
        // Run sync in background
        try {
            set_time_limit(0);
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '256M');
            
            $response = Modules::run('biotimejobs/biotimeFacilities');
            
            if ($response) {
                log_message('info', 'Facilities sync completed successfully');
            } else {
                log_message('error', 'Facilities sync completed with errors');
            }
        } catch (Exception $e) {
            log_message('error', 'Sync Facilities Error: ' . $e->getMessage());
        } catch (Error $e) {
            log_message('error', 'Sync Facilities Fatal Error: ' . $e->getMessage());
        }
        
        exit;
    }
    
    //position code, Position Name
    public function syncJobs(){
        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json');
        
        // Return immediately and run sync in background
        $result = array(
            'status' => 'initiated',
            'message' => 'Jobs sync has been initiated and is running in the background',
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => 'jobs',
            'note' => 'Check server logs for completion status.'
        );
        
        echo json_encode($result, JSON_PRETTY_PRINT);
        
        // Close connection to client
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } else {
            ignore_user_abort(true);
            if (ob_get_level()) {
                ob_end_flush();
            }
            flush();
        }
        
        // Run sync in background
        try {
            set_time_limit(0);
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '256M');
            
            $response = Modules::run('biotimejobs/biotime_jobs');
            
            if ($response) {
                log_message('info', 'Jobs sync completed successfully');
            } else {
                log_message('error', 'Jobs sync completed with errors');
            }
        } catch (Exception $e) {
            log_message('error', 'Sync Jobs Error: ' . $e->getMessage());
        } catch (Error $e) {
            log_message('error', 'Sync Jobs Fatal Error: ' . $e->getMessage());
        }
        
        exit;
    }
    
    public function syncEmployees(){
        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json');
        
        // Return immediately and run sync in background to avoid timeout
        $result = array(
            'status' => 'initiated',
            'message' => 'Employees sync has been initiated and is running in the background',
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => 'employees',
            'note' => 'This is a long-running process. The sync will continue in the background. Check server logs for completion status.'
        );
        
        // Send response immediately
        echo json_encode($result, JSON_PRETTY_PRINT);
        
        // Close connection to client so sync can continue
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } else {
            // For non-FastCGI environments
            ignore_user_abort(true);
            if (ob_get_level()) {
                ob_end_flush();
            }
            flush();
        }
        
        // Now run the sync in background with no time limit
        try {
            set_time_limit(0); // No time limit for background process
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '512M');
            
            // Run the sync
            $response = Modules::run('biotimejobs/saveEnrolled');
            
            // Log completion
            if ($response === false) {
                log_message('error', 'Employees sync completed with errors - check logs');
            } else {
                log_message('info', 'Employees sync completed successfully');
            }
        } catch (Exception $e) {
            log_message('error', 'Sync Employees Error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
        } catch (Error $e) {
            log_message('error', 'Sync Employees Fatal Error: ' . $e->getMessage());
        }
        
        exit;
    }
    public function syncPersons($facilty){
        

        
    }
    public function getMachines($search=FALSE){
        return $this->biotime_mdl->getMachines($search);
    }

    public function getMachinesAjax(){
        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }
        ob_start();
        
        // Set JSON header
        header('Content-Type: application/json');
        
        // Initialize default values
        $draw = 1;
        $start = 0;
        $length = 25;
        $search = '';
        $order = null;
        
        try {
            // Get POST data (DataTables sends POST)
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
            
            // Ensure model is loaded
            if (!isset($this->biotime_mdl)) {
                $this->load->model('biotime_model', 'biotime_mdl');
            }
            
            // Get data from model
        $total = $this->biotime_mdl->getMachinesCount($search);
        $machines = $this->biotime_mdl->getMachinesPaginated($start, $length, $search, $order);
        
        $data = array();
            if (!empty($machines) && is_array($machines)) {
        foreach($machines as $machine) {
                    $lastActivity = isset($machine->last_activity) ? $machine->last_activity : null;
                    $status = $this->getMachineStatus($lastActivity);
                    $sn = isset($machine->sn) ? $machine->sn : '';
            $data[] = array(
                        $sn,
                        isset($machine->area_name) ? $machine->area_name : '',
                        isset($machine->last_activity) ? $machine->last_activity : '',
                        isset($machine->user_count) ? $machine->user_count : 0,
                        isset($machine->ip_address) ? $machine->ip_address : '',
                $status,
                        $this->getSyncButton($sn)
            );
                }
        }
        
        $response = array(
                'draw' => $draw,
                'recordsTotal' => $total ? intval($total) : 0,
                'recordsFiltered' => $total ? intval($total) : 0,
            'data' => $data
        );
        
            ob_end_clean();
            echo json_encode($response);
            exit;
        } catch (Exception $e) {
            ob_end_clean();
            log_message('error', 'getMachinesAjax Exception: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            
            $response = array(
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => array(),
                'error' => 'An error occurred: ' . $e->getMessage()
            );
            echo json_encode($response);
            exit;
        } catch (Error $e) {
            if (ob_get_level()) {
                ob_end_clean();
            }
            log_message('error', 'getMachinesAjax Fatal Error: ' . $e->getMessage());
            
            $response = array(
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => array(),
                'error' => 'A fatal error occurred: ' . $e->getMessage()
            );
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        } catch (Throwable $e) {
            // Catch any other throwable (PHP 7+)
            if (ob_get_level()) {
                ob_end_clean();
            }
            log_message('error', 'getMachinesAjax Throwable: ' . $e->getMessage());
            
            $response = array(
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => array(),
                'error' => 'An error occurred: ' . $e->getMessage()
            );
            header('Content-Type: application/json');
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
        return '<button type="button" class="btn btn-primary btn-sm sync-machine" data-sn="'.$sn.'">
                    <i class="fas fa-sync"></i> Sync
                </button>';
    }

    public function get_new_users(){
        return $this->biotime_mdl->get_new_users();
    }
   
   




}

