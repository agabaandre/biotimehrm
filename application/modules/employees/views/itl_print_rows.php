<?php
if (empty($timelogs) || !is_array($timelogs)) {
  $timelogs = isset($timelogs) && is_object($timelogs) ? $timelogs : array();
}
$start_no = isset($start_row_no) ? (int) $start_row_no : 1;
$wdays = $start_no - 1;
foreach ($timelogs as $timelog) {
  $wdays++;
  $time_out = isset($timelog->time_out) ? $timelog->time_out : null;
  $initial_time = $timelog->time_in ? (strtotime($timelog->time_in) / 3600) : 0;
  $final_time = $time_out ? (strtotime($time_out) / 3600) : 0;
  if ($initial_time == 0 || $final_time == 0 || $initial_time == $final_time) {
    $hours_worked = 0;
  } else {
    $hours_worked = round($final_time - $initial_time, 1);
  }
  if ($hours_worked < 0) {
    $hours_worked = $hours_worked * -1;
  }
?>
<tr>
  <td class="num"><?php echo $wdays; ?></td>
  <td class="name-col"><?php echo $timelog->date ? date('j F, Y', strtotime($timelog->date)) : ''; ?></td>
  <td class="num"><?php echo $timelog->time_in ? date('H:i:s', strtotime($timelog->time_in)) : ''; ?></td>
  <td class="num"><?php echo $time_out ? date('H:i:s', strtotime($time_out)) : ''; ?></td>
  <td class="num"><?php echo $hours_worked; ?> hr(s)</td>
</tr>
<?php
}
?>
