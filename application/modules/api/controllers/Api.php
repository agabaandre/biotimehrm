<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Api extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Auth_Model', 'auth_model');
        $this->load->model('Employee_Model', 'employee_model');
    }
    public function index_get()
    {
        echo "HRM ATTEND API";
    }
    public function login()
    {

        
    }

    public function clock_user($data)
    {
        //
        $userdata = $this->input->post();
        $result = $this->employee_model->clock_user($userdata);
        echo json_encode($result);
    }

    public function clock_history($facility)
    {
        // to download
    }

    public function enroll_user($userdata)
    {
        $userdata = $this->input->post();
        $result = $this->employee_model->enroll_user($userdata);
        echo json_encode($result);
    }
    public function upload_resources($userdata)
    {
        //image ai data set
    }
    public function download_resources()
    {
        //image ai data set

    }
    public function get_device_status()
    {

        //Device should call the server with the location, Facility ID and Time Stamp sERIAL NUMBER
    }
    public function receive_notfications($facility, $all)
    {

        //Device should call the server with the location, Facility ID and Time Stamp sERIAL NUMBER
    }

    public function enrolled_users($facilityId)
    {
        $result = $this->employee_model->get_enrolled_employees(urldecode($facilityId));
        echo json_encode($result);
    }

    public function employees($facility)
    {
        //provides an employee list for enrollment
    }
//individual logins int the app
    public function sendRequest()
    {
        //File upload

    }
    public function getRequest_Status($user)
    {
        //order by date
    }
}
