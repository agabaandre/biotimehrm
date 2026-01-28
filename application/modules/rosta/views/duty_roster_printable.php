<?php
function isWeekend($date)
{
	$day = intval(date('N', strtotime($date)));
	if ($day >= 6) {
		return 'yes';
	};
	return 'no';
}
?>
<!DOCTYPE html>
<html>

<head>
	<title>Duty Roster Report</title>
	<style>
		body {
			font-family: Arial, sans-serif;
			font-size: 10px;
		}

		table {
			width: 100%;
			border-collapse: collapse;
			margin-top: 10px;
		}

		th,
		td {
			border: 1px solid #000;
			padding: 2px;
			text-align: center;
		}

		th {
			background-color: #f0f0f0;
			font-weight: bold;
		}

		.cell {
			width: 20px;
		}

		.cost {
			text-align: left;
			padding-left: 5px;
		}
	</style>
</head>

<body>
	<div style="text-align:center;">
		<img src="<?php echo base_url(); ?>assets/img/MOH.png" width="100px" style="float:left;">
		<h2>MONTHLY DUTY ROSTER REPORT FOR</h2>
		<h3><?php echo $_SESSION['facility_name'] . " - " . date('F, Y', strtotime($year . "-" . $month)); ?></h3>
	</div>
	<table>
		<thead>
			<tr>
				<th>#</th>
				<th>Name</th>
				<th>Position</th>
				<?php
				$monthdays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
				for ($i = 1; $i < ($monthdays + 1); $i++) {
					$dy = $i;
					if ($i < 10) {
						$dy = "0" . $i;
					}
					$wekday = $year . "-" . $month . "-" . $dy;
					if (isWeekend($wekday) == 'yes') {
						$color = "#7a0404; color:#FFFFFF";
					} else {
						$color = "";
					}
				?>
					<td class="cell" style="padding:0px; text-align: center; border: 1px solid; background-color: <?php echo $color; ?>"><?php echo $i; ?></td>
				<?php } ?>
			</tr>
		</thead>
		<tbody>
			<?php
			$no = 0;
			foreach ($duties as $singleduty) {
				$no++;
			?>
				<tr>
					<td class='cost'><?php echo $no; ?></td>
					<td class='cost' style="text-align:left;"><?php echo $singleduty['fullname']; ?></td>
					<td class='cost' style="text-align:left;"><?php echo character_limiter($singleduty['job'], 15); ?></td>
					<?php
					for ($i = 1; $i < ($monthdays + 1); $i++) {
						$date_d = $year . "-" . $month . "-" . (($i < 10) ? "0" . $i : $i);
						$pid    = $singleduty['ihris_pid'];
						$letter = isset($schedules[$pid][$date_d]) ? $schedules[$pid][$date_d] : '';
					?>
						<td class="cost"><?php echo $letter; ?></td>
					<?php } ?>
				</tr>
			<?php } ?>
		</tbody>
	</table>
</body>

</html>