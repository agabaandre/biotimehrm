              </tbody>
              <tfoot>
                <tr><td colspan="5">Worked for <?php echo (isset($wdays_worked) ? (int)$wdays_worked : 0) . ' days out of ' . (isset($total_duty) ? (int)$total_duty : 0); ?> days</td></tr>
              </tfoot>
            </table>
            <?php
            $leaves = isset($leaves) ? $leaves : array();
            $requests = isset($requests) ? $requests : array();
            $offs = isset($offs) ? $offs : array();
            $workdays = isset($workdays) ? $workdays : array();
            ?>
            <table class="itl-summary">
              <thead><tr><th>Schedule</th><th>Date</th></tr></thead>
              <tbody>
                <?php foreach ($leaves as $leave) { ?>
                <tr><td>Leave</td><td><?php echo $leave->date ? date('j F, Y', strtotime($leave->date)) : ''; ?></td></tr>
                <?php } ?>
              </tbody>
              <tfoot><tr><th>Total Days on Leave</th><th><?php echo count($leaves); ?> day(s)</th></tr></tfoot>
            </table>
            <table class="itl-summary">
              <thead><tr><th>Type</th><th>From</th><th>To</th></tr></thead>
              <tbody>
                <?php foreach ($requests as $request) { ?>
                <tr><td>Official Request</td><td><?php echo $request->date ? date('j F, Y', strtotime($request->date)) : ''; ?></td><td><?php echo $request->date ? date('j F, Y', strtotime($request->date)) : ''; ?></td></tr>
                <?php } ?>
              </tbody>
              <tfoot><tr><th colspan="2">Total Requests</th><th><?php echo count($requests); ?> day(s)</th></tr></tfoot>
            </table>
            <table class="itl-summary">
              <thead><tr><th colspan="2">Type</th><th>Dates</th></tr></thead>
              <tbody>
                <?php foreach ($offs as $off) { ?>
                <tr><td colspan="2">Off Duty</td><td><?php echo $off->date ? date('j F, Y', strtotime($off->date)) : ''; ?></td></tr>
                <?php } ?>
              </tbody>
              <tfoot><tr><th colspan="2">Total Off Duty Days</th><th><?php echo count($offs); ?> day(s)</th></tr></tfoot>
            </table>
            <table class="itl-summary">
              <thead><tr><th colspan="2">Days Scheduled on Duty</th></tr><tr><th>From</th><th>To</th></tr></thead>
              <tbody>
                <?php foreach ($workdays as $workday) { ?>
                <tr><td><?php echo $workday->duty_date ? date('j F, Y', strtotime($workday->duty_date)) . ' 8:00 AM' : ''; ?></td><td><?php echo $workday->duty_date ? date('j F, Y', strtotime($workday->duty_date)) . ' 5:00 PM' : ''; ?></td></tr>
                <?php } ?>
              </tbody>
              <tfoot><tr><th>Total Duty Days</th><th><?php echo count($workdays); ?> Day(s)</th></tr></tfoot>
            </table>
</body>
</html>
