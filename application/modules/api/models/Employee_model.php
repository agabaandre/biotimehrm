<?php defined('BASEPATH') or exit('No direct script access allowed');

class Employee_model extends CI_Model
{
    public function get_employees($facility, $ihris_pid)
    {
    }

    public function clock_user($data)
    {




        return;
    }

    public function enroll_user($data)
    {

        $response = array();
        $fingerprint = isset($data['fingerprint']) ? $data['fingerprint'] : NULL;
        $pincode = isset($data['pin']) ? $data['pin'] : NULL;

        $entry_id = $data['facilityId'] . 'person|' . $data['userId'] . 'b' . $fingerprint . '|fpt';
        $facilityId = $data['facilityId'];
        $queryc = $this->db->query("delete from fingerprints where facilityId='$facilityId'  and fingerprint='$fingerprint'");

        if ($queryc) {
            $this->db->insert('fingerprints', array(
                'entry_id' => $entry_id,
                'fingerprint' => $fingerprint,
                'pin' => $pincode,
                'location' => $data['location'],
                'ihris_pid' => 'person|' . $data['userId'],
                'facilityId' => $data['facilityId'],
                'source' => $data['source']
            ));

            if ($this->db->affected_rows() > 0) {
                $response['status'] = 'SUCCESS';
                $response['message'] = 'User record inserted';
                $response['error'] = null;
            } else {
                $response['status'] = 'FAILED';
                $response['message'] = 'User record not inserted';
                $response['error'] = null;
            }
        }

        return $response;
    }
}
