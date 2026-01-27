<?php
use function GuzzleHttp\json_encode;
date_default_timezone_set('Africa/Kampala');
defined('BASEPATH') or exit('No direct script access allowed');

use utils\HttpUtils;

class Biotimejobs extends MX_Controller
{

    private $username;
    private $password;

    private $facility;
    //private $biotimejobs_mdl;

    public function __construct()
    {
        parent::__construct();

        $this->username = Modules::run('svariables/getSettings')->biotime_username;
        $this->password = Modules::run('svariables/getSettings')->biotime_password;
        $this->load->model('biotimejobs_mdl');
        // $this->facility = $_SESSION['facility'];
    }

    public function index()
    {
        echo "BIO-TIME HERE";
    }


    public function get_token($uri = FALSE)
    {

        $http = new HttpUtils();
        $headers = ['Content-Type' => 'application/json'];
        $body = array(
            "username" => $this->username,
            "password" => $this->password
        );
        $response = $http->sendRequest('jwt-api-token-auth', "POST", $headers, $body, $search = FALSE);
        //dd($response->token);
        return $response->token;
    }


    //get terminals
    public function terminals()
    {
        $http = new HttpUtils();
        $headr = array();
        $headr[] = 'Content-length: 0';
        $headr[] = 'Content-type: application/json';
        $headr[] = 'Authorization: JWT ' . $this->get_token();



        $query = array(
            'page_size' => 5000000
        );

        $params = '?' . http_build_query($query);
        $endpoint = 'iclock/api/terminals/' . $params;

        $response = $http->curlgetHttp($endpoint, $headr, []);
        //print_r($response->data);
        // exit();
        $insert1 = array();
        foreach ($response->data as $terminal) {


            $insert = array(
                'sn' => $terminal->sn,
                'ip_address' => $terminal->ip_address,
                'area_code' => $terminal->area->area_code,
                'user_count' => $terminal->user_count,
                'face_count' => $terminal->face_count,
                'palm_count' => $terminal->palm_count,
                'area_name' => $terminal->area_name,
                'last_activity' => $terminal->last_activity
            );
            array_push($insert1, $insert);
        }
        $message = $this->biotimejobs_mdl->addMachines($insert1);
        $this->log($message);
        $process = 1;
        $method = "bioitimejobs/terminals";
        if (count($response) > 0) {
            $status = "successful";
        } else {
            $status = "failed";
        }
        $this->cronjob_register($process, $method, $status);

        return ($response);
    }
    //cron job
    //Fetches ihris stafflsit via the api
    // public function get_ihrisdata()
    // {
    //     $http = new HttpUtils();
    //     $headers = [
    //         'Content-Type' => 'application/json',
    //         'Accept' => 'application/json',
    //     ];

    //     $response = $http->sendiHRISRequest('apiv1/index.php/api/ihrisdata', "GET", $headers, []);

    //     if ($response) {
    //         //dd(count($response));
    //         //$message = $this->biotimejobs_mdl->add_ihrisdata($response);
    //         $this->db->query("TRUNCATE table ihrisdata");
    //         foreach($response as $data){

    //             $message = $this->db->replace('ihrisdata', $data);
    //             ///dd($this->last->query);
    //         }
           
    //         $this->log($message);
    //     }
    //     $process = 2;
    //     $method = "bioitimejobs/get_ihrisdata";
    //     if (count($response) > 0) {
    //         $status = "successful";
    //     } else {
    //         $status = "failed";
    //     }
    //     $this->cronjob_register($process, $method, $status);
    //     $this->get_ucmbdata();
    //     $this->update_ipps();
    // }
    //employees all enrolled users before creating new ones.
	public function get_ihrisdata()
{
    $http = new HttpUtils();
    $headers = [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ];

    $response = $http->sendiHRISRequest('apiv1/index.php/api/ihrisdata', "GET", $headers, []);

    if ($response) {
        // Optional: You can truncate if you want to replace all data every time.
        // $this->db->query("TRUNCATE table ihrisdata");

        $inserted = 0;
        $errors = [];

        foreach ($response as $data) {
            try {
                // Use REPLACE INTO or INSERT ... ON DUPLICATE KEY UPDATE
                $this->db->replace('ihrisdata', $data); // Assumes primary key/unique key exists in table
                $inserted++;
            } catch (Exception $e) {
                // Continue on error, but log it
                $errors[] = $e->getMessage();
                continue;
            }
        }

        $this->log("Inserted: $inserted. Errors: " . count($errors));
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->log("Insert error: $error");
            }
        }
    }

    $process = 2;
    $method = "bioitimejobs/get_ihrisdata";
    $status = (count($response) > 0) ? "successful" : "failed";
    $this->cronjob_register($process, $method, $status);

    $this->get_ucmbdata();
    $this->update_ipps();
}


    public function update_ipps()
    {
        // Select records where card_number is NULL and ipps is NOT NULL
        $ipps_nos = $this->db->query("SELECT ihris_pid, ipps FROM ihrisdata WHERE card_number IS NULL AND ipps IS NOT NULL")->result();

        foreach ($ipps_nos as $ipps_no) {
            $ipps = $ipps_no->ipps*1;
            $id = $ipps_no->ihris_pid;

            // Update the card_number for each record
            $this->db->query("UPDATE ihrisdata SET card_number = '$ipps' WHERE ihris_pid = '$id'");
        }
    }



    public function get_ucmbdata()
    {
        $http = new HttpUtils();
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        $response = $http->sendUCMBiHRISRequest('apiv1/index.php/api/ihrisdata', "GET", $headers, []);

        if ($response) {

            foreach ($response as $data) {

                $message = $this->db->replace('ihrisdata', $data);
                ///dd($this->last->query);
            }
            $this->log($message);
        }
        $process = 2;
        $method = "bioitimejobs/get_ihrisdata";
        if (count($response) > 0) {
            $status = "successful";
        } else {
            $status = "failed";
        }
        $this->cronjob_register($process, $method, $status);
    }

    public function get_Enrolled($page = FALSE)
    {

        $http = new HttpUtils();
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => "JWT " . $this->get_token(),
        ];

        // $endpoint='iclock/api/transactions/';
        $endpoint = 'personnel/api/employees/';
        $options = (object) array(

            "page" => $page
        );


        $response = $http->get_List($endpoint, "GET", $headers, $options);
        return $response;
    }
    //cronjob
    //get enrolled data from biotime
    //after nun  call fingerprint cache procedure
    public function saveEnrolled()
    {
        try {
        $resp = $this->get_Enrolled();
            
            if (empty($resp) || !isset($resp->count)) {
                log_message('error', 'saveEnrolled: Invalid response from get_Enrolled()');
                return false;
            }
            
        $count = $resp->count;
        $pages = (int) ceil($count / 10);
        $rows = array();

        for ($currentPage = 1; $currentPage <= $pages; $currentPage++) {
            $response = $this->get_Enrolled($currentPage);
                
                if (empty($response) || !isset($response->data)) {
                    log_message('error', "saveEnrolled: Invalid response for page $currentPage");
                    continue;
                }
                
            foreach ($response->data as $mydata) {
                    // Check if required properties exist
                    if (empty($mydata->area) || !isset($mydata->area[0]) || !isset($mydata->emp_code)) {
                        log_message('error', 'saveEnrolled: Missing required data in employee record');
                        continue;
                    }

                $data = array(
                    'entry_id' => $mydata->area[0]->area_code . '-' . $mydata->emp_code,
                    "card_number" => $mydata->emp_code,
                    'facilityId' => $mydata->area[0]->area_code,
                    'source' => 'Biotime',
                        'device' => isset($mydata->enroll_sn) ? $mydata->enroll_sn : '',
                        'att_status' => isset($mydata->enable_att) ? $mydata->enable_att : 0
                );

                array_push($rows, $data);
            }
        }

            if (empty($rows)) {
                log_message('error', 'saveEnrolled: No rows to insert');
                return false;
            }

        $message = $this->biotimejobs_mdl->add_enrolled($rows);
        $this->log($message);
        $process = 3;
        $method = "bioitimejobs/save_Enrolled";
            if (count($rows) > 0) {
            $status = "successful";
        } else {
            $status = "failed";
        }
        $this->cronjob_register($process, $method, $status);
            
            return true;
        } catch (Exception $e) {
            log_message('error', 'saveEnrolled Exception: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return false;
        } catch (Error $e) {
            log_message('error', 'saveEnrolled Fatal Error: ' . $e->getMessage());
            return false;
        }
    }


    //get cron jobs from the server
    

    /**
     * Get time logs from BioTime API with support for date range and terminal filtering
     * 
     * @param int $page Page number (default: 1)
     * @param string|bool $end_date End date in Y-m-d or Y-m-d H:i:s format (default: FALSE = current date/time)
     * @param string|bool $terminal Terminal serial number (default: FALSE = all terminals)
     * @param string|bool $start_date Start date in Y-m-d or Y-m-d H:i:s format (default: FALSE = 24 hours before end_date)
     * @param int $max_retries Maximum number of retry attempts on failure (default: 3)
     * @return object|bool API response object or FALSE on failure
     */
    public function getTime($page = 1, $end_date = FALSE, $terminal = FALSE, $start_date = FALSE, $max_retries = 3)
    {
        date_default_timezone_set('Africa/Kampala');
        $http = new HttpUtils();
        
        $attempt = 0;
        while ($attempt < $max_retries) {
            try {
                $headers = [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => "JWT " . $this->get_token(),
                ];
                
                // Handle date parameters
                if (empty($end_date)) {
                    $edate = date('Y-m-d H:i:s');
                } else {
                    // If only date is provided (Y-m-d), add time component
                    if (strlen($end_date) == 10) {
                        $edate = $end_date . ' 23:59:59';
                    } else {
                        $edate = $end_date;
                    }
                }
                
                // Handle start date
                if (empty($start_date)) {
                    $sdate = date("Y-m-d H:i:s", strtotime("-24 hours", strtotime($edate)));
                } else {
                    // If only date is provided (Y-m-d), add time component
                    if (strlen($start_date) == 10) {
                        $sdate = $start_date . ' 00:00:00';
                    } else {
                        $sdate = $start_date;
                    }
                }
                
                // Ensure start_date is before end_date
                if (strtotime($sdate) > strtotime($edate)) {
                    $temp = $sdate;
                    $sdate = $edate;
                    $edate = $temp;
                }

                // Build query parameters
                $query = array(
                    'page' => $page,
                    'start_time' => $sdate,
                    'end_time' => $edate,
                );
                
                // Add terminal filter if provided
                if (!empty($terminal)) {
                    $query['terminal_sn'] = $terminal;
                }

                $params = '?' . http_build_query($query);
                $endpoint = 'iclock/api/transactions/' . $params;

                $response = $http->getTimeLogs($endpoint, "GET", $headers);
                
                // Validate response
                if (!isset($response) || !is_object($response)) {
                    throw new Exception("Invalid API response format");
                }
                
                // Check for API errors in response
                if (isset($response->error) || isset($response->detail)) {
                    $error_msg = isset($response->error) ? $response->error : $response->detail;
                    throw new Exception("API Error: " . $error_msg);
                }
                
                return $response;
                
            } catch (Exception $e) {
                $attempt++;
                $this->log("getTime() attempt $attempt failed: " . $e->getMessage());
                
                if ($attempt >= $max_retries) {
                    $this->log("getTime() failed after $max_retries attempts");
                    return FALSE;
                }
                
                // Wait before retry (exponential backoff)
                sleep(pow(2, $attempt - 1));
            } catch (Error $e) {
                $attempt++;
                $this->log("getTime() fatal error on attempt $attempt: " . $e->getMessage());
                
                if ($attempt >= $max_retries) {
                    return FALSE;
                }
                
                sleep(pow(2, $attempt - 1));
            }
        }
        
        return FALSE;
    }


    /**
     * Fetch BioTime logs from API and save to database
     * 
     * @param string|bool $end_date End date in Y-m-d or Y-m-d H:i:s format (default: FALSE = current date)
     * @param string|bool $terminal Terminal serial number (default: FALSE = all terminals)
     * @param string|bool $start_date Start date in Y-m-d or Y-m-d H:i:s format (default: FALSE = 24 hours before end_date)
     * @param int $batch_size Number of records per page (default: 10, API default)
     * @param callable|null $progress_callback Optional callback function for progress updates
     * @return array Result array with status, message, and statistics
     */
    public function fetchBiotTimeLogs($end_date = FALSE, $terminal = FALSE, $start_date = FALSE, $batch_size = 10, $progress_callback = NULL)
    {
        ignore_user_abort(true);
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');
        
        $result = array(
            'status' => 'error',
            'message' => '',
            'records_fetched' => 0,
            'records_saved' => 0,
            'pages_processed' => 0,
            'errors' => array()
        );
        
        try {
            // Get first page to determine total count
            $resp = $this->getTime(1, $end_date, $terminal, $start_date);
            
            if ($resp === FALSE) {
                throw new Exception("Failed to fetch initial data from API");
            }
            
            if (!isset($resp->count) || !isset($resp->data)) {
                throw new Exception("Invalid API response structure");
            }
            
            $count = (int) $resp->count;
            $pages = (int) ceil($count / $batch_size);
            
            if ($pages == 0) {
                $result['status'] = 'success';
                $result['message'] = 'No records found for the specified date range';
                return $result;
            }
            
            $rows = array();
            $total_processed = 0;
            
            // Process all pages
            for ($currentPage = 1; $currentPage <= $pages; $currentPage++) {
                $response = $this->getTime($currentPage, $end_date, $terminal, $start_date);
                
                if ($response === FALSE) {
                    $error_msg = "Failed to fetch page $currentPage";
                    $result['errors'][] = $error_msg;
                    $this->log("fetchBiotTimeLogs() error: $error_msg");
                    continue;
                }
                
                if (!isset($response->data) || !is_array($response->data)) {
                    $error_msg = "Invalid data structure on page $currentPage";
                    $result['errors'][] = $error_msg;
                    $this->log("fetchBiotTimeLogs() error: $error_msg");
                    continue;
                }
                
                // Process records from this page
                foreach ($response->data as $mydata) {
                    if (!isset($mydata->punch_time) || !isset($mydata->emp_code)) {
                        continue; // Skip invalid records
                    }
                    
                    $datetime = date("Y-m-d H:i:s", strtotime($mydata->punch_time));
                    
                    $data = array(
                        "emp_code" => isset($mydata->emp_code) ? $mydata->emp_code : '',
                        "terminal_sn" => isset($mydata->terminal_sn) ? $mydata->terminal_sn : '',
                        "area_alias" => isset($mydata->area_alias) ? $mydata->area_alias : '',
                        "longitude" => isset($mydata->longitude) ? $mydata->longitude : NULL,
                        "latitude" => isset($mydata->latitude) ? $mydata->latitude : NULL,
                        "punch_state" => isset($mydata->punch_state) ? $mydata->punch_state : '',
                        "punch_time" => $datetime
                    );
                    array_push($rows, $data);
                    $total_processed++;
                }
                
                $result['pages_processed'] = $currentPage;
                
                // Call progress callback if provided
                if (is_callable($progress_callback)) {
                    call_user_func($progress_callback, array(
                        'page' => $currentPage,
                        'total_pages' => $pages,
                        'records_processed' => $total_processed,
                        'total_records' => $count
                    ));
                }
                
                // Insert in batches to avoid memory issues
                if (count($rows) >= 1000) {
                    $message = $this->biotimejobs_mdl->add_time_logs($rows);
                    $result['records_saved'] += count($rows);
                    $rows = array(); // Clear array
                }
            }
            
            // Insert remaining records
            if (count($rows) > 0) {
                $message = $this->biotimejobs_mdl->add_time_logs($rows);
                $result['records_saved'] += count($rows);
            }
            
            $result['records_fetched'] = $total_processed;
            $result['status'] = 'success';
            $result['message'] = "Successfully fetched and saved $total_processed records";
            
            $this->logattendance($result['message']);
            
            // Register cronjob
            $process = 4;
            $method = "bioitimejobs/fetchBiotTimeLogs";
            $status = ($result['records_saved'] > 0) ? "successful" : "failed";
            $this->cronjob_register($process, $method, $status);
            
            // Process clock-in/out data
            $this->biotimeClockin();
            
        } catch (Exception $e) {
            $result['status'] = 'error';
            $result['message'] = "Error: " . $e->getMessage();
            $result['errors'][] = $e->getMessage();
            $this->log("fetchBiotTimeLogs() exception: " . $e->getMessage());
        } catch (Error $e) {
            $result['status'] = 'error';
            $result['message'] = "Fatal Error: " . $e->getMessage();
            $result['errors'][] = $e->getMessage();
            $this->log("fetchBiotTimeLogs() fatal error: " . $e->getMessage());
        }
        
        return $result;
    }

    /**
     * Custom logs endpoint for frontend - syncs individual machines
     * Supports async background processing with proper JSON responses
     * 
     * @return void Outputs JSON response
     */
    public function custom_logs()
    {
        header('Content-Type: application/json');
        
        try {
            // Get parameters
            $end_date_input = $this->input->get('end_date');
            $terminal_sn = $this->input->get('terminal_sn');
            $start_date_input = $this->input->get('start_date');
            $sync_type = $this->input->get('sync_type') ?: 'attendance';
            $batch_size = (int) ($this->input->get('batch_size') ?: 10);
            $async = $this->input->get('async') !== 'false'; // Default to async
            
            // Validate terminal_sn
            if (empty($terminal_sn)) {
                throw new Exception("Terminal serial number (terminal_sn) is required");
            }
            
            // Validate and set dates
            if (empty($end_date_input)) {
                $end_date = date('Y-m-d');
            } else {
                $end_date = date('Y-m-d', strtotime($end_date_input));
                if ($end_date === '1970-01-01' || $end_date === FALSE) {
                    throw new Exception("Invalid end_date format. Expected Y-m-d format.");
                }
            }
            
            if (empty($start_date_input)) {
                $start_date = FALSE; // Will default to 24 hours before end_date in getTime()
            } else {
                $start_date = date('Y-m-d', strtotime($start_date_input));
                if ($start_date === '1970-01-01' || $start_date === FALSE) {
                    throw new Exception("Invalid start_date format. Expected Y-m-d format.");
                }
            }
            
            // Get facility name for logging
            $facility = 'Unknown';
            if (!empty($terminal_sn)) {
                $machine = $this->db->query("SELECT area_name FROM biotime_devices WHERE sn = ?", array($terminal_sn))->row();
                if ($machine && isset($machine->area_name)) {
                    $facility = $machine->area_name;
                }
            }
            
            // Prepare response data
            $response = array(
                'status' => 'initiated',
                'message' => 'Sync process started',
                'timestamp' => date('Y-m-d H:i:s'),
                'parameters' => array(
                    'terminal_sn' => $terminal_sn,
                    'facility' => $facility,
                    'start_date' => $start_date ?: 'auto (24h before end_date)',
                    'end_date' => $end_date,
                    'sync_type' => $sync_type,
                    'batch_size' => $batch_size,
                    'async' => $async
                )
            );
            
            // Log the sync request
            $log_message = "custom_logs() - Terminal: $terminal_sn, Facility: $facility, Start: " . ($start_date ?: 'auto') . ", End: $end_date, Type: $sync_type, Batch: $batch_size, Async: " . ($async ? 'yes' : 'no');
            $this->log($log_message);
            
            if ($async) {
                // Async processing - return immediately and run in background
                if (function_exists('fastcgi_finish_request')) {
                    // Send response immediately
                    echo json_encode($response, JSON_PRETTY_PRINT);
                    fastcgi_finish_request();
                } else {
                    // For non-FastCGI, flush output
                    echo json_encode($response, JSON_PRETTY_PRINT);
                    if (ob_get_level() > 0) {
                        ob_end_flush();
                    }
                    flush();
                }
                
                // Run sync in background
                $this->run_sync_background($terminal_sn, $start_date, $end_date, $facility, $sync_type, $batch_size);
                
            } else {
                // Synchronous processing - wait for completion
                $result = $this->fetchBiotTimeLogs($end_date, $terminal_sn, $start_date, $batch_size);
                
                $response['status'] = $result['status'];
                $response['message'] = $result['message'];
                $response['records_fetched'] = $result['records_fetched'];
                $response['records_saved'] = $result['records_saved'];
                $response['pages_processed'] = $result['pages_processed'];
                
                if (!empty($result['errors'])) {
                    $response['errors'] = $result['errors'];
                }
                
                echo json_encode($response, JSON_PRETTY_PRINT);
            }
            
        } catch (Exception $e) {
            $error_response = array(
                'status' => 'error',
                'message' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            );
            echo json_encode($error_response, JSON_PRETTY_PRINT);
            $this->log("custom_logs() exception: " . $e->getMessage());
        } catch (Error $e) {
            $error_response = array(
                'status' => 'error',
                'message' => "Fatal Error: " . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            );
            echo json_encode($error_response, JSON_PRETTY_PRINT);
            $this->log("custom_logs() fatal error: " . $e->getMessage());
        }
    }
    
    /**
     * Run sync in background (for async processing)
     * 
     * @param string $terminal_sn Terminal serial number
     * @param string|bool $start_date Start date
     * @param string $end_date End date
     * @param string $facility Facility name
     * @param string $sync_type Sync type
     * @param int $batch_size Batch size
     * @return void
     */
    private function run_sync_background($terminal_sn, $start_date, $end_date, $facility, $sync_type, $batch_size)
    {
        ignore_user_abort(true);
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');
        
        try {
            $this->log("run_sync_background() started for terminal $terminal_sn");
            
            $result = $this->fetchBiotTimeLogs($end_date, $terminal_sn, $start_date, $batch_size);
            
            $this->log("run_sync_background() completed for terminal $terminal_sn: " . $result['message']);
            
            // Update machine last_activity if successful
            if ($result['status'] === 'success' && $result['records_saved'] > 0) {
                $this->db->where('sn', $terminal_sn);
                $this->db->update('biotime_devices', array('last_activity' => $end_date));
            }
            
        } catch (Exception $e) {
            $this->log("run_sync_background() exception for terminal $terminal_sn: " . $e->getMessage());
        } catch (Error $e) {
            $this->log("run_sync_background() fatal error for terminal $terminal_sn: " . $e->getMessage());
        }
    }
    
    /**
     * Sync individual machine endpoint with progress tracking
     * Returns JSON response suitable for frontend terminal display
     * 
     * @param string $terminal_sn Terminal serial number (URL parameter)
     * @param string $end_date End date in Y-m-d format (URL parameter, optional)
     * @return void Outputs JSON response
     */
    public function syncMachine($terminal_sn = FALSE, $end_date = FALSE)
    {
        header('Content-Type: application/json');
        
        try {
            // Get parameters from URL or GET
            if (empty($terminal_sn)) {
                $terminal_sn = $this->input->get('terminal_sn') ?: $this->uri->segment(3);
            }
            
            if (empty($end_date)) {
                $end_date = $this->input->get('end_date') ?: $this->uri->segment(4);
            }
            
            // Validate terminal_sn
            if (empty($terminal_sn)) {
                throw new Exception("Terminal serial number is required");
            }
            
            // Set default end_date if not provided
            if (empty($end_date)) {
                $end_date = date('Y-m-d');
            } else {
                $end_date = date('Y-m-d', strtotime($end_date));
                if ($end_date === '1970-01-01' || $end_date === FALSE) {
                    throw new Exception("Invalid end_date format. Expected Y-m-d format.");
                }
            }
            
            // Get machine info
            $machine = $this->db->query("SELECT * FROM biotime_devices WHERE sn = ?", array($terminal_sn))->row();
            
            if (empty($machine)) {
                throw new Exception("Machine with serial number '$terminal_sn' not found");
            }
            
            $facility = isset($machine->area_name) ? $machine->area_name : 'Unknown';
            $start_date = isset($machine->last_activity) && !empty($machine->last_activity) 
                ? date('Y-m-d', strtotime($machine->last_activity . ' -1 day'))
                : date('Y-m-d', strtotime('-7 days'));
            
            // Prepare response
            $response = array(
                'status' => 'initiated',
                'message' => 'Machine sync process started',
                'timestamp' => date('Y-m-d H:i:s'),
                'machine' => array(
                    'terminal_sn' => $terminal_sn,
                    'facility' => $facility,
                    'last_activity' => isset($machine->last_activity) ? $machine->last_activity : NULL
                ),
                'parameters' => array(
                    'start_date' => $start_date,
                    'end_date' => $end_date
                )
            );
            
            $this->log("syncMachine() initiated for terminal $terminal_sn ($facility) from $start_date to $end_date");
            
            // Run sync asynchronously
            if (function_exists('fastcgi_finish_request')) {
                echo json_encode($response, JSON_PRETTY_PRINT);
                fastcgi_finish_request();
            } else {
                echo json_encode($response, JSON_PRETTY_PRINT);
                if (ob_get_level() > 0) {
                    ob_end_flush();
                }
                flush();
            }
            
            // Run sync in background
            $this->run_sync_background($terminal_sn, $start_date, $end_date, $facility, 'attendance', 10);
            
        } catch (Exception $e) {
            $error_response = array(
                'status' => 'error',
                'message' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            );
            echo json_encode($error_response, JSON_PRETTY_PRINT);
            $this->log("syncMachine() exception: " . $e->getMessage());
        } catch (Error $e) {
            $error_response = array(
                'status' => 'error',
                'message' => "Fatal Error: " . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            );
            echo json_encode($error_response, JSON_PRETTY_PRINT);
            $this->log("syncMachine() fatal error: " . $e->getMessage());
        }
    }


    //create multiple new users cronjob
    public function multiple_new_users()
    {
        $howmany = array();
        $query = $this->db->query("SELECT * FROM  ihrisdata WHERE ihrisdata.facility_id IN(SELECT area_code from biotime_devices) AND ihrisdata.card_number NOT IN (SELECT fingerprints_staging.card_number from fingerprints_staging)");
        $newusers = $query->result();
        foreach ($newusers as $newuser):
            $id =$newuser->card_number;

            $message = $this->create_new_biotimeuser($newuser->firstname, $newuser->surname, $id, $newuser->facility_id, $newuser->department_id, $newuser->job_id);


        endforeach;
        $process = 5;
        $method = "bioitimejobs/multiple_new_users";
        if ($method) {
            $status = "successful";
        } else {
            $status = "failed";
        }
        $this->cronjob_register($process, $method, $status);
        $this->log($status);


        return $status;
    }
    public function update_biotimeuser($userdata)
    {


        $barea = $this->getbioloc($userdata->new_facility);
        $bpos = $this->getbiojobs($userdata->job_id);

        $http = new HttpUtils();

        $body = array(
            'area' => [(string) $barea],
            'position' => $bpos
        );

        $endpoint = 'personnel/api/employees/' . $userdata->biotime_emp_id . '/';
        $headr = array();
        $headr[] = 'Content-length:' . strlen(json_encode($body));
        $headr[] = 'Content-type: application/json';
        $headr[] = 'Authorization: JWT ' . $this->get_token();

        $response = $http->curlupdateHttpPost($endpoint, $headr, $body);

        //dd($response);

        if ($response) {
            $this->log($response);
        }

        $process = 6;
        $method = "bioitimejobs/update_biotimeuser";
        if ($response) {
            $status = "successful";
        } else {
            $status = "failed";
        }
        $this->cronjob_register($process, $method, $status);
        return $response;
    }


    //enroll new users (Front End Action that requires login);
    public function get_new_users($facility)
    {
        $query = $this->db->query("SELECT * FROM  ihrisdata WHERE ihrisdata.facility_id='$facility' AND ihrisdata.card_number NOT IN (SELECT fingerprints_staging.card_number from fingerprints_staging)");
        $query->result();
    }


    // create new user

    public function create_new_biotimeuser($firstname, $surname, $emp_code, $area, $department, $position)
    {
        $farea = urldecode($area);
        $fjob = urldecode($position);
        $fdep = urldecode($department);

        $barea = $this->getbioloc($farea);
        if (empty($barea)) {
            $parea = 1;
        } else {
            $parea = $barea;
        }
        $bjob = $this->getbiojobs($fjob);
        if (empty($bjob)) {
            $pjobs = 1;
        } else {
            $pjobs = $bjob;
        }
        $bdep = $this->getbiodeps($fdep);
        if (empty($bdep)) {
            $pdep = 1;
        } else {
            $pdep = $bdep;
        }

        $http = new HttpUtils();

        $body = array(
            'first_name' => $firstname,
            'last_name' => $surname,
            'emp_code' => $emp_code,
            'area' => [(string) $parea],
            'department' => (string) $pdep,
            'position' => (string) $pjobs,
        );

        $endpoint = 'personnel/api/employees/';
        $headr = array();
        $headr[] = 'Content-length:' . strlen(json_encode($body));
        $headr[] = 'Content-type: application/json';
        $headr[] = 'Authorization: JWT ' . $this->get_token();

        $response = $http->curlsendHttpPost($endpoint, $headr, $body);

        if ($response) {
            $this->log($response);
        }

        $process = 6;
        $method = "bioitimejobs/create_new_biotimeuser";
        if ($response) {
            $status = "successful";
        } else {
            $status = "failed";
        }
        $this->cronjob_register($process, $method, $status);
    }
    public function log($message)
    {
        //add double [] at the beggining and at the end of file contents
        return file_put_contents('logs/log.txt', "\n{" . '"REQUEST DETAILS: ' . date('Y-m-d H:i:s') . ' Time": ' . json_encode($message) . '}\n', FILE_APPEND);
    }
    public function logattendance($message)
    {
        //add double [] at the beggining and at the end of file contents
        return file_put_contents('logs/fetchatt_log.txt', "\n{" . '"REQUEST DETAILS: ' . date('Y-m-d H:i:s') . ' Time": ' . json_encode($message) . '},\n', FILE_APPEND);
    }
    public function getbiojobs($job)
    {
        $query = $this->db->query("SELECT id from biotime_jobs where position_code='$job' LIMIT 1");

        return $query->result()[0]->id;
    }
    public function getbiodeps($dep_id)
    {
        $query = $this->db->query("SELECT dept_code from biotime_departments where dept_code='$dep_id' LIMIT 1");
        if ($query->num_rows() > 0) {
            return $query->result()[0]->dept_code;
        }
        return null;
    }
    public function getbioloc($facility)
    {
        $query = $this->db->query("SELECT id from biotime_facilities where area_code='$facility' LIMIT 1");
        return $query->result()[0]->id;
    }
    //not working
    public function biotimeFacilities()
    {

        $http = new HttpUtils();
        $headr = array();
        $headr[] = 'Content-length: 0';
        $headr[] = 'Content-type: application/json';
        $headr[] = 'Authorization: JWT ' . $this->get_token();



        $query = array(
            'page_size' => 50000
        );

        $params = '?' . http_build_query($query);
        $endpoint = 'personnel/api/areas/' . $params;

        //leave options and undefined. guzzle will use the http:query;

        $response = $http->curlgetHttp($endpoint, $headr, []);
        //return $response;
        //return $response;
        $j = array();
        foreach ($response->data as $facs) {
            $data = array(
                'id' => $facs->id,
                'area_code' => $facs->area_code,
                'area_name' => $facs->area_name
            );
            array_push($j, $data);
        }

        $message = $this->biotimejobs_mdl->save_facilities($j);
        //  print_r($response->data[0]->id);
        $process = 7;
        $method = "bioitimejobs/biotimeFacilities";
        if ($response) {
            $status = "successful";
        } else {
            $status = "failed";
        }
        $this->cronjob_register($process, $method, $status);
        return $this->log($message);
    }

    public function biotime_jobs()
    {

        $http = new HttpUtils();
        $headr = array();
        $headr[] = 'Content-length: 0';
        $headr[] = 'Content-type: application/json';
        $headr[] = 'Authorization: JWT ' . $this->get_token();



        $query = array(
            'page_size' => 50000
        );

        $params = '?' . http_build_query($query);
        $endpoint = 'personnel/api/position/' . $params;

        //leave options and undefined. guzzle will use the http:query;

        $response = $http->curlgetHttp($endpoint, $headr, []);
        //return $response;
        $j = array();
        foreach ($response->data as $jobs) {
            $data = array(
                'id' => $jobs->id,
                'position_code' => $jobs->position_code,
                'position_name' => $jobs->position_name
            );

            array_push($j, $data);

        }
        // dd($j);

        $message = $this->biotimejobs_mdl->save_jobs($j);
        $process = 8;
        $method = "bioitimejobs/biotime_jobs";
        if ($response) {
            $status = "successful";
        } else {
            $status = "failed";
        }
        $this->cronjob_register($process, $method, $status);
        return $this->log($message);
    }
    public function biotimedepartments()
    {

        $http = new HttpUtils();
        $headr = array();
        $headr[] = 'Content-length: 0';
        $headr[] = 'Content-type: application/json';
        $headr[] = 'Authorization: JWT ' . $this->get_token();



        $query = array(
            'page_size' => 5000000
        );

        $params = '?' . http_build_query($query);
        $endpoint = 'personnel/api/department/' . $params;

        //leave options and undefined. guzzle will use the http:query;

        $response = $http->curlgetHttp($endpoint, $headr, []);
        //return $response;
        $j = array();
        foreach ($response->data as $deps) {
            $data = array(
                'dept_code' => $deps->dept_code,
                'dept_name' => $deps->dept_name
            );
            // Note: id column is auto-increment, so we don't include it in the insert
            array_push($j, $data);
        }

        $message = $this->biotimejobs_mdl->save_department($j);
        $process = 9;
        $method = "bioitimejobs/biotimedepartments";
        if ($response) {
            $status = "successful";
        } else {
            $status = "failed";
        }
        $this->cronjob_register($process, $method, $status);

        return $this->log($message);
    }
    //clean
    public function create_jobs()
    {
    }
    public function facilities()
    {
        //get biotime_facilities
        //get ihris_facilities
        //if not exits in biotime_facilities create
        //method
        //personnel/api/areas/{area_code	area_name}

    }
    public function facility_departments()
    {
    }
    public function deleteEnrolled()
    {
    }
    // get all biotime deployements
    //get cron jobs from the server
    public function fetch_biotime_employees($page)
    {
        date_default_timezone_set('Africa/Kampala');
        $http = new HttpUtils();
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => "JWT " . $this->get_token(),
        ];


        $sdate = date("Y-m-d H:i:s", strtotime("-12 hours"));
        $query = array(
            'page' => $page
        );

        $params = '?' . http_build_query($query);
        $endpoint = 'personnel/api/employees/' . $params;

        //dd($endpoint);

        //leave options and undefined. guzzle will use the http:query;

        $response = $http->getempData($endpoint, "GET", $headers);
        //return $response;
        //dd($response->data);

        return $response;
    }
    public function biotime_employees()
    {

        ignore_user_abort(true);
        ini_set('max_execution_time', 0);
        $resp = $this->fetch_biotime_employees($page = 1);
        $count = $resp->count;
        //dd($count);
        $pages = (int) ceil($count / 10);
        $rows = array();
        if ($count > 1) {
            $this->db->truncate('biotime_enrollment');
        }
        //dd($resp);

        for ($currentPage = 1; $currentPage <= $pages; $currentPage++) {
            $response = $this->fetch_biotime_employees($currentPage);
            foreach ($response->data as $mydata) {

                $data = array(

                    "emp_code" => $mydata->emp_code,
                    "biotime_emp_id" => $mydata->id,
                    "biotime_facility_id" => $mydata->area[0]->id,
                    "biotime_fac_id" => $mydata->area[0]->area_code
                );
                $message = $this->db->replace('biotime_enrollment', $data);
                // array_push($rows, $data);
            }
        }
        // dd($data);

        $process = 7;
        $method = "bioitimejobs/biotime_employees";
        if ($response) {
            $status = "successful";
        } else {
            $status = "failed";
        }
        $this->cronjob_register($process, $method, $status);
        return $this->log($message);
    }

    //create multiple new users cronjob
    public function transfer_employees()
    {
        //effective transfers
        $howmany = array();
        $query = $this->db->query("SELECT * FROM  biotime_transfers");
        $trasnfers = $query->result();
        foreach ($trasnfers as $newuser):

            $message = $this->update_biotimeuser($newuser);


        endforeach;
        $process = 5;
        $method = "bioitimejobs/tranfer_employees";
        if (@$message) {
            $status = "successful";
        } else {
            $status = "failed";
        }
        $this->cronjob_register($process, $method, $status);
        $this->log($status);


        echo $status;
    }


    public function biotimeClockin()
    {
        ignore_user_abort(true);
        ini_set('max_execution_time', 0);
        $this->db->query("CALL `clockin_users`();");

        $message = " Checkin " . $this->db->affected_rows();

        $this->biotimeClockout();
        $this->biotimeClockoutnight();
        $this->markAttendance();

        $this->db->query("CALL `biotime_cache`();");

        $this->db->query("TRUNCATE TABLE biotime_data");


        $this->log($message);
    }
    //rethink the clockin, clockin people as the data is fetched.
    public function biotimeClockout()
    {
        ignore_user_abort(true);
        ini_set('max_execution_time', 0);
        //$query = $this->db->query("SELECT concat(DATE(biotime_data.punch_time),ihrisdata.ihris_pid) as `entry_id`, punch_time from biotime_data,ihrisdata where (biotime_data.emp_code=ihrisdata.card_number or biotime_data.ihris_pid=ihrisdata.ihris_pid) AND (punch_state='1' OR punch_state='Check Out' OR punch_state='0') AND concat(DATE(biotime_data.punch_time),ihrisdata.ihris_pid) in (SELECT `entry_id` from clk_log) ");

        $query = $this->db->query("SELECT concat(DATE(biotime_data.punch_time),ihrisdata.ihris_pid) as `entry_id`, punch_time from biotime_data,ihrisdata where (biotime_data.emp_code=ihrisdata.card_number or biotime_data.emp_code=ihrisdata.ipps)  AND concat(DATE(biotime_data.punch_time),ihrisdata.ihris_pid) in (SELECT `entry_id` from clk_log) ");
        $entry_id = $query->result();

        foreach ($entry_id as $entry) {
            $final_time = strtotime($entry->punch_time) / 3600;
            if ($final_time > 0):
                $this->db->set('time_out', "$entry->punch_time");
                $this->db->where("time_in <", "$entry->punch_time");
                $this->db->where('entry_id', "$entry->entry_id");
                $query = $this->db->update('clk_log');
            endif;
        }
        //night shift

        echo $message = $this->db->affected_rows() . " Clocked Out";
        $this->log($message);
        
    }
    //clockout night people
    public function biotimeClockoutnight($dates=FALSE)
    {
        ignore_user_abort(true);
        ini_set('max_execution_time', 0);

        //get night shift people.
        if(!empty($today)){
        $today  = $dates;
       }
    
       else{
        $today = date('Y-m-d');

       }
        $yesterday = date($today, strtotime("-1 day"));

        $nights = $this->db->query("SELECT duty_date,duty_rosta.ihris_pid as person_id,entry_id,card_number from duty_rosta,ihrisdata where schedule_id='16' and ihrisdata.ihris_pid=duty_rosta.ihris_pid  and concat(duty_date,duty_rosta.ihris_pid) in (SELECT entry_id from clk_log WHERE date='$yesterday'
         )")->result();
        foreach ($nights as $night):
            //yesterdays entry_id 
            $nights = $yesterday . $night->person_id;

            $querys = $this->db->query("SELECT punch_time,punch_state from biotime_data,ihrisdata where (biotime_data.emp_code='$night->card_number') AND DATE(biotime_data.punch_time)='$today' ");
            $entry = $querys->row();
            //get time in for the log
            $timein = $this->db->query("select time_in from clk_log WHERE entry_id='$nights'")->row()->time_in;


            $initial_time = strtotime($timein) / 3600;
            $final_time = strtotime($entry->punch_time) / 3600;
            $hours_worked = round(($final_time - $initial_time), 1);
            //echo $final_time;
            if (($final_time > 0) && ($hours_worked <= 15)):
                $this->db->set('time_out', "$entry->punch_time");
                //  $this->db->where("time_in <","$entry->punch_time");
                //todays entry
                $this->db->where('entry_id', "$nights");
                $query = $this->db->update('clk_log');
                // print_r($entry);
                // echo "<br>";
            endif;



        endforeach;
        //night shift

        echo $message = $this->db->affected_rows() . " Clocked Out";

        $this->biotimeClockoutnight_ipps();
        $this->log($message);
     
    }

    //clockout night people ipps
    public function biotimeClockoutnight_ipps($dates = FALSE)
    {
        ignore_user_abort(true);
        ini_set('max_execution_time', 0);

        //get night shift people.
        if (!empty($today)) {
            $today = $dates;
        } else {
            $today = date('Y-m-d');

        }
        $yesterday = date($today, strtotime("-1 day"));

        $nights = $this->db->query("SELECT duty_date,duty_rosta.ihris_pid as person_id,entry_id,ipps as card_number from duty_rosta,ihrisdata where schedule_id='16' and ihrisdata.ihris_pid=duty_rosta.ihris_pid  and concat(duty_date,duty_rosta.ihris_pid) in (SELECT entry_id from clk_log WHERE date='$yesterday'
         )")->result();
        foreach ($nights as $night):
            //yesterdays entry_id 
            $nights = $yesterday . $night->person_id;

            $querys = $this->db->query("SELECT punch_time,punch_state from biotime_data,ihrisdata where (biotime_data.emp_code='$night->card_number') AND DATE(biotime_data.punch_time)='$today' ");
            $entry = $querys->row();
            //get time in for the log
            $timein = $this->db->query("select time_in from clk_log WHERE entry_id='$nights'")->row()->time_in;


            $initial_time = strtotime($timein) / 3600;
            $final_time = strtotime($entry->punch_time) / 3600;
            $hours_worked = round(($final_time - $initial_time), 1);
            //echo $final_time;
            if (($final_time > 0) && ($hours_worked <= 15)):
                $this->db->set('time_out', "$entry->punch_time");
                //  $this->db->where("time_in <","$entry->punch_time");
                //todays entry
                $this->db->where('entry_id', "$nights");
                $query = $this->db->update('clk_log');
                // print_r($entry);
                // echo "<br>";
            endif;



        endforeach;
        //night shift

        echo $message = $this->db->affected_rows() . " Clocked Out";
        $this->log($message);

    }
    public function markAttendance()
    {
        ini_set('max_execution_time', 0);
        //poplulate actuals
        $query = $this->db->query("CALL insert_actuals()");

        $rowsnow = $this->db->affected_rows();
        if ($query) {
          echo "\e[32m$rowsnow Attendance Records Marked\e[0m";
        } else {

           echo  "\e[31mFailed to Mark\e[0m";
        }
       
    }

    //every 30th day monthly
    public function rostatoAttend()
    {
        ignore_user_abort(true);
        ini_set('max_execution_time', 0);
        //To set custom month uncomment below and set  ymonth of choice
        //$ymonth="2019-08"."-";   
        $ymonth = date('Y-m');

        //poplulate actuals
        $query = $this->db->query("REPLACE
  INTO actuals(
      entry_id,
      facility_id,
      department_id,
      ihris_pid,
      schedule_id,
      color,
      actuals.date,
      actuals.end
  )
  SELECT
      entry_id,
      facility_id,
      department_id,
      ihris_pid,
      schedule_id,
      color,
      duty_rosta.duty_date,
      duty_rosta.end
  FROM
      duty_rosta
  WHERE
      (duty_rosta.schedule_id IN(17, 18, 19, 20, 21) AND (
          DATE_FORMAT(duty_rosta.duty_date, '%Y-%m') <= '$ymonth') AND duty_rosta.entry_id NOT IN(
      SELECT
          entry_id
      FROM
          actuals
      ))");
        $rowsnow = $this->db->affected_rows();
        if ($query) {
            echo $msg = $rowsnow . "  Attendance Records Marked";
        } else {

            echo $msg = "Failed to Mark";
        }
        $this->log($msg);


        $query = $this->db->query("Update actuals set schedule_id='25', color='#29910d' WHERE schedule_id IN(18,19,20,21)");

        $rowsnow = $this->db->affected_rows();
        if ($query) {
            echo $msg = "" . $rowsnow . "  Leave records recognised by attendance";
        } else {

            echo $msg = "No leave records found";
        }

        $query = $this->db->query("Update actuals set schedule_id='24', color='#d1a110' WHERE schedule_id='17'");

        $rowsnow = $this->db->affected_rows();
        if ($query) {
            echo $msg = "" . $rowsnow . "  Offduty records recognised by attendance";
        } else {

            echo $msg = "No Off duty records found";
        }
        $this->log($msg);
    }

    public function cronjob_register($process, $method, $status)
    {
        $data = array('process_id' => $process, 'process' => $method, 'status' => $status);
        $this->db->replace("cronjob_register", $data);
    }
    /**
     * Fetch time history for a date range, processing day by day
     * Uses PostgreSQL database for faster data retrieval
     * 
     * @param string $start_date Start date in Y-m-d format
     * @param string $end_date End date in Y-m-d format
     * @param string|bool $terminal_sn Terminal serial number (default: FALSE = all terminals)
     * @param string|bool $facility Facility name (for logging, default: FALSE)
     * @param string|bool $empcode Employee code filter (default: FALSE = all employees)
     * @param callable|null $progress_callback Optional callback function for progress updates
     * @param bool $output_console Whether to output console messages (default: true)
     * @return array Result array with status, message, and statistics
     */
    public function fetch_time_history($start_date, $end_date, $terminal_sn = FALSE, $facility = FALSE, $empcode = FALSE, $progress_callback = NULL, $output_console = TRUE)
    {
        ignore_user_abort(true);
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');
        
        $result = array(
            'status' => 'error',
            'message' => '',
            'dates_processed' => 0,
            'total_records' => 0,
            'errors' => array(),
            'daily_stats' => array()
        );
        
        // Console output helper
        $console = function($message, $type = 'info') use ($output_console) {
            if ($output_console) {
                $timestamp = date('Y-m-d H:i:s');
                $prefix = '';
                switch($type) {
                    case 'success':
                        $prefix = '✓';
                        break;
                    case 'error':
                        $prefix = '✗';
                        break;
                    case 'warning':
                        $prefix = '⚠';
                        break;
                    case 'info':
                    default:
                        $prefix = '→';
                        break;
                }
                echo "[$timestamp] $prefix $message\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            }
        };
        
        try {
            // Validate dates
            $currentDate = strtotime($start_date);
            $endDate = strtotime($end_date);
            
            if ($currentDate === FALSE || $endDate === FALSE) {
                throw new Exception("Invalid date format. Expected Y-m-d format.");
            }
            
            if ($currentDate > $endDate) {
                throw new Exception("Start date must be before or equal to end date.");
            }
            
            // Calculate total days
            $total_days = (int) ceil(($endDate - $currentDate) / 86400) + 1;
            $days_processed = 0;
            
            $console("=== Starting Time History Sync ===", 'info');
            $console("Date Range: $start_date to $end_date ($total_days days)", 'info');
            if ($terminal_sn) {
                $console("Terminal: $terminal_sn", 'info');
            }
            if ($facility) {
                $console("Facility: $facility", 'info');
            }
            $console("", 'info');
            
            // Loop through each date
            while ($currentDate <= $endDate) {
                $dates = date('Y-m-d', $currentDate);
                $day_start = $dates . ' 00:00:00';
                $day_end = $dates . ' 23:59:59';
                $day_number = $days_processed + 1;
                
                $console("[$day_number/$total_days] Processing date: $dates", 'info');
                
                // Progress callback
                if (is_callable($progress_callback)) {
                    call_user_func($progress_callback, array(
                        'date' => $dates,
                        'day' => $day_number,
                        'total_days' => $total_days,
                        'terminal_sn' => $terminal_sn,
                        'facility' => $facility
                    ));
                }
                
                // Fetch data for this date via database
                $fetch_result = $this->biotimejobs_mdl->fetch_time_history($day_start, $day_end, $terminal_sn, $empcode);
                
                $daily_stat = array(
                    'date' => $dates,
                    'status' => $fetch_result['status'],
                    'records_fetched' => $fetch_result['records_fetched'],
                    'records_saved' => $fetch_result['records_saved'],
                    'message' => $fetch_result['message']
                );
                
                if ($fetch_result['status'] === 'success') {
                    $result['total_records'] += $fetch_result['records_saved'];
                    $days_processed++;
                    
                    $console("  ✓ Fetched: {$fetch_result['records_fetched']} | Saved: {$fetch_result['records_saved']}", 'success');
                    
                    // Process clock-out data for this date
                    $this->biotimeClockoutnight($dates);
                    
                    $this->log("fetch_time_history() processed date $dates: " . $fetch_result['records_saved'] . " records saved");
                } else {
                    $error_msg = "Failed to fetch data for date $dates: " . $fetch_result['message'];
                    $result['errors'][] = $error_msg;
                    $daily_stat['errors'] = $fetch_result['errors'];
                    $console("  ✗ Error: " . $fetch_result['message'], 'error');
                    $this->log("fetch_time_history() error: $error_msg");
                }
                
                $result['daily_stats'][] = $daily_stat;
                
                // Increment current date by 1 day
                $currentDate = strtotime('+1 day', $currentDate);
            }
            
            $result['dates_processed'] = $days_processed;
            $result['status'] = 'success';
            $result['message'] = "Successfully processed $days_processed of $total_days days. Total records: " . $result['total_records'];
            
            $console("", 'info');
            $console("=== Sync Summary ===", 'info');
            $console("Days Processed: $days_processed / $total_days", 'info');
            $console("Total Records: " . $result['total_records'], 'info');
            if (!empty($result['errors'])) {
                $console("Errors: " . count($result['errors']), 'warning');
            }
            $console("=== Sync Completed ===", 'success');
            
            $this->log("fetch_time_history() completed: " . $result['message']);
            
        } catch (Exception $e) {
            $result['status'] = 'error';
            $result['message'] = "Error: " . $e->getMessage();
            $result['errors'][] = $e->getMessage();
            $console("✗ Fatal Error: " . $e->getMessage(), 'error');
            $this->log("fetch_time_history() exception: " . $e->getMessage());
        } catch (Error $e) {
            $result['status'] = 'error';
            $result['message'] = "Fatal Error: " . $e->getMessage();
            $result['errors'][] = $e->getMessage();
            $console("✗ Fatal Error: " . $e->getMessage(), 'error');
            $this->log("fetch_time_history() fatal error: " . $e->getMessage());
        }
        
        return $result;
    }

    /**
     * Fetch daily attendance for all machines
     * Processes each machine individually with proper error handling and console output
     * 
     * @param string|bool $end_date End date in Y-m-d format (default: FALSE = current date)
     * @param int $max_days Maximum number of days to sync per machine (default: 60)
     * @param string|bool $specific_device Specific device SN to sync (default: FALSE = all devices)
     * @param bool $output_console Whether to output console messages (default: true)
     * @return array Result array with status, message, and statistics per machine
     */
    public function fetch_daily_attendance($end_date = FALSE, $max_days = 60, $specific_device = FALSE, $output_console = TRUE)
    {
        ignore_user_abort(true);
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');
        
        $result = array(
            'status' => 'error',
            'message' => '',
            'machines_processed' => 0,
            'machines_total' => 0,
            'total_records' => 0,
            'machine_results' => array(),
            'errors' => array()
        );
        
        // Console output helper
        $console = function($message, $type = 'info') use ($output_console) {
            if ($output_console) {
                $timestamp = date('Y-m-d H:i:s');
                $prefix = '';
                switch($type) {
                    case 'success':
                        $prefix = '✓';
                        break;
                    case 'error':
                        $prefix = '✗';
                        break;
                    case 'warning':
                        $prefix = '⚠';
                        break;
                    case 'info':
                    default:
                        $prefix = '→';
                        break;
                }
                echo "[$timestamp] $prefix $message\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            }
        };
        
        try {
            // Set end date
            if (empty($end_date)) {
                $end_date = date('Y-m-d');
            }
            
            $console("═══════════════════════════════════════════════════════", 'info');
            $console("  DAILY ATTENDANCE SYNC - MACHINES", 'info');
            $console("═══════════════════════════════════════════════════════", 'info');
            $console("End Date: $end_date", 'info');
            $console("Max Days Per Machine: $max_days", 'info');
            if ($specific_device) {
                $console("Specific Device: $specific_device", 'info');
            }
            $console("", 'info');
            
            // Build query for machines
            $query = "SELECT * FROM biotime_devices";
            if (!empty($specific_device)) {
                $query .= " WHERE sn = " . $this->db->escape($specific_device);
            }
            $query .= " ORDER BY sn ASC";
            
            $machines = $this->db->query($query)->result();
            
            if (empty($machines)) {
                throw new Exception("No machines found in database");
            }
            
            $result['machines_total'] = count($machines);
            $machines_processed = 0;
            
            $console("Found " . $result['machines_total'] . " machine(s) to sync", 'info');
            $console("", 'info');
            
            foreach ($machines as $machine_index => $machine) {
                $device = $machine->sn;
                $facility = isset($machine->area_name) ? $machine->area_name : 'Unknown';
                $machine_num = $machine_index + 1;
                
                $console("─────────────────────────────────────────────────────", 'info');
                $console("MACHINE [$machine_num/{$result['machines_total']}]: $device", 'info');
                $console("Facility: $facility", 'info');
                
                $machine_result = array(
                    'device' => $device,
                    'facility' => $facility,
                    'status' => 'error',
                    'records' => 0,
                    'message' => ''
                );
                
                try {
                    // Get start date from last_activity or use default
                    $startdate = isset($machine->last_activity) && !empty($machine->last_activity) 
                        ? $machine->last_activity 
                        : date('Y-m-d', strtotime('-7 days'));
                    
                    // Ensure startdate is a valid date
                    $start_timestamp = strtotime($startdate);
                    if ($start_timestamp === FALSE) {
                        $startdate = date('Y-m-d', strtotime('-7 days'));
                        $start_timestamp = strtotime($startdate);
                    }
                    
                    // Subtract one day to ensure we don't miss data
                    $new_timestamp = $start_timestamp - 86400;
                    $start = date('Y-m-d', $new_timestamp);
                    
                    // Convert end date to timestamp
                    $end_timestamp = strtotime($end_date);
                    
                    // Calculate the difference in days
                    $difference_seconds = $end_timestamp - $start_timestamp;
                    $difference_days = $difference_seconds / (60 * 60 * 24);
                    
                    $console("Date Range: $start to $end_date ($difference_days days)", 'info');
                    $console("Last Activity: " . ($machine->last_activity ?: 'Never'), 'info');
                    
                    // Only sync if within max_days limit
                    if ($difference_days <= $max_days && $difference_days >= 0) {
                        $console("Starting sync...", 'info');
                        $this->log("fetch_daily_attendance() starting sync for device $device ($facility) from $start to $end_date");
                        
                        // Fetch time history for this machine (with console output)
                        $fetch_result = $this->fetch_time_history($start, $end_date, $device, $facility, FALSE, NULL, $output_console);
                        
                        if ($fetch_result['status'] === 'success') {
                            $machine_result['status'] = 'success';
                            $machine_result['records'] = $fetch_result['total_records'];
                            $machine_result['message'] = $fetch_result['message'];
                            $result['total_records'] += $fetch_result['total_records'];
                            $machines_processed++;
                            
                            // Update last_activity for this machine
                            $this->db->where('sn', $device);
                            $this->db->update('biotime_devices', array('last_activity' => $end_date));
                            
                            $console("✓ Sync completed: {$fetch_result['total_records']} records", 'success');
                            $this->log("fetch_daily_attendance() completed for device $device: " . $fetch_result['total_records'] . " records");
                        } else {
                            $machine_result['message'] = $fetch_result['message'];
                            $result['errors'][] = "Device $device: " . $fetch_result['message'];
                            $console("✗ Sync failed: " . $fetch_result['message'], 'error');
                            $this->log("fetch_daily_attendance() failed for device $device: " . $fetch_result['message']);
                        }
                    } else {
                        $machine_result['status'] = 'skipped';
                        $machine_result['message'] = "Date range too large ($difference_days days, max: $max_days)";
                        $console("⚠ Skipped: Date range too large ($difference_days days)", 'warning');
                        $this->log("fetch_daily_attendance() skipped device $device: date range too large");
                    }
                    
                } catch (Exception $e) {
                    $machine_result['message'] = "Error: " . $e->getMessage();
                    $result['errors'][] = "Device $device: " . $e->getMessage();
                    $console("✗ Exception: " . $e->getMessage(), 'error');
                    $this->log("fetch_daily_attendance() exception for device $device: " . $e->getMessage());
                } catch (Error $e) {
                    $machine_result['message'] = "Fatal Error: " . $e->getMessage();
                    $result['errors'][] = "Device $device: " . $e->getMessage();
                    $console("✗ Fatal Error: " . $e->getMessage(), 'error');
                    $this->log("fetch_daily_attendance() fatal error for device $device: " . $e->getMessage());
                }
                
                $result['machine_results'][] = $machine_result;
                $console("", 'info');
            }
            
            // Sync terminals after processing all machines
            $console("Syncing terminal information...", 'info');
            $this->terminals();
            $console("✓ Terminal sync completed", 'success');
            $console("", 'info');
            
            $result['machines_processed'] = $machines_processed;
            $result['status'] = ($machines_processed > 0) ? 'success' : 'error';
            $result['message'] = "Processed $machines_processed of " . $result['machines_total'] . " machines. Total records: " . $result['total_records'];
            
            $console("═══════════════════════════════════════════════════════", 'info');
            $console("  SYNC SUMMARY", 'info');
            $console("═══════════════════════════════════════════════════════", 'info');
            $console("Machines Processed: $machines_processed / {$result['machines_total']}", 'info');
            $console("Total Records: " . $result['total_records'], 'info');
            if (!empty($result['errors'])) {
                $console("Errors: " . count($result['errors']), 'warning');
            }
            $console("═══════════════════════════════════════════════════════", 'info');
            
            $this->log("fetch_daily_attendance() completed: " . $result['message']);
            
        } catch (Exception $e) {
            $result['status'] = 'error';
            $result['message'] = "Error: " . $e->getMessage();
            $result['errors'][] = $e->getMessage();
            $console("✗ Fatal Error: " . $e->getMessage(), 'error');
            $this->log("fetch_daily_attendance() exception: " . $e->getMessage());
        } catch (Error $e) {
            $result['status'] = 'error';
            $result['message'] = "Fatal Error: " . $e->getMessage();
            $result['errors'][] = $e->getMessage();
            $console("✗ Fatal Error: " . $e->getMessage(), 'error');
            $this->log("fetch_daily_attendance() fatal error: " . $e->getMessage());
        }
        
        return $result;
    }

    public function daily_logs($ihris_id,$date){
        $ihris_pid = urldecode($ihris_id);
        $this->db->where("date", "$date");
        $this->db->where("ihris_pid","$ihris_pid");
       $data =  $this->db->get('clk_log')->row();
    echo json_encode($data);
    }

    // public function attendance_data($valid_range, $district = FALSE, $facility_id = FALSE)
    // {
    //     // Set the default date range if not provided
    //     if (empty($valid_range)) {
    //         $valid_range = date('Y-m');
    //     }

    //     // Decode URL parameters
    //     $facility = urldecode($facility_id);
    //     $district = ucwords(urldecode($district));

    //     // Initialize necessary variables
    //     $empid = "";
    //     $dep = "";

    //     // Fetch attendance summary data
    //     $datas = $this->attendance_model->attendance_summary($valid_range, $this->filters, $config['per_page'] = NULL, $page = NULL, $district, $facility, $empid, $dep, 'api');

    //     // Pre-fetch fields to reduce redundant database queries
    //     $ihris_pids = array_column($datas, 'ihris_pid');
    //     $fields = $this->get_fields_for_ihris_pids($ihris_pids, ['card_number', 'nin', 'ipps']);

    //     $attendanceData = [];

    //     foreach ($datas as $data) {
    //         $ihris_pid = $data['ihris_pid'];

    //         // Fetch roster data
    //         $roster = Modules::run('attendance/attrosta', $valid_range, urlencode($ihris_pid));

    //         // Use pre-fetched fields
    //         $cardnumber = $fields[$ihris_pid]['card_number'];
    //         $nin = $fields[$ihris_pid]['nin'];
    //         $ipps = $fields[$ihris_pid]['ipps'];

    //         $present = !empty($data['P']) ? $data['P'] : 0;
    //         $off = !empty($data['O']) ? $data['O'] : 0;
    //         $leave = !empty($data['L']) ? $data['L'] : 0;
    //         $request = !empty($data['R']) ? $data['R'] : 0;
    //         $holiday = !empty($data['H']) ? $data['H'] : 0;
    //         $duty_date = $data['duty_date'];

    //         $eve = isset($roster['Evening'][0]) ? $roster['Evening'][0]->days : 0;
    //         $day = isset($roster['Day'][0]) ? $roster['Day'][0]->days : 0;
    //         $night = isset($roster['Night'][0]) ? $roster['Night'][0]->days : 0;
    //         $r_days = ($eve + $day + $night);
    //         if ($r_days == 0) {
    //             $r_days = 22;
    //         }

    //         $absent = days_absent_helper($present, $r_days);
    //         $per = per_present_helper($present, $r_days);

    //         // Construct the normal JSON data structure
    //         $attendance = [
    //             "ihris_pid" => $ihris_pid,
    //             "ipps" => $ipps,
    //             "nin" => $nin,
    //             "card_number" => $cardnumber,
    //             "facility_id" => $data["facility_id"],
    //             "district" => $data["district"],
    //             "Name" => $data['fullname'],
    //             "Job" => $data['job'],
    //             "Department" => $data['department_id'],
    //             "Duty Date" => $duty_date,
    //             "Off Duty" => $off,
    //             "Official Request" => $request,
    //             "Leave" => $leave,
    //             "Holiday" => $holiday,
    //             "Total Days Expected at Work" => $r_days,
    //             "Total Days Worked" => $present,
    //             "Total Days Absent" => $absent,
    //             "% Present" => $per
    //         ];

    //         $attendanceData[] = $attendance;
    //     }

    //     echo json_encode($attendanceData);
    // }

    private function get_fields_for_ihris_pids($ihris_pids, $fields)
    {
        $this->db->select('ihris_pid, ' . implode(', ', $fields));
        $this->db->from('ihrisdata');
        $this->db->where_in('ihris_pid', $ihris_pids);
        $query = $this->db->get();

        $result = [];
        foreach ($query->result_array() as $row) {
            $result[$row['ihris_pid']] = $row;
        }

        return $result;
    }


    public function attendance_data($fhir,$valid_range, $district = FALSE, $facility_id = FALSE)
    {
        // Set the default date range if not provided
        if (empty($valid_range)) {
            $valid_range = date('Y-m');
        }

        // Decode URL parameters
        $facility = urldecode($facility_id);
        $district = ucwords(urldecode($district));

        // Initialize necessary variables
        $empid = "";
        $dep = "";

        // Fetch attendance summary data
        $datas = $this->attendance_model->attendance5_summary($valid_range, $this->filters, $config['per_page'] = NULL, $page = NULL, $district, $facility, $empid, $dep, 'api');

        // Pre-fetch fields to reduce redundant database queries
        $ihris_pids = array_column($datas, 'ihris_pid');
        $fields = $this->get_fields_for_ihris_pids($ihris_pids, ['card_number', 'nin', 'ipps']);

        $attendanceData = [];

        foreach ($datas as $data) {
           // dd($data);
            $ihris_pid = $data['ihris_pid'];
            $ihris5_pid = $data['ihris5_pid'];

            // Fetch roster data
            $roster = Modules::run('attendance/attrosta', $valid_range, urlencode($ihris_pid));

            // Use pre-fetched fields
            $cardnumber = $fields[$ihris_pid]['card_number'];
            $nin = $fields[$ihris_pid]['nin'];
            $ipps = $fields[$ihris_pid]['ipps'];

            $present = !empty($data['P']) ? $data['P'] : 0;
            $off = !empty($data['O']) ? $data['O'] : 0;
            $leave = !empty($data['L']) ? $data['L'] : 0;
            $request = !empty($data['R']) ? $data['R'] : 0;
            $holiday = !empty($data['H']) ? $data['H'] : 0;
            $duty_date = $data['duty_date'];

            $eve = isset($roster['Evening'][0]) ? $roster['Evening'][0]->days : 0;
            $day = isset($roster['Day'][0]) ? $roster['Day'][0]->days : 0;
            $night = isset($roster['Night'][0]) ? $roster['Night'][0]->days : 0;
            $r_days = ($eve + $day + $night);
            if ($r_days == 0) {
                $r_days = 22;
            }

            $absent = days_absent_helper($present, $r_days);
            $per = per_present_helper($present, $r_days);

            $attendance = [
                "ihris_pid" => $ihris5_pid,
                "ipps" => $ipps,
                "nin" => $nin,
                "card_number" => $cardnumber,
                "facility_id" => $data["facility_id"],
                "district" => $data["district"],
                "Name" => $data['fullname'],
                "Job" => $data['job'],
                "Department" => $data['department_id'],
                "Duty Date" => $duty_date . '-01',
                "Off Duty" => $off,
                "Official Request" => $request,
                "Leave" => $leave,
                "Holiday" => $holiday,
                "Expected" => $r_days,
                "Total Days Worked" => $present,
                "Total Days Absent" => $absent,
                "percent" => intval(round(str_replace(" %","",$per),0))
            ];

            $attendanceData[] = $attendance;
        }

        if ($fhir=='view') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($this->convert_to_fhir($attendanceData));
             
        } else {
           return $this->convert_to_fhir($attendanceData);
           
        }
    }
    private function convert_to_fhir($attendanceData)
    {
        $fhirData = [
            "resourceType" => "Bundle",
            "type" => "transaction",
            "entry" => []
        ];

        foreach ($attendanceData as $data) {
            //dd($data);
            $entry = [
                "resource" => [
                    "resourceType" => "Basic",
                     // Generate a unique ID for each entry
                    "meta" => [
                        "profile" => ["http://ihris.org/fhir/StructureDefinition/ihris-basic-attendance"]
                    ],
                    "extension" => [
                        [
                            "url" => "http://ihris.org/fhir/StructureDefinition/ihris-practitioner-reference",
                            "valueReference" => [
                                "reference" => "Practitioner/" . $data["ihris_pid"]
                            ]
                        ],
                        [
                            "url" => "http://ihris.org/fhir/StructureDefinition/ihris-attendance",
                            "extension" => [
                                ["url" => "period", "valueDate" => $data["Duty Date"]],
                                ["url" => "present", "valueInteger" => $data["Total Days Worked"]],
                                ["url" => "absent", "valueInteger" => $data["Total Days Absent"]],
                                ["url" => "offDuty", "valueInteger" => $data["Off Duty"]],
                                ["url" => "leave", "valueInteger" => $data["Leave"]],
                                ["url" => "request", "valueInteger" => $data["Official Request"]],
                                ["url" => "holidays", "valueInteger" => $data["Holiday"]],
                                ["url" => "expected", "valueInteger" => $data["Expected"]],
                                ["url" => "percentPresent", "valueInteger" => $data["percent"]]
                            ]
                        ]
                    ]
                ],
                "request" => [
                    "method" => "POST",
                    "url" => "Basic" // Generate a unique URL for each entry
                ]
            ];

            $fhirData["entry"][] = $entry;
        }
        //header('Content-Type: application/json; charset=utf-8');

        return $fhirData;
    }

   

    public function get_ihris5data()
    {
        $http = new HttpUtils();
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
      $districts  = $this->db->get('ihris5_districts')->result();
        $this->db->query("TRUNCATE table ihrisdata5");
      foreach($districts as $district){

       //s $dist = str_replace(" District","",$district->name);
        $dist = 'Mbale';
        $response = $http->sendiHRIS5Request('ihrisdata/'.$dist, "GET", $headers, []);

        if ($response) {
            //dd(count($response));
            //$message = $this->biotimejobs_mdl->add_ihrisdata($response);
        
            foreach ($response->entry as $insert) {
                //dd($insert);



                
                         $data = array(
                'ihris_pid' => $insert->ihris_pid,
                'district_id' => $insert->district_id,
                'district' => $insert->district,
                'nin' => isset($insert->nin) ? $insert->nin : null,
                'card_number' => $insert->card_number,
                'ipps' => $insert->ipps,
                'facility_type_id' => $insert->facility_type_id,
                'facility_id' => null, // Assuming facility_id is not present in JSON
                'facility' => $insert->facility,
                'department_id' => null, // Assuming department_id is not present in JSON
                'department' => null, // Assuming department is not present in JSON
                'division' => null, // Assuming division is not present in JSON
                'section' => null, // Assuming section is not present in JSON
                'unit' => '', // Assuming unit is not present in JSON
                'job_id' => $insert->job_id,
                'job' => $insert->job,
                'employment_terms' => $insert->employmentTerms,
                'salary_grade' => isset($insert->salary_grade) ? $insert->salary_grade : null,
                'surname' => $insert->surname,
                'firstname' => $insert->firstname,
                'othername' => $insert->othername,
                'mobile' => isset($insert->mobile) ? $insert->mobile : null,
                'telephone' => isset($insert->telephone) ? $insert->telephone : null,
                'institution_type_id' =>  $insert->facility_type_id,
                'institutiontype_name' =>  $insert->facility_type_id, 
                'gender' => $insert->gender,
                'birth_date' => date('Y-m-d', strtotime($insert->birth_date)),
                'cadre' => isset($insert->cadre) ? $insert->cadre : null,
                'email' => isset($insert->email) ? $insert->email : null,
                'region' => $insert->region
            );
                    


                    //dd($data);


                $message = $this->db->replace('ihrisdata5', $data);
                ///dd($this->last->query);
            }
            $this->remap_data();

            $this->log($message);
        }
        $process = 2;
        $method = "bioitimejobs/get_ihris5data";
        if (count($response) > 0) {
            $status = "successful";
        } else {
            $status = "failed";
        }
    }
        
    }

    public function get_districts()
    {
        $http = new HttpUtils();
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
     
        $response = $http->sendiHRIS5Request('ihrisdata/districts', "GET", $headers, []);

        if ($response) {
            //dd(count($response));
            //$message = $this->biotimejobs_mdl->add_ihrisdata($response);
            $this->db->query("TRUNCATE table ihris5_districts");
            foreach ($response as $insert) {
                    
              //  dd($insert);

                $message = $this->db->insert('ihris5_districts', $insert);
                ///dd($this->last->query);
            }

            $this->log($message);
        }
        $process = 2;
        $method = "bioitimejobs/ihris5_districts";
        if (count($response) > 0) {
            $status = "successful";
        } else {
            $status = "failed";
        }
    }
    public function remap_data(){

        // Optimized and fixed query to get matching values
        $this->db->select('ihrisdata.ihris_pid as ihris4_pid, ihrisdata5.ihris_pid as ihris5_pid');
        $this->db->from('ihrisdata');
        $this->db->join(
            'ihrisdata5',
            'ihrisdata.card_number = ihrisdata5.card_number OR 
     ihrisdata.ipps = ihrisdata5.ipps OR 
     ihrisdata.nin = ihrisdata5.nin'
        );
        $this->db->where('ihrisdata.nin IS NOT NULL');
        $this->db->where('ihrisdata.ipps IS NOT NULL');
        $this->db->where('ihrisdata.card_number IS NOT NULL');

        $query = $this->db->get();
        $map_values = $query->result();

        // Check if there are values to insert
        if (!empty($map_values)) {
            foreach ($map_values as $insert) {
                $data = array(
                    'ihris4_pid' => $insert->ihris4_pid,
                    'ihris5_pid' => $insert->ihris5_pid
                );

                // Using REPLACE to avoid duplicates
                $this->db->replace('data_mapper', $data);
            }
        }

    }
    public function fhir_Server_post()
    {
        $valid_range = '2024-07';
        $district = 'MBALE';
        $body = $this->attendance_data('false', $valid_range, $district);
        // dd($body);
        $http = new HttpUtils();

        $endpoint = 'hapi/fhir';
        $headers = array(
            'Content-Type: application/fhir+json',
            'Content-Length: ' . strlen(json_encode($body)),
            //'Authorization: JWT ' . $this->get_token()
        );

        $response = $http->curlsendiHRIS5HttpPost($endpoint, $headers, $body);

        if ($response) {
            dd($response);
        }
    }
public function ihris5jobs(){
// Sample FHIR resource data (JSON)
        $http = new HttpUtils();
        $headers = [
            'Content-Type: application/fhir+json',
            'Accept' => '*',
        ];

        $response = $http->curlgetihris5Http('hapi/fhir/Basic?_profile=http://ihris.org/fhir/StructureDefinition/ihris-manage-job', "GET", $headers);
//var_dump($response);
// Decode the JSON string into an associative array
$fhirData = json_decode($response, true);

// Initialize an array to hold the formatted data
$formattedData = [];

if (isset($fhirData['entry'])) {
    foreach ($fhirData['entry'] as $entry) {
        $jobName = '';
        $dhis2Uuid = '';

        // Iterate through the extensions to find the job name and dhis2_uuid
        foreach ($entry['resource']['extension'] as $extension) {
            if ($extension['url'] === 'http://ihris.org/fhir/StructureDefinition/ihris-basic-name') {
                $jobName = $extension['valueString'];
            }
            if ($extension['url'] === 'http://ihris.org/fhir/StructureDefinition/ihris-dhis2-id') {
                $dhis2Uuid = $extension['valueString'];
            }
        }

        // Add the job name and empty dhis2_uuid to the formatted data
        $formattedData[] = [
            'dhis2_uuid' => $dhis2Uuid,
            'job_name' => $jobName
        ];
    }
}

// Encode the formatted data into JSON
$jsonOutput = json_encode($formattedData, JSON_PRETTY_PRINT);

// Output the JSON
dd($jsonOutput);

}







}
