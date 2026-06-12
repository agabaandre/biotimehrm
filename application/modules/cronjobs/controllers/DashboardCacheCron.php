<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Health (MOH) dashboard cache maintenance.
 * Bumps per-facility cache versions so dashboard stats refresh after BioTime sync.
 *
 * Cron (every hour at :15, via jobs master on MOH only):
 *   php index.php cronjobs/DashboardCacheCron/warm
 */
class DashboardCacheCron extends MX_Controller {

	public function warm()
	{
		if (function_exists('is_education_deployment') && is_education_deployment()) {
			$this->output
				->set_content_type('application/json')
				->set_output(json_encode([
					'status'  => 'skipped',
					'message' => 'Dashboard cache warm is MOH-only',
				]));
			return;
		}

		log_message('info', 'Dashboard cache warm started');

		$this->load->library('dashboard_cache_store', null, 'dash_cache');

		$facility_ids = $this->db
			->distinct()
			->select('facility_id')
			->where('facility_id IS NOT NULL', null, false)
			->where('facility_id !=', '')
			->get('ihrisdata')
			->result_array();

		$count = 0;
		foreach ($facility_ids as $row) {
			$fid = trim((string) ($row['facility_id'] ?? ''));
			if ($fid === '') {
				continue;
			}
			$this->dash_cache->invalidateFacility($fid);
			$count++;
		}

		$result = [
			'status'       => 'success',
			'facilities'   => $count,
			'generated_at' => date('c'),
			'cache_layer'  => $this->dash_cache->availability(),
		];

		log_message('info', 'Dashboard cache warm: ' . json_encode($result));

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($result));
	}
}
