<?php defined('BASEPATH') OR exit('No direct script access allowed');

Class Biotimejobs_mdl extends CI_Model
{
   public  function __construct(){
        parent:: __construct();
        $this->facility=$this->session->userdata['facility'];
      

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

 
public function add_enrolled($data){
    if(count($data)>1){
    $this->db->query("TRUNCATE fingerprints_staging");
    }
    $query = $this->db->insert_batch('fingerprints_staging',$data);
    $times = $this->db->query_times;


    if($query){
        $n=$this->db->query("select entry_id from fingerprints_staging");
        $this->db->query("CALL `fingerpints_cache`()");
        $message=print_r($this->exect()) ." saveEnrolled() add_enrolled() Created Enrolled users from Biotime ".$n->num_rows();

        
    }
    else{
        $message=print_r($this->exect()) ." saveEnrolled() add_enrolled() Failed ";

    }

return $message;

}
public function add_time_logs($data){
    if(count($data)>1){
    
    $this->db->query("TRUNCATE biotime_data");
    }
    $query = $this->db->insert_batch('biotime_data',$data);
    $times = $this->db->query_times;


    if($query){
        $n=$this->db->query("select entry_id biotime_data");
        $this->db->query("CALL `biotime_cache`()");
        $message=print_r($this->exect()) ." fetchBiotTimeLogs()  add_time_logs() Created Enrolled users from Biotime ".$n->num_rows();
        
    }
    else{
        $message=print_r($this->exect()) ." fetchBiotTimeLogs()  add_time_logs() Failed ";

    }

return $message;

}
//not working as expected. should return querytime
public function exect(){
return  $this->benchmark->elapsed_time();
}

}