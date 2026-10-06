<?php
$setting = isset($setting) ? $setting : new stdClass();
$setting_array = is_object($setting) ? (array) $setting : $setting;
$csrf_name = $this->security->get_csrf_token_name();
$counts = isset($sync_counts) && is_array($sync_counts) ? $sync_counts : ['pending' => 0, 'sent' => 0, 'failed' => 0, 'total' => 0];
$enabled = isset($setting_array['remote_clk_enabled']) ? (string) $setting_array['remote_clk_enabled'] : '0';
$url = isset($setting_array['remote_clk_url']) ? (string) $setting_array['remote_clk_url'] : '';
$key = isset($setting_array['remote_clk_private_key']) ? (string) $setting_array['remote_clk_private_key'] : '';
$lastId = isset($setting_array['remote_clk_last_id']) ? (string) $setting_array['remote_clk_last_id'] : '0';
$isOn = ($enabled === '1' || strtolower($enabled) === 'true' || strtolower($enabled) === 'yes');
?>
<style>
.svariables-page .page-header { background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 0.5rem; padding: 1.5rem; margin-bottom: 1.5rem; }
.svariables-page .page-title { font-size: 1.5rem; font-weight: 600; color: #212529; margin: 0 0 0.25rem 0; }
.svariables-page .page-subtitle { font-size: 0.875rem; color: #6c757d; margin: 0; }
.svariables-page .card { border: 1px solid #dee2e6; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
.svariables-page .card-header { background: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 1rem 1.25rem; font-weight: 600; font-size: 1rem; border-radius: 0.5rem 0.5rem 0 0; }
.svariables-page .form-group { margin-bottom: 1.25rem; }
.svariables-page .form-label { font-weight: 600; color: #495057; font-size: 0.875rem; margin-bottom: 0.375rem; }
.svariables-page .form-control { border-radius: 0.375rem; border: 1px solid #ced4da; }
.svariables-page .form-control:focus { border-color: #80bdff; box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.15); }
.svariables-page .btn-save { padding: 0.5rem 1.5rem; font-weight: 600; border-radius: 0.375rem; }
.svariables-page .stat-box { border: 1px solid #dee2e6; border-radius: 0.5rem; padding: 1rem; background: #fff; }
.svariables-page .stat-box .n { font-size: 1.5rem; font-weight: 700; line-height: 1.2; }
.svariables-page .stat-box .l { font-size: 0.8rem; color: #6c757d; }
</style>

<section class="content svariables-page">
  <div class="container-fluid">
    <div class="row mb-3">
      <div class="col-12">
        <div class="page-header">
          <h1 class="page-title"><i class="fas fa-exchange-alt text-primary mr-2"></i>Remote Clock Sync</h1>
          <p class="page-subtitle">Push attendance clocks to a peer Attend server. Each row is marked sent so it is not resent unless clock-in or clock-out changes.</p>
        </div>
      </div>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle mr-2"></i><?php echo $this->session->flashdata('success'); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle mr-2"></i><?php echo $this->session->flashdata('error'); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
    <?php endif; ?>

    <div class="row mb-3">
      <div class="col-md-3 mb-2">
        <div class="stat-box">
          <div class="n text-warning"><?php echo (int) $counts['pending']; ?></div>
          <div class="l">Pending (will send)</div>
        </div>
      </div>
      <div class="col-md-3 mb-2">
        <div class="stat-box">
          <div class="n text-success"><?php echo (int) $counts['sent']; ?></div>
          <div class="l">Sent (not resent)</div>
        </div>
      </div>
      <div class="col-md-3 mb-2">
        <div class="stat-box">
          <div class="n text-danger"><?php echo (int) $counts['failed']; ?></div>
          <div class="l">Failed (will retry)</div>
        </div>
      </div>
      <div class="col-md-3 mb-2">
        <div class="stat-box">
          <div class="n"><?php echo (int) $counts['total']; ?></div>
          <div class="l">Total clk_log rows</div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <i class="fas fa-sliders-h mr-2"></i>Peer connection
            <span class="badge <?php echo $isOn ? 'badge-success' : 'badge-secondary'; ?> float-right"><?php echo $isOn ? 'Enabled' : 'Disabled'; ?></span>
          </div>
          <div class="card-body">
            <p class="text-muted small">
              Use the peer base URL, e.g. <code>https://attend.health.go.ug</code>.
              Generate a one-time private key and paste the <strong>same key</strong> on the receiving server.
              The hourly job only sends rows with status pending or failed.
            </p>
            <?php echo form_open('svariables/remote_clk', array('class' => 'svariables-form', 'id' => 'remoteClkForm')); ?>
              <?php if (isset($setting_array['id'])): ?>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($setting_array['id'], ENT_QUOTES, 'UTF-8'); ?>">
              <?php endif; ?>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label" for="remote_clk_enabled">Enabled</label>
                    <select class="form-control" name="remote_clk_enabled" id="remote_clk_enabled">
                      <option value="0" <?php echo $isOn ? '' : 'selected'; ?>>No</option>
                      <option value="1" <?php echo $isOn ? 'selected' : ''; ?>>Yes</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label class="form-label" for="remote_clk_url">Peer Attend URL</label>
                    <input type="text" class="form-control" name="remote_clk_url" id="remote_clk_url"
                           value="<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>"
                           placeholder="https://attend.health.go.ug">
                  </div>
                  <div class="form-group">
                    <label class="form-label">Last successfully pushed id</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($lastId, ENT_QUOTES, 'UTF-8'); ?>" readonly>
                    <small class="form-text text-muted">Updated automatically by the job. Not used to skip rows; status is.</small>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label" for="remote_clk_private_key">One-time private key</label>
                    <div class="input-group">
                      <input type="password" class="form-control" name="remote_clk_private_key" id="remote_clk_private_key"
                             value="<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>"
                             autocomplete="new-password">
                      <div class="input-group-append">
                        <button type="button" class="btn btn-outline-secondary" id="toggleRemoteKey" title="Show key">
                          <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="genRemoteKey">Generate</button>
                      </div>
                    </div>
                    <small class="form-text text-muted">Same key on sender and receiver. Changing it will break decrypt until both sides match.</small>
                  </div>
                </div>
              </div>

              <hr class="my-4">
              <button type="submit" class="btn btn-primary btn-save" id="remoteClkSubmitBtn">
                <i class="fas fa-save mr-1"></i> Save
              </button>
            <?php echo form_close(); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
$(function() {
  var $form = $('#remoteClkForm');
  var $btn = $('#remoteClkSubmitBtn');
  if (!$form.length || !$btn.length) return;

  $('#toggleRemoteKey').on('click', function() {
    var $input = $('#remote_clk_private_key');
    var $icon = $(this).find('i');
    if ($input.attr('type') === 'password') {
      $input.attr('type', 'text');
      $icon.removeClass('fa-eye').addClass('fa-eye-slash');
    } else {
      $input.attr('type', 'password');
      $icon.removeClass('fa-eye-slash').addClass('fa-eye');
    }
  });

  $('#genRemoteKey').on('click', function() {
    var bytes = new Uint8Array(32);
    window.crypto.getRandomValues(bytes);
    var hex = Array.from(bytes).map(function(b) {
      return ('0' + b.toString(16)).slice(-2);
    }).join('');
    $('#remote_clk_private_key').val(hex).attr('type', 'text');
    $('#toggleRemoteKey').find('i').removeClass('fa-eye').addClass('fa-eye-slash');
  });

  function updateCsrfToken(name, hash) {
    if (!name || !hash) return;
    var $field = $form.find('input[name="' + name + '"]');
    if ($field.length) $field.val(hash);
  }

  $form.on('submit', function(e) {
    e.preventDefault();
    var origHtml = $btn.html();
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');
    $.ajax({
      url: '<?php echo base_url('svariables/remote_clk'); ?>',
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      contentType: false,
      processData: false,
      dataType: 'json',
      data: new FormData(this),
      success: function(res) {
        if (typeof res !== 'object') {
          try { res = JSON.parse(res); } catch (err) { res = {}; }
        }
        updateCsrfToken(res.csrf_name, res.csrf_hash);
        if (res.status === 'success') {
          $.notify(res.message || 'Saved.', 'success');
        } else {
          $.notify(res.message || 'Failed to save.', 'error');
        }
      },
      error: function(xhr) {
        var msg = 'Request failed. Please refresh the page and try again.';
        if (xhr.status === 403) {
          msg = 'Security token expired. Please refresh the page and try again.';
        } else if (xhr.responseJSON && xhr.responseJSON.message) {
          msg = xhr.responseJSON.message;
        }
        $.notify(msg, 'error');
        if (xhr.responseJSON) {
          updateCsrfToken(xhr.responseJSON.csrf_name, xhr.responseJSON.csrf_hash);
        }
      },
      complete: function() {
        $btn.prop('disabled', false).html(origHtml);
      }
    });
  });
});
</script>
