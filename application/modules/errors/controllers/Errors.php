<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Errors extends MX_Controller {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function page_missing() {
        // Set HTTP status code to 404
        $this->output->set_status_header(404);
        
        // Load the 404 error view
        $this->load->view('errors/error404');
    }
    
    public function error_500() {
        // Set HTTP status code to 500
        $this->output->set_status_header(500);
        
        // Load the 500 error view
        $this->load->view('errors/error500');
    }
}

