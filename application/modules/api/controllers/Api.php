<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Load Rest_Controller
use chriskacerguis\RestServer\RestController;

class Api extends RestController {

    public function __construct() {
        parent::__construct();
        $this->load->model('auth_model', 'mAuth');
        $this->load->model('employee_model', 'mEmployee');
    }

    public function login_post() {
        $username = $this->post('username');
        $password = $this->post('password');

        $result = $this->mAuth->login($username, $password);

        if($result != null) {  
            $this->response([
                'status' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => $result
                ]
            ], 200);
        }

        $this->response([
            'status' => false,
            'message' => 'Login failed'
        ], 401);
    }

    // Get Staff List
    public function staff_get() {
        $facilityId = $this->get('facility_id', true);

        $result = $this->mEmployee->get_staff_list($facilityId);
        if($result != null) {
            $this->response([
                'status' => true,
                'message' => 'Staff list',
                'data' => [
                    'staff' => $result
                ]
            ], 200);
        }

        $this->response([
            'status' => false,
            'message' => 'No staff found'
        ], 404);
    }

    // Enroll Users
    public function enroll_users_post() {
        //Array of enrolled ids
        $enrolled_ids = array();

        $data = $this->post();
       // For Each Data as a record
        foreach($data as $record) {
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

            if($result != null) {
                array_push($enrolled_ids, $result);
            }

            
        }
        
        if(count($enrolled_ids) > 0) {
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

    public function clock_users_post() {
        $data = $this->post();
        $clocked_ids = array();

        foreach($data as $record) {
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

            if($result != null) {
                array_push($clocked_ids, $result);
            }
        }

        if(count($clocked_ids) > 0) {
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

    
}