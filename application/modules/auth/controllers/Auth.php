<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Auth extends MX_Controller
{
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
  public function login()
  {
    $postdata = $this->input->post();
    $person = $this->auth_mdl->loginChecker($postdata);
    // print_r($person);
    // exit;
    if (!empty($person->user_id)) {
      $user_group = $person->role;
      $userdata = array(
        "names" => $person->name,
        "user_id" => $person->user_id,
        "ihris_pid" => $person->ihris_pid,
        "username" => $person->username,
        "role" => $person->group_name,
        "state" => $person->status,
        "dateChanged" => $person->changed,
        "changed" => $person->isChanged,
        "isLoggedIn" => true,
        "facility" => $person->facility_id,
        "facility_name" => $person->facility,
        "department" => $person->department,
        "permissions" => $this->auth_mdl->getUserPerms($user_group),
        "department_id" => $person->department_id,
        "division" => $person->division,
        "unit" => $person->unit,
        "district_id" => $person->district_id,
        "district" => $person->district,
        "year" => date('Y'),
        "month" => date('m'),
        "date_from" => date("Y-m-d", strtotime("-1 month")),
        "date_to" => date('Y-m-d')
      );
      //print_r($userdata);
      $this->checkerUser($userdata);
    } else {
      if ($person == "new") {
        $msg = $this->session->set_flashdata('msg', "First time access detected, Contact the Admin for Activation");
      } else {
        $msg = $this->session->set_flashdata('msg', "Login Failed, Wrong credentials");
      }
      redirect("auth");
    }
  }
  public function checkerUser($userdata)
  {
    // print_r("USer".$userdata);
    if (!$userdata['isLoggedIn']) {
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
    $searchkey = $this->input->post('search_key');
    if (empty($searchkey)) {
      $searchkey = "";
    }
    $this->load->library('pagination');
    $config = array();
    $config['base_url'] = base_url() . "auth/users";
    $config['total_rows'] = $this->auth_mdl->count_Users($searchkey);
    $config['per_page'] = 20; //records per page
    $config['uri_segment'] = 3; //segment in url  
    //pagination links styling
    $config['full_tag_open'] = '<ul class="pagination">';
    $config['full_tag_close'] = '</ul>';
    $config['attributes'] = ['class' => 'page-link'];
    $config['first_link'] = false;
    $config['last_link'] = false;
    $config['first_tag_open'] = '<li class="page-item">';
    $config['first_tag_close'] = '</li>';
    $config['prev_link'] = '&laquo';
    $config['prev_tag_open'] = '<li class="page-item">';
    $config['prev_tag_close'] = '</li>';
    $config['next_link'] = '&raquo';
    $config['next_tag_open'] = '<li class="page-item">';
    $config['next_tag_close'] = '</li>';
    $config['last_tag_open'] = '<li class="page-item">';
    $config['last_tag_close'] = '</li>';
    $config['cur_tag_open'] = '<li class="page-item active"><a href="#" class="page-link">';
    $config['cur_tag_close'] = '<span class="sr-only">(current)</span></a></li>';
    $config['num_tag_open'] = '<li class="page-item">';
    $config['num_tag_close'] = '</li>';
    $config['use_page_numbers'] = false;
    $this->pagination->initialize($config);
    $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0; //default starting point for limits 
    $data['links'] = $this->pagination->create_links();
    $data['users'] = $this->auth_mdl->getAll($config['per_page'], $page, $searchkey);
    $data['module'] = "auth";
    $data['view'] = "add_users";
    $data['title'] = "User management";
    $data['uptitle'] = "User management";
    echo Modules::run("templates/main", $data);
  }
  public function addUser()
  {
    $postdata = $this->input->post();
    $res = $this->auth_mdl->addUser($postdata);
    echo $res;
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
    } //no photo
    //echo $res;
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
}
