<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Lists extends MX_Controller
{


	public function __Construct()
	{

		parent::__Construct();

		$this->load->model('districts_mdl');
		$this->load->model('facilities_mdl');
		$this->load->model('cadre_mdl');
		$this->load->model('jobs_mdl');
	}


	//DISTRICTS-----------
	public function getDistricts()
	{
		$data['districts'] = $this->districts_mdl->getDistricts();
		$data['module'] = "lists";
		$data['title'] = "";
		$data['view'] = 'districts/districts';
		echo Modules::run("templates/main", $data);
	}

	public function getDistrict($id)
	{
		$district = $this->districts_mdl->getDistrict($id);
		return $district;
	}

	public function get_all_districts()
	{
		return $this->districts_mdl->get_all_Districts();
	}

	public function add_Districts()
	{
		$data['view'] = "add_districts";
		$data['title'] = "Districts";
		$data['module'] = "districts";
		echo Modules::run('templates/main', $data);
	}

	public function save_district()
	{
		$data = $this->input->post();
		$distr = $this->districts_mdl->save_district($data);
		redirect('lists/getDistricts');
	}

	public function updateDistrict()
	{
		$data = $this->input->post();
		$this->districts_mdl->updateDistrict($data);
		redirect('lists/getDistricts');
	}


	public function deleteDistrict()
	{
		$data = $this->input->post();
		$distr_delete = $this->districts_mdl->deleteDistrict($data);
		redirect('lists/getDistricts');
	}

	//end DISTRICTS


	//FACILITIES
	public function getFacilities()
	{
		$data['facilities'] = $this->facilities_mdl->getAll();
		$data['districts'] = $this->districts_mdl->getDistricts();
		$data['module'] = "lists";
		$data['title'] = "";
		$data['view'] = 'facilities/facilities';
		echo Modules::run("templates/main", $data);
	}

	public function get_all_Facilities()
	{
		return $this->facilities_mdl->getAll();
	}

	public function getFacility($id)
	{
		$facility = $this->facilities_mdl->getFacilitiesByDistrict($id);
		return $facility;
	}

	public function saveFacility()
	{
		$data = $this->input->post();
		$distr = $this->facilities_mdl->saveFacility($data);
		redirect('lists/getFacilities');
	}

	//end FACILITIES

	//CADRE-----------
	public function getCadres()
	{
		$data['cadres'] = $this->cadre_mdl->getCadres();
		$data['module'] = "lists";
		$data['title'] = "";
		$data['view'] = 'cadre/cadre';
		echo Modules::run("templates/main", $data);
	}

	public function getCadre($id)
	{
		$district = $this->cadre_mdl->getCadre($id);
		return $district;
	}

	public function get_all_cadres()
	{
		return $this->cadre_mdl->getCadres();
	}

	public function add_Cadre()
	{
		$data['view'] = "add_cadre";
		$data['title'] = "Cadres";
		$data['module'] = "lists";
		echo Modules::run('templates/main', $data);
	}

	public function save_cadre()
	{
		$data = $this->input->post();
		$distr = $this->cadre_mdl->save_cadre($data);
		redirect('lists/getCadres');
	}

	public function updateCadre()
	{
		$data = $this->input->post();
		$this->cadre_mdl->updateCadre($data);
		redirect('lists/getCadres');
	}


	public function deleteCadre()
	{
		$data = $this->input->post();
		$this->cadre_mdl->deleteCadre($data);
		redirect('lists/getCadres');
	}

	//end CADRE


	//JOBS-----------
	public function getJobs()
	{
		$data['jobs'] = $this->jobs_mdl->getJobs();
		$data['module'] = "lists";
		$data['title'] = "";
		$data['view'] = 'jobs/jobs';
		echo Modules::run("templates/main", $data);
	}

	public function getJob($id)
	{
		$district = $this->jobs_mdl->getJob($id);
		return $district;
	}

	public function get_all_jobs()
	{
		return $this->jobs_mdl->getJobs();
	}

	public function saveJob()
	{
		$data = $this->input->post();
		$distr = $this->jobs_mdl->saveJob($data);
		redirect('lists/getJobs');
	}

	public function updateJob()
	{
		$data = $this->input->post();
		$this->jobs_mdl->updateJob($data);
		redirect('lists/getJobs');
	}

	public function deleteJob()
	{
		$data = $this->input->post();
		$this->jobs_mdl->deleteJob($data);
		redirect('lists/getJobs');
	}

	//end JOBS



}
