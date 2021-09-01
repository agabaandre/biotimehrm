
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Departments extends MX_Controller{
	
	public  function __construct(){
		parent:: __construct();
		$this->load->model('department_model','departModel');
    $this->module="departments";
	}
  public function get_facilities()
    {
    
      if(!empty($_GET['dist_data'])){

        $dist = urldecode($_GET["dist_data"]); 

        $distdata= array();
        $distdata=explode("_",$dist);

        $dist_id=$distdata[0];
        $district=$distdata[1]; 
       
       
        $sql = "SELECT DISTINCT facility_id,facility FROM ihrisdata WHERE district_id LIKE '$dist_id' ORDER BY division ASC";

        $facilities = $this->db->query($sql)->result();
      
        $opt = "<option value=''>Select Facility</option>";

          if(!empty($facilities)){

             foreach($facilities as $facility) {
             $opt .= "<option value='".$facility->facility_id."'>".ucwords($facility->facility)."</option>";
           
            }
        

          }
     
        echo $opt;
      }

    }


   public function get_departments()
    {
    
      if(!empty($_GET['fac_data'])){

        $fac = urldecode($_GET["fac_data"]); 

        $facdata= array();
        $facdata=explode("_",$fac);

        $fac_id=$facdata[0];
        $facname=$facdata[1]; 
       
        $sql = "SELECT DISTINCT department_id,department FROM ihrisdata WHERE facility_id LIKE '$fac_id' ORDER BY division ASC";

        $departments = $this->db->query($sql)->result();
      
        $opt = "<option value=''>Select Department</option>";

          if(!empty($departments)){

             foreach($departments as $department) {
             $opt .= "<option value='".$department->department_id."'>".ucwords($department->department)."</option>";
           
            }
        

          }
     
        echo $opt;
      }

    }

    public function get_divisions(){


     if(!empty($_GET['depart_data'])){

          $depart = $_GET["depart_data"]; 

          $departdata= array();
          $departdata=explode("_",$depart);

          $depart_id=$departdata[0];
          $departname=$departdata[1]; 
         
         
          $sql = "SELECT DISTINCT division FROM ihrisdata WHERE department_id LIKE '$depart_id' and division!='' ORDER BY division ASC";

            $divisions = $this->db->query($sql)->result();
        
            $opt = "<option value=''>Select division</option>";

            if(!empty($divisions)){

               foreach($divisions as $division) {
               $opt .= "<option value='".$division->division."'>".ucwords($division->division)."</option>";
             
              }
          

            }
       
          echo $opt;
        }

    }
    public function get_sections(){


      if(!empty($_GET['division_data'])){
 
           $division = $_GET["division_data"]; 
 
        
          
          
           $sql = "SELECT DISTINCT section FROM ihrisdata WHERE section LIKE '$division' and division!='' ORDER BY division ASC";
 
             $divisions = $this->db->query($sql)->result();
         
             $opt = "<option value=''>Select division</option>";
 
             if(!empty($divisions)){
 
                foreach($divisions as $division) {
                $opt .= "<option value='".$division->division."'>".ucwords($division->division)."</option>";
              
               }
           
 
             }
        
           echo $opt;
         }
 
     }

    public function get_units(){
      
       if(!empty($_GET['division'])){

          $division = $_GET["division"];  
         
          $sql = "SELECT DISTINCT unit FROM ihrisdata WHERE division LIKE '$division' and unit!='' ORDER BY unit ASC";

            $units = $this->db->query($sql)->result();

           // print_r($units);
        
            $option = "<option value=''>Select Unit</option>";

            if(!empty($units)){

               foreach($units as $unit) {
               $option .= "<option value='".$unit->unit."'>".ucwords($unit->unit)."</option>";
             
              }
          

            }
       
          echo $option;
       }
    }



    public function switchDepartment(){
 
      $distdata= array();
      $distdata=explode("_",$this->input->post('district'));

      $district_id=$distdata[0];
      $district_name=$distdata[1];
      if(!empty($district_name)){
      $_SESSION['district']=$district_name;
      $_SESSION['district_id']=$district_id;
      }
      $redirect=$this->input->post('direct');
      $facility_id = $this->input->post('facility');
      if(!empty($facility_id)){
      $this->session->set_userdata('facility', $facility_id);
      }
      $facquery=$this->db->query("SELECT facility from ihrisdata where facility_id='$facility_id'");
      $facname=$facquery->row();
      $this->session->set_userdata('facility_name', $facname->facility);
      $depart_id=$this->input->post('department');
      $_SESSION['department_id']=$depart_id;
      $depquery=$this->db->query("SELECT department from ihrisdata where department_id='$depart_id'");
      $depname=$depquery->row();
      $_SESSION['department']=$depname->department;

      $division = $this->input->post('division');
      $section = $this->input->post('section');
      $unit = $this->input->post('unit');
      $_SESSION['division']=$division;
      $_SESSION['section']=$section;
      $_SESSION['unit']=$unit;
      

      redirect($redirect);
    }


   public function countDepart(){

   	$cont=$this->departModel->countDepart();
   	return $cont;
   }


      // gets departments from the department modal
      public function getAll_departments(){

      $depart=$this->departModel->getAll_departments();

      return $depart;

      //print_r($depart);


     }

     public function addDepartments(){
        $data['view']="add_department";
        $data['title']="Departments";
        $data['module']="departments";

        echo Modules::run('templates/main',$data);
      }

     public function  save_department(){

        $save_dprt=$this->departModel->save_department();

        //return $save_dprt;
        redirect('departments/addDepartments');


     }

      public function updateDepartment(){
         $postdata=$this->input->post();
         $update_dprt=$this->departModel->updateDepartment($postdata);

        //return $update_dprt;
        redirect('departments/addDepartments');

      }

      public function deleteDepartment(){
        $delete_dprt=$this->departModel->deleteDepartment();

        //return $delete_dprt;
        redirect('departments/addDepartments');

      }







}






?>