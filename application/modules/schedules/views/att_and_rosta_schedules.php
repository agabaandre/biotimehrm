<style type="">
  .modal{
    clear:both;
    position: fixed;
    margin-left:100px;
    margin-top:40px;
    margin-bottom :0;
    z-index: 10040;
    overflow-x: auto;
    overflow-y: auto;
}
</style>

<div class="admintab-area" style="padding-top: 10px;">
  <div class="container-fluid">
    <div class="row">
      <div class="col-lg-12">
        <div class="admintab-wrap mg-b-40">
          <ul class="nav nav-tabs custom-menu-wrap custon-tab-menu-style1">
            <li class="active"><a data-toggle="tab" href="#TabProject"><span class="adminpro-icon"></span>Attendance Schedule </a>
            </li>
            <li><a data-toggle="tab" href="#TabDetails"><span class="adminpro-icon "></span>Roster Schedules</a>
            </li>
          </ul>
          <div class="tab-content">

            <div id="TabProject" class="tab-pane in active animated flipInX custon-tab-style1">
              <?php $schedules = Modules::run('schedules/getattSchedules'); ?>

              <div class="dashtwo-order-area" style="padding-top: 2px;">
                <div class="container-fluid">
                  <div class="row">

                    <div class="col-md-8">
                      <div class="panel panel-default">
                        <div class="panel-heading">
                          <h3 class="panel-title">Attendance Schedule <h3>

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
                                    <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#del<?php echo $schedule->schedule_id; ?>"><i class="fa fa-trash"></i></button>

                                  </td>
                                </tr>



                                <!--delete modal starts-->
                                <div class="modal fade" id="del<?php echo $schedule->schedule_id; ?>">
                                  <div class="modal-dialog modal-sm modal-default" style="margin-top: 6%;">
                                    <div class="modal-content">
                                      <div class="modal-header">
                                        <h4 class="modal-title">Confirm Action <span style="cursor: pointer;" class="pull-right" data-dismiss="modal">&times;</span></h4>
                                      </div>
                                      <form method="post" action="<?php echo base_url(); ?>schedules/delete_attschedules">
                                        <div class="modal-body">

                                          <span id="dela<?php echo $schedule->schedule_id; ?>"></span>
                                          <input type="hidden" class="form-control" id="del_schedule" name="schedule_id" value="<?php echo $schedule->schedule_id; ?>">
                                          <p><i class="fa fa-remove"></i>&nbsp; You're Permanently Disabling a Schedule <b style="color: #000;"><?php echo $schedule->schedule; ?> </b></p>
                                        </div>
                                        <!--body-->
                                        <div class="modal-footer">

                                          <button class="btn btn-danger  btn-sm delete" id="<?php echo $schedule->schedule_id; ?>" type="submit"><i class="fa fa-cancel"></i> Yes, Change Status</button>

                                          <button class="btn btn-success btn-sm" data-dismiss="modal">Cancel</button>

                                        </div>
                                      </form>
                                    </div>
                                    <!--content-->
                                  </div>
                                  <!--modal dialogu-->
                                </div>
                                <!--modal-->

                                <!--details/edit modal starts-->

                                <div class="modal fade" id="edit<?php echo $schedule->schedule_id; ?>">
                                  <div class="modal-dialog modal-default">
                                    <div class="modal-content">
                                      <div class="modal-header">

                                        <h4 class="modal-title">Edit Schedule <span style="cursor: pointer;" class="pull-right" data-dismiss="modal">&times;</span></h4>

                                      </div>
                                      <form id="update_schedule" method="post" action="<?php echo base_url() ?>schedules/update_attschedule">
                                        <input type="hidden" class="form-control" id="upschedule" name="schedule_id" value="<?php echo $schedule->schedule_id; ?>">
                                        <div class="modal-body">
                                          <div class="form-group">
                                            <label for="exampleInputEmail1">Schedule Name</label>
                                            <input type="text" class="form-control" id="upschedule" name="schedule" value="<?php echo $schedule->schedule; ?>" placeholder="Enter Schedule">
                                          </div>
                                          <div class="form-group">
                                            <label for="letter">Letter</label>
                                            <input type="text" class="form-control" id="upletter" name="letter" value="<?php echo $schedule->letter; ?>" placeholder="e.g A">
                                          </div>

                                          <div class="col-lg-6 form-group">
                                            <label for="letter">Starts</label>
                                            <input type="text" class="form-control time" value="<?php echo $schedule->starts; ?>" data-provide="timepicker" data-minute-step="15" name="starts" placeholder="e.g 08:00AM">
                                          </div>

                                          <div class="col-lg-6 form-group">
                                            <label for="letter">Ends</label>
                                            <input type="text" class="form-control time" name="ends" value="<?php echo $schedule->ends; ?>" placeholder="e.g 05:00PM">
                                          </div>
                                        </div>
                                        <!--body-->
                                        <div class="modal-footer">
                                          <div class="">
                                            <button class="btn btn-success " type="submit">Save Schedule</button>

                                            <button class="btn btn-default pull-right" data-dismiss="modal" type="button">Cancel Edit</button>
                                          </div>
                                        </div>
                                      </form>
                                    </div>
                                    <!--content-->
                                  </div>
                                  <!--modal dialogu-->
                                </div>
                                <!--modal-->

                              <?php } ?>


                            </tbody>

                          </table>


                        </div>
                      </div>
                    </div>


                    <div class="col-md-4">
                      <div class="panel panel-default">
                        <div class="panel-heading">
                          <h3 class="panel-title">Add Att.Schedule<h3>

                        </div>
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



            <div id="TabDetails" class="tab-pane animated flipInX custon-tab-style1">

              <div class="dashtwo-order-area" style="padding-top: 2px;">
                <div class="container-fluid">
                  <div class="row">


                    <div class="col-md-8">
                      <div class="panel panel-default">
                        <div class="panel-heading">
                          <h3 class="panel-title">Duty Roster Schedules <h3>
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
                                    <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#del<?php echo $schedule->schedule_id; ?>"><i class="fa fa-trash"></i></button>

                                  </td>
                                </tr>

                                <!--delete modal starts-->
                                <div class="modal fade" id="del<?php echo $schedule->schedule_id; ?>">
                                  <div class="modal-dialog modal-sm modal-default" style="margin-top: 6%;">
                                    <div class="modal-content">
                                      <div class="modal-header">
                                        <h4 class="modal-title">Confirm Action <span style="cursor: pointer;" class="pull-right" data-dismiss="modal">&times;</span></h4>
                                      </div>
                                      <form method="post" action="<?php echo base_url(); ?>schedules/delete_rosterschedule">
                                        <div class="modal-body">

                                          <span id="dela<?php echo $schedule->schedule_id; ?>"></span>
                                          <input type="hidden" class="form-control" id="del_schedule" name="schedule_id" value="<?php echo $schedule->schedule_id; ?>">
                                          <p><i class="fa fa-remove"></i>&nbsp; You're Permanently Disabling a Schedule <b style="color: #000;"><?php echo $schedule->schedule; ?> </b></p>
                                        </div>
                                        <!--body-->
                                        <div class="modal-footer">

                                          <button class="btn btn-danger  btn-sm delete" id="<?php echo $schedule->schedule_id; ?>" type="submit"><i class="fa fa-cancel"></i> Yes, Change Status</button>

                                          <button class="btn btn-success btn-sm" data-dismiss="modal">Cancel</button>

                                        </div>
                                      </form>
                                    </div>
                                    <!--content-->
                                  </div>
                                  <!--modal dialogu-->
                                </div>
                                <!--modal-->

                                <!--details/edit modal starts-->

                                <div class="modal fade" id="edit<?php echo $schedule->schedule_id; ?>">
                                  <div class="modal-dialog modal-default">
                                    <div class="modal-content">
                                      <div class="modal-header">

                                        <h4 class="modal-title">Edit Schedule <span style="cursor: pointer;" class="pull-right" data-dismiss="modal">&times;</span></h4>

                                      </div>

                                      <form id="update_schedule" method="post" action="<?php echo base_url(); ?>schedules/update_rosterschedule">

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
                                              <input type="text" class="form-control timepicker" value="<?php echo $schedule->starts; ?>" data-provide="timepicker" data-minute-step="15" name="starts" placeholder="e.g 08:00AM">
                                            </div>
                                          </div>


                                          <div class="col-md-6">
                                            <div class="form-group">
                                              <label for="letter">Ends</label>
                                              <input type="text" class="form-control timepicker" name="ends" value="<?php echo $schedule->ends; ?>" placeholder="e.g 05:00PM">
                                            </div>
                                          </div>




                                        </div>
                                        <!--body-->
                                        <div class="modal-footer">
                                          <button class="btn btn-success " type="submit">Save Schedule</button>

                                          <button class="btn btn-default " data-dismiss="modal" type="button">Cancel Edit</button>


                                        </div>
                                      </form>
                                    </div>
                                    <!--content-->
                                  </div>
                                  <!--modal dialogu-->
                                </div>
                                <!--modal-->


                              <?php } ?>


                            </tbody>


                          </table>

                        </div>
                      </div>
                    </div>


                    <div class="col-md-4">
                      <div class="panel panel-default">
                        <div class="panel-heading">
                          <h3 class="panel-title">Add Roster Schedules <h3>

                        </div>

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

                        </form>


                      </div>
                    </div>

                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- tabs End-->