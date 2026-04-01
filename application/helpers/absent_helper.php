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

/**
 * person_att_final: expected working days = baseline calendar days minus non-work (O+L+R+H).
 */
if (!function_exists('person_att_expected_days_helper')) {
    function person_att_expected_days_helper($base_line, $O, $L, $R, $H)
    {
        $bl = (int) $base_line;
        $deduct = (int) $O + (int) $L + (int) $R + (int) $H;
        $exp = $bl - $deduct;
        return $exp > 0 ? $exp : 0;
    }
}

/** Absent days when expected >= P is 0, else expected - P. */
if (!function_exists('person_att_absent_helper')) {
    function person_att_absent_helper($P, $expected_days)
    {
        $diff = (int) $expected_days - (int) $P;
        return $diff > 0 ? $diff : 0;
    }
}

/** % Present = P / total days expected * 100 (person_att_final). */
if (!function_exists('person_att_percent_present_helper')) {
    function person_att_percent_present_helper($P, $expected_days, $with_suffix = true)
    {
        $e = (int) $expected_days;
        if ($e <= 0) {
            return $with_suffix ? '0 %' : 0;
        }
        $per = round(((int) $P / $e) * 100, 1);
        if (is_infinite($per) || is_nan($per)) {
            return $with_suffix ? '0 %' : 0;
        }
        return $with_suffix ? ($per . ' %') : $per;
    }
}
