<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

Class Auth_model extends CI_Model
{
    public function validate_login($userdata)
    {  
        $this->load->database();

        $username = $userdata['username'];
        $password = $userdata['password'];

        $this->db->select('facility_id, username, role');
        $this->db->where('username', $username);
        $this->db->where('password', md5($password));
        $query = $this->db->get('user');

        if($query->num_rows() === 1)
        {
            return $query->row();

        } else {
            return false;
        }
    }

    public function validate_register($userdata)
    {
        //Optionally Auto load the database ;) Your Choice
        $this->load->database();

        $user_data = array(
            'username'=>$userdata->username,
            'password'=> password_hash($userdata->password, PASSWORD_DEFAULT),
            'email_address'=>$userdata->email_address,
            'phone_number'=>$userdata->phone_number
        );

        $this->db->insert('users', $user_data);
        if($this->db->affected_rows() === 1)
        {
            return 1;
        } else {
            return 0;
        }
    }

    public function password_request_reset($userdata) 
    {
        //Optionally Auto load the database ;) Your Choice
        $this->load->database();

        $query = $this->db->get_where('users', array('username'=>$userdata->username));

        if($query->num_rows() === 1)
        {
            foreach ($query->result() as $row) {
               $phone_number = $row->phone_number;
               $first_name = $row->first_name;
               $last_name = $row->last_name;
            }

            $generated_code = rand(100000,999999);

            $url = 'https://platform.clickatell.com/messages/http/send?apiKey=6E8jqkyPRIuYuP9-XJWw5g==&to='.$phone_number.'&content='.$generated_code.'';

            // echo $url;
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close ($ch);

            // return $result;

            $decoded_data = json_decode($result);
            if($decoded_data->messages[0]->accepted === TRUE)
            {
                $data = array(
                    'reset_status' => true,
                    'reset_code' => $generated_code
                 );
                $this->db->where('username', $userdata->username);
                $this->db->update('users', $data); 

                return 1;
            } else {
                return 0;
            }
        }
    }

    public function password_reset($userdata)
    {
        $query = $this->db->get_where('users', array('username'=>$userdata->username, 'reset_code'=>$userdata->reset_code, 'reset_status'=>1));
        if($query->num_rows() > 0){
            $data = array(
                'account_reset'=>'YES'
            );
            $this->db->where('username', $userdata->username);
            $this->db->update('users', $data);
            return 1;
        } else {
            return 0;
        }
    }
}
