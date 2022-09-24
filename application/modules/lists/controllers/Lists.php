<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lists extends MX_Controller {

	
	public function __Construct(){

		parent::__Construct();

		$this->load->model('districts_mdl');
		$this->load->model('facilities_mdl');

	}


//DISTRICTS-----------
	public function getDistricts(){

		$data['districts'] = $this->districts_mdl->getDistricts();
		$data['module'] = "lists";
		$data['title'] = "";
    	$data['view'] = 'districts/districts';

        echo Modules::run("templates/main", $data);
	}

	public function getDistrict($id){

		$district=$this->districts_mdl->getDistrict($id);
		return $district;
	}

	public function get_all_districts(){	
		return $this->districts_mdl->getDistricts();
	}

	public function getFacility($id){

		$facility=$this->facilities_mdl->getFacilitiesByDistrict($id);

		return $facility;
	}

	public function add_Districts(){
		$data['view']="add_districts";
		$data['title']="Districts";
		$data['module']="districts";

		echo Modules::run('templates/main',$data);
	}

	public function save_district(){

		$data = $this->input->post();
		$distr=$this->districts_mdl->save_district($data);

		redirect('lists/getDistricts');
	}

	public function updateDistrict(){

		$data = $this->input->post();
		$this->districts_mdl->updateDistrict($data);
		redirect('lists/getDistricts');
	}


	public function deleteDistrict(){
		
		$data = $this->input->post();
		$distr_delete=$this->districts_mdl->deleteDistrict($data);
		redirect('lists/getDistricts');

	}


//FACILITIES	
	public function getFacilities(){

		$data['facilities'] = $this->facilities_mdl->getAll();
		$data['districts'] = $this->districts_mdl->getDistricts();
		$data['module'] = "lists";
		$data['title'] = "";
    	$data['view'] = 'facilities/facilities';

        echo Modules::run("templates/main", $data);
	}



	public function get_all_Facilities(){

		return $this->facilities_mdl->getAll();
	}




	public function saveFacility(){

		$data = $this->input->post();
		$distr=$this->facilities_mdl->saveFacility($data);

		redirect('lists/getFacilities');
	}
	



}
