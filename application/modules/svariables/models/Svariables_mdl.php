<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Svariables_mdl extends CI_Model {

	
	protected $table;
	protected $user;

	public function __construct() {
		parent::__construct();
		$this->table = "variables";
		$this->user = $this->session->get_userdata();
	}

	public function update_variables($data){
		        $this->db->where('id',$data['id']);
        $query = $this->db->update('setting',$data);

        if($query){
            echo "Sucessful";
        }
        else{
            echo "Failed";
        }
     
     }
	 public function getSettings(){
		return $this->db->get('setting')->row();
	 }
	}