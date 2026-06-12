<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| Dashboard Name filter staff lists: Redis → Memcached → database (lazy fill).
| Keys are per facility or per district; populated on first searchEmployees request.
*/
$config['dashboard_staff_cache'] = [
	'key_prefix'      => 'attend_dash_staff_',
	'ttl'             => 86400, // 24 hours
	'cache_key_prefix'=> 'attend_df_',
];
