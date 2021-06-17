<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Districts extends MX_Controller {

	
	public function __Construct(){

		parent::__Construct();

		$this->load->model('districts_mdl');

	}



	public function getDistricts(){

		$districts=$this->districts_mdl->getDistricts();

		return $districts;

		//print_r($districts);
	}



	public function getDistrict($id){

		$district=$this->districts_mdl->getDistrict($id);

		return $district;

		//print_r($district);
	}



	public function getFacility($id){

		$facility=$this->districts_mdl->getFacility($id);

		return $facility;
	}



	public function getFacilities($did){

		$facilities=$this->districts_mdl->getFacilities($did);

		$faci_options=FALSE;
		$faci_options.="<option disabled selected>--Select Facility--</option>";

		foreach ($facilities as $facility):

			$faci_options.='<option value="'.$facility->facility_id.'">'.$facility->facility.'</option>';
		
		endforeach;

		//$faci_options .="</select>";

		print_r($faci_options);
	}


	//thiis gets all districts form the districts table 
	public function getAll_Districts(){

		$distr=$this->districts_mdl->getAll_Districts();

		return $distr;

		//print_r($distr);
	}

	public function add_Districts(){
		$data['view']="add_districts";
		$data['title']="Districts";
		$data['module']="districts";

		echo Modules::run('templates/main',$data);
	}

	public function save_district(){

		$distr=$this->districts_mdl->save_district();

		//return $distr;

		//print_r($distr);

		redirect('districts/add_Districts');
	}

	public function updateDistrict(){

		$distr_update=$this->districts_mdl->updateDistrict();

		//return $distr_update;
		redirect('districts/add_Districts');
	}


	public function deleteDistrict(){

		$distr_delete=$this->districts_mdl->deleteDistrict();

		//return $distr_delete;
		redirect('districts/add_Districts');

	}

}
