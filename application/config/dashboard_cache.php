<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| Dashboard stats cache: Redis → Memcached → database.
| Live pulse uses a shorter TTL for near-real-time attendance counts.
*/
$config['dashboard_cache'] = [
	'key_prefix'        => 'attend_dash_',
	'stats_ttl'         => 60,
	'live_ttl'          => 10,
	'version_ttl'       => 86400,
	'live_poll_seconds' => 15,
];
