<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Load Rest_Controller
use chriskacerguis\RestServer\RestController;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Api extends RestController
{

    public $key = "qwerty@123";

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Auth_model', 'mAuth');
        $this->load->model('Employee_model', 'mEmployee');
    }

    public function validateRequest()
    {
        $headers = $this->input->request_headers();
        if (isset($headers['Authorization'])) {
            $token = $headers['Authorization'];

            //Remove Bearer from string
            $token = str_replace('Bearer ', '', $token);


            $decoded = JWT::decode($token, new Key($this->key, 'HS256'));

            $decoded = (array) $decoded;

            if ($decoded) {
                return $decoded;
            } else {
                $this->response([
                    'status' => 'FAILED',
                    'message' => 'You are not authorized to access this page',
                ], 401);
            }
        } else {
            $this->response([
                'status' => 'FAILED',
                'message' => 'Unable to authorize this request. Please try again',
            ], 401);
        }
    }



    public function login_post()
    {
        $username = $this->post('username');
        $password = $this->post('password');

        // If inputs are empty return 401
        if (empty($username) || empty($password)) {
            $this->response([
                'status' => 'FAILED',
                'message' => 'Please provide username and password',
            ], 401);
        }

        // Check if user exists
        $user = $this->mAuth->login($username, $password);

        if ($user) {

            $payload = array();
            $payload['user_id'] = $user->user_id;
            $payload['facility_id'] = $user->facility_id;

            $token = JWT::encode($payload, $this->key, 'HS256');

            $this->response([
                'status' => 'SUCCESS',
                'message' => 'Login successful',
                'token' => $token,
                'user' => $user,
            ], 200);
        } else {
            $this->response([
                'status' => 'FAILED',
                'message' => 'Invalid username or password',
            ], 401);
        }
    }

    // Get Staff List
    public function staff_get()
    {
        $decoded = $this->validateRequest();

        $facilityId = $decoded['facility_id'];

        $staff = $this->mEmployee->get_staff_list($facilityId);

        if ($staff) {
            $this->response([
                'status' => 'SUCCESS',
                'message' => 'Staff list fetched successfully',
                'staff' => $staff,
            ], 200);
        } else {
            $this->response([
                'status' => 'FAILED',
                'message' => 'No staff found',
            ], 404);
        }
    }

    // Enroll Users
    public function enroll_users_post()
    {
        //Array of enrolled ids
        $enrolled_ids = array();

        $data = $this->post();
         // For Each Data as a record
        foreach  ($data as $record) {
            $userRecord =  array();
            $userRecord['entry_id'] = $record['entry_id'];
            $userRecord['ihris_pid'] = $record['ihris_pid'];
            $userRecord['facility_id'] = $record['facility_id'];
            $userRecord['face_data'] = $record['face_data'];
            $userRecord['fingerprint'] = $record['fingerprint'];
            $userRecord['location'] = $record['location'];
            $userRecord['card_number'] = $record['card_number'];
            $userRecord['enroll_date'] = $record['enroll_date'];
            $userRecord['device'] = $record['device'];

            $result = $this->mEmployee->enroll($userRecord);

            if ($result != null) {
                array_push($enrolled_ids, $result);
            }
        }

        if (count($enrolled_ids) > 0) {
            $this->response([
                'status' => true,
                'message' => 'Enrollment successful',
                'data' => [
                    'enrolled_ids' => $enrolled_ids
                ]
            ], 200);
        }

        $this->response([
            'status' => false,
            'message' => 'Enrollment failed'
        ], 404);
    }

    public function clock_users_post()
    {


        $data = $this->post();
        $clocked_ids = array();

        foreach ($data as $record) {
            $userRecord =  array();
            $userRecord['entry_id'] = $record['entry_id'];
            $userRecord['ihris_pid'] = $record['ihris_pid'];
            $userRecord['facility_id'] = $record['facility_id'];
            $userRecord['time_in'] = $record['time_in'];
            $userRecord['time_out'] = $record['time_out'];
            $userRecord['date'] = $record['date'];
            $userRecord['status'] = $record['status'];
            // Location, Source, Facility
            $userRecord['location'] = $record['location'];
            $userRecord['source'] = $record['source'];
            $userRecord['facility'] = $record['facility'];

            $result = $this->mEmployee->clock($userRecord);

            if ($result != null) {
                array_push($clocked_ids, $result);
            }
        }

        if (count($clocked_ids) > 0) {
            $this->response([
                'status' => true,
                'message' => 'Clocking successful',
                'data' => [
                    'clocked_ids' => $clocked_ids
                ]
            ], 200);
        }

        $this->response([
            'status' => false,
            'message' => 'Clocking failed'
        ], 401);
    }

    // Upload Device Resources
    public function upload_fingerprints_post()
    {



        // Upload Fingerprints of file_type fpt
        $config['upload_path'] = './uploads/fingerprints/';
        $config['allowed_types'] = 'fpt';
        $config['max_size'] = 10000;
        $config['overwrite'] = TRUE;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('fingerprint')) {
            $this->response([
                'status' => false,
                'message' => $this->upload->display_errors()
            ], 401);
        } else {
            $this->response([
                'status' => true,
                'message' => 'Fingerprint uploaded successfully'
            ], 200);
        }
    }

    public function upload_faces_post()
    {



        // Upload Faces of file_type jpg
        $config['upload_path'] = './uploads/faces/';
        $config['allowed_types'] = 'jpg';
        $config['max_size'] = 10000;
        $config['overwrite'] = TRUE;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('face')) {
            $this->response([
                'status' => false,
                'message' => $this->upload->display_errors()
            ], 401);
        } else {
            $this->response([
                'status' => true,
                'message' => 'Face uploaded successfully'
            ], 200);
        }
    }

    // Check Device Time in sync with server
    public function check_time_get()
    {



        $time = $this->get('time', true);

        // Get current server time
        $serverTime = date('Y-m-d H:i:s');

        // Convert both to milliseconds from epoch and compare
        $time = strtotime($time) * 1000;
        $serverTime = strtotime($serverTime) * 1000;

        // They must be almost identical
        if (abs($time - $serverTime) < 1000) {
            $this->response([
                'status' => true,
                'message' => 'Time in sync'
            ], 200);
        }

        $this->response([
            'status' => false,
            'message' => 'Time not in sync'
        ], 401);
    }
}

