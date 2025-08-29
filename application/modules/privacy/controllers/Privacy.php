<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Privacy extends MX_Controller
{


	public function __Construct()
	{

		parent::__Construct();

	
	}


	public function index()
	{
		$data['title'] = "MoH HRM Attend Privacy Policy";
		$data['uptitle'] = "MoH HRM Attend Privacy Policy";
		$data['module'] = 'privacy';
		$data['view'] = "privacy";
		
	
			echo Modules::run('templates/main2', $data);
		
	}
	
}
