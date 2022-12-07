<style type="">
  .modal{
    clear:both;
    position: fixed;
    margin-left:100px;
    margin-top:40px;
    margin-bottom:0px;
    z-index: 10040;
    overflow-x: auto;
    overflow-y: auto;
}
</style>

<div class="row">
  <div class="col-md-12">

    <ul class="nav nav-tabs" id="custom-tabs-three-tab" role="tablist">
      <li class="nav-item">
        <a class="nav-link " id="custom-tabs-three-home-tab" href="<?php echo base_url() ?>schedules/Public_Holidays" role="tab" aria-controls="custom-tabs-three-home" aria-selected="true">Public Holidays </a>
      </li>
      <li class="nav-item">
        <a class="nav-link active" id="custom-tabs-three-profile-tab" href="<?php echo base_url() ?>schedules/duty_rosta_schedules" role="tab" aria-controls="custom-tabs-three-profile" aria-selected="false">Duty Roster</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" id="custom-tabs-three-messages-tab" href="<?php echo base_url() ?>schedules/attendance_schedules" role="tab" aria-controls="custom-tabs-three-messages" aria-selected="false">Attendance Schedules</a>
      </li>

    </ul>
  </div>

  <div class="col-md-8">
    <div class="card">
      <div class="panel panel-default">
        <div class="panel-heading">


          <?php $schedules = Modules::run('schedules/getrotaSchedules');
          ?>

        </div>
        <div class="panel-body">

          <table class="table table-striped thistbl">

            <thead>
              <th>Schedule</th>
              <th>Letter</th>
              <th>Starts</th>
              <th>Ends</th>
              <th width="13%"></th>
            </thead>

            <tbody>

              <?php foreach ($schedules as $schedule) { ?>

                <tr id="row<?php echo $schedule->schedule_id; ?>">
                  <td><?php echo $schedule->schedule; ?></td>
                  <td><?php echo $schedule->letter; ?></td>
                  <td><?php echo date('h:s A', strtotime($schedule->starts)); ?></td>
                  <td><?php echo date('h:s A', strtotime($schedule->ends)); ?></td>
                  <td>
                    <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#edit<?php echo $schedule->schedule_id; ?>"><i class="fa fa-edit"></i></button>
                    <!-- <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#del<?php echo $schedule->schedule_id; ?>"><i class="fa fa-trash"></i></button> -->

                  </td>
                </tr>

                <?php //include('deleteDutyRostaSch_modal.php'); 
                ?>
                <?php include('edit_DutyRostaSch_modal.php'); ?>

              <?php } ?>

            </tbody>
          </table>

        </div>
      </div>
    </div>
  </div>


  <div class="col-md-4">
    <div class="card">
      <div class="card-header">
        <h5 class="panel-title">New Roster Schedule <h5>
      </div>
      <div class="card-body">

        <form role="form" id="schedule_form" method="post" action="<?php echo base_url(); ?>schedules/add_rosterschedule">
          <div class="panel-body">

            <div class="form-group">
              <label for="exampleInputEmail1">Schedule Name</label>
              <input type="text" class="form-control" id="schedule" name="schedule" placeholder="Enter Schedule">
            </div>
            <div class="form-group">
              <label for="letter">Letter</label>
              <input type="text" class="form-control" id="letter" name="letter" placeholder="e.g A">
            </div>


            <div class="form-group">
              <label for="letter">Usage</label>
              <select name="purpose" class="form-control">

                <option value="r">Rota</option>
              </select>


            </div>


            <div class="col-md-6">
              <div class="form-group">
                <label for="letter">Starts</label>
                <div class="input-group">

                  <input type="text" class="form-control timepicker" id="starts" name="starts" placeholder="e.g 08:00AM">


                </div>
              </div>
            </div>


            <div class="col-md-6">
              <div class="form-group">
                <label for="letter">Ends</label>
                <input type="text" class="form-control time" id="ends" name="ends" placeholder="e.g 05:00PM">
              </div>
            </div>


          </div>
          <!-- /.box-body -->

          <div class="panel-footer">
            <button class="btn btn-success" type="submit">Save Schedule</button>

            <button class="btn btn-default" id="reset" type="reset">Reset</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>
</div>
</div>