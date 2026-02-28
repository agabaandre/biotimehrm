<?php
/**
 * Summary by key (letters only) for Duty Roster PDF.
 * Expects: $summary (letter => count), $key (array of objects with ->letter).
 */
$summary = isset($summary) ? $summary : array();
$key = isset($key) ? $key : array();
?>
<table width="100%" class="items" style="font-size: 11pt; border-collapse: collapse; margin-top: 12px;" cellpadding="6">
	<thead>
		<tr style="background: #EEEEEE;">
			<th colspan="2" style="text-align: left; border: 1px solid #000;">Summary by key</th>
		</tr>
		<tr style="background: #f5f5f5;">
			<th style="text-align: left; border: 1px solid #000;">Letter</th>
			<th style="text-align: right; border: 1px solid #000;">Count</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($key as $schedule) {
			$letter = isset($schedule->letter) ? $schedule->letter : '';
			$cnt = isset($summary[$letter]) ? (int)$summary[$letter] : 0;
		?>
		<tr>
			<td style="border: 1px solid #000;"><?php echo htmlspecialchars($letter); ?></td>
			<td style="border: 1px solid #000; text-align: right;"><?php echo $cnt; ?></td>
		</tr>
		<?php } ?>
	</tbody>
</table>
