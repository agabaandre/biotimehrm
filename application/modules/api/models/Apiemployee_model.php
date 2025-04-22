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
    
    // Get Clock History List
    public function get_clock_history_list($facilityId)
    {
        $this->db->select('*');
        $this->db->from('clk_log');
        $this->db->where('facility_id', $facilityId);
        $this->db->order_by('date', 'DESC');
        $this->db->limit(100); // Limit to prevent large data loads
        $query = $this->db->get();
        return $query->result_array();
    }

    // Get Staff List 
    public function get_staff_list($facilityId)	 {
	    $this->db->select('ihrisdata.id, ihrisdata.ihris_pid, ihrisdata.surname as surname, 
	        ihrisdata.firstname as firstname, ihrisdata.othername as othername, 
	        ihrisdata.job, ihrisdata.facility_id, ihrisdata.facility, 
	        mobile_enroll.fingerprint_data, mobile_enroll.face_data, mobile_enroll.enrolled');
	    $this->db->from('ihrisdata');
	    $this->db->join('mobile_enroll', 'mobile_enroll.ihris_pid = ihrisdata.ihris_pid', 'LEFT');
	    $this->db->join('user', 'user.ihris_pid = ihrisdata.ihris_pid', 'LEFT');
	    $this->db->where('ihrisdata.facility_id', $facilityId);
	
	    $query = $this->db->get();
	
	    if ($query) {
	        $result = $query->result();
	
	        // Process each staff record to clean and validate data
	        foreach ($result as $staff) {
	            // Handle fingerprint data
	            if ($staff->fingerprint_data !== null) {
	                // Try to decode if it's a JSON string
	                $decoded = json_decode($staff->fingerprint_data, true);
	                
	                if ($decoded !== null) {
	                    // If successfully decoded JSON, filter out null/empty values
	                    $staff->fingerprint_data = array_values(array_filter($decoded, function($item) {
	                        return $item !== null && $item !== 'null' && $item !== '';
	                    }));
	                } else {
	                    // If not JSON, check if it's a string "null" or actual fingerprint data
	                    if ($staff->fingerprint_data === 'null' || empty($staff->fingerprint_data)) {
	                        $staff->fingerprint_data = [];
	                    } else {
	                        // Single fingerprint data entry
	                        $staff->fingerprint_data = [$staff->fingerprint_data];
	                    }
	                }
	            } else {
	                $staff->fingerprint_data = [];
	            }
	
	            // Ensure enrolled is boolean
	            $staff->enrolled = ($staff->enrolled == '1' || $staff->enrolled === true) ? true : false;
	
	            // Clean up face data
	            if ($staff->face_data === 'null' || empty($staff->face_data)) {
	                $staff->face_data = null;
	            }
	        }
	
	        return $result;
	    } else {
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
	    // Prepare the enrollment data
	    $enrollData = [
	        'ihris_pid' => $data['ihris_pid'],
	        'face_data' => $data['face_data'],
	        'fingerprint_data' => is_array($data['fingerprint_data']) ? 
	            json_encode(array_filter($data['fingerprint_data'], function($v) { 
	                return $v !== null && $v !== 'null' && $v !== ''; 
	            })) : null,
	        'enrolled' => $data['enrolled'],
	        'facility_id' => $data['facility_id'],
	        'firstname' => $data['firstname'],
	        'surname' => $data['surname'],
	        'job' => $data['job'],
	        'synced' => $data['synced'],
	        'template_id' => $data['template_id'],
	        'face_enrolled' => $data['face_enrolled'],
	        'fingerprint_enrolled' => $data['fingerprint_enrolled']
	    ];
	
	    try {
	        // Begin transaction
	        $this->db->trans_begin();
	
	        // Check for existing record
	        $existing_record = $this->db->get_where('mobile_enroll', [
	            'ihris_pid' => $enrollData['ihris_pid']
	        ])->row_array();
	
	        if ($existing_record) {
	            // Update existing record
	            $this->db->where('ihris_pid', $enrollData['ihris_pid']);
	            $this->db->update('mobile_enroll', $enrollData);
	            $message = 'Record updated successfully';
	        } else {
	            // Insert new record
	            $this->db->insert('mobile_enroll', $enrollData);
	            $message = 'Record inserted successfully';
	        }
	
	        // Commit or rollback transaction
	        if ($this->db->trans_status() === FALSE) {
	            $this->db->trans_rollback();
	            return [
	                'status' => false,
	                'message' => 'Database error occurred: ' . $this->db->error()['message']
	            ];
	        }
	
	        $this->db->trans_commit();
	        return [
	            'status' => true,
	            'message' => $message
	        ];
	
	    } catch (Exception $e) {
	        $this->db->trans_rollback();
	        return [
	            'status' => false,
	            'message' => 'Error occurred: ' . $e->getMessage()
	        ];
	    }
	}

    public function clock($data)
    {
        try {
            $ihris_pid = $data['ihris_pid'];
            $facility_id = $data['facility_id'];
            $date = $data['date'];
            
            // If this is a CLOCK OUT request
            if ($data['status'] === "CLOCKED_OUT") {
                // Look for the most recent CLOCK IN record for this user on this date
                $this->db->where('ihris_pid', $ihris_pid);
                $this->db->where('facility_id', $facility_id);
                $this->db->where('date', $date);
                $this->db->where('status', 'CLOCKED_IN');
                $this->db->where('time_out IS NULL'); // Only records that haven't been clocked out
                $this->db->order_by('time_in', 'DESC'); // Get the most recent clock in
                $this->db->limit(1);
                $existingRecord = $this->db->get('mobileclk_log')->row();
                
                if ($existingRecord) {
                    // Update the existing record with the clock out time
                    $this->db->where('id', $existingRecord->id);
                    $updateData = [
                        'time_out' => $data['time_out'],
                        'status' => 'CLOCKED_OUT'
                    ];
                    
                    $this->db->update('mobileclk_log', $updateData);
                    
                    return [
                        'status' => true,
                        'message' => 'Clock-out successful',
                        'record_id' => $existingRecord->id,
                        'is_update' => true
                    ];
                } else {
                    // No matching record found to update
                    // Insert a new record as a fallback (note: this might indicate a missed clock-in)
                    log_message('warning', 'No matching CLOCK IN record found for user ' . $ihris_pid . ' on ' . $date);
                    
                    if ($this->db->insert('mobileclk_log', $data)) {
                        return [
                            'status' => true,
                            'message' => 'No matching clock-in found. Created new clock-out record.',
                            'insert_id' => $this->db->insert_id(),
                            'is_update' => false,
                            'warning' => 'No matching clock-in record was found'
                        ];
                    } else {
                        return [
                            'status' => false,
                            'error' => $this->db->error()
                        ];
                    }
                }
            } 
            // If this is a CLOCK IN request
            else if ($data['status'] === "CLOCKED_IN") {
                // Check if user is already clocked in for the day
                $this->db->where('ihris_pid', $ihris_pid);
                $this->db->where('facility_id', $facility_id);
                $this->db->where('date', $date);
                $this->db->where('status', 'CLOCKED_IN');
                $this->db->where('time_out IS NULL'); // Only records that haven't been clocked out
                $existingClockIn = $this->db->get('mobileclk_log')->row();
                
                if ($existingClockIn) {
                    // User is already clocked in and hasn't clocked out
                    return [
                        'status' => true,
                        'message' => 'User is already clocked in and has not clocked out',
                        'record_id' => $existingClockIn->id,
                        'is_duplicate' => true
                    ];
                }
                
                // Insert new clock in record
                if ($this->db->insert('mobileclk_log', $data)) {
                    return [
                        'status' => true,
                        'message' => 'Clock-in successful',
                        'insert_id' => $this->db->insert_id(),
                        'is_duplicate' => false
                    ];
                } else {
                    return [
                        'status' => false,
                        'error' => $this->db->error()
                    ];
                }
            }
            // Invalid status
            else {
                return [
                    'status' => false,
                    'error' => [
                        'message' => 'Invalid clock status: ' . $data['status']
                    ]
                ];
            }
        } catch (Exception $e) {
            // Catch any exceptions and return a friendly error
            return [
                'status' => false,
                'error' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]
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

    // Handle uploading face data
    public function upload_face_data($staffId, $face_data_path)
    {
        // Process the uploaded face data
        $data = [
            'ihris_pid' => $staffId,
            'face_data' => $face_data_path,
            'face_enrolled' => 1
        ];

        // Get existing record to check if we need to update
        $existing_record = $this->db->get_where('mobile_enroll', ['ihris_pid' => $staffId])->row_array();

        if ($existing_record) {
            // Update existing record
            $this->db->where('ihris_pid', $staffId);
            $result = $this->db->update('mobile_enroll', $data);
        } else {
            // Insert new record
            $data['enrolled'] = 1; // Ensure enrolled is set if this is a new record
            $result = $this->db->insert('mobile_enroll', $data);
        }

        return [
            'status' => $result ? true : false,
            'message' => $result ? 'Face data uploaded successfully' : 'Failed to upload face data',
            'file_path' => $face_data_path
        ];
    }

    // Handle uploading fingerprint data
    public function upload_fingerprint_data($staffId, $fingerprint_data_path)
    {
        // Read and process the uploaded fingerprint data
        $data = [
            'ihris_pid' => $staffId,
            'fingerprint_data' => $fingerprint_data_path,
            'fingerprint_enrolled' => 1
        ];

        // Get existing record to check if we need to update
        $existing_record = $this->db->get_where('mobile_enroll', ['ihris_pid' => $staffId])->row_array();

        if ($existing_record) {
            // Update existing record
            $this->db->where('ihris_pid', $staffId);
            $result = $this->db->update('mobile_enroll', $data);
        } else {
            // Insert new record
            $data['enrolled'] = 1; // Ensure enrolled is set if this is a new record
            $result = $this->db->insert('mobile_enroll', $data);
        }

        return [
            'status' => $result ? true : false,
            'message' => $result ? 'Fingerprint data uploaded successfully' : 'Failed to upload fingerprint data',
            'file_path' => $fingerprint_data_path
        ];
    }

    // Download face data for a staff member
    public function download_face_data($staffId)
    {
        $this->db->select('face_data');
        $this->db->from('mobile_enroll');
        $this->db->where('ihris_pid', $staffId);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $result = $query->row();
            return $result->face_data;
        }
        
        return null;
    }

    // Download fingerprint data for a staff member
    public function download_fingerprint_data($staffId)
    {
        $this->db->select('fingerprint_data');
        $this->db->from('mobile_enroll');
        $this->db->where('ihris_pid', $staffId);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $result = $query->row();
            return $result->fingerprint_data;
        }
        
        return null;
    }
}
