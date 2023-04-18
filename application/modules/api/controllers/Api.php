<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Load Rest_Controller
use chriskacerguis\RestServer\RestController;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;


/**
 * @property Apiauth_model $mAuth
 * @property Apiemployee_model $mEmployee
 */
class Api extends RestController
{

    public $key = "qwerty@123";

    public function __construct()
    {
        parent::__construct();
        
        $this->load->model('Apiauth_model', 'mAuth');
        $this->load->model('Apiemployee_model', 'mEmployee');
    }

    public function validateRequest()
    {
        $headers = $this->input->request_headers();
        if (isset($headers['Authorization'])) {
            $token = $headers['Authorization'];

            // Check if token is not null
            if ($token == 'Bearer null') {
                $this->response([
                    'status' => 'FAILED',
                    'message' => 'Authorization token is required',
                ], 401);
            }

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
            // $payload['expiry'] = time() + (24 * 60 * 60); // add 24 hours to the current time

            // calculate the timestamp for the next January 1st
            $now = time();
            $january1st = strtotime(date('Y', $now) . '-01-01');
            if ($now >= $january1st) {
                // if the current time is after January 1st this year,
                // set the expiry to January 1st next year
                $expiry = strtotime(date('Y', $now + 31536000) . '-01-01');
            } else {
                // otherwise, set the expiry to January 1st this year
                $expiry = $january1st;
            }
            $payload['expiry'] = $expiry;

            $token = JWT::encode($payload, $this->key, 'HS256');

            $this->response([
                'status' => 'SUCCESS',
                'message' => 'Login successful',
                'user' => [
                    'user_id' => $user->user_id,
                    'ihris_pid' => $user->ihris_pid,
                    'email' => $user->email,
                    'username' => $user->username,
                    'name' => $user->name,
                    'role_id' => $user->group_id,
                    'role_name' => $user->group_name,
                    'facility_id' => $user->facility_id,
                    'facility_name' => $user->facility,
                    'token' => $token
                ],
            ], 200);
        } else {
            $this->response([
                'status' => 'FAILED',
                'message' => 'Invalid username or password',
            ], 401);
        }
    }

    public function register_post()
    {
        // Get user input
        $username = $this->post('username');
        $email = $this->post('email');
        $name = $this->post('name');
        $password = $this->post('password');
        $passwordConfirm = $this->post('password_confirm');

        // If inputs are empty return 401
        if (empty($username) || empty($email) || empty($name) || empty($password)) {
            $this->response([
                'status' => 'FAILED',
                'message' => 'Please provide all required information',
            ], 401);
        }

        // Check passwords match
        if($password != $passwordConfirm) {
            $this->response([
                'status' => 'FAILED',
                'message' => 'Password confirmation does not match'
            ]);
        }

        
        // Check if user already exists
        $existing_user = $this->mAuth->get_user_by_username_or_email($username, $email);

        if ($existing_user) {
            $this->response([
                'status' => 'FAILED',
                'message' => 'User with this username or email already exists',
            ], 401);
        }

        // Hash password
        $hashed_password = $this->argonhash->make($password);

        // Insert user record into database
        $user_id = $this->mAuth->create_user($username, $email, $name, $hashed_password);

        if ($user_id) {
            $this->response([
                'status' => 'SUCCESS',
                'message' => 'Registration successful',
            ], 200);
        } else {
            $this->response([
                'status' => 'FAILED',
                'message' => 'Registration failed. Please try again later',
            ], 500);
        }
    }

    public function forgot_password_post()
    {
        // Get user input
        $email = $this->post('email');

        // If input is empty return 401
        if (empty($email)) {
            $this->response([
                'status' => 'FAILED',
                'message' => 'Please provide email address',
            ], 401);
        }

        // Check if user with email exists
        $user = $this->mAuth->get_user_by_email($email);

        if ($user) {
            // Generate password reset token and insert into database
            $reset_token = bin2hex(random_bytes(16));
            $reset_token_expiration = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $this->mAuth->set_password_reset_token($user->user_id, $reset_token, $reset_token_expiration);

            // Send password reset email to user
            $email_data = [
                'reset_token' => $reset_token,
                'reset_link' => base_url('auth/reset_password/' . $reset_token),
            ];

            // You can use any email library or service to send email
            // Here is an example using CodeIgniter's email library
            $this->load->library('email');

            $this->email->from('noreply@example.com', 'Your Name');
            $this->email->to($email);
            $this->email->subject('Password Reset Request');
            $this->email->message($this->load->view('email_templates/password_reset', $email_data, TRUE));

            $this->email->send();

            $this->response([
                'status' => 'SUCCESS',
                'message' => 'Password reset email sent to your email address',
            ], 200);
        } else {
            $this->response([
                'status' => 'FAILED',
                'message' => 'User with this email addressexists',
            ], 401);
        }
    }


    // Get Staff List
    public function staff_list_get()
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

    // Get Staff Details
    public function staff_details_get($id)
    {
        $decoded = $this->validateRequest();

        $facilityId = $decoded['facility_id'];

        $staffDetails = $this->mEmployee->get_staff_details($id, $facilityId);

        if ($staffDetails) {
            $this->response([
                'status' => 'SUCCESS',
                'message' => 'Staff details fetched successfully',
                'user' => $staffDetails,
            ], 200);
        } else {
            $this->response([
                'status' => 'FAILED',
                'message' => 'Unable to get records for selected user',
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
        foreach ($data as $record) {
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

    public function notifications_list_get() {
        $decoded = $this->validateRequest();

        $facilityId = $decoded['facility_id'];

        $notifications = $this->mEmployee->get_notifications_list($facilityId);

        $this->response([
            'status' => 'SUCCESS',
            'message' => 'Success',
            'notifications' => $notifications,
        ], 200);
    }

    // Clock History
    public function clock_history_list_get() {
        $decoded = $this->validateRequest();
        $facilityId = $dec['facility_id'];
        $clock_history = $this->mEmployee->get_clock_history_list($facilityId);
        $this->response([
            'status' => 'SUCCESS',
            'message' => 'Success',
            'clock_history' => $clock_history
        ]);
    }
}