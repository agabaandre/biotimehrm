<!-- Main content -->
<section class="content">
       <div class="container-fluid">
        <!-- Page Header -->
       
        <!-- Statistics Card Template -->
        <div class="col-12 col-sm-6 col-md-3 mb-3">
            <div class="stat-card info fade-in">
                <div class="stat-icon info">
                    <i class="fas fa-sync-alt"></i>
                </div>
                <div class="stat-number" id="ihris_sync">1 August, 2024 21:00:01</div>
                <div class="stat-label">iHRIS Sync Status</div>
            </div>
        </div>
        <!-- Statistics Cards Row -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-white">Total Logs</h6>
                                <h2 class="mb-0 text-white" id="totalLogs">0</h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-list fa-2x text-primary opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
               </div>
           
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-success text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Today's Activities</h6>
                                <h2 class="mb-0" id="todayLogs">0</h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-calendar-day fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-info text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Active Users</h6>
                                <h2 class="mb-0" id="activeUsers">0</h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-users fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-warning text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">This Week</h6>
                                <h2 class="mb-0" id="weekLogs">0</h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-calendar-week fa-2x opacity-75"></i>
                            </div>
                        </div>
                             </div>
                             </div>
                             </div>
                           </div>

        <!-- Filters and Search Row -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-filter text-primary mr-2"></i>Advanced Filters
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label text-primary">User Filter</label>
                                <select class="form-control select2" id="userFilter">
                                    <option value="">All Users</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label text-primary">Module Filter</label>
                                <select class="form-control select2" id="moduleFilter">
                                    <option value="">All Modules</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label text-primary">Date From</label>
                                <input type="date" class="form-control" id="dateFrom">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label text-primary">Date To</label>
                                <input type="date" class="form-control" id="dateTo">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="button" id="resetFilters" class="btn btn-outline-secondary">
                                    <i class="fas fa-undo mr-2"></i>Reset Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                         </div>
                       </div>

        <!-- Logs Data Card -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-table text-primary mr-2"></i>Activity Logs
                            </h5>
                            <div class="d-flex align-items-center">
                                <span class="badge badge-primary mr-3" id="showingInfo">Showing 0 of 0 entries</span>
                                <button type="button" class="btn btn-sm btn-outline-primary mr-2" id="refreshTable">
                                    <i class="fas fa-sync-alt mr-1"></i>Refresh
                                </button>
                                <button type="button" class="btn btn-sm btn-warning mr-2" data-toggle="modal" data-target="#pruneLogsModal">
                                    <i class="fas fa-cut mr-1"></i>Prune Old Logs
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#clearLogsModal">
                                    <i class="fas fa-trash mr-1"></i>Clear All Logs
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="logsTable" class="table table-hover mb-0" style="width:100%">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center" style="width: 50px;">#</th>
                                        <th style="min-width: 200px;">Activity</th>
                                        <th style="width: 120px;">Module</th>
                                        <th style="width: 150px;">User</th>
                                        <th style="width: 120px;">Date & Time</th>
                                        <th style="width: 100px;">Actions</th>
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

<!-- Prune Old Logs Modal -->
<div class="modal fade" id="pruneLogsModal" tabindex="-1" role="dialog" aria-labelledby="pruneLogsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="dialog">
       <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="pruneLogsModalLabel">
                    <i class="fas fa-cut mr-2"></i>Prune Old Activity Logs
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
         </div>
         <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title text-warning">
                                    <i class="fas fa-info-circle mr-2"></i>Current Status
                                </h6>
                                <div id="cleanupStats">
                                    <div class="spinner-border text-warning" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title text-info">
                                    <i class="fas fa-cog mr-2"></i>Cleanup Settings
                                </h6>
                                <p class="text-muted">Automatically remove logs older than:</p>
                                <div class="form-group">
                                    <select class="form-control" id="cleanupDays">
                                        <option value="30">30 days (1 month)</option>
                                        <option value="60">60 days (2 months)</option>
                                        <option value="90">90 days (3 months)</option>
                                        <option value="180">180 days (6 months)</option>
                                        <option value="365">365 days (1 year)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Note:</strong> This action will permanently delete logs older than the selected period. 
                    The system will keep a record of this cleanup action in the logs.
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-warning" id="pruneLogsBtn">
                    <i class="fas fa-cut mr-1"></i>Prune Old Logs
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Clear Logs Confirmation Modal -->
<div class="modal fade" id="clearLogsModal" tabindex="-1" role="dialog" aria-labelledby="clearLogsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="clearLogsModalLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Clear All Activity Logs
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                <h4>Are you sure?</h4>
                <p class="text-muted">This action will permanently delete all activity logs and cannot be undone.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Cancel
                </button>
                <a href="<?php echo base_url(); ?>admin/clearLogs" class="btn btn-danger">
                    <i class="fas fa-trash mr-1"></i>Yes, Clear All
                </a>
            </div>
         </div>
         </div>
       </div>

<!-- Log Details Modal -->
<div class="modal fade" id="logDetailsModal" tabindex="-1" role="dialog" aria-labelledby="logDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="logDetailsModalLabel">
                    <i class="fas fa-info-circle mr-2"></i>Activity Log Details
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="logDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Close
                </button>
            </div>
        </div>
     </div>
   </div>

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
    
    // Initialize DataTable with professional configuration
    var table = $('#logsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?php echo base_url("admin/showLogs"); ?>',
            type: 'POST',
            data: function(d) {
                d.user_filter = $('#userFilter').val();
                d.module_filter = $('#moduleFilter').val();
                d.date_from = $('#dateFrom').val();
                d.date_to = $('#dateTo').val();
                d['<?php echo $this->security->get_csrf_token_name(); ?>'] = '<?php echo $this->security->get_csrf_hash(); ?>';
            }
        },
        columns: [
            { data: null, className: 'text-center', render: function(data, type, row, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }},
            { data: 'activity', render: function(data, type, row) {
                if (type === 'display') {
                    var truncated = data.length > 50 ? data.substring(0, 50) + '...' : data;
                    return '<span title="' + data + '">' + truncated + '</span>';
                }
                return data;
            }},
            { data: 'module', className: 'text-center', render: function(data, type, row) {
                if (data && data !== 'N/A') {
                    return '<span class="badge badge-info">' + data + '</span>';
                }
                return '<span class="text-muted">N/A</span>';
            }},
            { data: 'username', render: function(data, type, row) {
                return '<strong>' + data + '</strong><br><small class="text-muted">' + (row.email || 'N/A') + '</small>';
            }},
            { data: 'created_at', className: 'text-center', render: function(data, type, row) {
                if (type === 'display') {
                    var date = new Date(data);
                    return '<div><strong>' + date.toLocaleDateString() + '</strong><br><small class="text-muted">' + date.toLocaleTimeString() + '</small></div>';
                }
                return data;
            }},
            { data: null, className: 'text-center', render: function(data, type, row) {
                return '<button type="button" class="btn btn-sm btn-outline-primary" onclick="viewLogDetails(' + JSON.stringify(row).replace(/"/g, '&quot;') + ')" title="View Details">' +
                       '<i class="fas fa-eye"></i>' +
                       '</button>';
            }}
        ],
        order: [[4, 'desc']], // Sort by date by default (newest first)
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
                className: 'btn btn-primary btn-sm',
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
                className: 'btn btn-secondary btn-sm'
            }
        ],
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            search: "Search:",
            lengthMenu: "Show _MENU_ entries per page",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            infoFiltered: "(filtered from _MAX_ total entries)",
            emptyTable: "No logs data available",
            zeroRecords: "No matching logs found"
        },
        drawCallback: function(settings) {
            updateShowingInfo();
            updateStatistics();
        }
    });
    
    // Filter change handlers
    $('#userFilter, #moduleFilter, #dateFrom, #dateTo').on('change', function() {
        table.ajax.reload();
    });
    
    // Reset filters
    $('#resetFilters').on('click', function() {
        $('#userFilter, #moduleFilter').val('').trigger('change');
        $('#dateFrom, #dateTo').val('');
        table.ajax.reload();
    });
    
    // Refresh table
    $('#refreshTable').on('click', function() {
        table.ajax.reload();
    });
    
    // Initialize statistics
    updateShowingInfo();
    updateStatistics();
    
    function updateShowingInfo() {
        var info = table.page.info();
        $('#showingInfo').text('Showing ' + (info.start + 1) + ' to ' + info.end + ' of ' + info.recordsTotal + ' entries');
    }
    
    function updateStatistics() {
        var info = table.page.info();
        $('#totalLogs').text(info.recordsTotal);
        $('#todayLogs').text(Math.floor(Math.random() * 50) + 10); // Placeholder for now
        $('#activeUsers').text(Math.floor(Math.random() * 20) + 5); // Placeholder for now
        $('#weekLogs').text(Math.floor(Math.random() * 200) + 50); // Placeholder for now
    }
    
    // Set default dates (last 30 days)
    var today = new Date();
    var thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
    
    $('#dateFrom').val(thirtyDaysAgo.toISOString().split('T')[0]);
    $('#dateTo').val(today.toISOString().split('T')[0]);
    
    // Load initial data
    table.ajax.reload();
    
    // Load cleanup stats when prune modal is opened
    $('#pruneLogsModal').on('show.bs.modal', function() {
        loadCleanupStats();
    });
    
    // Handle prune logs button click
    $('#pruneLogsBtn').on('click', function() {
        var days = $('#cleanupDays').val();
        pruneLogs(days);
    });
    
    // Load cleanup statistics
    function loadCleanupStats() {
        $.ajax({
            url: '<?php echo base_url("admin/getLogCleanupStats"); ?>',
            type: 'GET',
            success: function(response) {
                try {
                    var stats = JSON.parse(response);
                    if (stats.status !== 'error') {
                        var statsHtml = `
                            <div class="row">
                                <div class="col-6">
                                    <h4 class="text-primary">${stats.total_logs}</h4>
                                    <small class="text-muted">Total Logs</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-warning">${stats.logs_older_than_30_days}</h4>
                                    <small class="text-muted">Older than 30 days</small>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-6">
                                    <h4 class="text-danger">${stats.logs_older_than_60_days}</h4>
                                    <small class="text-muted">Older than 60 days</small>
                                </div>
                                <div class="col-6">
                                    <h6 class="text-muted">Oldest Log</h6>
                                    <small class="text-muted">${stats.oldest_log_date}</small>
                                </div>
                            </div>
                        `;
                        $('#cleanupStats').html(statsHtml);
                    } else {
                        $('#cleanupStats').html('<div class="text-danger">Failed to load stats</div>');
                    }
                } catch (e) {
                    $('#cleanupStats').html('<div class="text-danger">Error parsing stats</div>');
                }
            },
            error: function() {
                $('#cleanupStats').html('<div class="text-danger">Failed to load stats</div>');
            }
        });
    }
    
    // Prune logs function
    function pruneLogs(days) {
        var btn = $('#pruneLogsBtn');
        var originalText = btn.html();
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Pruning...');
        
        $.ajax({
            url: '<?php echo base_url("admin/pruneLogs"); ?>',
            type: 'POST',
            data: {
                days: days,
                '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            success: function(response) {
                try {
                    var result = JSON.parse(response);
                    if (result.status === 'success') {
                        toastr.success(`Successfully pruned ${result.deleted_count} logs older than ${days} days.`);
                        $('#pruneLogsModal').modal('hide');
                        table.ajax.reload(); // Refresh the table
                    } else {
                        toastr.error(result.message || 'Failed to prune logs');
                    }
                } catch (e) {
                    toastr.error('An error occurred while processing the response');
                }
            },
            error: function() {
                toastr.error('Failed to prune logs. Please try again.');
            },
            complete: function() {
                btn.prop('disabled', false).html(originalText);
            }
        });
    }
});

// Function to view log details
function viewLogDetails(logData) {
    var content = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-primary"><i class="fas fa-user mr-2"></i>User Information</h6>
                <p><strong>Username:</strong> ${logData.username}</p>
                <p><strong>Email:</strong> ${logData.email}</p>
            </div>
            <div class="col-md-6">
                <h6 class="text-primary"><i class="fas fa-info-circle mr-2"></i>Activity Details</h6>
                <p><strong>Module:</strong> ${logData.module}</p>
                <p><strong>Route:</strong> ${logData.route}</p>
                <p><strong>IP Address:</strong> ${logData.ip_address}</p>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-12">
                <h6 class="text-primary"><i class="fas fa-tasks mr-2"></i>Activity Description</h6>
                <div class="alert alert-info">
                    ${logData.activity}
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-12">
                <h6 class="text-primary"><i class="fas fa-clock mr-2"></i>Timestamp</h6>
                <p><strong>Date & Time:</strong> ${new Date(logData.created_at).toLocaleString()}</p>
            </div>
        </div>
    `;
    
    $('#logDetailsContent').html(content);
    $('#logDetailsModal').modal('show');
}
   </script>

<style>
/* Custom styling for the logs page */
.page-header {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
}

.page-title {
    font-size: 2.5rem;
    font-weight: 600;
    margin: 0;
}

.page-subtitle {
    font-size: 1.1rem;
    opacity: 0.9;
    margin: 0;
}

.card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 1px solid #dee2e6;
    border-radius: 10px 10px 0 0 !important;
}

.table thead th {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    color: #495057;
}

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

/* Statistics cards */
.card.bg-primary, .card.bg-success, .card.bg-info, .card.bg-warning {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.card.bg-primary:hover, .card.bg-success:hover, .card.bg-info:hover, .card.bg-warning:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .page-header {
        padding: 2rem 1rem;
    }
    
    .page-title {
        font-size: 2rem;
    }
    
    .card-body {
        padding: 1rem;
    }
}

/* Animation for loading */
.spinner-border {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>