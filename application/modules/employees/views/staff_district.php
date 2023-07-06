<!-- Main content -->
<section class="content">
  <div class="container-fluid">

    <!-- Main row -->
    <div class="row">
      <div class="card card-default col-md-12">
        <div class="card-header">
        </div>
        <div class="card-body">

          <form class="form-inline" method="post" action="<?php echo base_url(); ?>employees/district_employees">


            <div class=" col-md-4">
              <div class="input-group">
                <label>Facility</label>
                <select class="form-control select2" name="facility[]" style="width:100%;" multiple>
                  <?php $facs = Modules::run("facilities/get_Facilities");
                  foreach ($facs as $fac) : ?>
                    <option value="<?php echo $fac->facility_id; ?>"><?php echo $fac->facility; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="input-group">
                <label>Job</label>
                <select class="form-control select2" name="job[]" style="width:100%;" multiple>
                  <?php $jobs = Modules::run("jobs/getJobs");
                  foreach ($jobs as $job) : ?>
                    <option value="<?php echo $job->job_id; ?>"><?php echo $job->job; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-2">
              <label style="margin-top:10px;"></label>
              <button class="btn bg-gray btn-dark" type="submit">Search</button>
            </div>
            <div class="col-md-2">
              <label style="margin-top:10px;"></label>
              <a href="<?php echo base_url() ?>employees/district_employees/1" class="btn bt-sm bg-gray-dark color-pale" style="width:100px;"><i class="fa fa-file-excel" aria-hidden="true"></i>CSV</a>
            </div>
          </form>

        </div>
      </div>
      <div class="row">
        <div class="card">
          <div class="">
          </div>
          <div class="card-body">
            <section class="col-lg-12 ">

              <h5> <?php echo $_SESSION['district']; ?> District Staff </h5>

              <div class="row pull-right" style="padding: 0.5rem;"> <?php echo $links; ?> </div>

              <table id="mytab2" class="table table-bordered table-striped" style="width:100;">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Staff iHRIS ID</th>
                    <th>NIN</th>
                    <th>Name</th>
                    <th>Gender</th>
                    <th>Birth Date</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Facility</th>
                    <th>Department</th>
                    <th>Job</th>
                    <th>Employment Terms</th>
                    <th>Card Number</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($this->uri->segment(3))) {
                    $i = 1;
                  } else {
                    $i = $this->uri->segment(3);
                  };
                  foreach ($staffs as $staff) {
                  ?>
                    <tr>
                      <td><?php echo $i++; ?></td>
                      <td data-label="Staff iHRIS ID" style="background-image:url('<?php echo base_url() ?>/assets/images/user.png'); width:30px !imporntant; opacity: 0.6; background-size: contain;background-position: center center;background-repeat: no-repeat;  font-size:14px; margin:0 auto;" class="text text-center">
                        <p style="clear:both; text-align:center; color:#000; opacity:1;" class="badge budge-color-defined"><?php echo str_replace('person|', '', $staff->ihris_pid); ?></p>
                      </td>
                      <td data-label=" NATIONAL ID NUMBER"><?php echo $staff->nin; ?></td>
                      <td data-label="NAME"><?php echo $staff->surname . " " . $staff->firstname . " " . $staff->othername; ?>
                      </td>
                      <td data-label="GENDER"><?php echo $staff->gender; ?></td>
                      <td data-label="DATE OF BIRTH"><?php echo $staff->birth_date; ?></td>
                      <td data-label="TELEPHONE"><?php if (empty($staff->mobile)) {
                                                    echo $staff->mobile;
                                                  } else {
                                                    echo $staff->telephone;
                                                  } ?></td>

                      <td data-label="EMAIL"><?php echo @$staff->email; ?></td>
                      <td data-label="FACILITY"><?php echo $staff->facility; ?></td>
                      <td data-label="DEPARTMENT"><?php echo $staff->department; ?></td>
                      <td data-label="JOB"><?php echo $staff->job; ?></td>
                      <td data-label="TERMS"><?php echo str_replace("CContract", "Central Contract", str_replace("LContract", "Local Contract", str_replace("employment_terms|", "", $staff->employment_terms))); ?></td>
                      <td data-label="CARD NUMBER"><?php echo $staff->card_number; ?></td>
                    </tr>
                  <?php   } ?>
                </tbody>
                <tfoot>

                </tfoot>
              </table>
              <div class="row pull-right" style="padding: 0.5rem;"> <?php echo $links; ?> </div>
            </section>
          </div>
        </div>
      </div>
    </div>
    <!-- /.row (main row) -->
  </div><!-- /.container-fluid -->
</section>
<!-- /.content -->