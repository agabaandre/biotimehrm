<html>
<head>
	<title>Attendance Summary</title>
	<style>
		body { font-family: Arial; font-size: 11pt; }
		table.items { border-collapse: collapse; width: 100%; border: 0.1mm solid #000; }
		th, td { border: 0.2mm solid #000; padding: 5px; vertical-align: top; }
		th { background: #EEEEEE; text-align: center; }
		tr:nth-child(odd) { background: #e1f4f7; }
	</style>
</head>
<body>
	<table class="items" style="font-size: 12pt; border-collapse: collapse;" cellpadding="8" width="100%">
		<tr>
			<td colspan="2"><?php if (!empty($moh_logo_path) && is_file($moh_logo_path)) { ?><img src="<?php echo $moh_logo_path; ?>" width="100px"><?php } else { ?><img src="<?php echo base_url(); ?>assets/img/MOH.png" width="100px"><?php } ?></td>
			<td colspan="10">
				<h4>MONTHLY ATTENDANCE TO DUTY SUMMARY FOR <?php echo (isset($_SESSION['facility_name']) ? $_SESSION['facility_name'] : '') . ' ' . (isset($period_label) ? $period_label : (isset($dates) ? date('F, Y', strtotime($dates . '-01')) : date('F, Y'))); ?></h4>
			</td>
		</tr>
		<tr>
			<th>#</th>
			<th>Name</th>
			<th>Job</th>
			<th>Department</th>
			<th>Off Duty</th>
			<th>Official Request</th>
			<th>Leave</th>
			<th>Holiday</th>
			<th>Total Days Expected</th>
			<th>Total Days Worked</th>
			<th>Total Days Absent</th>
			<th>Present %</th>
		</tr>
		<tbody>
