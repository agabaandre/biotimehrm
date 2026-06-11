<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Districts_mdl extends CI_Model {

	protected $table;
	
	public function __construct(){

		parent::__construct();
		$this->table="employee_districts";

	}

	public function getDistricts(){

		$this->db->select('name, region,id, date_added');
	           $this->db->order_by('name', 'ASC');
		$query=$this->db->get('employee_districts');

		return $query->result();
 
	}

	/**
	 * Distinct non-empty region names from employee_districts.
	 *
	 * @return string[]
	 */
	public function getDistinctRegions()
	{
		$this->db->distinct();
		$this->db->select('region');
		$this->db->where('region !=', '');
		$this->db->where('region IS NOT NULL', null, false);
		$this->db->order_by('region', 'ASC');
		$query = $this->db->get($this->table);

		$regions = [];
		foreach ($query->result() as $row) {
			$region = trim((string) $row->region);
			if ($region !== '') {
				$regions[] = $region;
			}
		}

		return $regions;
	}
	
	public function switch_all_Districts()
	{
		$this->load->library('facility_switch_cache', null, 'fsc');
		return $this->fsc->get_districts();
	}

	public function get_all_Districts()
	{

		$this->db->select('distinct(district_id),district');
		//	$this->db->where("district_id!=''");
		$this->db->order_by('district', 'ASC');
		$query = $this->db->get('ihrisdata');

		return $query->result();
	}
		// to save in the district database /.....
	public function districtNameExists($name, $exclude_id = null)
	{
		$name = trim((string) $name);
		if ($name === '') {
			return false;
		}

		$this->db->from($this->table);
		$this->db->where('LOWER(TRIM(name))', strtolower($name));
		if ($exclude_id !== null) {
			$this->db->where('id !=', (int) $exclude_id);
		}

		return $this->db->count_all_results() > 0;
	}

	public function save_district($postdata){

		$name = trim((string) ($postdata['name'] ?? ''));
		$region = trim((string) ($postdata['region'] ?? ''));

		if ($name === '') {
			return 'District name is required.';
		}

		if ($this->districtNameExists($name)) {
			return 'A district with this name already exists.';
		}

		$data=array(
		'name'=>$name,
		'region'=>$region
		);

		$this->db->insert($this->table, $data);
		$rows=$this->db->affected_rows();

		if($rows>0){

			return "District has been Added Successfully";
		}

		$err = $this->db->error();
		if (!empty($err['code']) && in_array((int) $err['code'], [1062, 1586, 1022], true)) {
			return 'A district with this name already exists.';
		}

		return "Operation failed";
	}

	public function getDistrict($id){

		$this->db->select('district');
		$this->db->where('district_id',$id);
		$query=$this->db->get('ihrisdata');

		$result=$query->row();

		return $result->district;
 
	}
	
	
	public function getFacility($id){

		$this->db->select('facility');
		$this->db->where('facility_id',$id);
		$query=$this->db->get('ihrisdata');

		$result=$query->row();

		return $result->facility;
 
	}


	public function getFacilities($districtid){

		$this->db->select('distinct(facility_id),facility');
		$this->db->where('district_id',$districtid);
		$query=$this->db->get('ihrisdata');

		$result=$query->result();

		return $result;
 
	}

	//this gets all districts from the district table
	public function getAll_Districts(){

		$query=$this->db->get($this->table);

		return $query->result();
 
	}

	
	public function updateDistrict($postdata){

	    $id = isset($postdata['id']) ? (int) $postdata['id'] : 0;
		if ($id <= 0) {
			return 'Invalid district';
		}

		$data = array(
			'name'   => isset($postdata['name']) ? trim((string) $postdata['name']) : '',
			'region' => isset($postdata['region']) ? trim((string) $postdata['region']) : '',
		);

		if ($data['name'] === '') {
			return 'District name is required.';
		}

		if ($this->districtNameExists($data['name'], $id)) {
			return 'A district with this name already exists.';
		}

		$this->db->where('id', $id);
		$this->db->update($this->table, $data);
		$rows = $this->db->affected_rows();

		if ($rows > 0) {
			return 'The ' . $data['name'] . ' district has been updated';
		}

		return 'No changes made';
	}
	 

	 public function deleteDistrict($postdata = null){

	    $id = $postdata !== null && isset($postdata['id'])
			? $postdata['id']
			: $this->input->post('id');
		$this->db->where('id', $id);
		$this->db->delete($this->table);

		$rows = $this->db->affected_rows();
		if($rows>0){

			return "The district has been deleted";
		}

		else{

			return "No Operation made, seems like no changes made";
		}
	}



	


}
