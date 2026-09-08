<?php
$base = base_url();
$csrf_name = $this->security->get_csrf_token_name();
$csrf_hash = $this->security->get_csrf_hash();
?>
<div class="card">
  <section class="content">
    <div class="container-fluid">
      <div class="row" style="min-height:550px">
        <section class="col-lg-12">
          <h5 style="margin-top:10px;"><?php echo htmlspecialchars($uptitle); ?></h5>
          <div class="alert alert-info" style="margin-top:10px;">
            <strong>Needs update criteria</strong>
            <ul class="mb-0" style="margin-top:6px;">
              <!-- <li>Staff must already exist in <code>biotime_enrollment</code> (matched by resolved emp code: numeric card, bare person id, or UCMB <code>4253</code>+id).</li>
              <li>Flagged when <strong>iHRIS <code>facility_id</code> ≠ BioTime <code>biotime_fac_id</code></strong> (facility transfer / reassignment).</li>
              <li>List is limited to your logged-in facility: staff whose <em>current</em> iHRIS facility is yours, or whose BioTime enrollment is still under yours.</li>
              <li>Job/department are pushed on Force Update / cron, but are <strong>not</strong> used as mismatch triggers (enrollment table has no job field).</li> -->
            </ul>
          </div>
          <p class="text-muted">Force Update syncs now; background transfer job also runs every 5 minutes.</p>
          <div class="table-responsive" style="margin-top:10px;">
            <table id="needsUpdateTable" class="table table-bordered table-striped" style="width:100%;">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Staff iHRIS ID</th>
                  <th>Name</th>
                  <th>Job</th>
                  <th>Card Number</th>
                  <th>BioTime Emp Code</th>
                  <th>iHRIS Facility</th>
                  <th>BioTime Facility ID</th>
                  <th>Reason</th>
                  <th>Actions</th>
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
  var table;

  $(function () {
    if (typeof $.fn.DataTable !== 'function') {
      console.error('DataTables not loaded');
      return;
    }
    table = $('#needsUpdateTable').DataTable({
      processing: true,
      serverSide: true,
      searching: true,
      ordering: true,
      order: [[2, 'asc']],
      pageLength: 25,
      lengthMenu: [[10, 25, 50, 100, 200], [10, 25, 50, 100, 200]],
      ajax: {
        url: baseUrl + 'biometrics/needsUpdateAjax',
        type: 'POST',
        data: function (d) {
          d[csrfName] = csrfHash;
        },
        error: function (xhr, error, thrown) {
          console.error('needsUpdateAjax error', { xhr: xhr, error: error, thrown: thrown, body: xhr.responseText });
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
        { data: 7 },
        { data: 8, orderable: false },
        { data: 9, orderable: false }
      ],
      language: { processing: '<i class="fa fa-spinner fa-spin"></i> Loading...' }
    });
  });

  $(document).on('click', '.force-update-btn', function () {
    var $btn = $(this);
    var card = $btn.data('card') || '';
    var ihris = $btn.data('ihris') || '';
    if (!card && !ihris) {
      return;
    }
    if (!window.confirm('Force BioTime update for ' + (card || ihris) + '?')) {
      return;
    }
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
    var payload = {};
    payload[csrfName] = csrfHash;
    payload.card_number = card;
    payload.ihris_pid = ihris;
    $.ajax({
      url: baseUrl + 'biometrics/forceUpdate',
      type: 'POST',
      dataType: 'json',
      data: payload,
      success: function (res) {
        if (res && res.status === 'success') {
          alert(res.message || 'Update successful');
          if (table) {
            table.ajax.reload(null, false);
          }
        } else {
          alert((res && res.message) ? res.message : 'Update failed');
          $btn.prop('disabled', false).html('<i class="fas fa-sync"></i> Force Update');
        }
      },
      error: function (xhr) {
        alert('Update request failed: ' + (xhr.responseText || xhr.statusText));
        $btn.prop('disabled', false).html('<i class="fas fa-sync"></i> Force Update');
      }
    });
  });
})(jQuery);
</script>
