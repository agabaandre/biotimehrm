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
                    // Check if there's a completed record for this user on this date
                    // (has both time_in and time_out)
                    $this->db->where('ihris_pid', $ihris_pid);
                    $this->db->where('facility_id', $facility_id);
                    $this->db->where('date', $date);
                    $this->db->where('time_in IS NOT NULL');
                    $this->db->where('time_out IS NOT NULL');
                    $this->db->order_by('time_out', 'DESC'); // Get the most recent completed record
                    $this->db->limit(1);
                    $completedRecord = $this->db->get('mobileclk_log')->row();
                    
                    if ($completedRecord) {
                        // Create a new record for this clock-out
                        if ($this->db->insert('mobileclk_log', $data)) {
                            return [
                                'status' => true,
                                'message' => 'Found complete record, created new clock-out record',
                                'insert_id' => $this->db->insert_id(),
                                'is_update' => false,
                                'is_new_after_complete' => true
                            ];
                        } else {
                            return [
                                'status' => false,
                                'error' => $this->db->error()
                            ];
                        }
                    } else {
                        // No matching record found to update and no completed record
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
            } 
            // If this is a CLOCK IN request
            else if ($data['status'] === "CLOCKED_IN") {
                // Check if user has a complete record (both time_in and time_out) for the day
                $this->db->where('ihris_pid', $ihris_pid);
                $this->db->where('facility_id', $facility_id);
                $this->db->where('date', $date);
                $this->db->where('time_in IS NOT NULL');
                $this->db->where('time_out IS NOT NULL');
                $completedRecord = $this->db->get('mobileclk_log')->row();
                
                if ($completedRecord) {
                    // User has completed a clock cycle today, create a new clock-in
                    if ($this->db->insert('mobileclk_log', $data)) {
                        return [
                            'status' => true,
                            'message' => 'Created new clock-in after previous complete cycle',
                            'insert_id' => $this->db->insert_id(),
                            'is_duplicate' => false,
                            'is_new_after_complete' => true
                        ];
                    } else {
                        return [
                            'status' => false,
                            'error' => $this->db->error()
                        ];
                    }
                }
                
                // Check if user is already clocked in for the day but not out
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

    // =========================================================================
    // STAFF CRUD (mobile app sync: create, update, delete)
    // =========================================================================

    public function create_staff($data)
    {
        $ihris_pid = $data['ihris_pid'] ?? null;

        // Insert into ihrisdata
        $ihrisData = array_filter([
            'ihris_pid' => $ihris_pid,
            'surname' => $data['surname'] ?? null,
            'firstname' => $data['firstname'] ?? null,
            'othername' => $data['othername'] ?? null,
            'job' => $data['job'] ?? null,
            'facility_id' => $data['facility_id'] ?? null,
            'facility' => $data['facility'] ?? null,
            'gender' => $data['gender'] ?? null,
            'district' => $data['district'] ?? null,
            'dob' => $data['dob'] ?? null,
        ], function ($v) { return $v !== null; });

        $this->db->trans_begin();

        // Check if ihris_pid already exists
        $existing = $this->db->get_where('ihrisdata', ['ihris_pid' => $ihris_pid])->row();
        if ($existing) {
            $this->db->where('ihris_pid', $ihris_pid);
            $this->db->update('ihrisdata', $ihrisData);
        } else {
            $this->db->insert('ihrisdata', $ihrisData);
        }

        // Handle enrollment data if present
        $enrollData = array_filter([
            'ihris_pid' => $ihris_pid,
            'fingerprint_data' => $data['fingerprint_data'] ?? null,
            'face_data' => $data['face_data'] ?? null,
            'enrolled' => ($data['face_enrolled'] || $data['fingerprint_enrolled']) ? 1 : 0,
            'face_enrolled' => !empty($data['face_enrolled']) ? 1 : 0,
            'fingerprint_enrolled' => !empty($data['fingerprint_enrolled']) ? 1 : 0,
            'facility_id' => $data['facility_id'] ?? null,
        ], function ($v) { return $v !== null; });

        $existingEnroll = $this->db->get_where('mobile_enroll', ['ihris_pid' => $ihris_pid])->row();
        if ($existingEnroll) {
            $this->db->where('ihris_pid', $ihris_pid);
            $this->db->update('mobile_enroll', $enrollData);
        } else {
            $this->db->insert('mobile_enroll', $enrollData);
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return null;
        }

        $this->db->trans_commit();
        return $this->get_staff_record($ihris_pid);
    }

    public function update_staff($id, $data)
    {
        $ihris_pid = $data['ihris_pid'] ?? null;

        $ihrisData = array_filter([
            'surname' => $data['surname'] ?? null,
            'firstname' => $data['firstname'] ?? null,
            'othername' => $data['othername'] ?? null,
            'job' => $data['job'] ?? null,
            'facility_id' => $data['facility_id'] ?? null,
            'facility' => $data['facility'] ?? null,
            'gender' => $data['gender'] ?? null,
            'district' => $data['district'] ?? null,
            'dob' => $data['dob'] ?? null,
        ], function ($v) { return $v !== null; });

        $this->db->trans_begin();

        if ($ihris_pid) {
            $this->db->where('ihris_pid', $ihris_pid);
            $this->db->update('ihrisdata', $ihrisData);
        }

        // Update enrollment data if present
        $enrollFields = ['fingerprint_data', 'face_data', 'enrolled', 'face_enrolled', 'fingerprint_enrolled'];
        $enrollData = [];
        foreach ($enrollFields as $field) {
            if (isset($data[$field])) {
                $enrollData[$field] = $data[$field];
            }
        }

        if (!empty($enrollData) && $ihris_pid) {
            $existingEnroll = $this->db->get_where('mobile_enroll', ['ihris_pid' => $ihris_pid])->row();
            if ($existingEnroll) {
                $this->db->where('ihris_pid', $ihris_pid);
                $this->db->update('mobile_enroll', $enrollData);
            } else {
                $enrollData['ihris_pid'] = $ihris_pid;
                $this->db->insert('mobile_enroll', $enrollData);
            }
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return null;
        }

        $this->db->trans_commit();
        return $this->get_staff_record($ihris_pid);
    }

    public function delete_staff($id)
    {
        // Get the staff record first to find ihris_pid
        $this->db->select('ihris_pid');
        $this->db->from('ihrisdata');
        $this->db->where('id', $id);
        $record = $this->db->get()->row();

        if (!$record) {
            return false;
        }

        $this->db->trans_begin();

        // Delete enrollment data
        $this->db->where('ihris_pid', $record->ihris_pid);
        $this->db->delete('mobile_enroll');

        // Delete staff record
        $this->db->where('id', $id);
        $this->db->delete('ihrisdata');

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->trans_commit();
        return true;
    }

    private function get_staff_record($ihris_pid)
    {
        $this->db->select('ihrisdata.id, ihrisdata.ihris_pid, ihrisdata.surname, ihrisdata.firstname,
            ihrisdata.othername, ihrisdata.job, ihrisdata.facility_id, ihrisdata.facility,
            mobile_enroll.fingerprint_data, mobile_enroll.face_data, mobile_enroll.enrolled,
            mobile_enroll.face_enrolled, mobile_enroll.fingerprint_enrolled');
        $this->db->from('ihrisdata');
        $this->db->join('mobile_enroll', 'mobile_enroll.ihris_pid = ihrisdata.ihris_pid', 'left');
        $this->db->where('ihrisdata.ihris_pid', $ihris_pid);
        return $this->db->get()->row();
    }

    // =========================================================================
    // FINGERPRINT SYNC (JSON-based upload/download for mobile app)
    // =========================================================================

    public function upload_fingerprint_template($ihris_pid, $fingerprint_data)
    {
        $existing = $this->db->get_where('mobile_enroll', ['ihris_pid' => $ihris_pid])->row();

        $data = [
            'ihris_pid' => $ihris_pid,
            'fingerprint_data' => $fingerprint_data,
            'fingerprint_enrolled' => 1,
            'enrolled' => 1
        ];

        if ($existing) {
            $this->db->where('ihris_pid', $ihris_pid);
            return $this->db->update('mobile_enroll', $data);
        } else {
            return $this->db->insert('mobile_enroll', $data);
        }
    }

    public function get_fingerprints_by_facility($facilityId)
    {
        $this->db->select('mobile_enroll.ihris_pid, mobile_enroll.fingerprint_data');
        $this->db->from('mobile_enroll');
        $this->db->join('ihrisdata', 'ihrisdata.ihris_pid = mobile_enroll.ihris_pid', 'inner');
        $this->db->where('ihrisdata.facility_id', $facilityId);
        $this->db->where('mobile_enroll.fingerprint_data IS NOT NULL');
        $this->db->where('mobile_enroll.fingerprint_data !=', '');
        $this->db->where('mobile_enroll.fingerprint_data !=', 'null');
        $this->db->where('mobile_enroll.fingerprint_enrolled', 1);
        $query = $this->db->get();
        return $query->result_array();
    }

    // =========================================================================
    // FACE EMBEDDING SYNC (JSON-based upload/download for mobile app)
    // =========================================================================

    public function upload_face_embedding($ihris_pid, $face_data, $face_image = null)
    {
        $existing = $this->db->get_where('mobile_enroll', ['ihris_pid' => $ihris_pid])->row();

        $data = [
            'ihris_pid' => $ihris_pid,
            'face_data' => $face_data,
            'face_enrolled' => 1,
            'enrolled' => 1
        ];

        if ($face_image) {
            $data['face_image'] = $face_image;
        }

        if ($existing) {
            $this->db->where('ihris_pid', $ihris_pid);
            return $this->db->update('mobile_enroll', $data);
        } else {
            return $this->db->insert('mobile_enroll', $data);
        }
    }

    public function get_face_embeddings_by_facility($facilityId)
    {
        $this->db->select('mobile_enroll.ihris_pid, mobile_enroll.face_data, mobile_enroll.face_image');
        $this->db->from('mobile_enroll');
        $this->db->join('ihrisdata', 'ihrisdata.ihris_pid = mobile_enroll.ihris_pid', 'inner');
        $this->db->where('ihrisdata.facility_id', $facilityId);
        $this->db->where('mobile_enroll.face_data IS NOT NULL');
        $this->db->where('mobile_enroll.face_data !=', '');
        $this->db->where('mobile_enroll.face_data !=', 'null');
        $this->db->where('mobile_enroll.face_enrolled', 1);
        $query = $this->db->get();
        return $query->result_array();
    }

    // =========================================================================
    // OUT-OF-STATION / LEAVE REQUEST
    // =========================================================================

    public function create_out_of_station_request($data)
    {
        $entry_id = md5(($data['ihris_pid'] ?? '') . $data['startDate'] . $data['endDate'] . $data['reason']);

        $requestData = [
            'entry_id' => $entry_id,
            'reason_id' => $data['reason_id'] ?? null,
            'ihris_pid' => $data['ihris_pid'] ?? null,
            'date' => date('Y-m-d H:i:s'),
            'dateFrom' => $data['startDate'],
            'dateTo' => $data['endDate'],
            'remarks' => $data['comments'] ?? '',
            'facility_id' => $data['facility_id'] ?? null,
            'attachment' => $data['attachment'] ?? null,
            'status' => 'Pending'
        ];

        // Check for duplicate
        $existing = $this->db->get_where('requests', ['entry_id' => $entry_id])->row();
        if ($existing) {
            return [
                'success' => false,
                'message' => 'A similar request already exists'
            ];
        }

        if ($this->db->insert('requests', $requestData)) {
            return [
                'success' => true,
                'message' => 'Request submitted successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to submit request'
        ];
    }

    // Get user by ID
    public function get_user_by_id($userId)
    {
        $this->db->select('*');
        $this->db->from('user');
        $this->db->where('user_id', $userId);
        $query = $this->db->get();
        return $query->row();
    }

    // Get all reasons for leave/absence requests
    public function get_reasons()
    {
        return $this->db->get('reasons')->result_array();
    }

    // Get all cadres (distinct values from ihrisdata — employee_cadre table is empty)
    public function get_cadres()
    {
        $query = $this->db->query(
            "SELECT DISTINCT cadre FROM ihrisdata 
             WHERE cadre IS NOT NULL AND cadre != '' 
             ORDER BY cadre ASC"
        );
        return $query->result_array();
    }

    // Get all districts (distinct values from ihrisdata — employee_districts has 111 rows but ihrisdata has 147 distinct)
    public function get_districts()
    {
        $query = $this->db->query(
            "SELECT DISTINCT district FROM ihrisdata 
             WHERE district IS NOT NULL AND district != '' 
             ORDER BY district ASC"
        );
        return $query->result_array();
    }

    // Get all facilities (distinct from ihrisdata — employee_facility only has 2 rows)
    public function get_all_facilities()
    {
        $query = $this->db->query(
            "SELECT DISTINCT facility_id, facility FROM ihrisdata 
             WHERE facility IS NOT NULL AND facility != '' 
             ORDER BY facility ASC"
        );
        return $query->result_array();
    }

    // Get all jobs (distinct values from ihrisdata — employee_jobs table is empty)
    public function get_jobs()
    {
        $query = $this->db->query(
            "SELECT DISTINCT job FROM ihrisdata 
             WHERE job IS NOT NULL AND job != '' 
             ORDER BY job ASC"
        );
        return $query->result_array();
    }
}
