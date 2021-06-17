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
                        <h3 class="panel-title">Time Logs <h3>

                    </div>
                    <div class="panel-body">

                        <div class="row">

                            <form class="form-horizontal" id="limitsform" method="post"
                                action="<?php echo base_url(); ?>employees/employeeTimeLogs/<?php echo $this->uri->segment(3); ?>">
                                <div class="form-group col-md-2" style="margin-left:0.5em; margin-right:0.2em;">
                                    <div class="form-group">

                                        <div class="input-group date" data-provide="datepicker">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            <input type="text" class="form-control"
                                                value="<?php echo date('m').'/'.'01'.'/'.date('Y');?>" name="date_from"
                                                class="form-control" required>

                                        </div>
                                        <label>Date (from)</label>
                                    </div>

                                </div>

                                <div class="form-group col-md-2" style="margin-left:0.5em; margin-right:0.2em;">
                                    <div class="form-group">
                                        <div class="input-group date" data-provide="datepicker">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            <input type="text" class="form-control" value="<?php echo date('m/d/Y');?>"
                                                name="date_to" class="form-control" required>

                                        </div>
                                        <label>Date (to)</label>
                                    </div>


                                </div>

                                <div class="form-group col-md-2" style="margin-left:0.5em; margin-top:0.3em;">
                                    <button type="submit" class="btn btn-success"><i
                                            class="fa fa-search"></i>Search</button>
                                </div>


                            </form>


                            <a class="btn btn-success fa fa-print btn-sm" style="margin-left: 87%"
                                href="<?php echo base_url(); ?>employees/printindividualTimeLogs/<?php echo $this->uri->segment(3); ?>/<?php echo date("Y-m-d", strtotime($from)); ?>/<?php echo date("Y-m-d", strtotime($to)); ?>/1">Print This View</a>


                            <a class="btn btn-success fa fa-print btn-sm" style="margin-left:50px; margin-bottom:80px;"
                                href="<?php echo base_url(); ?>employees/printindividualTimeLogs/<?php echo $this->uri->segment(3); ?>/<?php echo date("Y-m-d", strtotime($from)); ?>/<?php echo date("Y-m-d", strtotime($to)); ?>/2">Print Detailed View</a>


                        </div>

                        <div class="row" style="float: left;">
                            <div class="col-md-4">
                                <h4><?php echo $employee->surname." ".$employee->firstname; ?></h4>
                            </div>
                            <div class="col-md-4">
                                <h4><?php echo $employee->job; ?></h4>
                            </div>
                            <div class="col-md-4">
                                <h4><?php echo $employee->facility; ?></h4>
                            </div>
                        </div>
                           
                        <?php 
                          
                              $totalDuty=0;

                              foreach($workdays as $workday) {
                                $no++;
                             

                                   date('j F,Y', strtotime($workday->duty_date))." ";
                                    date('j F,Y', strtotime($workday->duty_date))." "; 

                                $totalDuty++; } 
                          ?>


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

                              $no=0;
                              $totalHours=0;

                              foreach($timelogs as $timelog) {
                                $no++;
                               ?>
                                <tr>
                                    <td><?php echo $no; ?></td>
                                    <td><?php echo date('j F,Y', strtotime($timelog->date)); ?></td>
                                    <td><?php echo $timelog->time_out; ?></td>
                                    <td><?php echo $timelog->time_in; ?></td>
                                    <td><?php if(($timelog->time_out)==0 || ($timelog->time_in)==0) { $timediff=0; } else 
                                     { $timediff=(($timelog->time_out)-($timelog->time_in));   }

                                    if ($timediff<0)
                                      { 
                                        echo ($timediff*-1); 
                                           } 
                                      else { 
                                        echo $timediff; 
                                        
                                      }
                                      
                                        ?> hr(s)</td>
                                </tr>
                                <?php  
                               $totalHours+= $timediff; 


                              } ?>
                            </tbody>
                            <tfoot>
                           
                            
                         
                      

                                        <!---leaves-->
                           

                              <?php 

                              $no=0;
                              $totalLeaves=0;

                              foreach($leaves as $leave) {
                                $no++;
                               ?>
                              <?php  "Leave"; ?>
                               <?php  date('j F,Y', strtotime( $leave->date)); ?>
                           
                              
                                <?php  

                                $totalLeaves+=1;

                              } ?>
                          
                           
                              <tr>
                                <td colspan="4">Total Leave Days(L)</td><td><?=$totalLeaves?> Days</td>
                              </tr>
                          
                         

                          <!--/leaves-->

                           <!---Req-->
                         

                              <?php 

                              $totalRequests=0;

                              foreach($requests as $request) {
                                $no++;
                               ?>
                              
                                
                                <?php echo date('j F,Y', strtotime($request->date)); ?>
                               <?php echo date('j F,Y', strtotime($request->date)); ?>
                           
                                <?php  

                                $totalRequests=1;

                              } ?>
                          
                           
                              <tr>
                                <td colspan="4">Total Official Request(R)</td><td><?=$totalRequests?> Days</td>
                              </tr>
                           
                         
                          

                              <?php 

                              $toffs=0;

                              foreach($offs as $off) {
                                $no++;
                               ?>
                              
                                 <?php  "OFF DUTY"; ?>
                                 <?php date('j F,Y', strtotime($off->date)); ?>


                                <?php $toffs++; } 
                                ?>

                           
                                <tr>
                                    <td colspan="4">Total days Off Duty(O)</td>
                                    <td><?=$toffs?>Days</td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="font-weight:bold;">TOTAL DAYS WORKED</td>
                                    <td style="font-weight:bold;"><?php echo $wdays=$no;?> Days out of <?php echo totalDutys($totalDuty); ?></td>
                                 
                                </tr>
                              
                                <tr>
                                <td colspan="4" style="font-weight:bold;">TOTAL HOURS WORKED</td>

                                <td style="font-weight:bold;"><?=abs($totalHours)?> Hours</td>
                                </tr>
                             
                         </tfoot>
                     
                 
                        </table>




                    </div>
            

                </div>
            </div>
        </div>
    </div>
</div>
<?php 
function totalDutys($totalDuty){
 $dutydays=$totalDuty;

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