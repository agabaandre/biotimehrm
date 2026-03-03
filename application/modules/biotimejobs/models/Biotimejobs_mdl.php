<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Biotimejobs_mdl extends CI_Model
{
    protected $facility;

    public  function __construct()
    {
        parent::__construct();
        $this->facility = $_SESSION['facility'];
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

    public function add_enrolled($data)
    {
        if ($count = count($data) > 1) {
            $this->db->query("CALL `fingerpints_cache`()");
            $this->db->query("TRUNCATE fingerprints_staging");
        }
        $query = $this->db->insert_batch('fingerprints_staging', $data);



        if ($query) {
            $n = $this->db->query("select entry_id from fingerprints_staging");

            $message = print_r($this->exect()) . " saveEnrolled() add_enrolled() Created Enrolled users from Biotime " . $n->num_rows();

            // $this->db->insert("INSERT INTO `biotime_sync_log` (`serial_no`,  `last_gen`, `records`) VALUES (NULL, current_timestamp(), $n->num_rows() ));
            // ");
        } else {
            $message = print_r($this->exect()) . " saveEnrolled() add_enrolled() Failed ";
        }

        return $message;
    }
    public function add_time_logs($data)
    {
        if (count($data) > 1) {
            $this->db->query("CALL `biotime_cache`()");
            $this->db->query("TRUNCATE biotime_data");
        }
        $query = $this->db->insert_batch('biotime_data', $data);
        $this->db->query(" DELETE from biotime_data where emp_code='0'");




        if ($query) {
            $n = $this->db->get("biotime_data");

            $message = print_r($this->exect()) . " fetchBiotTimeLogs()  add_time_logs() Created Logs from Biotime " . $n->num_rows();
            // $this->db->insert("INSERT INTO `biotime_sync_log` (`serial_no`,  `last_gen`, `records`) VALUES (NULL, current_timestamp(), $n->num_rows());
            // ");
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
        foreach ($data as $row) {
            $dept_code = isset($row['dept_code']) ? $this->db->escape($row['dept_code']) : 'NULL';
            $dept_name = isset($row['dept_name']) ? $this->db->escape($row['dept_name']) : 'NULL';
            $values[] = "($dept_code, $dept_name)";
        }
        
        // Use REPLACE INTO - this will delete existing row if dept_code matches and insert new one
        // id is auto-increment, so we don't include it - MySQL will generate new id
        $sql = "REPLACE INTO biotime_departments (dept_code, dept_name) VALUES " . implode(', ', $values);
        
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

       
        $this->db->truncate('biotime_devices');
        
        $query = $this->db->insert_batch('biotime_devices', $data);
        if ($query) {
            $message = "Successful SYNC Biotime Devices " . $this->db->affected_rows();
        } else {
            $message = "Failed to SYNC Biotime Decices";
        }

        return $message;
    }
    //not working as expected. should return querytime
    public function exect()
    {
        return  $this->benchmark->elapsed_time();
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
     * @param string|bool $terminal_sn Terminal serial (false = all)
     * @param string|bool $empcode Employee code filter (false = all)
     * @param int $batch_size Rows per batch (default 20; smaller reduces deadlock risk)
     * @return array status, message, records_fetched, records_saved, clock_log_merged, errors
     */
    public function fetch_time_history_with_clocking($start_date, $end_date, $terminal_sn = FALSE, $empcode = FALSE, $batch_size = 20)
    {
        $result = array(
            'status' => 'error',
            'message' => '',
            'records_fetched' => 0,
            'records_saved' => 0,
            'clock_log_merged' => 0,
            'errors' => array()
        );

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

            $conditions = "punch_time >= '$start_date' AND punch_time <= '$end_date'";
            if (!empty($terminal_sn)) {
                $conditions .= " AND terminal_sn = '" . pg_escape_string($pg_conn, $terminal_sn) . "'";
            }
            if (!empty($empcode)) {
                $conditions .= " AND emp_code = '" . pg_escape_string($pg_conn, $empcode) . "'";
            }
            $pg_result = pg_query($pg_conn, "SELECT emp_code, terminal_sn, area_alias, punch_time FROM iclock_transaction WHERE $conditions ORDER BY punch_time ASC");
            if (!$pg_result) {
                throw new Exception("PostgreSQL query failed: " . pg_last_error($pg_conn));
            }
            $total_rows = pg_num_rows($pg_result);
            $result['records_fetched'] = $total_rows;
            if ($total_rows == 0) {
                pg_close($pg_conn);
                $result['status'] = 'success';
                $result['message'] = 'No records for range';
                return $result;
            }

            $emp_to_pid = array();
            $q = $this->db->query("SELECT card_number, ipps, ihris_pid FROM ihrisdata");
            if ($q && $q->num_rows() > 0) {
                foreach ($q->result() as $r) {
                    if (!empty($r->card_number)) {
                        $emp_to_pid[$r->card_number] = $r->ihris_pid;
                    }
                    if (!empty($r->ipps)) {
                        $emp_to_pid[$r->ipps] = $r->ihris_pid;
                    }
                }
            }
            $devices = array();
            $q2 = $this->db->query("SELECT sn, area_code, area_name FROM biotime_devices");
            if ($q2 && $q2->num_rows() > 0) {
                foreach ($q2->result() as $r) {
                    $devices[$r->sn] = array('facility_id' => $r->area_code, 'facility' => $r->area_name);
                }
            }

            $batch = array();
            $inserted_count = 0;
            $clock_merged = 0;
            $night_merged = 0;
            $actuals_merged = 0;
            $batch_num = 0;
            $stream_col = $this->db->field_exists('source', 'clk_log') ? 'cl.source' : "'BIO-TIME'";

            while ($row = pg_fetch_assoc($pg_result)) {
                $datetime = date("Y-m-d H:i:s", strtotime($row['punch_time']));
                $batch[] = array(
                    'emp_code'     => isset($row['emp_code']) ? $row['emp_code'] : '',
                    'terminal_sn'  => isset($row['terminal_sn']) ? $row['terminal_sn'] : '',
                    'area_alias'   => isset($row['area_alias']) ? $row['area_alias'] : '',
                    'longitude'    => NULL,
                    'latitude'     => NULL,
                    'punch_state'  => '',
                    'punch_time'   => $datetime
                );

                if (count($batch) >= $batch_size) {
                    $batch_num++;
                    if ($this->db->insert_batch('biotime_data_history', $batch)) {
                        $inserted_count += count($batch);
                    }
                    $agg = $this->_aggregate_batch_for_clk_log($batch, $emp_to_pid, $devices);
                    if (!empty($agg)) {
                        $this->_apply_batch_clocking_with_retry($agg, $batch, $stream_col, $clock_merged, $night_merged, $actuals_merged);
                    }
                    $batch = array();
                }
            }

            if (!empty($batch)) {
                if ($this->db->insert_batch('biotime_data_history', $batch)) {
                    $inserted_count += count($batch);
                }
                $agg = $this->_aggregate_batch_for_clk_log($batch, $emp_to_pid, $devices);
                if (!empty($agg)) {
                    $this->_apply_batch_clocking_with_retry($agg, $batch, $stream_col, $clock_merged, $night_merged, $actuals_merged);
                }
            }

            pg_close($pg_conn);

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
     * Run clk_log upsert, actuals insert, and night correction for a batch with deadlock retry.
     * @param array $agg aggregated rows for clk_log
     * @param array $batch raw batch for night correction
     * @param string $stream_col stream literal
     * @param int $clock_merged accumulated count (by reference)
     * @param int $night_merged accumulated count (by reference)
     * @param int $actuals_merged accumulated count (by reference)
     */
    protected function _apply_batch_clocking_with_retry($agg, $batch, $stream_col, &$clock_merged, &$night_merged, &$actuals_merged)
    {
        $max_attempts = 3;
        for ($attempt = 1; $attempt <= $max_attempts; $attempt++) {
            try {
                $n = $this->_upsert_clk_log_batch($agg);
                $clock_merged += $n;
                $actuals_merged += $this->_insert_actuals_for_batch($agg, $stream_col);
                $night_merged += $this->_apply_night_correction_batch($batch);
                return;
            } catch (Exception $e) {
                $msg = $e->getMessage();
                $is_retryable = (strpos($msg, 'Deadlock found') !== false) || (strpos($msg, 'Lock wait timeout exceeded') !== false);
                if ($is_retryable && $attempt < $max_attempts) {
                    usleep(100000 * (1 + rand(0, 20))); // 100–2100 ms jitter
                    continue;
                }
                throw $e;
            }
        }
    }

    /**
     * Aggregate a batch of biotime rows into (log_date, ihris_pid) -> time_in, time_out, facility_id, facility, location.
     */
    protected function _aggregate_batch_for_clk_log($batch, $emp_to_pid, $devices)
    {
        $agg = array();
        foreach ($batch as $r) {
            $emp = isset($r['emp_code']) ? $r['emp_code'] : '';
            $pid = isset($emp_to_pid[$emp]) ? $emp_to_pid[$emp] : null;
            if (!$pid) {
                continue;
            }
            $ts = isset($r['terminal_sn']) ? $r['terminal_sn'] : '';
            $dev = isset($devices[$ts]) ? $devices[$ts] : array('facility_id' => '', 'facility' => '');
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
     * Insert into actuals for the (date, ihris_pid) entries just merged into clk_log so every clock-in is reflected in actuals.
     * Uses INSERT IGNORE so if the record already exists (e.g. from another device or a prior run) we skip without error.
     * @param array $agg Aggregated rows from _aggregate_batch_for_clk_log (log_date, ihris_pid, ...)
     * @param string $stream_col SQL fragment for stream column (e.g. 'cl.source' or "'BIO-TIME'")
     * @return int Number of actuals inserted (skipped duplicates do not count)
     */
    protected function _insert_actuals_for_batch($agg, $stream_col = "'BIO-TIME'")
    {
        if (empty($agg)) {
            return 0;
        }
        $entry_ids = array();
        foreach ($agg as $r) {
            $entry_ids[] = $r['log_date'] . $r['ihris_pid'];
        }
        $placeholders = implode(',', array_fill(0, count($entry_ids), '?'));
        $this->db->trans_start();
        $this->db->query("
            INSERT IGNORE INTO actuals (entry_id, facility_id, department_id, ihris_pid, schedule_id, color, date, end, stream)
            SELECT CONCAT(cl.date, cl.ihris_pid), cl.facility_id, COALESCE(id.department_id, id.department), cl.ihris_pid, s.schedule_id, s.color, cl.date, DATE_ADD(cl.date, INTERVAL 1 DAY), {$stream_col}
            FROM clk_log cl
            JOIN ihrisdata id ON id.ihris_pid = cl.ihris_pid
            JOIN schedules s ON s.schedule_id = 22
            LEFT JOIN actuals a ON a.entry_id = CONCAT(cl.date, cl.ihris_pid)
            WHERE a.entry_id IS NULL
            AND CONCAT(cl.date, cl.ihris_pid) IN ($placeholders)
        ", $entry_ids);
        $n = $this->db->affected_rows();
        $this->db->trans_complete();
        return $n;
    }

    /**
     * Apply night-shift correction for one batch: set clk_log.time_out from next-day punches in this batch (schedule 16).
     * Scoped to batch time range to avoid long locks; run after each batch during streaming.
     * @param array $batch Batch rows with 'punch_time' (Y-m-d H:i:s)
     * @return int Affected rows
     */
    protected function _apply_night_correction_batch($batch)
    {
        if (empty($batch)) {
            return 0;
        }
        $times = array();
        foreach ($batch as $r) {
            if (!empty($r['punch_time'])) {
                $times[] = $r['punch_time'];
            }
        }
        if (empty($times)) {
            return 0;
        }
        $batch_min = min($times);
        $batch_max = max($times);
        $this->db->trans_start();
        $this->db->query("
            UPDATE clk_log cl
            INNER JOIN duty_rosta dr ON dr.ihris_pid = cl.ihris_pid AND dr.duty_date = cl.date AND dr.schedule_id = '16'
            INNER JOIN (
                SELECT i.ihris_pid, DATE_SUB(DATE(b.punch_time), INTERVAL 1 DAY) AS log_date, MAX(b.punch_time) AS punch_time
                FROM biotime_data_history b
                JOIN ihrisdata i ON (b.emp_code = i.card_number OR b.emp_code = i.ipps)
                WHERE b.punch_time >= ? AND b.punch_time <= ?
                GROUP BY i.ihris_pid, log_date
            ) sub ON sub.ihris_pid = cl.ihris_pid AND sub.log_date = cl.date
            SET cl.time_out = sub.punch_time
            WHERE sub.punch_time > cl.time_in
            AND TIMESTAMPDIFF(HOUR, cl.time_in, sub.punch_time) <= 15
        ", array($batch_min, $batch_max));
        $n = $this->db->affected_rows();
        $this->db->trans_complete();
        return $n;
    }

}
