<?php
if (empty($sums) || !is_array($sums)) {
	return;
}
$start_row_no = isset($start_row_no) ? (int) $start_row_no : 1;
$no = $start_row_no;
foreach ($sums as $sum) {
	$present = isset($sum['P']) && $sum['P'] !== '' ? (int) $sum['P'] : 0;
	$O = isset($sum['O']) && $sum['O'] !== '' ? (int) $sum['O'] : 0;
	$R = isset($sum['R']) && $sum['R'] !== '' ? (int) $sum['R'] : 0;
	$L = isset($sum['L']) && $sum['L'] !== '' ? (int) $sum['L'] : 0;
	$H = isset($sum['H']) && $sum['H'] !== '' ? (int) $sum['H'] : 0;
	$base_line = isset($sum['base_line']) && $sum['base_line'] !== '' && $sum['base_line'] !== null ? $sum['base_line'] : 0;
	$r_days = function_exists('person_att_expected_days_helper')
		? person_att_expected_days_helper($base_line, $O, $L, $R, $H)
		: max(0, (int) $base_line - $O - $L - $R - $H);
	$ab = function_exists('person_att_absent_helper') ? person_att_absent_helper($present, $r_days) : max(0, $r_days - $present);
	$per = function_exists('person_att_percent_present_helper')
		? person_att_percent_present_helper($present, $r_days, true)
		: ($r_days > 0 ? round(($present / $r_days) * 100, 1) . ' %' : '0 %');
	$name = (isset($sum['fullname']) ? $sum['fullname'] : '') . ' ' . (isset($sum['othername']) ? $sum['othername'] : '');
?>
<tr>
	<td class="num"><?php echo $no++; ?></td>
	<td class="name-col"><?php echo htmlspecialchars(trim($name)); ?></td>
	<td class="name-col"><?php echo htmlspecialchars(isset($sum['job']) ? $sum['job'] : ''); ?></td>
	<td class="name-col"><?php echo htmlspecialchars(isset($sum['department_id']) ? $sum['department_id'] : ''); ?></td>
	<td class="num"><?php echo $O; ?></td>
	<td class="num"><?php echo $R; ?></td>
	<td class="num"><?php echo $L; ?></td>
	<td class="num"><?php echo $H; ?></td>
	<td class="num"><?php echo $r_days; ?></td>
	<td class="num"><?php echo $present; ?></td>
	<td class="num"><?php echo $ab; ?></td>
	<td class="num"><?php echo $per; ?></td>
</tr>
<?php
}
?>
