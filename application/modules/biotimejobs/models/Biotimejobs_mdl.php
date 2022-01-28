<?php 

defined('BASEPATH') OR exit('No direct script access allowed');

Class Biotimejobs_mdl extends CI_Model
{
   public  function __construct(){
        parent:: __construct();
        $this->facility=$_SESSION['facility'];
      

    }

   
public function add_ihrisdata($data){
    if(count($data)>1){
    $this->db->query("TRUNCATE `ihrisdata`");
    }
    $query = $this->db->insert_batch('ihrisdata',$data);

    if($query){
        $n=$this->db->query("select ihris_pid from ihrisdata");
        
        
        $message=print_r($this->exect()) ." get_ihrisdata() add_ihrisdata()  IHRIS HRH ".$n->num_rows();



    }
    else{
        $message=print_r($this->exect()) ." get_ihrisdata() add_ihrisdata()  IHRIS HRH FAILED ";

    }

return $message;

}

public function add_ucmbdata($data){
 
    $query = $this->db->insert_batch('ihrisdata',$data);

    if($query){
        $n=$this->db->query("select ihris_pid from ihrisdata");
        
        
        $message=print_r($this->exect()) ." get_ihrisdata() add_ihrisdata()  IHRIS HRH ".$n->num_rows();



    }
    else{
        $message=print_r($this->exect()) ." get_ihrisdata() add_ihrisdata()  IHRIS HRH FAILED ";

    }

return $message;

}
 
public function add_enrolled($data){
    if($count=count($data)>1){
    $this->db->query("CALL `fingerpints_cache`()");
    $this->db->query("TRUNCATE fingerprints_staging");
    }
    $query = $this->db->insert_batch('fingerprints_staging',$data);

  

    if($query){
        $n=$this->db->query("select entry_id from fingerprints_staging");
      
        $message=print_r($this->exect()) ." saveEnrolled() add_enrolled() Created Enrolled users from Biotime ".$n->num_rows();

        // $this->db->insert("INSERT INTO `biotime_sync_log` (`serial_no`,  `last_gen`, `records`) VALUES (NULL, current_timestamp(), $n->num_rows() ));
        // ");
    }
    else{
        $message=print_r($this->exect()) ." saveEnrolled() add_enrolled() Failed ";

    }

return $message;

}
public function add_time_logs($data){
    if(count($data)>1){
    $this->db->query("CALL `biotime_cache`()");
    $this->db->query("TRUNCATE biotime_data");
    }
    $query = $this->db->insert_batch('biotime_data',$data);
    $this->db->query(" DELETE from biotime_data where emp_code='0'");

   

    
    if($query){
        $n=$this->db->get("biotime_data");
        
        $message=print_r($this->exect()) ." fetchBiotTimeLogs()  add_time_logs() Created Logs from Biotime ".$n->num_rows();
        // $this->db->insert("INSERT INTO `biotime_sync_log` (`serial_no`,  `last_gen`, `records`) VALUES (NULL, current_timestamp(), $n->num_rows());
        // ");
    }
    else{
        $message=print_r($this->exect()) ." fetchBiotTimeLogs()  add_time_logs() Failed ";

    }

return $message;

}


public function save_department($data){
    if(count($data)>1){
        $this->db->query("TRUNCATE biotime_departments");
    }
    $query=$this->db->insert_batch("biotime_departments",$data);
    if($query){
        $n=$this->db->query("select id biotime_departments");
        
        $message=print_r($this->exect()) ." save_department() Created Departments from Biotime ".$n->num_rows();
        // $this->db->insert("INSERT INTO `biotime_sync_log` (`serial_no`,  `last_gen`, `records`) VALUES (NULL, current_timestamp(), $n->num_rows());
        // ");
    }
    else{
        $message=print_r($this->exect()) ." Fetch Departments Failed ";

    }

return $message;
}
public function save_jobs($data){
    if(count($data)>1){
        $this->db->query("TRUNCATE biotime_jobs");
    }
    $query=$this->db->insert_batch("biotime_jobs",$data);
    if($query){
        $n=$this->db->get("biotime_jobs");
        $message=print_r($this->exect()) ." save_jobs() Created jobs from Biotime ".$n->num_rows();
        // $this->db->insert("INSERT INTO `biotime_sync_log` (`serial_no`,  `last_gen`, `records`) VALUES (NULL, current_timestamp(), $n->num_rows());
        // ");
    }
    else{
        $message=print_r($this->exect()) ." Fetch jobs Failed ";

    }

return $message;
}
public function save_facilities($data){
    if(count($data)>1){
        $this->db->query("TRUNCATE biotime_facilities");
    }
    $query=$this->db->insert_batch("biotime_facilities",$data);
    if($query){
        $n=$this->db->get("biotime_facilities");
        
        $message=print_r($this->exect()) ." save_facilities() Created Fcailities from Biotime ".$n->num_rows();
        // $this->db->insert("INSERT INTO `biotime_sync_log` (`serial_no`,  `last_gen`, `records`) VALUES (NULL, current_timestamp(), $n->num_rows());
        // ");
    }
    else{
        $message=print_r($this->exect()) ." Fetch Failities Failed ";

    }

return $message;
}
public function addMachines($data){

  
    $query=$this->db->replace('biotime_devices',$data);
  
    if ($query){
        $message="Successful SYNC Biotime Devices ".$this->db->affected_rows();
    }
    else{
        $message="Failed to SYNC Biotime Decices";

    }
    
return $message;
}
//not working as expected. should return querytime
public function exect(){
return  $this->benchmark->elapsed_time();
}


}