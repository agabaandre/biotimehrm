<?php
if (!isset($duties) || !is_array($duties)) {
	return;
}
$schedules = isset($schedules) ? $schedules : array();
$start_row_no = isset($start_row_no) ? (int) $start_row_no : 1;
$monthdays = cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);
$no = $start_row_no;
foreach ($duties as $singleduty) {
	$pid = isset($singleduty['ihris_pid']) ? $singleduty['ihris_pid'] : '';
?>
<tr>
	<td class='cost'><?php echo $no++; ?></td>
	<td class='cost' style="text-align:left;"><?php echo isset($singleduty['fullname']) ? $singleduty['fullname'] : ''; ?></td>
	<td class='cost' style="text-align:left;"><?php echo isset($singleduty['job']) ? character_limiter($singleduty['job'], 15) : ''; ?></td>
	<?php for ($i = 1; $i < $monthdays + 1; $i++) {
		$date_d = $year . '-' . $month . '-' . ($i < 10 ? '0' . $i : $i);
		$letter = isset($schedules[$pid][$date_d]) ? $schedules[$pid][$date_d] : '';
	?>
	<td class="cost"><?php echo $letter; ?></td>
	<?php } ?>
</tr>
<?php
}
