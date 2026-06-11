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

	/**
	 * Facilities/schools for the add-employee form (from employee_facility).
	 *
	 * @return array<int, object>
	 */
	public function getAllForEmployeeForm()
	{
		$this->db->select(
			'f.facility_id, f.facility, f.district_id, f.institution_category, f.institution_type, f.institution_level, d.name AS district_name',
			false
		);
		$this->db->from($this->table . ' f');
		$this->db->join('employee_districts d', 'd.id = f.district_id', 'LEFT');
		$this->db->order_by('f.facility', 'ASC');

		return $this->db->get()->result();
	}

	/**
	 * @param string $facility_id
	 * @return object|null
	 */
	public function getByFacilityId($facility_id)
	{
		$facility_id = trim((string) $facility_id);
		if ($facility_id === '') {
			return null;
		}

		return $this->db->get_where($this->table, ['facility_id' => $facility_id], 1)->row();
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
	 * Generate the next facility / school ID (e.g. SCH0001).
	 *
	 * @return string
	 */
	public function generateNextFacilityId()
	{
		$this->db->select('facility_id');
		$rows = $this->db->get($this->table)->result();

		$prefix = 'SCH';
		$width = 4;
		$max_num = 0;

		foreach ($rows as $row) {
			$facility_id = trim((string) $row->facility_id);
			if ($facility_id === '') {
				continue;
			}
			if (preg_match('/^(.*?)(\d+)$/', $facility_id, $m)) {
				$prefix = $m[1];
				$width = max($width, strlen($m[2]));
				$max_num = max($max_num, (int) $m[2]);
			}
		}

		return $prefix . str_pad((string) ($max_num + 1), $width, '0', STR_PAD_LEFT);
	}

	/**
	 * @param string $facility_id
	 * @param int|null $exclude_id
	 * @return bool
	 */
	public function facilityIdExists($facility_id, $exclude_id = null)
	{
		$facility_id = trim((string) $facility_id);
		if ($facility_id === '') {
			return false;
		}

		$this->db->from($this->table);
		$this->db->where('facility_id', $facility_id);
		if ($exclude_id !== null) {
			$this->db->where('id !=', (int) $exclude_id);
		}

		return $this->db->count_all_results() > 0;
	}

	/**
	 * @param int $errno
	 * @return bool
	 */
	private function isDuplicateKeyError($errno)
	{
		return in_array((int) $errno, [1062, 1586, 1022], true);
	}

    // to save in the facility /.....
	public function saveFacility($postdata){

		$district_id = (int) ($postdata['district_id'] ?? 0);
		$facility_name = trim((string) ($postdata['facility'] ?? ''));
		$provided_id = trim((string) ($postdata['facility_id'] ?? ''));

		if ($facility_name === '' || $district_id <= 0) {
			return 'Please provide a valid ' . strtolower(entity_label('facility')) . ' name and district.';
		}

		if ($this->facilityExistsInDistrict($facility_name, $district_id)) {
			return entity_label('facility') . ' name already exists in this district.';
		}

		if ($provided_id !== '' && $this->facilityIdExists($provided_id)) {
			return entity_label('entity_id') . ' already exists.';
		}

		$data = array(
			'facility'             => $facility_name,
			'institution_category' => isset($postdata['institution_category']) ? $postdata['institution_category'] : '',
			'institution_type'     => isset($postdata['institution_type']) ? $postdata['institution_type'] : '',
			'institution_level'    => isset($postdata['institution_level']) ? $postdata['institution_level'] : '',
			'district_id'          => $district_id,
		);

		$max_attempts = 5;
		for ($attempt = 0; $attempt < $max_attempts; $attempt++) {
			$facility_id = $provided_id !== '' ? $provided_id : $this->generateNextFacilityId();
			if ($this->facilityIdExists($facility_id)) {
				if ($provided_id !== '') {
					return entity_label('entity_id') . ' already exists.';
				}
				continue;
			}

			$data['facility_id'] = $facility_id;
			$this->db->insert($this->table, $data);

			if ($this->db->affected_rows() > 0) {
				return entity_label('entity_added');
			}

			$err = $this->db->error();
			if (!empty($err['code']) && $this->isDuplicateKeyError($err['code'])) {
				if ($provided_id !== '') {
					return entity_label('entity_id') . ' already exists.';
				}
				continue;
			}

			break;
		}

		return entity_label('entity_add_failed');
	}

	
	public function updateFacility($postdata)
	{
		$id = isset($postdata['id']) ? (int) $postdata['id'] : 0;
		if ($id <= 0) {
			return 'Invalid ' . strtolower(entity_label('facility'));
		}

		$district_id = (int) ($postdata['district_id'] ?? 0);
		$facility_name = isset($postdata['facility']) ? trim((string) $postdata['facility']) : '';

		if ($facility_name === '' || $district_id <= 0) {
			return 'Please provide a valid ' . strtolower(entity_label('facility')) . ' name and district.';
		}

		if ($this->facilityExistsInDistrict($facility_name, $district_id, $id)) {
			return entity_label('facility') . ' name already exists in this district.';
		}

		$data = array(
			'facility'             => $facility_name,
			'district_id'          => $district_id,
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
	 * Default values pre-filled in the import template sample row.
	 *
	 * @return array<string, string>
	 */
	public function importDefaultFieldValues()
	{
		return [
			'institution_category' => 'Government',
			'institution_type'     => 'District',
			'institution_level'    => 'Primary School',
		];
	}

	/**
	 * CSV column headers for the import template.
	 *
	 * @return array<int, string>
	 */
	public function importTemplateHeaders()
	{
		return [
			entity_label('entity_name'),
			'District',
			'Institution Category',
			'Institution Type',
			'Institution Level',
		];
	}

	/**
	 * Normalize a CSV header cell for comparison.
	 *
	 * @param string $label
	 * @return string
	 */
	public function normalizeImportHeaderKey($label)
	{
		$label = (string) $label;
		$label = preg_replace('/^\xEF\xBB\xBF/', '', $label);
		$label = str_replace("\xC2\xA0", ' ', $label);
		$label = strtolower(trim($label));
		$label = preg_replace('/\s+/', ' ', $label);

		return $label;
	}

	/**
	 * Accepted header aliases keyed by import field name.
	 *
	 * @return array<string, array<int, string>>
	 */
	public function importColumnAliases()
	{
		$name_aliases = [
			$this->normalizeImportHeaderKey(entity_label('entity_name')),
			'facility name',
			'school name',
			'facility',
			'school',
			'name',
		];

		return [
			'facility'             => array_values(array_unique($name_aliases)),
			'district'             => ['district', 'district name', 'home district'],
			'institution_category' => ['institution category', 'category', 'institution_category'],
			'institution_type'     => ['institution type', 'type', 'institution_type'],
			'institution_level'    => ['institution level', 'level', 'institution_level'],
		];
	}

	/**
	 * Map CSV header row to field indexes.
	 *
	 * @param array<int, string> $header_row
	 * @return array<string, int>|null
	 */
	public function mapImportColumns(array $header_row)
	{
		$normalized = [];
		foreach ($header_row as $i => $label) {
			$key = $this->normalizeImportHeaderKey($label);
			if ($key !== '') {
				$normalized[$key] = $i;
			}
		}

		$aliases = $this->importColumnAliases();
		$map = [];

		foreach (['facility', 'district'] as $required) {
			$index = null;
			foreach ($aliases[$required] as $alias) {
				if (isset($normalized[$alias])) {
					$index = $normalized[$alias];
					break;
				}
			}
			if ($index === null) {
				$map = null;
				break;
			}
			$map[$required] = $index;
		}

		if ($map !== null) {
			foreach (['institution_category', 'institution_type', 'institution_level'] as $optional) {
				foreach ($aliases[$optional] as $alias) {
					if (isset($normalized[$alias])) {
						$map[$optional] = $normalized[$alias];
						break;
					}
				}
			}

			return $map;
		}

		return $this->mapImportColumnsByTemplateOrder($header_row);
	}

	/**
	 * Fallback mapping using the downloaded template column order.
	 *
	 * @param array<int, string> $header_row
	 * @return array<string, int>|null
	 */
	public function mapImportColumnsByTemplateOrder(array $header_row)
	{
		$template_headers = $this->importTemplateHeaders();
		$field_order = ['facility', 'district', 'institution_category', 'institution_type', 'institution_level'];
		$aliases = $this->importColumnAliases();

		if (count($header_row) < 2) {
			return null;
		}

		$map = [];
		$limit = min(count($header_row), count($field_order));

		for ($i = 0; $i < $limit; $i++) {
			$field = $field_order[$i];
			$header_key = $this->normalizeImportHeaderKey($header_row[$i]);
			$template_key = $this->normalizeImportHeaderKey($template_headers[$i] ?? '');

			$matches = ($header_key !== '' && ($header_key === $template_key || in_array($header_key, $aliases[$field], true)));
			if (!$matches && $i < 2) {
				return null;
			}
			if ($matches) {
				$map[$field] = $i;
			}
		}

		if (!isset($map['facility'], $map['district'])) {
			return null;
		}

		return $map;
	}

	/**
	 * @param string $line
	 * @return string
	 */
	public function detectCsvDelimiter($line)
	{
		$candidates = [',', ';', "\t"];
		$best = ',';
		$best_count = 0;

		foreach ($candidates as $delimiter) {
			$count = count(str_getcsv((string) $line, $delimiter));
			if ($count > $best_count) {
				$best_count = $count;
				$best = $delimiter;
			}
		}

		return $best;
	}

	/**
	 * @param string $content
	 * @return string
	 */
	public function normalizeImportFileContents($content)
	{
		$content = (string) $content;

		if (strncmp($content, "\xEF\xBB\xBF", 3) === 0) {
			$content = substr($content, 3);
		} elseif (strncmp($content, "\xFF\xFE", 2) === 0) {
			$content = mb_convert_encoding($content, 'UTF-8', 'UTF-16LE');
		} elseif (strncmp($content, "\xFE\xFF", 2) === 0) {
			$content = mb_convert_encoding($content, 'UTF-8', 'UTF-16BE');
		}

		return $content;
	}

	/**
	 * Parse an uploaded CSV using the same rules as the downloaded template.
	 *
	 * @param string $filepath
	 * @return array{
	 *   column_map: array<string, int>|null,
	 *   rows: array<int, array<string, string>>,
	 *   headers: array<int, string>,
	 *   error: string|null
	 * }
	 */
	public function parseImportCsvFile($filepath)
	{
		$result = [
			'column_map' => null,
			'rows'       => [],
			'headers'    => [],
			'error'      => null,
		];

		$content = @file_get_contents($filepath);
		if ($content === false || $content === '') {
			$result['error'] = 'The import file is empty.';
			return $result;
		}

		$content = $this->normalizeImportFileContents($content);
		$lines = preg_split('/\R/', $content);
		$lines = array_values(array_filter($lines, function ($line) {
			return trim((string) $line) !== '';
		}));

		if (empty($lines)) {
			$result['error'] = 'The import file is empty.';
			return $result;
		}

		$delimiter = $this->detectCsvDelimiter($lines[0]);
		$header_row = str_getcsv($lines[0], $delimiter);
		if (!is_array($header_row)) {
			$result['error'] = 'Could not read header row from the import file.';
			return $result;
		}

		$result['headers'] = $header_row;
		$result['column_map'] = $this->mapImportColumns($header_row);
		if ($result['column_map'] === null) {
			$expected = implode(', ', $this->importTemplateHeaders());
			$found = implode(', ', array_map('trim', $header_row));
			$result['error'] = 'Invalid template headers. Expected: ' . $expected . '. Found: ' . $found;
			return $result;
		}

		$row_template = [
			'facility'             => '',
			'district'             => '',
			'institution_category' => '',
			'institution_type'     => '',
			'institution_level'    => '',
		];

		for ($i = 1, $count = count($lines); $i < $count; $i++) {
			$data = str_getcsv($lines[$i], $delimiter);
			if (!is_array($data) || count(array_filter($data, function ($v) {
				return trim((string) $v) !== '';
			})) === 0) {
				continue;
			}

			$row = $row_template;
			foreach ($result['column_map'] as $field => $index) {
				$row[$field] = isset($data[$index]) ? trim((string) $data[$index]) : '';
			}
			$result['rows'][] = $row;
		}

		if (empty($result['rows'])) {
			$result['error'] = 'No data rows found in the import file.';
		}

		return $result;
	}

	/**
	 * Resolve employee_districts.id from district name (case-insensitive).
	 *
	 * @param string $district_name
	 * @return int|null
	 */
	public function resolveDistrictIdByName($district_name)
	{
		$district_name = trim((string) $district_name);
		if ($district_name === '') {
			return null;
		}

		$row = $this->db->select('id')
			->from('employee_districts')
			->where('LOWER(TRIM(name))', strtolower($district_name))
			->limit(1)
			->get()
			->row();

		return $row ? (int) $row->id : null;
	}

	/**
	 * @param string $facility_name
	 * @param int $district_id
	 * @param int|null $exclude_id
	 * @return bool
	 */
	public function facilityExistsInDistrict($facility_name, $district_id, $exclude_id = null)
	{
		$facility_name = trim((string) $facility_name);
		$district_id = (int) $district_id;
		if ($facility_name === '' || $district_id <= 0) {
			return false;
		}

		$this->db->select('id');
		$this->db->from($this->table);
		$this->db->where('district_id', $district_id);
		$this->db->where('LOWER(TRIM(facility))', strtolower($facility_name));
		if ($exclude_id !== null) {
			$this->db->where('id !=', (int) $exclude_id);
		}
		$this->db->limit(1);

		return (bool) $this->db->get()->row();
	}

	/**
	 * Import facilities/schools from parsed CSV rows.
	 *
	 * @param array<int, array<string, string>> $rows
	 * @return array{imported: int, skipped: int, errors: array<int, string>}
	 */
	public function importFacilitiesFromRows(array $rows)
	{
		$defaults = $this->importDefaultFieldValues();
		$imported = 0;
		$skipped = 0;
		$errors = [];

		foreach ($rows as $index => $row) {
			$row_no = $index + 2;
			$facility_name = trim((string) ($row['facility'] ?? ''));
			$district_name = trim((string) ($row['district'] ?? ''));

			if ($facility_name === '' || stripos($facility_name, 'example') !== false) {
				$skipped++;
				continue;
			}

			$district_id = $this->resolveDistrictIdByName($district_name);
			if (!$district_id) {
				$errors[] = 'Row ' . $row_no . ': District "' . $district_name . '" not found.';
				continue;
			}

			if ($this->facilityExistsInDistrict($facility_name, $district_id)) {
				$skipped++;
				continue;
			}

			$category = trim((string) ($row['institution_category'] ?? ''));
			$type = trim((string) ($row['institution_type'] ?? ''));
			$level = trim((string) ($row['institution_level'] ?? ''));

			$result = $this->saveFacility([
				'facility_id'          => '',
				'facility'               => $facility_name,
				'district_id'            => $district_id,
				'institution_category'   => $category !== '' ? $category : $defaults['institution_category'],
				'institution_type'       => $type !== '' ? $type : $defaults['institution_type'],
				'institution_level'      => $level !== '' ? $level : $defaults['institution_level'],
			]);

			if (stripos((string) $result, 'success') !== false || stripos((string) $result, 'added') !== false) {
				$imported++;
			} else {
				$errors[] = 'Row ' . $row_no . ': ' . $result;
			}
		}

		return [
			'imported' => $imported,
			'skipped'  => $skipped,
			'errors'   => $errors,
		];
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
