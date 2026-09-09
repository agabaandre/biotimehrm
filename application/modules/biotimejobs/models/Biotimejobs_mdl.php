<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Biotimejobs_mdl extends CI_Model
{
    protected $facility;

    public  function __construct()
    {
        parent::__construct();
        $this->facility = isset($_SESSION['facility']) ? $_SESSION['facility'] : null;
    }


    public function add_ihrisdata($response)
    {
        // if (count($data) > 1) {
        //     $this->db->query("TRUNCATE `ihrisdata`");
        // }
        // $query = $this->db->insert_batch('ihrisdata', $data);
        //  $this->db->truncate('ihrisdata');
        

        //    if ($response) {

        //    foreach($response as $data){
                          
            
                 $query = $this->db->insert('ihrisdata',$response);
          //  }

        //$delete = $this->db->query("DELETE from ihrisdata where facility_id='facility|787' AND card_number IS NULL");

        if ($query) {
            $n = $this->db->query("select ihris_pid from ihrisdata");


            $message = print_r($this->exect()) . " get_ihrisdata() add_ihrisdata()  IHRIS HRH " . $n->num_rows();
        } else {
            $message = print_r($this->exect()) . " get_ihrisdata() add_ihrisdata()  IHRIS HRH FAILED ";
        }
    


        return $message;

    }

    public function add_ucmbdata($datas)
    {

        
        foreach($datas as $data){
            $query = $this->db->insert('ihrisdata', $data);
        }
        

        if ($query) {
            $n = $this->db->query("select ihris_pid from ihrisdata");


            $message = print_r($this->exect()) . " get_ihrisdata() add_ihrisdata()  IHRIS HRH " . $n->num_rows();
        } else {
            $message = print_r($this->exect()) . " get_ihrisdata() add_ihrisdata()  IHRIS HRH FAILED ";
        }

        return $message;
    }

    /**
     * Dedupe rows by a unique-key column (last wins), then INSERT IGNORE in chunks.
     * Prevents BioTime sync jobs from failing when the API returns duplicate unique keys.
     *
     * @param string $table
     * @param array $rows
     * @param string $uniqueKey Column that must be unique (PRIMARY/UNIQUE)
     * @param int $chunkSize
     * @return array{attempted:int,unique:int,inserted:int,skipped_dupes:int}
     */
    public function insert_batch_skip_duplicates($table, array $rows, $uniqueKey, $chunkSize = 200)
    {
        $stats = [
            'attempted' => count($rows),
            'unique' => 0,
            'inserted' => 0,
            'skipped_dupes' => 0,
        ];

        if (empty($rows) || $uniqueKey === '' || $uniqueKey === null) {
            return $stats;
        }

        $table = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $table);
        $uniqueKey = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $uniqueKey);
        if ($table === '' || $uniqueKey === '') {
            return $stats;
        }

        $deduped = [];
        foreach ($rows as $row) {
            if (!is_array($row) || !array_key_exists($uniqueKey, $row)) {
                continue;
            }
            $key = trim((string) $row[$uniqueKey]);
            if ($key === '') {
                continue;
            }
            $row[$uniqueKey] = $key;
            if (isset($deduped[$key])) {
                $stats['skipped_dupes']++;
            }
            $deduped[$key] = $row;
        }

        $deduped = array_values($deduped);
        $stats['unique'] = count($deduped);
        if (empty($deduped)) {
            return $stats;
        }

        $fields = array_keys($deduped[0]);
        $chunkSize = max(1, (int) $chunkSize);

        foreach (array_chunk($deduped, $chunkSize) as $chunk) {
            $valueSql = [];
            foreach ($chunk as $row) {
                $escaped = [];
                foreach ($fields as $field) {
                    if (!array_key_exists($field, $row) || $row[$field] === null) {
                        $escaped[] = 'NULL';
                    } else {
                        $escaped[] = $this->db->escape($row[$field]);
                    }
                }
                $valueSql[] = '(' . implode(',', $escaped) . ')';
            }

            $sql = 'INSERT IGNORE INTO `' . $table . '` (`' . implode('`,`', $fields) . '`) VALUES '
                . implode(',', $valueSql);

            try {
                if ($this->db->query($sql)) {
                    // affected_rows is rows actually written (excludes ignored duplicates)
                    $stats['inserted'] += (int) $this->db->affected_rows();
                } else {
                    $err = $this->db->error();
                    log_message(
                        'error',
                        'insert_batch_skip_duplicates(' . $table . '): '
                        . (isset($err['message']) ? $err['message'] : 'query failed')
                    );
                }
            } catch (Exception $e) {
                log_message('error', 'insert_batch_skip_duplicates(' . $table . ') Exception: ' . $e->getMessage());
            } catch (Error $e) {
                log_message('error', 'insert_batch_skip_duplicates(' . $table . ') Fatal: ' . $e->getMessage());
            }
        }

        if ($stats['skipped_dupes'] > 0) {
            log_message(
                'info',
                'insert_batch_skip_duplicates(' . $table . '): skipped '
                . $stats['skipped_dupes'] . ' duplicate ' . $uniqueKey . ' value(s) from BioTime payload'
            );
        }

        return $stats;
    }

    public function add_enrolled($data)
    {
        if (!is_array($data) || count($data) < 1) {
            return print_r($this->exect()) . " saveEnrolled() add_enrolled() Failed — empty payload";
        }

        $this->db->query("CALL `fingerpints_cache`()");
        $this->db->query("TRUNCATE fingerprints_staging");

        // PRIMARY KEY is entry_id — dedupe + INSERT IGNORE so API duplicates never abort the sync
        $stats = $this->insert_batch_skip_duplicates('fingerprints_staging', $data, 'entry_id');

        $n = $this->db->query("select entry_id from fingerprints_staging");
        $count = $n ? $n->num_rows() : 0;
        if ($count > 0) {
            $message = print_r($this->exect()) . " saveEnrolled() add_enrolled() Created Enrolled users from Biotime "
                . $count . " (unique=" . $stats['unique'] . ", skipped_dupes=" . $stats['skipped_dupes'] . ")";
        } else {
            $message = print_r($this->exect()) . " saveEnrolled() add_enrolled() Failed ";
            log_message('error', 'add_enrolled failed after skip-duplicates insert: ' . json_encode($stats));
        }

        return $message;
    }
    public function add_time_logs($data)
    {
        if (!is_array($data) || count($data) < 1) {
            return print_r($this->exect()) . " fetchBiotTimeLogs()  add_time_logs() Failed — empty payload";
        }

        if (count($data) > 1) {
            $this->db->query("CALL `biotime_cache`()");
            $this->db->query("TRUNCATE biotime_data");
        }

        // Prefer a stable natural key when present so duplicate punches from BioTime are skipped
        $uniqueKey = null;
        if (!empty($data[0]) && is_array($data[0])) {
            if (array_key_exists('id', $data[0]) && $data[0]['id'] !== null && $data[0]['id'] !== '') {
                $uniqueKey = 'id';
            } elseif (array_key_exists('punch_id', $data[0])) {
                $uniqueKey = 'punch_id';
            }
        }

        if ($uniqueKey) {
            $stats = $this->insert_batch_skip_duplicates('biotime_data', $data, $uniqueKey);
            $query = $stats['unique'] > 0;
        } else {
            $query = $this->db->insert_batch('biotime_data', $data);
        }
        $this->db->query(" DELETE from biotime_data where emp_code='0'");

        if ($query) {
            $n = $this->db->get("biotime_data");

            $message = print_r($this->exect()) . " fetchBiotTimeLogs()  add_time_logs() Created Logs from Biotime " . $n->num_rows();
        } else {
            $message = print_r($this->exect()) . " fetchBiotTimeLogs()  add_time_logs() Failed ";
        }

        return $message;
    }


    public function save_department($data)
    {
        if (count($data) > 1) {
            // Use TRUNCATE to clear table and reset auto-increment counter
            $this->db->query("TRUNCATE TABLE biotime_departments");
            // Ensure auto-increment starts from 1
            $this->db->query("ALTER TABLE biotime_departments AUTO_INCREMENT = 1");
        }
        
        // Build batch REPLACE with explicit column specification (excluding id)
        // REPLACE will delete existing row if dept_code matches and insert new one
        if (empty($data)) {
            $message = print_r($this->exect()) . " save_department() No data to insert";
            return $message;
        }
        
        $values = array();
        $has_biotime_id = $this->db->field_exists('biotime_dept_id', 'biotime_departments');
        foreach ($data as $row) {
            $dept_code = isset($row['dept_code']) ? $this->db->escape($row['dept_code']) : 'NULL';
            $dept_name = isset($row['dept_name']) ? $this->db->escape($row['dept_name']) : 'NULL';
            if ($has_biotime_id) {
                $biotime_id = isset($row['biotime_dept_id']) ? (int) $row['biotime_dept_id'] : 'NULL';
                $values[] = "($dept_code, $dept_name, $biotime_id)";
            } else {
                $values[] = "($dept_code, $dept_name)";
            }
        }
        
        // Use REPLACE INTO - this will delete existing row if dept_code matches and insert new one
        // id is auto-increment, so we don't include it - MySQL will generate new id
        if ($has_biotime_id) {
            $sql = "REPLACE INTO biotime_departments (dept_code, dept_name, biotime_dept_id) VALUES " . implode(', ', $values);
        } else {
            $sql = "REPLACE INTO biotime_departments (dept_code, dept_name) VALUES " . implode(', ', $values);
        }
        
        $query = $this->db->query($sql);
        if ($query) {
            $n = $this->db->get("biotime_departments");
            $message = print_r($this->exect()) . " save_department() Created Departments from Biotime " . $n->num_rows();
        } else {
            $error = $this->db->error();
            $error_msg = isset($error['message']) ? $error['message'] : 'Unknown error';
            $error_code = isset($error['code']) ? $error['code'] : '';
            $message = print_r($this->exect()) . " Fetch Departments Failed: " . $error_msg . " (Code: " . $error_code . ")";
            log_message('error', 'save_department failed: ' . $error_msg . ' | SQL: ' . substr($sql, 0, 200));
        }

        return $message;
    }
    public function save_jobs($data)
    {
        if (count($data) > 1) {
            $this->db->query("TRUNCATE biotime_jobs");
        }
        $query = $this->db->insert_batch("biotime_jobs", $data);
        if ($query) {
            $n = $this->db->get("biotime_jobs");
            $message = print_r($this->exect()) . " save_jobs() Created jobs from Biotime " . $n->num_rows();
            // $this->db->insert("INSERT INTO `biotime_sync_log` (`serial_no`,  `last_gen`, `records`) VALUES (NULL, current_timestamp(), $n->num_rows());
            // ");
        } else {
            $message = print_r($this->exect()) . " Fetch jobs Failed ";
        }

        return $message;
    }
    public function save_facilities($data)
    {
        if (count($data) > 1) {
            $this->db->query("TRUNCATE biotime_facilities");
        }
        $query = $this->db->insert_batch("biotime_facilities", $data);
        if ($query) {
            $n = $this->db->get("biotime_facilities");

            $message = print_r($this->exect()) . " save_facilities() Created Fcailities from Biotime " . $n->num_rows();
            // $this->db->insert("INSERT INTO `biotime_sync_log` (`serial_no`,  `last_gen`, `records`) VALUES (NULL, current_timestamp(), $n->num_rows());
            // ");
        } else {
            $message = print_r($this->exect()) . " Fetch Failities Failed ";
        }

        return $message;
    }
    public function addMachines($data)
    {
        if (!is_array($data) || count($data) < 1) {
            return "Failed to SYNC Biotime Devices — empty payload";
        }

        $this->db->truncate('biotime_devices');

        // UNIQUE KEY sn — skip duplicate serials from BioTime instead of failing the sync
        $stats = $this->insert_batch_skip_duplicates('biotime_devices', $data, 'sn');
        if ($stats['unique'] > 0) {
            $message = "Successful SYNC Biotime Devices " . $stats['inserted']
                . " (unique=" . $stats['unique'] . ", skipped_dupes=" . $stats['skipped_dupes'] . ")";
        } else {
            $message = "Failed to SYNC Biotime Devices";
        }

        return $message;
    }
    //not working as expected. should return querytime
    public function exect()
    {
        return  $this->benchmark->elapsed_time();
    }

    /**
     * Normalize employee codes for robust matching between BioTime and iHRIS.
     * Handles whitespace/case/noise differences without changing source data.
     */
    protected function normalize_emp_code($code)
    {
        if ($code === null) {
            return '';
        }
        $code = strtoupper(trim((string) $code));
        if ($code === '') {
            return '';
        }
        // Remove internal spaces and common separators occasionally present in exports.
        $code = str_replace(array(' ', "\t", "\r", "\n"), '', $code);
        return $code;
    }

    /**
     * True when value is digits-only (BioTime-safe numeric card / emp_code).
     */
    public function is_numeric_emp_code($code)
    {
        $code = trim((string) $code);
        return $code !== '' && ctype_digit($code);
    }

    /**
     * Strip iHRIS person| prefix (and any prefix before it).
     * UCMB person ids become 4253 + bare id (e.g. UCMB-person|113498 → 4253113498).
     */
    public function ihris_person_id_only($ihris_pid)
    {
        $pid = trim((string) $ihris_pid);
        if ($pid === '') {
            return '';
        }
        $idOnly = $pid;
        if (preg_match('/person\|(.+)$/i', $pid, $m)) {
            $idOnly = trim($m[1]);
        }
        if ($idOnly === '') {
            return '';
        }
        // UCMB BioTime emp_code: always prefix bare person id with 4253
        if (stripos($pid, 'UCMB') !== false) {
            return '4253' . $idOnly;
        }
        return $idOnly;
    }

    /**
     * BioTime emp_code for NEW enrollment: always bare iHRIS person id
     * (UCMB → 4253 + id). Existing BioTime emp_code is kept on updates
     * when biotime_emp_id + emp_code are already present.
     *
     * @param object|array $staff
     * @param array $overrides
     * @return string
     */
    public function resolve_biotime_emp_code($staff, array $overrides = [])
    {
        $s = is_array($staff) ? (object) $staff : $staff;
        if (!is_object($s)) {
            return '';
        }

        if (!empty($overrides['emp_code'])) {
            $overrideCode = trim((string) $overrides['emp_code']);
            // Allow override only when numeric person-style, or when updating an existing BioTime employee
            if ($this->is_numeric_emp_code($overrideCode) || !empty($s->biotime_emp_id)) {
                return $overrideCode;
            }
        }

        // Updates / transfers: keep the emp_code already stored in BioTime enrollment
        if (!empty($s->biotime_emp_id) && isset($s->emp_code) && trim((string) $s->emp_code) !== '') {
            return trim((string) $s->emp_code);
        }

        $pid = '';
        if (!empty($overrides['ihris_pid'])) {
            $pid = (string) $overrides['ihris_pid'];
        } elseif (!empty($s->ihris_pid)) {
            $pid = (string) $s->ihris_pid;
        }

        $person = $this->ihris_person_id_only($pid);
        // New enrollment emp_code must be digits-only person id (never card alphanumeric)
        if ($person !== '' && $this->is_numeric_emp_code($person)) {
            return $person;
        }
        return '';
    }

    /**
     * SQL expression: bare / UCMB-prefixed person emp_code from ihris_pid.
     * UCMB-person|123 → 4253123; person|123 → 123.
     * Used as the emp_code for all NEW enrollments.
     */
    public function sql_person_emp_code($alias = 'i')
    {
        $a = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $alias);
        if ($a === '') {
            $a = 'i';
        }
        return "CASE WHEN {$a}.ihris_pid LIKE '%UCMB%' THEN CONCAT('4253', TRIM(SUBSTRING_INDEX({$a}.ihris_pid, 'person|', -1))) WHEN {$a}.ihris_pid LIKE '%person|%' THEN TRIM(SUBSTRING_INDEX({$a}.ihris_pid, 'person|', -1)) WHEN TRIM({$a}.ihris_pid) REGEXP '^[0-9]+$' THEN TRIM({$a}.ihris_pid) ELSE TRIM(SUBSTRING_INDEX({$a}.ihris_pid, 'person|', -1)) END";
    }

    /**
     * SQL: emp_code used for new enrollment (= person id rule).
     */
    public function sql_resolved_emp_code($alias = 'i')
    {
        return $this->sql_person_emp_code($alias);
    }

    /**
     * Index-friendly JOIN: biotime_enrollment.emp_code → ihrisdata (card / ipps / person| / UCMB).
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
     * SQL ON fragment: match BioTime emp_code to person id, card_number, or ipps
     * (backward compatible with older card-based enrollments).
     */
    public function sql_emp_code_match_any($biotimeEmpExpr, $ihrisAlias = 'i')
    {
        $i = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $ihrisAlias);
        if ($i === '') {
            $i = 'i';
        }
        $expr = trim((string) $biotimeEmpExpr);
        if (preg_match('/^([a-zA-Z0-9_]+)\.emp_code$/', $expr, $m)) {
            return $this->sql_enrollment_to_ihris_on($m[1], $i);
        }
        $person = $this->sql_person_emp_code($i);
        return "("
            . "{$expr} = ({$person})"
            . " OR (NULLIF(TRIM({$i}.card_number), '') IS NOT NULL AND {$expr} = TRIM({$i}.card_number))"
            . " OR (NULLIF(TRIM({$i}.ipps), '') IS NOT NULL AND {$expr} = TRIM({$i}.ipps))"
            . ")";
    }

    /**
     * SQL ON clause fragment matching biotime emp_code to card_number, ipps, or person emp_code.
     */
    public function sql_emp_code_match($biotimeAlias = 'b', $ihrisAlias = 'i')
    {
        $b = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $biotimeAlias);
        $i = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $ihrisAlias);
        if ($b === '') {
            $b = 'b';
        }
        if ($i === '') {
            $i = 'i';
        }
        return $this->sql_emp_code_match_any("{$b}.emp_code", $i);
    }


    //process individuals

//     public function sync_attendance_data($date, $empcode = FALSE, $terminal_sn = FALSE)
// {
//     // PostgreSQL connection details
//     $pg_conn = pg_connect("host=172.27.1.101 port=7496 dbname=biotime user=postgres password=attendee@2020");

//     // Check PostgreSQL connection
//     if (!$pg_conn) {
//         throw new Exception("Connection to PostgreSQL failed!");
//     }

//     // Build dynamic conditions for the query
//     $conditions = "DATE_TRUNC('day', punch_time) = '$date'"; // Fixed date condition

//     if (!empty($empcode)) {
//         $conditions .= " AND emp_code = '$empcode'";
//     }

//     if (!empty($terminal_sn)) {
//         $conditions .= " AND terminal_sn = '$terminal_sn'";
//     }

//     // Construct the full query
//     $query = "SELECT emp_code, terminal_sn, area_alias, longitude, latitude, punch_state, punch_time 
//               FROM iclock_transaction 
//               WHERE $conditions";

//     // Execute the query
//     $result = pg_query($pg_conn, $query);

//     // Check for query errors
//     if (!$result) {
//         throw new Exception("Error executing query: " . pg_last_error($pg_conn));
//     }

//     // Process rows one by one
//     $row_count = 0;

//     while ($row = pg_fetch_assoc($result)) {
//         $datetime = date("Y-m-d H:i:s", strtotime($row['punch_time']));

//         // Insert row into MySQL
//         $insert_data = [
//             "emp_code" => $row['emp_code'],
//             "terminal_sn" => $row['terminal_sn'],
//             "area_alias" => $row['area_alias'],
//             "longitude" => $row['longitude'],
//             "latitude" => $row['latitude'],
//             "punch_state" => $row['punch_state'],
//             "punch_time" => $datetime,
//         ];

//         if (!$this->db->insert('biotime_data', $insert_data)) {
//             log_message('error', 'Insert failed for row: ' . json_encode($insert_data) . '. Error: ' . $this->db->error()['message']);
//         } else {
//             $row_count++;
//         }
//     }

//     // Close PostgreSQL connection
//     pg_close($pg_conn);

//     // Return the result
//     if ($row_count === 0) {
//         echo "No attendance data found for the given parameters.";
//     } else {
//         echo "Attendance data synced successfully! Total rows inserted: $row_count.";
//     }
// }



public function sync_attendance_data($date, $empcode = FALSE, $terminal_sn = FALSE)
{
    // PostgreSQL connection details
    $batch_size = 500;
    $pg_conn = pg_connect("host=172.27.1.101 port=7496 dbname=biotime user=postgres password=attendee@2020");

    // Check PostgreSQL connection
    if (!$pg_conn) {
        throw new Exception("Connection to PostgreSQL failed!");
    }

    // Build dynamic conditions for the query
    $conditions = "DATE_TRUNC('day', punch_time) = '$date'"; // Fixed date condition

    if (!empty($empcode)) {
        $conditions .= " AND emp_code = '$empcode'";
    }

    if (!empty($terminal_sn)) {
        $conditions .= " AND terminal_sn = '$terminal_sn'";
    }

    // Construct the full query
    $query = "SELECT emp_code, terminal_sn, area_alias, longitude, latitude, punch_state, punch_time 
              FROM iclock_transaction 
              WHERE $conditions";

    // Execute the query
    $result = pg_query($pg_conn, $query);

    // Check for query errors
    if (!$result) {
        throw new Exception("Error executing query: " . pg_last_error($pg_conn));
    }

    // Prepare data for MySQL insertion in batches
    $batch = [];
    $row_count = 0;
    $inserted_count = 0;

    while ($row = pg_fetch_assoc($result)) {
        $datetime = date("Y-m-d H:i:s", strtotime($row['punch_time']));
        $batch[] = [
            "emp_code" => $row['emp_code'],
            "terminal_sn" => $row['terminal_sn'],
            "area_alias" => $row['area_alias'],
            "longitude" => $row['longitude'],
            "latitude" => $row['latitude'],
            "punch_state" => $row['punch_state'],
            "punch_time" => $datetime,
        ];

        $row_count++;

        // Insert batch into MySQL when the batch size is reached
        if (count($batch) >= $batch_size) {
            if ($this->db->insert_batch('biotime_data', $batch)) {
                $inserted_count += count($batch);
            } else {
                log_message('error', 'Batch insert failed: ' . $this->db->error()['message']);
            }
            $batch = []; // Clear the batch array
        }
    }

    // Insert any remaining rows in the batch
    if (!empty($batch)) {
        if ($this->db->insert_batch('biotime_data', $batch)) {
            $inserted_count += count($batch);
        } else {
            log_message('error', 'Final batch insert failed: ' . $this->db->error()['message']);
        }
    }

    // Close PostgreSQL connection
    pg_close($pg_conn);

    // Return the result
    if ($row_count === 0) {
        echo "No attendance data found for the given parameters.";
    } else {
        echo "Attendance data synced successfully! Total rows retrieved: $row_count. Total rows inserted: $inserted_count.";
    }
}


    
    

    

    /**
     * Fetch time history from PostgreSQL database for a date range
     * 
     * @param string $start_date Start date/time in Y-m-d H:i:s format
     * @param string $end_date End date/time in Y-m-d H:i:s format
     * @param string|bool $terminal_sn Terminal serial number (default: FALSE = all terminals)
     * @param string|bool $empcode Employee code filter (default: FALSE = all employees)
     * @param int $batch_size Batch size for inserts (default: 2500; larger = fewer round-trips, faster)
     * @return array Result array with status, message, records_saved, and statistics
     */
    public function fetch_time_history($start_date, $end_date, $terminal_sn = FALSE, $empcode = FALSE, $batch_size = 2500)
    {
        $result = array(
            'status' => 'error',
            'message' => '',
            'records_fetched' => 0,
            'records_saved' => 0,
            'errors' => array()
        );
        
        try {
            // If syncing a specific terminal, delete existing rows in MySQL for the same terminal + time range
            // to make same-day re-syncs idempotent (avoid duplicates).
            if (!empty($terminal_sn)) {
                $this->db->where('terminal_sn', $terminal_sn);
                $this->db->where('punch_time >=', $start_date);
                $this->db->where('punch_time <=', $end_date);
                $this->db->delete('biotime_data');
            }

            // Get PostgreSQL connection details from environment or use defaults
            // Try $_ENV first, then getenv() as fallback
            $pg_host = isset($_ENV['PG_DB_HOST']) ? $_ENV['PG_DB_HOST'] : (getenv('PG_DB_HOST') ?: '172.27.1.101');
            $pg_port = isset($_ENV['PG_PORT']) ? $_ENV['PG_PORT'] : (getenv('PG_PORT') ?: '7496');
            $pg_db = isset($_ENV['PG_DB_NAME']) ? $_ENV['PG_DB_NAME'] : (getenv('PG_DB_NAME') ?: 'biotime');
            $pg_user = isset($_ENV['PG_USER']) ? $_ENV['PG_USER'] : (getenv('PG_USER') ?: 'postgres');
            $pg_pass = isset($_ENV['PG_PASS']) ? $_ENV['PG_PASS'] : (getenv('PG_PASS') ?: 'attendee@2020');
            
            // Build connection string (pg_connect handles escaping internally)
            $pg_conn_string = "host=$pg_host port=$pg_port dbname=$pg_db user=$pg_user password=$pg_pass connect_timeout=10";
            
            // Connect to PostgreSQL
            $pg_conn = @pg_connect($pg_conn_string);
            
            if (!$pg_conn) {
                $error = error_get_last();
                $error_msg = $error ? $error['message'] : 'Unknown connection error';
                throw new Exception("Connection to PostgreSQL failed! Host: $pg_host, Port: $pg_port, DB: $pg_db. Error: $error_msg");
            }
            
            // Build query conditions
            $conditions = "punch_time >= '$start_date' AND punch_time <= '$end_date'";
            
            if (!empty($terminal_sn)) {
                $terminal_sn_escaped = pg_escape_string($pg_conn, $terminal_sn);
                $conditions .= " AND terminal_sn = '$terminal_sn_escaped'";
            }
            
            if (!empty($empcode)) {
                $empcode_escaped = pg_escape_string($pg_conn, $empcode);
                $conditions .= " AND emp_code = '$empcode_escaped'";
            }

            // Construct the query
            $query = "SELECT emp_code, terminal_sn, area_alias, longitude, latitude, punch_state, punch_time 
                      FROM iclock_transaction 
                      WHERE $conditions
                      ORDER BY punch_time ASC";
            
            // Execute the query
            $pg_result = pg_query($pg_conn, $query);
            
            if (!$pg_result) {
                $error = pg_last_error($pg_conn);
                throw new Exception("Error executing PostgreSQL query: $error");
            }
            
            // Get total count for progress tracking
            $total_rows = pg_num_rows($pg_result);
            $result['records_fetched'] = $total_rows;
            
            if ($total_rows == 0) {
                pg_close($pg_conn);
                $result['status'] = 'success';
                $result['message'] = 'No records found for the specified date range';
                return $result;
            }
            
            // Prepare data for MySQL insertion in batches
            $batch = array();
            $inserted_count = 0;
            $processed_count = 0;
            
            while ($row = pg_fetch_assoc($pg_result)) {
                $datetime = date("Y-m-d H:i:s", strtotime($row['punch_time']));
                
                $batch[] = array(
                    "emp_code" => isset($row['emp_code']) ? $row['emp_code'] : '',
                    "terminal_sn" => isset($row['terminal_sn']) ? $row['terminal_sn'] : '',
                    "area_alias" => isset($row['area_alias']) ? $row['area_alias'] : '',
                    "longitude" => !empty($row['longitude']) ? $row['longitude'] : NULL,
                    "latitude" => !empty($row['latitude']) ? $row['latitude'] : NULL,
                    "punch_state" => isset($row['punch_state']) ? $row['punch_state'] : '',
                    "punch_time" => $datetime
                );
                
                $processed_count++;
                
                // Insert batch into MySQL when the batch size is reached
                if (count($batch) >= $batch_size) {
                    if ($this->db->insert_batch('biotime_data', $batch)) {
                        $inserted_count += count($batch);
                    } else {
                        $error = $this->db->error();
                        $error_msg = isset($error['message']) ? $error['message'] : 'Unknown error';
                        $result['errors'][] = "Batch insert failed: $error_msg";
                        log_message('error', 'fetch_time_history() batch insert failed: ' . $error_msg);
                    }
                    $batch = array(); // Clear the batch array
                }
            }
            
            // Insert any remaining rows in the batch
            if (!empty($batch)) {
                if ($this->db->insert_batch('biotime_data', $batch)) {
                    $inserted_count += count($batch);
                } else {
                    $error = $this->db->error();
                    $error_msg = isset($error['message']) ? $error['message'] : 'Unknown error';
                    $result['errors'][] = "Final batch insert failed: $error_msg";
                    log_message('error', 'fetch_time_history() final batch insert failed: ' . $error_msg);
                }
            }
            
            // Clean up invalid records
            $this->db->query("DELETE FROM biotime_data WHERE emp_code='0'");

           // DD($batch);
            
            // Close PostgreSQL connection
            pg_close($pg_conn);
            
            $result['records_saved'] = $inserted_count;
            $result['status'] = ($inserted_count > 0) ? 'success' : 'error';
            $result['message'] = "Fetched $total_rows records, saved $inserted_count records to database";
            
            if (!empty($result['errors'])) {
                $result['message'] .= ". Errors: " . count($result['errors']);
            }
            
        } catch (Exception $e) {
            $result['status'] = 'error';
            $result['message'] = "Exception: " . $e->getMessage();
            $result['errors'][] = $e->getMessage();
            log_message('error', 'fetch_time_history() exception: ' . $e->getMessage());
            
            if (isset($pg_conn) && $pg_conn) {
                pg_close($pg_conn);
            }
        } catch (Error $e) {
            $result['status'] = 'error';
            $result['message'] = "Fatal Error: " . $e->getMessage();
            $result['errors'][] = $e->getMessage();
            log_message('error', 'fetch_time_history() fatal error: ' . $e->getMessage());
            
            if (isset($pg_conn) && $pg_conn) {
                pg_close($pg_conn);
            }
        }
        
        return $result;
    }

    /**
     * Fetch time history from PostgreSQL and merge clock-in/clock-out into clk_log as we stream (per batch).
     * Avoids one big aggregation at the end; spreads load and keeps transactions short.
     * Call biotimeNightAndActualsOnly() after all devices for any remaining actuals (optional; actuals are also inserted per batch).
     * Streamed rows are archived to biotime_data_history. Every clock-in merged into clk_log is reflected in actuals per batch.
     *
     * @param string $start_date Start date/time Y-m-d H:i:s
     * @param string $end_date End date/time Y-m-d H:i:s
     * @param string|bool $area_alias Area name (matches area_name in biotime_devices; PG column is area_alias). False = all areas.
     * @param string|bool $empcode Employee code filter (false = all)
     * @param int $batch_size Rows per batch (default 50)
     * @return array status, message, records_fetched, records_saved, clock_log_merged, errors
     */
    public function fetch_time_history_with_clocking($start_date, $end_date, $area_alias = FALSE, $empcode = FALSE, $batch_size = 50)
    {
        $result = array(
            'status' => 'error',
            'message' => '',
            'records_fetched' => 0,
            'records_saved' => 0,
            'clock_log_merged' => 0,
            'errors' => array(),
            'debug' => array(
                'lookup_ihris_rows' => 0,
                'lookup_emp_map_keys' => 0,
                'lookup_device_areas' => 0,
                'batch_rows' => 0,
                'mapped_rows' => 0,
                'unmapped_emp_rows' => 0,
                'missing_device_area_rows' => 0,
                'unmapped_emp_samples' => array(),
            ),
            'timing' => array(
                'pg_query_s' => 0,
                'lookups_s' => 0,
                'history_s' => 0,
                'aggregate_s' => 0,
                'clk_log_s' => 0,
                'actuals_s' => 0,
                'night_s' => 0,
                'total_s' => 0
            )
        );
        $time_total_start = microtime(true);

        try {
            // Not writing to biotime_data; no delete needed. Data goes to biotime_data_history for archiving.

            $pg_host = isset($_ENV['PG_DB_HOST']) ? $_ENV['PG_DB_HOST'] : (getenv('PG_DB_HOST') ?: '172.27.1.101');
            $pg_port = isset($_ENV['PG_PORT']) ? $_ENV['PG_PORT'] : (getenv('PG_PORT') ?: '7496');
            $pg_db   = isset($_ENV['PG_DB_NAME']) ? $_ENV['PG_DB_NAME'] : (getenv('PG_DB_NAME') ?: 'biotime');
            $pg_user = isset($_ENV['PG_USER']) ? $_ENV['PG_USER'] : (getenv('PG_USER') ?: 'postgres');
            $pg_pass = isset($_ENV['PG_PASS']) ? $_ENV['PG_PASS'] : (getenv('PG_PASS') ?: 'attendee@2020');
            $pg_conn_string = "host=$pg_host port=$pg_port dbname=$pg_db user=$pg_user password=$pg_pass connect_timeout=10";
            $pg_conn = @pg_connect($pg_conn_string);
            if (!$pg_conn) {
                $err = error_get_last();
                throw new Exception("PostgreSQL connection failed: " . ($err ? $err['message'] : 'Unknown'));
            }

            $t0 = microtime(true);
            $conditions = "punch_time >= '$start_date' AND punch_time <= '$end_date'";
            if (!empty($area_alias)) {
                $conditions .= " AND area_alias = '" . pg_escape_string($pg_conn, $area_alias) . "'";
            }
            if (!empty($empcode)) {
                $conditions .= " AND emp_code = '" . pg_escape_string($pg_conn, $empcode) . "'";
            }
            $pg_result = pg_query($pg_conn, "SELECT emp_code, terminal_sn, area_alias, punch_time FROM iclock_transaction WHERE $conditions ORDER BY punch_time ASC");
            if (!$pg_result) {
                throw new Exception("PostgreSQL query failed: " . pg_last_error($pg_conn));
            }
            $total_rows = pg_num_rows($pg_result);
            $result['timing']['pg_query_s'] = round(microtime(true) - $t0, 3);
            $result['records_fetched'] = $total_rows;
            if ($total_rows == 0) {
                pg_close($pg_conn);
                $result['status'] = 'success';
                $result['message'] = 'No records for range';
                $result['timing']['total_s'] = round(microtime(true) - $time_total_start, 3);
                return $result;
            }

            $t1 = microtime(true);
            $emp_to_pid = array();
            $pid_to_department = array();
            $dept_col = $this->db->field_exists('department_id', 'ihrisdata') ? 'department_id' : 'department';
            $q = $this->db->query("SELECT card_number, ipps, ihris_pid, " . $dept_col . " AS dept FROM ihrisdata");
            if ($q && $q->num_rows() > 0) {
                $result['debug']['lookup_ihris_rows'] = (int) $q->num_rows();
                foreach ($q->result() as $r) {
                    $pid = $r->ihris_pid;
                    if (!empty($r->card_number)) {
                        $raw = (string) $r->card_number;
                        $emp_to_pid[$raw] = $pid;
                        $norm = $this->normalize_emp_code($raw);
                        if ($norm !== '') {
                            $emp_to_pid[$norm] = $pid;
                        }
                    }
                    if (!empty($r->ipps)) {
                        $raw = (string) $r->ipps;
                        $emp_to_pid[$raw] = $pid;
                        $norm = $this->normalize_emp_code($raw);
                        if ($norm !== '') {
                            $emp_to_pid[$norm] = $pid;
                        }
                    }
                    // Non-numeric cards enroll as bare iHRIS person id — match punches the same way as ipps
                    if ($pid !== null && $pid !== '') {
                        $idOnly = $this->ihris_person_id_only($pid);
                        if ($idOnly !== '') {
                            $emp_to_pid[$idOnly] = $pid;
                            $normId = $this->normalize_emp_code($idOnly);
                            if ($normId !== '') {
                                $emp_to_pid[$normId] = $pid;
                            }
                        }
                        $pid_to_department[$pid] = isset($r->dept) ? $r->dept : null;
                    }
                }
            }
            $result['debug']['lookup_emp_map_keys'] = count($emp_to_pid);
            $devices = array();
            $has_night_col = $this->db->field_exists('has_night', 'biotime_devices');
            $q2 = $this->db->query("SELECT sn, area_code, area_name" . ($has_night_col ? ", COALESCE(has_night, 0) AS has_night" : "") . " FROM biotime_devices");
            if ($q2 && $q2->num_rows() > 0) {
                foreach ($q2->result() as $r) {
                    $an = isset($r->area_name) ? $r->area_name : '';
                    if ($an !== '' && !isset($devices[$an])) {
                        $devices[$an] = array(
                            'facility_id' => isset($r->area_code) ? $r->area_code : '',
                            'facility' => $an,
                            'has_night' => ($has_night_col && !empty($r->has_night)) ? 1 : 0
                        );
                    }
                }
            }
            $result['debug']['lookup_device_areas'] = count($devices);
            $run_night_correction = false;
            if (!empty($area_alias)) {
                $run_night_correction = isset($devices[$area_alias]) && !empty($devices[$area_alias]['has_night']);
            } else {
                foreach ($devices as $d) {
                    if (!empty($d['has_night'])) {
                        $run_night_correction = true;
                        break;
                    }
                }
            }
            $schedule_id = 22;
            $schedule_color = '';
            $sq = $this->db->query("SELECT schedule_id, color FROM schedules WHERE schedule_id = ? LIMIT 1", array(22));
            if ($sq && $sq->num_rows() > 0) {
                $sr = $sq->row();
                $schedule_id = $sr->schedule_id;
                $schedule_color = isset($sr->color) ? $sr->color : '';
            }
            $result['timing']['lookups_s'] = round(microtime(true) - $t1, 3);

            $batch = array();
            $inserted_count = 0;
            $clock_merged = 0;
            $night_merged = 0;
            $actuals_merged = 0;
            $batch_num = 0;
            $time_history = 0;
            $time_agg = 0;
            $time_clk = 0;
            $time_actuals = 0;
            $time_night = 0;
            $night_every_n = 5;
            $night_pending_times = array();

            while ($row = pg_fetch_assoc($pg_result)) {
                $datetime = date("Y-m-d H:i:s", strtotime($row['punch_time']));
                $emp_code = isset($row['emp_code']) ? $row['emp_code'] : '';
                $area_name = isset($row['area_alias']) ? $row['area_alias'] : '';
                $norm_emp_code = $this->normalize_emp_code($emp_code);
                $result['debug']['batch_rows']++;
                if ($datetime && (isset($emp_to_pid[$emp_code]) || ($norm_emp_code !== '' && isset($emp_to_pid[$norm_emp_code])))) {
                    $result['debug']['mapped_rows']++;
                } else {
                    $result['debug']['unmapped_emp_rows']++;
                    if (count($result['debug']['unmapped_emp_samples']) < 10) {
                        $result['debug']['unmapped_emp_samples'][] = array(
                            'emp_code' => (string) $emp_code,
                            'normalized' => $norm_emp_code,
                            'area_alias' => (string) $area_name
                        );
                    }
                }
                if ($area_name !== '' && !isset($devices[$area_name])) {
                    $result['debug']['missing_device_area_rows']++;
                }
                $batch[] = array(
                    'emp_code'     => $emp_code,
                    'terminal_sn'  => isset($row['terminal_sn']) ? $row['terminal_sn'] : '',
                    'area_alias'   => $area_name,
                    'longitude'    => NULL,
                    'latitude'     => NULL,
                    'punch_state'  => '',
                    'punch_time'   => $datetime
                );

                if (count($batch) >= $batch_size) {
                    $batch_num++;
                    $this->db->trans_start();
                    $tb = microtime(true);
                    if ($this->_insert_biotime_history_batch($batch)) {
                        $inserted_count += count($batch);
                    }
                    $time_history += microtime(true) - $tb;

                    $ta = microtime(true);
                    $agg = $this->_aggregate_batch_for_clk_log($batch, $emp_to_pid, $devices);
                    $time_agg += microtime(true) - $ta;

                    if (!empty($agg)) {
                        $tc = microtime(true);
                        $n = $this->_upsert_clk_log_batch($agg);
                        $clock_merged += $n;
                        $time_clk += microtime(true) - $tc;

                        $tac = microtime(true);
                        $actuals_merged += $this->_insert_actuals_for_batch($agg, $pid_to_department, $schedule_id, $schedule_color);
                        $time_actuals += microtime(true) - $tac;
                    }
                    if ($run_night_correction) {
                        $batch_times = $this->_batch_punch_time_range($batch);
                        if (!empty($batch_times)) {
                            $night_pending_times[] = $batch_times;
                        }
                        if ($night_every_n > 0 && count($night_pending_times) >= $night_every_n) {
                            $tn = microtime(true);
                            $night_merged += $this->_apply_night_correction_range($night_pending_times);
                            $time_night += microtime(true) - $tn;
                            $night_pending_times = array();
                        }
                    }
                    $this->db->trans_complete();
                    $batch = array();
                }
            }

            if (!empty($batch)) {
                $this->db->trans_start();
                $tb = microtime(true);
                if ($this->_insert_biotime_history_batch($batch)) {
                    $inserted_count += count($batch);
                }
                $time_history += microtime(true) - $tb;

                $ta = microtime(true);
                $agg = $this->_aggregate_batch_for_clk_log($batch, $emp_to_pid, $devices);
                $time_agg += microtime(true) - $ta;

                if (!empty($agg)) {
                    $tc = microtime(true);
                    $clock_merged += $this->_upsert_clk_log_batch($agg);
                    $time_clk += microtime(true) - $tc;

                    $tac = microtime(true);
                    $actuals_merged += $this->_insert_actuals_for_batch($agg, $pid_to_department, $schedule_id, $schedule_color);
                    $time_actuals += microtime(true) - $tac;
                }
                if ($run_night_correction) {
                    $batch_times = $this->_batch_punch_time_range($batch);
                    if (!empty($batch_times)) {
                        $night_pending_times[] = $batch_times;
                    }
                    if (!empty($night_pending_times)) {
                        $tn = microtime(true);
                        $night_merged += $this->_apply_night_correction_range($night_pending_times);
                        $time_night += microtime(true) - $tn;
                    }
                }
                $this->db->trans_complete();
            }

            pg_close($pg_conn);

            $result['timing']['history_s'] = round($time_history, 3);
            $result['timing']['aggregate_s'] = round($time_agg, 3);
            $result['timing']['clk_log_s'] = round($time_clk, 3);
            $result['timing']['actuals_s'] = round($time_actuals, 3);
            $result['timing']['night_s'] = round($time_night, 3);
            $result['timing']['total_s'] = round(microtime(true) - $time_total_start, 3);

            $result['records_saved'] = $inserted_count;
            $result['clock_log_merged'] = $clock_merged;
            $result['night_corrected'] = $night_merged;
            $result['actuals_merged'] = $actuals_merged;
            $result['status'] = $inserted_count > 0 ? 'success' : 'error';
            $result['message'] = "Fetched $total_rows, saved $inserted_count, clk_log $clock_merged, night $night_merged, actuals $actuals_merged";
        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
            $result['errors'][] = $e->getMessage();
            log_message('error', 'fetch_time_history_with_clocking: ' . $e->getMessage());
            if (isset($pg_conn) && $pg_conn) {
                pg_close($pg_conn);
            }
        } catch (Error $e) {
            $result['message'] = $e->getMessage();
            $result['errors'][] = $e->getMessage();
            log_message('error', 'fetch_time_history_with_clocking Error: ' . $e->getMessage());
            if (isset($pg_conn) && $pg_conn) {
                pg_close($pg_conn);
            }
        }
        return $result;
    }

    /**
     * Aggregate a batch of biotime rows into (log_date, ihris_pid) -> time_in, time_out, facility_id, facility, location.
     */
    protected function _aggregate_batch_for_clk_log($batch, $emp_to_pid, $devices)
    {
        $agg = array();
        foreach ($batch as $r) {
            $emp = isset($r['emp_code']) ? $r['emp_code'] : '';
            $norm_emp = $this->normalize_emp_code($emp);
            $pid = isset($emp_to_pid[$emp]) ? $emp_to_pid[$emp] : (isset($emp_to_pid[$norm_emp]) ? $emp_to_pid[$norm_emp] : null);
            if (!$pid) {
                continue;
            }
            $area = isset($r['area_alias']) ? $r['area_alias'] : '';
            $dev = isset($devices[$area]) ? $devices[$area] : array('facility_id' => '', 'facility' => '');
            $facility_id = $dev['facility_id'];
            $facility = $dev['facility'];
            $location = isset($r['area_alias']) && $r['area_alias'] !== '' ? $r['area_alias'] : $facility;
            $punch = isset($r['punch_time']) ? $r['punch_time'] : '';
            if (!$punch) {
                continue;
            }
            $log_date = date('Y-m-d', strtotime($punch));
            $key = $log_date . "\t" . $pid;
            if (!isset($agg[$key])) {
                $agg[$key] = array(
                    'log_date'    => $log_date,
                    'ihris_pid'   => $pid,
                    'time_in'     => $punch,
                    'time_out'    => $punch,
                    'facility_id'  => $facility_id,
                    'facility'    => $facility,
                    'location'    => $location
                );
            } else {
                if (strtotime($punch) < strtotime($agg[$key]['time_in'])) {
                    $agg[$key]['time_in'] = $punch;
                }
                if (strtotime($punch) > strtotime($agg[$key]['time_out'])) {
                    $agg[$key]['time_out'] = $punch;
                }
            }
        }
        return array_values($agg);
    }

    /**
     * Bulk upsert into clk_log (merge time_in/time_out with existing).
     */
    protected function _upsert_clk_log_batch($rows)
    {
        if (empty($rows)) {
            return 0;
        }
        $values = array();
        $params = array();
        foreach ($rows as $r) {
            $entry_id = $r['log_date'] . $r['ihris_pid'];
            $time_out = $r['time_in'] === $r['time_out'] ? null : $r['time_out'];
            $values[] = "(?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params[] = $entry_id;
            $params[] = $r['ihris_pid'];
            $params[] = $r['facility_id'];
            $params[] = $r['time_in'];
            $params[] = $time_out;
            $params[] = $r['log_date'];
            $params[] = $r['location'];
            $params[] = 'BIO-TIME';
            $params[] = $r['facility'];
        }
        $sql = "INSERT INTO clk_log (entry_id, ihris_pid, facility_id, time_in, time_out, date, location, source, facility) VALUES " . implode(', ', $values);
        $sql .= " ON DUPLICATE KEY UPDATE time_in = LEAST(time_in, VALUES(time_in)), time_out = GREATEST(COALESCE(time_out, time_in), COALESCE(VALUES(time_out), VALUES(time_in))), facility_id = IF(VALUES(time_in) < time_in, VALUES(facility_id), facility_id), location = IF(VALUES(time_in) < time_in, VALUES(location), location), facility = IF(VALUES(time_in) < time_in, VALUES(facility), facility), source = 'BIO-TIME'";
        $this->db->query($sql, $params);
        return count($rows);
    }

    /**
     * Insert into actuals for the (date, ihris_pid) entries just merged into clk_log. Uses preloaded lookups
     * to avoid heavy JOINs; single INSERT IGNORE with VALUES for speed.
     * @param array $agg Aggregated rows from _aggregate_batch_for_clk_log (log_date, ihris_pid, facility_id, ...)
     * @param array $pid_to_department Map ihris_pid => department_id (or department)
     * @param string|int $schedule_id Schedule ID (e.g. 22)
     * @param string $schedule_color Color for schedule
     * @return int Number of actuals inserted (skipped duplicates do not count)
     */
    protected function _insert_actuals_for_batch($agg, $pid_to_department, $schedule_id = 22, $schedule_color = '')
    {
        if (empty($agg)) {
            return 0;
        }
        $stream = 'BIO-TIME';
        $values = array();
        $params = array();
        foreach ($agg as $r) {
            $entry_id = $r['log_date'] . $r['ihris_pid'];
            $dept = isset($pid_to_department[$r['ihris_pid']]) ? $pid_to_department[$r['ihris_pid']] : null;
            $end_date = date('Y-m-d', strtotime($r['log_date'] . ' +1 day'));
            $values[] = '(?, ?, ?, ?, ?, ?, ?, ?, ?)';
            $params[] = $entry_id;
            $params[] = isset($r['facility_id']) ? $r['facility_id'] : '';
            $params[] = $dept;
            $params[] = $r['ihris_pid'];
            $params[] = $schedule_id;
            $params[] = $schedule_color;
            $params[] = $r['log_date'];
            $params[] = $end_date;
            $params[] = $stream;
        }
        $sql = "INSERT IGNORE INTO actuals (entry_id, facility_id, department_id, ihris_pid, schedule_id, color, date, end, stream) VALUES " . implode(', ', $values);
        $this->db->query($sql, $params);
        $inserted = $this->db->affected_rows();
        if ($inserted > 0) {
            $facilities = [];
            foreach ($agg as $r) {
                if (!empty($r['facility_id'])) {
                    $facilities[(string) $r['facility_id']] = true;
                }
            }
            if (!empty($facilities)) {
                $CI =& get_instance();
                $CI->load->library('dashboard_cache_store', null, 'dash_cache');
                foreach (array_keys($facilities) as $facility_id) {
                    $CI->dash_cache->invalidateFacility($facility_id);
                }
            }
        }
        return $inserted;
    }

    /**
     * Insert batch into biotime_data_history using explicit column list to avoid duplicate-column errors.
     * Columns: emp_code, terminal_sn, area_alias, longitude, latitude, punch_state, punch_time.
     * @param array $batch Array of rows with those keys
     * @return bool Success
     */
    protected function _insert_biotime_history_batch($batch)
    {
        if (empty($batch)) {
            return true;
        }
        $cols = array('emp_code', 'terminal_sn', 'area_alias', 'longitude', 'latitude', 'punch_state', 'punch_time');
        $values = array();
        $params = array();
        foreach ($batch as $r) {
            $values[] = '(?, ?, ?, ?, ?, ?, ?)';
            $params[] = isset($r['emp_code']) ? $r['emp_code'] : '';
            $params[] = isset($r['terminal_sn']) ? $r['terminal_sn'] : '';
            $params[] = isset($r['area_alias']) ? $r['area_alias'] : '';
            $params[] = isset($r['longitude']) ? $r['longitude'] : null;
            $params[] = isset($r['latitude']) ? $r['latitude'] : null;
            $params[] = isset($r['punch_state']) ? $r['punch_state'] : '';
            $params[] = isset($r['punch_time']) ? $r['punch_time'] : null;
        }
        $sql = 'INSERT INTO biotime_data_history (' . implode(', ', $cols) . ') VALUES ' . implode(', ', $values);
        $this->db->query($sql, $params);
        return true;
    }

    /**
     * Return min/max punch_time for a batch (for night correction range).
     * @param array $batch Batch rows with 'punch_time'
     * @return array|null ['min'=>..., 'max'=>...] or null if empty
     */
    protected function _batch_punch_time_range($batch)
    {
        $times = array();
        foreach ($batch as $r) {
            if (!empty($r['punch_time'])) {
                $times[] = $r['punch_time'];
            }
        }
        if (empty($times)) {
            return null;
        }
        return array('min' => min($times), 'max' => max($times));
    }

    /**
     * Apply night-shift correction for a combined time range (multiple batches).
     * Single UPDATE for the whole range to reduce MySQL round-trips. Caller holds transaction.
     * @param array $ranges Array of ['min'=>..., 'max'=>...] from _batch_punch_time_range
     * @return int Affected rows
     */
    protected function _apply_night_correction_range($ranges)
    {
        if (empty($ranges)) {
            return 0;
        }
        $global_min = $ranges[0]['min'];
        $global_max = $ranges[0]['max'];
        foreach ($ranges as $r) {
            if (isset($r['min']) && $r['min'] < $global_min) {
                $global_min = $r['min'];
            }
            if (isset($r['max']) && $r['max'] > $global_max) {
                $global_max = $r['max'];
            }
        }
        $this->db->query("
            UPDATE clk_log cl
            INNER JOIN duty_rosta dr ON dr.ihris_pid = cl.ihris_pid AND dr.duty_date = cl.date AND dr.schedule_id = '16'
            INNER JOIN (
                SELECT i.ihris_pid, DATE_SUB(DATE(b.punch_time), INTERVAL 1 DAY) AS log_date, MAX(b.punch_time) AS punch_time
                FROM biotime_data_history b
                JOIN ihrisdata i ON (b.emp_code = i.card_number OR b.emp_code = i.ipps OR b.emp_code = CASE WHEN i.ihris_pid LIKE '%UCMB%' THEN CONCAT('4253', TRIM(SUBSTRING_INDEX(i.ihris_pid, 'person|', -1))) ELSE TRIM(SUBSTRING_INDEX(i.ihris_pid, 'person|', -1)) END)
                WHERE b.punch_time >= ? AND b.punch_time <= ?
                GROUP BY i.ihris_pid, log_date
            ) sub ON sub.ihris_pid = cl.ihris_pid AND sub.log_date = cl.date
            SET cl.time_out = sub.punch_time
            WHERE sub.punch_time > cl.time_in
            AND TIMESTAMPDIFF(HOUR, cl.time_in, sub.punch_time) <= 15
        ", array($global_min, $global_max));
        return $this->db->affected_rows();
    }

    /**
     * Apply night-shift correction for one batch (schedule 16). Used when not batching night in stream.
     * @param array $batch Batch rows with 'punch_time'
     * @return int Affected rows
     */
    protected function _apply_night_correction_batch($batch)
    {
        $range = $this->_batch_punch_time_range($batch);
        if (!$range) {
            return 0;
        }
        $this->db->trans_start();
        $this->db->query("
            UPDATE clk_log cl
            INNER JOIN duty_rosta dr ON dr.ihris_pid = cl.ihris_pid AND dr.duty_date = cl.date AND dr.schedule_id = '16'
            INNER JOIN (
                SELECT i.ihris_pid, DATE_SUB(DATE(b.punch_time), INTERVAL 1 DAY) AS log_date, MAX(b.punch_time) AS punch_time
                FROM biotime_data_history b
                JOIN ihrisdata i ON (b.emp_code = i.card_number OR b.emp_code = i.ipps OR b.emp_code = CASE WHEN i.ihris_pid LIKE '%UCMB%' THEN CONCAT('4253', TRIM(SUBSTRING_INDEX(i.ihris_pid, 'person|', -1))) ELSE TRIM(SUBSTRING_INDEX(i.ihris_pid, 'person|', -1)) END)
                WHERE b.punch_time >= ? AND b.punch_time <= ?
                GROUP BY i.ihris_pid, log_date
            ) sub ON sub.ihris_pid = cl.ihris_pid AND sub.log_date = cl.date
            SET cl.time_out = sub.punch_time
            WHERE sub.punch_time > cl.time_in
            AND TIMESTAMPDIFF(HOUR, cl.time_in, sub.punch_time) <= 15
        ", array($range['min'], $range['max']));
        $n = $this->db->affected_rows();
        $this->db->trans_complete();
        return $n;
    }

}