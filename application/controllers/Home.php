<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

    /**
     * Main entry point - redirects based on login status
     */
    public function index() {
        // Check if user is logged in
        if ($this->session->userdata('isLoggedIn')) {
            // User is logged in, redirect to dashboard
            log_message('debug', 'Home controller: User is logged in, redirecting to dashboard');
            redirect('dashboard');
        } else {
            // User is not logged in, redirect to login page
            log_message('debug', 'Home controller: User is not logged in, redirecting to auth');
            redirect('auth');
        }
    }
    
    /**
     * Test method to verify routing is working
     */
    public function test() {
        echo "Home controller is working!<br>";
        echo "Session data: " . print_r($this->session->userdata(), true);
    }
}
