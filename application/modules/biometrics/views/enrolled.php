<?php
$base = base_url();
$csrf_name = $this->security->get_csrf_token_name();
$csrf_hash = $this->security->get_csrf_hash();
?>
<div class="card">
  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <section class="col-lg-12">
          <h5 style="margin-top:10px;"><?php echo htmlspecialchars($uptitle); ?></h5>
          <p class="text-muted">Enrolled BioTime users at this facility (server-side search &amp; paging).</p>
          <div class="table-responsive" style="margin-top:10px;">
            <table id="enrolledTable" class="table table-bordered table-striped" style="width:100%;">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Staff iHRIS ID</th>
                  <th>Name</th>
                  <th>Facility</th>
                  <th>Device</th>
                  <th>Job</th>
                  <th>Card / Emp Code</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </section>
      </div>
    </div>
  </section>
</div>

<script type="text/javascript">
(function ($) {
  var baseUrl = '<?php echo addslashes($base); ?>';
  var csrfName = '<?php echo addslashes($csrf_name); ?>';
  var csrfHash = '<?php echo addslashes($csrf_hash); ?>';

  $(function () {
    if (typeof $.fn.DataTable !== 'function') {
      console.error('DataTables not loaded');
      return;
    }
    $('#enrolledTable').DataTable({
      processing: true,
      serverSide: true,
      searching: true,
      ordering: true,
      order: [[2, 'asc']],
      pageLength: 25,
      lengthMenu: [[10, 25, 50, 100, 200], [10, 25, 50, 100, 200]],
      ajax: {
        url: baseUrl + 'biometrics/enrolledAjax',
        type: 'POST',
        data: function (d) {
          d[csrfName] = csrfHash;
        },
        error: function (xhr, error, thrown) {
          console.error('enrolledAjax error', { xhr: xhr, error: error, thrown: thrown, body: xhr.responseText });
        }
      },
      columns: [
        { data: 0, orderable: false },
        { data: 1 },
        { data: 2 },
        { data: 3 },
        { data: 4 },
        { data: 5 },
        { data: 6 },
        { data: 7, orderable: false }
      ],
      language: { processing: '<i class="fa fa-spinner fa-spin"></i> Loading...' }
    });
  });
})(jQuery);
</script>
