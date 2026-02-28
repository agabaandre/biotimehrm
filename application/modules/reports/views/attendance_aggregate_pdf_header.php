<html>
<head>
	<meta charset="UTF-8">
	<title>Attendance Aggregate Report</title>
	<style>
		body { font-family: Arial; font-size: 10pt; }
		table { border-collapse: collapse; width: 100%; }
		th, td { border: 0.5pt solid #333; padding: 4px; text-align: left; }
		th { background: #eee; font-weight: bold; }
		td.num { text-align: right; }
		tr:nth-child(even) { background: #f9f9f9; }
		.logo { margin-bottom: 8px; }
		.report-title { font-size: 12pt; font-weight: bold; margin-bottom: 10px; }
	</style>
</head>
<body>
	<div class="logo"><img src="<?php echo base_url(); ?>assets/img/MOH.png" width="80" /></div>
	<div class="report-title">AGGREGATED ATTENDANCE TO DUTY SUMMARY BY <?php echo strtoupper(str_replace('_', ' ', isset($grouped_by) ? $grouped_by : 'district')); ?></div>
	<div style="margin-bottom: 6px;">Period: <?php echo isset($period_label) ? htmlspecialchars($period_label) : ''; ?></div>
	<table>
		<thead>
			<tr>
				<th>#</th>
				<th><?php echo ucwords(str_replace('_', ' ', isset($grouped_by) ? $grouped_by : 'District')); ?></th>
				<th>Period</th>
				<th class="num">Present</th>
				<th class="num">Off Duty</th>
				<th class="num">Official</th>
				<th class="num">Leave</th>
				<th class="num">Holiday</th>
				<th class="num">Absent</th>
				<th class="num">Days Worked</th>
				<th class="num">Days Sched.</th>
				<th class="num">% Accounted</th>
				<th class="num">% Absent.</th>
			</tr>
		</thead>
		<tbody>
