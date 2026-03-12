<?php
if (empty($rows) || !is_array($rows)) {
  return;
}
$row_no = isset($start_row_no) ? (int) $start_row_no : 1;
foreach ($rows as $log) {
  $name = trim(($log->surname ?? '') . ' ' . ($log->firstname ?? ''));
  $dateStr = $log->date ?? '';
  $dateFormatted = !empty($dateStr) ? date('F, Y', strtotime($dateStr)) : '';
  $hours = number_format($log->m_timediff ?? 0, 2);
?>
<tr>
  <td><?php echo $row_no++; ?></td>
  <td><?php echo htmlspecialchars($name); ?></td>
  <td><?php echo htmlspecialchars($log->job ?? ''); ?></td>
  <td><?php echo htmlspecialchars($log->facility ?? ''); ?></td>
  <td><?php echo htmlspecialchars($log->department ?? ''); ?></td>
  <td><?php echo htmlspecialchars($dateFormatted); ?></td>
  <td><?php echo $hours; ?></td>
</tr>
<?php
}
?>
