<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MX_Controller {

	
	public  function __construct(){
		parent:: __construct();

		
			$this->load->model("dashboard_mdl",'dash_mdl');

			}

	public function index()
	{
		$data['module']= "dashboard";
		$data['title']="Main Dashboard";
		$data['uptitle']="Main Dashboard";
		$data['view']="home";
		//$data['dashboard']=$this->dashboardData();

		echo Modules::run('templates/main',$data);
	}
	public function dashboardData(){
		
	return $this->db->get('dash_data')->row()->data;

	}




}
