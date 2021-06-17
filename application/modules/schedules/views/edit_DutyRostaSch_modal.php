  <div class="modal fade" id="edit<?php echo $schedule->schedule_id; ?>">
    <div class="modal-dialog modal-default">
      <div class="modal-content">
        <div class="modal-header">

   <h4 class="modal-title">Edit Schedule <span style="cursor: pointer;" class="pull-right" data-dismiss="modal">&times;</span></h4>

        </div>

        <form id="update_schedule" method="post" action="<?php echo base_url();?>schedules/update_rosterschedule">

            <div class="modal-body">
              <div class="form-group">
                    <input type="hidden" class="form-control" id="upschedule" name="schedule_id" value="<?php echo $schedule->schedule_id; ?>">
                    <label for="exampleInputEmail1">Schedule Name</label>
                    <input type="text" class="form-control" id="upschedule" name="schedule" value="<?php echo $schedule->schedule; ?>" placeholder="Enter Schedule">
                  </div>
                  <div class="form-group">
                    <label for="letter">Letter</label>
                    <input type="text" class="form-control" id="upletter" name="letter" value="<?php echo $schedule->letter; ?>" placeholder="e.g A">
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                    <label for="letter">Starts</label>
                    <input type="text" class="form-control timepicker"  value="<?php echo $schedule->starts; ?>" data-provide="timepicker"  data-minute-step="15" name="starts" placeholder="e.g 08:00AM">
                  </div>
                  </div>


                   <div class="col-md-6">
                    <div class="form-group">
                    <label for="letter">Ends</label>
                    <input type="text" class="form-control timepicker" name="ends" value="<?php echo $schedule->ends; ?>" placeholder="e.g 05:00PM">
                  </div>
                  </div>
                  </div><!--body-->
                  <div class="modal-footer">
                      <button class="btn btn-success " type="submit">Save Schedule</button>
                             
                      <button class="btn btn-default " data-dismiss="modal" type="button">Cancel Edit</button>


                    </div>
                    </form>
          </div><!--content-->
      </div><!--modal dialogu-->
</div><!--modal-->