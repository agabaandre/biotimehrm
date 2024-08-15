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
    public function get_ihrisdata()
    {
        $http = new HttpUtils();
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        $response = $http->sendiHRISRequest('apiv1/index.php/api/ihrisdata', "GET", $headers, []);

        if ($response) {
            //dd(count($response));
            //$message = $this->biotimejobs_mdl->add_ihrisdata($response);
            $this->db->query("TRUNCATE table ihrisdata");
            foreach($response as $data){

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
        $this->get_ucmbdata();
        $this->update_ipps();
    }
    //employees all enrolled users before creating new ones.

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
        $resp = $this->get_Enrolled();
        $count = $resp->count;
        $pages = (int) ceil($count / 10);
        $rows = array();

        for ($currentPage = 1; $currentPage <= $pages; $currentPage++) {
            $response = $this->get_Enrolled($currentPage);
            foreach ($response->data as $mydata) {

                $data = array(
                    'entry_id' => $mydata->area[0]->area_code . '-' . $mydata->emp_code,
                    "card_number" => $mydata->emp_code,
                    'facilityId' => $mydata->area[0]->area_code,
                    'source' => 'Biotime',
                    'device' => $mydata->enroll_sn,
                    'att_status' => $mydata->enable_att
                );

                array_push($rows, $data);
            }
        }

        $message = $this->biotimejobs_mdl->add_enrolled($rows);
        $this->log($message);
        $process = 3;
        $method = "bioitimejobs/save_Enrolled";
        if (count($response) > 0) {
            $status = "successful";
        } else {
            $status = "failed";
        }
        $this->cronjob_register($process, $method, $status);
    }


    //get cron jobs from the server
    

    public function getTime($page, $end_date = FALSE, $terminal = FALSE)
    {
        date_default_timezone_set('Africa/Kampala');
        $http = new HttpUtils();
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => "JWT " . $this->get_token(),
        ];
        if (empty($end_date)) {
            $edate = date('Y-m-d H:i:s');
            $sdate = date("Y-m-d H:i:s", strtotime("-24 hours", strtotime($edate)));
        } else {
            $edate = $end_date;
            // Use the $edate variable to calculate the start date, which is 24 hours before the end date
            $sdate = date("Y-m-d H:i:s", strtotime("-24 hours", strtotime($edate)));
        }

        if (!empty($terminal)) {
            $query = array(
                'page' => $page,
                'start_time' => $sdate,
                'end_time' => $edate,
                'terminal_sn' => $terminal
            );
        } else {
            $query = array(
                'page' => $page,
                'start_time' => $sdate,
                'end_time' => $edate,
            );

        }

        $params = '?' . http_build_query($query);
        $endpoint = 'iclock/api/transactions/' . $params;

        //leave options and undefined. guzzle will use the http:query;

        $response = $http->getTimeLogs($endpoint, "GET", $headers);
        //return $response;
        //print_r($sdate);
        //dd($response);
        return $response;
    }


    public function fetchBiotTimeLogs($end_date = FALSE, $terminal = FALSE)
    {
        ignore_user_abort(true);
        ini_set('max_execution_time', 0);
        $resp = $this->getTime($page = 1, $end_date = FALSE, $terminal = FALSE);
        $count = $resp->count;
        $pages = (int) ceil($count / 10);
        $rows = array();

        for ($currentPage = 1; $currentPage <= $pages; $currentPage++) {
            $response = $this->getTime($currentPage, $end_date = FALSE, $terminal = FALSE);
            foreach ($response->data as $mydata) {
                $datetime = date("Y-m-d H:i:s", strtotime($mydata->punch_time));
             
                $data = array(
                    "emp_code" => $mydata->emp_code,
                    "terminal_sn" => $mydata->terminal_sn,
                    "area_alias" => $mydata->area_alias,
                    "longitude" => $mydata->longitude,
                    "latitude" => $mydata->latitude,
                    "punch_state" => $mydata->punch_state,
                    "punch_time" => $datetime
                );
                array_push($rows, $data);
            }
        }

        $message = $this->biotimejobs_mdl->add_time_logs($rows);

        $this->logattendance($message);
        $process = 4;
        $method = "bioitimejobs/fetchBiotTimeLogs";
        if (count($response) > 0) {
            $status = "successful";
        } else {
            $status = "failed";
        }
        $this->biotimeClockin();
        $this->cronjob_register($process, $method, $status);
    }

    public function custom_logs()
    {
        $end_date = date('Y-m-d', strtotime($this->input->get('end_date')));
        $terminal_sn = $this->input->get('terminal_sn');

     //   dd($this->input->get());

        $url = "curl https://attend.health.go.ug/biotimejobs/fetchBiotTimeLogs/" . $end_date . "/" . $terminal_sn;
        shell_exec("$url");

        echo json_encode($url);


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
        $query = $this->db->query("SELECT id from biotime_departments where dept_code='$dep_id' LIMIT 1");
        return $query->result()[0]->id;
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
                'id' => $deps->id,
                'dep_code' => $deps->dept_code,
                'dept_name' => $deps->dept_name
            );
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
    public function fetch_time_history($start_date, $end_date, $terminal_sn = FALSE,$facility=FALSE, $empcode = FALSE)
    {
        ignore_user_abort(true);
        ini_set('max_execution_time', 0);
        $dates = array();
        $currentDate = strtotime($start_date); // Convert start date to a timestamp
        $endDate = strtotime($end_date); // Convert end date to a timestamp
        
        // Loop until all dates are processed
        while ($currentDate <= $endDate) {
            $dates = date('Y-m-d', $currentDate);
            $insert = array(); // Initialize the insert array for each date

            // Fetch data from the database
            $rows = $this->biotimejobs_mdl->get_attendance_data($dates, $empcode, $terminal_sn);

            // Check if there is data to insert
            if (!empty($rows)) {
                foreach ($rows as $object) {
                    $datetime = date("Y-m-d H:i:s", strtotime($object->punch_time));
                    $rowData = array(
                        "emp_code" => $object->emp_code,
                        "terminal_sn" => $object->terminal_sn,
                        "area_alias" => $object->area_alias,
                        "longitude" => $object->longitude,
                        "latitude" => $object->latitude,
                        "punch_state" => $object->punch_state,
                        "punch_time" => $datetime // Changed to punch_time to match the object's key
                    );
                    $insert[] = $rowData;
                }

                // Insert data in batches of 1000 rows
                foreach (array_chunk($insert, 1000) as $batch) {
                    $this->db->insert_batch('biotime_data', $batch);
                  
                    
                }

                // Clear the insert array
                $insert = array();
            }

            // Increment current date by 1 day
            $this->biotimeClockoutnight($dates);
            $currentDate = strtotime('+1 day', $currentDate);

            // Output status message
            echo "Data for " . $dates . " inserted successfully. Total rows affected: " . count($rows) . " ".$terminal_sn."<br>";
        }

        // Final completion message
      echo  "\e[32mData insertion completed successfully.\e[0m\n";

     

        // clcokin

    //    $clock = $this->db->query("CALL copy_clk_log_data()");
    //    if ($clock){
    //           $this->db->query("CALL insert_actuals()");
    //    echo  "\e[34m$(echo $this->db->affected_rows())\e[0m Recognized\n";

       //}

       

    }

    public function fetch_daily_attendance(){
       
        $end_date = date('Y-m-d');
       
        $machines = $this->db->query("SELECT * FROM biotime_devices")->result();
       foreach ($machines as $machine) {
        $device = $machine->sn;
        $startdate = $machine->last_activity;
        $start_timestamp = strtotime($startdate);
        $new_timestamp = $start_timestamp - 86400; // Subtracting one day (86400 seconds)
        echo $start = date('Y-m-d', $new_timestamp);
        $facility = $machine->area_name;
        echo "Start Synchronisation for " . $device . " " . $facility;
         
            // Convert end date to timestamp
            $end_timestamp = strtotime($end_date);

            // Calculate the difference in seconds
            $difference_seconds = $end_timestamp - $start_timestamp;

            // If you want to convert the difference to days, you can do so
            $difference_days = $difference_seconds / (60 * 60 * 24);
            if($difference_days<60){
            $this->fetch_time_history($start,$end_date,$device,$facility);
            $this->biotimeClockin();
            }
       
       }

        $this->terminals();

       
        //$this->db->query("TRUNCATE biotime_data");
      
      


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

        $response = $http->sendiHRIS5Request('hapi/fhir/Basic?_profile=http://ihris.org/fhir/StructureDefinition/ihris-manage-job', "GET", $headers, []);
var_dump($response);
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
