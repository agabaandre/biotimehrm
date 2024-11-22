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
    public function fetch_time_history($start_date, $end_date, $device)
{
    // PostgreSQL connection details
    $conn = pg_connect("host=172.27.1.105 port=7496 dbname=biotime user=postgres password=attendee@2020");

    if (!$conn) {
        throw new Exception("Connection to PostgreSQL failed!");
    }

    // Query with explicit parameter casting
    $query = "SELECT emp_code, terminal_sn, area_alias, longitude, latitude, punch_state, punch_time 
              FROM iclock_transaction 
              WHERE DATE_TRUNC('day', punch_time) BETWEEN $1::date AND $2::date 
              AND terminal_sn = $3";

    $params = [$start_date, $end_date, $device];

    $result = pg_query_params($conn, $query, $params);

    if (!$result) {
        throw new Exception("Error executing query: " . pg_last_error($conn));
    }

    // Fetch all data as an associative array
    $data = pg_fetch_all($result);

    // Close the connection
    pg_close($conn);

    return $data ?: []; // Return an empty array if no data is found
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
