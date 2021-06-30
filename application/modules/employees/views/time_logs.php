

<?php 
//  $timelogs=Modules::run('employees/getTimeLogs');
?>



<section class="col-lg-12">
            <!-- Custom tabs (Charts with tabs)-->
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
               
                <div class="card-tools">
                
                  <form class="form-horizontal" action="<?php echo base_url() ?>employees/viewTimeLogs" method="post">
                
                  <div class="row">
                    <div class="form-group col-md-2">
                    <label>Date From:</label>

                    <div class="input-group">
                        <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="far fa-calendar-alt"></i>
                        </span>
                        </div>
                        <input type="text"  name="dateFrom" class="form-control datepicker" autocomplete="off">
                    </div>
                    <!-- /.input group -->
                    </div>
                    <div class="form-group col-md-2">
                    <label>Date To:</label>

                    <div class="input-group">
                        <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="far fa-calendar-alt"></i>
                        </span>
                        </div>
                        <input type="text"  name="dateTo" class="form-control datepicker "  autocomplete="off">
                    </div>
                    <!-- /.input group -->
                    </div>
                            
                     
     
     
                  <div class="form-group col-md-6">
                       
                       <label for="aw_description">
                         Name </label>
                        
                    <input class="form-control" type="text" name="name" placeholder="Name">
                     
                    </div>
                        
    
                <div class="form-group col-md-2">
            
                <button type="submit" class="btn bt-md bg-gray-dark color-pale" style="margin-top:24px;">Apply</button>
             
              </div>
            
               </form>
                        
              </div><!-- /.card-header -->
              </div>
              </div>
              <div class="card-body">

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
                                <td><?php echo $time_out=$timelog->time_out; ?></td>
                                <td><?php echo $time_in=$timelog->time_in; ?></td>
                                <td><?php 
                                 
                                  
                                    $initial_time = strtotime($time_in)/ 3600;
                                    $final_time = strtotime($time_out)/ 3600;
                
                                  if(($initial_time)==0 || ($final_time)==0){ 
                                    $hours_worked=0; 
                                  } 
                
                                  else { 
                                    $hours_worked = round(($final_time - $initial_time), 1);   
                                  } 
                
                                  if ($hours_worked<0){ 
                                    echo ($hours_worked*-1) .'hr(s)'; 
                                  } 
                                  else { 
                                    echo $hours_worked.'hr(s)'; 
                                  } 

                                
                                
                                ?>
                                
                                
                                 </td>
                              
                               </tr>
                                <?php  } ?>
                            </tbody>
                          </table>

                          </div><!-- /.card-body -->
            </div>
            <!-- /.card -->
    </section>
<script type="text/javascript">

  $(document).ready(function(){

$('#thistbl').slimscroll({
  height: '400px',
  size: '5px'
});

});




</script>