<html>
<head>
	<meta charset="UTF-8">
	<title>Individual Time Log Report</title>
	<style>
		body { font-family: Arial, Helvetica, sans-serif; font-size: 10pt; color: #333; margin: 0; padding: 12px; }
		.report-header { width: 100%; margin-bottom: 12px; padding-bottom: 10px; border-bottom: 2px solid #1a5276; }
		.report-header td { vertical-align: middle; padding: 0; border: none; }
		.report-header .logo-cell { width: 90px; padding-right: 14px; }
		.report-header .logo-cell img { height: 70px; width: auto; display: block; }
		.report-title { font-size: 14pt; font-weight: bold; color: #1a5276; margin: 0 0 2px 0; }
		.report-subtitle { font-size: 11pt; color: #555; margin: 0; }
		.info-table { width: 100%; border-collapse: collapse; font-size: 9pt; margin-bottom: 12px; }
		.info-table td { border: 0.5px solid #bdc3c7; padding: 5px 8px; vertical-align: middle; }
		.info-table td:first-child { font-weight: bold; width: 28%; background: #ecf0f1; color: #2c3e50; }
		table.itl-table { width: 100%; border-collapse: collapse; font-size: 9pt; margin-top: 4px; }
		table.itl-table th, table.itl-table td { border: 0.5px solid #7f8c8d; padding: 6px 8px; vertical-align: middle; }
		table.itl-table thead th { background: #1a5276; color: #fff; font-weight: bold; text-align: center; font-size: 8.5pt; }
		table.itl-table thead th.text-left { text-align: left; }
		table.itl-table tbody td { background: #fff; }
		table.itl-table tbody tr:nth-child(even) td { background: #f4f6f7; }
		table.itl-table .num { text-align: center; }
		table.itl-table .name-col { text-align: left; }
		table.itl-table tfoot td { background: #e8eef1; font-weight: bold; border-top: 1.5px solid #1a5276; }
		table.itl-summary { width: 100%; border-collapse: collapse; font-size: 9pt; margin-top: 14px; }
		table.itl-summary th, table.itl-summary td { border: 0.5px solid #7f8c8d; padding: 6px 8px; }
		table.itl-summary thead th { background: #1a5276; color: #fff; font-weight: bold; }
		table.itl-summary tfoot th { background: #e8eef1; border-top: 1.5px solid #1a5276; }
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
				<p class="report-title">Individual Time Log Report</p>
				<p class="report-subtitle"><?php echo isset($employee) && $employee ? htmlspecialchars($employee->facility ?? '') : ''; ?> — <?php echo isset($from) && isset($to) ? date('j F, Y', strtotime($from)) . ' to ' . date('j F, Y', strtotime($to)) : ''; ?></p>
			</td>
		</tr>
	</table>
	<table class="info-table">
		<tbody>
			<tr><td>Employee Name</td><td><?php echo isset($employee) && $employee ? htmlspecialchars(trim(($employee->surname ?? '') . ' ' . ($employee->firstname ?? ''))) : ''; ?></td></tr>
			<tr><td>Designation</td><td><?php echo isset($employee) && $employee ? htmlspecialchars($employee->job ?? '') : ''; ?></td></tr>
			<tr><td>Facility</td><td><?php echo isset($employee) && $employee ? htmlspecialchars($employee->facility ?? '') : ''; ?></td></tr>
			<tr><td>Department</td><td><?php echo isset($employee) && $employee ? htmlspecialchars($employee->department ?? '') : ''; ?></td></tr>
			<tr><td>Period</td><td><strong><?php echo isset($from) ? date('j F, Y', strtotime($from)) : ''; ?> to <?php echo isset($to) ? date('j F, Y', strtotime($to)) : ''; ?></strong></td></tr>
		</tbody>
	</table>
	<table class="itl-table" id="timelogs">
		<thead>
			<tr>
				<th style="width: 32px;">#</th>
				<th class="text-left" style="width: 22%;">Date</th>
				<th class="num" style="width: 18%;">Time In</th>
				<th class="num" style="width: 18%;">Time Out</th>
				<th class="num" style="width: 22%;"><?php echo (isset($summary_label) && $summary_label === 'HOURS') ? 'Hours Worked' : 'Summary'; ?></th>
			</tr>
		</thead>
		<tbody>
