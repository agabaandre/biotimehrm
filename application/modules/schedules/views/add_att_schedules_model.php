
<div class="modal fade" id="addAtt">
    <div class="modal-dialog modal-default" style="margin-top:0%;">
      <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Add Att.Schedule<span style="cursor: pointer;" class="pull-right" data-dismiss="modal">&times;</span></h4>
            </div>
          <form role="form" id="schedule_form" method="post" action="<?php echo base_url();?>schedules/add_rosterschedule">
            <div class="modal-body">
                      <div class="form-group">
                        <small>Schedule Name</small>
                        <input type="text" class="form-control" id="schedule" name="schedule" placeholder="Enter Schedule">
                      </div>
                      <div class="form-group">
                        <small>Letter</small>
                        <input type="text" class="form-control" id="letter" name="letter" placeholder="e.g A">
                      </div>
                      
                      
                      <div class="form-group">
                        <small>Usage</small>
                        <select name="purpose" class="form-control">
                            <option value="r">Rota</option>
                        </select>
                      </div>
                      <div class="col-md-6">
                      <div class="form-group">
                        <small>Starts</small>
                        <div class="input-group">
                          <input type="text" class="form-control timepicker" id="starts" name="starts" placeholder="e.g 08:00AM">
                      </div>
                      </div>
                      </div>

                       <div class="col-md-6">
                        <div class="form-group">
                        <small>Ends</small>
                        <input type="text" class="form-control time" id="ends" name="ends" placeholder="e.g 05:00PM">
                      </div>
                      </div>

            </div>
            <div class="modal-footer">
              <button class="btn btn-success" type="submit">Save Schedule</button>
              <button class="btn btn-success btn-sm" data-dismiss="modal">Cancel</button>
            </div>
          </form>
    </div><!--content-->
  </div><!--modal dialogu-->
</div><!--modal-->