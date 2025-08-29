         <?php
          $requests = Modules::run('requests/getPending', NULL, NULL, 'Pending', NULL);
          $userdata = $this->session->get_userdata();
          $permissions = $userdata['permissions'];
?>

<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-3">
      <div class="col-12">
        <div class="page-header">
          <h1 class="page-title">
            <i class="fas fa-inbox text-primary mr-2"></i>
            Incoming Requests
          </h1>
          <p class="page-subtitle">Review and manage all incoming requests for approval</p>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
      <div class="col-lg-3 col-md-6">
        <div class="card bg-warning text-white">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h4 class="mb-0"><?php echo count(array_filter($requests, function($r) { return $r->status == 'Pending'; })); ?></h4>
                <p class="mb-0">Pending</p>
              </div>
              <div class="align-self-center">
                <i class="fas fa-clock fa-2x"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="card bg-success text-white">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h4 class="mb-0"><?php echo count(array_filter($requests, function($r) { return $r->status == 'Approved'; })); ?></h4>
                <p class="mb-0">Approved</p>
              </div>
              <div class="align-self-center">
                <i class="fas fa-check-circle fa-2x"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="card bg-danger text-white">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h4 class="mb-0"><?php echo count(array_filter($requests, function($r) { return $r->status == 'Rejected'; })); ?></h4>
                <p class="mb-0">Rejected</p>
              </div>
              <div class="align-self-center">
                <i class="fas fa-times-circle fa-2x"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="card bg-info text-white">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h4 class="mb-0"><?php echo count($requests); ?></h4>
                <p class="mb-0">Total</p>
              </div>
              <div class="align-self-center">
                <i class="fas fa-file-alt fa-2x"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Notification Bell -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="alert alert-info alert-dismissible fade show" role="alert">
          <div class="d-flex align-items-center">
            <i class="fas fa-bell text-primary fa-2x mr-3"></i>
            <div>
              <h6 class="alert-heading mb-1">New Requests Available</h6>
              <p class="mb-0">You have <strong><?php echo count(array_filter($requests, function($r) { return $r->status == 'Pending'; })); ?> pending requests</strong> that require your attention.</p>
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      </div>
    </div>

    <!-- Filters Card -->
    <div class="row mb-4">
      <div class="col-12">
         <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-filter text-primary mr-2"></i>Filters & Search
            </h5>
          </div>
          <div class="card-body">
            <form id="filterForm" class="row g-3">
              <div class="col-md-2">
                <label class="form-label">Status</label>
                <select class="form-control select2" id="statusFilter">
                  <option value="">All Statuses</option>
                  <option value="Pending">Pending</option>
                  <option value="Approved">Approved</option>
                  <option value="Rejected">Rejected</option>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label">Unit</label>
                <select class="form-control select2" id="unitFilter">
                  <option value="">All Units</option>
                  <?php 
                  $units = array_unique(array_column($requests, 'unit'));
                  foreach ($units as $unit): ?>
                    <option value="<?php echo $unit; ?>"><?php echo $unit; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label">Date Range</label>
                <select class="form-control select2" id="dateFilter">
                  <option value="">All Dates</option>
                  <option value="today">Today</option>
                  <option value="week">This Week</option>
                  <option value="month">This Month</option>
                  <option value="quarter">This Quarter</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" class="form-control" id="searchInput" placeholder="Search by name, reason, unit...">
              </div>
              <div class="col-md-2 d-flex align-items-end">
                <button type="button" id="resetFilters" class="btn btn-outline-secondary w-100">
                  <i class="fas fa-undo mr-1"></i>Reset
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Requests Table Card -->
               <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="card-title mb-0">
                <i class="fas fa-table text-primary mr-2"></i>All Requests
              </h5>
              <div class="d-flex align-items-center">
                <span class="badge badge-info mr-2" id="showingInfo">Showing 0 of 0 entries</span>
                <button type="button" class="btn btn-sm btn-outline-primary" id="refreshTable">
                  <i class="fas fa-sync-alt"></i>
                </button>
              </div>
            </div>
                     </div>
          <div class="card-body p-0">
                       <div class="table-responsive">
              <table id="requestsTable" class="table table-hover mb-0" style="width:100%">
                <thead>
                  <tr>
                    <th class="text-center" style="width: 50px;">#</th>
                    <th style="min-width: 200px;">Staff Name</th>
                    <th style="min-width: 120px;">Unit</th>
                    <th style="min-width: 150px;">Request Reason</th>
                    <th style="width: 120px;">Request Date</th>
                    <th style="min-width: 200px;">Duration</th>
                    <th style="width: 120px;">Attachment</th>
                    <th style="width: 120px;">Status</th>
                    <th style="width: 200px;">Actions</th>
                             </tr>
                           </thead>
                           <tbody>
                  <?php if (!empty($requests)): ?>
                    <?php foreach ($requests as $request): ?>
                      <tr>
                        <td class="text-center"><?php echo $loop->iteration ?? $loop->index + 1; ?></td>
                        <td>
                          <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-2" style="width: 32px; height: 32px;">
                              <i class="fas fa-user"></i>
                            </div>
                            <div>
                              <div class="font-weight-bold"><?php echo $request->surname . " " . $request->firstname . " " . $request->othername; ?></div>
                            </div>
                          </div>
                        </td>
                        <td>
                          <span class="badge badge-secondary"><?php echo $request->unit; ?></span>
                        </td>
                        <td>
                          <span class="badge badge-info"><?php echo $request->reason; ?></span>
                        </td>
                        <td class="text-center"><?php echo date('M j, Y H:i', strtotime($request->date)); ?></td>
                        <td>
                          <div class="text-muted">
                            <div><i class="fas fa-calendar-day text-primary mr-1"></i>From: <?php echo date('M j, Y', strtotime($request->dateFrom)); ?></div>
                            <div><i class="fas fa-calendar-day text-success mr-1"></i>To: <?php echo date('M j, Y', strtotime($request->dateTo)); ?></div>
                          </div>
                        </td>
                        <td class="text-center">
                          <?php if ($request->attachment == ""): ?>
                            <span class="badge badge-danger">
                              <i class="fas fa-times-circle mr-1"></i>No Files
                            </span>
                          <?php else: ?>
                            <a href="<?php echo base_url(); ?>assets/files/<?php echo $request->attachment; ?>" 
                               target="_blank" class="btn btn-sm btn-outline-info">
                              <i class="fas fa-download mr-1"></i>View File
                            </a>
                          <?php endif; ?>
                                 </td>
                        <td class="text-center">
                          <?php
                          $statusClass = '';
                          $statusIcon = '';
                          switch($request->status) {
                            case 'Pending':
                              $statusClass = 'badge-warning';
                              $statusIcon = 'fa-clock';
                              break;
                            case 'Approved':
                              $statusClass = 'badge-success';
                              $statusIcon = 'fa-check-circle';
                              break;
                            case 'Rejected':
                              $statusClass = 'badge-danger';
                              $statusIcon = 'fa-times-circle';
                              break;
                            default:
                              $statusClass = 'badge-secondary';
                              $statusIcon = 'fa-question-circle';
                          }
                          ?>
                          <span class="badge <?php echo $statusClass; ?>">
                            <i class="fas <?php echo $statusIcon; ?> mr-1"></i><?php echo $request->status; ?>
                          </span>
                        </td>
                        <td class="text-center">
                          <?php if (in_array('30', $permissions)): ?>
                            <div class="btn-group" role="group">
                              <button data-toggle="modal" data-target="#clarify<?php echo $request->entry_id; ?>" 
                                      class="btn btn-info btn-sm" title="Query Request">
                                <i class="fas fa-question-circle"></i>
                              </button>
                              <button data-toggle="modal" data-target="#acceptr<?php echo $request->entry_id; ?>" 
                                      class="btn btn-success btn-sm" title="Accept Request">
                                <i class="fas fa-check"></i>
                              </button>
                              <button data-toggle="modal" data-target="#rejectr<?php echo $request->entry_id; ?>" 
                                      class="btn btn-danger btn-sm" title="Reject Request">
                                <i class="fas fa-times"></i>
                              </button>
                            </div>
                          <?php else: ?>
                            <span class="badge badge-warning">
                              <i class="fas fa-clock mr-1"></i>Being Processed by HR
                            </span>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="9" class="text-center py-4">
                        <div class="text-muted">
                          <i class="fas fa-inbox fa-3x mb-3"></i>
                          <h5>No requests found</h5>
                          <p>There are no pending requests at the moment.</p>
                        </div>
                                 </td>
                               </tr>
                  <?php endif; ?>
                           </tbody>
                         </table>
                       </div>
                     </div>
                   </div>
                 </div>
               </div>
             </div>
</section>

<!-- Include the edit request modal -->
<?php include('edit_request_modal.php'); ?>

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
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });
    
    // Initialize DataTable
    var table = $('#requestsTable').DataTable({
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
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            search: "Search:",
            lengthMenu: "Show _MENU_ entries per page",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            infoFiltered: "(filtered from _MAX_ total entries)",
            emptyTable: "No requests data available",
            zeroRecords: "No matching requests found"
        },
        drawCallback: function(settings) {
            updateShowingInfo();
        }
    });
    
    // Handle filters
    $('#statusFilter, #unitFilter, #dateFilter').on('change', function() {
        applyFilters();
    });
    
    $('#searchInput').on('keyup', function() {
        table.search(this.value).draw();
    });
    
    // Reset filters
    $('#resetFilters').on('click', function() {
        $('#statusFilter, #unitFilter, #dateFilter').val('').trigger('change');
        $('#searchInput').val('');
        table.search('').draw();
    });
    
    // Refresh table
    $('#refreshTable').on('click', function() {
        table.ajax.reload();
    });
    
    function applyFilters() {
        var statusFilter = $('#statusFilter').val();
        var unitFilter = $('#unitFilter').val();
        var dateFilter = $('#dateFilter').val();
        
        // Custom filtering logic can be added here
        table.draw();
    }
    
    function updateShowingInfo() {
        var info = table.page.info();
        $('#showingInfo').text('Showing ' + (info.start + 1) + ' to ' + info.end + ' of ' + info.recordsTotal + ' entries');
    }
    
    // Initialize
    updateShowingInfo();
    
    // Auto-dismiss notification after 10 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 10000);
});
</script>

<style>
/* Custom styling for the requests page */
.page-header {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  padding: 2rem;
  border-radius: 8px;
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

.avatar-sm {
  font-size: 0.875rem;
}

.alert {
  border-radius: 8px;
  border: none;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.alert-info {
  background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
  color: #0c5460;
  border-left: 4px solid #17a2b8;
}

/* Responsive improvements */
@media (max-width: 768px) {
  .page-header {
    padding: 1.5rem;
  }
  
  .page-title {
    font-size: 1.5rem;
  }
  
  .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
  }
  
  .btn-group .btn {
    margin-bottom: 0.25rem;
  }
}
</style>