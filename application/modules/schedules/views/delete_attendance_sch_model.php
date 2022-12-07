      <!--delete modal starts-->
      <div class="modal fade" id="del<?php echo $schedule->schedule_id; ?>">
        <div class="modal-dialog modal-sm modal-default" style="margin-top: 1%;">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Confirm Action <span style="cursor: pointer;" class="pull-right" data-dismiss="modal">&times;</span></h4>
            </div>
          <form method="post" action="<?php echo base_url();?>schedules/delete_attschedules">
          <div class="modal-body">

            <span id="dela<?php echo $schedule->schedule_id; ?>"></span>
            <input type="hidden" class="form-control" id="del_schedule" name="schedule_id" value="<?php echo $schedule->schedule_id; ?>">
      <p><i class="fa fa-remove"></i>&nbsp; You're Permanently Disabling a Schedule <b style="color: #000;"><?php echo $schedule->schedule; ?> </b></p>
      </div><!--body-->
      <div class="modal-footer">

        <button class="btn btn-danger  btn-sm delete" id="<?php echo $schedule->schedule_id; ?>" type="submit"><i class="fa fa-cancel"></i> Yes, Change Status</button>

        <button class="btn btn-success btn-sm" data-dismiss="modal">Cancel</button>

        </div>
      </form>
      </div><!--content-->
      </div><!--modal dialogu-->
      </div><!--modal-->