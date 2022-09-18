<!-- Contains page content -->
<div class="container-fluid">
  <div class="row">
    <script>
      $('#timelogs').DataTable({
        responsive: true
      });
    </script>
    <section class="col-lg-12">
      <!-- Custom tabs (Charts with tabs)-->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">
            <div class="card-tools">
              <form class="form-horizontal" action="<?php echo base_url(); ?>employees/employeeTimeLogs/<?php echo $this->uri->segment(3); ?>" method="post">
                <div class="row">
                  <div class="form-group col-md-4">
                    <label>Date From:</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">
                          <i class="far fa-calendar-alt"></i>
                        </span>
                      </div>
                      <input type="text" name="date_from" class="form-control datepicker" value="<?php echo date("Y-m") . "-01"; ?>" autocomplete="off">
                    </div>
                    <!-- /.input group -->
                  </div>
                  <div class="form-group col-md-4">
                    <label>Date To:</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">
                          <i class="far fa-calendar-alt"></i>
                        </span>
                      </div>
                      <input type="text" name="date_to" class="form-control datepicker " value="<?php echo date('Y-m-d'); ?>" autocomplete="off">
                    </div>
                    <!-- /.input group -->
                  </div>
                  <div class="form-group col-md-4">
                    <div class="form-group col-md-3" style="margin-top:7px;">
                      <label></label>
                      <button type="submit" class="btn bt-sm bg-gray-dark color-pale" style="width:200px;"><i class="fa fa-tasks" aria-hidden="true"></i>Apply</button>
                    </div>
                  </div>
              </form>
            </div><!-- /.card-header -->
            <?php if ($this->input->post('date_from')) { ?>
              <a class="btn bt-sm bg-gray-dark color-pale" style="width:200px;" target="_blank" href="<?php echo base_url(); ?>employees/printindividualTimeLogs/<?php echo $this->uri->segment(3); ?>/<?php echo date("Y-m-d", strtotime($from)); ?>/<?php echo date("Y-m-d", strtotime($to)); ?>/1"><i class="fa fa-print"></i>Print This View</a>
              <a class="btn bt-sm bg-gray-dark color-pale" style="width:200px;" target="_blank" href="<?php echo base_url(); ?>employees/printindividualTimeLogs/<?php echo $this->uri->segment(3); ?>/<?php echo date("Y-m-d", strtotime($from)); ?>/<?php echo date("Y-m-d", strtotime($to)); ?>/2"><i class="fa fa-print"></i>Print Detailed View</a>
            <?php } ?>
            </p>
        </div>
      </div>
      <div class="card-body">
        <p class="panel-title" style="font-weight:bold; font-size:16px; text-align:center;"> ATTENDANCE LOG FOR
          <?php
          echo " - " . $_SESSION['facility_name'] . " BEWTWEEN ";
          if ($this->input->post('date_from')) {
            echo $this->input->post('date_from') . " AND ";
          } else {
            echo date("Y-m-d", strtotime("-1 month")) . " AND ";
          }
          if ($this->input->post('date_to')) {
            echo $this->input->post('date_to');
          } else {
            echo date("Y-m-d");
          }
          ?>
        </p>
        <div class="row">
          <div class="col-md-4">
            <h4><?php echo $employee->surname . " " . $employee->firstname; ?></h4>
          </div>
          <div class="col-md-4">
            <h4><?php echo $employee->job; ?></h4>
          </div>
          <div class="col-md-4">
            <h4><?php echo $employee->facility; ?></h4>
          </div>
          <div class="col-md-4">
          </div>
        </div>
        <?php
        $totalDuty = 0;
        foreach ($workdays as $workday) {
          $no++;
          date('j F,Y', strtotime($workday->duty_date)) . " ";
          date('j F,Y', strtotime($workday->duty_date)) . " ";
          $totalDuty++;
        }
        ?>
        <div class="col-md-12">
          <table class="table table-striped thistbl" id="timelogs">
            <thead>
              <tr>
                <th>#</th>
                <th>DATE</th>
                <th>TIME IN</th>
                <th>TIME OUT</th>
                <th width="30%">SUMMARY</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $wdays = 0;
              $totalHours = 0;
              foreach ($timelogs as $timelog) {
                $wdays++;
              ?>
                <tr>
                  <td><?php echo $wdays; ?></td>
                  <td><?php echo date('j F,Y', strtotime($timelog->date)); ?></td>
                  <td><?php echo    date('H:i:s', strtotime($timelog->time_in)); ?></td>
                  <td><?php if (!empty($time_out = $timelog->time_out)) {
                        echo date('H:i:s', strtotime($time_out = $timelog->time_out));
                      } ?></td>
                  <td>
                    <?php
                    $initial_time = strtotime($timelog->time_in) / 3600;
                    $final_time = strtotime($timelog->time_out) / 3600;
                    if (($initial_time) == 0 || ($final_time) == 0) {
                      $hours_worked = 0;
                    } elseif ($initial_time == $final_time) {
                      $hours_worked = 0;
                    } else {
                      $hours_worked = round(($final_time - $initial_time), 1);
                    }
                    if ($hours_worked < 0) {
                      echo $hours_worked = ($hours_worked * -1) . 'hr(s)';
                    } else {
                      echo $hours_worked . 'hr(s)';
                    } ?>
                  </td>
                </tr>
              <?php
                $totalHours += $hours_worked;
              } ?>
            </tbody>
            <tfoot>
              <!---leaves-->
              <?php
              $no = 0;
              $totalLeaves = 0;
              foreach ($leaves as $leave) {
                $no++;
              ?>
                <?php "Leave"; ?>
                <?php date('j F,Y', strtotime($leave->date)); ?>
              <?php
                $totalLeaves += 1;
              } ?>
              <tr>
                <td colspan="4">Total Leave Days(L)</td>
                <td><?= $totalLeaves ?> Days</td>
              </tr>
              <!--/leaves-->
              <!---Req-->
              <?php
              $totalRequests = 0;
              foreach ($requests as $request) {
                $no++;
              ?>
                <?php echo date('j F,Y', strtotime($request->date)); ?>
                <?php echo date('j F,Y', strtotime($request->date)); ?>
              <?php
                $totalRequests = 1;
              } ?>
              <tr>
                <td colspan="4">Total Official Request(R)</td>
                <td><?= $totalRequests ?> Days</td>
              </tr>
              <?php
              $toffs = 0;
              foreach ($offs as $off) {
                $no++;
              ?>
                <?php "OFF DUTY"; ?>
                <?php date('j F,Y', strtotime($off->date)); ?>
              <?php $toffs++;
              }
              ?>
              <tr>
                <td colspan="4">Total days Off Duty(O)</td>
                <td><?= $toffs ?>Days</td>
              </tr>
              <tr>
                <td colspan="4" style="font-weight:bold;">TOTAL DAYS WORKED</td>
                <td style="font-weight:bold;"><?php echo $wdays; ?> Days out of <?php echo totalDutys($totalDuty); ?></td>
              </tr>
              <tr>
                <td colspan="4" style="font-weight:bold;">TOTAL HOURS WORKED</td>
                <td style="font-weight:bold;"><?= abs($totalHours) ?> Hours</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
  </div>
</div>
<?php
function totalDutys($totalDuty)
{
  $dutydays = $totalDuty;
  return $dutydays;
}
?>
<script type="text/javascript">
  $(document).ready(function() {
    $('#thistbl').slimscroll({
      height: '400px',
      size: '5px'
    });
  });
</script>