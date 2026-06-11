<?php

defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('deployment_setting')) {
    function deployment_setting()
    {
        static $setting = null;
        if ($setting !== null) {
            return $setting;
        }
        $CI =& get_instance();
        if (!isset($CI->db)) {
            $setting = (object) ['deployment_type' => 'moh'];
            return $setting;
        }
        $setting = $CI->db->get('setting')->row();
        if (!$setting) {
            $setting = (object) ['deployment_type' => 'moh'];
        }
        return $setting;
    }
}

if (!function_exists('is_education_deployment')) {
    function is_education_deployment()
    {
        $type = deployment_setting()->deployment_type ?? 'moh';
        return strtolower(trim((string) $type)) === 'education';
    }
}

if (!function_exists('entity_label')) {
    /**
     * @param string $key facility|facility_type|facility_staff_list|in_the_facility|all_entities|change_facility|switch_facility|select_facility
     * @param bool $plural
     */
    function entity_label($key, $plural = false)
    {
        $moh = [
            'facility' => ['Facility', 'Facilities'],
            'facility_type' => ['Facility Type', 'Facility Types'],
            'facility_staff_list' => ['Facility Staff List', 'Facility Staff Lists'],
            'in_the_facility' => ['in the facility', 'in the facilities'],
            'all_entities' => ['All facilities', 'All facilities'],
            'change_facility' => ['Change Facility', 'Change Facilities'],
            'switch_facility' => ['Switch Facility', 'Switch Facilities'],
            'select_facility' => ['Select Facility', 'Select Facilities'],
        ];
        $education = [
            'facility' => ['School', 'Schools'],
            'facility_type' => ['School Type', 'School Types'],
            'facility_staff_list' => ['School Staff List', 'School Staff Lists'],
            'in_the_facility' => ['in the school', 'in the schools'],
            'all_entities' => ['All schools', 'All schools'],
            'change_facility' => ['Change School', 'Change Schools'],
            'switch_facility' => ['Switch School', 'Switch Schools'],
            'select_facility' => ['Select School', 'Select Schools'],
        ];
        $labels = is_education_deployment() ? $education : $moh;
        if (!isset($labels[$key])) {
            return $key;
        }
        return $labels[$key][$plural ? 1 : 0];
    }
}

if (!function_exists('group_by_label')) {
    function group_by_label($key)
    {
        $key = strtolower(trim((string) $key));
        $mapped = [
            'facility_name' => entity_label('facility'),
            'facility_type_name' => entity_label('facility_type'),
        ];
        if (isset($mapped[$key])) {
            return $mapped[$key];
        }
        return ucwords(str_replace('_', ' ', $key));
    }
}
