<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

/*
*Developer:Agaba Andrew 2022

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
        
    if(is_infinite($per)||is_nan($per)){ echo  0; } else{ echo $per; }
    return $per;
    }
}