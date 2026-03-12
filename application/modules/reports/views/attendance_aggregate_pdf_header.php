<html>
<head>
	<meta charset="UTF-8">
	<title>Attendance Aggregate Report</title>
	<style>
		body { font-family: Arial, Helvetica, sans-serif; font-size: 10pt; color: #333; margin: 0; padding: 12px; }
		.report-header { width: 100%; margin-bottom: 14px; padding-bottom: 10px; border-bottom: 2px solid #1a5276; }
		.report-header td { vertical-align: middle; padding: 0; border: none; }
		.report-header .logo-cell { width: 90px; padding-right: 14px; }
		.report-header .logo-cell img { height: 70px; width: auto; display: block; }
		.report-title { font-size: 14pt; font-weight: bold; color: #1a5276; margin: 0 0 2px 0; }
		.report-subtitle { font-size: 11pt; color: #555; margin: 0; }
		table.agg-table { width: 100%; border-collapse: collapse; font-size: 9pt; margin-top: 6px; }
		table.agg-table th, table.agg-table td { border: 0.5px solid #7f8c8d; padding: 6px 8px; vertical-align: middle; }
		table.agg-table thead th { background: #1a5276; color: #fff; font-weight: bold; text-align: center; font-size: 8.5pt; }
		table.agg-table thead th.text-left { text-align: left; }
		table.agg-table tbody td { background: #fff; }
		table.agg-table tbody tr:nth-child(even) td { background: #f4f6f7; }
		table.agg-table .num { text-align: center; }
		table.agg-table .name-col { text-align: left; }
	</style>
</head>
<body>
	<table class="report-header" cellpadding="0" cellspacing="0">
		<tr>
			<td class="logo-cell">
				<?php if (!empty($moh_logo_path) && is_file($moh_logo_path)) { ?>
					<img src="<?php echo $moh_logo_path; ?>" alt="MOH">
				<?php } else { ?>
					<img src="<?php echo base_url(); ?>assets/img/MOH.png" alt="MOH">
				<?php } ?>
			</td>
			<td>
				<p class="report-title">Aggregated Attendance to Duty Summary</p>
				<p class="report-subtitle">By <?php echo ucwords(str_replace('_', ' ', isset($grouped_by) ? $grouped_by : 'district')); ?> — Period: <?php echo isset($period_label) ? htmlspecialchars($period_label) : ''; ?></p>
			</td>
		</tr>
	</table>
	<table class="agg-table">
		<thead>
			<tr>
				<th style="width: 28px;">#</th>
				<th class="text-left" style="width: 14%;"><?php echo ucwords(str_replace('_', ' ', isset($grouped_by) ? $grouped_by : 'District')); ?></th>
				<th class="text-left" style="width: 10%;">Period</th>
				<th class="num" style="width: 7%;">Present</th>
				<th class="num" style="width: 7%;">Off Duty</th>
				<th class="num" style="width: 7%;">Official</th>
				<th class="num" style="width: 6%;">Leave</th>
				<th class="num" style="width: 6%;">Holiday</th>
				<th class="num" style="width: 6%;">Absent</th>
				<th class="num" style="width: 8%;">Days Worked</th>
				<th class="num" style="width: 8%;">Days Sched.</th>
				<th class="num" style="width: 8%;">% Accounted</th>
				<th class="num" style="width: 7%;">% Absent</th>
			</tr>
		</thead>
		<tbody>
