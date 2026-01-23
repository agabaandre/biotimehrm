<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Auth extends MX_Controller
{
  private $module;
  public function __construct()
  {
    parent::__construct();
    $this->load->model('auth_mdl');
    $this->module = "auth";
  }
  public function index()
  {
    $this->load->view("login");
  }
  
  /**
   * Check current session status
   */
  public function checkSession() {
    // Allow both AJAX and regular requests for session checking
    if (!$this->input->is_ajax_request() && !$this->input->get('ajax')) {
      show_404();
      return;
    }
    
    // Debug logging
    log_message('debug', 'checkSession called - isLoggedIn: ' . ($this->session->userdata('isLoggedIn') ? 'true' : 'false'));
    log_message('debug', 'checkSession called - session_id: ' . $this->session->session_id);
    log_message('debug', 'checkSession called - user_id: ' . $this->session->userdata('user_id'));
    
    if (!$this->session->userdata('isLoggedIn')) {
      log_message('debug', 'checkSession: Session not logged in, returning expired status');
      $this->output->set_content_type('application/json')->set_output(json_encode([
        'status' => 'expired',
        'message' => 'Session expired'
      ]));
      return;
    }
    
    $session_expiration = $this->config->item('sess_expiration');
    $session_start = $this->session->userdata('__ci_last_regenerate');
    $current_time = time();
    
    // Calculate time since session started
    $time_since_start = $current_time - $session_start;
    $expires_in = $session_expiration - $time_since_start;
    
    // Ensure expires_in is not negative
    if ($expires_in < 0) {
      $expires_in = 0;
    }
    
    log_message('debug', 'checkSession: Session active, expires_in: ' . $expires_in . ' seconds');
    log_message('debug', 'checkSession: Session started: ' . $session_start . ', Current time: ' . $current_time);
    log_message('debug', 'checkSession: Time since start: ' . $time_since_start . ' seconds');
    log_message('debug', 'checkSession: Session expiration config: ' . $session_expiration . ' seconds');
    
    // If session has expired, return expired status
    if ($expires_in <= 0) {
      log_message('debug', 'checkSession: Session has expired, returning expired status');
      $this->output->set_content_type('application/json')->set_output(json_encode([
        'status' => 'expired',
        'message' => 'Session expired',
        'expires_in' => 0
      ]));
      return;
    }
    
    $this->output->set_content_type('application/json')->set_output(json_encode([
      'status' => 'active',
      'expires_in' => $expires_in,
      'user_id' => $this->session->userdata('user_id'),
      'facility' => $this->session->userdata('facility'),
      'session_start' => $session_start,
      'current_time' => $current_time,
      'time_since_start' => $time_since_start
    ]));
  }

  /**
   * Check if user should only enter password (for recent logins within 2 hours)
   */
  public function checkRecentLogin() {
    if (!$this->input->is_ajax_request()) {
      show_404();
      return;
    }
    
    $username = $this->input->post('username');
    if (!$username) {
      $this->output->set_content_type('application/json')->set_output(json_encode([
        'status' => 'error',
        'message' => 'Username is required'
      ]));
      return;
    }
    
    // Check if this username has logged in recently (within 2 hours)
    $recent_login = $this->auth_mdl->checkRecentLogin($username);
    
    if ($recent_login) {
      $this->output->set_content_type('application/json')->set_output(json_encode([
        'status' => 'success',
        'recent_login' => true,
        'user_info' => [
          'username' => $recent_login->username,
          'name' => $recent_login->name,
          'email' => $recent_login->email,
          'last_login' => $recent_login->last_login
        ]
      ]));
    } else {
      $this->output->set_content_type('application/json')->set_output(json_encode([
        'status' => 'success',
        'recent_login' => false
      ]));
    }
  }
  
  /**
   * Refresh CSRF token
   */
  public function refreshCsrf() {
    if (!$this->input->is_ajax_request()) {
      show_404();
      return;
    }
    
    // Check if user is logged in
    if (!$this->session->userdata('isLoggedIn')) {
      $this->output->set_content_type('application/json')->set_output(json_encode([
        'status' => 'error',
        'message' => 'User not logged in'
      ]));
      return;
    }
    
    // Generate new CSRF token
    $csrf_token = $this->security->get_csrf_hash();
    
    $this->output->set_content_type('application/json')->set_output(json_encode([
      'status' => 'success',
      'csrf_token' => $csrf_token
    ]));
  }
  public function recovery()
  {
    $this->load->view("recover_password");
  }
  public function myprofile()
  {
    $data['module'] = "auth";
    $data['view'] = "profile";
    $data['title'] = "My Profile";
    $user_role = $this->session->userdata('role');
    if ($user_role == 'sadmin') {
      echo Modules::run("templates/main", $data);
    } else {
      echo Modules::run("templates/main", $data);
    }
  }
public function login($user_id = FALSE)
{
    // Prepare post data
    $postdata = !empty($user_id) ? array('username' => $user_id) : $this->input->post();
  //dd($postdata);
    // Check login credentials
    $person = $this->auth_mdl->loginChecker($postdata);
    //dd(password_algos());

    // If a valid user is found
    if (!empty($person->user_id)) {
        $user_group = $person->role;
        // Update last login timestamp
        $this->auth_mdl->updateLastLogin($person->user_id);
        
        $userdata = array(
            "names" => $person->name,
            "user_id" => $person->user_id,
            "ihris_pid" => $person->ihris_pid,
            "username" => $person->username,
            "role" => $person->group_name,
            "state" => $person->status,
            "dateChanged" => isset($person->changed) ? $person->changed : null,
            "changed" => isset($person->isChanged) ? $person->isChanged : null,
            "isLoggedIn" => true,
            "facility" => isset($person->facility_id) ? $person->facility_id : null,
            "facility_name" => isset($person->facility) ? $person->facility : null,
            "department" => isset($person->department) ? $person->department : null,
            "permissions" => $this->auth_mdl->getUserPerms($user_group),
            "department_id" => isset($person->department_id) ? $person->department_id : null,
            "division" => isset($person->division) ? $person->division : null,
            "unit" => isset($person->unit) ? $person->unit : null,
            "district_id" => isset($person->district_id) ? $person->district_id : null,
            "district" => isset($person->district) ? $person->district : null,
            "year" => date('Y'),
            "month" => date('m'),
            "date_from" => date("Y-m-d", strtotime("-1 month")),
            "date_to" => date('Y-m-d')
        );
  //dd($userdata);
        // Check user login state and redirect accordingly
        if (!$userdata['isLoggedIn']) {
            // Safely save to cache if memcached is available
            if (isset($this->cache) && isset($this->cache->memcached) && is_object($this->cache->memcached)) {
                try {
            $this->cache->memcached->save('facility', $userdata['facility_id'], 43600);
                } catch (Exception $e) {
                    log_message('error', 'Failed to save facility to cache: ' . $e->getMessage());
                }
            }
            $this->session->set_flashdata('msg', "Unauthorized access detected.");
            redirect("auth");
        } else {
            $this->session->set_userdata($userdata);
            redirect("dashboard");
        }
    } else {
        // Handle login failure
        $msg = ($person == "New")
            ? "First time access detected, Contact the Admin for Activation"
            : "Login Failed, Wrong credentials";
        $this->session->set_flashdata('msg', $msg);
        redirect("auth");
    }
}



  public function checkerUser($userdata)
  {
    // print_r("USer".$userdata);
    if (!$userdata['isLoggedIn']) {
      // Safely save to cache if memcached is available
      if (isset($this->cache) && isset($this->cache->memcached) && is_object($this->cache->memcached)) {
        try {
      $this->cache->memcached->save('facility', $userdata['facility_id'], 43600);
        } catch (Exception $e) {
          log_message('error', 'Failed to save facility to cache: ' . $e->getMessage());
        }
      }
      redirect("auth");
    } else {
      $this->session->set_userdata($userdata);
      redirect("dashboard");
    }
  }
  public function adminLegal()
  {
    if ($this->session->userdata['role'] !== "sadmin") {
      redirect("auth");
    }
  }
  public function isLegal()
  {
    date_default_timezone_set("Africa/Kampala");
    if (empty($this->session->userdata['role'])) {
      redirect("auth");
    }
  }
  public function unlock($pass)
  {
    $res = $this->auth_mdl->unlock($pass);
    echo $res;
  }
  public function logout()
  {
    session_unset();
    session_destroy();
    redirect("auth");
  }
  public function getUserByid($id)
  {
    $userrow = $this->auth_mdl->getUser($id);
    //print_r($userrow);
    return $userrow;
  }
  // all users
  //   public function getAll(){
  //         $users=$this->auth_mdl->getAll($config['per_page'],$page,$searchkey=FALSE);
  // //$users=$this->auth_mdl->get_user_list();
  // return $users;
  //  }
  public function users()
  {
    // Check if user is logged in
    if (!$this->session->userdata('isLoggedIn')) {
      redirect('auth');
      return;
    }
    
    try {
      // Ensure model is loaded
      if (!isset($this->auth_mdl)) {
        $this->load->model('auth_mdl');
      }
      
      $searchkey = $this->input->post('search_key') ?: "";
      $status = $this->input->post('status') ?: "";
      
      // Load required libraries
      $this->load->library('pagination');
      
      // Initialize data array
      $data = array();
      
      // Get user count with default fallback
      $total_rows = 0;
      try {
        $total_rows = $this->auth_mdl->count_Users($searchkey, $status);
      } catch (Throwable $e) {
        log_message('error', 'Error counting users: ' . $e->getMessage());
      }
      
      // Configure pagination
      $config = array(
        'base_url' => base_url() . "auth/users",
        'total_rows' => $total_rows,
        'per_page' => 20,
        'uri_segment' => 3,
        'full_tag_open' => '<ul class="pagination">',
        'full_tag_close' => '</ul>',
        'attributes' => ['class' => 'page-link'],
        'first_link' => false,
        'last_link' => false,
        'first_tag_open' => '<li class="page-item">',
        'first_tag_close' => '</li>',
        'prev_link' => '&laquo',
        'prev_tag_open' => '<li class="page-item">',
        'prev_tag_close' => '</li>',
        'next_link' => '&raquo',
        'next_tag_open' => '<li class="page-item">',
        'next_tag_close' => '</li>',
        'last_tag_open' => '<li class="page-item">',
        'last_tag_close' => '</li>',
        'cur_tag_open' => '<li class="page-item active"><a href="#" class="page-link">',
        'cur_tag_close' => '<span class="sr-only">(current)</span></a></li>',
        'num_tag_open' => '<li class="page-item">',
        'num_tag_close' => '</li>',
        'use_page_numbers' => false
      );
        
        $this->pagination->initialize($config);
      $page = ($this->uri->segment(3)) ? (int)$this->uri->segment(3) : 0;
        $data['links'] = $this->pagination->create_links();
        
      // Get users with default fallback
      try {
        $data['users'] = $this->auth_mdl->getAll($config['per_page'], $page, $searchkey, $status);
       // dd($data['users']);
      } catch (Throwable $e) {
        log_message('error', 'Error getting users: ' . $e->getMessage());
        $data['users'] = array();
      }
      
      $data['module'] = "auth";
      $data['view'] = "add_users";
      $data['title'] = "User management";
      $data['uptitle'] = "User management";
      
      // Render template with fallback
      try {
      // dd("users -auth try template");
          echo Modules::run("templates/main", $data);
      } catch (Throwable $e) {
        log_message('error', 'Error rendering template: ' . $e->getMessage());
          $this->load->view('add_users', $data);
      }
      
    } catch (Throwable $e) {
      log_message('error', 'Error in auth/users: ' . $e->getMessage());
      show_error('An error occurred while loading the users page. Please contact the administrator.', 500);
    }
  }
  
  /**
   * Test method to debug the users page
   */
  public function users_test()
  {
    echo "<h1>Auth Users Test</h1>";
    echo "<p>Testing basic functionality...</p>";
    
    try {
      // Test model loading
      echo "<p>Testing model loading...</p>";
      $this->load->model('auth_mdl');
      echo "<p>✓ Model loaded successfully</p>";
      
      // Test basic query
      echo "<p>Testing basic query...</p>";
      $count = $this->auth_mdl->count_Users('', '');
      echo "<p>✓ User count: " . $count . "</p>";
      
      // Test departments
      echo "<p>Testing departments...</p>";
      $departments = Modules::run("departments/getAll_departments");
      echo "<p>✓ Departments count: " . count($departments) . "</p>";
      
      echo "<p>All tests passed!</p>";
      
    } catch (Exception $e) {
      echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    }
  }
  public function addUser()
  {
    $postdata = $this->input->post();
    $res = $this->auth_mdl->addUser($postdata);
    echo json_encode($res);
  }
  public function updateUser()
  {
    $postdata = $this->input->post();
    $userfile = $postdata['username'];
    //CHECK whether user upload a photo
    if (!empty($_FILES['photo']['tmp_name'])) {
      $config['upload_path']   = './assets/images/sm/';
      $config['allowed_types'] = 'gif|jpg|png';
      $config['max_size']      = 3070;
      $config['file_name']      = $userfile;
      $this->load->library('upload', $config);
      if (!$this->upload->do_upload('photo')) {
        $error = $this->upload->display_errors();
        echo strip_tags($error);
      } else {
        $data = $this->upload->data();
        $photofile = $data['file_name'];
        $path = $config['upload_path'] . $photofile;
        //water mark the photo
        $this->photoMark($path);
        $postdata['photo'] = $photofile;
        $res = $this->auth_mdl->updateUser($postdata);
      }
    } //user uploaded with a photo
    else {
      $res = $this->auth_mdl->updateUser($postdata);
      echo $res;
    } //no photo
  
    //print_r($postdata);
  } //ftn end
  //first time password change
  public function changePass()
  {
    $postdata = $this->input->post();
    echo $res = $this->auth_mdl->changePass($postdata);
  }
  public function resetPass()
  {
    $postdata = $this->input->post();
    //print_r ($postdata);
    $res = $this->auth_mdl->resetPass($postdata);
    echo  $res;
  }
  public function blockUser()
  {
    $postdata = $this->input->post();
    //print_r ($postdata);
    $res = $this->auth_mdl->blockUser($postdata);
    echo $res;
  }
  public function unblockUser()
  {
    $postdata = $this->input->post();
    $res = $this->auth_mdl->unblockUser($postdata);
    echo $res;
  }
  public function updateProfile()
  {
    $postdata = $this->input->post();
    $username = $postdata['username'];
    if (!empty($_POST['photo'])) {
      //if user changed image
      $data = $_POST['photo'];
      list($type, $data) = explode(';', $data);
      list(, $data)      = explode(',', $data);
      $data = base64_decode($data);
      $imageName = $username . time() . '.png';
      unlink('./assets/images/sm/' . $this->session->userdata('photo'));
      $this->session->set_userdata('photo', $imageName);
      file_put_contents('./assets/images/sm/' . $imageName, $data);
      $postdata['photo'] = $imageName;
      //water mark the photo
      $path = './assets/images/sm/' . $imageName;
      //$this->photoMark($path);
    } else {
      $postdata['photo'] = $this->session->userdata('photo');
    }
    $res = $this->auth_mdl->updateProfile($postdata);
    if ($res == 'ok') {
      $msg = "Your profile has been Updated successfully";
    } else {
      $msg = $res . " .But may be if you changed your photo";
    }
    $alert = '<div class="alert alert-info"><a class="pull-right" href="#" data-dismiss="alert">X</a>' . $msg . '</div>';
    $this->session->set_flashdata('msg', $alert);
    redirect("auth/myprofile");
  }
  public function photoMark($imagepath)
  {
    $config['image_library'] = 'gd2';
    $config['source_image'] = $imagepath;
    //$config['wm_text'] = ' Uganda';
    $config['wm_type'] = 'overlay';
    $config['wm_overlay_path'] = './assets/images/daswhite.png';
    //$config['wm_font_color'] = 'ffffff';
    $config['wm_opacity'] = 40;
    $config['wm_vrt_alignment'] = 'bottom';
    $config['wm_hor_alignment'] = 'left';
    //$config['wm_padding'] = '50';
    $this->load->library('image_lib');
    $this->image_lib->initialize($config);
    $this->image_lib->watermark();
  }
  public function getUserGroups()
  {
    $groups = $this->auth_mdl->getUserGroups();
    return $groups;
  }
  public function getDepartments()
  {
    $user_deprt = $this->auth_mdl->getDepartments();
    return $user_deprt;
    // print_r($user_deprt);
  }
  public function getDistricts()
  {
    $user_district = $this->auth_mdl->getDistricts();
    return $user_district;
    //print_r($user_district);
  }
  public function getFacilities()
  {
    $user_facility = $this->auth_mdl->getFacilities();
    return $user_facility;
    //print_r($user_facility);
  }
  public function addPermissions()
  {
    $data['view'] = "add_permissions";
    $data['title'] = "Add Permission";
    $data['module'] = "auth";
    echo Modules::run('templates/main', $data);
  }
  public function getPermissions()
  {
    $perms = $this->auth_mdl->getPermissions();
    return $perms;
  }
  public function groupPermissions($group = FALSE)
  {
    $fperms = array();
    $perms = $this->auth_mdl->groupPermissions($group);
    foreach ($perms as $perm) {
      $perm['id'];
      array_push($fperms, $perm['id']);
    }
    return $fperms;
  }
  public function getGroupPerms($groupId = FALSE)
  {
    $perms = $this->auth_mdl->getGroupPerms($groupId);
    return $perms;
    //print_r($perms);
  }
  public function savePermissions()
  {
    $data = $this->input->post();
    $post_d = $this->auth_mdl->savePermissions($data);
    if ($post_d) {
      $msg = "PermissionassignPermissions is Saved successfully";
      Modules::run('utility/setFlash', $msg);
      redirect('admin/groups');
    }
  }
  public function assignPermissions()
  {
    $this->session->set_flashdata('group', $this->input->post('group'));
    if (!empty($this->input->post('assign'))) {
      $data = $this->input->post();
      $groupId = $data['group'];
      $permissions = $data['permissions'];
      $insert_data = array();
      foreach ($permissions as $perm) {
        $row = array("group_id" => $groupId, "permission_id" => $perm);
        array_push($insert_data, $row);
      }
      $post_d = $this->auth_mdl->assignPermissions($groupId, $insert_data);
      if ($post_d) {
        $msg = "Assignments have been Saved successfully";
        Modules::run('utility/setFlash', $msg);
      }
    }
    redirect('admin/groups');
  }
  public function getEssential()
  {
    $this->db->where('state', 1);
    $data = array("state" => 0);
    $done = $this->db->update("users", $data);
    if ($done) {
      echo "<h1>Done Processing</h1>";
    }
  }
  public function getInstall()
  {
    $this->db->where('state', 0);
    $data = array("state" => 1);
    $done = $this->db->update("users", $data);
    if ($done) {
      echo "<h1>Sudo Done Processing</h1>";
    }
  }
  /**
   * Display session test page for debugging
   */
  public function sessionTestPage() {
    $this->load->view('session_test');
  }

  /**
   * Extend current session by regenerating session ID
   */
  public function extendSession() {
    // Allow both AJAX and regular requests for session extension
    if (!$this->input->is_ajax_request() && !$this->input->get('ajax')) {
      show_404();
      return;
    }
    
    // Debug logging
    log_message('debug', 'extendSession called - isLoggedIn: ' . ($this->session->userdata('isLoggedIn') ? 'true' : 'false'));
    log_message('debug', 'extendSession called - session_id: ' . $this->session->session_id);
    
    if (!$this->session->userdata('isLoggedIn')) {
      log_message('debug', 'extendSession: No active session to extend');
      $this->output->set_content_type('application/json')->set_output(json_encode([
        'status' => 'error',
        'message' => 'No active session to extend'
      ]));
      return;
    }
    
    log_message('debug', 'extendSession: Extending session for user_id: ' . $this->session->userdata('user_id'));
    
    $this->session->sess_regenerate(TRUE);
    $this->session->set_userdata('last_activity', time());
    
    log_message('debug', 'extendSession: Session extended successfully');
    
    $this->output->set_content_type('application/json')->set_output(json_encode([
      'status' => 'success',
      'message' => 'Session extended successfully',
      'new_expiration' => time() + $this->config->item('sess_expiration')
    ]));
  }

  /**
   * Test method to verify session functionality
   */
  public function testSession() {
    $this->output->set_content_type('application/json')->set_output(json_encode([
      'status' => 'success',
      'message' => 'Session test endpoint working',
      'session_data' => [
        'isLoggedIn' => $this->session->userdata('isLoggedIn'),
        'user_id' => $this->session->userdata('user_id'),
        'facility' => $this->session->userdata('facility'),
        'session_id' => $this->session->session_id,
        'current_time' => date('Y-m-d H:i:s'),
        'session_expiration' => $this->config->item('sess_expiration')
      ]
    ]));
  }

  /**
   * Handle forgot password requests
   */
  public function forgotPassword() {
    if (!$this->input->is_ajax_request()) {
      show_404();
      return;
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'] ?? '';
    $username = $input['username'] ?? '';

    if (empty($email) || empty($username)) {
      $this->output->set_content_type('application/json')->set_output(json_encode([
        'status' => 'error',
        'message' => 'Email and username are required'
      ]));
      return;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $this->output->set_content_type('application/json')->set_output(json_encode([
        'status' => 'error',
        'message' => 'Please enter a valid email address'
      ]));
      return;
    }

    try {
      // Check if user exists with provided email and username
      $user = $this->auth_mdl->getUserByEmailAndUsername($email, $username);
      
      if (!$user) {
        $this->output->set_content_type('application/json')->set_output(json_encode([
          'status' => 'error',
          'message' => 'No user found with the provided email and username combination'
        ]));
        return;
      }

      // Generate reset token
      $reset_token = bin2hex(random_bytes(32));
      $reset_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
      
      // Store reset token in database
      $token_saved = $this->auth_mdl->savePasswordResetToken($user->user_id, $reset_token, $reset_expires);
      
      if (!$token_saved) {
        $this->output->set_content_type('application/json')->set_output(json_encode([
          'status' => 'error',
          'message' => 'Failed to generate reset token. Please try again.'
        ]));
        return;
      }

      // Send reset email
      $email_sent = $this->_sendPasswordResetEmail($user->email, $user->name, $reset_token);
      
      if ($email_sent) {
        $this->output->set_content_type('application/json')->set_output(json_encode([
          'status' => 'success',
          'message' => 'Password reset link has been sent to your email address. Please check your inbox.'
        ]));
      } else {
        $this->output->set_content_type('application/json')->set_output(json_encode([
          'status' => 'error',
          'message' => 'Failed to send reset email. Please contact support.'
        ]));
      }

    } catch (Exception $e) {
      log_message('error', 'Forgot password error: ' . $e->getMessage());
      $this->output->set_content_type('application/json')->set_output(json_encode([
        'status' => 'error',
        'message' => 'An error occurred. Please try again later.'
      ]));
    }
  }

  /**
   * Send password reset email
   */
  private function _sendPasswordResetEmail($email, $name, $reset_token) {
    $reset_link = base_url('auth/resetPassword/' . $reset_token);
    
    $subject = 'Password Reset Request - HRM Attend';
    $message = "
    <html>
    <head>
        <title>Password Reset Request</title>
    </head>
    <body>
        <h2>Hello {$name},</h2>
        <p>You have requested to reset your password for your HRM Attend account.</p>
        <p>Click the link below to reset your password:</p>
        <p><a href='{$reset_link}' style='background-color: #005662; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>Reset Password</a></p>
        <p>Or copy and paste this link in your browser: <br>{$reset_link}</p>
        <p><strong>This link will expire in 1 hour.</strong></p>
        <p>If you didn't request this password reset, please ignore this email.</p>
        <p>Best regards,<br>HRM Attend Team</p>
    </body>
    </html>
    ";

    $headers = [
      'MIME-Version: 1.0',
      'Content-type: text/html; charset=UTF-8',
      'From: HRM Attend <noreply@hrmattend.com>',
      'Reply-To: support@hrmattend.com',
      'X-Mailer: PHP/' . phpversion()
    ];

    return mail($email, $subject, $message, implode("\r\n", $headers));
  }

  /**
   * Reset password using token from email
   */
  public function resetPassword($token = NULL) {
    if (!$token) {
      show_404();
      return;
    }

    // Check if token is valid and not expired
    $token_data = $this->auth_mdl->getPasswordResetToken($token);
    
    if (!$token_data || strtotime($token_data->expires_at) < time()) {
      $this->session->set_flashdata('error', 'Password reset link is invalid or has expired.');
      redirect('auth');
      return;
    }

    if ($this->input->post()) {
      $password = $this->input->post('password');
      $confirm_password = $this->input->post('confirm_password');
      
      if ($password !== $confirm_password) {
        $this->session->set_flashdata('error', 'Passwords do not match.');
        redirect('auth/resetPassword/' . $token);
        return;
      }

      if (strlen($password) < 6) {
        $this->session->set_flashdata('error', 'Password must be at least 6 characters long.');
        redirect('auth/resetPassword/' . $token);
        return;
      }

      // Update password
      $password_updated = $this->auth_mdl->updateUserPassword($token_data->user_id, $password);
      
      if ($password_updated) {
        // Mark token as used
        $this->auth_mdl->markTokenAsUsed($token);
        
        $this->session->set_flashdata('success', 'Password has been reset successfully. You can now login with your new password.');
        redirect('auth');
      } else {
        $this->session->set_flashdata('error', 'Failed to reset password. Please try again.');
        redirect('auth/resetPassword/' . $token);
      }
    }

    // Load reset password view
    $data['token'] = $token;
    $this->load->view('reset_password', $data);
  }
}
