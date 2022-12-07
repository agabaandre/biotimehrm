
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \utils\HttpUtil;
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

    public function get_new_users(){
        return $this->biotime_mdl->get_new_users();
    }
   
   




}

