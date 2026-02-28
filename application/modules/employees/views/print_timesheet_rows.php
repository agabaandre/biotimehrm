<?php
/**
 * Renders only the tbody rows for a batch of employees (streaming print).
 * Expects: $workinghours (batch), $logs_by_pid_date, $scheduledDaysByPid, $month, $year,
 *          $dateList (optional for range), $date_from, $date_to (optional), $start_row_no.
 */
if (!isset($workinghours) || !is_array($workinghours)) {
	return;
}
$dateList = isset($dateList) ? $dateList : array();
$scheduledDaysByPid = isset($scheduledDaysByPid) ? $scheduledDaysByPid : array();
$logs_by_pid_date = isset($logs_by_pid_date) ? $logs_by_pid_date : array();
$start_row_no = isset($start_row_no) ? (int) $start_row_no : 1;
$pple = $start_row_no;
foreach ($workinghours as $hours) {
	$personhrs = array();
	$pid = isset($hours['ihris_pid']) ? $hours['ihris_pid'] : '';
?>
<tr>
	<td class="cell" style="width:7%;"><?php echo $pple++; ?></td>
	<td class='cost' style="text-align:left;"><?php echo isset($hours['fullname']) ? trim($hours['fullname']) : ''; ?></td>
	<td class='cost'><?php
		$job = isset($hours['job']) ? $hours['job'] : '';
		$words = explode(" ", $job);
		$letters = "";
		foreach ($words as $word) {
			$letters .= isset($word[0]) ? $word[0] : '';
		}
		echo $letters;
	?></td>
	<?php
	if (!empty($dateList)) {
		foreach ($dateList as $date_d) {
			$timedata = isset($logs_by_pid_date[$pid][$date_d]) ? $logs_by_pid_date[$pid][$date_d] : null;
			$hours_worked = 0;
			if (!empty($timedata)) {
				$starTime = isset($timedata->time_in) ? $timedata->time_in : null;
				$endTime = isset($timedata->time_out) ? $timedata->time_out : null;
				$initial_time = $starTime ? strtotime($starTime) / 3600 : 0;
				$final_time = $endTime ? strtotime($endTime) / 3600 : 0;
				if ($initial_time && $final_time && $initial_time != $final_time) {
					$hours_worked = round($final_time - $initial_time, 1);
				}
				if ($hours_worked < 0) { $hours_worked = -$hours_worked; }
				$personhrs[] = $hours_worked;
			}
			?>
			<td class="cell" data-label="Day<?php echo (int)date('j', strtotime($date_d)); ?>"><?php echo $hours_worked ? $hours_worked : ''; ?></td>
			<?php
		}
	} else {
		$month_days = cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);
		for ($i = 1; $i <= $month_days; $i++) {
			$date_d = $year . "-" . $month . "-" . ($i < 10 ? "0" . $i : $i);
			$timedata = isset($logs_by_pid_date[$pid][$date_d]) ? $logs_by_pid_date[$pid][$date_d] : null;
			$hours_worked = 0;
			if (!empty($timedata)) {
				$starTime = isset($timedata->time_in) ? $timedata->time_in : null;
				$endTime = isset($timedata->time_out) ? $timedata->time_out : null;
				$initial_time = $starTime ? strtotime($starTime) / 3600 : 0;
				$final_time = $endTime ? strtotime($endTime) / 3600 : 0;
				if ($initial_time && $final_time && $initial_time != $final_time) {
					$hours_worked = round($final_time - $initial_time, 1);
				}
				if ($hours_worked < 0) { $hours_worked = -$hours_worked; }
				$personhrs[] = $hours_worked;
			}
			?>
			<td class="cell" data-label="Day<?php echo $i; ?>"><?php echo $hours_worked ? $hours_worked : ''; ?></td>
			<?php
		}
	}
	$worked_days = count($personhrs);
	?>
	<td class="cell" style="width:6%;"><?php echo array_sum($personhrs); ?></td>
	<td class="cell" style="width:6%;"><?php echo $worked_days; ?></td>
</tr>
<?php
}
