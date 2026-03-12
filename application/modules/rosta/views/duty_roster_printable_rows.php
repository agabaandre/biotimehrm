<?php
if (empty($duties) || !is_array($duties)) {
	return;
}
$schedules = isset($schedules) ? $schedules : array();
$start_row_no = isset($start_row_no) ? (int) $start_row_no : 1;
$monthdays = cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);
$no = $start_row_no;
$total_letters = array('D', 'E', 'N', 'O', 'A', 'S', 'M', 'Z');
foreach ($duties as $singleduty) {
	$pid = isset($singleduty['ihris_pid']) ? $singleduty['ihris_pid'] : '';
	$counts = array('D' => 0, 'E' => 0, 'N' => 0, 'O' => 0, 'A' => 0, 'S' => 0, 'M' => 0, 'Z' => 0);
	for ($i = 1; $i < $monthdays + 1; $i++) {
		$date_d = $year . '-' . $month . '-' . ($i < 10 ? '0' . $i : $i);
		$letter = isset($schedules[$pid][$date_d]) ? strtoupper(trim($schedules[$pid][$date_d])) : '';
		if ($letter !== '' && isset($counts[$letter])) {
			$counts[$letter]++;
		}
	}
?>
<tr>
	<td class="num"><?php echo $no++; ?></td>
	<td class="name-col"><?php echo isset($singleduty['fullname']) ? htmlspecialchars($singleduty['fullname']) : ''; ?></td>
	<?php
		$job = isset($singleduty['job']) ? $singleduty['job'] : '';
		$job = str_replace(array('&#8230;', '…', "\xE2\x80\xA6"), '', $job);
	?>
	<td class="name-col"><?php echo $job !== '' ? htmlspecialchars(character_limiter($job, 15)) : ''; ?></td>
	<?php for ($i = 1; $i < $monthdays + 1; $i++) {
		$date_d = $year . '-' . $month . '-' . ($i < 10 ? '0' . $i : $i);
		$letter = isset($schedules[$pid][$date_d]) ? $schedules[$pid][$date_d] : '';
	?>
	<td class="num"><?php echo $letter; ?></td>
	<?php } ?>
	<?php foreach ($total_letters as $l) { ?>
	<td class="num"><?php echo $counts[$l]; ?></td>
	<?php } ?>
</tr>
<?php
}
?>
