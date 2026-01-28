<html>

<head>
	<title>Time Sheet</title>
	<style>
		body {
			font-family: Arial;
			font-size: 12pt;
			max-width: 21cm;
			max-height: 29.7cm;
		}

		p {
			margin: 0pt;
		}

		table.items {
			border: 0.1mm solid #000000;
		}

		td {
			vertical-align: top;
		}

		.items td {
			border-left: 0.2mm solid #000000;
			border-right: 0.2mm solid #000000;
		}

		table thead th {
			background-color: #EEEEEE;
			text-align: center;
			border: 0.1mm solid #000000;
			/*font-variant: small-caps;*/
		}

		.items tr td {
			border: 0.2mm solid #000000;
		}

		.items td.blanktotal {
			background-color: #EEEEEE;
			border: 0.1mm solid #000000;
			background-color: #FFFFFF;
			border: 0mm none #000000;
			border-top: 0.1mm solid #000000;
			border-right: 0.1mm solid #000000;
		}

		.items td.totals {
			text-align: right;
			border: 0.1mm solid #000000;
		}

		.items td.cost {
			text-align: "."center;
		}

		.logo {
			margin-top: 0em;
			margin-left: 20%;
			margin-right: 20%;
			margin-bottom: 0.5em;
		}

		.heading {
			margin-top: 0.4em;
			margin-left: 20%;
			margin-right: 10%;
			margin-bottom: 0.1em;
		}

		.title {
			margin-top: 0.0em;
			margin-left: 30%;
			margin-right: 10%;
			margin-bottom: 0.1em;
		}

		tr:nth-child(odd) {
			background-color: #e1f4f7;
		}

		td {
			padding: 5px;
		}
	</style>
</head>

<body>
	<table width="100%" class="items" style="font-size: 12pt; border-collapse: collapse; " cellpadding="8">
		<thead>
			<tr style="border-right: 0; border-left: 0; border-top: 0;">
				<td colspan=3 style="border-right: 0; border-left: 0; border-top: 0;"><img src="<?php echo base_url(); ?>assets/img/MOH.png" width="100px"></td>
				<?php
				// Support either monthly (month/year) OR date range (date_from/date_to)
				$dateList = array();
				if (!empty($date_from) && !empty($date_to)) {
					$start = new DateTime($date_from);
					$end = new DateTime($date_to);
					if ($start > $end) {
						$tmp = $start;
						$start = $end;
						$end = $tmp;
					}
					$endInc = clone $end;
					$endInc->modify('+1 day');
					$period = new DatePeriod($start, new DateInterval('P1D'), $endInc);
					foreach ($period as $dt) {
						$dateList[] = $dt->format('Y-m-d');
					}
					if (count($dateList) > 31) {
						$dateList = array_slice($dateList, 0, 31);
					}
					$allcols = count($dateList);
				} else {
					$allcols = cal_days_in_month(CAL_GREGORIAN, $month, $year);
				}
				?>
				<td colspan=<?php echo $allcols; ?> style="border-right: 0; border-left: 0; border-top: 0;">
					<h2>
						MONTHLY TIMESHEET ATTENDANCE REPORT
						<?php
						echo $_SESSION['facility_name'];
						if (!empty($date_from) && !empty($date_to)) {
							echo "   " . $date_from . " to " . $date_to;
						} else {
							$dates = $year . '-' . $month;
							echo "   " . date('F, Y', strtotime(date($dates)));
						}
						?>
					</h2>
				</td>
			</tr>
			<tr>
				<th>#</th>
				<th>Name</th>
				<th>Position</th>
				<?php
				function isWeekend($date)
				{
					$day = intval(date('N', strtotime($date)));
					if ($day >= 6) {
						return 'yes';
					};
					return 'no';
				}
				if (!empty($dateList)) {
					foreach ($dateList as $dStr) {
						$wekday = $dStr;
						$color = (isWeekend($wekday) == 'yes') ? "#7a0404; color:#FFFFFF" : "";
						$label = (int)date('j', strtotime($dStr));
						?>
						<th class="cell" style="padding:0px; text-align: center; border: 1px solid; background-color: <?php echo $color; ?>"><?php echo $label; ?></th>
						<?php
					}
				} else {
					$monthdays = cal_days_in_month(CAL_GREGORIAN, $month, $year); // get days in a month
					for ($i = 1; $i < ($monthdays + 1); $i++) {
						$dy = ($i < 10) ? "0" . $i : $i;
						$wekday = $year . "-" . $month . "-" . $dy;
						$color = (isWeekend($wekday) == 'yes') ? "#7a0404; color:#FFFFFF" : "";
						?>
						<th class="cell" style="padding:0px; text-align: center; border: 1px solid; background-color: <?php echo $color; ?>"><?php echo $i; ?></th>
						<?php
					}
				}
				?>
				<th class="cell" style="width:10%;">Hours</th>
				<th class="cell" style="width:10%;">Days</th>
				<th class="cell" style="width:10%;">% Present</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$no = 0;
			//$nonworkables contains non duty days
			//$workeddays contains  worked days
			$pple = 1;
			foreach ($workinghours as $hours) {
				$personhrs = array();
				$no++;
			?>
				<tr>
					<td class="cell" style="width:7%;"><?php echo $pple++; ?></td>
					<td class='cost' style="text-align:left;"><?php echo $hours['fullname'] . ' ' . $hours['othername']; ?></td>
					<td class='cost'><?php $words = explode(" ", $hours['job']);
										$letters = "";
										foreach ($words as $word) {
											$letters .= $word[0];
										}
										echo $letters;
										?></td>
					<?php
					if (!empty($dateList)) {
						foreach ($dateList as $date_d) {
							?>
							<td class="cell" data-label="Day<?php echo (int)date('j', strtotime($date_d)); ?>">
								<?php
								$pid    = $hours['ihris_pid'];
								$timedata = gettimedata($pid, $date_d);
								if (!empty($timedata)) {
									$starTime = @$timedata->time_in;
									$endTime = @$timedata->time_out;
									$initial_time = strtotime($starTime) / 3600;
									$final_time = strtotime($endTime) / 3600;
									if (empty($initial_time) || empty($final_time)) {
										$hours_worked = 0;
									} elseif ($initial_time == $final_time) {
										$hours_worked = 0;
									} else {
										$hours_worked = round(($final_time - $initial_time), 1);
									}
									if ($hours_worked < 0) {
										echo $hours_worked = $hours_worked * -1;
									} elseif ($hours_worked == -0) {
										echo $hours_worked = 0;
									} else {
										echo $hours_worked;
									}
									array_push($personhrs, $hours_worked);
								}
								?>
							</td>
							<?php
						}
					} else {
						$month_days = cal_days_in_month(CAL_GREGORIAN, $month, $year); //days in a month
						for ($i = 1; $i <= $month_days; $i++) { // repeating td
							$day = "day" . $i;  //changing day 
							?>
							<td class="cell" data-label="Day<?php echo $i; ?>">
								<?php
								$hours_data = $hours[$day];
								$date_d = $year . "-" . $month . "-" . (($i < 10) ? "0" . $i : $i);
								$pid    = $hours['ihris_pid'];
								$timedata = gettimedata($pid, $date_d);
								if (!empty($timedata)) {
									$Time_data = array();
									$starTime = @$timedata->time_in;
									$endTime = @$timedata->time_out;
									$initial_time = strtotime($starTime) / 3600;
									$final_time = strtotime($endTime) / 3600;
									if (empty($initial_time) || empty($final_time)) {
										$hours_worked = 0;
									} elseif ($initial_time == $final_time) {
										$hours_worked = 0;
									} else {
										$hours_worked = round(($final_time - $initial_time), 1);
									}
									if ($hours_worked < 0) {
										echo $hours_worked = $hours_worked * -1;
									} elseif ($hours_worked == -0) {
										echo $hours_worked = 0;
									} else {
										echo $hours_worked;
									}
									array_push($personhrs, $hours_worked);
								}
								?>
							</td>
							<?php
						} //repeat days
					}
					?>
					<td class="cell" style="width:6%;"><?php echo array_sum($personhrs); ?></td>
					<?php
					$worked_days = count($personhrs);
					if (!empty($date_from) && !empty($date_to) && isset($scheduledDaysByPid) && is_array($scheduledDaysByPid)) {
						$twdays = isset($scheduledDaysByPid[$hours['ihris_pid']]) ? (int)$scheduledDaysByPid[$hours['ihris_pid']] : 0;
					} else {
						$mydate = $year . "-" . $month;
						$roster = Modules::run('attendance/attrosta', $mydate, urlencode($hours['ihris_pid']));
						$day = $roster['Day'][0]->days;
						$eve = $roster['Evening'][0]->days;
						$night = $roster['Night'][0]->days;
						$twdays = ($day + $eve + $night);
					}
					$percent_present = ($twdays > 0) ? round(($worked_days / $twdays) * 100, 0) : 0;
					?>
					<td class="cell" style="width:6%;"><?php echo $worked_days . "/" . $twdays; ?></td>
					<td class="cell" style="width:7%;"><?php echo $percent_present . "%"; ?></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
</body>

</html>