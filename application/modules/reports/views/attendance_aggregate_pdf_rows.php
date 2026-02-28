<?php
if (empty($records) || !is_array($records)) {
	return;
}
$grouped_by = isset($grouped_by) ? $grouped_by : 'district';
$row_no = isset($start_row_no) ? (int) $start_row_no : 1;
foreach ($records as $row) {
	$supposed_days = isset($row->days_supposed) ? $row->days_supposed : 0;
	$days_worked = $supposed_days - (isset($row->days_absent) ? $row->days_absent : 0);
	if ($supposed_days > 0) {
		$attendance_rate = number_format(($days_worked / $supposed_days) * 100, 1);
		$absentism_rate = number_format((isset($row->days_absent) ? $row->days_absent : 0) / $supposed_days * 100, 1);
		$present = number_format((isset($row->present) ? $row->present : 0) / $supposed_days * 100, 1);
		$on_leave = number_format((isset($row->own_leave) ? $row->own_leave : 0) / $supposed_days * 100, 1);
		$official = number_format((isset($row->official) ? $row->official : 0) / $supposed_days * 100, 1);
		$off = number_format((isset($row->off) ? $row->off : 0) / $supposed_days * 100, 1);
		$holiday = number_format((isset($row->holiday) ? $row->holiday : 0) / $supposed_days * 100, 1);
		$absent = number_format((isset($row->absent) ? $row->absent : 0) / $supposed_days * 100, 1);
	} else {
		$attendance_rate = $absentism_rate = $present = $on_leave = $official = $off = $holiday = $absent = '0';
	}
	$group_val = isset($row->{$grouped_by}) ? $row->{$grouped_by} : 'N/A';
?>
<tr>
	<td><?php echo $row_no++; ?></td>
	<td><?php echo htmlspecialchars($group_val); ?></td>
	<td><?php echo htmlspecialchars($row->duty_date ?? ''); ?></td>
	<td class="num"><?php echo $present; ?>%</td>
	<td class="num"><?php echo $off; ?>%</td>
	<td class="num"><?php echo $official; ?>%</td>
	<td class="num"><?php echo $on_leave; ?>%</td>
	<td class="num"><?php echo $holiday; ?>%</td>
	<td class="num"><?php echo $absent; ?>%</td>
	<td class="num"><?php echo number_format($days_worked, 1); ?></td>
	<td class="num"><?php echo number_format($supposed_days, 1); ?></td>
	<td class="num"><?php echo $attendance_rate; ?>%</td>
	<td class="num"><?php echo $absentism_rate; ?>%</td>
</tr>
<?php
}
?>
