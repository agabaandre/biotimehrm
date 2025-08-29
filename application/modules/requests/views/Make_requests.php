<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-3">
      <div class="col-12">
        <div class="page-header">
          <h1 class="page-title">
            <i class="fas fa-plus-circle text-info mr-2"></i>
            Submit New Request
          </h1>
          <p class="page-subtitle">Submit a new out-of-station request for approval</p>
        </div>
      </div>
    </div>

    <!-- Submit Request Form -->
    <div class="row">
      <div class="col-lg-8 col-md-12">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-edit text-success mr-2"></i>Request Details
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

            <form id="requestForm" method="post" enctype="multipart/form-data" autocomplete="off">
              <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
              
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="dateFrom" class="form-label">
                      <i class="fas fa-calendar text-info mr-1"></i>From Date
                    </label>
                    <div class="input-group">
                      <span class="input-group-text">
                        <i class="far fa-calendar-alt"></i>
                      </span>
                      <input type="date" class="form-control" id="dateFrom" name="dateFrom" 
                             value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <small class="form-text text-muted">Select the start date for your request</small>
                  </div>
                </div>
                
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="dateTo" class="form-label">
                      <i class="fas fa-calendar text-info mr-1"></i>To Date
                    </label>
                    <div class="input-group">
                      <span class="input-group-text">
                        <i class="far fa-calendar-alt"></i>
                      </span>
                      <input type="date" class="form-control" id="dateTo" name="dateTo" 
                             value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <small class="form-text text-muted">Select the end date for your request</small>
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <label for="reason_id" class="form-label">
                  <i class="fas fa-question-circle text-warning mr-1"></i>Reason
                </label>
                <select name="reason_id" id="reason_id" class="form-control select2" required>
                  <option value="" disabled selected>Select Out of Station Reason</option>
                  <?php foreach ($reasons as $reason) : ?>
                    <option value="<?php echo $reason->id; ?>"><?php echo $reason->reason; ?></option>
                  <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">Choose the reason for your out-of-station request</small>
              </div>

              <div class="mb-3">
                <label for="remarks" class="form-label">
                  <i class="fas fa-comment text-success mr-1"></i>Remarks
                </label>
                <textarea name="remarks" id="remarks" rows="4" class="form-control" 
                          placeholder="Please provide detailed information about your request..." required></textarea>
                <small class="form-text text-muted">Provide additional details to support your request</small>
              </div>

              <div class="mb-3">
                <label for="files" class="form-label">
                  <i class="fas fa-paperclip text-secondary mr-1"></i>Supporting Documents
                </label>
                <input type="file" name="files" id="files" class="form-control" 
                       accept=".pdf,.xlsx,.docx,.jpg,.png">
                <small class="form-text text-muted">Upload supporting documents (PDF, Excel, Word, Images)</small>
              </div>

              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-info btn-lg" id="submitBtn">
                  <i class="fas fa-paper-plane mr-2"></i>Submit Request
                </button>
                <button type="reset" class="btn btn-outline-secondary btn-lg">
                  <i class="fas fa-undo mr-2"></i>Reset Form
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Information Card -->
      <div class="col-lg-4 col-md-12">
        <div class="card bg-light">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-info-circle text-info mr-2"></i>Request Guidelines
            </h5>
          </div>
          <div class="card-body">
            <div class="alert alert-info">
              <h6><i class="fas fa-lightbulb mr-2"></i>Important Notes:</h6>
              <ul class="mb-0">
                <li>Requests cannot be made for past dates</li>
                <li>Ensure all required fields are completed</li>
                <li>Attach relevant supporting documents</li>
                <li>Requests are subject to approval</li>
                <li>You can track your request status</li>
              </ul>
            </div>
            
            <div class="alert alert-warning">
              <h6><i class="fas fa-clock mr-2"></i>Processing Time:</h6>
              <p class="mb-0">Requests are typically processed within 2-3 business days</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Request Summary Modal -->
<div class="modal fade" id="requestSummaryModal" tabindex="-1" aria-labelledby="requestSummaryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="requestSummaryModalLabel">
          <i class="fas fa-check-circle mr-2"></i>Request Submitted Successfully!
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <h6 class="text-info">Request Summary</h6>
            <table class="table table-borderless">
              <tr>
                <td><strong>Request ID:</strong></td>
                <td id="modalRequestId">-</td>
              </tr>
              <tr>
                <td><strong>From Date:</strong></td>
                <td id="modalDateFrom">-</td>
              </tr>
              <tr>
                <td><strong>To Date:</strong></td>
                <td id="modalDateTo">-</td>
              </tr>
              <tr>
                <td><strong>Reason:</strong></td>
                <td id="modalReason">-</td>
              </tr>
              <tr>
                <td><strong>Status:</strong></td>
                <td><span class="badge badge-warning">Pending</span></td>
              </tr>
            </table>
          </div>
          <div class="col-md-6">
            <h6 class="text-info">Next Steps</h6>
            <div class="alert alert-info">
              <ul class="mb-0">
                <li>Your request has been submitted</li>
                <li>You will receive notifications on status changes</li>
                <li>You can track progress in "My Requests"</li>
                <li>Approval typically takes 2-3 business days</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fas fa-times mr-2"></i>Close
        </button>
        <a href="<?php echo base_url('requests/viewMySubmittedRequests'); ?>" class="btn btn-info">
          <i class="fas fa-list mr-2"></i>View My Requests
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-body text-center">
        <div class="spinner-border text-info" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 mb-0">Submitting your request...</p>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    // Set minimum date for date inputs
    var today = new Date().toISOString().split('T')[0];
    $('#dateFrom').attr('min', today);
    $('#dateTo').attr('min', today);

    // Update min date for dateTo when dateFrom changes
    $('#dateFrom').on('change', function() {
        $('#dateTo').attr('min', $(this).val());
        if ($('#dateTo').val() && $('#dateTo').val() < $(this).val()) {
            $('#dateTo').val($(this).val());
        }
    });

    // Form submission
    $('#requestForm').on('submit', function(e) {
        e.preventDefault();
        
        // Show loading modal
        $('#loadingModal').modal('show');
        
        // Disable submit button
        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Submitting...');
        
        // Create FormData object
        var formData = new FormData(this);
        
        $.ajax({
            url: '<?php echo base_url("requests/saveRequest"); ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                $('#loadingModal').modal('hide');
                
                if (response.status === 'success') {
                    // Populate modal with request details
                    $('#modalRequestId').text(response.request.entry_id);
                    $('#modalDateFrom').text(moment(response.request.dateFrom).format('MMM DD, YYYY'));
                    $('#modalDateTo').text(moment(response.request.dateTo).format('MMM DD, YYYY'));
                    $('#modalReason').text(response.request.reason);
                    
                    // Show success modal
                    $('#requestSummaryModal').modal('show');
                    
                    // Reset form
                    $('#requestForm')[0].reset();
                    $('.select2').val(null).trigger('change');
                } else {
                    // Show error message
                    Swal.fire({
                        icon: 'error',
                        title: 'Submission Failed',
                        text: response.message,
                        confirmButtonColor: '#d33'
                    });
                }
            },
            error: function(xhr, status, error) {
                $('#loadingModal').modal('hide');
                
                Swal.fire({
                    icon: 'error',
                    title: 'Submission Failed',
                    text: 'An error occurred while submitting your request. Please try again.',
                    confirmButtonColor: '#d33'
                });
            },
            complete: function() {
                // Re-enable submit button
                $('#submitBtn').prop('disabled', false).html('<i class="fas fa-paper-plane mr-2"></i>Submit Request');
            }
        });
    });

    // Reset form when modal is closed
    $('#requestSummaryModal').on('hidden.bs.modal', function() {
        $('#requestForm')[0].reset();
        $('.select2').val(null).trigger('change');
    });
});
</script>