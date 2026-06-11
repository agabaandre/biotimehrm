<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <?php if (!empty($can_import_staff)) { ?>
        <div class="row mb-3">
            <div class="col-12">
                <div class="card card-outline card-info">
                    <div class="card-body d-flex flex-wrap align-items-center justify-content-between">
                        <div class="mb-2 mb-md-0">
                            <strong><i class="fas fa-file-import mr-1"></i>Bulk Import Staff</strong>
                            <div class="text-muted small mt-1">Download the template, add one row per staff member, then upload the CSV. Cadre is set to Education automatically. Imported staff are also created as incharge users (role 21).</div>
                        </div>
                        <div>
                            <button type="button" class="btn btn-outline-info btn-sm mr-2" data-toggle="modal" data-target="#importStaffModal">
                                <i class="fas fa-upload mr-1"></i>Import Staff
                            </button>
                            <a href="<?php echo base_url('employees/downloadEmployeeImportTemplate'); ?>" class="btn btn-info btn-sm">
                                <i class="fas fa-download mr-1"></i>Download Template
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
        <form id="createEmployeeForm" class="district_form" method="post" action="<?php echo base_url(); ?>employees/saveEmployee">
            <input type="hidden" id="createEmployeeCsrf" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
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
                                <input type="text" class="form-control" name="nin">
                            </div>
                            <div class="form-group">
                                <label>National ID Card Number</label>
                                <input type="text" class="form-control" name="card_number">
                            </div>
                            <div class="form-group">
                                <label>IPPS / HCM Number</label>
                                <input type="text" class="form-control" name="ipps" placeholder="Optional">
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
                                <button type="reset" class="btn bg-gray btn-outline" id="resetEmployeeForm">Reset All</button>
                                <button type="submit" class="btn bg-gray-dark color-pale" id="submitEmployeeForm">
                                    <i class="fas fa-save mr-1"></i>Submit
                                </button>
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

<?php if (!empty($can_import_staff)) { ?>
<div class="modal fade" id="importStaffModal" tabindex="-1" role="dialog" aria-labelledby="importStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="importStaffModalLabel">
                    <i class="fas fa-file-import mr-2"></i>Import Staff
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="importStaffForm" method="post" enctype="multipart/form-data" action="<?php echo base_url('employees/importEmployees'); ?>">
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                <div class="modal-body">
                    <p class="text-muted mb-3">
                        Use the downloaded template columns exactly. The sample row is skipped automatically during import.
                        Cadre is assigned as Education in the backend. Each imported staff member is saved to iHRIS and added to the users table with the incharge role.
                    </p>
                    <div class="form-group mb-0">
                        <label for="importStaffFile"><i class="fas fa-file-csv text-info mr-1"></i>CSV File</label>
                        <input type="file" class="form-control-file" id="importStaffFile" name="import_file" accept=".csv,text/csv" required>
                        <small class="form-text text-muted">
                            Columns: <?php echo htmlspecialchars(implode(', ', isset($import_template_headers) ? $import_template_headers : []), ENT_QUOTES, 'UTF-8'); ?>.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-upload mr-1"></i>Upload &amp; Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php } ?>

<link rel="stylesheet" href="<?php echo base_url('assets/plugins/toastr/toastr.min.css'); ?>">
<script src="<?php echo base_url('assets/plugins/toastr/toastr.min.js'); ?>"></script>

<script>
    var csrfTokenName = <?php echo json_encode($this->security->get_csrf_token_name()); ?>;
    var facility_json = <?php echo !empty($facilities_json) ? $facilities_json : '[]'; ?>;
    var job_json = <?php echo !empty($jobs_json) ? $jobs_json : '[]'; ?>;

    function refreshEmployeeCsrfToken(hash) {
        if (!hash) {
            return;
        }
        $('input[name="' + csrfTokenName + '"]').val(hash);
        $('#createEmployeeCsrf').val(hash);
    }

    function resetEmployeeFormFields() {
        var form = document.getElementById('createEmployeeForm');
        if (form) {
            form.reset();
        }
        $('#createEmployeeForm .select2').val(null).trigger('change');
        updateInstitutionFields('');
        document.getElementById('job_id').value = '';
    }

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
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: 5000
        };

        $('#facility').on('change', function() {
            updateInstitutionFields(this.value);
        });

        $('#createEmployeeForm').on('submit', function(e) {
            e.preventDefault();
            var form = this;
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            if (!$('#facility_id').val()) {
                toastr.error('Please select a <?php echo strtolower(entity_label('facility')); ?>.');
                return;
            }

            var $form = $(form);
            var $submit = $('#submitEmployeeForm');
            var originalHtml = $submit.html();
            $submit.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Saving...');

            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: $form.serialize(),
                dataType: 'json',
                success: function(result) {
                    refreshEmployeeCsrfToken(result.csrf_token);
                    if (result.status === 'success') {
                        toastr.success(result.message || 'Employee saved successfully.');
                        resetEmployeeFormFields();
                    } else {
                        toastr.error(result.message || 'Failed to save employee.');
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 403) {
                        toastr.error('Security token expired. Please refresh the page and try again.');
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error('Failed to save employee. Please try again.');
                    }
                },
                complete: function() {
                    $submit.prop('disabled', false).html(originalHtml);
                }
            });
        });

        $('#resetEmployeeForm').on('click', function() {
            window.setTimeout(function() {
                resetEmployeeFormFields();
            }, 0);
        });

        $('#importStaffForm').on('submit', function(e) {
            e.preventDefault();
            var $form = $(this);
            var $submit = $form.find('[type="submit"]');
            var formData = new FormData(this);
            $submit.prop('disabled', true);

            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(result) {
                    refreshEmployeeCsrfToken(result.csrf_token);
                    if (result.status === 'success') {
                        toastr.success(result.message || 'Import completed.');
                        $('#importStaffModal').modal('hide');
                        $form[0].reset();
                    } else {
                        toastr.error(result.message || 'Import failed.');
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 403) {
                        toastr.error('Security token expired. Please refresh the page and try again.');
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error('Import failed. Please check your CSV file and try again.');
                    }
                },
                complete: function() {
                    $submit.prop('disabled', false);
                }
            });
        });
    });
</script>