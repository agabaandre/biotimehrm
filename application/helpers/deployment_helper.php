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
            'facilities_manage_subtitle' => ['Manage and view all healthcare facilities in the system', 'Manage and view all schools in the system'],
            'entity_id' => ['Facility ID', 'School ID'],
            'entity_name' => ['Facility Name', 'School Name'],
            'enter_entity_name' => ['Enter facility name', 'Enter school name'],
            'entity_institution' => ['Facility/Institution', 'School'],
            'entity_not_found' => ['Facility not found', 'School not found'],
            'entity_added' => ['Facility has been added successfully', 'School has been added successfully'],
            'entity_updated' => ['Facility has been updated successfully', 'School has been updated successfully'],
            'entity_deleted' => ['Facility has been deleted successfully', 'School has been deleted successfully'],
            'entity_delete_failed' => ['Facility could not be deleted', 'School could not be deleted'],
            'entities_load_failed' => ['Failed to load facilities. Please refresh the page.', 'Failed to load schools. Please refresh the page.'],
            'entity_add_failed' => ['Failed to add facility', 'Failed to add school'],
            'entity_update_failed' => ['Failed to update facility', 'Failed to update school'],
            'entity_delete_confirm' => ['Are you sure you want to delete this facility?', 'Are you sure you want to delete this school?'],
            'entity_load_failed' => ['Could not load facility', 'Could not load school'],
            'entity_details_load_failed' => ['Failed to load facility details', 'Failed to load school details'],
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
            'facilities_manage_subtitle' => ['Manage and view all healthcare facilities in the system', 'Manage and view all schools in the system'],
            'entity_id' => ['Facility ID', 'School ID'],
            'entity_name' => ['Facility Name', 'School Name'],
            'enter_entity_name' => ['Enter facility name', 'Enter school name'],
            'entity_institution' => ['Facility/Institution', 'School'],
            'entity_not_found' => ['Facility not found', 'School not found'],
            'entity_added' => ['Facility has been added successfully', 'School has been added successfully'],
            'entity_updated' => ['Facility has been updated successfully', 'School has been updated successfully'],
            'entity_deleted' => ['Facility has been deleted successfully', 'School has been deleted successfully'],
            'entity_delete_failed' => ['Facility could not be deleted', 'School could not be deleted'],
            'entities_load_failed' => ['Failed to load facilities. Please refresh the page.', 'Failed to load schools. Please refresh the page.'],
            'entity_add_failed' => ['Failed to add facility', 'Failed to add school'],
            'entity_update_failed' => ['Failed to update facility', 'Failed to update school'],
            'entity_delete_confirm' => ['Are you sure you want to delete this facility?', 'Are you sure you want to delete this school?'],
            'entity_load_failed' => ['Could not load facility', 'Could not load school'],
            'entity_details_load_failed' => ['Failed to load facility details', 'Failed to load school details'],
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
