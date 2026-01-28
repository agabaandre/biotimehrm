<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-3">
      <div class="col-12">
        <div class="page-header">
          <h4 class="page-title">
            <i class="fas fa-users text-info"></i>
            <?php echo $_SESSION['facility_name']; ?> Staff
          </h4>
          <p class="page-subtitle">Manage and view all employees in the facility</p>
        </div>
      </div>
    </div>

    <!-- Filters Card -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-filter text-info mr-2"></i>Search & Filter Options
            </h5>
          </div>
          <div class="card-body">
                               <form id="filterForm" class="row g-3">
                     <!-- Global Search -->
                     <div class="col-12">
                       <label class="form-label">
                         <i class="fas fa-search text-info mr-1"></i> Global Search
                       </label>
                       <div class="input-group">
                         <input type="text" class="form-control form-control-lg" id="globalSearch" 
                                placeholder="Search by name, ID, NIN, phone, email, department, job, or any other field...">
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
                         <i class="fas fa-info-circle mr-1"></i> Searches across all employee fields including name, ID, NIN, phone, email, department, job, etc.
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
                    <th style="width: 100px;">IPPS</th>
                    <th style="width: 120px;">Card #</th>
                    <th style="width: 120px;">Phone</th>
                    <th style="min-width: 150px;">Email</th>
                    <th style="min-width: 150px;">Department</th>
                    <th style="min-width: 120px;">Job</th>
                    <th style="width: 120px;">Terms</th>
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
<style>
  /* Employees page: make DataTables action buttons uniform + professional */
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
    box-shadow: 0 1px 2px rgba(0,0,0,0.08);
  }

  #staffTable_wrapper .dt-buttons .btn i {
    font-size: 14px;
    line-height: 1;
    width: 16px;
    text-align: center;
  }

  #staffTable_wrapper .dt-buttons .btn + .btn { margin-left: 0; }

  /* Ensure the "Columns" collection button matches size too */
  #staffTable_wrapper .dt-buttons .btn.buttons-collection::after {
    margin-left: 8px;
  }

  /* Keep the toolbar aligned nicely on small screens */
  @media (max-width: 768px) {
    #staffTable_wrapper .dt-buttons .btn {
      height: 36px;
      padding: 0 12px;
      font-size: 13px;
      border-radius: 8px;
    }
  }
</style>
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
    var baseUrl = '<?php echo base_url(); ?>';

    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });
    
    // Initialize DataTable with professional configuration
    var table = $('#staffTable').DataTable({
        processing: true,
        serverSide: true,
                       ajax: {
                   url: '<?php echo base_url("employees"); ?>',
                   type: 'POST',
                   data: function(d) {
                       d.globalSearch = $('#globalSearch').val();
                       d['<?php echo $this->security->get_csrf_token_name(); ?>'] = '<?php echo $this->security->get_csrf_hash(); ?>';
                   }
               },
        columns: [
            { data: null, className: 'text-center', render: function(data, type, row, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }},
            { data: 'ihris_pid', className: 'text-center' },
            { data: 'nin', className: 'text-center' },
            { data: 'fullname', render: function(data, type, row) {
                // Make full name clickable to employee time logs
                if (type !== 'display') return data;
                var safeName = $('<div>').text(data || '').html();
                var pid = row && row.ihris_pid ? String(row.ihris_pid) : '';
                if (!pid) return safeName;
                var href = baseUrl + 'employees/employeeTimeLogs/' + encodeURIComponent(pid);
                return '<a href="' + href + '" title="View time logs">' + safeName + '</a>';
            }},
            { data: 'gender', className: 'text-center' },
            { data: 'birth_date', className: 'text-center' },
            { data: 'ipps', className: 'text-center' },
            { data: 'card_number', className: 'text-center' },
            { data: 'phone', className: 'text-center' },
            { data: 'email' },
            { data: 'department' },
            { data: 'job' },
            { data: 'employment_terms', className: 'text-center' }
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
    
    // Initialize statistics
    updateShowingInfo();
    
    function updateShowingInfo() {
        var info = table.page.info();
        $('#showingInfo').text('Showing ' + (info.start + 1) + ' to ' + info.end + ' of ' + info.recordsTotal + ' entries');
    }
    
    function showLoading() {
        // Show loading indicator
        $('body').append('<div id="loadingOverlay" class="loading-overlay"><div class="loading-content"><div class="spinner-border text-info" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Processing...</p></div></div>');
        setTimeout(function() {
            $('#loadingOverlay').remove();
        }, 1000);
    }
});
</script>

<style>
/* Custom styling for the staff page */
.page-header {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  padding: 2rem;
  border-radius: 4px;
  margin-bottom: 2rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.page-title {
  font-size: 1.8rem;
  font-weight: 600;
  color: #212529;
  margin-bottom: 0.5rem;
}

.page-subtitle {
  font-size: 1rem;
  color: #6c757d;
  margin-bottom: 0;
}

.card {
  border-radius: 4px;
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

.form-control, .select2-container--default .select2-selection--multiple {
  border: 1px solid #dee2e6;
  border-radius: 4px;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus, .select2-container--default.select2-container--focus .select2-selection--multiple {
  border-color: #212529;
  box-shadow: 0 0 0 0.2rem rgba(33, 37, 41, 0.25);
}

.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.loading-content {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  text-align: center;
}
</style>
