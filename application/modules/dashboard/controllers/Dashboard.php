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

      if (($this->cache->memcached->get('dashboard')) && ($this->session->userdata('facility')==$this->cache->memcached->get('facility'))) {
    // Data not found in cache, perform your data retrieval or processing logic here
		$data = $this->cache->memcached->get('dashboard');
    // Store the processed data in the cache
		} 
		else {
		$data = $this->dash_mdl->getData();
		$this->cache->memcached->save('dashboard', $data, 13600);
				
		}

		
	echo json_encode($data);
	}
	public function cache_stats(){
		//$data = $this->dash_mdl->getData();
		$data =array();
		$cached = $this->cache->memcached->save('dashboard', $data, 13600); // MemCache for 1 hour
		if ($cached){
			echo "Success";
		}
		else{
			echo "failed";
		}
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
