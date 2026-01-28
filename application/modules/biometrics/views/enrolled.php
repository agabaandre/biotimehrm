<!-- Main content -->
<div class="card">
  <section class="content">
    <div class="container-fluid">
      <!-- Main row -->

      <div class="row">

        <section class="col-lg-12 ">
          <h5 style="margin-top:10px;"><?php echo $uptitle ?></h5>

          <div class="table-responsive">
            <table id="enrolledTable" class="table table-bordered table-striped" style="width:100%">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Staff iHRIS ID</th>
                  <th>Name</th>
                  <th>Facility</th>
                  <th>Device</th>
                  <th>Job</th>
                  <th>Card Number</th>
                  <th>Status</th>
                </tr>
              </thead>
            </table>
          </div>

        </section>
      </div>
      <!-- /.row (main row) -->
    </div><!-- /.container-fluid -->
  </section>
</div>
<!-- /.content -->

<script>
$(document).ready(function() {
    var baseUrl = '<?php echo base_url(); ?>';
    
    // Initialize DataTable with server-side processing
    var table = $('#enrolledTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: baseUrl + 'biometrics/getEnrolledAjax',
            type: 'POST',
            data: function(d) {
                d['<?php echo $this->security->get_csrf_token_name(); ?>'] = '<?php echo $this->security->get_csrf_hash(); ?>';
            }
        },
        columns: [
            { data: 0, orderable: false }, // #
            { data: 1, orderable: true }, // Staff iHRIS ID
            { data: 2, orderable: true }, // Name
            { data: 3, orderable: true }, // Facility
            { data: 4, orderable: true }, // Device
            { data: 5, orderable: true }, // Job
            { data: 6, orderable: true }, // Card Number
            { data: 7, orderable: false } // Status
        ],
        order: [[2, 'asc']], // Sort by name ascending by default
        pageLength: 20,
        lengthMenu: [[10, 20, 25, 50, 100], [10, 20, 25, 50, 100]],
        responsive: true,
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        language: {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>',
            search: "Search:",
            lengthMenu: "Show _MENU_ entries per page",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "No entries found",
            infoFiltered: "(filtered from _MAX_ total entries)"
        }
    });
});
</script>
