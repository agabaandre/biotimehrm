<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Facilities extends MX_Controller {

	
	public function __Construct(){

		parent::__Construct();

		$this->load->model('facilities_mdl','facMdl');

	}


	public function getFacilities($district=FALSE){

		$faci_options="";

		$facilities=$this->facMdl->getFacilities($district);

   
		$faci_options.="<option disabled selected>--Select Facility--</option>";

		foreach ($facilities as $facility):

			$faci_options.='<option value="'.htmlspecialchars($facility->facility_id).'">'.$facility->facility.'</option>';
		
		endforeach;


		//print_r($faci_options);


	}

	public function add_facility(){
		$data['view']="add_faclity";
		$data['title']="Facilities";
		$data['module']="facilities";

		echo Modules::run('templates/main',$data);
	}

	public function getAll_Facilities(){

		$facil=$this->facMdl->getAll_Facilities();

      	return $facil;

      //print_r($depart);

	}
	public function saveFacility(){
		$save_facil=$this->facMdl->saveFacility();

      	//return $save_facil;

      	redirect('facilities/add_facility');

	}
	public function updateFacility(){

		$update_facil=$this->facMdl->updateFacility();

      	//return $update_facil;

      	redirect('facilities/add_facility');


	}

	public function deleteFacility(){

		$delete_facil=$this->facMdl->deleteFacility();

      	//return $delete_facil;

      	redirect('facilities/add_facility');


	}
	


}
