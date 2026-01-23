<?php defined('BASEPATH') OR exit('No direct script access allowed');

Class Biotime_model extends CI_Model
{
    protected $facility;
    protected $user;
    protected $watermark;
    protected $filters;

   public  function __construct(){
        parent:: __construct();
        
        // Safely get facility from session
        $userdata = $this->session->userdata;
        $this->facility = isset($userdata['facility']) ? $userdata['facility'] : null;
        $this->user = $this->session->get_userdata();
        $this->watermark = FCPATH."assets/img/448px-Coat_of_arms_of_Uganda.svg.png";
        
        // Safely get filters
        try {
            $this->filters = Modules::run('filters/sessionfilters');
        } catch (Exception $e) {
            $this->filters = array();
            log_message('error', 'Failed to get session filters: ' . $e->getMessage());
        }
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

public function getMachinesCount($search = '') {
    try {
        $this->db->from('biotime_devices');
        
    if (!empty($search)) {
        $this->db->group_start();
        $this->db->like('sn', $search);
        $this->db->or_like('area_name', $search);
        $this->db->or_like('ip_address', $search);
        $this->db->group_end();
    }
        
        return $this->db->count_all_results();
    } catch (Exception $e) {
        log_message('error', 'getMachinesCount error: ' . $e->getMessage());
        return 0;
    }
}

public function getMachinesPaginated($start, $length, $search = '', $order = null) {
    try {
        $this->db->select('*');
        $this->db->from('biotime_devices');
        
    if (!empty($search)) {
        $this->db->group_start();
        $this->db->like('sn', $search);
        $this->db->or_like('area_name', $search);
        $this->db->or_like('ip_address', $search);
        $this->db->group_end();
    }
    
    if ($order && isset($order['column']) && isset($order['dir'])) {
            $columns = ['sn', 'area_name', 'last_activity', 'user_count', 'ip_address'];
        if (isset($columns[$order['column']])) {
            $this->db->order_by($columns[$order['column']], $order['dir']);
            } else {
                // Default ordering by last_activity desc
                $this->db->order_by('last_activity', 'desc');
            }
        } else {
            // Default ordering by last_activity desc
            $this->db->order_by('last_activity', 'desc');
    }
    
    $this->db->limit($length, $start);
        $query = $this->db->get();
        
        if ($query) {
            return $query->result();
        } else {
            log_message('error', 'getMachinesPaginated query failed');
            return array();
        }
    } catch (Exception $e) {
        log_message('error', 'getMachinesPaginated error: ' . $e->getMessage());
        return array();
    }
}
public function get_enrolled(){
  $query= $this->db->query("SELECT * FROM fingerprints_final WHERE facilityId='$this->facility' AND device!=''");
return $query->result(); 
}
public function get_new_users(){
    $facility=$_SESSION['facility'];
    $query= $this->db->query("SELECT * FROM fingerprints_final WHERE facilityId='$this->facility' AND device=''");
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