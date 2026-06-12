<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| Facility / district list for the Switch Facility modal and auth user forms.
| MOH: built from ihrisdata (facility_switch_ihris.json).
| Education: built from employee_districts / employee_facility (facility_switch_education.json).
| Refreshed weekly via cron; rebuild manually when master data changes.
| Read order: Redis → Memcached → JSON file → database rebuild.
| Write on rebuild: all available tiers (best effort).
*/
$config['facility_switch_cache'] = [
	'cache_file'        => APPPATH . 'cache/facility_switch_ihris.json',
	'max_age_seconds'   => 604800, // 7 days
	'timezone'          => 'Africa/Kampala',
	'cache_key_prefix'  => 'attend_df_',
];
