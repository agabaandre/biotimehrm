<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Adldap {
    private $ci;
    private $conn;
    
    public function __construct() {
        $this->ci =& get_instance();
        $this->ci->load->config('adldap');
        $this->connect();
    }
    
    private function connect() {
        // Load CodeIgniter instance
        $this->ci =& get_instance();
    
        // Load LDAP configuration
        $ldap_host = $this->ci->config->item('ldap_host');
        $ldap_port = $this->ci->config->item('ldap_port');
        $ldap_user = $this->ci->config->item('ldap_user');
        $ldap_pass = $this->ci->config->item('ldap_pass');
    
        // Ensure LDAP host and port are set
        if (empty($ldap_host) || empty($ldap_port)) {
            log_message('error', 'LDAP host or port is missing.');
            return false;
        }
    
        // Connect to the LDAP server
        $this->conn = @ldap_connect($ldap_host, $ldap_port);
    
        if (!$this->conn) {
            log_message('error', 'Failed to connect to LDAP server: ' . $ldap_host);
            return false;
        }
    
        // Set LDAP protocol options
        ldap_set_option($this->conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->conn, LDAP_OPT_REFERRALS, 0);
    
        // Attempt to bind with the provided credentials
        if (!@ldap_bind($this->conn, $ldap_user, $ldap_pass)) {
            log_message('error', 'LDAP bind failed for user: ' . $ldap_user);
            return false;
        }
    
        return true;
    }
    
    
    public function search($filter, $attributes = array()) {
        $base_dn = $this->ci->config->item('ldap_base_dn');
        $result = ldap_search($this->conn, $base_dn, $filter, $attributes);
        return ldap_get_entries($this->conn, $result);
    }
    
    public function modify($dn, $entry) {
        return ldap_modify($this->conn, $dn, $entry);
    }
}
