              </tbody>
              <tfoot>
                <?php
                $totalDuty = isset($total_duty) ? (int) $total_duty : 0;
                $totalLeaves = isset($total_leaves) ? (int) $total_leaves : 0;
                $totalRequests = isset($total_requests) ? (int) $total_requests : 0;
                $toffs = isset($total_offs) ? (int) $total_offs : 0;
                $wdays = isset($wdays_worked) ? (int) $wdays_worked : 0;
                $totalHours = isset($total_hours) ? (float) $total_hours : 0;
                $totalDutyDisplay = function_exists('totalDutys') ? totalDutys($totalDuty) : $totalDuty;
                $presentAccounted = function_exists('person_att_percent_present_helper')
                  ? person_att_percent_present_helper($wdays, $totalDutyDisplay, true)
                  : ($totalDutyDisplay > 0 ? round(($wdays / $totalDutyDisplay) * 100, 1) . ' %' : '0 %');
                ?>
                <tr><td colspan="4">Total Scheduled/Roster Days (D)</td><td class="num"><?php echo $totalDutyDisplay; ?> Days</td></tr>
                <tr><td colspan="4">Total Leave Days (L)</td><td class="num"><?php echo $totalLeaves; ?> Days</td></tr>
                <tr><td colspan="4">Total Official Request (R)</td><td class="num"><?php echo $totalRequests; ?> Days</td></tr>
                <tr><td colspan="4">Total days Off Duty (O)</td><td class="num"><?php echo $toffs; ?> Days</td></tr>
                <tr><td colspan="4" style="font-weight:bold;">Total Days Worked</td><td class="num" style="font-weight:bold;"><?php echo $wdays <= $totalDuty ? $wdays . ' Days out of ' . $totalDutyDisplay : $wdays . ' Days'; ?></td></tr>
                <tr><td colspan="4" style="font-weight:bold;">Total Hours Worked</td><td class="num" style="font-weight:bold;"><?php echo abs($totalHours); ?> Hours</td></tr>
                <tr><td colspan="4" style="font-weight:bold;">%Present/Accounted</td><td class="num" style="font-weight:bold;"><?php echo $presentAccounted; ?></td></tr>
              </tfoot>
            </table>
</body>
</html>
