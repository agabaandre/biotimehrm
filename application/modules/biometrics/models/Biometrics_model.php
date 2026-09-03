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

/**
 * Count distinct areas (area_name) for Attendance Sync table.
 */
public function getAreasCount($search = '') {
    try {
        $this->db->select('area_name');
        $this->db->from('biotime_devices');
        $this->db->group_by('area_name');
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('area_name', $search);
            $this->db->group_end();
        }
        return $this->db->get()->num_rows();
    } catch (Exception $e) {
        log_message('error', 'getAreasCount error: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Get distinct areas with MAX(last_activity) and machine count for Attendance Sync table.
 */
public function getAreasPaginated($start, $length, $search = '', $order = null) {
    try {
        $this->db->select('area_name, MAX(last_activity) AS last_activity, COUNT(*) AS machine_count');
        $this->db->from('biotime_devices');
        $this->db->group_by('area_name');
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('area_name', $search);
            $this->db->group_end();
        }
        if ($order && isset($order['column']) && isset($order['dir'])) {
            $columns = ['area_name', 'last_activity', 'machine_count'];
            $columnIndex = intval($order['column']);
            if ($columnIndex >= 0 && $columnIndex < count($columns)) {
                $this->db->order_by($columns[$columnIndex], $order['dir']);
            } else {
                $this->db->order_by('last_activity', 'desc');
            }
        } else {
            $this->db->order_by('last_activity', 'desc');
        }
        $this->db->limit($length, $start);
        $query = $this->db->get();
        return $query ? $query->result() : array();
    } catch (Exception $e) {
        log_message('error', 'getAreasPaginated error: ' . $e->getMessage());
        return array();
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

/**
 * Staff at this facility with a card number who are not yet in BioTime (fingerprints_staging).
 */
public function get_new_users(){
    $facility = $this->db->escape_str($this->facility);
    $query = $this->db->query(
        "SELECT * FROM ihrisdata
         WHERE facility_id = '$facility'
           AND card_number IS NOT NULL
           AND card_number <> ''
           AND card_number NOT IN (
                SELECT card_number FROM fingerprints_staging
                WHERE card_number IS NOT NULL AND card_number <> ''
           )
         ORDER BY surname, firstname"
    );
    return $query ? $query->result() : [];
}

/**
 * Enrolled BioTime users whose iHRIS facility no longer matches biotime_enrollment
 * (same logic as biotime_transfers view, without relying on the view definer).
 */
public function get_users_needing_update(){
    $facility = $this->db->escape_str($this->facility);
    $query = $this->db->query(
        "SELECT i.*,
                i.facility_id AS new_facility,
                i.facility AS new_fname,
                be.id AS enrollment_row_id,
                be.emp_code,
                be.biotime_emp_id,
                be.biotime_facility_id AS biotime_area_id,
                be.biotime_fac_id,
                be.last_update AS enrollment_last_update
         FROM ihrisdata i
         INNER JOIN biotime_enrollment be ON be.emp_code = i.card_number
         WHERE i.facility_id <> be.biotime_fac_id
           AND (i.facility_id = '$facility' OR be.biotime_fac_id = '$facility')
         ORDER BY i.surname, i.firstname"
    );
    return $query ? $query->result() : [];
}

/**
 * Single transfer candidate by emp/card number (for force update).
 */
public function get_transfer_by_card($card_number){
    $card = $this->db->escape_str(trim((string) $card_number));
    if ($card === '') {
        return null;
    }
    $query = $this->db->query(
        "SELECT i.*,
                i.facility_id AS new_facility,
                i.facility AS new_fname,
                be.id AS enrollment_row_id,
                be.emp_code,
                be.biotime_emp_id,
                be.biotime_facility_id AS biotime_area_id,
                be.biotime_fac_id,
                be.last_update AS enrollment_last_update
         FROM ihrisdata i
         INNER JOIN biotime_enrollment be ON be.emp_code = i.card_number
         WHERE i.card_number = '$card'
           AND i.facility_id <> be.biotime_fac_id
         LIMIT 1"
    );
    return ($query && $query->num_rows()) ? $query->row() : null;
}

public function get_ihris_by_card($card_number){
    $card = trim((string) $card_number);
    if ($card === '') {
        return null;
    }
    $query = $this->db->get_where('ihrisdata', ['card_number' => $card], 1);
    return ($query && $query->num_rows()) ? $query->row() : null;
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

