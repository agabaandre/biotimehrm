<?php

$reasons = Modules::run('reasons/getAll');

?>
<div class="card">
    <div class="dashtwo-order-area" style="padding-top: 10px; min-height: 35em;">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">Out of Station Reasons <h3>

                        </div>
                        <div class="panel-body">

                            <form class="reason_form" action="<?php echo base_url(); ?>reasons/saveReason" method="post" enctype="multipart/form-data">

                                <table>

                                    <tr>
                                        <td colspan="7"><span class="status"></span></td>

                                        <td colspan="1"><button type="submit" class="btn  btn-info">Save</button></td>
                                        <td colspan="1"><button type="reset" class="btn bg-gray-dark color-pale">Reset All</button></td>
                                    </tr>

                                </table>

                                <table id="myTable" class="table" cellpadding="0" style="border-collapse: collapse;">


                                    <thead>
                                        <tr>
                                            <th>Reason</th>
                                            <th>Schedule</th>
                                        </tr>
                                    </thead>

                                    <tbody class="tb">
                                        <tr>
                                            <td data-label="Name:">
                                                <input type="text" name="reason" class="form-control" required />
                                            </td>

                                            <td data-label="Schedule">
                                                <?php $shedules = Modules::run("schedules/getattSchedules"); ?>
                                                <select name="schedule_id" class="form-control" required>
                                                    <option value="">Select Reason</option>

                                                    <?php foreach ($shedules as $shedule) :
                                                    ?>
                                                        <option value="<?php echo $shedule->schedule_id; ?>"><?php echo $shedule->schedule; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </form>

                            <table class="table">
                                <thead>
                                    <tr>
                                        <th style="width:2%;">#</th>
                                        <th>Reason</th>
                                        <th>Schedule</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    <?php

                                    $schedules = Modules::run("schedules/getSchedules");

                                    $no = 1;

                                    foreach ($reasons as $reason) : ?>

                                        <tr>
                                            <td data-label="#"><?php echo $no; ?>. </td>
                                            <td data-label="first Name:"><?php echo $reason->reason; ?></td>
                                            <td data-label="Username:"><?php echo $reason->schedule; ?></td>

                                            <!-- <td><a data-toggle="modal" data-target="#edit<?php echo $reason->r_id; ?>" href="#">Edit</a> 
                                    </td>    -->
                                        </tr>

                                    <?php

                                        //include('reason_edit_mdl.php');
                                        //include('reason_delete_mdl.php');
                                        $no++;
                                    endforeach; ?>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>