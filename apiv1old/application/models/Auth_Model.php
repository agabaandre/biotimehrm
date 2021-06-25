<?php 

CLass Auth_Model extends CI_Model
{
    public function login($username, $password)
    {
        $response = array();

        $this->db->select('ihris_pid, username, name, department, department_id, facility, facility_id, role');
        $this->db->from('user');
        $this->db->where(array('username'=>$username, 'password'=> md5($password)));
        $query = $this->db->get();
        if($query->num_rows() === 1) {
           
            $userdata = $query->row();
            
            $response['status'] = 'AUTH_SUCCESS';
            $response['message'] = 'Successfuly authenticated';
            $response['error'] = FALSE;
            $response['user'] = $userdata;

            return $response;
        
        } else {
            $response['status'] = 'AUTH_FAILED';
            $response['message'] = 'Invalid username or password';
            $response['error'] = TRUE;

            return $response;
        }

    }
}