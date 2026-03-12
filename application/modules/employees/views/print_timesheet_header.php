<html>
<head>
	<meta charset="UTF-8">
	<title>Monthly Timesheet Attendance Report</title>
	<style>
		body { font-family: Arial, Helvetica, sans-serif; font-size: 9pt; color: #333; margin: 0; padding: 8px; width: 100%; }
		.report-header { width: 100%; margin-bottom: 10px; padding-bottom: 8px; border-bottom: 2px solid #005662; }
		.report-header td { vertical-align: middle; padding: 0; border: none; }
		.report-header .logo-cell { width: 56px; padding-right: 10px; }
		.report-header .logo-cell img { height: 42px; width: auto; display: block; max-width: 56px; }
		.report-title { font-size: 13pt; font-weight: bold; color: #005662; margin: 0 0 2px 0; }
		.report-subtitle { font-size: 10pt; color: #555; margin: 0; }
		table.ts-table { width: 100% !important; max-width: 100%; border-collapse: collapse; font-size: 7pt; margin-top: 4px; table-layout: fixed; }
		table.ts-table th, table.ts-table td { border: 0.5px solid #7f8c8d; padding: 2px 4px; vertical-align: middle; }
		table.ts-table thead th { background: linear-gradient(135deg, #005662 0%, #20c198 100%) !important; color: #fff; font-weight: bold; text-align: center; font-size: 7pt; }
		table.ts-table thead th.text-left { text-align: left; }
		table.ts-table thead th.day-cell { width: 2%; min-width: 14px; }
		table.ts-table thead th.total-col { width: 1.5%; min-width: 20px; background: linear-gradient(135deg, #005662 0%, #20c198 100%) !important; }
		table.ts-table tbody td { background: #fff; text-align: center; font-size: 7pt; }
		table.ts-table tbody td.name-col { text-align: left; }
		table.ts-table tbody td.num { text-align: center; }
		table.ts-table tbody tr:nth-child(even) td { background: #f4f6f7; }
	</style>
</head>
<body style="width: 100%;">
	<?php
	$dateList = isset($dateList) ? $dateList : array();
	if (!empty($date_from) && !empty($date_to)) {
		$period_label = (isset($date_from) && isset($date_to)) ? $date_from . ' to ' . $date_to : '';
	} else {
		$period_label = isset($year) && isset($month) ? date('F, Y', strtotime($year . '-' . $month . '-01')) : '';
	}
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
				<p class="report-title">Monthly Timesheet Attendance Report</p>
				<p class="report-subtitle"><?php echo isset($facility_name) ? htmlspecialchars($facility_name) : ''; ?> — <?php echo $period_label; ?></p>
			</td>
		</tr>
	</table>
	<table class="ts-table" style="width: 100%;" cellspacing="0">
		<thead>
			<tr>
				<th style="width: 3%;">#</th>
				<th class="text-left" style="width: 10%;">Name</th>
				<th class="text-left" style="width: 8%;">Position</th>
				<?php
				if (!function_exists('_print_ts_is_weekend')) {
					function _print_ts_is_weekend($date) {
						$day = (int) date('N', strtotime($date));
						return $day >= 6 ? 'yes' : 'no';
					}
				}
				if (!empty($dateList)) {
					foreach ($dateList as $dStr) {
						$bg = (_print_ts_is_weekend($dStr) == 'yes') ? '#7a0404' : '';
						$style = $bg ? 'background: #7a0404; color: #fff;' : '';
						$label = (int) date('j', strtotime($dStr));
				?>
				<th class="day-cell" style="<?php echo $style; ?>"><?php echo $label; ?></th>
				<?php
					}
				} else {
					$monthdays = cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);
					for ($i = 1; $i < $monthdays + 1; $i++) {
						$dy = $i < 10 ? '0' . $i : $i;
						$wekday = $year . '-' . $month . '-' . $dy;
						$bg = (_print_ts_is_weekend($wekday) == 'yes') ? '#7a0404' : '';
						$style = $bg ? 'background: #7a0404; color: #fff;' : '';
				?>
				<th class="day-cell" style="<?php echo $style; ?>"><?php echo $i; ?></th>
				<?php
					}
				}
				?>
				<th class="total-col">Hours</th>
				<th class="total-col">Days</th>
			</tr>
		</thead>
		<tbody>
