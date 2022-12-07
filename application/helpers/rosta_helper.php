<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
*Developer:Agaba Andrew 2022

*/
if (!function_exists('retrieve_schedule')) {
    function retrieve_schedule($pid, $date)
    {
        $ci = &get_instance();
        $ci->load->model('rosta_model');
        return $ci->rosta_model->get_roster_schedules($pid, $date);
    }
}

if (!function_exists('retrieve_attendance_schedule')) {
    function retrieve_attendance_schedule($pid, $date)
    {
        $ci = &get_instance();
        $ci->load->model('rosta_model');
        return $ci->rosta_model->get_attendance_schedules($pid, $date);
    }
}

if (!function_exists('gettimedata')) {
    function gettimedata($pid, $date)
    {
        $ci = &get_instance();
        $ci->load->model('employee_model');
        return $ci->employee_model->gettimedata($pid, $date);
    }
}