<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Biometrics Model (formerly Biotime_model)
 * Handles biometrics/biotime data operations
 */
Class Biometrics_model extends CI_Model
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
            // Map DataTable column indices to database columns
            // Columns: 0=sn, 1=area_name, 2=last_activity, 3=user_count, 4=ip_address, 5=status (not sortable), 6=manual_sync (not sortable)
            $columns = ['sn', 'area_name', 'last_activity', 'user_count', 'ip_address'];
            $columnIndex = intval($order['column']);
            
            // Only sort if column index is valid and sortable (0-4)
            if ($columnIndex >= 0 && $columnIndex < count($columns)) {
                $this->db->order_by($columns[$columnIndex], $order['dir']);
            } else {
                // Default ordering by last_activity desc for non-sortable columns
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

public function getEnrolledCount($search = '') {
    try {
        $this->db->from('fingerprints_final');
        $this->db->where('facilityId', $this->facility);
        $this->db->where("device != ''");
        $this->db->where("device IS NOT NULL");
        
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('ihris_pid', $search);
            $this->db->or_like('fullname', $search);
            $this->db->or_like('othername', $search);
            $this->db->or_like('facility', $search);
            $this->db->or_like('device', $search);
            $this->db->or_like('job', $search);
            $this->db->or_like('card_number', $search);
            $this->db->group_end();
        }
        
        return $this->db->count_all_results();
    } catch (Exception $e) {
        log_message('error', 'getEnrolledCount error: ' . $e->getMessage());
        return 0;
    }
}

public function getEnrolledPaginated($start, $length, $search = '', $order = null) {
    try {
        $this->db->select('*');
        $this->db->from('fingerprints_final');
        $this->db->where('facilityId', $this->facility);
        $this->db->where("device != ''");
        $this->db->where("device IS NOT NULL");
        
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('ihris_pid', $search);
            $this->db->or_like('fullname', $search);
            $this->db->or_like('othername', $search);
            $this->db->or_like('facility', $search);
            $this->db->or_like('device', $search);
            $this->db->or_like('job', $search);
            $this->db->or_like('card_number', $search);
            $this->db->group_end();
        }
        
        if ($order && isset($order['column']) && isset($order['dir'])) {
            // Map DataTable column indices to database columns
            // Columns: 0=# (not sortable), 1=ihris_pid, 2=fullname, 3=facility, 4=device, 5=job, 6=card_number, 7=status (not sortable)
            $columns = ['ihris_pid', 'fullname', 'facility', 'device', 'job', 'card_number'];
            $columnIndex = intval($order['column']);
            
            // Adjust for # column (index 0 is not sortable) and status (index 7 is not sortable)
            if ($columnIndex > 0 && $columnIndex < 7 && ($columnIndex - 1) < count($columns)) {
                $this->db->order_by($columns[$columnIndex - 1], $order['dir']);
            } else {
                $this->db->order_by('fullname', 'asc');
            }
        } else {
            $this->db->order_by('fullname', 'asc');
        }
        
        $this->db->limit($length, $start);
        $query = $this->db->get();
        
        if ($query) {
            return $query->result();
        } else {
            log_message('error', 'getEnrolledPaginated query failed');
            return array();
        }
    } catch (Exception $e) {
        log_message('error', 'getEnrolledPaginated error: ' . $e->getMessage());
        return array();
    }
}

public function get_new_users(){
    $facility=$_SESSION['facility'];
    $query= $this->db->query("SELECT * FROM fingerprints_final WHERE facilityId='$this->facility' AND device=''");
 return $query->result();
 }

public function getUnenrolledCount($search = '') {
    try {
        $this->db->from('fingerprints_final');
        $this->db->where('facilityId', $this->facility);
        $this->db->group_start();
        $this->db->where("device = ''");
        $this->db->or_where("device IS NULL");
        $this->db->group_end();
        
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('ihris_pid', $search);
            $this->db->or_like('fullname', $search);
            $this->db->or_like('othername', $search);
            $this->db->or_like('job', $search);
            $this->db->or_like('card_number', $search);
            $this->db->group_end();
        }
        
        return $this->db->count_all_results();
    } catch (Exception $e) {
        log_message('error', 'getUnenrolledCount error: ' . $e->getMessage());
        return 0;
    }
}

public function getUnenrolledPaginated($start, $length, $search = '', $order = null) {
    try {
        $this->db->select('*');
        $this->db->from('fingerprints_final');
        $this->db->where('facilityId', $this->facility);
        $this->db->group_start();
        $this->db->where("device = ''");
        $this->db->or_where("device IS NULL");
        $this->db->group_end();
        
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('ihris_pid', $search);
            $this->db->or_like('fullname', $search);
            $this->db->or_like('othername', $search);
            $this->db->or_like('job', $search);
            $this->db->or_like('card_number', $search);
            $this->db->group_end();
        }
        
        if ($order && isset($order['column']) && isset($order['dir'])) {
            // Map DataTable column indices to database columns
            // Columns: 0=# (not sortable), 1=ihris_pid, 2=fullname, 3=job, 4=card_number
            $columns = ['ihris_pid', 'fullname', 'job', 'card_number'];
            $columnIndex = intval($order['column']);
            
            // Adjust for # column (index 0 is not sortable)
            if ($columnIndex > 0 && ($columnIndex - 1) < count($columns)) {
                $this->db->order_by($columns[$columnIndex - 1], $order['dir']);
            } else {
                $this->db->order_by('fullname', 'asc');
            }
        } else {
            $this->db->order_by('fullname', 'asc');
        }
        
        $this->db->limit($length, $start);
        $query = $this->db->get();
        
        if ($query) {
            return $query->result();
        } else {
            log_message('error', 'getUnenrolledPaginated query failed');
            return array();
        }
    } catch (Exception $e) {
        log_message('error', 'getUnenrolledPaginated error: ' . $e->getMessage());
        return array();
    }
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

