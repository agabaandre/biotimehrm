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
     * @param int $batch_size Batch size for inserts (default: 1000)
     * @return array Result array with status, message, records_saved, and statistics
     */
    public function fetch_time_history($start_date, $end_date, $terminal_sn = FALSE, $empcode = FALSE, $batch_size = 1000)
    {
        $result = array(
            'status' => 'error',
            'message' => '',
            'records_fetched' => 0,
            'records_saved' => 0,
            'errors' => array()
        );
        
        try {
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

}
