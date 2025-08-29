<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['ldap_host'] = $_ENV['LDAP_HOST'];
$config['ldap_port'] = $_ENV['LDAP_PORT'];
$config['ldap_user'] = $_ENV['LDAP_USER'];
$config['ldap_pass'] = $_ENV['LDAP_PASS'];
$config['ldap_base_dn'] = $_ENV['LDAP_BASE_DN'];

//dd($config);