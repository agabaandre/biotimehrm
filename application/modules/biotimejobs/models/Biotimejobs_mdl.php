<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Biotimejobs_mdl extends CI_Model
{
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
            $this->db->query("TRUNCATE biotime_departments");
        }
        $query = $this->db->insert_batch("biotime_departments", $data);
        if ($query) {
            $n = $this->db->query("select id biotime_departments");

            $message = print_r($this->exect()) . " save_department() Created Departments from Biotime " . $n->num_rows();
            // $this->db->insert("INSERT INTO `biotime_sync_log` (`serial_no`,  `last_gen`, `records`) VALUES (NULL, current_timestamp(), $n->num_rows());
            // ");
        } else {
            $message = print_r($this->exect()) . " Fetch Departments Failed ";
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
    
        // Fetch all rows as associative arrays
        $rows = pg_fetch_all($result);
    
        // Close PostgreSQL connection
        pg_close($pg_conn);
    
        // If no rows are found, exit early
        if (empty($rows)) {
            echo  "No attendance data found for the given parameters.";
        }
    
        // Prepare data for MySQL insertion
        $insert = [];
        foreach ($rows as $row) {
            $datetime = date("Y-m-d H:i:s", strtotime($row['punch_time']));
            $insert[] = [
                "emp_code" => $row['emp_code'],
                "terminal_sn" => $row['terminal_sn'],
                "area_alias" => $row['area_alias'],
                "longitude" => $row['longitude'],
                "latitude" => $row['latitude'],
                "punch_state" => $row['punch_state'],
                "punch_time" => $datetime,
            ];
        }
    
        // Insert data into MySQL in batches
        foreach (array_chunk($insert, $batch_size) as $batch) {
            if (!$this->db->insert_batch('biotime_data', $batch)) {
                log_message('error', 'Batch insert failed: ' . $this->db->error()['message']);
            }
        }
    
        echo  "Attendance data synced successfully!";
    }
    
    

    

    // public function get_attendance_data($date, $empcode = FALSE, $terminal_sn = FALSE)
    // {
       

    //             $conn = pg_connect("host=172.27.1.105 port=7496 dbname=biotime user=postgres password=attendee@2020");
    //             if ($conn) {
    //                 echo "Connection successful!";
    //             } else {
    //                 echo "Connection failed!";
    //             }



    

    //     if (!empty($empcode)) {
    //         $empcode = "AND  emp_code ='$empcode'";
    //     }

    //     if (!empty($terminal_sn)) {
    //         $terminal_sn = "AND terminal_sn = '$terminal_sn'";
    //     }

   


    //     $data = $conn->query("SELECT emp_code, terminal_sn, area_alias, longitude, latitude, punch_state, punch_time FROM iclock_transaction WHERE DATE_TRUNC('day', punch_time)= '$date' $empcode $terminal_sn" )->result();
    //     //($data);

    //     return $data;

    // }

}
