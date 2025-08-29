<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Facility Change Hook
 * Automatically detects when a user switches facilities and clears related cache
 */
class FacilityChangeHook {
    
    protected $CI;
    
    public function __construct() {
        $this->CI =& get_instance();
    }
    
    /**
     * Check for facility changes and clear cache if needed
     */
    public function checkFacilityChange() {
        // Only run this hook for dashboard-related requests
        $uri = $this->CI->uri->uri_string();
        if (strpos($uri, 'dashboard') === false) {
            return;
        }
        
        // Check if user is logged in
        if (!$this->CI->session->userdata('isLoggedIn')) {
            return;
        }
        
        $current_facility = $this->CI->session->userdata('facility');
        $previous_facility = $this->CI->session->userdata('previous_facility');
        
        // If this is the first time or facility changed
        if (!$previous_facility || $previous_facility != $current_facility) {
            // Clear cache for the previous facility if it exists
            if ($previous_facility) {
                $this->_clearFacilityCache($previous_facility);
                log_message('info', 'Facility changed from ' . $previous_facility . ' to ' . $current_facility . ' - cache cleared');
            }
            
            // Update the previous facility in session
            $this->CI->session->set_userdata('previous_facility', $current_facility);
            
            // Clear any existing cache for current facility to ensure fresh data
            $this->_clearCurrentFacilityCache($current_facility);
        }
    }
    
    /**
     * Clear all cache entries for a specific facility
     */
    private function _clearFacilityCache($facility) {
        try {
            // Try to clear cache using available cache drivers
            if (isset($this->CI->cache->memcached) && method_exists($this->CI->cache->memcached, 'is_supported') && $this->CI->cache->memcached->is_supported()) {
                // Clear all dashboard cache for the facility
                $cache_keys = [
                    'dashboard_' . $facility . '_' . date('Y-m-d'),
                    'dashboard_essential_' . $facility . '_' . date('Y-m-d'),
                    'dashboard_' . $facility . '_' . date('Y-m-d', strtotime('-1 day')),
                    'dashboard_essential_' . $facility . '_' . date('Y-m-d', strtotime('-1 day'))
                ];
                
                foreach ($cache_keys as $key) {
                    $this->CI->cache->memcached->delete($key);
                }
            } else {
                // Fallback: clear file cache if available
                $cache_path = APPPATH . 'cache/';
                if (is_dir($cache_path)) {
                    $files = glob($cache_path . 'dashboard_' . $facility . '_*');
                    foreach ($files as $file) {
                        if (is_file($file)) {
                            unlink($file);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            log_message('error', 'Cache clear error: ' . $e->getMessage());
        }
    }
    
    /**
     * Clear current facility cache to ensure fresh data
     */
    private function _clearCurrentFacilityCache($facility) {
        try {
            // Try to clear cache using available cache drivers
            if (isset($this->CI->cache->memcached) && method_exists($this->CI->cache->memcached, 'is_supported') && $this->CI->cache->memcached->is_supported()) {
                // Clear current day cache for the facility
                $cache_keys = [
                    'dashboard_' . $facility . '_' . date('Y-m-d'),
                    'dashboard_essential_' . $facility . '_' . date('Y-m-d')
                ];
                
                foreach ($cache_keys as $key) {
                    $this->CI->cache->memcached->delete($key);
                }
            } else {
                // Fallback: clear file cache if available
                $cache_path = APPPATH . 'cache/';
                if (is_dir($cache_path)) {
                    $files = glob($cache_path . 'dashboard_' . $facility . '_' . date('Y-m-d') . '*');
                    foreach ($files as $file) {
                        if (is_file($file)) {
                            unlink($file);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            log_message('error', 'Cache clear error: ' . $e->getMessage());
        }
    }
}
