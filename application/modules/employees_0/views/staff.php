<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">

            <!-- right column -->
            <div class="col-md-8">
                <!-- Form Element sizes -->
                <div class="card card-default">
                    <div class="card-header">
                        <div class="callout callout-success">
                            <h5><i class="fas fa-file"></i> <?php //echo $_SESSION['facility_name']; ?> Staff </h5>

                        </div>
                    </div>
                    <div class="card-body">

                    <a class="btn btn-sm btn  btnkey bg-gray-dark color-pale" 
                      href="<?php echo base_url() ?>/employees/createEmployee">Add New Employee</a>
                    
                    <table id="mytab2" class="table table-bordered table-striped mytable">
                      <thead>
                        <tr>
                          <th>Staff iHRIS ID</th>
                          <th>NIN</th>
                          <th>Name</th>
                          <th>Gender</th>
                          <th>Birth Date</th>
                          <th>Phone</th>
                          <th>Email</th>
                          <th>Department</th>
                          <th>Job</th>
                          <th>Employment Terms</th>
                          <th>Card Number</th>
                        </tr>
                      </thead>
                      <tbody>

                        <?php
                          
                          $staffs = Modules::run("employees/get_employees");
                          $i = 1;
                          foreach ($staffs as $staff) {
                        ?>
                        
                          <tr>
                            <td data-label="Staff iHRIS ID" style="background-image:url('<?php echo base_url() ?>/assets/images/user.png'); width:30px !imporntant; opacity: 0.6; background-size: contain;background-position: center center;background-repeat: no-repeat;  font-size:14px; margin:0 auto; ">
                              <p style="clear:both; text-align:center; color:#000; opacity:1;"><?php echo str_replace('person|', '', $staff->ihris_pid); ?></p>
                            </td>
                            <td data-label="NATIONAL ID NUMBER"><?php echo $staff->nin; ?></td>
                            <td data-label="NAME"><?php echo $staff->surname . " " . $staff->firstname . " " . $staff->othername; ?>
                            </td>
                            <td data-label="GENDER"><?php echo $staff->gender; ?></td>
                            <td data-label="DATE OF BIRTH"><?php echo $staff->birth_date; ?></td>
                            <td data-label="TELEPHONE">
                              <?php if (empty($staff->mobile)) {
                                  echo $staff->mobile;
                                } else {
                                  echo $staff->telephone;
                                } ?>
                            </td>
                            <td data-label="FACILITY"><?php echo $staff->email; ?></td>
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

                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->

                <!-- /.card -->
            </div>
            <!--/.col (right) -->

        </div>
        <!-- /.row -->
    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

