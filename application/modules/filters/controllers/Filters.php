
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Filters extends MX_Controller{
	
	public  function __construct(){
		parent:: __construct();
        $this->district_id=$this->session->userdata['district_id'];
        $this->facility_id=$this->session->userdata['facility'];
        $this->department_id=$this->session->userdata['department_id'];
        $this->division=$this->session->userdata['division'];
        $this->section=$this->session->userdata['section'];
        $this->unit=$this->session->userdata['unit'];
       
     
	}


   public function sessionfilters()
    {
        $facility_id=$this->facility_id;
        $department_id=$this->department_id;
        $division=$this->division;
        $unit=$this->unit;
        $section=$this->section;

        if(!empty($facility_id)){
            $facility="ihrisdata.facility_id='$facility_id'";
        }
        else{
            $facility="";
        }
        
        if(!empty($department_id)){
            $department="and ihrisdata.department_id='$department_id'";
        }
        else{
            $department="";
        }
        if(!empty($division)){
            $division="and ihrisdata.division='$division'";
        }
        else{
            $division="";
        }
        if(!empty($section)){
            $section="and ihrisdata.division='$section'";
        }
        else{
            $section="";
        }

        if(!empty($unit)){
            $unit="and ihrisdata.unit='$unit'";
        }
        else{
            $unit="";
        }
return $facility.' '.$department.' '.$division.' '.$section.' '.$unit;
    }





}

