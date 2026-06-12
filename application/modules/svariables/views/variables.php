<?php
$setting = isset($setting) ? $setting : new stdClass();
$setting_array = is_object($setting) ? (array) $setting : $setting;
$csrf_name = $this->security->get_csrf_token_name();

if (!function_exists('svariables_render_field')) {
  function svariables_render_field($key, $value)
  {
    $label = ucwords(str_replace('_', ' ', $key));
    $value_esc = is_string($value) ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $value;
    $is_long = is_string($value) && strlen($value) > 80;
    $is_password = stripos($key, 'password') !== false;
    $field_id = 'var_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $key);
    $name_attr = htmlspecialchars($key, ENT_QUOTES, 'UTF-8');
    $label_esc = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    ?>
    <div class="form-group">
      <label class="form-label" for="<?php echo $field_id; ?>"><?php echo $label_esc; ?></label>
      <?php if ($is_password): ?>
        <div class="input-group sv-password-group">
          <input type="password"
                 class="form-control sv-password-input"
                 name="<?php echo $name_attr; ?>"
                 id="<?php echo $field_id; ?>"
                 value="<?php echo $value_esc; ?>"
                 placeholder="<?php echo $label_esc; ?>"
                 autocomplete="new-password">
          <div class="input-group-append">
            <button type="button" class="btn btn-outline-secondary sv-toggle-pw" data-target="#<?php echo $field_id; ?>" title="Show password" aria-label="Show password">
              <i class="fas fa-eye"></i>
            </button>
          </div>
        </div>
      <?php elseif ($is_long): ?>
        <textarea class="form-control" name="<?php echo $name_attr; ?>" id="<?php echo $field_id; ?>" rows="3" placeholder="<?php echo $label_esc; ?>"><?php echo $value_esc; ?></textarea>
      <?php else: ?>
        <input type="text" class="form-control" name="<?php echo $name_attr; ?>" id="<?php echo $field_id; ?>" value="<?php echo $value_esc; ?>" placeholder="<?php echo $label_esc; ?>">
      <?php endif; ?>
    </div>
    <?php
  }
}

// Collect visible fields (exclude id) and split into two columns
$visible_fields = array();
foreach ($setting_array as $key => $value) {
  if ($key !== 'id') {
    $visible_fields[$key] = $value;
  }
}
$total = count($visible_fields);
$half = (int) ceil($total / 2);
$col1 = array_slice($visible_fields, 0, $half, true);
$col2 = array_slice($visible_fields, $half, null, true);
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
.svariables-page .alert { border-radius: 0.375rem; }
.svariables-page .sv-password-group .btn { border-color: #ced4da; }
</style>

<section class="content svariables-page">
  <div class="container-fluid">
    <div class="row mb-3">
      <div class="col-12">
        <div class="page-header">
          <h1 class="page-title"><i class="fas fa-cog text-primary mr-2"></i>Constants &amp; Variables</h1>
          <p class="page-subtitle">Configure system-wide settings and integration parameters. Changes take effect after saving.</p>
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

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <i class="fas fa-sliders-h mr-2"></i>Settings
          </div>
          <div class="card-body">
            <?php echo form_open('svariables/index', array('class' => 'svariables-form', 'id' => 'svariablesForm')); ?>
              <?php if (isset($setting_array['id'])): ?>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($setting_array['id'], ENT_QUOTES, 'UTF-8'); ?>">
              <?php endif; ?>

              <div class="row">
                <div class="col-md-6">
                  <?php foreach ($col1 as $key => $value): ?>
                    <?php svariables_render_field($key, $value); ?>
                  <?php endforeach; ?>
                </div>
                <div class="col-md-6">
                  <?php foreach ($col2 as $key => $value): ?>
                    <?php svariables_render_field($key, $value); ?>
                  <?php endforeach; ?>
                </div>
              </div>

              <hr class="my-4">
              <div class="d-flex align-items-center">
                <button type="submit" class="btn btn-primary btn-save" id="svariablesSubmitBtn">
                  <i class="fas fa-save mr-1"></i> Save changes
                </button>
                <span class="text-muted small ml-3">Click Save to apply updates.</span>
              </div>
            <?php echo form_close(); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
$(function() {
  var $form = $('#svariablesForm');
  var $btn = $('#svariablesSubmitBtn');
  var csrfName = <?php echo json_encode($csrf_name); ?>;
  if (!$form.length || !$btn.length) return;

  $(document).on('click', '.sv-toggle-pw', function() {
    var $input = $($(this).data('target'));
    var $icon = $(this).find('i');
    if ($input.attr('type') === 'password') {
      $input.attr('type', 'text');
      $icon.removeClass('fa-eye').addClass('fa-eye-slash');
      $(this).attr('title', 'Hide password').attr('aria-label', 'Hide password');
    } else {
      $input.attr('type', 'password');
      $icon.removeClass('fa-eye-slash').addClass('fa-eye');
      $(this).attr('title', 'Show password').attr('aria-label', 'Show password');
    }
  });

  function updateCsrfToken(name, hash) {
    if (!name || !hash) return;
    var $field = $form.find('input[name="' + name + '"]');
    if ($field.length) {
      $field.val(hash);
    }
  }

  $form.on('submit', function(e) {
    e.preventDefault();
    var origHtml = $btn.html();
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

    $.ajax({
      url: '<?php echo base_url('svariables/index'); ?>',
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
          $.notify(res.message || 'Settings updated successfully!', 'success');
        } else {
          $.notify(res.message || 'Failed to update settings.', 'error');
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
