<?php
date_default_timezone_set('Africa/Kampala');
defined('BASEPATH') or exit('No direct script access allowed');

use \utils\HttpUtil;

class Biotimejobs extends MX_Controller
{


    public  function __construct()
    {
        parent::__construct();

        $this->username = Modules::run('svariables/getSettings')->biotime_username;
        $this->password = Modules::run('svariables/getSettings')->biotime_password;
        $this->load->model('biotimejobs_mdl');
        @$this->facility = $_SESSION['facility'];
    }

    public function index()
    {
        echo "BIO-TIME HERE";
    }

    public function get_token($uri = FALSE)
    {

        $http = new HttpUtil();
        $headers = ['Content-Type' => 'application/json'];
        $body = array(
            "username" => $this->username,
            "password" => $this->password
        );
        $response = $http->sendRequest('jwt-api-token-auth', "POST", $headers, $body, $search = FALSE);
        // print_r ($response->token);
        return $response->token;
    }
    //get terminals
    public function terminals()
    {
        $http = new HttpUtil();
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            // 'Authorization' => "Token ".$this->get_general_auth(),
            'Authorization' => "JWT " . $this->get_token(),
        ];

        $response = $http->sendRequest('iclock/api/terminals', "GET", $headers, []);
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
            $message = $this->biotimejobs_mdl->addMachines($insert);
        }
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
        $http = new HttpUtil();
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        $response = $http->sendiHRISRequest('apiv1/index.php/api/ihrisdata', "GET", $headers, []);

        if ($response) {
            $message = $this->biotimejobs_mdl->add_ihrisdata($response);
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
    }
    //employees all enrolled users before creating new ones.


    public function get_ucmbdata()
    {
        $http = new HttpUtil();
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        $response = $http->sendUCMBiHRISRequest('apiv1/index.php/api/ihrisdata', "GET", $headers, []);

        if ($response) {
            $message = $this->biotimejobs_mdl->add_ucmbdata($response);
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

        $http = new HttpUtil();
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
        $pages = (int)ceil($count / 10);
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
    public function getTime($page = FALSE, $userdate = FALSE)
    {
        date_default_timezone_set('Africa/Kampala');
        $http = new HttpUtil();
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => "JWT " . $this->get_token(),
        ];
        if (empty($userdate)) {
            $edate = date('Y-m-d H:i:s');
        } else {
            $page = $userdate;
        }

        //if las sync is empty
        //    $sdate="2021-10-22 00:00:00";
        //    $edate = "2021-10-30 00:00:00";

        $sdate = date("Y-m-d H:i:s", strtotime("-12 hours"));
        $query = array(
            'page' => $page, 'start_time' => $sdate,
            'end_time' => $edate,
        );

        $params = '?' . http_build_query($query);
        $endpoint = 'iclock/api/transactions/' . $params;

        //leave options and undefined. guzzle will use the http:query;

        $response = $http->getTimeLogs($endpoint, "GET", $headers);
        //return $response;
        return $response;
    }


    public function fetchBiotTimeLogs($user_date = FALSE)
    {
        ignore_user_abort(true);
        ini_set('max_execution_time', 0);
        $resp = $this->getTime($page = 1, $user_date);
        $count = $resp->count;
        $pages = (int)ceil($count / 10);
        $rows = array();

        for ($currentPage = 1; $currentPage <= $pages; $currentPage++) {
            $response = $this->getTime($currentPage, $user_date);
            foreach ($response->data as $mydata) {

                $data = array(
                    "emp_code" => $mydata->emp_code,
                    "terminal_sn" => $mydata->terminal_sn,
                    "area_alias" => $mydata->area_alias,
                    "longitude" => $mydata->longitude,
                    "latitude" => $mydata->latitude,
                    "punch_state" => $mydata->punch_state,
                    "punch_time" => $mydata->punch_time
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
    //create multiple new users cronjob
    public function multiple_new_users()
    {
        $howmany = array();
        $query = $this->db->query("SELECT * FROM  ihrisdata WHERE ihrisdata.facility_id IN(SELECT area_code from biotime_devices) AND ihrisdata.card_number NOT IN (SELECT fingerprints_staging.card_number from fingerprints_staging)");
        $newusers = $query->result();
        foreach ($newusers as $newuser) :

            $message = $this->create_new_biotimeuser($newuser->firstname, $newuser->surname, $newuser->card_number, $newuser->facility_id, $newuser->department_id, $newuser->job_id);


        endforeach;
        $process = 5;
        $method = "bioitimejobs/multiple_new_users";
        if ($message) {
            $status = "successful";
        } else {
            $status = "failed";
        }
        $this->cronjob_register($process, $method, $status);
        $this->log($message);


        return $message;
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

        $http = new HttpUtil();

        $body = array(
            'first_name' => $firstname,
            'last_name' => $surname,
            'emp_code' => $emp_code,
            'area' => [(string)$parea],
            'department' => (string)$pdep,
            'position' => (string)$pjobs,
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
        return file_put_contents('log.txt', "\n{" . '"REQUEST DETAILS: ' . date('Y-m-d H:i:s') . ' Time": ' . json_encode($message) . '},', FILE_APPEND);
    }
    public function logattendance($message)
    {
        //add double [] at the beggining and at the end of file contents
        return file_put_contents('fetchatt_log.txt', "\n{" . '"REQUEST DETAILS: ' . date('Y-m-d H:i:s') . ' Time": ' . json_encode($message) . '},', FILE_APPEND);
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

        $http = new HttpUtil();
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

        $http = new HttpUtil();
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
                'position_name' => $jobs->posistion_name
            );
            array_push($j, $data);
        }

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

        $http = new HttpUtil();
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
    public function updateEnrolled()
    {
        //check local enrolled against server enrolled for change in facility or job.
        //update info
        //endpoint /personnel/api/employees/adjust_area/ {employees,	,areas}

    }

    public function biotimeClockin()
    {
        ignore_user_abort(true);
        ini_set('max_execution_time', 0);
        $this->db->query("CALL `clockin_users`();");

        $message = " Checkin " . $this->db->affected_rows();

        $this->biotimeClockout();
        $this->biotimeClockoutnight();


        $this->log($message);
    }
    //clockin and out users depening on gthe biotime clock data
    //    public function biotimeClockin(){
    //     ignore_user_abort(true);
    //     ini_set('max_execution_time',0);
    //     $areas=$this->db->get('biotime_devices')->result();
    //     foreach($areas as $area){
    //     $query=$this->db->query("REPLACE INTO clk_log (
    //       entry_id,
    //       ihris_pid,
    //       facility_id,
    //       time_in,
    //       date,
    //       location,
    //       source,
    //       facility)
    //       SELECT

    //      DISTINCT concat(DATE(biotime_data.punch_time),ihrisdata.ihris_pid) as entry_id,
    //       ihrisdata.ihris_pid,
    //       facility_id, 
    //       punch_time,
    //       DATE(biotime_data.punch_time) as date,
    //       area_alias,
    //       'BIO-TIME',
    //       ihrisdata.facility
    //       from  biotime_data, ihrisdata where biotime_data.area_alias='$area->area_name' AND (biotime_data.emp_code=ihrisdata.card_number) AND (punch_state='Check In' OR punch_state='0') ");

    //    $message=$area->area_name. " Checkin " .$this->db->affected_rows();

    //   }

    //   $this->biotimeClockout();


    //   $this->log($message);
    //   }

    public function biotimeClockout()
    {
        ignore_user_abort(true);
        ini_set('max_execution_time', 0);
        $query = $this->db->query("SELECT concat(DATE(biotime_data.punch_time),ihrisdata.ihris_pid) as `entry_id`, punch_time from biotime_data,ihrisdata where (biotime_data.emp_code=ihrisdata.card_number or biotime_data.ihris_pid=ihrisdata.ihris_pid) AND (punch_state='1' OR punch_state='Check Out' OR punch_state='0') AND concat(DATE(biotime_data.punch_time),ihrisdata.ihris_pid) in (SELECT `entry_id` from clk_log) ");
        $entry_id = $query->result();

        foreach ($entry_id as $entry) {
            $final_time = strtotime($entry->punch_time) / 3600;
            if ($final_time > 0) :
                $this->db->set('time_out', "$entry->punch_time");
                $this->db->where("time_in <", "$entry->punch_time");
                $this->db->where('entry_id', "$entry->entry_id");
                $query = $this->db->update('clk_log');
            endif;
        }
        //night shift

        echo $message = $this->db->affected_rows() . " Clocked Out";
        $this->log($message);
        $this->markAttendance();
    }
    //clockout night people
    public function biotimeClockoutnight()
    {
        ignore_user_abort(true);
        ini_set('max_execution_time', 0);

        //get night shift people.
        $today = date('Y-m-d');
        $yesterday = date("Y-m-d", strtotime("-1 day"));

        $nights = $this->db->query("SELECT duty_date,duty_rosta.ihris_pid as person_id,entry_id,card_number from duty_rosta,ihrisdata where schedule_id='16' and ihrisdata.ihris_pid=duty_rosta.ihris_pid  and concat(duty_date,duty_rosta.ihris_pid) in (SELECT entry_id from clk_log WHERE date='$yesterday'
    )")->result();
        foreach ($nights as $night) :
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
            if (($final_time > 0) && ($hours_worked <= 15)) :
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
        $this->markAttendance();
    }
    public function markAttendance()
    {
        ignore_user_abort(true);
        ini_set('max_execution_time', 0);
        //poplulate actuals
        $query = $this->db->query("REPLACE INTO actuals( entry_id, facility_id, department_id, ihris_pid, schedule_id, color,
     actuals.date, actuals.end,stream ) SELECT DISTINCT CONCAT( clk_log.date, ihrisdata.ihris_pid ) AS entry_id, ihrisdata.facility_id, 
     ihrisdata.department, ihrisdata.ihris_pid, schedules.schedule_id, schedules.color, clk_log.date, DATE_ADD(date, INTERVAL 01 DAY),clk_log.source FROM ihrisdata, 
     clk_log, schedules WHERE ihrisdata.ihris_pid = clk_log.ihris_pid AND schedules.schedule_id =22 AND CONCAT( clk_log.date, ihrisdata.ihris_pid )
      NOT IN (SELECT entry_id from actuals)");

        $rowsnow = $this->db->affected_rows();
        if ($query) {
            echo  $msg = "<font color='green'>" . $rowsnow . "  Attendance Records Marked</font><br>";
        } else {

            echo   $msg = "<font color='red'>Failed to Mark</font><br>";
        }
        $this->log($msg);
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
            echo  $msg = $rowsnow . "  Attendance Records Marked";
        } else {

            echo   $msg = "Failed to Mark";
        }
        $this->log($msg);


        $query = $this->db->query("Update actuals set schedule_id='25', color='#29910d' WHERE schedule_id IN(18,19,20,21)");

        $rowsnow = $this->db->affected_rows();
        if ($query) {
            echo  $msg = "" . $rowsnow . "  Leave records recognised by attendance";
        } else {

            echo   $msg = "No leave records found";
        }

        $query = $this->db->query("Update actuals set schedule_id='24', color='#d1a110' WHERE schedule_id='17'");

        $rowsnow = $this->db->affected_rows();
        if ($query) {
            echo  $msg = "" . $rowsnow . "  Offduty records recognised by attendance";
        } else {

            echo   $msg = "No Off duty records found";
        }
        $this->log($msg);
    }

    public function cronjob_register($process, $method, $status)
    {
        $data = array('process_id' => $process, 'process' => $method, 'status' => $status);
        $this->db->replace("cronjob_register", $data);
    }
}
