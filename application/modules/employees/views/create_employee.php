<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <form class="district_form" method="post" action="<?php echo base_url(); ?>employees/saveEmployee">
            <div class="row">

                <!-- right column -->
                <div class="col-md-4">
                    <!-- Form Element sizes -->
                    <div class="card card-default" style="min-height:630px;">
                        <div class="card-header">
                            <div class="">
                                <h5><i class="fas fa-user"></i> Employe Bio Information</h5>

                            </div>
                        </div>
                        <div class="card-body">

                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text" class="form-control" name="firstname" required>
                            </div>
                            <div class="form-group">
                                <label>Middle Name</label>
                                <input type="text" class="form-control" name="othername">
                            </div>
                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" class="form-control" name="surname" required>
                            </div>
                            <div class="form-group">
                                <label>Gender</label>
                                <select type="text" class="form-control" name="gender" required>
                                    <option value="">Select...</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Date of Birth</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="far fa-calendar-alt"></i>
                                        </span>
                                    </div>
                                    <input type="text" name="birth_date" class="form-control datepicker" value="" autocomplete="off">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Home District</label>
                                <select type="text" class="form-control select2" name="home_district" required>
                                    <option disabled>Select ...</option>
                                    <?php
                                    $districts = Modules::run('lists/get_all_districts');
                                    foreach ($districts as $district) { ?>
                                        <option value="<?php echo $district->name; ?>"><?php echo $district->name; ?></option>
                                    <?php } ?>

                                </select>
                            </div>

                        </div>
                    </div>
                </div>
                <!--/.col (right) -->

                <!-- right column -->
                <div class="col-md-4">
                    <!-- Form Element sizes -->
                    <div class="card card-default" style="min-height:630px;">
                        <div class="card-header">
                            <div class="">
                                <h5><i class="fas fa-phone"></i> Contact Information</h5>

                            </div>
                        </div>
                        <div class="card-body">

                            <div class="form-group">
                                <label>Mobile</label>
                                <input type="text" class="form-control" name="mobile" required>
                            </div>
                            <div class="form-group">
                                <label>Telephone</label>
                                <input type="text" class="form-control" name="telephone">
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" class="form-control" name="email">
                            </div>
                            <div class="form-group">
                                <label>National Identification Number (NIN)</label>
                                <input type="text" class="form-control" name="nin" required>
                            </div>
                            <div class="form-group">
                                <label>National ID Card Number</label>
                                <input type="number" class="form-control" name="card_number" required>
                            </div>
                            <div class="form-group">
                                <label>Place of Residence</label>
                                <input type="text" class="form-control" name="place_of_residence" required>
                            </div>
                        </div>
                    </div>
                </div>
                <!--/.col (right) -->

                <!-- right column -->
                <div class="col-md-4">
                    <!-- general form elements -->
                    <div class="card card-default">
                        <div class="card-header">
                            <div class="">
                                <h5><i class="fas fa-building"></i> Work details</h5>

                            </div>
                        </div>
                        <div class="card-body">

                            <div class="form-group">
                                <label>Institution</label>
                                <select type="text" class="form-control select2" id="facility" 
                                name="facility" onchange="updateInstitutionFields(document.getElementById('facility').value)" required>

                                    <option>Select ...</option>
                                    <?php foreach ($facilities as $facility) { ?>
                                        <option value="<?php echo $facility->facility; ?>"><?php echo $facility->facility; ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <input type="hidden" class="form-control" id="facility_id" name="facility_id">
                            <input type="hidden" class="form-control" id="institution_cateegory" name="institution_cateegory">
                            <input type="hidden" class="form-control" id="institutiontype_name" name="institutiontype_name">
                            <input type="hidden" class="form-control" id="institution_level" name="institution_level">
                            <input type="hidden" class="form-control" id="district_id" name="district_id">

                            <div class="form-group">
                                <label>Job Title</label>
                                <select type="text" class="form-control select2" name="job" id="job" onchange="updateJobFields(document.getElementById('job').value)" required>
                                    <option value="">Select...</option>
                                    <?php foreach ($jobs as $job) { ?>
                                        <option value="<?php echo $job->job_title; ?>"><?php echo $job->job_title; ?></option>
                                    <?php } ?>

                                </select>
                            </div>

                            <div class="form-group">
                                <label>Job ID</label>
                                <input type="text" class="form-control" id="job_id" name="job_id" readonly>
                            </div>
                            <div class="form-group">
                                <label>Salary Grade</label>
                                <input type="text" class="form-control" name="salary_grade" required>
                            </div>
                            <div class="form-group">
                                <label>Employment Terms</label>
                                <select type="text" class="form-control" name="employment_terms" required>
                                    <option value="">Select...</option>
                                    <option value="Permanent">Permanent</option>
                                    <option value="Contract">Contract</option>
                                    <option value="Probation">Probation</option>
                                    <option value="Full Time">Full Time</option>
                                    <option value="Part Time">Part Time</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Cadre</label>
                                <select type="text" class="form-control select2" name="cadre" required>
                                    <option value="">Select...</option>
                                    <?php foreach ($cadres as $cadre) { ?>
                                        <option value="<?php echo $cadre->cadre; ?>"><?php echo $cadre->cadre; ?></option>
                                    <?php } ?>

                                </select>
                            </div>

                            <div class="card-footer">
                                <button type="reset" class="btn bg-gray btn-outline">Reset All</button>
                                <button type="submit" class="btn bg-gray-dark color-pale">Submit</button>
                            </div>

                        </div>

                    </div>
                </div>
                <!--/.col (right) -->
            </div>
        </form>
        <!-- /.row -->
    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<script>
    var facility_json = JSON.parse('<?php echo $facilities_json; ?>');
    var job_json = JSON.parse('<?php echo $jobs_json; ?>');

    console.log(job_json);

    function updateInstitutionFields(addressId) {

        var as = $(facility_json).filter(function(i, n) {
            return n.facility === addressId
        });
        console.log(as);

        for (var i = 0; i < as.length; i++) {
            document.getElementById('facility_id').value = as[i].facility_id;
            document.getElementById('institution_cateegory').value = as[i].institution_cateegory;
            document.getElementById('institution_level').value = as[i].institution_level;
            document.getElementById('institutiontype_name').value = as[i].institution_type;

            document.getElementById('district_id').value = as[i].name;

        }

    }

   function updateJobFields(job_title) {

    var jb = $(job_json).filter(function(i, n) {
        return n.job_title === job_title
    });
    console.log(jb);

    for (var i = 0; i < jb.length; i++) {
        document.getElementById('job_id').value = jb[i].job_id;
    }

    } 
</script>