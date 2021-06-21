<script>
$('.datepicker').datepicker({
    format: 'mm/dd/yyyy',
    startDate: '-3d'
});
</script>

<?php 
//  $timelogs=Modules::run('employees/getTimeLogs');
?>



<!-- Contains page content -->
<div class="dashtwo-order-area" style="padding-top: 10px;">
  <div class="container-fluid">
      <div class="row">
          <script>
            $('#timelogs').DataTable( {
                responsive: true
            } );
          </script>  
          <div class="col-lg-12">
              <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Time Logs <h3>
                      
                </div>
                <div class="panel-body">

                   <div>

                      <form class="form-horizontal" method="post" action="<?php echo base_url(); ?>employees/viewTimeLogs" style="margin-left:0.5em;">
                      <div class="form-group col-md-2" style="margin-left:0.8em; margin-right:0.5em;">
                      <div class="form-group">
           
                      <div class="input-group date" data-provide="datepicker">
                                             <span class="input-group-addon"><i class="fa fa-calendar" ></i></span>
                                             <input type="text" class="form-control" value="<?php echo date('m').'/'.'01'.'/'.date('Y');?>" name="date_from" class="form-control" required>
                                          
                                           </div>
                      <label>Date (from)</label>
                      </div>
                              
                      </div>
                          
                           <div class="form-group col-md-2" style="margin-left:0.8em; margin-right:0.5em;"> <div class="form-group">
                      <div class="input-group date" data-provide="datepicker">
                                             <span class="input-group-addon"><i class="fa fa-calendar" ></i></span>
                                             <input type="text" class="form-control" value="<?php echo date('m/d/Y');?>" name="date_to" class="form-control" required>
                                          
                                           </div>
                                           <label>Date (to)</label>
                      </div>
                              
                              
                          </div>
                          
                            <div class="form-group col-md-3" style="margin-left:0.5em; margin-top:0.3em;">
                                <div class="form-group">
                              <div class='input-group userin'>
                               <input type="text" name="name" class="name form-control" placeholder="Search By Name" value="<?php echo $name; ?>">
                                 <span class="input-group-addon">
                                  <span class="fa fa-user"></span>
                               </span>
                              </div>
                              </div>
                          
                          <label>Name</label>
                          </div>
                          
                           <div class="form-group col-md-2" style="margin-left:0.5em; margin-top:0.3em;">
                              <button type="submit" class="btn btn-success"><i class="fa fa-search"></i>Search</button>
                          </div>
                          
                          <div class="form-group col-md-2" style="margin-left:0.5em; margin-top:0.3em;">
                              <!-- <input type="button" class="btn btn-success csvbtn" value="Get CSV" /> -->
                          </div>
                      </form>
                     
                              <!-- <a class="btn btn-success fa fa-print btn-sm" style="margin-left: 87%" href="<?php echo base_url(); ?>employees/print_timelogs">Print</a>
            -->
                          </div>
                          <table class="table table-striped thistbl" id="timelogs">
                            <thead>
                              <tr>
                                  <th>#</th>
                                  <th>NAME</th>
                                  <th>DEPARTMENT</th>
                                  <th>DATE</th>
                                  <th>TIME IN</th>
                                  <th>TIME OUT</th>
                                  <th width="30%">HOURS WORKED</th>
                                
                              </tr>
                            </thead>

                            <tbody>

                              <?php 

                              $no=0;

                              foreach($timelogs as $timelog) {
                                $no++;
                               ?>
                              <tr>
                                <td><?php echo $no; ?></td>
                                <td><?php echo $timelog->surname." ".$timelog->firstname; ?></td>
                                <td><?php echo $timelog->department; ?></td>
                                <td><?php echo date('j F,Y', strtotime($timelog->date)); ?></td>
                                <td><?php echo $timelog->time_out; ?></td>
                                <td><?php echo $timelog->time_in; ?></td>
                                <td><?php if(($timelog->time_out)==0 || ($timelog->time_in)==0) { $timediff=0; } else { $timediff=(($timelog->time_out)-($timelog->time_in));   } if ($timediff<0){ echo ($timediff*-1); } else { echo $timediff; } ?> hr(s)</td>
                              
                               </tr>
                                <?php  } ?>
                            </tbody>
                          </table>

                          <p class="pull-right"><?php  echo $links; ?></p>
                        </div>

              </div>
          </div>
      </div>
    </div>
</div>


<script type="text/javascript">

  $(document).ready(function(){

$('#thistbl').slimscroll({
  height: '400px',
  size: '5px'
});

});




</script>