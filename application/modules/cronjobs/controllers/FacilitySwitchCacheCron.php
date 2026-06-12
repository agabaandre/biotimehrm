<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Weekly rebuild of Switch Facility district/facility cache (MOH: ihrisdata; education: local tables).
 *
 * Cron (Sunday midnight, Africa/Kampala):
 *   0 0 * * 0 cd /path/to/attend && php index.php cronjobs/FacilitySwitchCacheCron/rebuild
 *
 * Manual:
 *   php index.php cronjobs/FacilitySwitchCacheCron/rebuild
 *   GET/POST lists/rebuild_switch_facility_cache (logged-in user with switch permission)
 */
class FacilitySwitchCacheCron extends MX_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->config->load('facility_switch_cache', true, true);
		$cfg = $this->config->item('facility_switch_cache');
		$tz = isset($cfg['timezone']) ? $cfg['timezone'] : 'Africa/Kampala';
		date_default_timezone_set($tz);
	}

	public function rebuild()
	{
		log_message('info', 'Facility switch cache rebuild started');

		try {
			$this->load->library('facility_switch_cache', null, 'fsc');
			$result = $this->fsc->rebuild();
			log_message('info', 'Facility switch cache rebuild: ' . json_encode($result));

			$this->output
				->set_content_type('application/json')
				->set_output(json_encode($result));
		} catch (Exception $e) {
			log_message('error', 'Facility switch cache rebuild failed: ' . $e->getMessage());

			$this->output
				->set_content_type('application/json')
				->set_status_header(500)
				->set_output(json_encode([
					'status'  => 'error',
					'message' => $e->getMessage(),
				]));
		}
	}

	public function status()
	{
		$this->load->library('facility_switch_cache', null, 'fsc');
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($this->fsc->status()));
	}
}
