
<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Illuminate\Database\Capsule\Manager as Capsule;

$active_group = 'default';
$query_builder = TRUE;

$db['default'] = array(
	'dsn' => '',
	'hostname' => $_ENV['DB_HOST'],
    'username' => $_ENV['DB_USER'],
    'password' => $_ENV['DB_PASS'],
    'database' => $_ENV['DB_NAME'],
	'dbdriver' => 'mysqli',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);




$db['pg'] = array(
    'dsn'      => '',
    'hostname' => $ENV['PG_DB_HOST'],
    'port'     => $ENV['PG_PORT'],
    'username' => $ENV['PG_USER'],
    'password' => $ENV['PG_PASS'],
    'database' => $ENV['PG_DB_NAME'],
    'dbdriver' => 'postgre',
    'dbprefix' => '',
    'pconnect' => FALSE,
    'db_debug' => TRUE, // Enable for debugging connection issues
    'cache_on' => FALSE,
    'cachedir' => '',
    'char_set' => 'utf8',
    'dbcollat' => 'utf8_general_ci',
    'swap_pre' => '',
    'encrypt'  => FALSE,
    'compress' => FALSE,
    'stricton' => FALSE,
    'failover' => array(),
    'save_queries' => TRUE
);

$capsule = new Capsule;

$capsule->addConnection([
	'driver' => 'mysql',
	// $database['DBDriver']
	'host' => $db['default']['hostname'],
	'database' => $db['default']['database'],
	'username' => $db['default']['username'],
	'password' => $db['default']['password'],
	'charset' => $db['default']['char_set'],
	'prefix' => $db['default']['dbprefix']
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();
