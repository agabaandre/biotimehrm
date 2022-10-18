<?php

function aggregate_att_count($column,$value,$period){

	$ci =& get_instance();
	$ci->load->model('reports_mdl');
	
	$db_count = $ci->reports_mdl->aggregate_group_count($column,$value,$period);

	$year_month      = explode('-',$period);
	$dasy_this_month = cal_days_in_month(CAL_GREGORIAN,$year_month[1],$year_month[0]);

	return $db_count * $dasy_this_month;
}

?>