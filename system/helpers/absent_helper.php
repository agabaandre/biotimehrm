<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

/*
*Developer:Agaba Andrew 2022
* Helps to filter dashboard data by financial year or by the user department
*andyear,whereyear,andsubject,wheresubject
*/
if (!function_exists('days_absent_helper')) {
    function days_absent_helper($present,$scheduled){
     $absent=$scheduled-$present;
     return $absent;

    }
}
    if (!function_exists('days_absent_percent')) {
    function days_absent_percent($present,$scheduled){
        $per=(($present/$scheduled)*100);
        
     return $per;

    }
}