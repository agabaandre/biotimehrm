              </tbody>
              <tfoot>
                <tr><th colspan="4">Worked for <?php echo (isset($wdays_worked) ? (int)$wdays_worked : 0) . ' days out of ' . (isset($total_duty) ? (int)$total_duty : 0); ?> days</th></tr>
              </tfoot>
            </table>
            <?php
            $leaves = isset($leaves) ? $leaves : array();
            $requests = isset($requests) ? $requests : array();
            $offs = isset($offs) ? $offs : array();
            $workdays = isset($workdays) ? $workdays : array();
            ?>
            <table class="minimalistBlack" style="margin-top:20px;">
              <thead><tr><th>SCHEDULE</th><th>DATE</th></tr></thead>
              <tbody>
                <?php foreach ($leaves as $leave) { ?>
                <tr><td>Leave</td><td><?php echo $leave->date ? date('j F, Y', strtotime($leave->date)) : ''; ?></td></tr>
                <?php } ?>
              </tbody>
              <tfoot><tr><th>TOTAL DAYS ON LEAVE</th><th><?php echo count($leaves); ?> day(s)</th></tr></tfoot>
            </table>
            <table class="minimalistBlack" style="margin-top:20px;">
              <thead><tr><th>TYPE</th><th>FROM</th><th>TO</th></tr></thead>
              <tbody>
                <?php foreach ($requests as $request) { ?>
                <tr><td>OFFICIAL REQUEST</td><td><?php echo $request->date ? date('j F, Y', strtotime($request->date)) : ''; ?></td><td><?php echo $request->date ? date('j F, Y', strtotime($request->date)) : ''; ?></td></tr>
                <?php } ?>
              </tbody>
              <tfoot><tr><th colspan="2">TOTAL REQUESTS</th><th><?php echo count($requests); ?> day(s)</th></tr></tfoot>
            </table>
            <table class="minimalistBlack" style="margin-top:20px;">
              <thead><tr><th colspan="2">TYPE</th><th>DATES</th></tr></thead>
              <tbody>
                <?php foreach ($offs as $off) { ?>
                <tr><td colspan="2">OFF DUTY</td><td><?php echo $off->date ? date('j F, Y', strtotime($off->date)) : ''; ?></td></tr>
                <?php } ?>
              </tbody>
              <tfoot><tr><th colspan="2">TOTAL OFF DUTY DAYS</th><th><?php echo count($offs); ?> day(s)</th></tr></tfoot>
            </table>
            <table class="minimalistBlack" style="margin-top:5px;">
              <thead><tr><th colspan="2">DAYS SCHEDULED ON DUTY</th></tr><tr><th colspan="2">FROM</th><th>TO</th></tr></thead>
              <tbody>
                <?php foreach ($workdays as $workday) { ?>
                <tr><td colspan="2"><?php echo $workday->duty_date ? date('j F, Y', strtotime($workday->duty_date)) . ' 8:00 AM' : ''; ?></td><td><?php echo $workday->duty_date ? date('j F, Y', strtotime($workday->duty_date)) . ' 5:00 PM' : ''; ?></td></tr>
                <?php } ?>
              </tbody>
              <tfoot><tr><th colspan="2">TOTAL DUTY DAYS</th><th><?php echo count($workdays); ?> Day(s)</th></tr></tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
