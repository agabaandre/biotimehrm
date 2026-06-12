<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| Attendance aggregate report cache: Redis → Memcached → database.
*/
$config['report_cache'] = [
	'key_prefix'            => 'attend_report_',
	'aggregate_ttl'         => 300,
	'person_attendance_ttl' => 300,
];
