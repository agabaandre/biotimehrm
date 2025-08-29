<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Utility extends MX_Controller {

	protected $module;
		public function __construct()
        {
                parent::__construct();

                $this->load->model('utility_mdl');
                $this->module="utility";//current module

             //   Modules::run("auth/isLegal");

                
        }


//appends ... on text if the text is to long, it takes the text and max desired char
	public function truncate($mytext,$mychars){

		$charlength=intval(strlen($mytext));

		if($charlength  > $mychars){

			$nexttext=substr($mytext, 0,$mychars)."...";

		} 
		else if($charlength <= $mychars){

			$nexttext=$mytext;
		}

		return $nexttext;
		
	}

	public function setFlash($msg){

		$this->session->set_flashdata('msg',$msg);

	}


	public function showBusy()
	{
		 return "<center><img src='".base_url()."assets/images/busy.gif' width='60px'/><center>";
	}



}
