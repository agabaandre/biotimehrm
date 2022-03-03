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
