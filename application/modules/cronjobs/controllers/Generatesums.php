<?php 

class Generatesums extends MX_Controller{
   //district data share
    public function dutySums()
    {
    
        $ymonth="";
        //$ymonth=$_GET['month'];
        if($ymonth){
          $ymonth;
        }
        else{
            //last month
            $ymonth = date('Y-m', strtotime('-1 months'));
        }
       
       
         if($ymonth){
            
         ini_set('max_execution_time',0);
         ignore_user_abort(true);
         $sql=$this->db->query("SET @p0='$ymonth'");
         $sql=$this->db->query("TRUNCATE TABLE dutysummary");
         $sql=$this->db->query("CALL `duty_sums`(@p0)");
         if($sql){
             echo "Procedure Executed Succesfully";
         }
         else{
             echo "Procedure Execution Failed";
         }
        }
    }
    public function attSums()
    {
       
        $ymonth="";
        //$ymonth=$_GET['month'];
        if($ymonth){
          $ymonth;
        }
        else{
            //last month
            $ymonth = date('Y-m', strtotime('-1 months'));
        }
       
         if($ymonth){
         ini_set('max_execution_time',0);
         ignore_user_abort(true);
         $sql=$this->db->query("SET @p0='$ymonth'");
         $sql=$this->db->query("TRUNCATE TABLE att_summary");
         $sql=$this->db->query("CALL `att_proc`(@p0)");
         if($sql){
             echo "Procedure Executed Succesfully";
         }
         else{
             echo "Procedure Execution Failed";
         }
        }
    }
   
	



}
?>
