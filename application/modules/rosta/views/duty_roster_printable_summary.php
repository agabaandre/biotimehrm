<?php
/**
 * Totals row and summary by key for Duty Roster PDF.
 * Expects: $summary (letter => count), $key (array of objects with ->letter).
 */
$summary = isset($summary) ? $summary : array();
$key = isset($key) ? $key : array();
$letters_order = array('D', 'E', 'N', 'O', 'A', 'S', 'M', 'Z');
?>
<table width="100%" style="font-size: 10pt; border-collapse: collapse; margin-top: 12px;" cellpadding="4" cellspacing="0">
	<thead>
		<tr style="background: #f0f0f0;">
			<th style="border: 1px solid #000; padding: 4px; text-align: left;">Totals</th>
			<?php foreach ($letters_order as $letter) { ?>
			<th style="border: 1px solid #000; padding: 4px; text-align: center;"><?php echo htmlspecialchars($letter); ?></th>
			<?php } ?>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td style="border: 1px solid #000; padding: 4px; font-weight: bold;"></td>
			<?php foreach ($letters_order as $letter) {
				$cnt = isset($summary[$letter]) ? (int)$summary[$letter] : 0;
			?>
			<td style="border: 1px solid #000; padding: 4px; text-align: center;"><?php echo $cnt; ?></td>
			<?php } ?>
		</tr>
	</tbody>
</table>
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
