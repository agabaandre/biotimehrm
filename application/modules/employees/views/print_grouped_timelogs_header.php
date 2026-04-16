<html>
<head>
	<meta charset="UTF-8">
	<title>Monthly Time Log Report</title>
	<style>
		body { font-family: Arial, Helvetica, sans-serif; font-size: 10pt; color: #333; margin: 0; padding: 12px; }
		.report-header { width: 100%; margin-bottom: 14px; padding-bottom: 10px; border-bottom: 2px solid #1a5276; }
		.report-header td { vertical-align: middle; padding: 0; border: none; }
		.report-header .logo-cell { width: 90px; padding-right: 14px; }
		.report-header .logo-cell img { height: 70px; width: auto; display: block; }
		.report-title { font-size: 14pt; font-weight: bold; color: #1a5276; margin: 0 0 2px 0; }
		.report-subtitle { font-size: 11pt; color: #555; margin: 0; }
		table.gtl-table { width: 100%; border-collapse: collapse; font-size: 9pt; margin-top: 6px; }
		table.gtl-table th, table.gtl-table td { border: 0.5px solid #7f8c8d; padding: 6px 8px; vertical-align: middle; }
		table.gtl-table thead th { background: #1a5276; color: #fff; font-weight: bold; text-align: center; font-size: 8.5pt; }
		table.gtl-table thead th.text-left { text-align: left; }
		table.gtl-table tbody td { background: #fff; }
		table.gtl-table tbody tr:nth-child(even) td { background: #f4f6f7; }
		table.gtl-table .num { text-align: center; }
		table.gtl-table .name-col { text-align: left; }
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
				<p class="report-title">Monthly Time Log Report</p>
				<p class="report-subtitle"><?php echo isset($facility_name) ? htmlspecialchars($facility_name) : ''; ?> — <?php echo isset($date_from) && isset($date_to) ? htmlspecialchars($date_from) . ' to ' . htmlspecialchars($date_to) : ''; ?></p>
			</td>
		</tr>
	</table>
	<table class="gtl-table">
		<thead>
			<tr>
				<th style="width: 32px;">#</th>
				<th class="text-left" style="width: 22%;">Name</th>
				<th class="text-left" style="width: 18%;">Position</th>
				<th class="text-left" style="width: 18%;">Facility</th>
				<th class="text-left" style="width: 14%;">Department</th>
				<th class="text-left" style="width: 14%;">Date</th>
				<th class="num" style="width: 14%;">Hours Worked</th>
			</tr>
		</thead>
		<tbody>
