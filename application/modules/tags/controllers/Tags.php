<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tags extends MX_Controller
{


	public  function __construct()
	{
		parent::__construct();

		$this->module = "tags";
		$this->title  = "Search Tags";
	}

	public function index()
	{
		$data['module'] = $this->module;
		$data['title']  = $this->title;
		$data['page']   = "Search Tags";
		$data['tags'] = $this->tagsmodel->get();

		render('list', $data);
	}
	public function get()
	{

		$data['tags'] = $this->tagsmodel->get();

		return  $data;
	}

	public function save()
	{

		$is_error = false;

		$theme = [
			'id' => @$this->input->post("id"), 'name' => $this->input->post("name")
		];

		$resp = $this->tagsmodel->save($theme);

		$msg = "Operation Successful";
		// }
		set_flash($msg, $is_error);
		redirect(base_url("tags"));
	}

	public function delete($id)
	{

		$resp = $this->tagsmodel->delete($id);
		$is_error = false;

		if ($resp) {
			$msg = "Success";
		} else {
			$msg = "False";
		}

		die($msg);
	}
}
