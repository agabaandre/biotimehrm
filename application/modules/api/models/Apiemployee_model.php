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
        $this->db->select('ihrisdata.id, ihrisdata.ihris_pid, surname, firstname, othername, job, ihrisdata.facility_id, ihrisdata.facility, mobile_enroll.fingerprint_data, mobile_enroll.face_data, mobile_enroll.enrolled');
        $this->db->from('ihrisdata');
        $this->db->join('mobile_enroll', 'mobile_enroll.ihris_pid = ihrisdata.ihris_pid', 'LEFT');
        $this->db->join('user', 'user.ihris_pid = ihrisdata.ihris_pid', 'LEFT');
        $this->db->where('ihrisdata.facility_id', $facilityId);

        $query = $this->db->get();
        return $query->result();
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
        $this->db->insert('fingerprints', $data);
        return $this->db->insert_id();
    }

    public function clock($data)
    {
        $this->db->insert('clk_log', $data);
        return $this->db->insert_id();
    }

    public function get_notifications_list($facilityID) {
        $this->db->select('*');
        $this->db->where('facility_id', $facilityID);
        $this->db->from('notifications');
        $query = $this->db->get();

        if($query->num_rows()) {
            return $query->result_array();
        } else 
        {
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
    public function get_facilities_list()
    {
        // Get all facilities that have staff
        $this->db->select('facilities.facility_id, facilities.facility');
        $this->db->from('facilities');
        $this->db->join('ihrisdata', 'ihrisdata.facility_id = facilities.facility_id', 'LEFT');
        // $this->db->group_by('facilities.facility_id');

        // Order by facility name
        $this->db->order_by('facilities.facility', 'ASC');

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
}