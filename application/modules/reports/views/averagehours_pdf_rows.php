<?php
if (empty($sums) || !is_array($sums)) {
	return;
}
$row_no = isset($start_row_no) ? (int) $start_row_no : 1;
foreach ($sums as $sum) {
	$month_fmt = !empty($sum['month_year']) ? date('F, Y', strtotime($sum['month_year'] . '-01')) : '';
	$avg = isset($sum['avg_hours']) ? $sum['avg_hours'] : '';
?>
<tr>
	<td class="num"><?php echo $row_no++; ?></td>
	<td class="name-col"><?php echo htmlspecialchars($month_fmt); ?></td>
	<td class="num"><?php echo htmlspecialchars($avg); ?></td>
</tr>
<?php
}
?>
