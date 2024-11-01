<?php defined('BASEPATH') or exit('No direct script access allowed');

class Apiemployee_model extends CI_Model
{

    // Get Clock History of a faciliry
    public function get_clock_history($facilityId)
    {
        $this->db->select('*');
        $this->db->from('clock_history');
        $this->db->where('facility_id', $facilityId);
        $query = $this->db->get();
        return $query->result();
    }

    // Get Staff List 
    public function get_staff_list($facilityId)
	{
	    $this->db->select('ihrisdata.id, ihrisdata.ihris_pid, ihrisdata.surname as surname, ihrisdata.firstname as firstname, ihrisdata.othername as othername, ihrisdata.job, ihrisdata.facility_id, ihrisdata.facility, mobile_enroll.fingerprint_data, mobile_enroll.face_data, mobile_enroll.enrolled');
	    $this->db->from('ihrisdata');
	    $this->db->join('mobile_enroll', 'mobile_enroll.ihris_pid = ihrisdata.ihris_pid', 'LEFT');
	    $this->db->join('user', 'user.ihris_pid = ihrisdata.ihris_pid', 'LEFT');
	    $this->db->where('ihrisdata.facility_id', $facilityId);
	
	    $query = $this->db->get();
	
	    if ($query) {
	        $result = $query->result();
	
	        // Modify fingerprint_data to always return an array
	        foreach ($result as $staff) {
	            if (is_string($staff->fingerprint_data)) {
	                // Convert fingerprint_data string to an array
	                $staff->fingerprint_data = [$staff->fingerprint_data];
	            } elseif ($staff->fingerprint_data === null) {
	                // If fingerprint_data is null, set it as an empty array
	                $staff->fingerprint_data = [];
	            }
	        }
	
	        return $result;
	    } else {
	        // Handle the error for debugging
	        log_message('error', 'Database query failed: ' . $this->db->last_query());
	        return false;
	    }
	}


    // Post Staff List
    public function post_staff_list($data)
	{
	    $ihris_pid = $data['ihris_pid'];
	
	    // Remove keys with null values from the data array
	    $data = array_filter($data, function($value) {
	        return !is_null($value);
	    });
	
	    // Check if the record with the given 'ihris_pid' exists
	    $existing_record = $this->db->get_where('mobile_enroll', ['ihris_pid' => $ihris_pid])->row_array();
	
	    if ($existing_record) {
	        // If the record exists, update it
	        $this->db->where('ihris_pid', $ihris_pid);
	        $this->db->update('mobile_enroll', $data);
	    } else {
	        // If the record does not exist, insert it
	        $this->db->insert('mobile_enroll', $data);
	    }
	}


    // Get Staff Details
    public function get_staff_details($id, $facilityId)
    {
        $this->db->select('id, ihrisdata.ihris_pid, surname, firstname, othername, job, facility_id, facility, fingerprint_data, face_data, enrolled');
        $this->db->from('ihrisdata');
        $this->db->where('facility_id', $facilityId);
        $this->db->where('id', $id);
        $query = $this->db->get();
        $user = $query->row();
        return $user;
    }

    public function enroll($data)
    {
        // Extract the required fields from the $data array
        $enrollData = [
            'ihris_pid' => $data['ihris_pid'],
            'face_data' => $data['face_data'],
            'fingerprint_data' => json_encode($data['fingerprint_data']),
            'enrolled' => $data['face_enrolled'] || $data['fingerprint_enrolled'] ? 1 : 0,
            'facility_id' => $data['facility_id'],
            'firstname' => $data['firstname'],
            'surname' => $data['surname'],
            'job' => $data['job'],
            'synced' => $data['synced'],
            'template_id' => $data['template_id'],
            'face_enrolled' => $data['face_enrolled'],
            'fingerprint_enrolled' => $data['fingerprint_enrolled']
        ];

        // Check if a record with the given 'ihris_pid' exists in the mobile_enroll table
        $existing_record = $this->db->get_where('mobile_enroll', ['ihris_pid' => $enrollData['ihris_pid']])->row_array();

        if ($existing_record) {
            // If the record exists, update it
            $this->db->where('ihris_pid', $enrollData['ihris_pid']);
            $this->db->update('mobile_enroll', $enrollData);
        } else {
            // If the record does not exist, insert it
            $this->db->insert('mobile_enroll', $enrollData);
        }

        if ($existing_record) {
            // If the record exists, update it
            $this->db->where('ihris_pid', $enrollData['ihris_pid']);
            if ($this->db->update('mobile_enroll', $enrollData)) {
                return ['status' => true, 'message' => 'Record updated successfully'];
            } else {
                return ['status' => false, 'message' => 'Failed to update record'];
            }
        } else {
            // If the record does not exist, insert it
            if ($this->db->insert('mobile_enroll', $enrollData)) {
                return ['status' => true, 'message' => 'Record inserted successfully'];
            } else {
                return ['status' => false, 'message' => 'Failed to insert record'];
            }
        }
    }

    public function clock($data)
    {
        if ($this->db->insert('mobileclk_log', $data)) {
            return [
                'status' => true,
                'insert_id' => $this->db->insert_id()
            ];
        } else {
            return [
                'status' => false,
                'error' => $this->db->error() // Capture the database error
            ];
        }
    }

    public function get_notifications_list($facilityID)
    {
        $this->db->select('*');
        $this->db->where('facility_id', $facilityID);
        $this->db->from('notifications');
        $query = $this->db->get();

        if ($query->num_rows()) {
            return $query->result_array();
        } else {
            return [];
        }
    }

    public function get_staff_by_ihris_pid($ihris_pid)
    {
        $this->db->select('*');
        $this->db->from('ihrisdata');
        $this->db->where('ihris_pid', $ihris_pid);
        $query = $this->db->get();
        return $query->row();
    }

    public function update_staff_record($data)
    {
        // Extract the data, skipping keys that are not defined
        $dataToSave = array_filter($data, function ($key) {
            return in_array($key, ['ihris_pid', 'fingerprint_data', 'face_data', 'enrolled']);
        });

        // Check if staff has a record in mobile_enroll table, this handles (face_data, fingerprint_data, ihris_pid, enrolled)
        $this->db->select('*');
        $this->db->from('mobile_enroll');
        $this->db->where('ihris_pid', $dataToSave['ihris_pid']);
        $query = $this->db->get();
        $staff = $query->row();

        if ($staff) {
            // Update staff record using set
            $this->db->set($dataToSave);
            $this->db->where('ihris_pid', $dataToSave['ihris_pid']);
            $this->db->update('mobile_enroll');
        } else {
            // Insert staff record
            $this->db->insert('mobile_enroll', $dataToSave);
        }

        return $this->db->affected_rows();
    }

    // Get Facilities
    public function get_facilities_list($id)
    {
        // Get all facilities that have staff
        $this->db->select('user_facilities.facility_id, user_facilities.facility');
        $this->db->from('user_facilities');
        $this->db->where('user_facilities.user_id', $id);
        // Order by facility name
        $this->db->order_by('user_facilities.facility', 'ASC');

        // Return distinct facilities
        $this->db->distinct();

        $query = $this->db->get();

        if ($query->num_rows()) {
            return $query->result_array();
        } else {
            return [];
        }
    }

    // Get Facility by name
    public function get_facility_by_name($facilityName)
    {
        $this->db->select('*');
        $this->db->from('facilities');
        $this->db->where('facility', $facilityName);
        $query = $this->db->get();
        return $query->row();
    }

    public function clock_user_mobile($data)
    {

        $this->db->insert('clk_log', $data);


        return $this->db->insert_id(); // Return the ID of the inserted record if needed
    }
    public function clock_out_mobile($entry_id, $timeout)
    {
        return $this->db->query("UPDATE clk_log set time_out='$timeout' WHERE entry_id='$entry_id'");
    }

    public function enroll_user_mobile($data)
    {
        $this->db->insert('mobile_enroll', $data);
        return $this->db->insert_id(); // Return the ID of the inserted record if needed
    }

    public function get_facility_name($facilityId)
    {
        $this->db->select('facility');
        $this->db->from('facilities');
        $this->db->where('facility_id', $facilityId);
        $query = $this->db->get();
        $result = $query->row();
        return $result ? $result->facility : null;
    }

    public function update_mobile_enroll($data)
    {
        // Extract ihris_pid from the data
        $ihris_pid = $data['ihris_pid'];

        // Check if a record with the given 'ihris_pid' exists in the mobile_enroll table
        $existing_record = $this->db->get_where('mobile_enroll', ['ihris_pid' => $ihris_pid])->row_array();

        if ($existing_record) {
            // If the record exists, update it
            $this->db->where('ihris_pid', $ihris_pid);
            $this->db->update('mobile_enroll', $data);
        } else {
            // If the record does not exist, insert it
            $this->db->insert('mobile_enroll', $data);
        }
    }
}
