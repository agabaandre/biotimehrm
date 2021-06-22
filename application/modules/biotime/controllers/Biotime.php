
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Biotime extends MX_Controller{
	
	public  function __construct(){
		parent:: __construct();
        $this->user=$this->session->get_userdata();
        $this->load->library('pagination');
        $this->watermark=FCPATH."assets/img/448px-Coat_of_arms_of_Uganda.svg.png";
        $this->filters=Modules::run('filters/sessionfilters');
        
		$this->load->model('biotime_model','biotime_mdl');
     
	}
    public function updateTerminals(){
     $terminals=Modules::run('cronjobs/Biotimejobs/terminals');
        //  print_r($terminals);
       $db=array();
         foreach ($terminals->data as $terminal){
            
       
        $insert= array(
        'sn'=>$terminal->sn,
        'ip_address'=>$terminal->ip_address,
        'area_code'=>$terminal->area_code,
        'user_count'=>$terminal->user_count,
        'face_count'=>$terminal->face_count,
        'palm_count'=>$terminal->palm_count,
        'area_name' =>$terminal->area_name,
        'last_activity'=>$terminal->last_activity);
        $message=$this->biotime_mdl->addMachines($insert);
        $this->session->set_flashdata('message', $message);
         $data['view']='biotime_devices';
         $data['uptitle']="Bio Time Devices";
         $data['title']="Bio Time Devices";
		 $data['module']="biotime";
       
		 echo Modules::run("templates/main",$data);
        //  print_r($terminal);
         }

       
       
    
    }
    public function tasks(){
    
            $data['view']='biotime_tasks';
            $data['uptitle']="iHRIS & BioTime Tasks";
            $data['title']="iHRIS BioTime Tasks ";
            $data['module']="biotime";
            echo Modules::run("templates/main",$data);
  
       
       }
    //Department Department code, Department Name
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
   




}

