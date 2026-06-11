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
                                <select class="form-control select2" name="home_district" required>
                                    <option value="">Select ...</option>
                                    <?php
                                    $home_districts = (isset($districts) && is_array($districts)) ? $districts : [];
                                    foreach ($home_districts as $district) {
                                        $district_name = isset($district->name) ? trim((string) $district->name) : '';
                                        if ($district_name === '') {
                                            continue;
                                        }
                                    ?>
                                        <option value="<?php echo htmlspecialchars($district_name, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($district_name, ENT_QUOTES, 'UTF-8'); ?></option>
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
                                <label><?php echo entity_label('facility'); ?></label>
                                <select class="form-control select2" id="facility" name="facility" onchange="updateInstitutionFields(this.value)" required>
                                    <option value="">Select ...</option>
                                    <?php if (!empty($facilities)) { foreach ($facilities as $facility) { ?>
                                        <option value="<?php echo htmlspecialchars($facility->facility, ENT_QUOTES, 'UTF-8'); ?>"
                                                data-facility-id="<?php echo htmlspecialchars($facility->facility_id, ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($facility->facility, ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php } } ?>
                                </select>
                            </div>

                            <input type="hidden" class="form-control" id="facility_id" name="facility_id">
                            <input type="hidden" class="form-control" id="institution_category" name="institution_category">
                            <input type="hidden" class="form-control" id="institutiontype_name" name="institutiontype_name">
                            <input type="hidden" class="form-control" id="institution_level" name="institution_level">
                            <input type="hidden" class="form-control" id="district_id" name="district_id">
                            <input type="hidden" class="form-control" id="district" name="district">

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
    var facility_json = <?php echo !empty($facilities_json) ? $facilities_json : '[]'; ?>;
    var job_json = <?php echo !empty($jobs_json) ? $jobs_json : '[]'; ?>;

    function updateInstitutionFields(facilityName) {
        var match = (facility_json || []).find(function(row) {
            return row && row.facility === facilityName;
        });

        if (!match) {
            document.getElementById('facility_id').value = '';
            document.getElementById('institution_category').value = '';
            document.getElementById('institution_level').value = '';
            document.getElementById('institutiontype_name').value = '';
            document.getElementById('district_id').value = '';
            document.getElementById('district').value = '';
            return;
        }

        document.getElementById('facility_id').value = match.facility_id || '';
        document.getElementById('institution_category').value = match.institution_category || '';
        document.getElementById('institution_level').value = match.institution_level || '';
        document.getElementById('institutiontype_name').value = match.institution_type || '';
        document.getElementById('district_id').value = match.district_id || '';
        document.getElementById('district').value = match.district_name || '';
    }

   function updateJobFields(job_title) {

    var jb = (job_json || []).filter(function(n) {
        return n.job_title === job_title;
    });

    for (var i = 0; i < jb.length; i++) {
        document.getElementById('job_id').value = jb[i].job_id;
    }

    }

    $(document).ready(function() {
        $('#facility').on('change', function() {
            updateInstitutionFields(this.value);
        });
    });
</script>