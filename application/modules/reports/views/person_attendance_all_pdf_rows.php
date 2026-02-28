<?php
if (empty($records) || !is_array($records)) {
	return;
}
$month_days = cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);
$row_no = isset($start_row_no) ? (int) $start_row_no : 1;
foreach ($records as $row) {
	$p = isset($row->P) ? $row->P : 0;
	$o = isset($row->O) ? $row->O : 0;
	$r = isset($row->R) ? $row->R : 0;
	$l = isset($row->L) ? $row->L : 0;
	$absent = $month_days - ($p + $o + $r + $l);
	$abrate = $month_days > 0 ? number_format(($absent / $month_days) * 100, 1) : 0;
?>
<tr>
	<td><?php echo $row_no++; ?></td>
	<td><?php echo htmlspecialchars(isset($row->fullname) ? $row->fullname : ''); ?></td>
	<td><?php echo htmlspecialchars(isset($row->district) ? $row->district : ''); ?></td>
	<td><?php echo htmlspecialchars(isset($row->facility_name) ? $row->facility_name : ''); ?></td>
	<td><?php echo htmlspecialchars(isset($row->duty_date) ? $row->duty_date : ''); ?></td>
	<td class="num"><?php echo $p; ?></td>
	<td class="num"><?php echo $o; ?></td>
	<td class="num"><?php echo $r; ?></td>
	<td class="num"><?php echo $l; ?></td>
	<td class="num"><?php echo isset($row->H) ? $row->H : 0; ?></td>
	<td class="num"><?php echo $absent; ?></td>
	<td class="num"><?php echo $abrate; ?>%</td>
</tr>
<?php
}
?>
