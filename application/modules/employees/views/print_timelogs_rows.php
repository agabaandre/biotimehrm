<?php
if (empty($logs) || !is_array($logs)) {
  return;
}
$row_no = isset($start_row_no) ? (int) $start_row_no : 1;
foreach ($logs as $timelog) {
  $time_in = $timelog->time_in ?? null;
  $time_out = $timelog->time_out ?? null;
  $hours_worked = 0;
  if ($time_in && $time_out) {
    $initial_time = strtotime($time_in) / 3600;
    $final_time = strtotime($time_out) / 3600;
    if ($initial_time != 0 && $final_time != 0 && $initial_time != $final_time) {
      $hours_worked = round($final_time - $initial_time, 1);
      if ($hours_worked < 0) {
        $hours_worked = abs($hours_worked);
      }
    }
  }
  $time_out_display = '';
  if (!empty($time_out)) {
    $time_out_display = date('H:i:s', strtotime($time_out));
  }
?>
<tr>
  <td class="num"><?php echo $row_no++; ?></td>
  <td class="name-col"><?php echo htmlspecialchars(trim(($timelog->surname ?? '') . ' ' . ($timelog->firstname ?? ''))); ?></td>
  <td class="name-col"><?php echo htmlspecialchars($timelog->job ?? ''); ?></td>
  <td class="name-col"><?php echo htmlspecialchars($timelog->date ?? ''); ?></td>
  <td class="num"><?php echo $time_in ? date('H:i:s', strtotime($time_in)) : ''; ?></td>
  <td class="num"><?php echo $time_out_display; ?></td>
  <td class="num"><?php echo $hours_worked . ' hr(s)'; ?></td>
</tr>
<?php
}
?>
