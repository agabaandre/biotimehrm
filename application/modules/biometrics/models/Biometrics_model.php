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
 * SQL: bare / UCMB-prefixed person emp_code from ihris_pid.
 * UCMB-person|123 → 4253123; person|123 → 123.
 */
public function sql_person_emp_code($alias = '')
{
    $p = ($alias !== '' && $alias !== null) ? preg_replace('/[^a-zA-Z0-9_]/', '', $alias) . '.' : '';
    return "CASE WHEN {$p}ihris_pid LIKE '%UCMB%' THEN CONCAT('4253', TRIM(SUBSTRING_INDEX({$p}ihris_pid, 'person|', -1))) ELSE TRIM(SUBSTRING_INDEX({$p}ihris_pid, 'person|', -1)) END";
}

/**
 * SQL: resolved BioTime emp_code (UCMB → 4253+id; else numeric card; else bare person id).
 */
public function sql_resolved_emp_code($alias = '')
{
    $p = ($alias !== '' && $alias !== null) ? preg_replace('/[^a-zA-Z0-9_]/', '', $alias) . '.' : '';
    $person = $this->sql_person_emp_code($alias);
    return "CASE WHEN {$p}ihris_pid LIKE '%UCMB%' THEN {$person} WHEN {$p}card_number REGEXP '^[0-9]+$' THEN TRIM({$p}card_number) ELSE {$person} END";
}

/**
 * Parse DataTables POST params.
 */
protected function _dt_params()
{
    $draw = (int) $this->input->post('draw');
    $start = max(0, (int) $this->input->post('start'));
    $length = (int) $this->input->post('length');
    if ($length < 1 || $length > 500) {
        $length = 25;
    }
    $search_post = $this->input->post('search');
    $search = '';
    if (is_array($search_post) && isset($search_post['value'])) {
        $search = trim((string) $search_post['value']);
    }
    $order_col = 1;
    $order_dir = 'asc';
    $order_post = $this->input->post('order');
    if (is_array($order_post) && isset($order_post[0]['column'])) {
        $order_col = (int) $order_post[0]['column'];
        $order_dir = (isset($order_post[0]['dir']) && strtolower($order_post[0]['dir']) === 'desc') ? 'desc' : 'asc';
    }
    return compact('draw', 'start', 'length', 'search', 'order_col', 'order_dir');
}

/**
 * Server-side enrolled users (fingerprints_final with device).
 */
public function get_enrolled_datatable()
{
    $p = $this->_dt_params();
    $facility = $this->db->escape_str($this->facility);
    $where = "facilityId = '$facility' AND device != '' AND device IS NOT NULL";
    if ($p['search'] !== '') {
        $s = $this->db->escape_like_str($p['search']);
        $where .= " AND (ihris_pid LIKE '%$s%' OR fullname LIKE '%$s%' OR othername LIKE '%$s%' OR job LIKE '%$s%' OR card_number LIKE '%$s%' OR facility LIKE '%$s%' OR device LIKE '%$s%')";
    }

    $total = (int) $this->db->query("SELECT COUNT(*) AS c FROM fingerprints_final WHERE facilityId = '$facility' AND device != '' AND device IS NOT NULL")->row()->c;
    $filtered = (int) $this->db->query("SELECT COUNT(*) AS c FROM fingerprints_final WHERE $where")->row()->c;

    $order_map = [
        1 => 'ihris_pid',
        2 => 'fullname',
        3 => 'facility',
        4 => 'device',
        5 => 'job',
        6 => 'card_number',
        7 => 'att_status',
    ];
    $order_by = isset($order_map[$p['order_col']]) ? $order_map[$p['order_col']] : 'fullname';
    $sql = "SELECT * FROM fingerprints_final WHERE $where ORDER BY $order_by {$p['order_dir']} LIMIT {$p['length']} OFFSET {$p['start']}";
    $rows = $this->db->query($sql)->result();

    $data = [];
    $n = $p['start'] + 1;
    foreach ($rows as $r) {
        $status = ((string) ($r->att_status ?? '') === '1')
            ? "<span style='color:green;'>Active</span>"
            : "<span style='color:#999;'>In-Active</span>";
        $data[] = [
            $n++,
            htmlspecialchars(str_replace('person|', '', $r->ihris_pid ?? '')),
            htmlspecialchars(trim(($r->fullname ?? '') . ' ' . ($r->othername ?? ''))),
            htmlspecialchars($r->facility ?? ''),
            htmlspecialchars($r->device ?? ''),
            htmlspecialchars($r->job ?? ''),
            htmlspecialchars($r->card_number ?? ''),
            $status,
        ];
    }

    return [
        'draw' => $p['draw'],
        'recordsTotal' => $total,
        'recordsFiltered' => $filtered,
        'data' => $data,
    ];
}

/**
 * Server-side new (unenrolled) users — uses resolved emp_code like enrollment.
 */
public function get_new_users_datatable()
{
    $p = $this->_dt_params();
    $facility = $this->db->escape_str($this->facility);
    $resolved = $this->sql_resolved_emp_code('');
    $base_where = "facility_id = '$facility'
           AND {$resolved} <> ''
           AND {$resolved} NOT IN (
                SELECT card_number FROM fingerprints_staging
                WHERE card_number IS NOT NULL AND card_number <> ''
           )";
    $where = $base_where;
    if ($p['search'] !== '') {
        $s = $this->db->escape_like_str($p['search']);
        $where .= " AND (ihris_pid LIKE '%$s%' OR surname LIKE '%$s%' OR firstname LIKE '%$s%' OR othername LIKE '%$s%' OR job LIKE '%$s%' OR card_number LIKE '%$s%' OR {$resolved} LIKE '%$s%')";
    }

    $total = (int) $this->db->query("SELECT COUNT(*) AS c FROM ihrisdata WHERE $base_where")->row()->c;
    $filtered = (int) $this->db->query("SELECT COUNT(*) AS c FROM ihrisdata WHERE $where")->row()->c;

    $order_map = [
        1 => 'ihris_pid',
        2 => 'surname',
        3 => 'job',
        4 => 'card_number',
        5 => $resolved,
    ];
    $order_by = isset($order_map[$p['order_col']]) ? $order_map[$p['order_col']] : 'surname';
    $sql = "SELECT *, {$resolved} AS biotime_emp_code FROM ihrisdata WHERE $where ORDER BY $order_by {$p['order_dir']} LIMIT {$p['length']} OFFSET {$p['start']}";
    $rows = $this->db->query($sql)->result();

    $data = [];
    $n = $p['start'] + 1;
    foreach ($rows as $r) {
        $name = trim(($r->surname ?? '') . ' ' . ($r->firstname ?? ''));
        if ($name === '') {
            $name = trim(($r->fullname ?? '') . ' ' . ($r->othername ?? ''));
        }
        $emp = (string) ($r->biotime_emp_code ?? '');
        $ihris = (string) ($r->ihris_pid ?? '');
        $card = (string) ($r->card_number ?? '');
        $btn = '';
        if ($emp !== '' || $ihris !== '') {
            $btn = '<button type="button" class="btn btn-sm btn-primary force-enroll-btn"'
                . ' data-card="' . htmlspecialchars($emp !== '' ? $emp : $card, ENT_QUOTES, 'UTF-8') . '"'
                . ' data-ihris="' . htmlspecialchars($ihris, ENT_QUOTES, 'UTF-8') . '">'
                . '<i class="fas fa-user-plus"></i> Force Enroll</button>';
        } else {
            $btn = '<span class="text-muted">No emp code</span>';
        }
        $data[] = [
            $n++,
            htmlspecialchars(str_replace('person|', '', $ihris)),
            htmlspecialchars($name),
            htmlspecialchars($r->job ?? ''),
            htmlspecialchars($card),
            htmlspecialchars($emp),
            $btn,
        ];
    }

    return [
        'draw' => $p['draw'],
        'recordsTotal' => $total,
        'recordsFiltered' => $filtered,
        'data' => $data,
    ];
}

/**
 * Server-side users needing BioTime facility/job update.
 */
public function get_needs_update_datatable()
{
    $p = $this->_dt_params();
    $facility = $this->db->escape_str($this->facility);
    $resolved = $this->sql_resolved_emp_code('i');
    $from = "FROM ihrisdata i
         INNER JOIN biotime_enrollment be ON be.emp_code = {$resolved}
         WHERE i.facility_id <> be.biotime_fac_id
           AND (i.facility_id = '$facility' OR be.biotime_fac_id = '$facility')";
    $search_sql = '';
    if ($p['search'] !== '') {
        $s = $this->db->escape_like_str($p['search']);
        $search_sql = " AND (i.ihris_pid LIKE '%$s%' OR i.surname LIKE '%$s%' OR i.firstname LIKE '%$s%' OR i.job LIKE '%$s%' OR i.card_number LIKE '%$s%' OR i.facility LIKE '%$s%' OR be.emp_code LIKE '%$s%' OR be.biotime_fac_id LIKE '%$s%')";
    }

    $total = (int) $this->db->query("SELECT COUNT(*) AS c $from")->row()->c;
    $filtered = (int) $this->db->query("SELECT COUNT(*) AS c $from $search_sql")->row()->c;

    $order_map = [
        1 => 'i.ihris_pid',
        2 => 'i.surname',
        3 => 'i.job',
        4 => 'i.card_number',
        5 => 'be.emp_code',
        6 => 'i.facility',
        7 => 'be.biotime_fac_id',
    ];
    $order_by = isset($order_map[$p['order_col']]) ? $order_map[$p['order_col']] : 'i.surname';
    $sql = "SELECT i.*,
                i.facility_id AS new_facility,
                i.facility AS new_fname,
                be.emp_code,
                be.biotime_emp_id,
                be.biotime_fac_id,
                {$resolved} AS biotime_emp_code
         $from $search_sql
         ORDER BY $order_by {$p['order_dir']}
         LIMIT {$p['length']} OFFSET {$p['start']}";
    $rows = $this->db->query($sql)->result();

    $data = [];
    $n = $p['start'] + 1;
    foreach ($rows as $r) {
        $name = trim(($r->surname ?? '') . ' ' . ($r->firstname ?? ''));
        $emp = (string) ($r->emp_code ?? $r->biotime_emp_code ?? '');
        $ihris = (string) ($r->ihris_pid ?? '');
        $card = (string) ($r->card_number ?? '');
        $btn = '<button type="button" class="btn btn-sm btn-warning force-update-btn"'
            . ' data-card="' . htmlspecialchars($emp !== '' ? $emp : $card, ENT_QUOTES, 'UTF-8') . '"'
            . ' data-ihris="' . htmlspecialchars($ihris, ENT_QUOTES, 'UTF-8') . '">'
            . '<i class="fas fa-sync"></i> Force Update</button>';
        $data[] = [
            $n++,
            htmlspecialchars(str_replace('person|', '', $ihris)),
            htmlspecialchars($name),
            htmlspecialchars($r->job ?? ''),
            htmlspecialchars($card),
            htmlspecialchars($emp),
            htmlspecialchars($r->new_fname ?? $r->facility ?? ''),
            htmlspecialchars($r->biotime_fac_id ?? ''),
            $btn,
        ];
    }

    return [
        'draw' => $p['draw'],
        'recordsTotal' => $total,
        'recordsFiltered' => $filtered,
        'data' => $data,
    ];
}

/**
 * Staff at this facility who are not yet in BioTime (fingerprints_staging).
 * Uses resolved emp_code: UCMB → 4253+person id; else numeric card; else bare person id.
 */
public function get_new_users(){
    $facility = $this->db->escape_str($this->facility);
    $resolved = $this->sql_resolved_emp_code('');
    $query = $this->db->query(
        "SELECT * FROM ihrisdata
         WHERE facility_id = '$facility'
           AND {$resolved} <> ''
           AND {$resolved} NOT IN (
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
    $resolved = $this->sql_resolved_emp_code('i');
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
         INNER JOIN biotime_enrollment be ON be.emp_code = {$resolved}
         WHERE i.facility_id <> be.biotime_fac_id
           AND (i.facility_id = '$facility' OR be.biotime_fac_id = '$facility')
         ORDER BY i.surname, i.firstname"
    );
    return $query ? $query->result() : [];
}

/**
 * Single transfer candidate by emp/card/person id (for force update).
 */
public function get_transfer_by_card($card_number){
    $card = $this->db->escape_str(trim((string) $card_number));
    if ($card === '') {
        return null;
    }
    $resolved = $this->sql_resolved_emp_code('i');
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
         INNER JOIN biotime_enrollment be ON be.emp_code = {$resolved}
         WHERE ({$resolved} = '$card' OR i.card_number = '$card' OR be.emp_code = '$card')
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
    if ($query && $query->num_rows()) {
        return $query->row();
    }
    $esc = $this->db->escape_str($card);
    $person = $this->sql_person_emp_code('');
    $resolved = $this->sql_resolved_emp_code('');
    $q2 = $this->db->query(
        "SELECT * FROM ihrisdata
         WHERE {$resolved} = '$esc'
            OR {$person} = '$esc'
         LIMIT 1"
    );
    return ($q2 && $q2->num_rows()) ? $q2->row() : null;
}

public function get_ihris_by_pid($ihris_pid){
    $pid = trim((string) $ihris_pid);
    if ($pid === '') {
        return null;
    }
    $query = $this->db->get_where('ihrisdata', ['ihris_pid' => $pid], 1);
    return ($query && $query->num_rows()) ? $query->row() : null;
}

/**
 * Resolve BioTime emp_code for a staff row (same rules as enrollment).
 */
public function resolve_emp_code_for_staff($staff)
{
    if (empty($staff) || empty($staff->ihris_pid)) {
        return '';
    }
    $pid = $this->db->escape_str($staff->ihris_pid);
    $resolved = $this->sql_resolved_emp_code('');
    $q = $this->db->query("SELECT {$resolved} AS emp FROM ihrisdata WHERE ihris_pid = '$pid' LIMIT 1");
    if ($q && $q->num_rows()) {
        return trim((string) $q->row()->emp);
    }
    return '';
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

