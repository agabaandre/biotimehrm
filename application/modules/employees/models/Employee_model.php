<?php defined('BASEPATH') or exit('No direct script access allowed');
class Employee_model extends CI_Model
{   
    protected $facility;
    protected $department;
    protected $division;
    protected $unit;
    protected $filters;
    protected $ufilters;
    protected $distfilters;

    public  function __construct()
    {
        parent::__construct();
        $this->facility = $this->session->userdata['facility'];
    }
    public function get_employees($filters)
    {
        // Use Query Builder for better performance and security
        $this->db->select('ihris_pid, surname, employment_terms, firstname, othername, job, telephone, mobile, department, facility, district, nin, card_number, birth_date, cadre, gender, facility_id, ipps, email');
        $this->db->from('ihrisdata');
        $this->db->where($filters);
        $this->db->distinct();
        
        $query = $this->db->get();
        return $query->result();
    }
    
    /**
     * Get total count of employees for pagination
     */
    public function get_employees_count($filters, $globalSearch = '', $include_inactive = false)
    {
        $this->db->select('COUNT(DISTINCT ihris_pid) as total');
        $this->db->from('ihrisdata');
        $this->db->where($filters);
        if (!$include_inactive && $this->db->field_exists('is_active_employee', 'ihrisdata')) {
            $this->db->where('(COALESCE(is_active_employee, 1) = 1)');
        }
        
        // Apply global search filter if provided
        if (!empty($globalSearch)) {
            $this->db->group_start();
            $this->db->like('ihris_pid', $globalSearch);
            $this->db->or_like('surname', $globalSearch);
            $this->db->or_like('firstname', $globalSearch);
            $this->db->or_like('othername', $globalSearch);
            $this->db->or_like('job', $globalSearch);
            $this->db->or_like('facility', $globalSearch);
            $this->db->or_like('department', $globalSearch);
            $this->db->or_like('nin', $globalSearch);
            $this->db->or_like('card_number', $globalSearch);
            $this->db->or_like('mobile', $globalSearch);
            $this->db->or_like('telephone', $globalSearch);
            $this->db->or_like('email', $globalSearch);
            $this->db->or_like('ipps', $globalSearch);
            $this->db->group_end();
        }
        
        $query = $this->db->get();
        $result = $query->row();
        return $result->total;
    }
    
    /**
     * Get employees with AJAX support for DataTables (main employees page)
     */
    public function get_employees_ajax($filters, $start = 0, $length = 10, $search = '', $order_column = 0, $order_dir = 'asc', $globalSearch = '', $include_inactive = false)
    {
        $has_active = $this->db->field_exists('is_active_employee', 'ihrisdata');
        $select = 'ihris_pid, surname, employment_terms, firstname, othername, job, telephone, mobile, department, department_id, facility, district, district_id, nin, card_number, birth_date, cadre, gender, facility_id, ipps, email, is_incharge';
        if ($has_active) {
            $select .= ', is_active_employee';
        }
        $this->db->select($select);
        $this->db->from('ihrisdata');
        $this->db->where($filters);
        if (!$include_inactive && $has_active) {
            $this->db->where('(COALESCE(is_active_employee, 1) = 1)');
        }
        
        // Apply global search filter
        if (!empty($globalSearch)) {
            $this->db->group_start();
            $this->db->like('ihris_pid', $globalSearch);
            $this->db->or_like('surname', $globalSearch);
            $this->db->or_like('firstname', $globalSearch);
            $this->db->or_like('othername', $globalSearch);
            $this->db->or_like('job', $globalSearch);
            $this->db->or_like('facility', $globalSearch);
            $this->db->or_like('department', $globalSearch);
            $this->db->or_like('nin', $globalSearch);
            $this->db->or_like('card_number', $globalSearch);
            $this->db->or_like('mobile', $globalSearch);
            $this->db->or_like('telephone', $globalSearch);
            $this->db->or_like('email', $globalSearch);
            $this->db->or_like('ipps', $globalSearch);
            $this->db->group_end();
        }
        
        // Apply DataTables search filter
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('ihris_pid', $search);
            $this->db->or_like('surname', $search);
            $this->db->or_like('firstname', $search);
            $this->db->or_like('othername', $search);
            $this->db->or_like('job', $search);
            $this->db->or_like('facility', $search);
            $this->db->or_like('department', $search);
            $this->db->or_like('nin', $search);
            $this->db->or_like('card_number', $search);
            $this->db->group_end();
        }
        
        // Apply ordering (status column = is_active_employee)
        $columns = ['ihris_pid', 'nin', 'surname', 'gender', 'birth_date', 'ipps', 'card_number', 'telephone', 'email', 'department', 'job', 'employment_terms', 'is_active_employee'];
        if (isset($columns[$order_column])) {
            $order_col = $columns[$order_column];
            if ($order_col === 'is_active_employee' && !$has_active) {
                $order_col = 'surname';
            }
            $this->db->order_by($order_col, $order_dir);
        }
        
        // Apply pagination
        $this->db->limit($length, $start);
        
        $query = $this->db->get();
        $result = $query->result();
        
        // Format data for DataTables (status/status_label for display: 0 = Former Staff, 1 = Active)
        $formatted_data = [];
        foreach ($result as $row) {
            $active = ($has_active && isset($row->is_active_employee)) ? (int) $row->is_active_employee : 1;
            $status = $active; // 1 = active, 0 = former staff
            $formatted_data[] = [
                'ihris_pid' => str_replace('person|', '', $row->ihris_pid),
                'nin' => $row->nin,
                'fullname' => trim($row->surname . ' ' . $row->firstname . ' ' . ($row->othername ?? '')),
                'gender' => $row->gender,
                'birth_date' => $row->birth_date,
                'ipps' => (!is_null($row->ipps) && is_numeric($row->ipps)) ? ((int)$row->ipps * 1) : 'N/A',
                'card_number' => $row->card_number,
                'phone' => !empty($row->mobile) ? $row->mobile : ($row->telephone ?? 'N/A'),
                'email' => $row->email ?? 'N/A',
                'department' => $row->department,
                'job' => $row->job,
                'employment_terms' => str_replace("CContract", "Central Contract", str_replace("LContract", "Local Contract", str_replace("employment_terms|", "", $row->employment_terms ?? ''))),
                'status' => $status,
                'status_label' => $status === 0 ? 'Former Staff' : 'Active',
                'is_incharge' => isset($row->is_incharge) ? (int) $row->is_incharge : 0,
                'facility_id' => $row->facility_id ?? '',
                'district_id' => $row->district_id ?? '',
                'facility' => $row->facility ?? '',
                'department_id' => $row->department_id ?? ''
            ];
        }
        
        return $formatted_data;
    }

    /**
     * Fetch a batch of staff for streaming export (same filters as get_employees_ajax). Returns array of row arrays.
     */
    public function get_employees_export_batch($filters, $globalSearch, $include_inactive, $offset, $limit = 500)
    {
        $has_active = $this->db->field_exists('is_active_employee', 'ihrisdata');
        $select = 'ihris_pid, surname, employment_terms, firstname, othername, job, telephone, mobile, department, facility, district, nin, card_number, birth_date, cadre, gender, facility_id, ipps, email';
        if ($has_active) {
            $select .= ', is_active_employee';
        }
        $this->db->select($select);
        $this->db->from('ihrisdata');
        $this->db->where($filters);
        if (!$include_inactive && $has_active) {
            $this->db->where('(COALESCE(is_active_employee, 1) = 1)');
        }
        if (!empty($globalSearch)) {
            $this->db->group_start();
            $this->db->like('ihris_pid', $globalSearch);
            $this->db->or_like('surname', $globalSearch);
            $this->db->or_like('firstname', $globalSearch);
            $this->db->or_like('othername', $globalSearch);
            $this->db->or_like('job', $globalSearch);
            $this->db->or_like('facility', $globalSearch);
            $this->db->or_like('department', $globalSearch);
            $this->db->or_like('nin', $globalSearch);
            $this->db->or_like('card_number', $globalSearch);
            $this->db->or_like('mobile', $globalSearch);
            $this->db->or_like('telephone', $globalSearch);
            $this->db->or_like('email', $globalSearch);
            $this->db->or_like('ipps', $globalSearch);
            $this->db->group_end();
        }
        $this->db->order_by('surname', 'asc');
        $this->db->limit($limit, $offset);
        $query = $this->db->get();
        $result = $query->result();
        $rows = [];
        foreach ($result as $row) {
            $active = ($has_active && isset($row->is_active_employee)) ? (int) $row->is_active_employee : 1;
            $rows[] = [
                str_replace('person|', '', $row->ihris_pid),
                $row->nin,
                trim($row->surname . ' ' . $row->firstname . ' ' . ($row->othername ?? '')),
                $row->gender,
                $row->birth_date,
                (!is_null($row->ipps) && is_numeric($row->ipps)) ? ((int)$row->ipps * 1) : 'N/A',
                $row->card_number,
                !empty($row->mobile) ? $row->mobile : ($row->telephone ?? 'N/A'),
                $row->email ?? 'N/A',
                $row->department,
                $row->job,
                str_replace("CContract", "Central Contract", str_replace("LContract", "Local Contract", str_replace("employment_terms|", "", $row->employment_terms ?? ''))),
                $active === 0 ? 'Former Staff' : 'Active'
            ];
        }
        return $rows;
    }

    /**
     * Fetch a batch of district staff for streaming export. Returns array of row arrays.
     */
    public function get_district_employees_export_batch($district, $job, $facility, $search, $include_inactive, $offset, $limit = 500)
    {
        $has_active = $this->db->field_exists('is_active_employee', 'ihrisdata');
        $select = 'ihris_pid, surname, employment_terms, firstname, othername, job, telephone, mobile, department, facility, facility_id, district, nin, card_number, birth_date, gender, email';
        if ($has_active) {
            $select .= ', is_active_employee';
        }
        $this->db->select($select);
        $this->db->from('ihrisdata');
        $this->db->where('district', $district);
        if (!$include_inactive && $has_active) {
            $this->db->where('(COALESCE(is_active_employee, 1) = 1)');
        }
        if (!empty($job)) {
            $this->db->where_in('job_id', $job);
        }
        if (!empty($facility)) {
            $this->db->where_in('facility_id', $facility);
        }
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('ihris_pid', $search);
            $this->db->or_like('surname', $search);
            $this->db->or_like('firstname', $search);
            $this->db->or_like('othername', $search);
            $this->db->or_like('job', $search);
            $this->db->or_like('facility', $search);
            $this->db->or_like('department', $search);
            $this->db->or_like('nin', $search);
            $this->db->or_like('card_number', $search);
            $this->db->group_end();
        }
        $this->db->order_by('surname', 'asc');
        $this->db->limit($limit, $offset);
        $query = $this->db->get();
        $result = $query->result();
        $rows = [];
        foreach ($result as $row) {
            $active = ($has_active && isset($row->is_active_employee)) ? (int) $row->is_active_employee : 1;
            $rows[] = [
                str_replace('person|', '', $row->ihris_pid),
                $row->nin,
                trim($row->surname . ' ' . $row->firstname . ' ' . ($row->othername ?? '')),
                $row->gender,
                $row->birth_date,
                !empty($row->mobile) ? $row->mobile : ($row->telephone ?? 'N/A'),
                $row->email ?? 'N/A',
                $row->facility,
                $row->department,
                $row->job,
                str_replace("CContract", "Central Contract", str_replace("LContract", "Local Contract", str_replace("employment_terms|", "", $row->employment_terms ?? ''))),
                $row->card_number,
                $active === 0 ? 'Former Staff' : 'Active'
            ];
        }
        return $rows;
    }

    /**
     * Count district staff for export (same filters as get_district_employees_export_batch including search).
     */
    public function get_district_employees_export_count($district, $job, $facility, $search, $include_inactive)
    {
        $has_active = $this->db->field_exists('is_active_employee', 'ihrisdata');
        $this->db->from('ihrisdata');
        $this->db->where('district', $district);
        if (!$include_inactive && $has_active) {
            $this->db->where('(COALESCE(is_active_employee, 1) = 1)');
        }
        if (!empty($job)) {
            $this->db->where_in('job_id', $job);
        }
        if (!empty($facility)) {
            $this->db->where_in('facility_id', $facility);
        }
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('ihris_pid', $search);
            $this->db->or_like('surname', $search);
            $this->db->or_like('firstname', $search);
            $this->db->or_like('othername', $search);
            $this->db->or_like('job', $search);
            $this->db->or_like('facility', $search);
            $this->db->or_like('department', $search);
            $this->db->or_like('nin', $search);
            $this->db->or_like('card_number', $search);
            $this->db->group_end();
        }
        return $this->db->count_all_results();
    }

    public function district_employees($district, $job, $facility, $count = FALSE, $start=FALSE, $limit=FALSE, $csv=FALSE, $include_inactive = false)
    {
        $has_active = $this->db->field_exists('is_active_employee', 'ihrisdata');
        $select = 'ihris_pid, surname, employment_terms, firstname, othername, job, telephone, mobile, department, department_id, is_incharge, facility, facility_id, district, district_id, nin, card_number, birth_date, cadre, gender, email';
        if ($has_active) {
            $select .= ', is_active_employee';
        }
        $this->db->select($select);
        $this->db->from('ihrisdata');
        $this->db->where('district', $district);
        if (!$include_inactive && $has_active) {
            $this->db->where('(COALESCE(is_active_employee, 1) = 1)');
        }
        
        // Apply job filter
        if (!empty($job)) {
            $this->db->where_in('job_id', $job);
        }
        
        // Apply facility filter
        if (!empty($facility)) {
            $this->db->where_in('facility_id', $facility);
        }
        
        // Apply pagination
        if (!empty($start) && ($csv != 1)) {
            $this->db->limit($limit, $start);
        }
        
        $query = $this->db->get();
        
        if ($count == 'count') {
            return $query->num_rows();
        } else if ($csv == 1) {
            return $query->result_array();
        } else {
            return $query->result();
        }
    }
    
    /**
     * Get district employees with AJAX support for DataTables
     */
    public function district_employees_ajax($district, $job = NULL, $facility = NULL, $start = 0, $length = 10, $search = '', $order_column = 0, $order_dir = 'asc', $include_inactive = false)
    {
        $has_active = $this->db->field_exists('is_active_employee', 'ihrisdata');
        $select = 'ihris_pid, surname, employment_terms, firstname, othername, job, telephone, mobile, department, department_id, is_incharge, facility, facility_id, district, district_id, nin, card_number, birth_date, cadre, gender, email';
        if ($has_active) {
            $select .= ', is_active_employee';
        }
        $this->db->select($select);
        $this->db->from('ihrisdata');
        $this->db->where('district', $district);
        if (!$include_inactive && $has_active) {
            $this->db->where('(COALESCE(is_active_employee, 1) = 1)');
        }
        
        // Apply job filter
        if (!empty($job)) {
            $this->db->where_in('job_id', $job);
        }
        
        // Apply facility filter
        if (!empty($facility)) {
            $this->db->where_in('facility_id', $facility);
        }
        
        // Apply search filter
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('ihris_pid', $search);
            $this->db->or_like('surname', $search);
            $this->db->or_like('firstname', $search);
            $this->db->or_like('othername', $search);
            $this->db->or_like('job', $search);
            $this->db->or_like('facility', $search);
            $this->db->or_like('department', $search);
            $this->db->or_like('nin', $search);
            $this->db->or_like('card_number', $search);
            $this->db->group_end();
        }
        
        // Apply ordering
        $columns = ['ihris_pid', 'nin', 'surname', 'gender', 'birth_date', 'telephone', 'email', 'facility', 'department', 'job', 'employment_terms', 'card_number', 'is_active_employee'];
        if (isset($columns[$order_column])) {
            $order_col = $columns[$order_column];
            if ($order_col === 'is_active_employee' && !$has_active) {
                $order_col = 'surname';
            }
            $this->db->order_by($order_col, $order_dir);
        } else {
            $this->db->order_by('surname', 'asc');
        }
        
        // Apply pagination
        $this->db->limit($length, $start);
        
        $query = $this->db->get();
        $result = $query->result();
        
        // Format data for DataTables (status/status_label: 0 = Former Staff, 1 = Active)
        $formatted_data = [];
        foreach ($result as $index => $staff) {
            $active = ($has_active && isset($staff->is_active_employee)) ? (int) $staff->is_active_employee : 1;
            $status = $active;
            $formatted_data[] = [
                'DT_RowId' => 'row_' . str_replace('person|', '', $staff->ihris_pid),
                'serial' => $start + $index + 1,
                'ihris_pid' => str_replace('person|', '', $staff->ihris_pid),
                'nin' => $staff->nin,
                'fullname' => $fullname = trim($staff->surname . ' ' . $staff->firstname . ' ' . $staff->othername),
                'gender' => $staff->gender,
                'birth_date' => $staff->birth_date,
                'phone' => !empty($staff->mobile) ? $staff->mobile : $staff->telephone,
                'email' => $staff->email,
                'facility' => $staff->facility,
                'department' => $staff->department,
                'job' => $staff->job,
                'employment_terms' => str_replace("CContract", "Central Contract", str_replace("LContract", "Local Contract", str_replace("employment_terms|", "", $staff->employment_terms ?? ''))),
                'card_number' => $staff->card_number,
                'is_incharge' => $staff->is_incharge,
                'facility_id' => $staff->facility_id,
                'district_id' => $staff->district_id,
                'status' => $status,
                'status_label' => $status === 0 ? 'Former Staff' : 'Active'
            ];
        }
        
        return $formatted_data;
    }

    /**
     * Get distinct filter options from ihrisdata for All iHRIS Staff page.
     * Uses query builder and permissive WHERE so options populate from actual table columns.
     */
    public function get_all_ihris_filter_options()
    {
        $opts = ['districts' => [], 'facilities' => [], 'jobs' => [], 'institution_types' => [], 'facility_types' => []];
        if (!$this->db->table_exists('ihrisdata')) {
            return $opts;
        }

        $add_non_empty = function (array &$list, $value, $label = null) {
            if ($value === null || trim((string) $value) === '') {
                return;
            }
            $lbl = $label !== null ? trim((string) $label) : trim((string) $value);
            if ($lbl === '') {
                $lbl = (string) $value;
            }
            $list[] = ['value' => $value, 'label' => $lbl];
        };

        // Districts
        $this->db->reset_query();
        $this->db->distinct();
        $this->db->select('district');
        $this->db->from('ihrisdata');
        $this->db->order_by('district', 'asc');
        $q = $this->db->get();
        if ($q && $q->num_rows() > 0) {
            foreach ($q->result() as $r) {
                $add_non_empty($opts['districts'], $r->district, $r->district);
            }
        }

        // Facilities (facility_id, facility)
        $this->db->reset_query();
        $this->db->distinct();
        $this->db->select('facility_id, facility');
        $this->db->from('ihrisdata');
        $this->db->order_by('facility', 'asc');
        $q = $this->db->get();
        if ($q && $q->num_rows() > 0) {
            foreach ($q->result() as $r) {
                $val = isset($r->facility_id) ? $r->facility_id : '';
                $lbl = (isset($r->facility) && trim((string) $r->facility) !== '') ? $r->facility : $val;
                $add_non_empty($opts['facilities'], $val, $lbl);
            }
        }

        // Jobs (job_id, job)
        $this->db->reset_query();
        $this->db->distinct();
        $this->db->select('job_id, job');
        $this->db->from('ihrisdata');
        $this->db->order_by('job', 'asc');
        $q = $this->db->get();
        if ($q && $q->num_rows() > 0) {
            foreach ($q->result() as $r) {
                $val = isset($r->job_id) ? $r->job_id : '';
                $lbl = (isset($r->job) && trim((string) $r->job) !== '') ? $r->job : $val;
                $add_non_empty($opts['jobs'], $val, $lbl);
            }
        }

        // Institution type (institution_type or institutiontype_name)
        $col_inst = $this->db->field_exists('institution_type', 'ihrisdata') ? 'institution_type' : ($this->db->field_exists('institutiontype_name', 'ihrisdata') ? 'institutiontype_name' : null);
        if ($col_inst) {
            $this->db->reset_query();
            $this->db->distinct();
            $this->db->select($col_inst);
            $this->db->from('ihrisdata');
            $this->db->order_by($col_inst, 'asc');
            $q = $this->db->get();
            if ($q && $q->num_rows() > 0) {
                foreach ($q->result() as $r) {
                    $v = $r->{$col_inst};
                    $add_non_empty($opts['institution_types'], $v, $v);
                }
            }
        }

        // Facility type (facility_type_id or facility_type)
        $col_ft = $this->db->field_exists('facility_type_id', 'ihrisdata') ? 'facility_type_id' : ($this->db->field_exists('facility_type', 'ihrisdata') ? 'facility_type' : null);
        if ($col_ft) {
            $this->db->reset_query();
            $this->db->distinct();
            $this->db->select($col_ft);
            $this->db->from('ihrisdata');
            $this->db->order_by($col_ft, 'asc');
            $q = $this->db->get();
            if ($q && $q->num_rows() > 0) {
                foreach ($q->result() as $r) {
                    $v = $r->{$col_ft};
                    $add_non_empty($opts['facility_types'], $v, $v);
                }
            }
        }

        return $opts;
    }

    /**
     * Count all iHRIS staff with optional filters (for All iHRIS Staff page).
     */
    public function all_ihris_staff_count($district = null, $facility = null, $job = null, $institution_type = null, $facility_type = null, $search = '', $include_inactive = false)
    {
        $has_active = $this->db->field_exists('is_active_employee', 'ihrisdata');
        $this->db->from('ihrisdata');
        if (!$include_inactive && $has_active) {
            $this->db->where('(COALESCE(is_active_employee, 1) = 1)');
        }
        if ($district !== null && $district !== '') {
            $this->db->where('district', $district);
        }
        if (!empty($facility)) {
            $facility = is_array($facility) ? $facility : array_filter(explode(',', $facility));
            if (!empty($facility)) {
                $this->db->where_in('facility_id', $facility);
            }
        }
        if (!empty($job)) {
            $job = is_array($job) ? $job : array_filter(explode(',', $job));
            if (!empty($job)) {
                $this->db->where_in('job_id', $job);
            }
        }
        $col_inst = $this->db->field_exists('institution_type', 'ihrisdata') ? 'institution_type' : ($this->db->field_exists('institutiontype_name', 'ihrisdata') ? 'institutiontype_name' : null);
        if ($institution_type !== null && $institution_type !== '' && $col_inst) {
            $this->db->where($col_inst, $institution_type);
        }
        $col_ft = $this->db->field_exists('facility_type_id', 'ihrisdata') ? 'facility_type_id' : ($this->db->field_exists('facility_type', 'ihrisdata') ? 'facility_type' : null);
        if ($facility_type !== null && $facility_type !== '' && $col_ft) {
            $this->db->where($col_ft, $facility_type);
        }
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('ihris_pid', $search);
            $this->db->or_like('surname', $search);
            $this->db->or_like('firstname', $search);
            $this->db->or_like('othername', $search);
            $this->db->or_like('job', $search);
            $this->db->or_like('facility', $search);
            $this->db->or_like('department', $search);
            $this->db->or_like('nin', $search);
            $this->db->or_like('card_number', $search);
            $this->db->or_like('mobile', $search);
            $this->db->or_like('telephone', $search);
            $this->db->or_like('email', $search);
            $this->db->group_end();
        }
        return (int) $this->db->count_all_results();
    }

    /**
     * Get all iHRIS staff with AJAX support (All iHRIS Staff page). Same row format as district_employees_ajax.
     */
    public function all_ihris_staff_ajax($district = null, $facility = null, $job = null, $institution_type = null, $facility_type = null, $start = 0, $length = 10, $search = '', $order_column = 0, $order_dir = 'asc', $include_inactive = false)
    {
        $has_active = $this->db->field_exists('is_active_employee', 'ihrisdata');
        $select = 'ihris_pid, surname, employment_terms, firstname, othername, job, telephone, mobile, department, department_id, is_incharge, facility, facility_id, district, district_id, nin, card_number, birth_date, cadre, gender, email';
        if ($has_active) {
            $select .= ', is_active_employee';
        }
        $this->db->select($select);
        $this->db->from('ihrisdata');
        if (!$include_inactive && $has_active) {
            $this->db->where('(COALESCE(is_active_employee, 1) = 1)');
        }
        if ($district !== null && $district !== '') {
            $this->db->where('district', $district);
        }
        if (!empty($facility)) {
            $facility = is_array($facility) ? $facility : array_filter(explode(',', $facility));
            if (!empty($facility)) {
                $this->db->where_in('facility_id', $facility);
            }
        }
        if (!empty($job)) {
            $job = is_array($job) ? $job : array_filter(explode(',', $job));
            if (!empty($job)) {
                $this->db->where_in('job_id', $job);
            }
        }
        $col_inst = $this->db->field_exists('institution_type', 'ihrisdata') ? 'institution_type' : ($this->db->field_exists('institutiontype_name', 'ihrisdata') ? 'institutiontype_name' : null);
        if ($institution_type !== null && $institution_type !== '' && $col_inst) {
            $this->db->where($col_inst, $institution_type);
        }
        $col_ft = $this->db->field_exists('facility_type_id', 'ihrisdata') ? 'facility_type_id' : ($this->db->field_exists('facility_type', 'ihrisdata') ? 'facility_type' : null);
        if ($facility_type !== null && $facility_type !== '' && $col_ft) {
            $this->db->where($col_ft, $facility_type);
        }
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('ihris_pid', $search);
            $this->db->or_like('surname', $search);
            $this->db->or_like('firstname', $search);
            $this->db->or_like('othername', $search);
            $this->db->or_like('job', $search);
            $this->db->or_like('facility', $search);
            $this->db->or_like('department', $search);
            $this->db->or_like('nin', $search);
            $this->db->or_like('card_number', $search);
            $this->db->group_end();
        }
        $columns = ['ihris_pid', 'nin', 'surname', 'gender', 'birth_date', 'telephone', 'email', 'facility', 'department', 'job', 'employment_terms', 'card_number', 'is_active_employee'];
        if (isset($columns[$order_column])) {
            $order_col = $columns[$order_column];
            if ($order_col === 'is_active_employee' && !$has_active) {
                $order_col = 'surname';
            }
            $this->db->order_by($order_col, $order_dir);
        } else {
            $this->db->order_by('surname', 'asc');
        }
        $this->db->limit($length, $start);
        $query = $this->db->get();
        $result = $query->result();
        $formatted_data = [];
        foreach ($result as $index => $staff) {
            $active = ($has_active && isset($staff->is_active_employee)) ? (int) $staff->is_active_employee : 1;
            $formatted_data[] = [
                'DT_RowId' => 'row_' . str_replace('person|', '', $staff->ihris_pid),
                'serial' => $start + $index + 1,
                'ihris_pid' => str_replace('person|', '', $staff->ihris_pid),
                'nin' => $staff->nin,
                'fullname' => trim($staff->surname . ' ' . $staff->firstname . ' ' . $staff->othername),
                'gender' => $staff->gender,
                'birth_date' => $staff->birth_date,
                'phone' => !empty($staff->mobile) ? $staff->mobile : $staff->telephone,
                'email' => $staff->email,
                'facility' => $staff->facility,
                'department' => $staff->department,
                'job' => $staff->job,
                'employment_terms' => str_replace("CContract", "Central Contract", str_replace("LContract", "Local Contract", str_replace("employment_terms|", "", $staff->employment_terms ?? ''))),
                'card_number' => $staff->card_number,
                'is_incharge' => $staff->is_incharge,
                'facility_id' => $staff->facility_id,
                'district_id' => $staff->district_id,
                'district' => isset($staff->district) ? $staff->district : '',
                'status' => $active,
                'status_label' => $active === 0 ? 'Former Staff' : 'Active'
            ];
        }
        return $formatted_data;
    }

    public function get_employee($id = FALSE)
    {
        $this->db->where('ihris_pid', $id);
        $query = $this->db->get('ihrisdata');
        return $query->row();
    }

    /**
     * Set staff active state (0 = inactive/Former Staff, 1 = active). Uses is_active_employee in ihrisdata.
     */
    public function set_staff_status($ihris_pid, $active)
    {
        $pid = is_string($ihris_pid) ? $ihris_pid : ('person|' . $ihris_pid);
        if (strpos($pid, 'person|') !== 0) {
            $pid = 'person|' . $pid;
        }
        $this->db->where('ihris_pid', $pid);
        $this->db->update('ihrisdata', array('is_active_employee' => (int) $active));
        return $this->db->affected_rows() > 0;
    }

    public function get_employee_clock($facility, $staffId)
    {
        $this->db->select('status');
        $this->db->from('clk_log');
        $this->db->where('facility_id', urldecode($facility));
        $this->db->where('ihris_pid', urldecode($staffId));
        $this->db->where('date', date("Y-m-d"));
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
            return $query->row();
        } else {
            return false;
        }
    }
    public function search_employees($facility, $search_term)
    {
        $this->db->select('ihris_pid, firstname, surname, job, facility_id');
        $this->db->where('ihrisdata.facility_id', urldecode($facility))
            ->like('ihris_pid', urldecode($search_term));
        $this->db->or_where('ihrisdata.facility_id', urldecode($facility))
            ->like('firstname', urldecode($search_term));
        $this->db->or_where('ihrisdata.facility_id', urldecode($facility))
            ->like('surname', urldecode($search_term));
        $this->db->order_by('surname', 'asc');
        $this->db->from('ihrisdata');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }
    public function count_employees($facility)
    {
        $department = $this->department;
        $unit = $this->unit;
        // return $this->db->count_all_results('ihrisdata');
        if ($this->department) {
            $this->db->where('department_id', $this->department);
            $this->db->or_where('division', $this->division);
            $this->db->or_where('unit', $this->unit);
        }
        $this->db->where('facility_id', urldecode($facility));
        $this->db->from('ihrisdata');
        $qry = $this->db->get();
        return $qry->num_rows();
    }
    public function get_schedules($employeeId)
    {
        $this->db->select('duty_date, end, allDay, starts, ends, letter');
        $this->db->from('ihrisdata');
        $this->db->join('duty_rosta', 'duty_rosta.ihris_pid = ihrisdata.ihris_pid');
        $this->db->join('schedules', 'schedules.schedule_id = duty_rosta.schedule_id');
        $this->db->where('duty_rosta.ihris_pid', urldecode($employeeId));
        $query = $this->db->get()->result();
        return $query;
    }
    public function count_employees_present($facility)
    {
        $this->db->select('*');
        $this->db->from('actuals');
        if ($this->department) {
            $this->db->where('department_id', $this->department);
        }
        $this->db->where('actuals.facility_id', urldecode($facility));
        $this->db->where('actuals.date', date("Y-m-d"));
        $this->db->where('actuals.schedule_id', '22');
        $qry = $this->db->get();
        return $qry->num_rows();
    }
    public function count_employees_off($facility)
    {
        $this->db->select('*');
        $this->db->from('duty_rosta');
        if ($this->department) {
            $this->db->where('department_id', $this->department);
        }
        $this->db->where('duty_rosta.facility_id', urldecode($facility));
        $this->db->where('duty_rosta.schedule_id', '17');
        $this->db->where('duty_rosta.duty_date', date("Y-m-d"));
        $qry = $this->db->get();
        return $qry->num_rows();
    }
    //Staff On Duty Today
    public function count_employees_working($facility)
    {
        $work_days = array('14', '15', '16');
        $this->db->select('*');
        $this->db->from('duty_rosta');
        if ($this->department) {
            $this->db->where('department_id', $this->department);
        }
        $this->db->where('duty_date', date("Y-m-d"));
        $this->db->where('duty_rosta.facility_id', urldecode($facility));
        $this->db->join('schedules', 'schedules.schedule_id = duty_rosta.schedule_id');
        $this->db->where_in('schedules.schedule_id', $work_days);
        $qry = $this->db->get();
        return $qry->num_rows();
    }
    public function staff_working_today($facility)
    {
        $work_days = array('14', '15', '16');
        $this->db->select('*');
        $this->db->from('duty_rosta');
        if ($this->department) {
            $this->db->where('department_id', $this->department);
        }
        $this->db->where('duty_date', date("Y-m-d"));
        $this->db->where('duty_rosta.facility_id', urldecode($facility));
        $this->db->join('ihrisdata', 'ihrisdata.ihris_pid = duty_rosta.ihris_pid');
        $this->db->join('schedules', 'schedules.schedule_id = duty_rosta.schedule_id');
        $this->db->where_in('schedules.schedule_id', $work_days);
        return $this->db->get()->result_array();
    }
    public function get_schedule_types($type)
    {
        if ($type == 'attendance') {
            $purpose = 'a';
        } else if ($type == 'roster') {
            $purpose = 'r';
        } else {
            die();
        }
        $this->db->select('schedule_id, schedule, letter');
        $this->db->from('schedules');
        $this->db->where('purpose', $purpose);
        return $this->db->get()->result_array();
    }
    public function insert_schedule($data)
    {
        $response = array();
        foreach ($data->workdates as $key => $value) {
            // echo $value;
            $duty = array(
                'entry_id' => $value . '' . $data->ihris_pid,
                'facility_id' => $data->facility_id,
                'ihris_pid' => $data->ihris_pid,
                'schedule_id' => $data->schedule_id,
                'duty_date' => $value,
                'end' => strftime("%Y-%m-%d", strtotime("$value +1 day")),
                'allDay' => 'true',
                'color' => $this->setColor($data->schedule_id)
            );
            $entry_id = $value . '' . $data->ihris_pid;
            $duty_date = $value;
            $end_date = strftime("%Y-%m-%d", strtotime("$value +1 day"));
            $this->db->select('entry_id');
            $this->db->where('entry_id', $entry_id);
            $query = $this->db->get('duty_rosta');
            if ($query->num_rows() == 1) {
                $this->db->query("UPDATE duty_rosta SET duty_date='" . $duty_date . "', end='" . $end_date . "' WHERE entry_id = '" . $entry_id . "' ");
                // $response[] = "$entry_id Updated";
                $response[] = true;
            } else {
                if ($this->db->insert('duty_rosta', $duty)) {
                    $response[] = true;
                } else {
                    $response[] = false;
                }
            }
        }
        return $response;
    }
    public function insert_attendance($data)
    {
        $response = array();
        foreach ($data->workdates as $key => $value) {
            // echo $value;
            $duty = array(
                'entry_id' => $value . '' . $data->ihris_pid,
                'facility_id' => $data->facility_id,
                'ihris_pid' => $data->ihris_pid,
                'schedule_id' => $data->schedule_id,
                'date' => $value
            );
            $entry_id = $value . '' . $data->ihris_pid;
            $duty_date = $value;
            $this->db->select('entry_id');
            $this->db->where('entry_id', $entry_id);
            $query = $this->db->get('actuals');
            if ($query->num_rows() == 1) {
                $this->db->query("UPDATE actuals SET `date`='" . $duty_date . "' WHERE entry_id = '" . $entry_id . "' ");
                // $response[] = "$entry_id Updated";
                $response[] = array('status' => 'updated', 'entry' => $entry_id);
            } else {
                $this->db->insert('actuals', $duty);
                // $response[] = "$entry_id Created";
                $response[] = array('status' => 'created', 'entry' => $entry_id);
            }
        }
        return $response;
    }
    public function setColor($schedule)
    {
        switch ($schedule) {
            case '14':
                # Day
                $color = '#d1a110';
                break;
            case '15':
                # Evening
                $color = '#49b229';
                break;
            case '16':
                # Night
                $color = '#29b229';
                break;
            case '17':
                # Off Duty
                $color = '#297bb2';
                break;
            case '18':
                # Annual Leave
                $color = '#603E1F';
                break;
            case '19':
                # Study Leave
                $color = '#0592942';
                break;
            case '20':
                # Maternity Leave
                $color = '#280542';
                break;
            case '21':
                # Other Leave
                $color = '#420524';
                break;
            default:
                # code...
                break;
        }
        return $color;
    }
    public function get_employee_schedule($facility, $person)
    {
        $this->db->select('duty_date');
        $this->db->from('duty_rosta');
        if ($this->department) {
            $this->db->where('department_id', $this->department);
        }
        $this->db->where('facility_id', urldecode($facility));
        $this->db->where('ihris_pid', urldecode($person));
        $this->db->order_by('duty_date', 'DESC');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }
    public function get_employee_attendance($facility, $person)
    {
        $this->db->select('date');
        $this->db->from('actuals');
        if ($this->department) {
            $this->db->where('department_id', $this->department);
        }
        $this->db->where('facility_id', urldecode($facility));
        $this->db->where('ihris_pid', urldecode($person));
        $this->db->order_by('date', 'DESC');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }
    public function clock_in_employee($userdata)
    {
        $data = array(
            'entry_id' => $userdata->date . $userdata->ihris_pid,
            'ihris_pid' => $userdata->ihris_pid,
            'facility_id' => $userdata->facility_id,
            'time_in' => $userdata->time_in,
            'time_out' => '',
            'status' => $userdata->status,
            'date' => $userdata->date
        );
        $query = $this->db->insert('clk_log', $data);
        if ($query) {
            $query2 = $this->db->insert('clk_log_history', array(
                'ihris_pid' => $userdata->ihris_pid,
                'facility_id' => $userdata->facility_id,
                'entry_id' => $userdata->date . $userdata->ihris_pid,
                'time_in' => $userdata->time_in,
                'status' => $userdata->status,
                'date' => $userdata->date
            ));
            if ($query2) {
                return true;
            } else {
                return false;
            };
        } else {
            return false;
        }
    }
    public function clock_out_employee($userdata)
    {
        $data = array(
            'time_out' => $userdata->time_out,
            'status' => $userdata->status
        );
        $entry_id = $userdata->date . $userdata->ihris_pid;
        $this->db->set($data);
        if ($this->department) {
            $this->db->where('department_id', $this->department);
        }
        $this->db->where('entry_id', $entry_id);
        $query = $this->db->update('clk_log');
        if ($query) {
            $query2 = $this->db->insert('clk_log_history', array(
                'ihris_pid' => $userdata->ihris_pid,
                'facility_id' => $userdata->facility_id,
                'entry_id' => $entry_id,
                'time_out' => $userdata->time_out,
                'status' => $userdata->status,
                'date' => $userdata->date
            ));
            if ($query2) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    //datatable ops
    function countStaff()
    {
        $facility = $this->facility;
        if ($this->department) {
            $this->db->where('department_id', $this->department);
        }
        if ($facility) {
            $this->db->where('facility_id', $facility);
        }
        $query = $this
            ->db
            ->where('facility_id', $facility)
            ->get('ihrisdata');
        return $query->num_rows();
    }
    function fetchAllStaff($limit, $start, $col, $dir)
    {
        $facility = $this->facility;
        if ($this->department) {
            $this->db->where('department_id', $this->department);
        }
        if ($facility) {
            $this->db->where('facility_id', $facility);
        }
        $query = $this
            ->db
            ->limit($limit, $start)
            ->order_by($col, $dir)
            ->get('ihrisdata');
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return null;
        }
    }
    function searchStaff($limit, $start, $search, $col, $dir)
    {
        $facility = $this->facility;
        if ($this->department) {
            $this->db->where('department_id', $this->department);
        }
        if ($facility) {
            $this->db->where('facility_id', $facility);
        }
        $query = $this
            ->db
            ->like('surname', $search)
            ->or_like('othername', $search)
            ->or_like('firstname', $search)
            ->limit($limit, $start)
            ->order_by($col, $dir)
            ->get('ihrisdata');
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return null;
        }
    }
    public function countforSearch($search)
    {
        $facility = $this->facility;
        if ($this->department) {
            $this->db->where('department_id', $this->department);
        }
        if ($facility) {
            $this->db->where('facility_id', $facility);
        }
        $query = $this->db
            ->like('surname', $search)
            ->or_like('othername', $search)
            ->or_like('firstname', $search)
            ->get('ihrisdata');
        return $query->num_rows();
    }
    public function count_timelogs($search_data, $filter)
    {
        $rows = $this->getTimeLogs($limit = false, $start = FALSE, $search_data, $filter);
        return count($rows);
    }
    public function count_monthlytimelogs($search_data, $filter)
    {
        $rows = $this->groupmonthlyTimeLogs($limit = false, $start = FALSE, $search_data, $filter);
        return count($rows);
    }
    public function groupmonthlyTimeLogs($limit, $start, $search_data, $filter)
    {
        if (empty($search_data['date_from'])) {
            $date_from = date("Y-m-d", strtotime("-1 month"));
            $date_to = date('Y-m-d');
        } else {
            $date_from = $search_data['date_from'];
            $date_to = $search_data['date_to'];
        }
        $date_from_esc = $this->db->escape($date_from);
        $date_to_esc = $this->db->escape($date_to);

        $namesearch = "";
        if (!empty($search_data['name'])) {
            $ids = $this->getIds($search_data['name']);
            if (!empty($ids)) {
                $escaped = array_map(array($this->db, 'escape'), $ids);
                $namesearch = " AND k.pid IN (" . implode(',', $escaped) . ")";
            }
        }

        $sjob = "";
        if (!empty($search_data['job'])) {
            $like_val = $this->db->escape('%' . $this->db->escape_like_str($search_data['job']) . '%');
            $sjob = " AND k.job LIKE " . $like_val;
        }

        $limits = "";
        if (!empty($limit)) {
            $limits = " LIMIT " . (int) $start . "," . (int) $limit;
        }
        /* MySQL 8 ONLY_FULL_GROUP_BY: aggregates and ORDER BY column positions */
        $query = $this->db->query("SELECT k.pid, MAX(k.surname) AS surname, MAX(k.firstname) AS firstname, MAX(k.othername) AS othername, MAX(k.job) AS job, MAX(k.facility) AS facility, MAX(k.department) AS department, SUM(k.time_diff) AS m_timediff, DATE_FORMAT(k.date, '%Y-%m-01') AS date FROM clk_diff k WHERE k.date BETWEEN $date_from_esc AND $date_to_esc AND $filter $namesearch $sjob GROUP BY k.pid, DATE_FORMAT(k.date, '%Y-%m') ORDER BY 2 ASC, 9 ASC $limits");
        $data = $query->result();
        return $data;
    }

    /**
     * Server-side DataTables count for grouped monthly time logs.
     * Uses clk_log + ihrisdata (same as viewTimeLogs) so it works without clk_diff.
     */
    public function countGroupedMonthlyTimeLogsAjax($search_data, $filter, $search = '')
    {
        if (empty($search_data['date_from'])) {
            $date_from = date("Y-m-d", strtotime("-1 month"));
            $date_to = date('Y-m-d');
        } else {
            $date_from = $search_data['date_from'];
            $date_to = $search_data['date_to'];
        }
        $date_from_esc = $this->db->escape($date_from);
        $date_to_esc = $this->db->escape($date_to);

        $namesearch = "";
        if (!empty($search_data['name'])) {
            $ids = $this->getIds($search_data['name']);
            if (!empty($ids)) {
                $escaped = array_map(array($this->db, 'escape'), $ids);
                $namesearch = " AND ihrisdata.ihris_pid IN (" . implode(',', $escaped) . ")";
            } else {
                return 0;
            }
        }

        $sjob = "";
        if (!empty($search_data['job'])) {
            $like_val = $this->db->escape('%' . $this->db->escape_like_str($search_data['job']) . '%');
            $sjob = " AND ihrisdata.job LIKE " . $like_val;
        }

        $searchClause = "";
        if ($search !== '' && trim($search) !== '') {
            $like_val = $this->db->escape('%' . $this->db->escape_like_str(trim($search)) . '%');
            $searchClause = " AND (ihrisdata.surname LIKE $like_val OR ihrisdata.firstname LIKE $like_val OR ihrisdata.job LIKE $like_val OR ihrisdata.facility LIKE $like_val OR ihrisdata.department LIKE $like_val)";
        }

        $sql = "SELECT COUNT(*) AS cnt FROM (
                SELECT ihrisdata.ihris_pid, DATE_FORMAT(clk_log.date, '%Y-%m') AS ym
                FROM clk_log
                INNER JOIN ihrisdata ON ihrisdata.ihris_pid = clk_log.ihris_pid
                WHERE clk_log.date BETWEEN $date_from_esc AND $date_to_esc
                AND $filter
                $namesearch
                $sjob
                $searchClause
                GROUP BY ihrisdata.ihris_pid, DATE_FORMAT(clk_log.date, '%Y-%m')
                ) t";
        $query = $this->db->query($sql);
        $row = $query->row();
        return (int) ($row->cnt ?? 0);
    }

    /**
     * Server-side DataTables fetch for grouped monthly time logs.
     * Uses clk_log + ihrisdata (same as viewTimeLogs) so it works without clk_diff.
     */
    public function fetchGroupedMonthlyTimeLogsAjax($search_data, $filter, $start = 0, $length = 200, $search = '')
    {
        if (empty($search_data['date_from'])) {
            $date_from = date("Y-m-d", strtotime("-1 month"));
            $date_to = date('Y-m-d');
        } else {
            $date_from = $search_data['date_from'];
            $date_to = $search_data['date_to'];
        }
        $date_from_esc = $this->db->escape($date_from);
        $date_to_esc = $this->db->escape($date_to);

        $namesearch = "";
        if (!empty($search_data['name'])) {
            $ids = $this->getIds($search_data['name']);
            if (!empty($ids)) {
                $escaped = array_map(array($this->db, 'escape'), $ids);
                $namesearch = " AND ihrisdata.ihris_pid IN (" . implode(',', $escaped) . ")";
            } else {
                return array();
            }
        }

        $sjob = "";
        if (!empty($search_data['job'])) {
            $like_val = $this->db->escape('%' . $this->db->escape_like_str($search_data['job']) . '%');
            $sjob = " AND ihrisdata.job LIKE " . $like_val;
        }

        $searchClause = "";
        if ($search !== '' && trim($search) !== '') {
            $like_val = $this->db->escape('%' . $this->db->escape_like_str(trim($search)) . '%');
            $searchClause = " AND (ihrisdata.surname LIKE $like_val OR ihrisdata.firstname LIKE $like_val OR ihrisdata.job LIKE $like_val OR ihrisdata.facility LIKE $like_val OR ihrisdata.department LIKE $like_val)";
        }

        $start = (int) $start;
        $length = (int) $length;
        $limit = " LIMIT $start, $length";

        /* MySQL 8 ONLY_FULL_GROUP_BY: aggregate non-grouped; ORDER BY column position; hours from time_in/time_out */
        $sql = "SELECT ihrisdata.ihris_pid AS pid,
                MAX(ihrisdata.surname) AS surname,
                MAX(ihrisdata.firstname) AS firstname,
                MAX(ihrisdata.othername) AS othername,
                MAX(ihrisdata.job) AS job,
                MAX(ihrisdata.facility) AS facility,
                MAX(ihrisdata.department) AS department,
                SUM(IFNULL(TIMESTAMPDIFF(SECOND, clk_log.time_in, clk_log.time_out), 0) / 3600) AS m_timediff,
                DATE_FORMAT(clk_log.date, '%Y-%m-01') AS date
                FROM clk_log
                INNER JOIN ihrisdata ON ihrisdata.ihris_pid = clk_log.ihris_pid
                WHERE clk_log.date BETWEEN $date_from_esc AND $date_to_esc
                AND $filter
                $namesearch
                $sjob
                $searchClause
                GROUP BY ihrisdata.ihris_pid, DATE_FORMAT(clk_log.date, '%Y-%m')
                ORDER BY 2 ASC, 9 ASC
                $limit";
        $query = $this->db->query($sql);
        return $query->result();
    }
    public function getTimeLogs($limit, $start, $search_data, $filter)
    {
        $date_from = $search_data['date_from'];
        $date_to = $search_data['date_to'];
        if (!empty($search_data['name'])) {
            $ids = $this->getIds($search_data['name']);
            $emps = "'" . implode("','", $ids) . "'";
            $namesearch = "and ihrisdata.ihris_pid in ($emps)";
        } else {
            $namesearch = "";
        }
        if (!empty($search_data['job'])) {
            $job = $search_data['job'];
            $sjob = "AND job like'$job' ";
        } else {
            $sjob = "";
        }
        if (!empty($limit)) {
            $limit = "LIMIT $start,$limit";
        } else {
            $limit = "";
        }
        $query = $this->db->query("SELECT surname,firstname,othername,department,job,ihrisdata.ihris_pid as pid,ihrisdata.facility_id as facid, ihrisdata.facility as fac, time_in ,  time_out,clk_log.date as date  from clk_log, ihrisdata WHERE ihrisdata.ihris_pid=clk_log.ihris_pid and clk_log.date BETWEEN '$date_from' AND '$date_to' AND $filter $namesearch $sjob order by surname ASC, clk_log.date ASC $limit");
        $data = $query->result();
        return $data;
    }

    /**
     * Server-side DataTables count for time logs.
     */
    public function countTimeLogsAjax($search_data, $filter, $search = '')
    {
        if (empty($search_data['date_from'])) {
            $date_from = date("Y-m-d", strtotime("-1 month"));
            $date_to = date('Y-m-d');
        } else {
            $date_from = $search_data['date_from'];
            $date_to = $search_data['date_to'];
        }

        $namesearch = "";
        if (!empty($search_data['name'])) {
            $ids = $this->getIds($search_data['name']);
            if (!empty($ids)) {
                $emps = "'" . implode("','", $ids) . "'";
                $namesearch = "and ihrisdata.ihris_pid in ($emps)";
            } else {
                return 0;
            }
        }

        $sjob = "";
        if (!empty($search_data['job'])) {
            $job = $this->db->escape_like_str($search_data['job']);
            $sjob = "AND ihrisdata.job LIKE '$job'";
        }

        $searchClause = "";
        if (!empty($search)) {
            $escaped = $this->db->escape_like_str($search);
            $searchClause = "AND (ihrisdata.surname LIKE '%$escaped%' OR ihrisdata.firstname LIKE '%$escaped%' OR ihrisdata.job LIKE '%$escaped%' OR logs.date LIKE '%$escaped%')";
        }

        $sql = "SELECT COUNT(*) as cnt
                FROM (
                    SELECT ihris_pid, date, time_in, time_out, facility_id FROM clk_log
                    UNION ALL
                    SELECT ihris_pid, date, time_in, time_out, facility_id FROM mobileclk_log
                ) AS logs
                INNER JOIN ihrisdata ON ihrisdata.ihris_pid = logs.ihris_pid
                WHERE logs.date BETWEEN '$date_from' AND '$date_to' 
                AND $filter 
                $namesearch 
                $sjob 
                $searchClause";
        
        $query = $this->db->query($sql);
        $row = $query->row();
        return (int) ($row->cnt ?? 0);
    }

    /**
     * Server-side DataTables fetch for time logs.
     */
    public function fetchTimeLogsAjax($search_data, $filter, $start = 0, $length = 200, $search = '')
    {
        if (empty($search_data['date_from'])) {
            $date_from = date("Y-m-d", strtotime("-1 month"));
            $date_to = date('Y-m-d');
        } else {
            $date_from = $search_data['date_from'];
            $date_to = $search_data['date_to'];
        }

        $namesearch = "";
        if (!empty($search_data['name'])) {
            $ids = $this->getIds($search_data['name']);
            if (!empty($ids)) {
                $emps = "'" . implode("','", $ids) . "'";
                $namesearch = "and ihrisdata.ihris_pid in ($emps)";
            } else {
                return array();
            }
        }

        $sjob = "";
        if (!empty($search_data['job'])) {
            $job = $this->db->escape_like_str($search_data['job']);
            $sjob = "AND ihrisdata.job LIKE '$job'";
        }

        $searchClause = "";
        if (!empty($search)) {
            $escaped = $this->db->escape_like_str($search);
            $searchClause = "AND (ihrisdata.surname LIKE '%$escaped%' OR ihrisdata.firstname LIKE '%$escaped%' OR ihrisdata.job LIKE '%$escaped%' OR logs.date LIKE '%$escaped%')";
        }

        $limit = "LIMIT $start, $length";

        $sql = "SELECT ihrisdata.surname, ihrisdata.firstname, ihrisdata.othername, ihrisdata.department, 
                       ihrisdata.job, ihrisdata.ihris_pid as pid, ihrisdata.facility_id as facid, 
                       ihrisdata.facility as fac, logs.time_in, logs.time_out, logs.date
                FROM (
                    SELECT ihris_pid, date, time_in, time_out FROM clk_log
                    UNION ALL
                    SELECT ihris_pid, date, time_in, time_out FROM mobileclk_log
                ) AS logs
                INNER JOIN ihrisdata ON ihrisdata.ihris_pid = logs.ihris_pid
                WHERE logs.date BETWEEN '$date_from' AND '$date_to' 
                AND $filter 
                $namesearch 
                $sjob 
                $searchClause
                ORDER BY ihrisdata.surname ASC, logs.date ASC
                $limit";
        
        $query = $this->db->query($sql);
        return $query->result();
    }
    public function getIds($name)
    {
        $query = $this->db->query("SELECT ihris_pid from ihrisdata WHERE firstname like'$name%' OR surname like '$name%' ");
        $result = $query->result();
        $ids = array();
        foreach ($result as $row) {
            array_push($ids, $row->ihris_pid);
        }
        return $ids;
    }
    public function count_Staff($filters)
    {
        $query = $this->db->query("SELECT * from ihrisdata where $filters");
        return $query->num_rows();
    }
    public function timelogscsv($date_from, $date_to, $name, $job, $filter)
    {
        if (!empty($name)) {
            $sname = "AND (firstname like'$name%' OR surname like '$name%') ";
        } else {
            $sname = "";
        }
        if (!empty($job)) {
            $sjob = "AND ihrisdata.job like '$job' ";
        } else {
            $sjob = "";
        }
        $query = $this->db->query("SELECT surname,firstname,othername,department,job,gender,birth_date,cadre,ihrisdata.ihris_pid as pid,ihrisdata.facility_id as facid, ihrisdata.facility as fac, logs.time_in, logs.time_out, logs.date as date
                FROM (
                    SELECT ihris_pid, date, time_in, time_out FROM clk_log
                    UNION ALL
                    SELECT ihris_pid, date, time_in, time_out FROM mobileclk_log
                ) AS logs
                INNER JOIN ihrisdata ON ihrisdata.ihris_pid = logs.ihris_pid
                WHERE logs.date BETWEEN '$date_from' AND '$date_to' AND $filter $sname $sjob ORDER BY surname ASC, logs.date ASC");
        $data = $query->result();
        return $data;
    }
    public function countTimesheet($filters, $employee)
    {
        $facility = $this->facility;
        if (!empty($employee)) {
            $search = "and ihrisdata.ihris_pid='$employee'";
        }
        else{
            $search = "";
        }
        $query = $this->db->query("SELECT ihris_pid from ihrisdata where $filters $search");
        return $query->num_rows();
    }

    /**
     * Server-side DataTables count for timesheet employees list.
     * Note: $filters is a pre-built SQL fragment used across the app.
     */
    public function countTimesheetAjax($filters, $employee = '', $job = '', $search = '')
    {
        $where = "WHERE $filters";

        if (!empty($employee)) {
            $emp = $this->db->escape($employee);
            $where .= " AND ihrisdata.ihris_pid = $emp";
        }

        if (!empty($job)) {
            $jobEsc = $this->db->escape_like_str($job);
            $where .= " AND ihrisdata.job LIKE '%$jobEsc%'";
        }

        if (!empty($search)) {
            $s = $this->db->escape_like_str($search);
            $where .= " AND (ihrisdata.ihris_pid LIKE '%$s%' OR ihrisdata.surname LIKE '%$s%' OR ihrisdata.firstname LIKE '%$s%' OR ihrisdata.othername LIKE '%$s%' OR ihrisdata.job LIKE '%$s%')";
        }

        $query = $this->db->query("SELECT COUNT(DISTINCT ihrisdata.ihris_pid) as total FROM ihrisdata $where");
        $row = $query->row();
        return isset($row->total) ? (int)$row->total : 0;
    }

    /**
     * Server-side DataTables fetch for timesheet employees list (paged).
     * Returns: ihris_pid, fullname, job
     */
    public function fetchTimesheetEmployeesAjax($filters, $employee = '', $job = '', $start = 0, $length = 20, $search = '')
    {
        $start = (int)$start;
        $length = (int)$length;
        if ($length <= 0) {
            $length = 20;
        }

        $where = "WHERE $filters";

        if (!empty($employee)) {
            $emp = $this->db->escape($employee);
            $where .= " AND ihrisdata.ihris_pid = $emp";
        }

        if (!empty($job)) {
            $jobEsc = $this->db->escape_like_str($job);
            $where .= " AND ihrisdata.job LIKE '%$jobEsc%'";
        }

        if (!empty($search)) {
            $s = $this->db->escape_like_str($search);
            $where .= " AND (ihrisdata.ihris_pid LIKE '%$s%' OR ihrisdata.surname LIKE '%$s%' OR ihrisdata.firstname LIKE '%$s%' OR ihrisdata.othername LIKE '%$s%' OR ihrisdata.job LIKE '%$s%')";
        }

        $sql = "
            SELECT DISTINCT ihrisdata.ihris_pid,
                CONCAT(
                    COALESCE(ihrisdata.surname,''),
                    ' ',
                    COALESCE(ihrisdata.firstname,''),
                    ' ',
                    COALESCE(ihrisdata.othername,'')
                ) AS fullname,
                ihrisdata.job
            FROM ihrisdata
            $where
            ORDER BY fullname ASC
            LIMIT $start, $length
        ";

        return $this->db->query($sql)->result_array();
    }
    /**
     * Count employees for timesheet (same filters as fetch_TimeSheet). Used for streaming print.
     */
    public function count_TimeSheet_employees($date_range, $employee = FALSE, $filters = FALSE, $job = NULL)
    {
        $search = "";
        if (!empty($employee)) {
            $search = " AND ihrisdata.ihris_pid=" . $this->db->escape($employee);
        }
        $jsearch = "";
        if (!empty($job)) {
            $jsearch = " AND ihrisdata.job LIKE " . $this->db->escape('%' . $job . '%');
        }
        $where = trim((string)$filters . ' ' . $search . ' ' . $jsearch);
        if ($where === '' || preg_match('/^\s*and\s*$/i', $where)) {
            $where = '1=1';
        } elseif (preg_match('/^\s*and\s+/i', $where)) {
            $where = '1=1 ' . $where;
        }
        $sql = "SELECT COUNT(DISTINCT ihrisdata.ihris_pid) AS cnt FROM ihrisdata WHERE $where";
        $row = $this->db->query($sql)->row();
        return isset($row->cnt) ? (int) $row->cnt : 0;
    }

    public function fetch_TimeSheet($date_range = FALSE, $start = FALSE, $limit = FALSE, $employee = FALSE, $filters=FALSE, $job = NULL)
    {
        $month = $this->input->post('month');
        $year = $this->input->post('year');
        $date = $year . "-" . $month;
        if ($month != "") {
            $valid_range = $date;
        } else {
            $valid_range = $date_range;
        }
        $search = "";
        if (!empty($employee)) {
            $search = " AND ihrisdata.ihris_pid=" . $this->db->escape($employee);
        }
        $jsearch = "";
        if (!empty($job)) {
            $jsearch = " AND ihrisdata.job LIKE " . $this->db->escape('%' . $job . '%');
        }
        // Support limit/offset for streaming: MySQL LIMIT is offset, row_count.
        $limit_sql = "";
        if ($limit !== FALSE && $limit !== NULL && (int)$limit > 0) {
            $limit_sql = " LIMIT " . max(0, (int)$start) . ", " . (int)$limit;
        }
        $where = trim((string)$filters . ' ' . $search . ' ' . $jsearch);
        if ($where === '' || preg_match('/^\s*and\s*$/i', $where)) {
            $where = '1=1';
        } elseif (preg_match('/^\s*and\s+/i', $where)) {
            $where = '1=1 ' . $where;
        }
        $all = $this->db->query("SELECT DISTINCT ihrisdata.ihris_pid,
                CONCAT(COALESCE(ihrisdata.surname,''),' ',COALESCE(ihrisdata.firstname,''),' ',COALESCE(ihrisdata.othername,'')) AS fullname,
                ihrisdata.job FROM ihrisdata WHERE $where ORDER BY fullname ASC $limit_sql");
        $data = $all->result_array();
        return $data;
    }

    /**
     * Get scheduled days per employee for a month (Day+Evening+Night, schedule_id 14,15,16).
     * Returns array ihris_pid => total_days. Used to avoid N×attrosta calls in print.
     */
    public function get_scheduled_days_for_month($pids, $year_month)
    {
        if (empty($pids) || empty($year_month)) {
            return array();
        }
        $this->db->select('ihris_pid, COUNT(*) AS days');
        $this->db->from('duty_rosta');
        $this->db->where_in('ihris_pid', $pids);
        $this->db->where_in('schedule_id', array(14, 15, 16));
        $this->db->where("DATE_FORMAT(duty_date, '%Y-%m') = ", $year_month);
        $this->db->group_by('ihris_pid');
        $q = $this->db->get();
        $out = array();
        foreach ($q->result() as $r) {
            $out[(string) $r->ihris_pid] = (int) $r->days;
        }
        return $out;
    }

    /**
     * Get scheduled days per employee for a date range (schedule_id 14,15,16).
     */
    public function get_scheduled_days_for_range($pids, $start_date, $end_date)
    {
        if (empty($pids) || empty($start_date) || empty($end_date)) {
            return array();
        }
        $this->db->select('ihris_pid, COUNT(*) AS days');
        $this->db->from('duty_rosta');
        $this->db->where_in('ihris_pid', $pids);
        $this->db->where_in('schedule_id', array(14, 15, 16));
        $this->db->where('duty_date >=', $start_date);
        $this->db->where('duty_date <=', $end_date);
        $this->db->group_by('ihris_pid');
        $q = $this->db->get();
        $out = array();
        foreach ($q->result() as $r) {
            $out[(string) $r->ihris_pid] = (int) $r->days;
        }
        return $out;
    }
    //get employees
    public function count_employeeTimelogs($ihris_pid, $date = NULL)
    {
        $query = $this->db->query("SELECT ihris_pid from clk_log where ihris_pid='$ihris_pid' and date like'$date-%'");
        return $query->num_rows();
    }
    public function getEmployeeTimeLogs($ihris_pid, $limit = false, $start = FALSE, $search_data = FALSE, $search_data2 = FALSE)
    {
        if ($search_data) {
            $date_from = $search_data['date_from'];
            $date_to = $search_data['date_to'];
            $date_from = date('Y-m-d', strtotime($date_from));
            $date_to = date('Y-m-d', strtotime($date_to));
            $this->db->where("date >= '$date_from' AND date <= '$date_to'");
        }
        if ($search_data2) {
            $date_from = $search_data2['date_from'];
            $date_to = $search_data2['date_to'];
            $date_from = date('Y-m-d', strtotime($date_from));
            $date_to = date('Y-m-d', strtotime($date_to));
            $this->db->where("date >= '$date_from' AND date <= '$date_to'");
        }
        $this->db->where("clk_log.ihris_pid", $ihris_pid);
        $this->db->limit($limit, $start);
        $this->db->order_by('date','ASC');
        $query = $this->db->get("clk_log");
        $data['timelogs'] = $query->result();
        //======userdata====
        $this->db->where('ihris_pid', $ihris_pid);
        $qry = $this->db->get('ihrisdata');
        $data['employee'] = $qry->row();
        //leave info
        $ihris_pid = @$data['employee']->ihris_pid;
        $this->db->where('ihris_pid', $ihris_pid);
        $this->db->where('schedule_id', '25');
        if ($search_data) {
            $this->db->where("date >= '$date_from' AND date <= '$date_to'");
        }
        if ($search_data2) {
            $date_from = $search_data2['date_from'];
            $date_to = $search_data2['date_to'];
            $date_from = date('Y-m-d', strtotime($date_from));
            $date_to = date('Y-m-d', strtotime($date_to));
            $this->db->where("date >= '$date_from' AND date <= '$date_to'");
        }
        $qry = $this->db->get('actuals');
        $data['leaves'] = $qry->result();
        //offs info
        $ihris_pid = @$data['employee']->ihris_pid;
        $this->db->where('ihris_pid', $ihris_pid);
        $schedules = array('24', '27');
        $this->db->where_in('schedule_id', $schedules);
        if ($search_data) {
            $this->db->where("date >= '$date_from' AND date <= '$date_to'");
        }
        if ($search_data2) {
            $date_from = $search_data2['date_from'];
            $date_to = $search_data2['date_to'];
            $date_from = date('Y-m-d', strtotime($date_from));
            $date_to = date('Y-m-d', strtotime($date_to));
            $this->db->where("date >= '$date_from' AND date <= '$date_to'");
        }
        $qry = $this->db->get('actuals');
        $data['offs'] = $qry->result();
        //requests info
        $ihris_pid = @$data['employee']->ihris_pid;
        $this->db->where('ihris_pid', $ihris_pid);
        $this->db->where('schedule_id', '23');
        if ($search_data) {
            $this->db->where("date >= '$date_from' AND date <= '$date_to'");
        }
        if ($search_data2) {
            $date_from = $search_data2['date_from'];
            $date_to = $search_data2['date_to'];
            $date_from = date('Y-m-d', strtotime($date_from));
            $date_to = date('Y-m-d', strtotime($date_to));
            $this->db->where("date >= '$date_from' AND date <= '$date_to'");
        }
        $qry = $this->db->get('actuals');
        $data['requests'] = $qry->result();
        //holidays
        $darray = array();
        $this->db->select('holidaydate');
        $this->db->from('public_holiday');
        $query = $this->db->get();
        $dates = $query->result();
        foreach ($dates as $date) {
            $fdates =   $date->holidaydate;
            array_push($darray, $fdates);
        }
        //days supposed to work
        $ihris_pid = @$data['employee']->ihris_pid;
        $this->db->where('ihris_pid', $ihris_pid);
        $this->db->where('schedule_id', '14');
        if ($search_data) {
            $this->db->where("duty_date >= '$date_from' AND duty_date <= '$date_to'");
        }
        if ($search_data2) {
            $date_from = $search_data2['date_from'];
            $date_to = $search_data2['date_to'];
            $date_from = date('Y-m-d', strtotime($date_from));
            $date_to = date('Y-m-d', strtotime($date_to));
            $this->db->where("duty_date >= '$date_from' AND duty_date <= '$date_to'");
        }
        $this->db->where_not_in('duty_date', $darray);
        $qry = $this->db->get('duty_rosta');
        $data['dutydays'] = $qry->result();
        return $data;
    }

    /**
     * Count clk_log rows for one employee in a date range (for streaming print).
     */
    public function count_employee_timelogs_range($ihris_pid, $date_from, $date_to)
    {
        $date_from = date('Y-m-d', strtotime($date_from));
        $date_to = date('Y-m-d', strtotime($date_to));
        $this->db->where('ihris_pid', $ihris_pid);
        $this->db->where('date >=', $date_from);
        $this->db->where('date <=', $date_to);
        return $this->db->count_all_results('clk_log');
    }

    /**
     * Fetch one batch of timelogs for streaming (clk_log only).
     */
    public function get_employee_timelogs_batch($ihris_pid, $date_from, $date_to, $offset, $limit)
    {
        $date_from = date('Y-m-d', strtotime($date_from));
        $date_to = date('Y-m-d', strtotime($date_to));
        $this->db->where('ihris_pid', $ihris_pid);
        $this->db->where('date >=', $date_from);
        $this->db->where('date <=', $date_to);
        $this->db->order_by('date', 'ASC');
        $this->db->limit($limit, $offset);
        $q = $this->db->get('clk_log');
        return $q->result();
    }

    /**
     * Fetch employee + leaves, offs, requests, dutydays for a date range (no clk_log). Used once for print header/footer.
     */
    public function get_employee_timelogs_meta($ihris_pid, $date_from, $date_to)
    {
        $date_from = date('Y-m-d', strtotime($date_from));
        $date_to = date('Y-m-d', strtotime($date_to));
        $out = array('employee' => null, 'leaves' => array(), 'offs' => array(), 'requests' => array(), 'dutydays' => array());

        $this->db->where('ihris_pid', $ihris_pid);
        $out['employee'] = $this->db->get('ihrisdata')->row();

        $pid = $out['employee'] ? $out['employee']->ihris_pid : $ihris_pid;

        $this->db->where('ihris_pid', $pid);
        $this->db->where('schedule_id', '25');
        $this->db->where('date >=', $date_from);
        $this->db->where('date <=', $date_to);
        $out['leaves'] = $this->db->get('actuals')->result();

        $this->db->where('ihris_pid', $pid);
        $this->db->where_in('schedule_id', array('24', '27'));
        $this->db->where('date >=', $date_from);
        $this->db->where('date <=', $date_to);
        $out['offs'] = $this->db->get('actuals')->result();

        $this->db->where('ihris_pid', $pid);
        $this->db->where('schedule_id', '23');
        $this->db->where('date >=', $date_from);
        $this->db->where('date <=', $date_to);
        $out['requests'] = $this->db->get('actuals')->result();

        $this->db->select('holidaydate');
        $holidays = $this->db->get('public_holiday')->result();
        $darray = array();
        foreach ($holidays as $d) {
            $darray[] = $d->holidaydate;
        }
        $this->db->where('ihris_pid', $pid);
        $this->db->where('schedule_id', '14');
        $this->db->where('duty_date >=', $date_from);
        $this->db->where('duty_date <=', $date_to);
        if (!empty($darray)) {
            $this->db->where_not_in('duty_date', $darray);
        }
        $out['dutydays'] = $this->db->get('duty_rosta')->result();

        return $out;
    }

    public function gettimedata($person_id, $date)
    {
        $sql    = "SELECT time_in,time_out FROM clk_log WHERE ihris_pid = '$person_id' AND date='$date'";
        $query  = $this->db->query($sql);
        return  $query->row();
    }


    public function save_employee($postdata)
    {

        $data = array(

            'firstname' => $postdata['firstname'],
            'othername' => $postdata['othername'],
            'surname' => $postdata['surname'],
            'gender' => $postdata['gender'],
            'birth_date' => $postdata['birth_date'],
            'home_district' => $postdata['home_district'],
            'mobile' => $postdata['mobile'],
            'telephone' => $postdata['telephone'],
            'email' => $postdata['email'],
            'place_of_residence' => $postdata['place_of_residence'],
            'nin' => $postdata['nin'],
            'job' => $postdata['job'],
            'job_id' => $postdata['job_id'],
            'salary_grade' => $postdata['salary_grade'],
            'employment_terms' => $postdata['employment_terms'],
            'cadre' => $postdata['cadre'],
            'facility_id' => $postdata['facility_id'],
            'facility' => $postdata['facility'],
            'institution_category' => $postdata['institution_category'],
            'institutiontype_name' => $postdata['institutiontype_name'],
            'institution_level' => $postdata['institution_level'],
            'district_id' => $postdata['district_id']
        );

        $qry = $this->db->insert("ihrisdata", $data);
        $rows = $this->db->affected_rows();

        if ($rows > 0) {

            return "Employee has been Added Successfully";
        } else {

            return "Operation failed";
        }
    }
}
