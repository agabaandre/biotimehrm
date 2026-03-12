<?php
/**
 * Summary by key (schedule letter) for Monthly Attendance Form PDF.
 * Expects: $summary (letter => count), $key (array of objects with ->letter, ->schedule).
 */
$summary = isset($summary) ? $summary : array();
$key = isset($key) ? $key : array();
?>
<table class="actuals-table" style="margin-top: 14px; font-size: 9pt;">
	<thead>
		<tr>
			<th colspan="2" class="text-left" style="background: #1a5276; color: #fff;">Summary by key</th>
		</tr>
		<tr style="background: #e8eef1;">
			<th class="text-left" style="width: 60%;">Code</th>
			<th class="num" style="width: 40%;">Count</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($key as $schedule) {
			$letter = isset($schedule->letter) ? $schedule->letter : '';
			$label = isset($schedule->schedule) ? $schedule->schedule : $letter;
			$cnt = isset($summary[$letter]) ? (int)$summary[$letter] : 0;
		?>
		<tr>
			<td class="name-col"><?php echo htmlspecialchars($letter . ' (' . $label . ')'); ?></td>
			<td class="num"><?php echo $cnt; ?></td>
		</tr>
		<?php } ?>
	</tbody>
</table>
