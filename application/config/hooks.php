<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/user_guide/general/hooks.html
|
*/
// Add activity logger hook
$hook['post_controller_constructor'][] = array(
    'class'    => 'ActivityLogger',
    'function' => 'log_page_view',
    'filename' => 'ActivityLogger.php',
    'filepath' => 'hooks',
    'params'   => array()
);

// Add facility change detection hook (fixed)
$hook['post_controller_constructor'][] = array(
    'class'    => 'FacilityChangeHook',
    'function' => 'checkFacilityChange',
    'filename' => 'FacilityChangeHook.php',
    'filepath' => 'hooks',
    'params'   => array()
);

// Add session keep-alive hook (fixed)
$hook['post_controller_constructor'][] = array(
    'class'    => 'SessionKeepAlive',
    'function' => 'extendSession',
    'filename' => 'SessionKeepAlive.php',
    'filepath' => 'hooks',
    'params'   => array()
);
