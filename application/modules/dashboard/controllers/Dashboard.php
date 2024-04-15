<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MX_Controller {

	
	public  function __construct(){
		parent:: __construct();

			@$this->dashmodule="dashboard";
			$this->load->model("dashboard_mdl",'dash_mdl');
		  


	}

	public function index()
	{
		$data['module']=$this->dashmodule;
		$data['title']="Main Dashboard";
		$data['uptitle']="Main Dashboard";
		$data['view']= "home";
		echo Modules::run('templates/main',$data);
	}
	public function dashboardData(){

      if ((!$cached_data = $this->cache->memcached->get('dashboard')) && ($this->session->userdata('facility')==$this->cache->memcached->get('facility'))) {
    // Data not found in cache, perform your data retrieval or processing logic here
        $data = $this->dash_mdl->getData();
    

    // Store the processed data in the cache
		$this->cache->memcached->save('dashboard', $data, 13600); // Cache for 1 hour
		} else {
			// Data found in cache, return it directly
			$data = $cached_data;
		}

		
	echo json_encode($data);
	}
	public function test_cache(){
		$data = $this->cache->memcached->get('dashboard');
		print_r($this->session->userdata());
	dd($data);
	}
	public function get_dashboard()
	{
		$html_content = $this->load->view('home', NULL, TRUE);
		$response = [
			'html' => $html_content
		];

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($response));
	}

	



}
