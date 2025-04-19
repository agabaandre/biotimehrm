<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Load Rest_Controller
use chriskacerguis\RestServer\RestController;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;


/**
 * 
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
        if ($password != $passwordConfirm) {
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
	    // Check if user is logged in
        $decoded = $this->validateRequest();

        // Check if facility_name was passed in as query parameter
        $facility_name = $this->get('facility_name');

        $facilityId = $decoded['facility_id'];

        if (isset($facility_name)) {

            // Decode & get the facility with this name
            $facility = urldecode($facility_name);
            $facility = $this->mEmployee->get_facility_by_name($facility);

            // Get the id of the facility
            $facilityId = $facility->facility_id;
        }

        // Dump faciltity
        // dd($facilityId);

        $staffList = $this->mEmployee->get_staff_list($facilityId);

        $this->response([
            'status' => 'SUCCESS',
            'message' => 'Staff list fetched successfully',
            'staff' => $staffList,
        ], 200);
	}

    // SENDING ENROLLMENT RECORDS to the server
    public function staff_list_post()
    {
        // Get the POST data
        $post_data = $this->post();
        if($post_data['face_data']=='null'){
            $faces_data =NULL;

        } 
        else{
            $faces_data =$post_data['face_data'];
            
        }
        if ($post_data['fingerprint_data'] == 'null') {
            $finger_data = NULL;

        } else {
            $finger_data = $post_data['fingerprint_data'];

        }
        // Check if any data is received
        if (!empty($post_data)) {
            // Accessing specific fields from the received data
            $enrolled = $post_data['enrolled'];
            $facility = $post_data['facility'];
            $facility_id = $post_data['facility_id'];
            $firstname = $post_data['firstname'];
            $id = $post_data['id'];
            $ihris_pid = $post_data['ihris_pid'];
            $job = $post_data['job'];
            $surname = $post_data['surname'];
            $synced = $post_data['synced'];
            $template = $post_data['template'];
            $face_data = NULL;
            $fingerprint_data = NULL;

            $data = [
                'ihris_pid' => $ihris_pid,
                'enrolled' => 1,
                'face_data' => $face_data,
                'fingerprint_data' => $fingerprint_data,
                '$template' => $template
            ];

            // Perform any other necessary operations with the data
            $this->mEmployee->post_staff_list($data);

            $this->response([
                'status' => 'SUCCESS',
                'message' => 'Data received successfully',
                'data' => $post_data,  // You can send back the received data in the response
            ], 200);
        } else {
            // No data received
            $this->response([
                'status' => 'FAILURE',
                'message' => 'No data received',
            ], 400);
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
    public function enroll_user_post() 
	{
	    // Get the JSON input
	    $input = $this->post();
	
	    // Validate and sanitize fingerprint data
	    $fingerprintData = $input['fingerprint_data'] ?? null;
	    if ($fingerprintData === 'null' || $fingerprintData === null || $fingerprintData === '') {
	        $fingerprintData = null;
	    } else if (is_string($fingerprintData)) {
	        // If it's a JSON string, decode it
	        $decoded = json_decode($fingerprintData, true);
	        if (json_last_error() === JSON_ERROR_NONE) {
	            $fingerprintData = $decoded;
	        }
	    }
	
	    // Validate and sanitize face data
	    $faceData = $input['face_data'] ?? null;
	    if ($faceData === 'null' || $faceData === '' || $faceData === 'undefined') {
	        $faceData = null;
	    }
	
	    // Determine enrollment status based on actual data presence
	    $faceEnrolled = !empty($faceData);
	    $fingerprintEnrolled = !empty($fingerprintData);
	    $enrolled = ($faceEnrolled || $fingerprintEnrolled) ? 1 : 0;
	
	    // Construct sanitized user record
	    $userRecord = [
	        'face_data' => $faceData,
	        'fingerprint_data' => $fingerprintData,
	        'ihris_pid' => trim($input['ihris_pid']),
	        'facility_id' => trim($input['facility_id']),
	        'firstname' => trim($input['firstname']),
	        'surname' => trim($input['surname']),
	        'job' => trim($input['job']),
	        'synced' => $input['synced'] ?? 0,
	        'template_id' => $input['template_id'] ?? null,
	        'face_enrolled' => $faceEnrolled ? 1 : 0,
	        'fingerprint_enrolled' => $fingerprintEnrolled ? 1 : 0,
	        'enrolled' => $enrolled
	    ];
	
	    // Validate required fields
	    $requiredFields = ['ihris_pid', 'facility_id', 'firstname', 'surname'];
	    foreach ($requiredFields as $field) {
	        if (empty($userRecord[$field])) {
	            $this->response([
	                'status' => 'FAILED',
	                'message' => "Missing required field: {$field}",
	                'data' => null
	            ], 400);
	            return;
	        }
	    }
	
	    // Call the model method to enroll the user
	    $result = $this->mEmployee->enroll($userRecord);
	
	    if ($result['status']) {
	        $this->response([
	            'status' => 'SUCCESS',
	            'message' => $result['message'],
	            'data' => $userRecord
	        ], 200);
	    } else {
	        $this->response([
	            'status' => 'FAILED',
	            'message' => $result['message'],
	            'data' => $userRecord
	        ], 400);
	    }
	}
    public function clock_user_post()
    {
        // Get the JSON input
        $input = $this->post();

        $userRecord = array();

        // Set the current date and time from the server
        date_default_timezone_set('Africa/Kampala');
        $currentDate = date('Y-m-d');
        $currentTime = date('Y-m-d H:i:s');

        // Construct the entry_id: {timestamp}_{ihris_pid}
        $userRecord['entry_id'] = time() . '|' . $input['ihris_pid'];
        $userRecord['ihris_pid'] = $input['ihris_pid'];
        // $userRecord['name'] = $input['name'];
        // $userRecord['synced'] = $input['synced'];
        $userRecord['facility_id'] = $input['facility_id'];
        $userRecord['source'] = 'mobile';
        $userRecord['latitude'] = $input['latitude'];
        $userRecord['longitude'] = $input['longitude'];

        // Set the current date
        $userRecord['date'] = $currentDate;

        // Normalize clock status to handle various formats
        $clockStatus = strtoupper(trim($input['clock_status']));
        
        // Log the value for debugging
        log_message('debug', 'Clock status received: ' . $clockStatus);
        
        // More flexible validation that accepts variations
        if ($clockStatus == "IN" || $clockStatus == "CLOCK_IN" || $clockStatus == "CLOCKED_IN" || $clockStatus == "CLOCKIN") {
            $userRecord['time_in'] = $currentTime;
            $userRecord['time_out'] = null;
            $userRecord['status'] = "CLOCKED_IN";
        } elseif ($clockStatus == "OUT" || $clockStatus == "CLOCK_OUT" || $clockStatus == "CLOCKED_OUT" || $clockStatus == "CLOCKOUT") {
            $userRecord['time_out'] = $currentTime;
            $userRecord['time_in'] = null;
            $userRecord['status'] = "CLOCKED_OUT";
        } else {
            $this->response([
                'status' => false,
                'message' => 'Invalid clock status: "' . $input['clock_status'] . '". Expected "IN" or "OUT"',
            ], 400);
            return;
        }

        // Determine the shift based on the current hour
        $currentHour = date('H');
        if ($currentHour >= 6 && $currentHour < 14) {
            $userRecord["shift"] = "Morning";
        } elseif ($currentHour >= 14 && $currentHour < 22) {
            $userRecord["shift"] = "Afternoon";
        } else {
            $userRecord["shift"] = "Night";
        }

        // Get Facility Name
        $facilityName = $this->mEmployee->get_facility_name($userRecord["facility_id"]);
        $userRecord["location"] = $facilityName;
        $userRecord["facility"] = $facilityName;

        // Save the user record
        $result = $this->mEmployee->clock($userRecord);

        if ($result['status']) {
            // Successful insert
            $this->response([
                'status' => true,
                'message' => 'User clock record received successfully',
                'data' => $userRecord
            ], 200);
        } else {
            // Failed insert, include the specific error message
            $this->response([
                'status' => false,
                'message' => 'Failed to clock in due to database error',
                'error' => $result['error'],
                'data' => $userRecord,
            ], 500); // Internal Server Error
        }
    }

    // Upload Device Resources
    public function upload_fingerprint_post()
    {
        $decoded = $this->validateRequest();
        
        // Get the staff ID from the request
        $staffId = $this->post('staff_id');
        
        if (empty($staffId)) {
            $this->response([
                'status' => 'FAILED',
                'message' => 'Staff ID is required'
            ], 400);
            return;
        }
        
        // Prepare user-specific directory and sanitize staff ID
        $pathInfo = $this->sanitize_and_prepare_path($staffId, 'fingerprints');
        $sanitizedStaffId = $pathInfo['sanitized_id'];
        $userDir = $pathInfo['dir'];

        // Load the necessary libraries
        $this->load->library('upload');

        // Set upload configuration
        $config['upload_path'] = $userDir; // User-specific directory
        $config['allowed_types'] = 'fpt|dat|*'; // Allowing all file types for fingerprint data
        $config['max_size'] = 10240; // 10MB
        $config['file_name'] = $sanitizedStaffId; // Use sanitized staff ID as filename
        $config['overwrite'] = TRUE; // Overwrite existing file

        // Initialize the upload library with the configuration
        $this->upload->initialize($config);

        // Use exactly the field name from BiometricUtils.java
        if (!$this->upload->do_upload('fingerprint')) {
            // If the upload fails, return an error response
            $error = $this->upload->display_errors();
            $this->response([
                'status' => 'FAILED',
                'message' => 'Unable to upload fingerprint data',
                'error' => $error
            ], 500);
        } else {
            $upload_data = $this->upload->data();
            $filePath = $upload_data['full_path'];
            
            // Update the database with the fingerprint data
            $result = $this->mEmployee->upload_fingerprint_data($staffId, $filePath);
            
            $this->response([
                'status' => 'SUCCESS',
                'message' => 'Fingerprint data uploaded successfully',
                'file_info' => [
                    'file_path' => $filePath,
                    'file_name' => $upload_data['file_name'],
                    'staff_id' => $staffId
                ]
            ], 200);
        }
    }

    public function upload_face_post()
    {
        $decoded = $this->validateRequest();
        
        // Get the staff ID from the request
        $staffId = $this->post('staff_id');
        
        if (empty($staffId)) {
            $this->response([
                'status' => 'FAILED',
                'message' => 'Staff ID is required'
            ], 400);
            return;
        }

        // Prepare user-specific directory and sanitize staff ID
        $pathInfo = $this->sanitize_and_prepare_path($staffId, 'faces');
        $sanitizedStaffId = $pathInfo['sanitized_id'];
        $userDir = $pathInfo['dir'];
        
        // Load the necessary libraries
        $this->load->library('upload');

        // Set upload configuration
        $config['upload_path'] = $userDir; // User-specific directory
        $config['allowed_types'] = 'jpg|jpeg|png|gif|image/*'; // Allowed image types
        $config['max_size'] = 10240; // 10MB
        $config['file_name'] = $sanitizedStaffId; // Use sanitized staff ID as filename
        $config['overwrite'] = TRUE; // Overwrite existing file

        // Initialize the upload library with the configuration
        $this->upload->initialize($config);

        // Use exactly the field name from BiometricUtils.java
        if (!$this->upload->do_upload('face_image_path')) {
            // If the upload fails, return an error response
            $error = $this->upload->display_errors();
            $this->response([
                'status' => 'FAILED',
                'message' => 'Unable to upload face image',
                'error' => $error
            ], 500);
        } else {
            $upload_data = $this->upload->data();
            $filePath = $upload_data['full_path'];
            
            // Update the database with the face data
            $result = $this->mEmployee->upload_face_data($staffId, $filePath);
            
            $this->response([
                'status' => 'SUCCESS',
                'message' => 'Face data uploaded successfully',
                'file_info' => [
                    'file_path' => $filePath,
                    'file_name' => $upload_data['file_name'],
                    'staff_id' => $staffId
                ]
            ], 200);
        }
    }

    // Download face data
    public function download_face_get($id)
    {
        $decoded = $this->validateRequest();
        
        if (empty($id)) {
            $this->response([
                'status' => 'FAILED',
                'message' => 'Staff ID is required'
            ], 400);
            return;
        }
        
        $faceData = $this->mEmployee->download_face_data($id);
        
        if ($faceData) {
            // Check if the file exists
            if (file_exists($faceData)) {
                // Return the file content
                $this->response(file_get_contents($faceData), 200);
            } else {
                $this->response([
                    'status' => 'FAILED',
                    'message' => 'Face data file not found'
                ], 404);
            }
        } else {
            $this->response([
                'status' => 'FAILED',
                'message' => 'No face data found for this staff member'
            ], 404);
        }
    }

    // Download fingerprint data
    public function download_fingerprint_get($id)
    {
        $decoded = $this->validateRequest();
        
        if (empty($id)) {
            $this->response([
                'status' => 'FAILED',
                'message' => 'Staff ID is required'
            ], 400);
            return;
        }
        
        $fingerprintData = $this->mEmployee->download_fingerprint_data($id);
        
        if ($fingerprintData) {
            // Return the fingerprint data
            $this->response($fingerprintData, 200);
        } else {
            $this->response([
                'status' => 'FAILED',
                'message' => 'No fingerprint data found for this staff member'
            ], 404);
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

    public function notifications_list_get()
    {
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
    public function clock_history_list_get()
    {
        $decoded = $this->validateRequest();
        $facilityId = $decoded['facility_id'];
        $clock_history = $this->mEmployee->get_clock_history_list($facilityId);
        $this->response([
            'status' => 'SUCCESS',
            'message' => 'Success',
            'clock_history' => $clock_history
        ]);
    }

    // Get a list of all the facilities
    public function facilities_get()
    {
        $decoded = $this->validateRequest();
        $userId = $decoded['user_id'];
        $facilities = $this->mEmployee->get_facilities_list($userId);
        $this->response([
            'status' => 'SUCCESS',
            'message' => 'Success',
            'facilities' => $facilities
        ]);
    }

    public function clock_user_mobile_post()
    {
        try {
            $decoded = $this->validateRequest();
            $userId = $decoded['user_id'];

            // Extract data from the request

            $ihris_pid = $this->post('ihris_pid');
            $facility_id = $this->db->query("SELECT  facility_id from  ihrisdata  where ihris_pid='$ihris_pid'")->row()->facility_id;

            $tin = $this->post('time_in');

            $date_time = DateTime::createFromFormat('d/m/Y H:i', $tin);
            if ($date_time !== false) {
                $timein = $date_time->format('Y-m-d H:i:s');
            }


            $tout = $this->post('time_out');

            $date_time2 = DateTime::createFromFormat('d/m/Y H:i', $tout);
            if ($date_time2 !== false) {
                $timeout = $date_time2->format('Y-m-d H:i:s');
            }

            $dt = $this->post('date');
            $date = date('Y-m-d', strtotime($dt));

            $data = array(
                // Assuming you receive data such as entry_id, ihris_pid, facility_id, time_in, time_out, date, status, location, source, facility from the client
                'entry_id' => $this->post('entry_id'),
                'ihris_pid' => $this->post('ihris_pid'),
                'facility_id' => $facility_id,
                'time_in' => $timein,
                'time_out' => $timeout,
                'date' => $dt,
                'status' => $this->post('status'),
                'location' => $this->post('facility'),
                'source' => 'Mobile App',
                'facility' => $this->post('facility'),
                'longitude' => $this->post('longitude'),
                'latitude' => $this->post('latitude')
            );

            // Call the model method to insert data into the database
            $this->mEmployee->clock_user_mobile($data);

            $this->response([
                'status' => 'SUCCESS',
                'message' => json_encode($data)
            ]);
        } catch (Exception $e) {
            $this->response([
                'status' => 'FAILED',
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function enroll_user_mobile_post()
    {
        try {
            $decoded = $this->validateRequest();
            $userId = $decoded['user_id'];

            // Extract data from the request
            $data = array(
                // Assuming you receive data such as enrolled, face_data, fingerprint_data, ihris_pid from the client
                'enrolled' => $this->post('enrolled'),
                'face_data' => $this->post('face_data'),
                'fingerprint_data' => $this->post('fingerprint_data'),
                'ihris_pid' => $this->post('ihris_pid')
            );

            // Call the model method to insert data into the database
            $this->mEmployee->enroll_user_mobile($data);

            $this->response([
                'status' => 'SUCCESS',
                'message' => 'Data inserted successfully'
            ]);
        } catch (Exception $e) {
            $this->response([
                'status' => 'FAILED',
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    // Route for uploading fpt files
    public function upload_fpt_post()
    {
        try {
            // Load the necessary libraries
            $this->load->library('upload');

            // Set upload configuration
            $config['upload_path'] = './uploads/fpt/'; // Change this to your desired upload directory
            $config['allowed_types'] = 'fpt'; // Allowed file types
            $config['max_size'] = 2048; // Maximum file size in kilobytes

            // Initialize the upload library with the configuration
            $this->upload->initialize($config);

            // Perform the upload
            if (!$this->upload->do_upload('fpt_file')) {
                // If the upload fails, return an error response
                $error = $this->upload->display_errors();
                $this->response([
                    'status' => 'FAILED',
                    'message' => 'Unable to upload fpt file',
                    'error' => $error
                ], 500);
            } else {
                // If upload succeeds, get the file data
                $upload_data = $this->upload->data();
                $file_path = $upload_data['full_path']; // Path to the uploaded file

                // Read the content of the fpt file
                $fpt_content = file_get_contents($file_path);

                // Extract ihris_pid from the filename or request data, assuming ihris_pid is included in the request
                $ihris_pid = $this->post('ihris_pid');

                // Update mobile_enroll table with fingerprint data and ihris_pid
                $data = array(
                    'fingerprint_data' => $fpt_content,
                    'ihris_pid' => $ihris_pid
                );

                // Call the model method to update mobile_enroll
                $this->mEmployee->update_mobile_enroll($data);

                // Return success response
                $this->response([
                    'status' => 'SUCCESS',
                    'message' => 'Fingerprint data uploaded and mobile enroll updated successfully'
                ], 200);
            }
        } catch (Exception $e) {
            // Return error response if any exception occurs
            $this->response([
                'status' => 'FAILED',
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    // Route for uploading face images
    public function upload_faces_post()
    {
        try {
            // Load the necessary libraries
            $this->load->library('upload');

            // Set upload configuration
            $config['upload_path'] = './uploads/faces/'; // Change this to your desired upload directory
            $config['allowed_types'] = 'jpg|jpeg|png'; // Allowed image types
            $config['max_size'] = 2048; // Maximum file size in kilobytes

            // Initialize the upload library with the configuration
            $this->upload->initialize($config);

            // Perform the upload
            if (!$this->upload->do_upload('face_image')) {
                // If the upload fails, return an error response
                $error = $this->upload->display_errors();
                $this->response([
                    'status' => 'FAILED',
                    'message' => 'Unable to upload face image',
                    'error' => $error
                ], 500);
            } else {
                // If upload succeeds, get the file data
                $upload_data = $this->upload->data();
                $file_path = $upload_data['full_path']; // Path to the uploaded file

                // Extract ihris_pid from the filename or request data, assuming ihris_pid is included in the request
                $ihris_pid = $this->post('ihris_pid');

                // Update mobile_enroll table with face data and ihris_pid
                $data = array(
                    'face_data' => $file_path, // Assuming you store the file path in the database
                    'ihris_pid' => $ihris_pid
                );

                // Call the model method to update mobile_enroll
                $this->mEmployee->update_mobile_enroll($data);

                // Return success response
                $this->response([
                    'status' => 'SUCCESS',
                    'message' => 'Face image uploaded and mobile enroll updated successfully'
                ], 200);
            }
        } catch (Exception $e) {
            // Return error response if any exception occurs
            $this->response([
                'status' => 'FAILED',
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    // Helper method to create a directory and sanitize filename
    private function sanitize_and_prepare_path($staffId, $type) {
        // Remove any special characters from the staff ID
        $sanitizedStaffId = preg_replace('/[^a-zA-Z0-9_-]/', '_', $staffId);
        
        // Create user's folder in the appropriate directory
        $basePath = './uploads/' . $type . '/';
        $userDir = $basePath . $sanitizedStaffId;
        
        // Create directory if it doesn't exist
        if (!is_dir($userDir)) {
            mkdir($userDir, 0755, true);
        }
        
        return [
            'dir' => $userDir,
            'sanitized_id' => $sanitizedStaffId
        ];
    }
}
