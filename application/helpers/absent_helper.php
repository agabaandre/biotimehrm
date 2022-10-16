<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
*Developer:Agaba Andrew 2022

*/
if (!function_exists('days_absent_helper')) {
    function days_absent_helper($present, $scheduled)
    {
        $absent = $scheduled - $present;
        return $absent;
    }
}
if (!function_exists('per_present_helper')) {
    function per_present_helper($present, $rdays)
    {
        $per = round(($present / ($rdays)) * 100, 1);
        if (is_infinite($per) || is_nan($per)) {
            return   0 . ' %';
        } else {
            return $per . ' %';
        }
    }
}
