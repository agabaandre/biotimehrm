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
		try {
			$settings = Modules::run('svariables/getSettings');
			$this->password = (!empty($settings) && isset($settings->default_password)) ? $settings->default_password : 'rKET2XW5Xvnp2ds';
		} catch (Exception $e) {
			$this->password = 'rKET2XW5Xvnp2ds';
			log_message('error', 'Failed to get default password from settings: ' . $e->getMessage());
		}
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
		if (!empty($status)) {
			$this->db->where("status", "$status");
		}
		if (!empty($key)) {
			$this->db->like("username", "$key", "both");
			$this->db->or_like("name", "$key", "both");
		}
		
		$this->db->limit($start, $limit);
		$this->db->join('user_groups', 'user_groups.group_id=user.role', 'left');
		$qry = $this->db->get($this->table);
		return $qry->result();
	}
	public function count_Users($key,$status)
	{
		// Reset query builder to avoid conflicts
		$this->db->reset_query();
		
		$this->db->from($this->table);
		
		if (!empty($status)) {
			$this->db->where("status", $status);
		}
		if (!empty($key)) {
			$this->db->group_start();
			$this->db->like("username", $key, "both");
			$this->db->or_like("name", $key, "both");
			$this->db->group_end();
		}
		
		$qry = $this->db->get();
		
		// Log query for debugging
		log_message('debug', 'count_Users Query: ' . $this->db->last_query());
		log_message('debug', 'count_Users Results: ' . $qry->num_rows() . ' rows');
		
		return $qry->num_rows();
	}
	public function addUser($postdata)
	{
		$distid = isset($postdata['district_id']) ? $postdata['district_id'] : '';
		$facilities = $this->normalizeFacilitySelection(isset($postdata['facility_id']) ? $postdata['facility_id'] : []);
		if (empty($facilities)) {
			return 'Please select at least one facility';
		}

		$facids = $facilities[0];
		$facd = explode('_', (string) $facids, 2);
		$facid = isset($facd[0]) ? trim($facd[0]) : '';
		$facility = isset($facd[1]) ? $facd[1] : '';

		$department_id = $this->normalizeDepartmentId(isset($postdata['department_id']) ? $postdata['department_id'] : '');

		//get district
		$distn = $this->lookupDistrictName($distid);

		$insert = array(
			'username' => $postdata['username'],
			'name' => $postdata['name'],
			'email' => $postdata['email'],
			'password' => $this->password,
			'facility_id' => "$facid",
			'facility' => "$facility",
			"role" => 21, // Role 21 for facility incharges
			'department' => $department_id,
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
		$facilities = $this->normalizeFacilitySelection($facilities);

		for ($i = 1; $i < count($facilities); $i++) :

			$fac_id = explode('_',$facilities[$i]);
			$facid = $fac_id[0];
			$facn = $this->lookupFacilityName($facid, isset($fac_id[1]) ? $fac_id[1] : '');
			$insert = array(
				"user_id" => $userid,
				"facility_id" => "$facid",
				"facility" => "$facn",
			

			);

			$this->db->insert("user_facilities", $insert);

		//logic for mutiple users

		endfor;
	}


	public function addInchargeUserFromEmployee($ihris_pid)
	{
		$ihris_pid = trim((string) $ihris_pid);
		if ($ihris_pid === '') {
			return ['ok' => false, 'message' => 'Missing employee ID'];
		}

		$employee = $this->db->get_where('ihrisdata', ['ihris_pid' => $ihris_pid], 1)->row();
		if (!$employee) {
			return ['ok' => false, 'message' => 'Employee record not found'];
		}

		if ($this->db->get_where($this->table, ['username' => $ihris_pid], 1)->row()) {
			return ['ok' => false, 'message' => 'User account already exists', 'skipped' => true];
		}

		$distid = trim((string) ($employee->district_id ?? ''));
		$distn = trim((string) ($employee->district ?? ''));
		if ($distn === '' && $distid !== '') {
			$drow = $this->db->select('name')->from('employee_districts')->where('id', $distid)->limit(1)->get()->row();
			if ($drow) {
				$distn = trim((string) $drow->name);
			}
		}

		$facid = trim((string) ($employee->facility_id ?? ''));
		$facility = trim((string) ($employee->facility ?? ''));
		$fullname = trim(preg_replace('/\s+/', ' ', trim(($employee->firstname ?? '') . ' ' . ($employee->othername ?? '') . ' ' . ($employee->surname ?? ''))));

		$insert = [
			'username'    => $ihris_pid,
			'name'        => $fullname,
			'email'       => trim((string) ($employee->email ?? '')),
			'password'    => $this->password,
			'facility_id' => $facid,
			'facility'    => $facility,
			'role'        => 21,
			'department'  => '',
			'district_id' => $distid,
			'district'    => $distn,
			'status'      => 1,
		];

		$this->db->insert($this->table, $insert);
		if ($this->db->affected_rows() <= 0) {
			return ['ok' => false, 'message' => 'Failed to create user account'];
		}

		$userid = (int) $this->db->insert_id();
		$this->user_facilities([$facid . '_' . $facility], $userid);

		$this->db->where('ihris_pid', $ihris_pid);
		$this->db->update('ihrisdata', ['is_incharge' => '1']);

		return ['ok' => true, 'message' => 'User account created'];
	}

	public function update_user_facilities($facilities, $userid)
	{
		$facilities = $this->normalizeFacilitySelection($facilities);

		if ($userid) {
			$this->db->query("DELETE from user_facilities WHERE user_id=$userid");
		}
		for ($i = 1; $i < count($facilities); $i++) :

			$fac_id = explode('_', $facilities[$i]);
			$facid = $fac_id[0];
			$facn = $this->lookupFacilityName($facid, isset($fac_id[1]) ? $fac_id[1] : '');
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
		$uid = isset($postdata['user_id']) ? (int) $postdata['user_id'] : 0;
		if ($uid <= 0) {
			return 'Invalid user';
		}

		$existing = $this->db->get_where($this->table, ['user_id' => $uid], 1)->row();

		$distid = isset($postdata['district_id']) ? $postdata['district_id'] : ($existing ? $existing->district_id : '');
		$facilities = $this->normalizeFacilitySelection(isset($postdata['facility_id']) ? $postdata['facility_id'] : []);
		$facdata = isset($facilities[0]) ? $facilities[0] : ($existing ? $existing->facility_id : '');

		$department_id = $this->normalizeDepartmentId(isset($postdata['department_id']) ? $postdata['department_id'] : '');
		if ($department_id === '' && $existing) {
			$department_id = trim((string) ($existing->department_id ?? $existing->department ?? ''));
		}

		//get district
		$distn = $this->lookupDistrictName($distid);
		if ($distn === '' && $existing) {
			$distn = trim((string) ($existing->district ?? ''));
		}
		//get facility
		$facd = explode('_', (string) $facdata, 2);
		$fac_id = isset($facd[0]) ? trim($facd[0]) : '';
		$facility = isset($facd[1]) ? $facd[1] : '';
		if ($fac_id === '' && $existing) {
			$fac_id = trim((string) ($existing->facility_id ?? ''));
		}
		$facn = $this->lookupFacilityName($fac_id, $facility !== '' ? $facility : ($existing ? ($existing->facility ?? '') : ''));
		$savedata = array(
			'name' => isset($postdata['name']) ? $postdata['name'] : ($existing ? $existing->name : ''),
			'district' => $distn,
			'district_id' => $distid,
			'facility_id' => $fac_id,
			'facility' => $facn,
			'email' => isset($postdata['email']) ? $postdata['email'] : ($existing ? $existing->email : ''),
			'department' => $department_id,
			'department_id' => $department_id,
			'role' => isset($postdata['role']) ? $postdata['role'] : ($existing ? $existing->role : null),
		);
		if (isset($postdata['photo']) && trim((string) $postdata['photo']) !== '') {
			$savedata['photo'] = trim((string) $postdata['photo']);
		}
		$this->db->where('user_id', $uid);
		$query = $this->db->update($this->table, $savedata);
		if ($query) {
			if (!empty($facilities)) {
				$this->update_user_facilities($facilities, $uid);
			}
			return 'User details updated';
		}

		return 'No changes made';
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
		$this->load->library('facility_switch_cache', null, 'fsc');
		return $this->fsc->get_districts();
	}
	public function getFacilities()
	{
		$this->load->library('facility_switch_cache', null, 'fsc');
		$data = $this->fsc->get_data();
		$out = [];
		$seen = [];
		foreach ($data['facilities_by_district'] as $rows) {
			foreach ($rows as $row) {
				$fid = isset($row['facility_id']) ? (string) $row['facility_id'] : '';
				if ($fid === '' || isset($seen[$fid])) {
					continue;
				}
				$seen[$fid] = true;
				$o = new stdClass();
				$o->facility_id = $fid;
				$o->facility = isset($row['facility']) ? (string) $row['facility'] : '';
				$out[] = $o;
			}
		}
		usort($out, function ($a, $b) {
			return strcasecmp($a->facility, $b->facility);
		});
		return $out;
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

	protected function lookupDistrictName($distid)
	{
		$distid = trim((string) $distid);
		if ($distid === '') {
			return '';
		}
		if (function_exists('is_education_deployment') && is_education_deployment()) {
			$drow = $this->db->select('name')->from('employee_districts')->where('id', $distid)->limit(1)->get()->row();
			return $drow ? trim((string) $drow->name) : '';
		}
		$q = $this->db->query(
			'SELECT DISTINCT district FROM ihrisdata WHERE district_id = ' . $this->db->escape($distid) . ' LIMIT 1'
		);
		return ($q->num_rows() > 0) ? trim((string) $q->row()->district) : '';
	}

	protected function lookupFacilityName($facid, $fallback = '')
	{
		$facid = trim((string) $facid);
		if ($facid === '') {
			return $fallback;
		}
		if (function_exists('is_education_deployment') && is_education_deployment()) {
			$frow = $this->db->select('facility')->from('employee_facility')->where('facility_id', $facid)->limit(1)->get()->row();
			return $frow ? trim((string) $frow->facility) : $fallback;
		}
		$q = $this->db->query(
			'SELECT DISTINCT facility FROM ihrisdata WHERE facility_id = ' . $this->db->escape($facid) . ' LIMIT 1'
		);
		return ($q->num_rows() > 0) ? trim((string) $q->row()->facility) : $fallback;
	}

	protected function normalizeFacilitySelection($facilities)
	{
		if (!is_array($facilities)) {
			return ($facilities !== '' && $facilities !== null) ? [(string) $facilities] : [];
		}

		return array_values(array_filter($facilities, function ($value) {
			return trim((string) $value) !== '';
		}));
	}

	protected function normalizeDepartmentId($department_id)
	{
		if (is_array($department_id)) {
			$department_id = isset($department_id[0]) ? $department_id[0] : '';
		}

		return trim((string) $department_id);
	}

	/**
	 * Add nullable photo column to user table when missing.
	 */
	public function ensureUserPhotoColumn()
	{
		if ($this->db->field_exists('photo', $this->table)) {
			return true;
		}
		$this->load->dbforge();
		$this->dbforge->add_column($this->table, [
			'photo' => [
				'type'       => 'VARCHAR',
				'constraint' => 255,
				'null'       => true,
			],
		]);
		return $this->db->field_exists('photo', $this->table);
	}

	/**
	 * @param int $user_id
	 * @return object|null
	 */
	public function getProfileUser($user_id)
	{
		$user_id = (int) $user_id;
		if ($user_id <= 0) {
			return null;
		}
		$this->db->select('user.*, user_groups.group_name');
		$this->db->from($this->table);
		$this->db->join('user_groups', 'user_groups.group_id = user.role', 'left');
		$this->db->where('user.user_id', $user_id);
		return $this->db->get()->row();
	}

	/**
	 * @param int $user_id
	 * @return array<int, object>
	 */
	public function getUserAssignedFacilities($user_id)
	{
		$user_id = (int) $user_id;
		$facilities = [];
		$seen = [];

		$user = $this->db->get_where($this->table, ['user_id' => $user_id], 1)->row();
		if ($user) {
			$primary_id = trim((string) ($user->facility_id ?? ''));
			$primary_name = trim((string) ($user->facility ?? ''));
			if ($primary_id !== '') {
				$o = new stdClass();
				$o->facility_id = $primary_id;
				$o->facility = $primary_name !== '' ? $primary_name : $primary_id;
				$o->is_primary = true;
				$facilities[] = $o;
				$seen[$primary_id] = true;
			}
		}

		if ($this->db->table_exists('user_facilities')) {
			$rows = $this->db->get_where('user_facilities', ['user_id' => $user_id])->result();
			foreach ($rows as $row) {
				$fid = trim((string) ($row->facility_id ?? ''));
				if ($fid === '' || isset($seen[$fid])) {
					continue;
				}
				$seen[$fid] = true;
				$o = new stdClass();
				$o->facility_id = $fid;
				$o->facility = trim((string) ($row->facility ?? $fid));
				$o->is_primary = false;
				$facilities[] = $o;
			}
		}

		return $facilities;
	}

	/**
	 * Monthly attendance from actuals for linked employee (if any).
	 *
	 * @param string $ihris_pid
	 * @param int    $year
	 * @param int    $month
	 * @return array|null  null when no ihris_pid or table missing
	 */
	public function getMonthlyAttendanceForPerson($ihris_pid, $year = null, $month = null)
	{
		$ihris_pid = trim((string) $ihris_pid);
		if ($ihris_pid === '' || !$this->db->table_exists('actuals')) {
			return null;
		}

		$year = $year ? (int) $year : (int) date('Y');
		$month = $month ? (int) $month : (int) date('m');
		$month_start = sprintf('%04d-%02d-01', $year, $month);
		$month_end = date('Y-m-t', strtotime($month_start));

		$rows = $this->db->query(
			"SELECT schedule_id, COUNT(DISTINCT `date`) AS cnt
			 FROM actuals
			 WHERE ihris_pid = ?
			   AND `date` >= ?
			   AND `date` <= ?
			 GROUP BY schedule_id",
			[$ihris_pid, $month_start, $month_end]
		)->result();

		if (empty($rows)) {
			return null;
		}

		$map = [
			22 => 'present',
			24 => 'offduty',
			25 => 'leave',
			23 => 'request',
			26 => 'absent',
			27 => 'holiday',
		];
		$stats = [
			'present'  => 0,
			'offduty'  => 0,
			'leave'    => 0,
			'request'  => 0,
			'absent'   => 0,
			'holiday'  => 0,
			'year'     => $year,
			'month'    => $month,
			'label'    => date('F Y', strtotime($month_start)),
		];
		$total = 0;
		foreach ($rows as $row) {
			$sid = (int) $row->schedule_id;
			$cnt = (int) $row->cnt;
			if (isset($map[$sid])) {
				$stats[$map[$sid]] = $cnt;
				$total += $cnt;
			}
		}
		$stats['total_days'] = $total;
		if ($stats['present'] > 0 && $total > 0) {
			$stats['attendance_rate'] = round(($stats['present'] / $total) * 100, 1);
		} else {
			$stats['attendance_rate'] = 0;
		}

		return $stats;
	}

	/**
	 * Resolve employee ID for attendance lookup.
	 *
	 * @param object|null $user
	 * @return string
	 */
	public function resolveEmployeePid($user)
	{
		if (!$user) {
			return '';
		}
		$pid = trim((string) ($user->ihris_pid ?? ''));
		if ($pid !== '') {
			return $pid;
		}
		$username = trim((string) ($user->username ?? ''));
		if ($username !== '' && $this->db->table_exists('ihrisdata')) {
			$row = $this->db->get_where('ihrisdata', ['ihris_pid' => $username], 1)->row();
			if ($row) {
				return $username;
			}
			$person_key = 'person|' . $username;
			$row = $this->db->get_where('ihrisdata', ['ihris_pid' => $person_key], 1)->row();
			if ($row) {
				return $person_key;
			}
		}
		return $username;
	}

	/**
	 * @param int    $user_id
	 * @param string $filename
	 * @return bool
	 */
	public function saveUserPhoto($user_id, $filename)
	{
		$user_id = (int) $user_id;
		$filename = trim((string) $filename);
		if ($user_id <= 0 || $filename === '') {
			return false;
		}
		$this->db->where('user_id', $user_id);
		return (bool) $this->db->update($this->table, ['photo' => $filename]);
	}

	/**
	 * @param int   $user_id
	 * @param array $data  name, email
	 * @return string
	 */
	public function updateProfileDetails($user_id, array $data)
	{
		$user_id = (int) $user_id;
		if ($user_id <= 0) {
			return 'Invalid user';
		}
		$save = [];
		if (isset($data['name']) && trim((string) $data['name']) !== '') {
			$save['name'] = trim((string) $data['name']);
		}
		if (isset($data['email'])) {
			$save['email'] = trim((string) $data['email']);
		}
		if (empty($save)) {
			return 'No changes to save';
		}
		$this->db->where('user_id', $user_id);
		if ($this->db->update($this->table, $save)) {
			return 'Profile updated';
		}
		return 'Update failed';
	}
}
