<?php 

Class Auth extends CI_Model {

    public function authenticate($userdata) {
        $username = $userdata['username'];
        $password = $userdata['password'];

        $this->db->where('username', $username);
        $this->db->where('password', md5($password));
        $this->db->from('user');
        $query = $this->db->get();

        if($query->num_rows() === 1) {
            $row = $query->row();
            $userInfo = array(
                "name"=> $row->name,
                "department" => $row->department,
                "personId"=>$row->ihris_pid,
                "departmentId" => $row->department_id,
                "role" => $row->role,
                "facilityId" => $row->facility_id,
                "facility" => $row->facility
            );

            return $userInfo;

        } else {
            return 0;
        }

    }
}