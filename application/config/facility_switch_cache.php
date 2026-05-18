<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| Facility / district list for the Switch Facility modal (built from ihrisdata).
| Refreshed weekly via cron; rebuild manually when ihris data changes.
*/
$config['facility_switch_cache'] = [
	'cache_file'        => APPPATH . 'cache/facility_switch_ihris.json',
	'max_age_seconds'   => 604800, // 7 days
	'timezone'          => 'Africa/Kampala',
];
