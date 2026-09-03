<!-- Main content -->
<div class="card">
  <section class="content">
    <div class="container-fluid">
      <div class="row" style="min-height:550px">
        <section class="col-lg-12">
          <h5 style="margin-top:10px;"><?php echo $uptitle ?></h5>
          <p class="text-muted">Staff whose iHRIS facility no longer matches BioTime enrollment. Use Force Update to sync now; the background job also runs every 5 minutes.</p>

          <?php
          $staffs = Modules::run('biometrics/get_users_needing_update');
          if (!is_array($staffs) && !($staffs instanceof Traversable)) {
              $staffs = [];
          }
          ?>
          <table id="needsUpdateTable" class="table table-bordered table-striped mytable">
            <thead>
              <tr>
                <th>#</th>
                <th>Staff iHRIS ID</th>
                <th>Name</th>
                <th>Job</th>
                <th>Card Number</th>
                <th>iHRIS Facility</th>
                <th>BioTime Facility ID</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php $i = 1;
              foreach ($staffs as $staff) {
                  $card = $staff->card_number ?? $staff->emp_code ?? '';
                  $name = trim(($staff->surname ?? '') . ' ' . ($staff->firstname ?? ''));
                  if ($name === '') {
                      $name = trim(($staff->fullname ?? '') . ' ' . ($staff->othername ?? ''));
                  }
              ?>
                <tr id="row-update-<?php echo htmlspecialchars($card, ENT_QUOTES, 'UTF-8'); ?>">
                  <td data-label="No"><?php echo $i++; ?></td>
                  <td data-label="Staff iHRIS ID"><?php echo str_replace('person|', '', $staff->ihris_pid ?? ''); ?></td>
                  <td data-label="NAME"><?php echo htmlspecialchars($name); ?></td>
                  <td data-label="JOB"><?php echo htmlspecialchars($staff->job ?? ''); ?></td>
                  <td data-label="CARD NUMBER"><?php echo htmlspecialchars($card); ?></td>
                  <td data-label="iHRIS Facility"><?php echo htmlspecialchars($staff->new_fname ?? $staff->facility ?? ''); ?></td>
                  <td data-label="BioTime Facility"><?php echo htmlspecialchars($staff->biotime_fac_id ?? ''); ?></td>
                  <td data-label="Actions">
                    <button type="button"
                            class="btn btn-sm btn-warning force-update-btn"
                            data-card="<?php echo htmlspecialchars($card, ENT_QUOTES, 'UTF-8'); ?>">
                      <i class="fas fa-sync"></i> Force Update
                    </button>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </section>
      </div>
    </div>
  </section>
</div>

<script>
(function ($) {
  $(document).on('click', '.force-update-btn', function () {
    var $btn = $(this);
    var card = $btn.data('card');
    if (!card) {
      return;
    }
    if (!window.confirm('Force BioTime update for card ' + card + '?')) {
      return;
    }
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
    $.ajax({
      url: '<?php echo base_url("biometrics/forceUpdate"); ?>',
      type: 'POST',
      dataType: 'json',
      data: { card_number: card },
      success: function (res) {
        if (res && res.status === 'success') {
          alert(res.message || 'Update successful');
          $btn.closest('tr').fadeOut(400, function () { $(this).remove(); });
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
