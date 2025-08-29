<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Session Keep Alive Hook
 * Extends session lifetime during active user activity
 */
class SessionKeepAlive {
    
    protected $CI;
    
    public function __construct() {
        $this->CI =& get_instance();
    }
    
    /**
     * Extend session lifetime if user is active
     */
    public function extendSession() {
        // Only run for authenticated users
        if (!$this->CI->session->userdata('isLoggedIn')) {
            return;
        }
        
        // Get current session data
        $session_id = $this->CI->session->session_id;
        $user_id = $this->CI->session->userdata('user_id') ?: $this->CI->session->userdata('id');
        
        if (!$session_id || !$user_id) {
            return;
        }
        
        // Update session timestamp in database
        $this->_updateSessionTimestamp($session_id, $user_id);
        
        // Extend session cookie
        $this->_extendSessionCookie();
    }
    
    /**
     * Update session timestamp in database
     */
    private function _updateSessionTimestamp($session_id, $user_id) {
        try {
            // Find the session by IP address and user data
            $this->CI->db->where('ip_address', $this->CI->input->ip_address());
            $this->CI->db->where('timestamp >', time() - 86400); // Within last 24 hours
            
            // Update the session timestamp to extend lifetime
            $this->CI->db->update('access_sessions', [
                'timestamp' => time()
            ]);
        } catch (Exception $e) {
            log_message('error', 'Session update error: ' . $e->getMessage());
        }
    }
    
    /**
     * Extend session cookie lifetime
     */
    private function _extendSessionCookie() {
        $cookie_name = $this->CI->config->item('sess_cookie_name');
        $cookie_expire = time() + $this->CI->config->item('sess_expiration');
        $cookie_path = $this->CI->config->item('cookie_path');
        $cookie_domain = $this->CI->config->item('cookie_domain');
        $cookie_secure = $this->CI->config->item('cookie_secure');
        $cookie_httponly = $this->CI->config->item('cookie_httponly');
        
        // Set the session cookie with extended expiration
        setcookie(
            $cookie_name,
            $this->CI->session->session_id,
            $cookie_expire,
            $cookie_path,
            $cookie_domain,
            $cookie_secure,
            $cookie_httponly
        );
    }
    
    /**
     * Check if session is about to expire and extend if needed
     */
    public function checkSessionExpiry() {
        if (!$this->CI->session->userdata('isLoggedIn')) {
            return;
        }
        
        $session_id = $this->CI->session->session_id;
        if (!$session_id) {
            return;
        }
        
        try {
            // Check session age by IP address
            $this->CI->db->select('timestamp');
            $this->CI->db->where('ip_address', $this->CI->input->ip_address());
            $this->CI->db->where('timestamp >', time() - 86400); // Within last 24 hours
            $session_data = $this->CI->db->get('access_sessions')->row();
            
            if ($session_data) {
                $session_age = time() - $session_data->timestamp;
                $expiration = $this->CI->config->item('sess_expiration');
                
                // If session is more than 75% expired, extend it
                if ($expiration && $session_age > ($expiration * 0.75)) {
                    $this->extendSession();
                }
            }
        } catch (Exception $e) {
            log_message('error', 'Session expiry check error: ' . $e->getMessage());
        }
    }
}
