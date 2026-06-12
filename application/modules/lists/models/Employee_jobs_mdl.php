<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Employee_jobs_mdl extends CI_Model {

	protected $table;

	public function __construct()
	{
		parent::__construct();
		$this->table = 'employee_jobs';
	}

	public function getJobs()
	{
		$this->db->select('job_title,description,id,job_id,created_at');
		$this->db->order_by('job_title', 'ASC');
		$query = $this->db->get($this->table);
		return $query->result();
	}

	/**
	 * @param string $title
	 * @param int|null $exclude_id
	 * @return bool
	 */
	public function isJobTitleDuplicate($title, $exclude_id = null)
	{
		$title = trim((string) $title);
		if ($title === '') {
			return false;
		}

		$this->db->from($this->table);
		$this->db->where('LOWER(TRIM(job_title))', strtolower($title));
		if ($exclude_id !== null) {
			$this->db->where('id !=', (int) $exclude_id);
		}

		return $this->db->count_all_results() > 0;
	}

	/**
	 * @param string $job_id
	 * @param int|null $exclude_id
	 * @return bool
	 */
	public function isJobIdDuplicate($job_id, $exclude_id = null)
	{
		$job_id = trim((string) $job_id);
		if ($job_id === '') {
			return false;
		}

		$this->db->from($this->table);
		$this->db->where('job_id', $job_id);
		if ($exclude_id !== null) {
			$this->db->where('id !=', (int) $exclude_id);
		}

		return $this->db->count_all_results() > 0;
	}

	/**
	 * @return string
	 */
	public function generateJobId()
	{
		$this->db->select('job_id');
		$rows = $this->db->get($this->table)->result();

		$prefix = 'JOB';
		$width = 4;
		$max_num = 0;

		foreach ($rows as $row) {
			$job_id = trim((string) $row->job_id);
			if ($job_id === '') {
				continue;
			}
			if (preg_match('/^(.*?)(\d+)$/', $job_id, $m)) {
				$prefix = $m[1];
				$width = max($width, strlen($m[2]));
				$max_num = max($max_num, (int) $m[2]);
			}
		}

		return $prefix . str_pad((string) ($max_num + 1), $width, '0', STR_PAD_LEFT);
	}

	/**
	 * @param int $errno
	 * @return bool
	 */
	private function isDuplicateKeyError($errno)
	{
		return in_array((int) $errno, [1062, 1586, 1022], true);
	}

	public function saveJob($postdata)
	{
		$job_title = trim((string) ($postdata['job_title'] ?? ''));
		$provided_job_id = trim((string) ($postdata['job_id'] ?? ''));

		if ($job_title === '') {
			return 'Job title is required.';
		}

		if ($this->isJobTitleDuplicate($job_title)) {
			return 'Job title already exists. Please use a different title.';
		}

		if ($provided_job_id !== '' && $this->isJobIdDuplicate($provided_job_id)) {
			return 'Job ID already exists.';
		}

		$data = [
			'job_title'   => $job_title,
			'description' => $postdata['description'] ?? '',
		];

		$max_attempts = 5;
		for ($attempt = 0; $attempt < $max_attempts; $attempt++) {
			$job_id = $provided_job_id !== '' ? $provided_job_id : $this->generateJobId();
			if ($this->isJobIdDuplicate($job_id)) {
				if ($provided_job_id !== '') {
					return 'Job ID already exists.';
				}
				continue;
			}

			$data['job_id'] = $job_id;
			$this->db->insert($this->table, $data);

			if ($this->db->affected_rows() > 0) {
				return 'Job has been Added Successfully';
			}

			$err = $this->db->error();
			if (!empty($err['code']) && $this->isDuplicateKeyError($err['code'])) {
				if ($provided_job_id !== '') {
					return 'Job ID already exists.';
				}
				continue;
			}

			break;
		}

		return 'Operation failed';
	}

	public function updateJob($postdata)
	{
		$id = isset($postdata['id']) ? (int) $postdata['id'] : 0;
		if ($id <= 0) {
			return 'Invalid job';
		}

		$job_title = trim((string) ($postdata['job_title'] ?? ''));
		$job_id = trim((string) ($postdata['job_id'] ?? ''));

		if ($job_title === '' || $job_id === '') {
			return 'Job title and Job ID are required.';
		}

		if ($this->isJobTitleDuplicate($job_title, $id)) {
			return 'Job title already exists. Please use a different title.';
		}

		if ($this->isJobIdDuplicate($job_id, $id)) {
			return 'Job ID already exists.';
		}

		$data = [
			'job_title'   => $job_title,
			'job_id'      => $job_id,
			'description' => $postdata['description'] ?? '',
		];

		$this->db->where('id', $id);
		$this->db->update($this->table, $data);
		$rows = $this->db->affected_rows();

		if ($rows > 0) {
			return 'The Job has been updated';
		}

		return 'No Operation made, seems like no changes made';
	}

	public function deleteJob()
	{
		$data = $this->input->post('id');
		$this->db->where('id', $data);
		$this->db->delete($this->table);

		$rows = $this->db->affected_rows();
		if ($rows > 0) {
			return 'The Job has been updated';
		}

		return 'No Operation made, seems like no changes made';
	}
}
