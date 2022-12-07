<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Utility_mdl extends CI_Model {

	
	public function __construct()
        {
                parent::__construct();

               
                $this->sliders_tb="sliders";//classes table

          

                
        }



        public function getSliders($page){

        	$this->db->where('page',$page);
                $query=$this->db->get($this->sliders_tb);

        	$sliders=$query->result();

        	return $sliders;
        }





}
