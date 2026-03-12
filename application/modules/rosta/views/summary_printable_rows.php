<?php
if (empty($sums) || !is_array($sums)) {
	return;
}
$row_no = isset($start_row_no) ? (int) $start_row_no : 1;
foreach ($sums as $sum) {
	$name = (isset($sum['fullname']) ? $sum['fullname'] : '') . ' ' . (isset($sum['othername']) ? $sum['othername'] : '');
	$d = isset($sum['D']) && $sum['D'] !== '' ? $sum['D'] : 0;
	$e = isset($sum['E']) && $sum['E'] !== '' ? $sum['E'] : 0;
	$n = isset($sum['N']) && $sum['N'] !== '' ? $sum['N'] : 0;
	$o = isset($sum['O']) && $sum['O'] !== '' ? $sum['O'] : 0;
	$a = isset($sum['A']) && $sum['A'] !== '' ? $sum['A'] : 0;
	$s = isset($sum['S']) && $sum['S'] !== '' ? $sum['S'] : 0;
	$m = isset($sum['M']) && $sum['M'] !== '' ? $sum['M'] : 0;
	$z = isset($sum['Z']) && $sum['Z'] !== '' ? $sum['Z'] : 0;
	$total = $d + $e + $n + $o + $a + $s + $m + $z;
?>
<tr>
	<td class="num"><?php echo $row_no++; ?></td>
	<td class="name-col"><?php echo htmlspecialchars(trim($name)); ?></td>
	<td class="name-col"><?php echo htmlspecialchars(isset($sum['job']) ? $sum['job'] : ''); ?></td>
	<td class="num"><?php echo $d; ?></td>
	<td class="num"><?php echo $e; ?></td>
	<td class="num"><?php echo $n; ?></td>
	<td class="num"><?php echo $o; ?></td>
	<td class="num"><?php echo $a; ?></td>
	<td class="num"><?php echo $s; ?></td>
	<td class="num"><?php echo $m; ?></td>
	<td class="num"><?php echo $z; ?></td>
	<td class="num"><?php echo $total; ?></td>
</tr>
<?php
}
?>
