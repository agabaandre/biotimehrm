<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Staff CRUD routes for mobile app sync
// These map nested URLs to the Api controller's staff_* methods
$route['api/staff/create'] = 'api/api/staff/create';
$route['api/staff/update/(:num)'] = 'api/api/staff/update/$1';
$route['api/staff/delete/(:num)'] = 'api/api/staff/delete/$1';

// Explicit routes for endpoints that HMVC auto-routing doesn't resolve correctly
$route['api/fingerprints'] = 'api/api/fingerprints';
$route['api/face_embeddings'] = 'api/api/face_embeddings';
$route['api/upload_fingerprint'] = 'api/api/upload_fingerprint';
$route['api/upload_face_embedding'] = 'api/api/upload_face_embedding';
$route['api/reasons'] = 'api/api/reasons';
$route['api/cadres'] = 'api/api/cadres';
$route['api/districts'] = 'api/api/districts';
$route['api/all_facilities'] = 'api/api/all_facilities';
$route['api/jobs'] = 'api/api/jobs';
