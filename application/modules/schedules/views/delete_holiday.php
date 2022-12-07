  <!--delete modal starts-->
  
  <div class="modal fade" id="del<?php echo $holiday->rid;  ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-sm modal-default" style="">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Confirm Action <span style="cursor: pointer;" class="pull-right" data-dismiss="modal">&times;</span></h4>
        </div>
      <div class="modal-body">

              
  <p><i class="fa fa-remove"></i>&nbsp; You're Permanently deleting <b style="color: #000;"><?php echo $holiday->holiday_name; ?></b></p>
  </div><!--body-->
  <div class="modal-footer">

  <a class="btn btn-danger  btn-sm btn-success" href="<?php echo base_url();?>schedules/delete_publicHoliday/<?php echo $holiday->rid; ?>"><i></i>Delete Holiday</a>

    <button class="btn btn-success btn-sm" data-dismiss="modal">Cancel</button>

    </div>
 
  </div><!--content-->
  </div><!--modal dialogu-->
  </div><!--modal-->