<style>
/* Custom color overrides for district employees page */
:root {
  --info-color: #212529;
  --secondary-color: #FEFFFF;
}

/* Override button colors to use specified theme colors */
.btn-info {
  background-color: var(--info-color) !important;
  border-color: var(--info-color) !important;
  color: var(--secondary-color) !important;
}

.btn-info:hover, .btn-info:focus {
  background-color: #1a1e21 !important;
  border-color: #1a1e21 !important;
  color: var(--secondary-color) !important;
}

.btn-success {
  background-color: #28a745 !important;
  border-color: #28a745 !important;
  color: var(--secondary-color) !important;
}

.btn-success:hover, .btn-success:focus {
  background-color: #218838 !important;
  border-color: #1e7e34 !important;
  color: var(--secondary-color) !important;
}

.btn-info {
  background-color: #17a2b8 !important;
  border-color: #17a2b8 !important;
  color: var(--secondary-color) !important;
}

.btn-info:hover, .btn-info:focus {
  background-color: #138496 !important;
  border-color: #117a8b !important;
  color: var(--secondary-color) !important;
}

.btn-secondary {
  background-color: #6c757d !important;
  border-color: #6c757d !important;
  color: var(--secondary-color) !important;
}

.btn-secondary:hover, .btn-secondary:focus {
  background-color: #5a6268 !important;
  border-color: #545b62 !important;
  color: var(--secondary-color) !important;
}

/* Card styling improvements */
.card {
  border-radius: 8px;
  border: 1px solid #dee2e6;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  transition: all 0.3s ease;
}

.card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}

.card-header {
  background-color: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
  padding: 1.25rem 1.5rem;
}

/* Table improvements */
.table thead th {
  background-color: #f8f9fa;
  border-bottom: 2px solid #dee2e6;
  color: var(--info-color);
  font-weight: 600;
  padding: 1rem 0.75rem;
}

.table tbody td {
  padding: 0.75rem;
  vertical-align: middle;
  border-bottom: 1px solid #dee2e6;
}

.table-hover tbody tr:hover {
  background-color: rgba(33, 37, 41, 0.05);
}

/* Form improvements */
.form-control, .select2-container--default .select2-selection--multiple {
  border: 1px solid #dee2e6;
  border-radius: 6px;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus, .select2-container--default.select2-container--focus .select2-selection--multiple {
  border-color: var(--info-color);
  box-shadow: 0 0 0 0.2rem rgba(33, 37, 41, 0.25);
}

/* Modal improvements */
.modal-header {
  background-color: var(--info-color);
  color: var(--secondary-color);
  border-bottom: 1px solid #dee2e6;
}

.modal-title {
  color: var(--secondary-color);
  font-weight: 600;
}

/* DataTables Customization */
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter,
.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_processing,
.dataTables_wrapper .dataTables_paginate {
  padding: 1rem 1.5rem;
}

.dataTables_wrapper .dataTables_filter input {
  border: 1px solid #dee2e6;
  border-radius: 4px;
  padding: 0.375rem 0.75rem;
  margin-left: 0.5rem;
}

.dataTables_wrapper .dataTables_filter input:focus {
  border-color: var(--info-color);
  box-shadow: 0 0 0 0.2rem rgba(33, 37, 41, 0.25);
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
  border-radius: 4px;
  margin: 0 2px;
  border: 1px solid #dee2e6;
  color: var(--info-color) !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
  background: #f8f9fa;
  border-color: var(--info-color);
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
  background: var(--info-color);
  border-color: var(--info-color);
  color: var(--secondary-color) !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
  color: #6c757d !important;
  border-color: #dee2e6;
}

/* DataTables Buttons (uniform + professional) */
#staffTable_wrapper .dt-buttons .btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  height: 38px;
  padding: 0 14px;
  line-height: 1;
  font-weight: 600;
  font-size: 14px;
  border-radius: 8px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
  margin: 0;
}

#staffTable_wrapper .dt-buttons .btn i {
  font-size: 14px;
  line-height: 1;
  width: 16px;
  text-align: center;
}

#staffTable_wrapper .dt-buttons .btn.buttons-collection::after {
  margin-left: 8px;
}

/* Responsive improvements */
@media (max-width: 768px) {
  #staffTable_wrapper .dt-buttons .btn {
    height: 36px;
    padding: 0 12px;
    font-size: 13px;
    border-radius: 8px;
  }

  .card-body {
    padding: 1rem;
  }
  
  .btn-lg {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
  }
  
  .dataTables_wrapper .dataTables_length,
  .dataTables_wrapper .dataTables_filter {
    text-align: left;
    margin-bottom: 1rem;
  }
}
</style>

<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-3">
      <div class="col-12">
        <div class="page-header">
          <h4 class="page-title">
            <i class="fas fa-users text-info"></i>
            <?php echo $_SESSION['district']; ?> District Staff
          </h4>
          <p class="page-subtitle">Manage and view all employees in the district</p>
        </div>
      </div>
    </div>

    <!-- Global Search Card -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-search text-info mr-2"></i>Global Search
            </h5>
        </div>
        <div class="card-body">
            <form id="filterForm" class="row g-3">
              <div class="col-12">
                <label class="form-label">
                  <i class="fas fa-search text-info mr-1"></i> Search All Fields
                </label>
              <div class="input-group">
                  <input type="text" class="form-control form-control-lg" id="globalSearch" 
                         placeholder="Search by name, ID, NIN, phone, email, department, job, facility, or any other field...">
                  <div class="input-group-append">
                    <button type="submit" class="btn btn-info btn-lg">
                      <i class="fas fa-search mr-2"></i>Search
                    </button>
                    <button type="button" id="resetFilters" class="btn btn-outline-secondary btn-lg">
                      <i class="fas fa-undo mr-2"></i>Reset
                    </button>
              </div>
            </div>
                <small class="form-text text-muted">
                  <i class="fas fa-info-circle mr-1"></i> Searches across all employee fields including name, ID, NIN, phone, email, department, job, facility, etc.
                </small>
              </div>
            </form>
            </div>
        </div>
      </div>
    </div>

    <!-- Data Table Card -->
      <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="card-title mb-0">
                <i class="fas fa-table text-info mr-2"></i>Staff Directory
              </h5>
              <div class="d-flex align-items-center">
                <span class="badge badge-info mr-2" id="showingInfo">Showing 0 of 0 entries</span>
                <button type="button" class="btn btn-sm btn-outline-info" id="refreshTable">
                  <i class="fas fa-sync-alt"></i>
                </button>
              </div>
            </div>
          </div>
          <div class="card-body p-7">
            <div class="table-responsive">
              <table id="staffTable" class="table table-hover mb-0" style="width:100%">
                <thead>
                  <tr>
                    <th class="text-center" style="width: 50px;">#</th>
                    <th style="width: 120px;">Staff ID</th>
                    <th style="width: 100px;">NIN</th>
                    <th style="min-width: 200px;">Full Name</th>
                    <th style="width: 80px;">Gender</th>
                    <th style="width: 120px;">Birth Date</th>
                    <th style="width: 120px;">Phone</th>
                    <th style="min-width: 150px;">Email</th>
                    <th style="min-width: 150px;">Facility</th>
                    <th style="min-width: 120px;">Department</th>
                    <th style="min-width: 120px;">Job</th>
                    <th style="width: 120px;">Terms</th>
                    <th style="width: 100px;">Card #</th>
                    <th style="width: 120px;">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Data will be loaded via AJAX -->
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Incharge Assignment Modal -->
<div class="modal fade" id="inchargeModal" tabindex="-1" role="dialog" aria-labelledby="inchargeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                  <div class="modal-header">
        <h5 class="modal-title" id="inchargeModalLabel">
          <i class="fas fa-user-plus mr-2"></i>Assign Incharge Role
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
        <div class="row">
          <div class="col-md-4 text-center">
            <div class="avatar-placeholder">
              <i class="fas fa-user fa-3x text-muted"></i>
            </div>
            <h6 class="staff-name mt-2">Staff Name</h6>
            <p class="text-muted staff-job">Job Title</p>
            <p class="text-muted staff-facility">Facility</p>
          </div>
          <div class="col-md-8">
            <form id="inchargeForm">
              <input type="hidden" name="ihris_pid">
              <input type="hidden" name="district_id">
              <input type="hidden" name="facility_id[]">
              
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-control" name="name" readonly>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Username (iHRIS ID)</label>
                    <input type="text" class="form-control" name="username" readonly>
                </div>
              </div>
            </div>
          
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" readonly>
                  </div>
                  </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="text" class="form-control" name="password" readonly>
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-12">
                  <input type="hidden" name="is_incharge" value="1">
                  <button type="submit" class="btn btn-info">
                    <i class="fas fa-user-plus mr-2"></i>Assign Incharge
                  </button>
                </div>
              </div>
            </form>
              </div>
            </div>
</div>
</div>
</div>
</div>

<!-- Toastr Notifications -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
$(document).ready(function() {
    var baseUrl = '<?php echo base_url(); ?>';

    // Declare table variable in wider scope
    var table;
    
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });
    
    // Initialize DataTable with professional configuration
    table = $('#staffTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?php echo base_url("employees/district_employees"); ?>',
            type: 'POST',
            data: function(d) {
                d.globalSearch = $('#globalSearch').val();
                d['<?php echo $this->security->get_csrf_token_name(); ?>'] = '<?php echo $this->security->get_csrf_hash(); ?>';
            }
        },
        columns: [
            { data: 'serial', className: 'text-center' },
            { data: 'ihris_pid', className: 'text-center' },
            { data: 'nin', className: 'text-center' },
            { data: 'fullname', render: function(data, type, row) {
                if (type !== 'display') return data;
                var safeName = $('<div>').text(data || '').html();
                var pid = row && row.ihris_pid ? String(row.ihris_pid) : '';
                if (!pid) return safeName;
                var href = baseUrl + 'employees/employeeTimeLogs/' + encodeURIComponent(pid);
                return '<a href="' + href + '" title="View time logs">' + safeName + '</a>';
            }},
            { data: 'gender', className: 'text-center' },
            { data: 'birth_date', className: 'text-center' },
            { data: 'phone', className: 'text-center' },
            { data: 'email' },
            { data: 'facility' },
            { data: 'department' },
            { data: 'job' },
            { data: 'employment_terms', className: 'text-center' },
            { data: 'card_number', className: 'text-center' },
            { 
                data: null,
                className: 'text-center',
                render: function(data, type, row) {
                    if (row.is_incharge == 1) {
                        return '<span class="badge badge-success">Already Incharge</span>';
                    } else {
                        return '<button type="button" class="btn btn-sm btn-info assign-incharge" data-staff=\'' + JSON.stringify(row) + '\'><i class="fas fa-user-plus mr-1"></i>Assign</button>';
                    }
                }
            }
        ],
        order: [[3, 'asc']], // Sort by name by default
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        responsive: true,
        dom: '<"row"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        buttons: [
            {
                extend: 'copy',
                text: '<i class="fas fa-copy"></i> Copy',
                className: 'btn btn-secondary btn-sm',
                exportOptions: {
                    columns: ':visible'
                    }
            },
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv"></i> CSV',
                className: 'btn btn-success btn-sm',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-info btn-sm',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print',
                className: 'btn btn-warning btn-sm',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'colvis',
                text: '<i class="fas fa-columns"></i> Columns',
                className: 'btn btn-dark btn-sm'
            }
        ],
        language: {
            processing: '<div class="spinner-border text-info" role="status"><span class="visually-hidden">Loading...</span></div>',
            search: "Search:",
            lengthMenu: "Show _MENU_ entries per page",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            infoFiltered: "(filtered from _MAX_ total entries)",
            emptyTable: "No staff data available",
            zeroRecords: "No matching staff found"
        },
        drawCallback: function(settings) {
            updateStatistics();
            updateShowingInfo();
        }
    });
    
    // Handle filter form submission
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        table.ajax.reload();
        showLoading();
    });
    
    // Reset filters
    $('#resetFilters').on('click', function() {
        $('#globalSearch').val('');
        table.ajax.reload();
    });
    
    // Refresh table
    $('#refreshTable').on('click', function() {
        table.ajax.reload();
    });
    
    // Handle incharge assignment
    $(document).on('click', '.assign-incharge', function() {
        var staffData = $(this).data('staff');
        populateInchargeModal(staffData);
        $('#inchargeModal').modal('show');
    });
    
    // Handle incharge form submission
    $('#inchargeForm').on('submit', function(e) {
    e.preventDefault();
        submitInchargeForm();
    });
    
    // Initialize statistics
    updateStatistics();
    
    function updateStatistics() {
        var info = table.page.info();
        $('#totalStaff').text(info.recordsTotal);
        $('#activeStaff').text(info.recordsDisplayed);
    }
    
    function updateShowingInfo() {
        var info = table.page.info();
        $('#showingInfo').text('Showing ' + (info.start + 1) + ' to ' + info.end + ' of ' + info.recordsTotal + ' entries');
    }
    
    function populateInchargeModal(staffData) {
        $('.staff-name').text(staffData.fullname);
        $('.staff-job').text(staffData.job);
        $('.staff-facility').text(staffData.facility);
        
        $('input[name="name"]').val(staffData.fullname);
        $('input[name="username"]').val(staffData.ihris_pid);
        $('input[name="email"]').val(staffData.email);
        $('input[name="ihris_pid"]').val(staffData.ihris_pid);
        $('input[name="district_id"]').val(staffData.district_id);
        $('input[name="facility_id[]"]').val(staffData.facility_id);
        $('input[name="password"]').val('<?php echo Modules::run("svariables/getSettings")->default_password; ?>');
    }
    
    function submitInchargeForm() {
        var formData = $('#inchargeForm').serialize();
        var submitBtn = $('#inchargeForm button[type="submit"]');
        var originalText = submitBtn.html();
        
        // Debug: Log the form data being sent
        console.log('Form data being sent:', formData);
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Processing...');

    $.ajax({
            url: '<?php echo base_url("auth/addUser"); ?>',
      method: 'POST',
            data: formData + '&<?php echo $this->security->get_csrf_token_name(); ?>=<?php echo $this->security->get_csrf_hash(); ?>',
            success: function(response) {
                console.log('Success response:', response);
                showNotification('Incharge role assigned successfully!', 'success');
                $('#inchargeModal').modal('hide');
                table.ajax.reload();
            },
            error: function(xhr, status, error) {
                console.error('Error response:', xhr.responseText);
                showNotification('Error assigning incharge role. Please try again.', 'error');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    }
    
    function showLoading() {
        // Show loading indicator
        $('body').append('<div id="loadingOverlay" class="loading-overlay"><div class="loading-content"><div class="spinner-border text-info" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Processing...</p></div></div>');
        setTimeout(function() {
            $('#loadingOverlay').remove();
        }, 1000);
    }
    
    function showNotification(message, type) {
        // You can implement your preferred notification system here
        if (type === 'success') {
            toastr.success(message);
        } else {
            toastr.error(message);
        }
    }
});
</script>