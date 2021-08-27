<?php defined('BASEPATH') OR exit('No direct script access allowed');

Class Biotime_model extends CI_Model
{
   public  function __construct(){
        parent:: __construct();
        $this->facility=$this->session->userdata['facility'];
      

    }

   
public function addMachines($data){

  
    $query=$this->db->replace('biotime_devices',$data);
  
    if ($query){
        $message="Successful";
    }
    else{
        $message="Failed";

    }
    
return $message;
}
public function getMachines($filter){
    
return $this->db->get('biotime_devices')->result();

}
public function get_enrolled(){
  $query= $this->db->query("SELECT * FROM fingerprints_final WHERE facilityId='$this->facility' AND device IS NOT NULL");
return $query->result(); 
}
public function get_new_users(){
    $facility=$_SESSION['facility'];
    $query= $this->db->query("SELECT * FROM fingerprints_final WHERE facilityId='$this->facility' AND device IS NULL");
 return $query->result();
 }
 public function get_new_deps(){
    $facility=$_SESSION['facility'];
    $query=$this->db->query("SELECT distinct(department),department_id FROM  ihrisdata WHERE department_id NOT IN (SELECT dept_code from biotime_departments)");
 return $query->result();
 }
 public function get_new_facs(){
    $facility=$_SESSION['facility'];
    $query=$this->db->query("SELECT distinct(facility),facility_id FROM  ihrisdata WHERE facility_id NOT IN (SELECT area_code from biotime_facilities)");
 return $query->result();
 }
 public function get_new_jobs(){
    $facility=$_SESSION['facility'];
    $query=$this->db->query("SELECT distinct(job),job_id FROM  ihrisdata WHERE job_id NOT IN (SELECT position_code from biotime_jobs)");
 return $query->result();
 }
 
 public function getbioDeps(){
     $q=$this->db->get('biotime_departments');

    return  $q->result();
}
public function getbiojobs(){
   $q= $this->db->get("biotime_jobs");
return $q->result();

}
public function getbiofacilities(){
    $q= $this->db->get("biotime_facilities");
return $q->result();

}
public function getihrisDeps(){
    $q= $this->db->query("SELECT distinct(department),department_id from ihrisdata");
    return $q->num_rows();

}
public function getihris_users(){
    $this->db->where('facility_id',"$this->facility");
    $q= $this->db->get("ihrisdata");
    return $q->result();

}
public function getihrisjobs(){
    $q= $this->db->query("SELECT distinct(job_id),job from ihrisdata");
    return $q->num_rows();
}
public function getihrisfacilities(){
    
    $q= $this->db->query("SELECT distinct(facility_id),facility from ihrisdata");
    return $q->num_rows();

}






}