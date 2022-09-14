<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Api extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Auth_Model', 'authHandler');
        $this->load->model('Reason_Model', 'reasonHandler');
        $this->load->model('Request_Model', 'requestHandler');
        $this->load->model('Workshop_Model', 'workshopHandler');
        $this->load->model('Message_Model', 'messageHandler');
    }
    public function index_get()
    {
        echo "HRM ATTEND API";
    }
    public function login()
    {
        $userdata['username'] = $this->input->post('username');
        $userdata['password'] = $this->input->post('password');

        $model_response = $this->auth_model->validate_login($userdata);

        if ($model_response) {

            $response = array('error' => FALSE, 'message' => 'Successfully Authenticated', 'status' => 'USER_FOUND', 'user' => $model_response);
            echo json_encode($response);
        } else {
            $response = array('error' => TRUE, 'message' => 'Invalid username or password', 'status' => 'USER_NOT_FOUND');
            echo json_encode($response);
        }
    }

    public function clock_user()
    {
        $userdata = $this->input->post();
        $result = $this->employee_model->clock_user($userdata);
        echo json_encode($result);
    }

    public function enroll_user()
    {
        $userdata = $this->input->post();
        $result = $this->employee_model->enroll_user($userdata);
        echo json_encode($result);
    }

    public function enrolled_users($facilityId)
    {
        $result = $this->employee_model->get_enrolled_employees(urldecode($facilityId));
        echo json_encode($result);
    }
    public function clocked_employees($facilityId, $fingerprint)
    {
        $result = $this->employee_model->get_clocked_employees(urldecode($facilityId), $fingerprint);
        echo json_encode($result);
    }
}
