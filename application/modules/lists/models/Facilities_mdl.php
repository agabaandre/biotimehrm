<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Facilities_mdl extends CI_Model {

	protected $table;
	
	public function __Construct(){

		parent::__construct();

        $this->table="employee_facility";

	}


	public function getAll()
	{
		
		$this->db->select('distinct(facility_id),facility');
		//	$this->db->where("district_id!=''");
		$this->db->order_by('facility', 'ASC');
		$query = $this->db->get('ihrisdata');

		return $query->result();
 
	}

	public function getFacilitiesByDistrict($district_id=FALSE)
	{
		$query = $this->db->select('f.id as fac_id, f.facility_id, f.facility, f.district_id, d.name,d.region')
     	->from('employee_facility as f')
		->where('district_id',$district_id)
     	->join('employee_districts as d', 'd.id = f.district_id', 'LEFT')
     	->get();

		return $query->result();
 
	}

	public function get_facility()
	{
		$district=$_SESSION['district'];
		
		if($district!==""){
		$query=$this->db->query("select distinct facility_id,facility,district_id from ihrisdata where district_id='$district' order by facility ASC");
		
		}
		
		else
		{
		  $query=$this->db->query("select distinct facility_id,facility,district_id from ihrisdata order by facility ASC");  
		    
		}

		$res=$query->result_array();

		return $res;
   
	}


    // to save in the facility /.....
	public function saveFacility($postdata){

		$data=array(
		'facility_id'=>$postdata['facility_id'],
		'facility'=>$postdata['facility'],
		'institution_category'=>$postdata['institution_category'],
		'institution_type'=>$postdata['institution_type'],
		'institution_level'=>$postdata['institution_level'],
		'district_id'=>$postdata['district_id']
		);

		$qry=$this->db->insert($this->table, $data);
		$rows=$this->db->affected_rows();

		if($rows>0){

			return "Facility has been Added Successfully";
		}

		else{

			return "Operation failed";
		}

	}

	
	public function updateFacility(){

	    $data=$this->input->post('id');
		$this->db->where('id',$data);

		$this->db->update($this->table);
		$rows=$this->db->affected_rows();

		if($rows>0){

			return "The Facility has been updated";
		}

		else{

			return "No Operation made, seems like no changes made";
		}
	}

	


	public function deleteFacility(){

	    $data=$this->input->post('id');
		$this->db->where('id',$data);

		$this->db->delete($this->table);
		$rows=$this->db->affected_rows();

		if($rows>0){

			return "The facility has been updated";
		}

		else{

			return "No Operation made, seems like no changes made";
		}
	}
	
	/**
	 * Get total count of facilities
	 */
	public function getFacilitiesCount($search = '', $district_filter = '', $category_filter = '', $type_filter = '')
	{
		try {
			$this->db->select('COUNT(DISTINCT facility_id) as total');
			$this->db->from($this->table);
			
			if (!empty($search)) {
				$this->db->group_start();
				$this->db->like('facility', $search);
				$this->db->or_like('facility_id', $search);
				$this->db->or_like('institution_category', $search);
				$this->db->or_like('institution_type', $search);
				$this->db->or_like('institution_level', $search);
				$this->db->group_end();
			}
			
			if (!empty($district_filter)) {
				$this->db->where('district_id', $district_filter);
			}
			
			if (!empty($category_filter)) {
				$this->db->where('institution_category', $category_filter);
			}
			
			if (!empty($type_filter)) {
				$this->db->where('institution_type', $type_filter);
			}
			
			$query = $this->db->get();
			$result = $query->row();
			
			log_message('debug', 'Facilities count query: ' . $this->db->last_query());
			log_message('debug', 'Facilities count result: ' . ($result ? $result->total : 'null'));
			
			return $result ? $result->total : 0;
		} catch (Exception $e) {
			log_message('error', 'Facilities count error: ' . $e->getMessage());
			return 0;
		}
	}
	
	/**
	 * Get facilities for AJAX DataTables with pagination
	 */
	public function getFacilitiesAjax($start = 0, $length = 10, $search = '', $order_column = 0, $order_dir = 'asc', $district_filter = '', $category_filter = '', $type_filter = '')
	{
		try {
			$this->db->select('id, facility_id, facility, district_id, institution_category, institution_type, institution_level, created_at');
			$this->db->from($this->table);
			
			if (!empty($search)) {
				$this->db->group_start();
				$this->db->like('facility', $search);
				$this->db->or_like('facility_id', $search);
				$this->db->or_like('institution_category', $search);
				$this->db->or_like('institution_type', $search);
				$this->db->or_like('institution_level', $search);
				$this->db->group_end();
			}
			
			if (!empty($district_filter)) {
				$this->db->where('district_id', $district_filter);
			}
			
			if (!empty($category_filter)) {
				$this->db->where('institution_category', $category_filter);
			}
			
			if (!empty($type_filter)) {
				$this->db->where('institution_type', $type_filter);
			}
			
			// Apply ordering
			$columns = ['id', 'facility_id', 'facility', 'district_id', 'institution_category', 'institution_type', 'institution_level'];
			if (isset($columns[$order_column])) {
				$this->db->order_by($columns[$order_column], $order_dir);
			}
			
			// Apply pagination
			$this->db->limit($length, $start);
			
			$query = $this->db->get();
			$result = $query->result();
			
			log_message('debug', 'Facilities AJAX query: ' . $this->db->last_query());
			log_message('debug', 'Facilities AJAX result count: ' . count($result));
			
			// Format data for DataTables
			$formatted_data = [];
			foreach ($result as $row) {
				$formatted_data[] = [
					'id' => $row->id,
					'facility' => $row->facility,
					'district_name' => $this->_getDistrictName($row->district_id),
					'institution_category' => $row->institution_category ?: 'N/A',
					'institution_type' => $row->institution_type ?: 'N/A',
					'institution_level' => $row->institution_level ?: 'N/A'
				];
			}
			
			return $formatted_data;
		} catch (Exception $e) {
			log_message('error', 'Facilities AJAX error: ' . $e->getMessage());
			return [];
		}
	}
	
	/**
	 * Get district name by ID
	 */
	private function _getDistrictName($district_id)
	{
		if (empty($district_id)) return 'N/A';
		
		// Try to get from employee_districts table first
		$this->db->select('name, district');
		$this->db->from('employee_districts');
		$this->db->where('id', $district_id);
		$this->db->limit(1);
		
		$query = $this->db->get();
		$result = $query->row();
		
		if ($result) {
			return $result->name ?: $result->district ?: 'N/A';
		}
		
		// If not found, return the district_id as is
		return $district_id;
	}
}
