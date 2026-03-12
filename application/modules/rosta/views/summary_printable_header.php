<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Roster Summary</title>
	<style>
		body { font-family: Arial; font-size: 10pt; }
		table { border-collapse: collapse; width: 100%; }
		th, td { border: 1px solid #333; padding: 4px; text-align: left; }
		th { background: #eee; font-weight: bold; }
		td.num { text-align: right; }
		tr:nth-child(even) { background: #f9f9f9; }
		.report-title { font-size: 12pt; font-weight: bold; margin-bottom: 8px; }
		table.header-layout { border: none; }
		table.header-layout td { border: none; padding: 4px 8px; vertical-align: middle; }
	</style>
</head>
<body>
	<table class="header-layout" style="width: 100%; margin-bottom: 8px;">
		<tr>
			<td style="width: 100px;"><?php if (!empty($moh_logo_path) && is_file($moh_logo_path)) { ?><img src="<?php echo $moh_logo_path; ?>" width="100px"><?php } else { ?><img src="<?php echo base_url(); ?>assets/img/MOH.png" width="100px"><?php } ?></td>
			<td>
				<div class="report-title">MONTHLY ATTENDANCE TO DUTY SUMMARY</div>
				<div style="margin-bottom: 4px;"><?php echo isset($facility_name) ? htmlspecialchars($facility_name) : ''; ?> — Period: <?php echo isset($period_label) ? htmlspecialchars($period_label) : ''; ?></div>
			</td>
		</tr>
	</table>
	<table>
		<thead>
			<tr>
				<th>#</th>
				<th>Name</th>
				<th>Job</th>
				<th class="num">Day</th>
				<th class="num">Evening</th>
				<th class="num">Night</th>
				<th class="num">Off</th>
				<th class="num">Annual</th>
				<th class="num">Study</th>
				<th class="num">Maternity</th>
				<th class="num">Other</th>
				<th class="num">Total</th>
			</tr>
		</thead>
		<tbody>
