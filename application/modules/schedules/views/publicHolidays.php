<!-----------------------PUBLIC HOLIDAYS ------------------------------------------------------------------>
<style>
.modal{
  clear:both;
  position: fixed;
  margin-left:100px;
  margin-top:40px;
  margin-bottom: 0px;
  z-index: 10040;
  overflow-x: auto;
  overflow-y: auto;
}

/* Custom styling for the public holidays page */
.page-header {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  padding: 1.5rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.page-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #212529;
  margin-bottom: 0.5rem;
}

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

.table thead th {
  background-color: #f8f9fa;
  border-bottom: 2px solid #dee2e6;
  color: #212529;
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

.badge {
  font-size: 0.75rem;
  padding: 0.375rem 0.75rem;
}

/* Responsive improvements */
@media (max-width: 768px) {
  .page-header {
    padding: 1rem;
  }
  
  .page-title {
    font-size: 1.25rem;
  }
  
  .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
  }
}
</style>

<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <!-- Navigation Tabs -->
    <div class="row mb-3">
      <div class="col-12">
        <ul class="nav nav-tabs" id="custom-tabs-three-tab" role="tablist">
          <li class="nav-item">
            <a class="nav-link active" id="custom-tabs-three-home-tab" href="<?php echo base_url() ?>schedules/Public_Holidays" role="tab" aria-controls="custom-tabs-three-home" aria-selected="true">
              <i class="fas fa-calendar-day text-info mr-1"></i>Public Holidays
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="custom-tabs-three-profile-tab" href="<?php echo base_url() ?>schedules/duty_rosta_schedules" role="tab" aria-controls="custom-tabs-three-profile" aria-selected="false">
              <i class="fas fa-calendar-week text-info mr-1"></i>Duty Roster
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="custom-tabs-three-messages-tab" href="<?php echo base_url() ?>schedules/attendance_schedules" role="tab" aria-controls="custom-tabs-three-messages" aria-selected="false">
              <i class="fas fa-calendar-check text-success mr-1"></i>Attendance Schedules
            </a>
          </li>
        </ul>
      </div>
    </div>

    <!-- Page Header -->
    <div class="row mb-3">
      <div class="col-12">
        <div class="page-header">
          <h1 class="page-title">
            <i class="fas fa-calendar-day text-info mr-2"></i>
            Public Holiday Management
          </h1>
          <p class="page-subtitle">Manage and configure public holidays for the organization</p>
        </div>
      </div>
    </div>

    <!-- Add Holiday Form and Table -->
    <div class="row">
      <!-- Add Holiday Form -->
      <div class="col-lg-4 col-md-12 mb-4">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-plus text-success mr-2"></i>Add New Holiday
            </h5>
          </div>
          <div class="card-body">
            <?php if ($this->session->flashdata('msg')): ?>
              <div class="alert alert-<?php echo strpos($this->session->flashdata('msg'), 'Success') !== false ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?php echo strpos($this->session->flashdata('msg'), 'Success') !== false ? 'check-circle' : 'exclamation-triangle'; ?> mr-2"></i>
                <?php echo $this->session->flashdata('msg'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            <?php endif; ?>

            <form method="post" action="<?php echo base_url(); ?>schedules/addholiday" autocomplete="off">
              <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
              
              <div class="mb-3">
                <label for="holiday_name" class="form-label">
                  <i class="fas fa-tag text-info mr-1"></i>Holiday Name
                </label>
                <input type="text" class="form-control" id="holiday_name" name="holiday_name" placeholder="Enter holiday name" required>
              </div>

              <div class="mb-3">
                <label for="dateFrom" class="form-label">
                  <i class="fas fa-calendar text-info mr-1"></i>Date
                </label>
                <input type="date" class="form-control" id="dateFrom" name="dateFrom" required>
              </div>

              <div class="mb-3">
                <label for="year" class="form-label">
                  <i class="fas fa-calendar-year text-warning mr-1"></i>Year
                </label>
                <input type="number" class="form-control" id="year" name="year" value="<?php echo date('Y'); ?>" min="2020" max="2030" required>
              </div>

              <div class="mb-3">
                <label for="type" class="form-label">
                  <i class="fas fa-info-circle text-success mr-1"></i>Type
                </label>
                <select class="form-control" id="type" name="type" required>
                  <option value="">Select Type</option>
                  <option value="National">National Holiday</option>
                  <option value="Religious">Religious Holiday</option>
                  <option value="Cultural">Cultural Holiday</option>
                  <option value="Other">Other</option>
                </select>
              </div>

              <div class="d-grid gap-2">
                <button class="btn btn-success" type="submit">
                  <i class="fas fa-plus mr-2"></i>Add Holiday
                </button>
                <button class="btn btn-outline-secondary" type="reset">
                  <i class="fas fa-undo mr-2"></i>Reset
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Holidays Table -->
      <div class="col-lg-8 col-md-12">
        <div class="card">
          <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="card-title mb-0">
                <i class="fas fa-table text-info mr-2"></i>Public Holidays
              </h5>
              <div class="d-flex align-items-center">
                <span class="badge badge-info mr-2" id="showingInfo">Showing 0 of 0 entries</span>
                <button type="button" class="btn btn-sm btn-outline-info" id="refreshTable">
                  <i class="fas fa-sync-alt"></i>
                </button>
              </div>
            </div>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table id="holidaysTable" class="table table-hover mb-0" style="width:100%">
                <thead>
                  <tr>
                    <th class="text-center" style="width: 80px;">#</th>
                    <th style="min-width: 120px;">Date</th>
                    <th style="min-width: 200px;">Holiday Name</th>
                    <th style="min-width: 120px;">Type</th>
                    <th style="width: 80px;">Year</th>
                    <th style="width: 150px;">Actions</th>
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

<!-- DataTables Scripts -->
<script src="<?php echo base_url('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-buttons/js/dataTables.buttons.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/plugins/jszip/jszip.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/plugins/pdfmake/pdfmake.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/plugins/pdfmake/vfs_fonts.js'); ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-buttons/js/buttons.html5.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-buttons/js/buttons.print.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-buttons/js/buttons.colVis.min.js'); ?>"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable with server-side processing
    var table = $('#holidaysTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?php echo base_url("schedules/get_publicHoliday"); ?>',
            type: 'POST',
            data: function(d) {
                d['<?php echo $this->security->get_csrf_token_name(); ?>'] = '<?php echo $this->security->get_csrf_hash(); ?>';
            }
        },
        columns: [
            { 
                data: null, 
                className: 'text-center',
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { 
                data: 'holidaydate',
                className: 'text-center',
                render: function(data, type, row) {
                    if (type === 'display') {
                        return moment(data).format('MMM DD, YYYY');
                    }
                    return data;
                }
            },
            { 
                data: 'holiday_name',
                render: function(data, type, row) {
                    return '<span class="font-weight-bold">' + data + '</span>';
                }
            },
            { 
                data: 'type',
                className: 'text-center',
                render: function(data, type, row) {
                    var badgeClass = '';
                    switch(data) {
                        case 'National':
                            badgeClass = 'badge-info';
                            break;
                        case 'Religious':
                            badgeClass = 'badge-success';
                            break;
                        case 'Cultural':
                            badgeClass = 'badge-warning';
                            break;
                        default:
                            badgeClass = 'badge-secondary';
                    }
                    return '<span class="badge ' + badgeClass + '">' + data + '</span>';
                }
            },
            { 
                data: 'year',
                className: 'text-center'
            },
            { 
                data: null,
                className: 'text-center',
                render: function(data, type, row) {
                    var actions = '';
                    
                    // Edit form
                    actions += '<form method="post" action="<?php echo base_url(); ?>schedules/edit_holiday" class="d-inline mr-1">';
                    actions += '<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">';
                    actions += '<input type="hidden" name="id" value="' + row.id + '">';
                    actions += '<button type="submit" class="btn btn-sm btn-outline-info" title="Save Changes">';
                    actions += '<i class="fas fa-save"></i>';
                    actions += '</button>';
                    actions += '</form>';
                    
                    // Delete button
                    actions += '<button type="button" class="btn btn-sm btn-outline-danger ml-1" data-toggle="modal" data-target="#del' + row.rid + '" title="Delete Holiday">';
                    actions += '<i class="fas fa-trash"></i>';
                    actions += '</button>';
                    
                    return actions;
                }
            }
        ],
        order: [[1, 'asc']], // Sort by date by default
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
                className: 'btn btn-secondary btn-sm'
            },
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv"></i> CSV',
                className: 'btn btn-success btn-sm'
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-info btn-sm'
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm'
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print',
                className: 'btn btn-warning btn-sm'
            }
        ],
        language: {
            processing: '<div class="spinner-border text-info" role="status"><span class="visually-hidden">Loading...</span></div>',
            search: "Search:",
            lengthMenu: "Show _MENU_ entries per page",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            infoFiltered: "(filtered from _MAX_ total entries)",
            emptyTable: "No holidays data available",
            zeroRecords: "No matching holidays found"
        },
        drawCallback: function(settings) {
            updateShowingInfo();
        }
    });
    
    // Refresh table
    $('#refreshTable').on('click', function() {
        table.ajax.reload();
    });
    
    function updateShowingInfo() {
        var info = table.page.info();
        $('#showingInfo').text('Showing ' + (info.start + 1) + ' to ' + info.end + ' of ' + info.recordsTotal + ' entries');
    }
    
    // Initialize
    updateShowingInfo();
    
    // Set current year as default for year field
    $('#year').val(new Date().getFullYear());
    
    // Set current date as default for date field
    var today = new Date().toISOString().split('T')[0];
    $('#dateFrom').val(today);
});
</script>