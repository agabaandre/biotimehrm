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






}