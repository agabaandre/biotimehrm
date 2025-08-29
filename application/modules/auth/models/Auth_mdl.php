<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Auth_mdl extends CI_Model
{
	protected $table;
	protected $password;

	public function __construct()
	{
		parent::__construct();
		$this->table = "user";
		$this->password = Modules::run('svariables/getSettings')->default_password;
	}
	public function loginChecker($postdata)
	{
		    $username = $postdata['username'];
			//login using username
			$this->db->where("username", $username);
			$this->db->or_where("email", $username);
			$this->db->where("status", 1);
			$this->db->join('user_groups', 'user_groups.group_id=user.role');
			$qry = $this->db->get($this->table);
			$rows = $qry->num_rows();

	
			if ($rows > 0) {
				$person = $qry->row();
				if ($this->validate_password($postdata['password'], $person->password)){
				return $person;
				}
				else {
				return 0;

				}
		
			} 
			
			else {
			//check wther person id exists
				if ($this->checkNewUser($username)){ //check if new user was added
					return "New";
				} else {
					return FALSE;
				}
			}
		} 

    public function validate_password($post_password,$dbpassword){
	 $auth = ($this->argonhash->check($post_password, $dbpassword));
		if ($auth) {
			return TRUE;
		}
		else{
			return FALSE;
		}
		
	}
	
	
	public function checkNewUser($personid)
	{
		$newpid = 'person|' . trim($personid);
		$this->db->select('ihris_pid,surname,firstname,facility_id,department_id,department,facility,district,district_id');
		$this->db->where('ihris_pid', $newpid);
		$this->db->or_where('ipps', $personid);
		$query = $this->db->get('ihrisdata');
		$rows = $query->num_rows();
		if ($rows >= 1) {
			$userRow = $query->row();
			$newUser = array(
				"username" => $personid,
				"name" => $userRow->firstname . " " . $userRow->surname,
				"facility_id" => $userRow->facility_id,
				"department_id" => $userRow->department_id,
				"department" => $userRow->department,
				"facility" => $userRow->facility,
				"ihris_pid" => $userRow->ihris_pid,
				"district" => $userRow->district,
				"district_id" => $userRow->district_id,
				"password" => $this->password,
				"role" => "17",
				"status" => "0"
			);
			 $this->db->insert($this->table, $newUser);
			return TRUE;
		} else {
			return FALSE;
		}
	}
	public function unlock()
	{
		$uid = $this->session->userdata['user_id'];
		$username = $this->session->userdata['username'];
		$this->db->where("user_id", $uid);
		$this->db->where("username", $username);
		$qry = $this->db->get($this->table);
		$rows = $qry->num_rows();
		if ($rows == 1) {
			return "ok";
		}
	}

	/**
	 * Check if user has logged in recently (within 2 hours)
	 */
	public function checkRecentLogin($username) {
		$this->db->select('username, name, email, last_login');
		$this->db->from($this->table);
		$this->db->where('username', $username);
		$this->db->where('last_login >', date('Y-m-d H:i:s', strtotime('-2 hours')));
		$query = $this->db->get();
		return $query->row();
	}

	/**
	 * Get user by email and username combination
	 */
	public function getUserByEmailAndUsername($email, $username) {
		$this->db->select('user_id, username, name, email');
		$this->db->from($this->table);
		$this->db->where('email', $email);
		$this->db->where('username', $username);
		$this->db->where('status', 1);
		$query = $this->db->get();
		return $query->row();
	}

	/**
	 * Save password reset token
	 */
	public function savePasswordResetToken($user_id, $token, $expires) {
		// First, clear any existing tokens for this user
		$this->db->where('user_id', $user_id);
		$this->db->delete('password_reset_tokens');
		
		// Insert new token
		$data = [
			'user_id' => $user_id,
			'token' => $token,
			'expires_at' => $expires,
			'created_at' => date('Y-m-d H:i:s')
		];
		
		return $this->db->insert('password_reset_tokens', $data);
	}

	/**
	 * Get password reset token
	 */
	public function getPasswordResetToken($token) {
		$this->db->select('*');
		$this->db->from('password_reset_tokens');
		$this->db->where('token', $token);
		$this->db->where('used_at IS NULL');
		$this->db->where('expires_at >', date('Y-m-d H:i:s'));
		$query = $this->db->get();
		return $query->row();
	}

	/**
	 * Update user password
	 */
	public function updateUserPassword($user_id, $password) {
		$hashed_password = $this->argonhash->hash($password);
		$this->db->where('user_id', $user_id);
		return $this->db->update($this->table, ['password' => $hashed_password]);
	}

	/**
	 * Mark token as used
	 */
	public function markTokenAsUsed($token) {
		$this->db->where('token', $token);
		return $this->db->update('password_reset_tokens', ['used_at' => date('Y-m-d H:i:s')]);
	}

	/**
	 * Update last login timestamp
	 */
	public function updateLastLogin($user_id) {
		$this->db->where('user_id', $user_id);
		return $this->db->update($this->table, ['last_login' => date('Y-m-d H:i:s')]);
	}

	public function getUser($id)
	{
		$this->db->where("user_id", $id);
		$qry = $this->db->get($this->table);
		return $qry->row();
	}
	public function getAll($start, $limit, $key,$status)
	{
		$this->db->select('user.*, user_groups.group_name');
		$this->db->from($this->table);
		
		if (!empty($status)) {
			$this->db->where("status", "$status");
		}
		if (!empty($key)) {
			$this->db->group_start();
			$this->db->like("username", "$key", "both");
			$this->db->or_like("name", "$key", "both");
			$this->db->group_end();
		}
		
		$this->db->join('user_groups', 'user_groups.group_id=user.role', 'left');
		$this->db->limit($limit, $start);
		$this->db->order_by('user.username', 'ASC');
		
		$qry = $this->db->get();
		return $qry->result();
	}
	public function count_Users($key,$status)
	{
		if (!empty($status)) {
			$this->db->where("status", "$status");
		}
		if (!empty($key)) {
			$this->db->like("username", "$key", "both");
			$this->db->or_like("name", "$key", "both");
		}
		$qry = $this->db->get($this->table);
		return $qry->num_rows();
	}
	public function addUser($postdata)
	{

		$distid = $postdata['district_id'];
		$facilities = $postdata['facility_id'];
		$facids = $postdata['facility_id'][0];
				$facd = explode("_", $facids);
				//dd($facd);
		$facid = $facd[0];
		$facility = $facd[1];

		//get district
		$distname = $this->db->query("SELECT distinct district from ihrisdata where district_id='$distid'");
		$distn = $distname->row()->district;
		//get facility
		// $facname = $this->db->query("SELECT distinct facility from ihrisdata where facility_id='$facid'");
		// $facn = $facname->row()->facility;

		$insert = array(
			'username' => $postdata['username'],
			'name' => $postdata['name'],
			'email' => $postdata['email'],
			'password' => $this->password,
			'facility_id' => "$facid",
			'facility' => "$facility",
			"role" => 21, // Role 21 for facility incharges
			'department' => $postdata['department_id'],
			'district_id' => "$distid", // Use district ID, not name
			'district' => "$distn",
			'status' => 1
		);

		if (isset($postdata['is_incharge']) && $postdata['is_incharge'] == 1) {
			$ihris_pid = $postdata['ihris_pid'];
			$this->db->query("UPDATE `ihrisdata` SET `is_incharge` = '1' WHERE `ihrisdata`.`ihris_pid` = '$ihris_pid'");
		}
 
		$qry = $this->db->insert($this->table, $insert);
		$userid = $this->db->insert_id();
		$this->user_facilities($facilities, $userid);
		$rows = $this->db->affected_rows();
		if ($rows > 0) {
			return "User has been Added";
		} else {
			return "Operation failed";
		}
	}
	public function user_facilities($facilities, $userid)
	{
		//get district
	

		for ($i = 1; $i < count($facilities); $i++) :

			$fac_id = explode('_',$facilities[$i]);
			$facid = $fac_id[0];
			$facname = $this->db->query("SELECT distinct facility from ihrisdata where facility_id='$facid'");
			$facn = $facname->row()->facility;
			$insert = array(
				"user_id" => $userid,
				"facility_id" => "$facid",
				"facility" => "$facn",
			

			);

			$this->db->insert("user_facilities", $insert);

		//logic for mutiple users

		endfor;
	}


	public function update_user_facilities($facilities, $userid)
	{
		//get district

              if($userid){
				$this->db->query("DELETE from user_facilities WHERE user_id=$userid");
			  }
		for ($i = 1; $i < count($facilities); $i++):

			$fac_id = explode('_', $facilities[$i]);
			$facid = $fac_id[0];
			$facname = $this->db->query("SELECT distinct facility from ihrisdata where facility_id='$facid'");
			$facn = $facname->row()->facility;
			$insert = array(
				"user_id" => $userid,
				"facility_id" => "$facid",
				"facility" => "$facn",


			);

			$this->db->replace("user_facilities", $insert);

			//logic for mutiple users

		endfor;
	}
	// update user's details
	public function updateUser($postdata)
	{
		$distid = $postdata['district_id'];
		$facilities = $postdata['facility_id'];
		$facdata = $postdata['facility_id'][0];
		$depid = $postdata['user_id'];
		//get district
		$distname = $this->db->query("SELECT distinct district from ihrisdata where district_id='$distid'");
		$distn = $distname->row()->district;
		//get facility
		$facd = explode("_", $facdata);
		$fac_id = $facd[0];
		$facility = $facd[1];
		$facname = $this->db->query("SELECT distinct facility from ihrisdata where facility_id='$fac_id'");
		$facn = $facname->row()->facility;
		$savedata = array(
			"name" => $postdata['name'],
			"district" => $distn,
			"district_id" => $postdata['district_id'],
			"facility_id" => $fac_id,
			"facility" => $facn,
			'email' => $postdata['email'],
			"department" => $postdata['department_id'],
			"department_id" => $postdata['department_id'],
			"role" => $postdata['role']
		);
		$uid = $postdata['user_id'];
		$this->db->where('user_id', $uid);
		$query = $this->db->update($this->table, $savedata);
		if ($query) {
			$this->update_user_facilities($facilities, $uid);
			return "User details updated";
		} else {
			return "No changes made";
		}
	}
	// change password
	public function changePass($postdata)
	{

		$oldpass= $postdata['oldpass'];
		$newpass = $this->argonhash->make($postdata['newpass']);
		$user = $this->session->get_userdata();
		$uid = $user['user_id'];
		$this->db->select('password');
		$this->db->where('user_id', $uid);
		$qry = $this->db->get($this->table);
		$user = $qry->row();
		if ($this->argonhash->check($oldpass, $user->password)){
			// change the password
			$data = array("password" => $newpass, "isChanged" => 1);
			$this->db->where('user_id', $uid);
			$query = $this->db->update($this->table, $data);

			if ($query) {
				$_SESSION['changed'] = 1;
				return "Password Change Successful";
			} else {
				return "Operation failed, try again";
			}
		} else {
			return "The old password you provided is wrong";
		}
	}
	public function updateProfile($postdata)
	{
		$uid = $postdata['user_id'];
		$this->db->where('user_id', $uid);
		$done = $this->db->update($this->table, $postdata);

		if ($done) {
			return "Update Successful";
		} else {
			return "Update Failed";
		}
	}
	//reset user's password.................
	public function resetPass($postdata)
	{
		$uid = $postdata['user_id'];
		$password = $this->password;
		$data = array("password" => $password, "isChanged" => 0);
		$this->db->where('user_id', $uid);
		$done = $this->db->update($this->table, $data);

		if ($done) {
			return "User's password has been reset";
		} else {
			return "Failed, Try Again";
		}
	}
	//block
	public function blockUser($postdata)
	{
		$uid = $postdata['user_id'];
		$data = array("status" => 0);
		$this->db->where('user_id', $uid);
		$done = $this->db->update($this->table, $data);

		if ($done) {
			return "User has been blocked";
		} else {
			return "Failed, Try Again";
		}
	}
	//unblock user
	public function unblockUser($postdata)
	{
		$uid = $postdata['user_id'];
		$data = array("status" => 1);
		$this->db->where('user_id', $uid);
		$done = $this->db->update($this->table, $data);
		if ($done) {
			return "User has been Unblocked";
		} else {
			return "Failed, Try Again";
		}
	}
	public function getUserGroups()
	{
		$qry = $this->db->get("user_groups");
		$groups = $qry->result();
		return $groups;
	}
	public function getDepartments()
	{
		$this->db->select('department,department_id');
		$this->db->distinct('department');
		//$qry=$this->db->get('ihrisdata');
		$qry = $this->db->get('ihrisdata');
		return $qry->result();
	}
	public function getDistricts()
	{
		$this->db->select('district,district_id');
		$this->db->distinct('district');
		$qry = $this->db->get('ihrisdata');
		return $qry->result();
	}
	public function getFacilities()
	{
		$this->db->select('facility_id,facility');
		$this->db->distinct('facility_id');
		$qry = $this->db->get('ihrisdata');
		return $qry->result();
	}
	public function getPermissions()
	{
		$query = $this->db->get("permissions");
		$perms = $query->result();
		return $perms;
	}
	public function groupPermissions($group)
	{
		$query = $this->db->query("SELECT permissions.id, name, definition,group_id,group_permissions.permission_id from permissions,group_permissions where permissions.id=group_permissions.permission_id and group_id='$group'");
		$perms = $query->result_array();
		return $perms;
	}
	public function getGroupPerms($groupId = FALSE)
	{
		$this->db->where('group_id', $groupId);
		$this->db->join('permissions', 'permissions.id=group_permissions.permission_id');
		$qry = $this->db->get('group_permissions');
		return $qry->result();
	}
	public function getUserPerms($groupId)
	{
		$this->db->where('group_id', $groupId);
		$qry = $this->db->get('group_permissions');
		$permissions = $qry->result();
		$perms = array();
		foreach ($permissions as $perm) {
			array_push($perms, $perm->permission_id);
		}
		return $perms;
	}
	public function savePermissions($data)
	{
		$data['definition'] = ucwords($data['definition']);
		$data['name'] = strtolower(str_replace(" ", "", $data['name']));
		$save = $this->db->insert('permissions', $data);
		return $save;
	}
	public function assignPermissions($groupId, $data)
	{
		if (count($data) > 0) {
			$this->db->where('group_id', $groupId);
			$this->db->delete('group_permissions');
			$save = $this->db->insert_batch('group_permissions', $data);
			return $save;
		}
		return false;
	}
}
