<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Staff CRUD routes for mobile app sync
// These map nested URLs to the Api controller's staff_* methods
$route['api/staff/create'] = 'api/api/staff/create';
$route['api/staff/update/(:num)'] = 'api/api/staff/update/$1';
$route['api/staff/delete/(:num)'] = 'api/api/staff/delete/$1';
