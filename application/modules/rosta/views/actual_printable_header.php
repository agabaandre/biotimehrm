<html>
<head>
	<meta charset="UTF-8">
	<title>Monthly Attendance Report</title>
	<style>
		body { font-family: Arial, Helvetica, sans-serif; font-size: 9pt; color: #333; margin: 0; padding: 8px; }
		.report-header { width: 100%; margin-bottom: 10px; padding-bottom: 8px; border-bottom: 2px solid #005662; }
		.report-header td { vertical-align: middle; padding: 0; border: none; }
		.report-header .logo-cell { width: 56px; padding-right: 10px; }
		.report-header .logo-cell img { height: 42px; width: auto; display: block; max-width: 56px; }
		.report-title { font-size: 13pt; font-weight: bold; color: #005662; margin: 0 0 2px 0; }
		.report-subtitle { font-size: 10pt; color: #555; margin: 0; }
		table.actuals-table { width: 100% !important; max-width: 100%; border-collapse: collapse; font-size: 7pt; margin-top: 4px; table-layout: fixed; }
		table.actuals-table th, table.actuals-table td { border: 0.5px solid #7f8c8d; padding: 2px 4px; vertical-align: middle; }
		table.actuals-table thead th { background: linear-gradient(135deg, #005662 0%, #20c198 100%) !important; color: #fff; font-weight: bold; text-align: center; font-size: 7pt; }
		table.actuals-table thead th.text-left { text-align: left; }
		table.actuals-table thead th.day-cell { width: 2%; min-width: 14px; }
		table.actuals-table thead th.total-col { width: 1.2%; min-width: 18px; background: linear-gradient(135deg, #005662 0%, #20c198 100%) !important; }
		table.actuals-table tbody td { background: #fff; text-align: center; font-size: 7pt; }
		table.actuals-table tbody td.name-col { text-align: left; }
		table.actuals-table tbody td.num { text-align: center; }
		table.actuals-table tbody tr:nth-child(even) td { background: #f4f6f7; }
		.summary-key { width: 100% !important; max-width: 100%; margin-bottom: 6px; border-collapse: collapse; font-size: 8pt; line-height: 1.1; table-layout: fixed; }
		.summary-key td { border: 0.5px solid #7f8c8d; padding: 1px 6px; text-align: center; background: #e8eef1; white-space: nowrap; vertical-align: middle; line-height: 1.1; }
		.summary-key .summary-label { background: linear-gradient(135deg, #005662 0%, #20c198 100%) !important; color: #fff; font-weight: bold; text-align: left; padding: 1px 8px; white-space: nowrap; width: 15%; }
	</style>
</head>
<body style="width: 100%;">
	<?php
	if (!function_exists('_actual_print_is_weekend')) {
		function _actual_print_is_weekend($date) {
			$day = (int) date('N', strtotime($date));
			return $day >= 6 ? 'yes' : 'no';
		}
	}
	$monthdays = cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);
	$dates = isset($dates) ? $dates : (isset($year) && isset($month) ? $year . '-' . $month : '');
	?>
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
				<p class="report-title">Monthly Attendance Report</p>
				<p class="report-subtitle"><?php echo isset($facility_name) ? htmlspecialchars($facility_name) : ''; ?> — <?php echo $dates ? date('F, Y', strtotime($dates . '-01')) : ''; ?></p>
			</td>
		</tr>
	</table>
	<?php
	$summary = isset($summary) ? $summary : array();
	$key = isset($key) ? $key : array();
	if (!empty($key)) {
	?>
	<table class="summary-key" style="width: 100%;" cellspacing="0">
		<tr>
			<td class="summary-label">Summary by key</td>
			<?php foreach ($key as $schedule) {
				$letter = isset($schedule->letter) ? $schedule->letter : '';
				$label = isset($schedule->schedule) ? $schedule->schedule : $letter;
				$cnt = isset($summary[$letter]) ? (int)$summary[$letter] : 0;
			?>
			<td><?php echo htmlspecialchars($letter . ' (' . $label . '): ' . $cnt); ?></td>
			<?php } ?>
		</tr>
	</table>
	<?php } ?>
	<table class="actuals-table" style="width: 100%;" cellspacing="0">
		<thead>
			<tr>
				<th style="width: 3%;">#</th>
				<th class="text-left" style="width: 8%;">Name</th>
				<th class="text-left" style="width: 6%;">Position</th>
				<?php for ($i = 1; $i < $monthdays + 1; $i++) {
					$dy = $i < 10 ? '0' . $i : $i;
					$wekday = $year . '-' . $month . '-' . $dy;
					$bg = (_actual_print_is_weekend($wekday) == 'yes') ? '#7a0404' : '';
					$style = $bg ? 'background: #7a0404; color: #fff;' : '';
				?>
				<th class="day-cell" style="<?php echo $style; ?>"><?php echo $i; ?></th>
				<?php } ?>
				<th class="total-col" style="width: 1.2%;">P</th>
				<th class="total-col" style="width: 1.2%;">O</th>
				<th class="total-col" style="width: 1.2%;">R</th>
				<th class="total-col" style="width: 1.2%;">L</th>
				<th class="total-col" style="width: 1.2%;">X</th>
				<th class="total-col" style="width: 1.2%;">H</th>
			</tr>
		</thead>
		<tbody>
