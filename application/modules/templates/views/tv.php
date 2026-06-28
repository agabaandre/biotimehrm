<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$userdata = $this->session->get_userdata();
if (!isset($userdata['names'])) {
	redirect('auth');
}
$tv_title = !empty($setting->title) ? $setting->title : 'iHRIS Attendance';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<title><?php echo htmlspecialchars($tv_title, ENT_QUOTES, 'UTF-8'); ?> — Facility TV</title>
	<link rel="shortcut icon" href="<?php echo base_url(!empty($setting->favicon) ? $setting->favicon : 'assets/images/MOH.png'); ?>">
	<link rel="stylesheet" href="<?php echo base_url(); ?>assets/plugins/fontawesome-free/css/all.min.css">
	<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
	<script>
	(function() {
		if (typeof window.Highcharts !== 'undefined') return;
		var base = 'https://code.highcharts.com/10.3/';
		['highcharts.js', 'highcharts-more.js', 'modules/solid-gauge.js'].forEach(function(file, i, arr) {
			var s = document.createElement('script');
			s.src = base + file;
			s.async = false;
			document.head.appendChild(s);
		});
	})();
	</script>
</head>
<body class="tv-root">
<?php
$page_child_data = [];
if (isset($view)) {
	$page_child_data['view'] = $view;
}
if (isset($module)) {
	$page_child_data['module'] = $module;
}
if (isset($facility_id)) {
	$page_child_data['facility_id'] = $facility_id;
}
if (isset($facility_name)) {
	$page_child_data['facility_name'] = $facility_name;
}
if (isset($tv_poll_seconds)) {
	$page_child_data['tv_poll_seconds'] = $tv_poll_seconds;
}
if (isset($setting)) {
	$page_child_data['setting'] = $setting;
}
$this->load->view($module . '/' . $view, $page_child_data);
?>
</body>
</html>
