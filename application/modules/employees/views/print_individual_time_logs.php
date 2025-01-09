    <script>
      $('.datepicker').datepicker({
        format: 'mm/dd/yyyy',
        startDate: '-3d'
      });
    </script>
    <?php
    //  $timelogs=Modules::run('employees/getTimeLogs');
    //print_r($timelogs);
    ?>
    <style>
      table.minimalistBlack {
        border: 3px solid #000000;
        width: 100%;
        text-align: left;
        border-collapse: collapse;
      }

      table.minimalistBlack td,
      table.minimalistBlack th {
        border: 1px solid #000000;
        padding: 5px 4px;
      }

      table.minimalistBlack tbody td {
        font-size: 12px;
      }

      table.minimalistBlack thead {
        background: #CFCFCF;
        background: -moz-linear-gradient(top, #dbdbdb 0%, #d3d3d3 66%, #CFCFCF 100%);
        background: -webkit-linear-gradient(top, #dbdbdb 0%, #d3d3d3 66%, #CFCFCF 100%);
        background: linear-gradient(to bottom, #dbdbdb 0%, #d3d3d3 66%, #CFCFCF 100%);
        border-bottom: 2px solid #000000;
      }

      table.minimalistBlack thead th {
        font-size: 12px;
        font-weight: bold;
        color: #000000;
        text-align: left;
      }

      table.minimalistBlack tfoot {
        font-size: 12px;
        font-weight: bold;
        color: #000000;
        border-top: 3px solid #000000;
      }

      table.minimalistBlack tfoot td {
        font-size: 12px;
      }
    </style>
    <!-- Contains page content -->
    <div class="dashtwo-order-area" style="padding-top: 10px;">
      <div class="container-fluid">
        <div class="row">
          <script>
            $('#timelogs').DataTable({
              responsive: true
            });
          </script>
          <div class="col-lg-12">
            <div class="panel panel-default">
              <div class="panel-heading">
                <?php
                $totalDuty = 0;
                foreach ($workdays as $workday) {
                  $no++;
                  date('j F,Y', strtotime($workday->duty_date)) . " ";
                  date('j F,Y', strtotime($workday->duty_date)) . " ";
                  $totalDuty++;
                }
                ?>
                <h3 class="panel-title" style="text-align:center;"><?php echo strtoupper($employee->facility) . " "; ?>PERSON ATTENDANCE REPORT<h3>
              </div>
              <div class="panel-body">
                <div class="col-md-12" style="margin-bottom:10px;">
                  <table class="minimalistBlack">
                    <tbody>
                      <tr>
                        <td>EMPLOYEE NAME</td>
                        <td>
                          <p><?php echo $employee->surname . " " . $employee->firstname; ?></p>
                        </td>
                      </tr>
                      <tr>
                        <td>DESIGNATION</td>
                        <td><?php echo $employee->job; ?></td>
                      </tr>
                      <tr>
                        <td>FACILITY</td>
                        <td>
                          <p><?php echo $employee->facility; ?></p>
                        </td>
                      </tr>
                      <tr>
                        <td>DEPARTMENT</td>
                        <td>
                          <p><?php echo $employee->department; ?></p>
                        </td>
                      </tr>
                      <!-- <tr>
                        <td>DIVISION</td>
                        <td><p><?php //echo $employee->division; 
                                ?></p></td>
                        </tr>
                        <tr>
                        <td>UNIT</td>
                        <td><p><?php //echo $employee->unit; 
                                ?></p></td>
                        </tr> -->
                      <tr>
                        <td>PERIOD</td>
                        <td>
                          <h5><i>From: <?php echo date('j F,Y', strtotime($this->uri->segment(4)));  ?> To: <?php echo date('j F,Y', strtotime($this->uri->segment(5))); ?></i></h5>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <table class="minimalistBlack" id="timelogs">
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
                        <td><?php echo date('H:i:s', strtotime($timelog->time_in)); ?></td>
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
                            echo ($hours_worked * -1) . 'hr(s)';
                          } else {
                            echo $hours_worked . 'hr(s)';
                          } ?>
                        </td>
                      </tr>
                    <?php
                      $totalHours += $hours_worked;
                      // $values= unique_array($timelog->date);
                      // print_r($values);
                      $twdays = array();
                      array_push($twdays, $wdays);
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
                 <td colspan="4">Total Scheduled/Roster Days(D)</td>
                 <td><?= totalDutys($totalDuty) ?> Days</td>
                </tr>
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
                      <td style="font-weight:bold;">
                        
                      <?php if($wdays<=$totalDuty){echo $wdays." Days out of".totalDutys($totalDuty);}else{echo $wdays." Days";} ?>
                </td>
                    </tr>
                    <tr>
                      <td colspan="4" style="font-weight:bold;">TOTAL HOURS WORKED</td>
                      <td style="font-weight:bold;"><?= abs($totalHours) ?> Hours</td>
                    </tr>
                  </tfoot>
                </table>
                <?php
                function totalDutys($totalDuty)
                {
                  $dutydays = $totalDuty;
                  return $dutydays;
                }
                ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script type="text/javascript">
      $(document).ready(function() {
        $('#thistbl').slimscroll({
          height: '400px',
          size: '5px'
        });
      });
    </script>
