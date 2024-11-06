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
                  foreach ($facs as $fac): ?>
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
                  foreach ($jobs as $job): ?>
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
              <a href="<?php echo base_url() ?>employees/district_employees/1" class="btn bt-sm bg-gray-dark color-pale"
                style="width:100px;"><i class="fa fa-file-excel" aria-hidden="true"></i>CSV</a>
            </div>
          </form>

        </div>
      </div>
      <div class="row">
        <div class="card">
          <div class="">
          </div>
          <div class="card-body">
            <section class="col-lg-12 " style="overflow:auto;">

              <h5> <?php echo $_SESSION['district']; ?> District Staff </h5>

              <div class="row pull-right" style="padding: 0.5rem;"> <?php echo $links; ?> </div>

              <table id="mytab2" class="table table-bordered table-repsonsive table-striped" style="width:100;">
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
                    <th>#</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($this->uri->segment(3))) {
                    $i = 1;
                  } else {
                    $i = $this->uri->segment(3);
                  }
                  ;
                  foreach ($staffs as $staff) {
                    ?>
                    <tr>
                      <td><?php echo $i++; ?></td>
                      <td data-label="Staff iHRIS ID"
                        style="background-image:url('<?php echo base_url() ?>/assets/images/user.png'); width:30px !imporntant; opacity: 0.6; background-size: contain;background-position: center center;background-repeat: no-repeat;  font-size:14px; margin:0 auto; ">
                        <p style="clear:both; text-align:center; color:#000; opacity:1;">
                          <?php echo str_replace('person|', '', $staff->ihris_pid); ?></p>
                      </td>
                      <td data-label=" NATIONAL ID NUMBER"><?php echo $staff->nin; ?></td>
                      <td data-label="NAME">
                        <?php echo $fullname = $staff->surname . " " . $staff->firstname . " " . $staff->othername; ?>
                      </td>
                      <td data-label="GENDER"><?php echo $staff->gender; ?></td>
                      <td data-label="DATE OF BIRTH"><?php echo $staff->birth_date; ?></td>
                      <td data-label="TELEPHONE"><?php if (empty($staff->mobile)) {
                        echo $staff->mobile;
                      } else {
                        echo $staff->telephone;
                      } ?></td>

                      <td data-label="EMAIL"><?php echo $staff->email; ?></td>
                      <td data-label="FACILITY"><?php echo $staff->facility; ?></td>
                      <td data-label="DEPARTMENT"><?php echo $staff->department; ?></td>
                      <td data-label="JOB"><?php echo $staff->job; ?></td>
                      <td data-label="TERMS">
                        <?php echo str_replace("CContract", "Central Contract", str_replace("LContract", "Local Contract", str_replace("employment_terms|", "", $staff->employment_terms))); ?>
                      </td>
                      <td data-label="CARD NUMBER"><?php echo $staff->card_number; ?></td>
           <td data-label="Login_request">
  <!-- Button to Open the Modal -->
  <?php if ($staff->is_incharge == 1) { ?>
              <!-- Button for users who are already in charge -->
              <button type="button" class="btn btn-info" data-toggle="modal"
                data-target="#inchargeModal<?php echo str_replace('person|', '', $staff->ihris_pid); ?>">
                Assign Incharge
              </button>
            <?php } else { ?>
              <!-- Button for users who are not in charge -->
              <button type="button" class="btn btn-info" data-toggle="modal"
                data-target="#confirmAssignModal<?php echo str_replace('person|', '', $staff->ihris_pid); ?>">
                Assign Incharge
              </button>
            <?php } ?>
          
            <!-- Modal for users who are already in charge -->
            <div class="modal fade" id="inchargeModal<?php echo str_replace('person|', '', $staff->ihris_pid); ?>" tabindex="-1"
              role="dialog" aria-labelledby="inchargeModalLabel<?php echo str_replace('person|', '', $staff->ihris_pid); ?>"
              aria-hidden="true">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="inchargeModalLabel<?php echo str_replace('person|', '', $staff->ihris_pid); ?>">
                      Incharge Status</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    <b><?php echo $fullname; ?></b><br>
                    User is already an Incharge.
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                  </div>
                </div>
              </div>
            </div>
          
            <!-- Confirmation Modal for assigning incharge -->
            <div class="modal fade" id="confirmAssignModal<?php echo str_replace('person|', '', $staff->ihris_pid); ?>"
              tabindex="-1" role="dialog"
              aria-labelledby="confirmAssignModalLabel<?php echo str_replace('person|', '', $staff->ihris_pid); ?>"
              aria-hidden="true">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title"
                      id="confirmAssignModalLabel<?php echo str_replace('person|', '', $staff->ihris_pid); ?>">Confirm Assign
                      Incharge</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    <b><?php echo $fullname; ?></b><br>
                    Are you sure you want to assign this user as an incharge?
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <!-- Form submission button within the modal -->
                    <form class="user_form" method="post" action="<?php echo base_url(); ?>auth/addUser"
                      enctype="multipart/form-data">
                      <input type="hidden" name="name" value="<?= $fullname; ?>">
                      <input type="hidden" name="role" value="21">
                      <input type="hidden" name="username" value="<?php echo str_replace('person|', '', $staff->ihris_pid); ?>">
                      <input type="hidden" name="ihris_pid" value="<?= $staff->ihris_pid; ?>">
                      <input type="hidden" name="password"
                        value="<?php echo Modules::run("svariables/getSettings")->default_password; ?>">
                      <input type="hidden" name="email" value="<?php echo $staff->email; ?>">
                      <input type="hidden" name="district_id" value="<?php echo $staff->district_id; ?>">
                      <input type="hidden" name="facility_id[]" value="<?php echo $staff->facility_id; ?>">
                      <input type="hidden" name="department_id" value="">
                      <input type="hidden" name="is_incharge" value="1">
                      <button type="submit" class="btn btn-info">Confirm Assign</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </td>


          </tr>
        <?php } ?>
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
 <script>
 document.addEventListener("DOMContentLoaded", function() {
  
  // Select all forms with the class "user_form" and attach submit event listeners
  document.querySelectorAll(".user_form").forEach(form => {
    form.addEventListener("submit", function(e) {
      e.preventDefault();

       // Serialize form data
      const formData = new FormData(form);
      const url = "<?php echo base_url()?>auth/addUser";

      // Send the form data using Fetch API
      fetch(url, {
        method: 'POST',
        body: new URLSearchParams(formData)
      })
      .then(response => response.text())
      .then(result => {
        console.log(result);
        setTimeout(() => {
          // Display the result in .status
          document.querySelector('.status').innerHTML = result;
          
          // If notify function is available, show notification; otherwise, use alert as fallback
          if (typeof notify === 'function') {
            notify(result, 'info');
          } else {
            alert(result); // Fallback if notify function is unavailable
          }
          
          // Clear the status and click all elements with the class "clear" to reset forms
          document.querySelector('.status').innerHTML = '';
          document.querySelectorAll('.clear').forEach(button => button.click());
        }, 1000);
      })
      .catch(error => console.error('Error:', error));
    });
  });

});


 </script>