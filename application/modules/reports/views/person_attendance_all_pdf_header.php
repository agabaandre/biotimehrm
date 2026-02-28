<html>
<head>
	<meta charset="UTF-8">
	<title>Person Attendance All</title>
	<style>
		body { font-family: Arial; font-size: 9pt; }
		table { border-collapse: collapse; width: 100%; }
		th, td { border: 0.5pt solid #333; padding: 3px; text-align: left; }
		th { background: #eee; font-weight: bold; }
		td.num { text-align: right; }
		tr:nth-child(even) { background: #f9f9f9; }
		.logo { margin-bottom: 6px; }
		.report-title { font-size: 11pt; font-weight: bold; margin-bottom: 8px; }
	</style>
</head>
<body>
	<div class="logo"><img src="<?php echo base_url(); ?>assets/img/MOH.png" width="70" /></div>
	<div class="report-title">MONTHLY ATTENDANCE TO DUTY SUMMARY</div>
	<div style="margin-bottom: 4px;">Period: <?php echo isset($period_label) ? htmlspecialchars($period_label) : ''; ?></div>
	<table>
		<thead>
			<tr>
				<th>#</th>
				<th>Name</th>
				<th>District</th>
				<th>Facility</th>
				<th>Period</th>
				<th class="num">Present</th>
				<th class="num">Off Duty</th>
				<th class="num">Official</th>
				<th class="num">Leave</th>
				<th class="num">Holiday</th>
				<th class="num">Absent</th>
				<th class="num">% Absent.</th>
			</tr>
		</thead>
		<tbody>
