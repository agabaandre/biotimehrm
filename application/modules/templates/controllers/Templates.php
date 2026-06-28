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

	/**
	 * Full-screen layout for facility TV / wall displays (no sidebar).
	 */
	public function tv($data)
	{
		$userdata = $this->session->get_userdata();
		if (!isset($userdata['names'])) {
			redirect('auth');
		}
		$data['setting'] = $this->db->get('setting')->row();
		date_default_timezone_set('Africa/Kampala');
		$this->load->view('tv', $data);
	}
}
