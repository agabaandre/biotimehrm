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
              <i class="fas fa-chart-bar"></i> iHRIS Data vs Bio-Time Status
            </h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
            </div>
          </div><!-- /.card-header -->
          <div class="card-body">
            <?php $activity = Modules::run('biotime/bioihriscontrol'); ?>
            <div class="table-responsive">
              <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                  <tr>
                    <th>DATA SET</th>
                    <th>IHRIS COUNT</th>
                    <th>LAST SYNC</th>
                    <th>BIOTIME COUNT</th>
                    <th>LAST SYNC</th>
                    <th>GAP</th>
                    <th>ACTION</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <th data-label="Serial Number">DEPARTMENTS</th>
                    <td data-label="Facility"><?php echo $activity['ihrisdeps']; ?></td>
                    <td data-label="Facility"><?php echo $activity['ilastsync']; ?></td>
                    <td data-label="Last Sync"><?php echo $activity['biodeps']; ?></td>
                    <td data-label="Facility"><?php echo $activity['blastdepssync']; ?></td>
                    <td data-label="Last Sync">
                      <span class="badge badge-<?php echo $activity['depsgap'] > 0 ? 'warning' : 'success'; ?>">
                        <?php echo $activity['depsgap']; ?>
                      </span>
                    </td>
                    <td data-label="Last Sync">
                      <a href="" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-sync"></i> Sync from Biotime
                      </a>
                    </td>
                  </tr>
                  <tr>
                    <th data-label="Serial Number">FACILITIES /AREAS</th>
                    <td data-label="Facility"><?php echo $activity['ihrisfacs']; ?></td>
                    <td data-label="Facility"><?php echo $activity['ilastsync']; ?></td>
                    <td data-label="Last Sync"><?php echo $activity['biofacs']; ?></td>
                    <td data-label="Facility"><?php echo $activity['blastfacsync']; ?></td>
                    <td data-label="Last Sync">
                      <span class="badge badge-<?php echo $activity['facsgap'] > 0 ? 'warning' : 'success'; ?>">
                        <?php echo $activity['facsgap']; ?>
                      </span>
                    </td>
                    <td data-label="Last Sync">
                      <a href="" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-sync"></i> Sync from Biotime
                      </a>
                    </td>
                  </tr>
                  <tr>
                    <th data-label="Serial Number">JOB / POSITION</th>
                    <td data-label="Facility"><?php echo $activity['ihrisjobs']; ?></td>
                    <td data-label="Facility"><?php echo $activity['ilastsync']; ?></td>
                    <td data-label="Last Sync"><?php echo $activity['biojobs']; ?></td>
                    <td data-label="Facility"><?php echo $activity['blastjobssync']; ?></td>
                    <td data-label="Last Sync">
                      <span class="badge badge-<?php echo $activity['jobsgap'] > 0 ? 'warning' : 'success'; ?>">
                        <?php echo $activity['jobsgap']; ?>
                      </span>
                    </td>
                    <td data-label="Last Sync">
                      <a href="biotimejobs/" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-sync"></i> Sync from Biotime
                      </a>
                    </td>
                  </tr>
                  <tr>
                    <th data-label="Serial Number">EMPLOYEES</th>
                    <td data-label="Facility"><?php echo $activity['ihrisusers']; ?></td>
                    <td data-label="Facility"><?php echo $activity['ilastsync']; ?></td>
                    <td data-label="Last Sync"><?php echo $activity['biousers']; ?></td>
                    <td data-label="Facility"><?php echo $activity['biouserssync']; ?></td>
                    <td data-label="Last Sync">
                      <span class="badge badge-<?php echo $activity['usersgap'] > 0 ? 'warning' : 'success'; ?>">
                        <?php echo $activity['usersgap']; ?>
                      </span>
                    </td>
                    <td data-label="Last Sync">
                      <a href="<?php echo base_url() ?>biotimejobs/saveEnrolled" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-sync"></i> Sync from Biotime
                      </a>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div><!-- /.card-body -->
        </div><!-- /.card -->
      </section>

      <!-- Right col -->
      <section class="col-lg-12 connectedSortable">
        <!-- Custom tabs (Charts with tabs)-->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-server"></i> Attendance Sync - Machines
            </h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
            </div>
          </div><!-- /.card-header -->
          <div class="card-body">
            <div class="table-responsive">
              <table id="machinesTable" class="table table-bordered table-striped" style="width:100%">
                <thead class="thead-dark">
                  <tr>
                    <th>Serial Number</th>
                    <th>Facility</th>
                    <th>Last Sync</th>
                    <th>Number of Records</th>
                    <th>IP Address</th>
                    <th>Status</th>
                    <th>Manual Sync</th>
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

<!-- Sync Modal -->
<div class="modal fade" id="syncModal" tabindex="-1" role="dialog" aria-labelledby="syncModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="syncModalLabel">
          <i class="fas fa-sync"></i> Sync Time Data
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="syncForm" action="<?php echo base_url('biotimejobs/custom_logs'); ?>" method="GET">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="terminal_sn" class="font-weight-bold">Terminal Serial Number</label>
                <input type="text" name="terminal_sn" id="terminal_sn" class="form-control" readonly>
                <small class="form-text text-muted">Machine identifier</small>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="end_date" class="font-weight-bold">Date Before</label>
                <input type="date" name="end_date" id="end_date" class="form-control" required>
                <small class="form-text text-muted">Sync data before this date</small>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="sync_type" class="font-weight-bold">Sync Type</label>
                <select name="sync_type" id="sync_type" class="form-control">
                  <option value="attendance">Attendance Records</option>
                  <option value="users">User Data</option>
                  <option value="all">All Data</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="batch_size" class="font-weight-bold">Batch Size</label>
                <select name="batch_size" id="batch_size" class="form-control">
                  <option value="100">100 Records</option>
                  <option value="500">500 Records</option>
                  <option value="1000">1000 Records</option>
                </select>
              </div>
            </div>
          </div>

          <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>Note:</strong> This will sync data from the selected machine. Large datasets may take several minutes to process.
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
          <i class="fas fa-times"></i> Cancel
        </button>
        <button type="submit" form="syncForm" class="btn btn-primary">
          <i class="fas fa-sync"></i> Start Sync
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Progress Modal -->
<div class="modal fade" id="progressModal" tabindex="-1" role="dialog" aria-labelledby="progressModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info">
        <h5 class="modal-title" id="progressModalLabel">
          <i class="fas fa-spinner fa-spin"></i> Sync in Progress
        </h5>
      </div>
      <div class="modal-body">
        <div class="progress">
          <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
        </div>
        <div class="text-center mt-3">
          <p class="sync-status">Initializing sync process...</p>
        </div>
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

.modal-header.bg-info {
  background-color: #17a2b8 !important;
}

.progress {
  height: 20px;
}

.sync-status {
  font-weight: 500;
  color: #6c757d;
}
</style>

<script>
$(document).ready(function() {
    // Initialize DataTable with server-side processing
    var table = $('#machinesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?php echo base_url("biotime/getMachinesAjax"); ?>',
            type: 'POST',
            data: function(d) {
                d['<?php echo $this->security->get_csrf_token_name(); ?>'] = '<?php echo $this->security->get_csrf_hash(); ?>';
            }
        },
        columns: [
            { data: 0 }, // Serial Number
            { data: 1 }, // Facility
            { data: 2 }, // Last Sync
            { data: 3 }, // Number of Records
            { data: 4 }, // IP Address
            { data: 5 }, // Status
            { data: 6 }  // Manual Sync
        ],
        order: [[2, 'desc']], // Sort by last sync date by default
        pageLength: 25,
        responsive: true,
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        language: {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>',
            search: "Search machines:",
            lengthMenu: "Show _MENU_ machines per page",
            info: "Showing _START_ to _END_ of _TOTAL_ machines",
            infoEmpty: "No machines found",
            infoFiltered: "(filtered from _MAX_ total machines)"
        }
    });

    // Handle sync button clicks
    $(document).on('click', '.sync-machine', function() {
        var sn = $(this).data('sn');
        $('#terminal_sn').val(sn);
        $('#end_date').val(new Date().toISOString().split('T')[0]);
        $('#syncModal').modal('show');
    });

    // Handle form submission
    $('#syncForm').on('submit', function(e) {
        e.preventDefault();
        
        // Show progress modal
        $('#syncModal').modal('hide');
        $('#progressModal').modal('show');
        
        // Simulate progress (replace with actual AJAX call)
        var progress = 0;
        var interval = setInterval(function() {
            progress += Math.random() * 15;
            if (progress >= 100) {
                progress = 100;
                clearInterval(interval);
                $('.sync-status').html('<i class="fas fa-check-circle text-success"></i> Sync completed successfully!');
                setTimeout(function() {
                    $('#progressModal').modal('hide');
                    // Refresh the table
                    table.ajax.reload();
                }, 2000);
            }
            $('.progress-bar').css('width', progress + '%');
            $('.sync-status').text('Processing... ' + Math.round(progress) + '%');
        }, 500);
        
        // Submit form via AJAX
        $.ajax({
            url: $(this).attr('action'),
            type: 'GET',
            data: $(this).serialize(),
            success: function(response) {
                // Handle success
                console.log('Sync initiated:', response);
            },
            error: function(xhr, status, error) {
                // Handle error
                console.error('Sync error:', error);
                $('.sync-status').html('<i class="fas fa-exclamation-circle text-danger"></i> Sync failed: ' + error);
            }
        });
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