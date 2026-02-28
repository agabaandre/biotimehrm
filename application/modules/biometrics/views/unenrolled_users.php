<!-- Main content -->
<div class="card">
  <section class="content">
    <div class="container-fluid">
      <!-- Main row -->

      <div class="row" style="min-height:550px">

        <section class="col-lg-12 ">
          <h5 style="margin-top:10px;"><?php echo $uptitle ?></h5>

          <?php
          $staffs = Modules::run('biometrics/get_new_users');
          if (!is_array($staffs) && !($staffs instanceof Traversable)) {
              $staffs = [];
          }
          ?>
          <table id="mytab2" class="table table-bordered table-striped mytable">
            <thead>
              <tr>
                <th>#</th>
                <th> Staff iHRIS ID</th>
                <th>Name</th>

                <th>Job</th>

                <th>Card Number</th>


              </tr>
            </thead>
            <tbody>
              <?php $i = 1;
              foreach ($staffs as $staff) {

              ?>

                <tr>
                  <td data-label="No"><?php echo $i++; ?> </td>
                  <td data-label="Staff iHRIS ID"><?php echo str_replace('person|', '', $staff->ihris_pid ?? ''); ?></td>
                  <td data-label="NAME"><?php echo trim(($staff->fullname ?? '') . ' ' . ($staff->othername ?? '')); ?>
                  </td>



                  <td data-label="JOB"><?php echo $staff->job ?? '';
                                        $job_id = $staff->job_id ?? null; ?></td>

                  <td data-label="CARD NUMBER"><?php echo $card_number = $staff->card_number ?? '';  ?></td>
                  <?php
                  $dep = $staff->department_id ?? null;
                  $facility_id = $staff->facility_id ?? null;
                  $surname = $staff->surname ?? '';
                  $firstname = $staff->firstname ?? '';
                  ?>


                </tr>
              <?php   } ?>

            </tbody>
            <tfoot>

            </tfoot>
          </table>



        </section>
      </div>
      <!-- /.row (main row) -->
    </div><!-- /.container-fluid -->
  </section>
</div>
<!-- /.content -->