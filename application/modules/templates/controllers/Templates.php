<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Templates extends MX_Controller
{




	public function main($data)
	{
		
		$data['setting'] = $this->db->get('setting')->row();
		$this->load->view('main', $data);
	}

	public function main2($data)
	{
		$data['setting'] = $this->db->get('setting')->row();

		$this->load->view('main2', $data);
	}
}
