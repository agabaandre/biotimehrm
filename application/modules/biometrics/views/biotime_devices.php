<!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <!-- Main row -->
      <div class="row">
      <!-- Left col -->
      <section class="col-lg-12 connectedSortable">
        <!-- Custom tabs (Charts with tabs)-->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-server"></i> BioTime Devices Management
            </h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
            </div>
          </div><!-- /.card-header -->
          <div class="card-body">
            <div class="table-responsive">
              <table id="devicesTable" class="table table-bordered table-striped" style="width:100%">
                <thead class="thead-dark">
                  <tr>
                    <th>Serial Number</th>
                <th>Facility</th>
                <th>Last Sync</th>
                <th>Fingerprint Enrolled Users</th>
                <th>IP Address</th>
                    <th>Status</th>
                    <th>Actions</th>
              </tr>
            </thead>
          </table>
            </div>
          </div><!-- /.card-body -->
        </div><!-- /.card -->
      </section>
    </div><!-- /.row (main row) -->
  </div><!-- /.container-fluid -->
</section>

<!-- Device Details Modal -->
<div class="modal fade" id="deviceModal" tabindex="-1" role="dialog" aria-labelledby="deviceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="deviceModalLabel">
          <i class="fas fa-server"></i> Device Details
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label class="font-weight-bold">Serial Number:</label>
              <p id="modal-sn" class="form-control-plaintext"></p>
            </div>
            <div class="form-group">
              <label class="font-weight-bold">Facility:</label>
              <p id="modal-facility" class="form-control-plaintext"></p>
            </div>
            <div class="form-group">
              <label class="font-weight-bold">IP Address:</label>
              <p id="modal-ip" class="form-control-plaintext"></p>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label class="font-weight-bold">Last Sync:</label>
              <p id="modal-last-sync" class="form-control-plaintext"></p>
            </div>
            <div class="form-group">
              <label class="font-weight-bold">Enrolled Users:</label>
              <p id="modal-users" class="form-control-plaintext"></p>
            </div>
            <div class="form-group">
              <label class="font-weight-bold">Status:</label>
              <p id="modal-status" class="form-control-plaintext"></p>
            </div>
          </div>
        </div>
        
        <div class="alert alert-info">
          <i class="fas fa-info-circle"></i>
          <strong>Device Information:</strong> This shows the current status and configuration of the selected BioTime device.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
          <i class="fas fa-times"></i> Close
        </button>
        <button type="button" class="btn btn-primary" id="editDevice">
          <i class="fas fa-edit"></i> Edit Device
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Device Modal -->
<div class="modal fade" id="editDeviceModal" tabindex="-1" role="dialog" aria-labelledby="editDeviceModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title" id="editDeviceModalLabel">
          <i class="fas fa-edit"></i> Edit Device
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="editDeviceForm">
          <div class="form-group">
            <label for="edit-sn" class="font-weight-bold">Serial Number</label>
            <input type="text" class="form-control" id="edit-sn" name="sn" readonly>
          </div>
          <div class="form-group">
            <label for="edit-area-name" class="font-weight-bold">Facility Name</label>
            <input type="text" class="form-control" id="edit-area-name" name="area_name" required>
          </div>
          <div class="form-group">
            <label for="edit-ip-address" class="font-weight-bold">IP Address</label>
            <input type="text" class="form-control" id="edit-ip-address" name="ip_address" required>
          </div>
          <div class="form-group">
            <label for="edit-user-count" class="font-weight-bold">User Count</label>
            <input type="number" class="form-control" id="edit-user-count" name="user_count" min="0" required>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
          <i class="fas fa-times"></i> Cancel
        </button>
        <button type="button" class="btn btn-warning" id="saveDevice">
          <i class="fas fa-save"></i> Save Changes
        </button>
      </div>
    </div>
  </div>
</div>

<style>
.card {
  box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
  margin-bottom: 1rem;
}

.card-header {
  background-color: rgba(0,0,0,.03);
  border-bottom: 1px solid rgba(0,0,0,.125);
}

.table thead th {
  border-bottom: 2px solid #dee2e6;
  font-weight: 600;
}

.badge {
  font-size: 0.875em;
}

.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
}

.modal-header.bg-primary {
  background-color: #007bff !important;
}

.modal-header.bg-warning {
  background-color: #ffc107 !important;
  color: #212529 !important;
}

.device-actions {
  display: flex;
  gap: 5px;
  justify-content: center;
}

.device-actions .btn {
  margin: 0;
}
</style>

<script>
$(document).ready(function() {
    // Initialize DataTable with server-side processing
    var table = $('#devicesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?php echo base_url("biometrics/getMachinesAjax"); ?>',
            type: 'POST',
            data: function(d) {
                d['<?php echo $this->security->get_csrf_token_name(); ?>'] = '<?php echo $this->security->get_csrf_hash(); ?>';
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables Ajax Error:', {
                    xhr: xhr,
                    error: error,
                    thrown: thrown,
                    responseText: xhr.responseText
                });
                
                // Show user-friendly error message
                if (xhr.status === 0) {
                    alert('Network error: Please check your internet connection.');
                } else if (xhr.status === 404) {
                    alert('Page not found: The requested endpoint does not exist.');
                } else if (xhr.status === 500) {
                    alert('Server error: Please contact the administrator.');
                } else {
                    alert('An error occurred while loading data. Please try again.');
                }
            }
        },
        columns: [
            { data: 0, className: 'text-center' }, // Serial Number
            { data: 1 }, // Facility
            { data: 2, className: 'text-center' }, // Last Sync
            { data: 3, className: 'text-center' }, // Number of Records
            { data: 4, className: 'text-center' }, // IP Address
            { data: 5, className: 'text-center' }, // Status
            { 
                data: 6, // Actions
                className: 'text-center',
                render: function(data, type, row) {
                    return '<div class="device-actions">' +
                           '<button type="button" class="btn btn-sm btn-info view-device" data-row="' + JSON.stringify(row).replace(/"/g, '&quot;') + '"><i class="fas fa-eye"></i></button>' +
                           '<button type="button" class="btn btn-sm btn-warning edit-device" data-row="' + JSON.stringify(row).replace(/"/g, '&quot;') + '"><i class="fas fa-edit"></i></button>' +
                           '<button type="button" class="btn btn-sm btn-danger delete-device" data-sn="' + row[0] + '"><i class="fas fa-trash"></i></button>' +
                           '</div>';
                }
            }
        ],
        order: [[2, 'desc']], // Sort by last sync date by default
        pageLength: 25,
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
            search: "Search devices:",
            lengthMenu: "Show _MENU_ devices per page",
            info: "Showing _START_ to _END_ of _TOTAL_ devices",
            infoEmpty: "No devices found",
            infoFiltered: "(filtered from _MAX_ total devices)"
        }
    });

    // Handle view device button clicks
    $(document).on('click', '.view-device', function() {
        var rowData = JSON.parse($(this).data('row').replace(/&quot;/g, '"'));
        
        $('#modal-sn').text(rowData[0]);
        $('#modal-facility').text(rowData[1]);
        $('#modal-ip').text(rowData[4]);
        $('#modal-last-sync').text(rowData[2]);
        $('#modal-users').text(rowData[3]);
        $('#modal-status').html(rowData[5]);
        
        $('#deviceModal').modal('show');
    });

    // Handle edit device button clicks
    $(document).on('click', '.edit-device', function() {
        var rowData = JSON.parse($(this).data('row').replace(/&quot;/g, '"'));
        
        $('#edit-sn').val(rowData[0]);
        $('#edit-area-name').val(rowData[1]);
        $('#edit-ip-address').val(rowData[4]);
        $('#edit-user-count').val(rowData[3]);
        
        $('#editDeviceModal').modal('show');
    });

    // Handle save device changes
    $('#saveDevice').on('click', function() {
        var formData = $('#editDeviceForm').serialize();
        
        $.ajax({
            url: '<?php echo base_url("biometrics/updateDevice"); ?>',
            type: 'POST',
            data: formData + '&<?php echo $this->security->get_csrf_token_name(); ?>=<?php echo $this->security->get_csrf_hash(); ?>',
            success: function(response) {
                if (response.success) {
                    toastr.success('Device updated successfully!');
                    $('#editDeviceModal').modal('hide');
                    table.ajax.reload();
                } else {
                    toastr.error('Failed to update device: ' + response.message);
                }
            },
            error: function() {
                toastr.error('Error updating device. Please try again.');
            }
        });
    });

    // Handle delete device
    $(document).on('click', '.delete-device', function() {
        var sn = $(this).data('sn');
        
        if (confirm('Are you sure you want to delete device ' + sn + '? This action cannot be undone.')) {
            $.ajax({
                url: '<?php echo base_url("biometrics/deleteDevice"); ?>',
                type: 'POST',
                data: {
                    sn: sn,
                    '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('Device deleted successfully!');
                        table.ajax.reload();
                    } else {
                        toastr.error('Failed to delete device: ' + response.message);
                    }
                },
                error: function() {
                    toastr.error('Error deleting device. Please try again.');
                }
            });
        }
    });

    // Refresh table every 60 seconds
    setInterval(function() {
        table.ajax.reload(null, false);
    }, 60000);

    // Add passive event listeners to improve performance
    document.addEventListener('scroll', function() {}, { passive: true });
    document.addEventListener('wheel', function() {}, { passive: true });
    document.addEventListener('touchstart', function() {}, { passive: true });
    document.addEventListener('touchmove', function() {}, { passive: true });
});
</script>