<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ActivityLogger {
    
    protected $CI;
    
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
    }
    
    /**
     * Log user activity
     */
    public function log_activity($activity = '', $module = '', $route = '') {
        try {
            // Check if database is available
            if (!$this->CI->db->conn_id) {
                log_message('debug', 'ActivityLogger: Database not available yet');
                return false;
            }
            
            // Get current user ID from session - try different possible field names
            $user_id = $this->CI->session->userdata('id') ?: 
                      $this->CI->session->userdata('user_id') ?: 
                      $this->CI->session->userdata('userid');
            
            // Debug: log what we found
            log_message('debug', 'ActivityLogger: User ID found: ' . ($user_id ?: 'NULL'));
            log_message('debug', 'ActivityLogger: Session data: ' . json_encode($this->CI->session->userdata()));
            
            // If no user ID, don't log
            if (!$user_id) {
                log_message('debug', 'ActivityLogger: No user ID found, skipping log');
                return false;
            }
            
            // Get current route if not provided
            if (empty($route)) {
                $route = $this->CI->uri->uri_string();
            }
            
            // Get module if not provided
            if (empty($module)) {
                $module = $this->CI->uri->segment(1);
            }
            
            // Get IP address
            $ip_address = $this->CI->input->ip_address();
            
            // Prepare data for insertion
            $log_data = array(
                'fk_user_id' => $user_id,
                'activity' => $activity,
                'module' => $module,
                'route' => $route,
                'ip_address' => $ip_address,
                'created_at' => date('Y-m-d H:i:s')
            );
            
            log_message('debug', 'ActivityLogger: Attempting to log: ' . json_encode($log_data));
            
            // Check if activity_log table exists
            if (!$this->CI->db->table_exists('activity_log')) {
                log_message('error', 'Activity Logger: activity_log table does not exist');
                return false;
            }
            
            // Insert into activity_log table
            $this->CI->db->insert('activity_log', $log_data);
            
            $affected_rows = $this->CI->db->affected_rows();
            log_message('debug', 'ActivityLogger: Insert result - affected rows: ' . $affected_rows);
            
            if ($affected_rows > 0) {
                log_message('debug', 'ActivityLogger: Successfully logged activity');
                return true;
            } else {
                log_message('debug', 'ActivityLogger: No rows affected during insert');
                return false;
            }
            
        } catch (Exception $e) {
            log_message('error', 'Activity Logger Error: ' . $e->getMessage());
            log_message('error', 'Activity Logger Error Stack: ' . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Log page views automatically
     */
    public function log_page_view() {
        try {
            // Check if database is available
            if (!$this->CI->db->conn_id) {
                log_message('debug', 'ActivityLogger: Database not available yet');
                return;
            }
            
            // Check if session is available
            if (!$this->CI->session) {
                log_message('debug', 'ActivityLogger: Session not available yet');
                return;
            }
            
            $current_route = $this->CI->uri->uri_string();
            
            // Skip logging for certain routes (like AJAX calls, assets, etc.)
            $skip_routes = array(
                'assets',
                'uploads',
                'auth/checkSession',
                'admin/getLogs', // Avoid logging the logs page itself
                'lists/getFacilities', // Skip AJAX calls
                'employees', // Skip AJAX calls
                'employees/district_employees', // Skip AJAX calls
                'auth/refreshCsrf' // Skip CSRF refresh calls
            );
            
            foreach ($skip_routes as $skip_route) {
                if (strpos($current_route, $skip_route) === 0) {
                    log_message('debug', 'ActivityLogger: Skipping route: ' . $current_route);
                    return;
                }
            }
            
            // Check if user is logged in
            if (!$this->CI->session->userdata('isLoggedIn')) {
                log_message('debug', 'ActivityLogger: User not logged in, skipping page view logging');
                return;
            }
            
            // Log the page view
            $result = $this->log_activity('Page viewed', $this->CI->uri->segment(1), $current_route);
            
            if ($result) {
                log_message('debug', 'ActivityLogger: Successfully logged page view for route: ' . $current_route);
            } else {
                log_message('debug', 'ActivityLogger: Failed to log page view for route: ' . $current_route);
            }
            
        } catch (Exception $e) {
            log_message('error', 'ActivityLogger log_page_view error: ' . $e->getMessage());
        }
    }
    
    /**
     * Log specific actions
     */
    public function log_action($action, $details = '') {
        $activity = $action;
        if (!empty($details)) {
            $activity .= ': ' . $details;
        }
        
        $this->log_activity($activity);
    }
    
    /**
     * Log login/logout events
     */
    public function log_auth_event($event_type, $username = '') {
        $activity = ucfirst($event_type);
        if (!empty($username)) {
            $activity .= ' - User: ' . $username;
        }
        
        $this->log_activity($activity, 'auth');
    }
    
    /**
     * Log CRUD operations
     */
    public function log_crud($operation, $table, $record_id = '', $details = '') {
        $activity = ucfirst($operation) . ' ' . $table;
        if (!empty($record_id)) {
            $activity .= ' (ID: ' . $record_id . ')';
        }
        if (!empty($details)) {
            $activity .= ' - ' . $details;
        }
        
        $this->log_activity($activity);
    }
}
