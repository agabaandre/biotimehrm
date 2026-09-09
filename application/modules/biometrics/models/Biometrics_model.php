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
        
        // Logged-in facility only (same keys as dashboard / filters).
        $fac = trim((string) $this->session->userdata('facility'));
        if ($fac === '') {
            $fac = trim((string) $this->session->userdata('facility_id'));
        }
        $this->facility = ($fac !== '') ? $fac : null;
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

    /**
     * Facility id for the logged-in user (empty = refuse unscoped queries).
     */
    protected function _facility_id()
    {
        return trim((string) $this->facility);
    }

    /**
     * Empty DataTables payload when facility is missing.
     */
    protected function _empty_datatable($draw = 0)
    {
        return [
            'draw' => (int) $draw,
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'facility' => '',
            'error' => 'No facility in session — switch facility or log in again.',
        ];
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
  $facility = $this->db->escape_str($this->_facility_id());
  if ($facility === '') {
      return [];
  }
  // Query base tables. Multi-device areas share facilityId; enrolled = device or template marker.
  $pred = $this->sql_fingerprint_enrolled_predicate('f');
  $query = $this->db->query(
      "SELECT i.ihris_pid,
              CONCAT(i.surname, ' ', i.firstname) AS fullname,
              i.othername,
              i.facility,
              f.device,
              i.job,
              f.card_number,
              f.att_status,
              f.last_gen
       FROM fingerprints f
       INNER JOIN ihrisdata i ON i.card_number = f.card_number
       WHERE f.facilityId = '$facility'
         AND {$pred}"
  );
  return $query ? $query->result() : [];
}

/**
 * Fast counts for biometrics control panel (avoids loading full result sets).
 */
public function count_enrolled()
{
    $facility = $this->db->escape_str($this->_facility_id());
    if ($facility === '') {
        return 0;
    }
    $pred = $this->sql_fingerprint_enrolled_predicate('f');
    $row = $this->db->query(
        "SELECT COUNT(*) AS c
         FROM fingerprints f
         INNER JOIN ihrisdata i ON i.card_number = f.card_number
         WHERE f.facilityId = '$facility'
           AND {$pred}"
    )->row();
    return $row ? (int) $row->c : 0;
}

public function count_new_users()
{
    $facility = $this->_facility_id();
    if ($facility === '') {
        return 0;
    }
    $from = $this->_unenrolled_from_sql($facility);
    $row = $this->db->query("SELECT COUNT(*) AS c $from")->row();
    return $row ? (int) $row->c : 0;
}

public function count_needs_update()
{
    $facility = $this->_facility_id();
    if ($facility === '') {
        return 0;
    }
    $union = $this->_needs_update_union_sql($facility, 'be.emp_code', '');
    $row = $this->db->query("SELECT COUNT(*) AS c FROM ({$union}) needs_upd")->row();
    return $row ? (int) $row->c : 0;
}

/**
 * SQL: bare / UCMB-prefixed person emp_code from ihris_pid.
 * Used as BioTime emp_code for all NEW enrollments.
 * UCMB-person|123 → 4253123; person|123 → 123.
 */
public function sql_person_emp_code($alias = '')
{
    $p = ($alias !== '' && $alias !== null) ? preg_replace('/[^a-zA-Z0-9_]/', '', $alias) . '.' : '';
    // Bare numeric ihris_pid, person|123, or UCMB-person|123 → 4253123
    return "CASE
        WHEN {$p}ihris_pid LIKE '%UCMB%' THEN CONCAT('4253', TRIM(SUBSTRING_INDEX({$p}ihris_pid, 'person|', -1)))
        WHEN {$p}ihris_pid LIKE '%person|%' THEN TRIM(SUBSTRING_INDEX({$p}ihris_pid, 'person|', -1))
        WHEN TRIM({$p}ihris_pid) REGEXP '^[0-9]+$' THEN TRIM({$p}ihris_pid)
        ELSE TRIM(SUBSTRING_INDEX({$p}ihris_pid, 'person|', -1))
    END";
}

/**
 * SQL: emp_code for new enrollment (= iHRIS person id rule).
 */
public function sql_resolved_emp_code($alias = '')
{
    return $this->sql_person_emp_code($alias);
}

/**
 * Match biotime emp_code ← ihris identifiers (index-friendly).
 * Prefer equality on indexed columns (card_number, ihris_pid, emp_code) instead of
 * CASE/TRIM on the iHRIS side, which prevents index use and slows Needs Update.
 */
public function sql_emp_code_match_any($biotimeEmpExpr, $alias = 'i')
{
    // Legacy signature kept: when expr is "be.emp_code", build join against alias i.
    $i = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $alias);
    if ($i === '') {
        $i = 'i';
    }
    $expr = trim((string) $biotimeEmpExpr);
    // If caller passes be.emp_code, use reverse equalities that hit indexes.
    if (preg_match('/^[a-zA-Z0-9_]+\.emp_code$/', $expr)) {
        return $this->sql_enrollment_to_ihris_on(explode('.', $expr)[0], $i);
    }
    $person = $this->sql_person_emp_code($i);
    return "("
        . "{$expr} = ({$person})"
        . " OR (NULLIF(TRIM({$i}.card_number), '') IS NOT NULL AND {$expr} = TRIM({$i}.card_number))"
        . " OR (NULLIF(TRIM({$i}.ipps), '') IS NOT NULL AND {$expr} = TRIM({$i}.ipps))"
        . ")";
}

/**
 * INNER JOIN condition: biotime_enrollment → ihrisdata via indexed keys.
 */
public function sql_enrollment_to_ihris_on($beAlias = 'be', $iAlias = 'i')
{
    $be = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $beAlias);
    $i = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $iAlias);
    if ($be === '') {
        $be = 'be';
    }
    if ($i === '') {
        $i = 'i';
    }
    return "("
        . "{$i}.card_number = {$be}.emp_code"
        . " OR {$i}.ipps = {$be}.emp_code"
        . " OR {$i}.ihris_pid = CONCAT('person|', {$be}.emp_code)"
        . " OR ({$be}.emp_code LIKE '4253%' AND CHAR_LENGTH({$be}.emp_code) > 4"
        . " AND {$i}.ihris_pid = CONCAT('UCMB-person|', SUBSTRING({$be}.emp_code, 5)))"
        . ")";
}

/**
 * Needs-update match branches (UNION) — each ON clause is sargable.
 *
 * @return string[]
 */
protected function _needs_update_match_ons()
{
    return [
        'i.card_number = be.emp_code',
        "(NULLIF(i.ipps, '') IS NOT NULL AND i.ipps = be.emp_code)",
        "i.ihris_pid = CONCAT('person|', be.emp_code)",
        "(be.emp_code LIKE '4253%' AND CHAR_LENGTH(be.emp_code) > 4 AND i.ihris_pid = CONCAT('UCMB-person|', SUBSTRING(be.emp_code, 5)))",
    ];
}

/**
 * Build UNION of index-friendly needs-update SELECTs.
 */
protected function _needs_update_union_sql($facility, $selectList, $searchSql = '')
{
    $esc = $this->db->escape_str($facility);
    $parts = [];
    foreach ($this->_needs_update_match_ons() as $on) {
        $parts[] = "SELECT {$selectList}
            FROM biotime_enrollment be
            INNER JOIN ihrisdata i ON {$on}
            WHERE i.facility_id <> be.biotime_fac_id
              AND (i.facility_id = '{$esc}' OR be.biotime_fac_id = '{$esc}')
              {$searchSql}";
    }
    return implode("\nUNION\n", $parts);
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
 * Server-side enrolled users — facility scoped via fingerprints + ihrisdata (not the view).
 */
public function get_enrolled_datatable()
{
    $p = $this->_dt_params();
    $facility = $this->_facility_id();
    if ($facility === '') {
        return $this->_empty_datatable($p['draw']);
    }
    $esc = $this->db->escape_str($facility);
    $pred = $this->sql_fingerprint_enrolled_predicate('f');
    $person = $this->sql_person_emp_code('i');
    $from = "FROM fingerprints f
         INNER JOIN ihrisdata i ON (
                i.card_number = f.card_number
             OR f.card_number = ({$person})
             OR (NULLIF(i.ipps, '') IS NOT NULL AND f.card_number = i.ipps)
         )
         WHERE f.facilityId = '$esc'
           AND {$pred}";
    $where_extra = '';
    if ($p['search'] !== '') {
        $s = $this->db->escape_like_str($p['search']);
        $where_extra = " AND (i.ihris_pid LIKE '%$s%' OR i.surname LIKE '%$s%' OR i.firstname LIKE '%$s%' OR i.othername LIKE '%$s%' OR i.job LIKE '%$s%' OR f.card_number LIKE '%$s%' OR i.facility LIKE '%$s%' OR f.device LIKE '%$s%')";
    }

    $total = (int) $this->db->query("SELECT COUNT(*) AS c $from")->row()->c;
    $filtered = (int) $this->db->query("SELECT COUNT(*) AS c $from $where_extra")->row()->c;

    $order_map = [
        1 => 'i.ihris_pid',
        2 => 'i.surname',
        3 => 'i.facility',
        4 => 'f.device',
        5 => 'i.job',
        6 => 'f.card_number',
        7 => 'f.att_status',
    ];
    $order_by = isset($order_map[$p['order_col']]) ? $order_map[$p['order_col']] : 'i.surname';
    $sql = "SELECT i.ihris_pid, i.surname, i.firstname, i.othername, i.facility, i.job,
                   f.device, f.card_number, f.att_status
            $from $where_extra
            ORDER BY $order_by {$p['order_dir']}
            LIMIT {$p['length']} OFFSET {$p['start']}";
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
            htmlspecialchars(trim(($r->surname ?? '') . ' ' . ($r->firstname ?? '') . ' ' . ($r->othername ?? ''))),
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
        'facility' => $facility,
    ];
}

/**
 * SQL fragment: fingerprints row counts as enrolled (device SN, BIO-TEMPLATE, or template summary).
 * Multi-device facilities share facilityId (= area_code); one row covers all terminals in the area.
 */
public function sql_fingerprint_enrolled_predicate($fAlias = 'f')
{
    $f = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $fAlias);
    if ($f === '') {
        $f = 'f';
    }
    return "("
        . "({$f}.device IS NOT NULL AND TRIM({$f}.device) <> '' AND TRIM({$f}.device) <> '-')"
        . " OR ({$f}.fingerprint IS NOT NULL AND TRIM({$f}.fingerprint) <> '' AND TRIM({$f}.fingerprint) <> '-')"
        . ")";
}

/**
 * SQL: staff already device-/template-enrolled at facility (any matching card/person/ipps).
 */
public function sql_exists_fingerprint_enrolled($facilityEscaped, $iAlias = 'i', $personSql = null)
{
    $i = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $iAlias);
    if ($i === '') {
        $i = 'i';
    }
    if ($personSql === null) {
        $personSql = $this->sql_person_emp_code($i);
    }
    $pred = $this->sql_fingerprint_enrolled_predicate('f');
    return "EXISTS (
                SELECT 1 FROM fingerprints f
                WHERE f.facilityId = '{$facilityEscaped}'
                  AND {$pred}
                  AND (
                        (NULLIF({$i}.card_number, '') IS NOT NULL AND f.card_number = {$i}.card_number)
                     OR f.card_number = ({$personSql})
                     OR (NULLIF({$i}.ipps, '') IS NOT NULL AND f.card_number = {$i}.ipps)
                  )
           )";
}
/**
 * FROM/WHERE for unenrolled (New Users): facility staff who still need a BioTime
 * person-id enrollment and are not already device-/template-enrolled.
 *
 * Excludes only:
 *   - missing/non-numeric person emp_code (cannot create)
 *   - biotime_enrollment under that person emp_code (already created)
 *   - fingerprints enrolled at this facility (device SN, BIO-TEMPLATE, or template summary)
 *
 * Multi-device areas share facilityId (= area_code); one fingerprint row covers all terminals.
 * Legacy card/ipps biotime_enrollment rows alone do not hide candidates.
 */
protected function _unenrolled_from_sql($facility)
{
    $esc = $this->db->escape_str($facility);
    $person = $this->sql_person_emp_code('i');
    $active = '';
    if ($this->db->field_exists('is_active_employee', 'ihrisdata')) {
        $active = ' AND COALESCE(i.is_active_employee, 1) = 1';
    }
    $fpEnrolled = $this->sql_exists_fingerprint_enrolled($esc, 'i', $person);
    return "FROM ihrisdata i
         WHERE i.facility_id = '$esc'
           AND ({$person}) <> ''
           AND ({$person}) REGEXP '^[0-9]+$'
           {$active}
           AND NOT EXISTS (
                SELECT 1 FROM biotime_enrollment be
                WHERE be.emp_code = ({$person})
           )
           AND NOT ({$fpEnrolled})";
}

/**
 * FROM/WHERE for needs-update (legacy helper — prefer UNION helpers for DataTables).
 */
protected function _needs_update_from_sql($facility)
{
    $esc = $this->db->escape_str($facility);
    $on = $this->sql_enrollment_to_ihris_on('be', 'i');
    return "FROM biotime_enrollment be
         INNER JOIN ihrisdata i ON {$on}
         WHERE i.facility_id <> be.biotime_fac_id
           AND (i.facility_id = '$esc' OR be.biotime_fac_id = '$esc')";
}

/**
 * Server-side new (unenrolled) users — facility scoped, resolved emp_code anti-join.
 */
public function get_new_users_datatable()
{
    $p = $this->_dt_params();
    $facility = $this->_facility_id();
    if ($facility === '') {
        return $this->_empty_datatable($p['draw']);
    }
    $resolved = $this->sql_resolved_emp_code('i');
    $from = $this->_unenrolled_from_sql($facility);
    $where_extra = '';
    if ($p['search'] !== '') {
        $s = $this->db->escape_like_str($p['search']);
        $where_extra = " AND (i.ihris_pid LIKE '%$s%' OR i.surname LIKE '%$s%' OR i.firstname LIKE '%$s%' OR i.othername LIKE '%$s%' OR i.job LIKE '%$s%' OR i.card_number LIKE '%$s%' OR ({$resolved}) LIKE '%$s%')";
    }

    $total = (int) $this->db->query("SELECT COUNT(*) AS c $from")->row()->c;
    $filtered = (int) $this->db->query("SELECT COUNT(*) AS c $from $where_extra")->row()->c;

    $order_map = [
        1 => 'i.ihris_pid',
        2 => 'i.surname',
        3 => 'i.job',
        4 => 'i.card_number',
        5 => "({$resolved})",
    ];
    $order_by = isset($order_map[$p['order_col']]) ? $order_map[$p['order_col']] : 'i.surname';
    $sql = "SELECT i.ihris_pid, i.surname, i.firstname, i.othername, i.job, i.card_number,
                   ({$resolved}) AS biotime_emp_code
            $from $where_extra
            ORDER BY $order_by {$p['order_dir']}
            LIMIT {$p['length']} OFFSET {$p['start']}";
    $rows = $this->db->query($sql)->result();

    $data = [];
    $n = $p['start'] + 1;
    foreach ($rows as $r) {
        $name = trim(($r->surname ?? '') . ' ' . ($r->firstname ?? ''));
        if ($name === '' && !empty($r->othername)) {
            $name = trim((string) $r->othername);
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
        'facility' => $facility,
    ];
}

/**
 * Server-side users needing BioTime facility update (facility mismatch only).
 * Uses UNION of indexed emp_code match paths to avoid slow OR/CASE joins.
 */
public function get_needs_update_datatable()
{
    $p = $this->_dt_params();
    $facility = $this->_facility_id();
    if ($facility === '') {
        return $this->_empty_datatable($p['draw']);
    }

    $search_sql = '';
    if ($p['search'] !== '') {
        $s = $this->db->escape_like_str($p['search']);
        $search_sql = " AND (i.ihris_pid LIKE '%$s%' OR i.surname LIKE '%$s%' OR i.firstname LIKE '%$s%' OR i.job LIKE '%$s%' OR i.card_number LIKE '%$s%' OR i.facility LIKE '%$s%' OR be.emp_code LIKE '%$s%' OR be.biotime_fac_id LIKE '%$s%')";
    }

    $selectList = "i.ihris_pid, i.surname, i.firstname, i.job, i.card_number, i.facility_id, i.facility,
                i.facility_id AS new_facility,
                i.facility AS new_fname,
                be.emp_code,
                be.biotime_emp_id,
                be.biotime_fac_id";

    $union = $this->_needs_update_union_sql($facility, $selectList, $search_sql);

    // One count when there is no search filter
    $countSql = "SELECT COUNT(*) AS c FROM ({$union}) needs_upd";
    $filtered = (int) $this->db->query($countSql)->row()->c;
    $total = ($search_sql === '') ? $filtered : (int) $this->db->query(
        "SELECT COUNT(*) AS c FROM (" . $this->_needs_update_union_sql($facility, $selectList, '') . ") needs_upd_all"
    )->row()->c;

    $order_map = [
        1 => 'ihris_pid',
        2 => 'surname',
        3 => 'job',
        4 => 'card_number',
        5 => 'emp_code',
        6 => 'facility',
        7 => 'biotime_fac_id',
        8 => 'facility_id',
    ];
    $order_by = isset($order_map[$p['order_col']]) ? $order_map[$p['order_col']] : 'surname';
    $sql = "SELECT * FROM ({$union}) needs_upd
         ORDER BY {$order_by} {$p['order_dir']}
         LIMIT {$p['length']} OFFSET {$p['start']}";
    $rows = $this->db->query($sql)->result();

    $data = [];
    $n = $p['start'] + 1;
    foreach ($rows as $r) {
        $name = trim(($r->surname ?? '') . ' ' . ($r->firstname ?? ''));
        $emp = (string) ($r->emp_code ?? '');
        $ihris = (string) ($r->ihris_pid ?? '');
        $card = (string) ($r->card_number ?? '');
        $ihrisFac = (string) ($r->new_fname ?? $r->facility ?? '');
        $ihrisFacId = (string) ($r->new_facility ?? $r->facility_id ?? '');
        $bioFac = (string) ($r->biotime_fac_id ?? '');
        $reason = 'Facility mismatch: iHRIS '
            . ($ihrisFac !== '' ? $ihrisFac : '(unknown)')
            . ' (' . $ihrisFacId . ') ≠ BioTime facility ' . $bioFac;
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
            htmlspecialchars($ihrisFac) . ' <small class="text-muted">(' . htmlspecialchars($ihrisFacId) . ')</small>',
            htmlspecialchars($bioFac),
            htmlspecialchars($reason),
            $btn,
        ];
    }

    return [
        'draw' => $p['draw'],
        'recordsTotal' => $total,
        'recordsFiltered' => $filtered,
        'data' => $data,
        'facility' => $facility,
        'criteria' => 'Needs update when iHRIS facility_id differs from biotime_enrollment.biotime_fac_id (matched by card, ipps, or person id).',
    ];
}

/**
 * Staff at this facility who are not yet in BioTime (staging or enrollment).
 */
public function get_new_users(){
    $facility = $this->_facility_id();
    if ($facility === '') {
        return [];
    }
    $resolved = $this->sql_resolved_emp_code('i');
    $from = $this->_unenrolled_from_sql($facility);
    $query = $this->db->query(
        "SELECT i.*, ({$resolved}) AS biotime_emp_code
         $from
         ORDER BY i.surname, i.firstname"
    );
    return $query ? $query->result() : [];
}

/**
 * Enrolled BioTime users whose iHRIS facility no longer matches biotime_enrollment.
 */
public function get_users_needing_update(){
    $facility = $this->_facility_id();
    if ($facility === '') {
        return [];
    }
    $selectList = "i.*,
                i.facility_id AS new_facility,
                i.facility AS new_fname,
                be.id AS enrollment_row_id,
                be.emp_code,
                be.biotime_emp_id,
                be.biotime_facility_id AS biotime_area_id,
                be.biotime_fac_id,
                be.last_update AS enrollment_last_update";
    $union = $this->_needs_update_union_sql($facility, $selectList, '');
    $query = $this->db->query(
        "SELECT * FROM ({$union}) needs_upd
         ORDER BY surname, firstname"
    );
    return $query ? $query->result() : [];
}

/**
 * Single transfer candidate by emp/card/person id (for force update).
 */
public function get_transfer_by_card($card_number){
    $card = trim((string) $card_number);
    if ($card === '') {
        return null;
    }
    $esc = $this->db->escape_str($card);
    $select = "i.*,
                i.facility_id AS new_facility,
                i.facility AS new_fname,
                be.id AS enrollment_row_id,
                be.emp_code,
                be.biotime_emp_id,
                be.biotime_facility_id AS biotime_area_id,
                be.biotime_fac_id,
                be.last_update AS enrollment_last_update";

    // Prefer direct emp_code hit (indexed), then card/ipps/person joins
    $q = $this->db->query(
        "SELECT {$select}
         FROM biotime_enrollment be
         INNER JOIN ihrisdata i ON (
                i.card_number = be.emp_code
             OR i.ipps = be.emp_code
             OR i.ihris_pid = CONCAT('person|', be.emp_code)
             OR (be.emp_code LIKE '4253%' AND CHAR_LENGTH(be.emp_code) > 4
                 AND i.ihris_pid = CONCAT('UCMB-person|', SUBSTRING(be.emp_code, 5)))
         )
         WHERE be.emp_code = '$esc'
           AND i.facility_id <> be.biotime_fac_id
         LIMIT 1"
    );
    if ($q && $q->num_rows()) {
        return $q->row();
    }

    $person = $this->sql_person_emp_code('i');
    $q2 = $this->db->query(
        "SELECT {$select}
         FROM ihrisdata i
         INNER JOIN biotime_enrollment be ON (
                be.emp_code = i.card_number
             OR (NULLIF(i.ipps, '') IS NOT NULL AND be.emp_code = i.ipps)
             OR be.emp_code = ({$person})
         )
         WHERE (i.card_number = '$esc' OR i.ipps = '$esc' OR ({$person}) = '$esc' OR i.ihris_pid = '$esc')
           AND i.facility_id <> be.biotime_fac_id
         LIMIT 1"
    );
    return ($q2 && $q2->num_rows()) ? $q2->row() : null;
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
    $q2 = $this->db->query(
        "SELECT * FROM ihrisdata
         WHERE ({$person}) = '$esc'
            OR TRIM(ipps) = '$esc'
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

