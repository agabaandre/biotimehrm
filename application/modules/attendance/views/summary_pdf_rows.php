<?php
if (empty($sums) || !is_array($sums)) {
	return;
}
$scheduledDaysByPid = isset($scheduledDaysByPid) ? $scheduledDaysByPid : array();
$start_row_no = isset($start_row_no) ? (int) $start_row_no : 1;
$no = $start_row_no;
foreach ($sums as $sum) {
	$pid = isset($sum['ihris_pid']) ? $sum['ihris_pid'] : '';
	$present = isset($sum['P']) && $sum['P'] !== '' ? $sum['P'] : 0;
	$O = isset($sum['O']) && $sum['O'] !== '' ? $sum['O'] : 0;
	$R = isset($sum['R']) && $sum['R'] !== '' ? $sum['R'] : 0;
	$L = isset($sum['L']) && $sum['L'] !== '' ? $sum['L'] : 0;
	$H = isset($sum['H']) && $sum['H'] !== '' ? $sum['H'] : 0;
	$r_days = isset($scheduledDaysByPid[$pid]) ? (int) $scheduledDaysByPid[$pid] : 0;
	if ($r_days == 0) {
		$r_days = 22;
	}
	$ab = function_exists('days_absent_helper') ? days_absent_helper($present, $r_days) : max(0, $r_days - $present);
	$per = function_exists('per_present_helper') ? per_present_helper($present, $r_days) : ($r_days > 0 ? round(($present / $r_days) * 100, 1) : 0);
	$name = (isset($sum['fullname']) ? $sum['fullname'] : '') . ' ' . (isset($sum['othername']) ? $sum['othername'] : '');
?>
<tr>
	<td><?php echo $no++; ?></td>
	<td><?php echo htmlspecialchars(trim($name)); ?></td>
	<td><?php echo htmlspecialchars(isset($sum['job']) ? $sum['job'] : ''); ?></td>
	<td><?php echo htmlspecialchars(isset($sum['department_id']) ? $sum['department_id'] : ''); ?></td>
	<td><?php echo $O; ?></td>
	<td><?php echo $R; ?></td>
	<td><?php echo $L; ?></td>
	<td><?php echo $H; ?></td>
	<td><?php echo $r_days; ?></td>
	<td><?php echo $present; ?></td>
	<td><?php echo $ab <= 0 ? 0 : $ab; ?></td>
	<td><?php echo $per; ?></td>
</tr>
<?php
}
?>
