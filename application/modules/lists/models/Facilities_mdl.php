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


	/**
	 * @param int $id
	 * @return object|null
	 */
	public function getFacilityById($id)
	{
		$id = (int) $id;
		if ($id <= 0) {
			return null;
		}

		$query = $this->db->get_where($this->table, ['id' => $id], 1);
		return $query->row();
	}

	/**
	 * Generate the next facility / school ID (e.g. edu1 -> edu2, or SCH0001).
	 *
	 * @return string
	 */
	public function generateNextFacilityId()
	{
		$this->db->select('facility_id');
		$this->db->from($this->table);
		$this->db->order_by('id', 'DESC');
		$this->db->limit(1);
		$last = $this->db->get()->row();

		if (!$last || trim((string) $last->facility_id) === '') {
			return 'SCH0001';
		}

		$last_id = trim((string) $last->facility_id);
		if (preg_match('/^(.*?)(\d+)$/', $last_id, $m)) {
			$width = strlen($m[2]);
			$next = (int) $m[2] + 1;
			return $m[1] . str_pad((string) $next, $width, '0', STR_PAD_LEFT);
		}

		$row = $this->db->select_max('id')->get($this->table)->row();
		$next = ($row && !empty($row->id)) ? ((int) $row->id + 1) : 1;

		return 'SCH' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
	}

    // to save in the facility /.....
	public function saveFacility($postdata){

		$facility_id = isset($postdata['facility_id']) ? trim((string) $postdata['facility_id']) : '';
		if ($facility_id === '') {
			$facility_id = $this->generateNextFacilityId();
		}

		$data=array(
		'facility_id'=>$facility_id,
		'facility'=>isset($postdata['facility']) ? trim((string) $postdata['facility']) : '',
		'institution_category'=>isset($postdata['institution_category']) ? $postdata['institution_category'] : '',
		'institution_type'=>isset($postdata['institution_type']) ? $postdata['institution_type'] : '',
		'institution_level'=>isset($postdata['institution_level']) ? $postdata['institution_level'] : '',
		'district_id'=>isset($postdata['district_id']) ? $postdata['district_id'] : ''
		);

		$qry=$this->db->insert($this->table, $data);
		$rows=$this->db->affected_rows();

		if($rows>0){

			return entity_label('entity_added');
		}

		else{

			return "Operation failed";
		}

	}

	
	public function updateFacility($postdata)
	{
		$id = isset($postdata['id']) ? (int) $postdata['id'] : 0;
		if ($id <= 0) {
			return 'Invalid ' . strtolower(entity_label('facility'));
		}

		$data = array(
			'facility'             => isset($postdata['facility']) ? trim((string) $postdata['facility']) : '',
			'district_id'          => isset($postdata['district_id']) ? $postdata['district_id'] : '',
			'institution_category' => isset($postdata['institution_category']) ? $postdata['institution_category'] : '',
			'institution_type'     => isset($postdata['institution_type']) ? $postdata['institution_type'] : '',
			'institution_level'    => isset($postdata['institution_level']) ? $postdata['institution_level'] : '',
		);

		$this->db->where('id', $id);
		$this->db->update($this->table, $data);
		$rows = $this->db->affected_rows();

		if ($rows > 0) {
			return entity_label('entity_updated');
		}

		return 'No changes made';
	}

	public function deleteFacilityById($id)
	{
		$id = (int) $id;
		if ($id <= 0) {
			return 'Invalid ' . strtolower(entity_label('facility'));
		}

		$this->db->where('id', $id);
		$this->db->delete($this->table);
		$rows = $this->db->affected_rows();

		if ($rows > 0) {
			return entity_label('entity_deleted');
		}

		return entity_label('entity_delete_failed');
	}
	
	/**
	 * Get total count of facilities
	 */
	public function getFacilitiesCount($search = '', $district_filter = '', $category_filter = '', $type_filter = '')
	{
		try {
			$this->db->select('COUNT(*) as total', false);
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
			
			// Column indices match DataTables columns on facilities page.
			$columns = ['id', 'facility', 'district_id', 'institution_category', 'institution_type', 'institution_level'];
			if (isset($columns[$order_column])) {
				$this->db->order_by($columns[$order_column], $order_dir);
			} else {
				$this->db->order_by('facility', 'asc');
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
		if (empty($district_id)) {
			return 'N/A';
		}

		$this->db->select('name');
		$this->db->from('employee_districts');
		$this->db->where('id', $district_id);
		$this->db->limit(1);

		$query = $this->db->get();
		if (!$query) {
			return (string) $district_id;
		}

		$result = $query->row();
		if ($result && !empty($result->name)) {
			return $result->name;
		}

		return (string) $district_id;
	}
}
