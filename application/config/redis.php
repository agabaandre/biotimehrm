<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| CodeIgniter Redis cache driver (used by Dropdown_cache_store and CI cache library).
| Override host/port/password per environment when Redis is available.
*/
$config['redis'] = [
	'socket_type' => 'tcp',
	'host'        => '127.0.0.1',
	'port'        => 6379,
	'password'    => null,
	'timeout'     => 1,
];

// Legacy alias (unused elsewhere; kept for reference deployments).
$config['redis_slave'] = [
	'host'     => '127.0.0.1',
	'port'     => '6379',
	'password' => '',
];
