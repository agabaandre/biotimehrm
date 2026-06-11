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
     * @param string $key facility|facility_type|facility_staff_list|in_the_facility|all_entities
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
        ];
        $education = [
            'facility' => ['School', 'Schools'],
            'facility_type' => ['School Type', 'School Types'],
            'facility_staff_list' => ['School Staff List', 'School Staff Lists'],
            'in_the_facility' => ['in the school', 'in the schools'],
            'all_entities' => ['All schools', 'All schools'],
        ];
        $labels = is_education_deployment() ? $education : $moh;
        if (!isset($labels[$key])) {
            return $key;
        }
        return $labels[$key][$plural ? 1 : 0];
    }
}
