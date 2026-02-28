<!DOCTYPE html>
<html>
<head>
	<title>Duty Roster Report</title>
	<style>
		body { font-family: Arial, sans-serif; font-size: 10px; }
		table { width: 100%; border-collapse: collapse; margin-top: 10px; }
		th, td { border: 1px solid #000; padding: 2px; text-align: center; }
		th { background-color: #f0f0f0; font-weight: bold; }
		.cell { width: 20px; }
		.cost { text-align: left; padding-left: 5px; }
	</style>
</head>
<body>
	<div style="text-align:center;">
		<img src="<?php echo base_url(); ?>assets/img/MOH.png" width="100px" style="float:left;">
		<h2>MONTHLY DUTY ROSTER REPORT FOR</h2>
		<h3><?php echo $_SESSION['facility_name'] . ' - ' . date('F, Y', strtotime($year . '-' . $month)); ?></h3>
	</div>
	<table>
		<thead>
			<tr>
				<th>#</th>
				<th>Name</th>
				<th>Position</th>
				<?php
				$monthdays = cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);
				if (!function_exists('_roster_print_weekend')) {
					function _roster_print_weekend($d) {
						$day = (int) date('N', strtotime($d));
						return $day >= 6 ? 'yes' : 'no';
					}
				}
				for ($i = 1; $i < $monthdays + 1; $i++) {
					$dy = $i < 10 ? '0' . $i : $i;
					$wekday = $year . '-' . $month . '-' . $dy;
					$color = (_roster_print_weekend($wekday) == 'yes') ? '#7a0404; color:#FFFFFF' : '';
				?>
				<td class="cell" style="padding:0px; text-align: center; border: 1px solid; background-color: <?php echo $color; ?>"><?php echo $i; ?></td>
				<?php } ?>
			</tr>
		</thead>
		<tbody>
