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
            <?php $activity = Modules::run('biometrics/bioihriscontrol'); ?>
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
                      <button type="button" class="btn btn-sm btn-outline-primary sync-data-btn" data-sync-type="departments">
                        <i class="fas fa-sync"></i> Sync from Biotime
                      </button>
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
                      <button type="button" class="btn btn-sm btn-outline-primary sync-data-btn" data-sync-type="facilities">
                        <i class="fas fa-sync"></i> Sync from Biotime
                      </button>
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
                      <button type="button" class="btn btn-sm btn-outline-primary sync-data-btn" data-sync-type="jobs">
                        <i class="fas fa-sync"></i> Sync from Biotime
                      </button>
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
                      <button type="button" class="btn btn-sm btn-outline-primary sync-data-btn" data-sync-type="employees">
                        <i class="fas fa-sync"></i> Sync from Biotime
                      </button>
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

<!-- Terminal/Console Modal -->
<div class="modal fade" id="terminalModal" tabindex="-1" role="dialog" aria-labelledby="terminalModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content terminal-container">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title" id="terminalModalLabel">
          <i class="fas fa-terminal"></i> Sync Terminal Output
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-0">
        <div id="terminal" class="terminal">
          <div class="terminal-header">
            <span class="terminal-prompt">$</span> <span class="terminal-command">sync-attendance</span>
          </div>
          <div id="terminal-output" class="terminal-output">
            <div class="terminal-line">Initializing sync process...</div>
          </div>
          <div class="terminal-input-line">
            <span class="terminal-prompt">$</span>
            <span class="terminal-cursor">█</span>
        </div>
        </div>
      </div>
      <div class="modal-footer bg-dark">
        <button type="button" class="btn btn-sm btn-secondary" id="clearTerminal">
          <i class="fas fa-eraser"></i> Clear
        </button>
        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">
          <i class="fas fa-times"></i> Close
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

/* Terminal/Console Styling */
.terminal-container {
  background: #1e1e1e;
  color: #d4d4d4;
  font-family: 'Courier New', 'Consolas', 'Monaco', monospace;
  border-radius: 4px;
  overflow: hidden;
}

.terminal {
  background: #1e1e1e;
  color: #d4d4d4;
  padding: 15px;
  min-height: 400px;
  max-height: 600px;
  overflow-y: auto;
  font-size: 13px;
  line-height: 1.6;
}

.terminal-header {
  color: #4ec9b0;
  margin-bottom: 10px;
  font-weight: bold;
}

.terminal-prompt {
  color: #4ec9b0;
  margin-right: 8px;
}

.terminal-command {
  color: #ce9178;
}

.terminal-output {
  margin-bottom: 10px;
}

.terminal-line {
  margin-bottom: 4px;
  word-wrap: break-word;
  white-space: pre-wrap;
}

.terminal-line.success {
  color: #4ec9b0;
}

.terminal-line.error {
  color: #f48771;
}

.terminal-line.warning {
  color: #dcdcaa;
}

.terminal-line.info {
  color: #569cd6;
}

.terminal-input-line {
  display: flex;
  align-items: center;
  margin-top: 10px;
}

.terminal-cursor {
  background: #d4d4d4;
  color: #1e1e1e;
  animation: blink 1s infinite;
  margin-left: 2px;
  width: 8px;
  display: inline-block;
}

@keyframes blink {
  0%, 50% { opacity: 1; }
  51%, 100% { opacity: 0; }
}

.terminal::-webkit-scrollbar {
  width: 10px;
}

.terminal::-webkit-scrollbar-track {
  background: #252526;
}

.terminal::-webkit-scrollbar-thumb {
  background: #3e3e42;
  border-radius: 5px;
}

.terminal::-webkit-scrollbar-thumb:hover {
  background: #505050;
}
</style>

<script>
$(document).ready(function() {
    // Initialize DataTable with server-side processing
    var table = $('#machinesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?php echo base_url("biometrics/getMachinesAjax"); ?>',
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

    // Handle sync button clicks from DataTable
    $(document).on('click', '.sync-machine', function() {
        var sn = $(this).data('sn');
        $('#terminal_sn').val(sn);
        $('#end_date').val(new Date().toISOString().split('T')[0]);
        $('#sync_type').val('attendance');
        $('#batch_size').val('500');
        $('#syncModal').modal('show');
    });
    
    // Handle sync data buttons (Departments, Facilities, Jobs, Employees)
    $(document).on('click', '.sync-data-btn', function() {
        var syncType = $(this).data('sync-type');
        var syncTypeLabel = syncType.charAt(0).toUpperCase() + syncType.slice(1);
        
        // Show terminal modal
        $('#terminalModal').modal('show');
        
        // Clear and initialize terminal
        $('#terminal-output').empty();
        addTerminalLine('=== ' + syncTypeLabel + ' Sync Process Started ===', 'info');
        addTerminalLine('Sync Type: ' + syncTypeLabel, 'info');
        addTerminalLine('Timestamp: ' + new Date().toLocaleString(), 'info');
        addTerminalLine('', 'info');
        addTerminalLine('Connecting to BioTime server...', 'info');
        
        // Determine the endpoint URL
        var endpoint = '';
        switch(syncType) {
            case 'departments':
                endpoint = '<?php echo base_url("biometrics/syncDepartments"); ?>';
                break;
            case 'facilities':
                endpoint = '<?php echo base_url("biometrics/syncFacilities"); ?>';
                break;
            case 'jobs':
                endpoint = '<?php echo base_url("biometrics/syncJobs"); ?>';
                break;
            case 'employees':
                endpoint = '<?php echo base_url("biometrics/syncEmployees"); ?>';
                break;
        }
        
        // Log to console
        console.group('🔄 ' + syncTypeLabel + ' Sync Request');
        console.log('Sync Type:', syncType);
        console.log('Endpoint:', endpoint);
        console.log('Timestamp:', new Date().toISOString());
        console.groupEnd();
        
        // Make AJAX call
        $.ajax({
            url: endpoint,
            type: 'GET',
            dataType: 'json',
            timeout: 660000, // 11 minutes timeout for employee sync (server has 10 min, add buffer)
            beforeSend: function() {
                addTerminalLine('Sending sync request to: ' + endpoint, 'info');
            },
            success: function(response, textStatus, xhr) {
                // Check if response is actually JSON
                if (typeof response !== 'object' || response === null) {
                    addTerminalLine('⚠ Warning: Response is not valid JSON', 'warning');
                    addTerminalLine('Response Type: ' + typeof response, 'warning');
                    addTerminalLine('Raw Response: ' + String(response), 'warning');
                }
                addTerminalLine('✓ Sync request received', 'success');
                
                // Display response details
                if (response.status) {
                    var statusType = response.status === 'success' ? 'success' : 'error';
                    addTerminalLine('Status: ' + response.status.toUpperCase(), statusType);
                }
                if (response.message) {
                    addTerminalLine('Message: ' + response.message, response.status === 'success' ? 'success' : 'error');
                }
                if (response.timestamp) {
                    addTerminalLine('Completed: ' + response.timestamp, 'info');
                }
                if (response.type) {
                    addTerminalLine('Type: ' + response.type, 'info');
                }
                
                // Display full JSON
                addTerminalLine('', 'info');
                addTerminalLine('Full JSON Response:', 'info');
                addTerminalLine(JSON.stringify(response, null, 2), 'info');
                
                // Log to console
                console.group('✅ ' + syncTypeLabel + ' Sync Response');
                console.log('Status:', response.status);
                console.log('Message:', response.message);
                console.log('Type:', response.type);
                console.log('Full Response:', response);
                console.log('Full JSON:', JSON.stringify(response, null, 2));
                console.groupEnd();
                
                addTerminalLine('', 'info');
                if (response.status === 'success') {
                    addTerminalLine('=== ' + syncTypeLabel + ' Sync Completed Successfully ===', 'success');
                    addTerminalLine('The page will refresh in 3 seconds to show updated data...', 'info');
                    
                    // Refresh page after delay to show updated data
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                } else {
                    addTerminalLine('=== ' + syncTypeLabel + ' Sync Failed ===', 'error');
                }
            },
            error: function(xhr, status, error) {
                // Handle timeout
                if (status === 'timeout') {
                    addTerminalLine('⏱ Request Timeout: Sync is taking longer than expected', 'error');
                    addTerminalLine('This may be normal for large sync operations', 'warning');
                    addTerminalLine('Please check server logs for sync progress', 'info');
                }
                
                addTerminalLine('✗ Sync request failed', 'error');
                addTerminalLine('HTTP Status: ' + xhr.status + ' ' + (xhr.statusText || ''), 'error');
                addTerminalLine('Error Type: ' + status, 'error');
                addTerminalLine('Error Message: ' + (error || 'Unknown error'), 'error');
                
                // Check for different error types
                if (xhr.status === 0) {
                    addTerminalLine('Network Error: Unable to connect to server', 'error');
                    addTerminalLine('Check your internet connection and server status', 'error');
                } else if (xhr.status === 404) {
                    addTerminalLine('Endpoint Not Found: ' + endpoint, 'error');
                    addTerminalLine('Please check if the sync endpoint exists', 'error');
                } else if (xhr.status === 500) {
                    addTerminalLine('Server Error: Internal server error occurred', 'error');
                    addTerminalLine('Check server logs for detailed error information', 'error');
                } else if (xhr.status === 403) {
                    addTerminalLine('Access Forbidden: You may not have permission', 'error');
                } else if (xhr.status === 200) {
                    addTerminalLine('⚠ Warning: Got 200 OK but response is not valid JSON', 'warning');
                    addTerminalLine('The endpoint may be returning HTML or plain text instead of JSON', 'warning');
                }
                
                // Try to parse response
                if (xhr.responseText) {
                    addTerminalLine('', 'error');
                    addTerminalLine('Server Response:', 'error');
                    try {
                        var errorResponse = JSON.parse(xhr.responseText);
                        addTerminalLine(JSON.stringify(errorResponse, null, 2), 'error');
                    } catch(e) {
                        // If not JSON, show raw response (truncated if too long)
                        var responseText = xhr.responseText;
                        if (responseText.length > 500) {
                            responseText = responseText.substring(0, 500) + '... (truncated)';
                        }
                        addTerminalLine('Raw Response (not JSON):', 'error');
                        addTerminalLine(responseText, 'error');
                    }
                } else {
                    addTerminalLine('No response received from server', 'error');
                }
                
                // Show request details
                addTerminalLine('', 'error');
                addTerminalLine('Request Details:', 'error');
                addTerminalLine('  URL: ' + endpoint, 'error');
                addTerminalLine('  Method: GET', 'error');
                addTerminalLine('  Ready State: ' + xhr.readyState, 'error');
                
                // Log error to console with full details
                console.group('❌ ' + syncTypeLabel + ' Sync Error');
                console.error('HTTP Status:', xhr.status, xhr.statusText);
                console.error('Status:', status);
                console.error('Error:', error);
                console.error('Ready State:', xhr.readyState);
                console.error('Response Text:', xhr.responseText);
                console.error('Response Headers:', xhr.getAllResponseHeaders());
                console.error('Full XHR Object:', xhr);
                console.error('Endpoint:', endpoint);
                console.groupEnd();
            }
        });
    });

    // Terminal output functions
    function addTerminalLine(message, type = 'info') {
        var line = $('<div>').addClass('terminal-line').addClass(type).text(message);
        $('#terminal-output').append(line);
        $('#terminal').scrollTop($('#terminal')[0].scrollHeight);
        
        // Also log to console
        if (type === 'error') {
            console.error('[SYNC]', message);
        } else if (type === 'success') {
            console.log('[SYNC] ✓', message);
        } else {
            console.log('[SYNC]', message);
        }
    }
    
    function clearTerminal() {
        $('#terminal-output').empty();
        addTerminalLine('Terminal cleared. Ready for sync...', 'info');
    }
    
    // Clear terminal button
    $('#clearTerminal').on('click', function() {
        clearTerminal();
    });

    // Handle form submission
    $('#syncForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        var terminal_sn = $('#terminal_sn').val();
        var end_date = $('#end_date').val();
        var sync_type = $('#sync_type').val();
        var batch_size = $('#batch_size').val();
        
        // Show terminal modal
        $('#syncModal').modal('hide');
        $('#terminalModal').modal('show');
        
        // Clear and initialize terminal
        $('#terminal-output').empty();
        addTerminalLine('=== Sync Process Started ===', 'info');
        addTerminalLine('Terminal SN: ' + terminal_sn, 'info');
        addTerminalLine('End Date: ' + end_date, 'info');
        addTerminalLine('Sync Type: ' + sync_type, 'info');
        addTerminalLine('Batch Size: ' + batch_size, 'info');
        addTerminalLine('', 'info');
        addTerminalLine('Connecting to BioTime server...', 'info');
        
        // Log sync parameters to console
        console.group('🔄 Sync Request');
        console.log('Terminal SN:', terminal_sn);
        console.log('End Date:', end_date);
        console.log('Sync Type:', sync_type);
        console.log('Batch Size:', batch_size);
        console.log('Form Data:', formData);
        console.groupEnd();
        
        // Submit form via AJAX
        $.ajax({
            url: $(this).attr('action'),
            type: 'GET',
            data: formData,
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                // Track progress if possible
                return xhr;
            },
            beforeSend: function() {
                addTerminalLine('Sending sync request...', 'info');
            },
            success: function(response) {
                addTerminalLine('✓ Sync request received', 'success');
                
                // Parse response if it's a string
                var responseData = response;
                if (typeof response === 'string') {
                    try {
                        responseData = JSON.parse(response);
                    } catch(e) {
                        // If not JSON, display as is
                        addTerminalLine('Response: ' + response, 'info');
                    }
                }
                
                // Display formatted response
                if (typeof responseData === 'object' && responseData !== null) {
                    addTerminalLine('Response Details:', 'info');
                    if (responseData.status) {
                        addTerminalLine('  Status: ' + responseData.status, responseData.status === 'initiated' ? 'success' : 'info');
                    }
                    if (responseData.message) {
                        addTerminalLine('  Message: ' + responseData.message, 'info');
                    }
                    if (responseData.timestamp) {
                        addTerminalLine('  Timestamp: ' + responseData.timestamp, 'info');
                    }
                    if (responseData.parameters) {
                        addTerminalLine('  Parameters:', 'info');
                        for (var key in responseData.parameters) {
                            addTerminalLine('    ' + key + ': ' + responseData.parameters[key], 'info');
                        }
                    }
                    if (responseData.command) {
                        addTerminalLine('  Command: ' + responseData.command, 'info');
                    }
                    
                    // Display full JSON for debugging
                    addTerminalLine('', 'info');
                    addTerminalLine('Full JSON Response:', 'info');
                    addTerminalLine(JSON.stringify(responseData, null, 2), 'info');
                } else {
                    addTerminalLine('Response: ' + JSON.stringify(responseData), 'info');
                }
                
                // Log full response to console with detailed breakdown
                console.group('✅ Sync Response');
                console.log('Full Response Object:', responseData);
                console.log('Response Type:', typeof responseData);
                if (responseData.parameters) {
                    console.group('📋 Sync Parameters');
                    for (var key in responseData.parameters) {
                        console.log(key + ':', responseData.parameters[key]);
                    }
                    console.groupEnd();
                }
                if (responseData.command) {
                    console.log('🔧 Executed Command:', responseData.command);
                }
                console.log('📄 Full JSON:', JSON.stringify(responseData, null, 2));
                console.groupEnd();
                
                addTerminalLine('', 'info');
                addTerminalLine('=== Sync Process Initiated ===', 'success');
                addTerminalLine('Note: This is an asynchronous process. The sync is running in the background.', 'warning');
                addTerminalLine('Check server logs for detailed sync results.', 'info');
                
                // Refresh the table after a delay
                setTimeout(function() {
                    table.ajax.reload(null, false);
                }, 2000);
            },
            error: function(xhr, status, error) {
                addTerminalLine('✗ Sync request failed', 'error');
                addTerminalLine('Error: ' + error, 'error');
                addTerminalLine('Status: ' + status, 'error');
                if (xhr.responseText) {
                    addTerminalLine('Response: ' + xhr.responseText, 'error');
                }
                
                // Log error to console
                console.group('❌ Sync Error');
                console.error('Status:', status);
                console.error('Error:', error);
                console.error('Response:', xhr.responseText);
                console.error('XHR Object:', xhr);
                console.groupEnd();
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