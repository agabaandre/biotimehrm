<!-----------------------PUBLIC HOLIDAYS ------------------------------------------------------------------>
<style type="">
   .modal{
    clear:both;
    position: fixed;
    margin-left:100px;
    margin-top:40px;
    margin-bottom: :0;
    z-index: 10040;
    overflow-x: auto;
    overflow-y: auto;
}
</style>
     <div class="dashtwo-order-area" style="padding-top: 2px;">
     <div class="container-fluid">
        <div class="row">
           <div class="col-md-12">
           <ul class="nav nav-tabs" style="margin-top:2px; border:1px #d9534f;">
              <li class="active"><a href="<?php echo base_url()?>schedules/Public_Holidays">Holidays</a></li>
              <li class=""><a href="<?php echo base_url()?>schedules/duty_rosta_schedules">Duty Roster</a></li>
              <li class=""><a href="<?php echo base_url()?>schedules/attendance_schedules">Attendance</a></li>
       
          </ul>
                    <div class="panel panel-default">
                  <div class="panel-heading">
                      <h3 class="panel-title">Public Holiday Schedules <?php echo date('Y'); ?></h3>

                        <?php  $holidays=Modules::run('schedules/get_publicHoliday'); ?>
                  </div>
          <!----Add holiday form -->
                  <div class="col-md-12">
                  <p style="padding: 5px; text-align:center; font-weight:bold;">
                   <span class="text-center text-danger">
                     <?php echo $this->session->flashdata('msg'); ?></span>
               </p>
          <form method="post" action="<?php echo base_url();?>schedules/addholiday" autocomplete="off">
              
          <div class="col-md-3">
            <div class="form-group">
              <label for="letter">Holiday Name</label>
              <input type="text" class="form-control" name="holiday_name" value="">

          </div>
          </div>
        
          <div class="col-md-2">
            <div class="form-group">
              <label for="letter">Date</label>
              <div class="input-group date" data-provide="datepicker">
              <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
              <input type="text" class="form-control" name="dateFrom" value="" required>
              </div>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label for="letter">Year</label>
              <input type="text" class="form-control"name="year" value="">
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label for="letter">type</label>
              <input type="text" class="form-control"name="year" value="">
            </div>
          </div>
          <div class="col-md-3" style="margin-top:25px;">
          <div class="form-group">
          <label for="letter"></label>
          <button class="btn btn-danger btn-success" type="submit"><i class="fa"></i>Add</button>

          <button class="btn btn-default" type="reset" data-dismiss="modal">Reset</button>
            </div>
          </div>

          </form>
        </div><!--content-->
                  <div class="panel-body">
                 
                    <table class="table table-striped thistbl">
                      <thead>
                        
                        <th>Date</th>
                       
                        <th>Public Holiday Name</th>
                        <th>Type</th>
                        <th>Year</th>
                        <th>Action</th>
                        
                      </thead>

                      <tbody>
                      <?php  foreach($holidays as $holiday) { ?>

                        <tr>
                          <form method="post" action="<?php echo base_url();?>schedules/edit_holiday">
                          <td><input type="text" value="<?php echo $holiday->holidaydate; ?>" name="holidaydate"></td>
                        
                           <td> <input type="hidden" value="<?php echo $holiday->id; ?>" name="id"><input type="text" value="<?php echo $holiday->holiday_name; ?>" name="holiday_name"></td>
                           <td><input type="text" value="<?php echo $holiday->type; ?>" name="type"> </td>
                           <td><input type="text" value="<?php echo $holiday->year; ?>" name="year" readonly></td>
                           <td><button class="btn btn-sm btn-default" type="submit"><i class=""></i>Save Changes</button>
                           </form>
                         
                           <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#del<?php echo $holiday->rid;?>">
                            Delete Holiday
                            </button>
                            <?php include('delete_holiday.php'); ?> 
                       
                      </td>
                        
                          
                        </tr>

                      <?php } ?>
                        </tbody>

                  </table>

                  
  


                </div>
              </div>
           </div>



      </div>
   </div>
   </div>

</div>
