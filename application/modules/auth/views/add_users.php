<?php
defined('BASEPATH') OR exit('No direct script access allowed');

try {
    $usergroups = Modules::run('auth/getUserGroups') ?: [];
} catch (Exception $e) {
    $usergroups = [];
}

try {
    $districts = Modules::run('auth/getDistricts') ?: [];
} catch (Exception $e) {
    $districts = [];
}

try {
    $variables = Modules::run('svariables/getSettings') ?: (object) ['default_password' => 'rKET2XW5Xvnp2ds'];
} catch (Exception $e) {
    $variables = (object) ['default_password' => 'rKET2XW5Xvnp2ds'];
}

$users = isset($users) && is_array($users) ? $users : [];
$links = isset($links) ? $links : '';
$total_rows = isset($total_rows) ? (int) $total_rows : count($users);
$is_education = function_exists('is_education_deployment') && is_education_deployment();
$facility_label = entity_label('facility');
$default_password = isset($variables->default_password) ? $variables->default_password : '';
$search_status = $this->input->post('status');
if ($search_status === null || $search_status === '') {
    $search_status = '1';
}
$search_key = $this->input->post('search_key') ?: '';
?>

<style>
.user-mgmt {
  --um-teal: #005662;
  --um-teal-dark: #00424d;
  --um-mint: #20c198;
  --um-primary: #005662;
  --um-primary-soft: #e8f6f3;
  --um-success: #198754;
  --um-danger: #dc3545;
  --um-muted: #6c757d;
  --um-border: #e9ecef;
  --um-shadow: 0 4px 20px rgba(0, 86, 98, 0.12);
  --um-radius: 12px;
}
.user-mgmt .um-hero {
  background: linear-gradient(135deg, var(--um-teal) 0%, var(--um-mint) 100%);
  border-radius: var(--um-radius);
  color: #fff;
  padding: 1.5rem 1.75rem;
  margin-bottom: 1.25rem;
  box-shadow: var(--um-shadow);
}
.user-mgmt .um-hero h2 {
  font-size: 1.35rem;
  font-weight: 600;
  margin: 0 0 0.35rem;
}
.user-mgmt .um-hero p {
  margin: 0;
  opacity: 0.9;
  font-size: 0.92rem;
}
.user-mgmt .um-stat {
  background: rgba(255,255,255,0.15);
  border-radius: 10px;
  padding: 0.65rem 1rem;
  text-align: center;
  min-width: 90px;
}
.user-mgmt .um-stat strong {
  display: block;
  font-size: 1.4rem;
  line-height: 1.2;
}
.user-mgmt .um-stat span {
  font-size: 0.75rem;
  opacity: 0.9;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.user-mgmt .um-card {
  border: 1px solid var(--um-border);
  border-radius: var(--um-radius);
  box-shadow: var(--um-shadow);
  background: #fff;
  margin-bottom: 1.25rem;
}
.user-mgmt .um-card-header {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--um-border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 0.75rem;
}
.user-mgmt .um-card-header h3 {
  margin: 0;
  font-size: 1.05rem;
  font-weight: 600;
  color: #212529;
}
.user-mgmt .um-card-header h3 i {
  color: var(--um-primary);
  margin-right: 0.4rem;
}
.user-mgmt .um-card-body {
  padding: 1.25rem;
}
.user-mgmt .um-search {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  align-items: flex-end;
}
.user-mgmt .um-search .form-group {
  margin-bottom: 0;
  flex: 1 1 180px;
}
.user-mgmt .um-search label {
  font-size: 0.78rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  color: var(--um-muted);
  margin-bottom: 0.25rem;
}
.user-mgmt .um-table-wrap {
  position: relative;
}
.user-mgmt .um-table thead th {
  background: #f8fafc;
  border-bottom: 2px solid var(--um-border);
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: var(--um-muted);
  white-space: nowrap;
}
.user-mgmt .um-table tbody tr {
  transition: background 0.15s ease;
}
.user-mgmt .um-table tbody tr:hover {
  background: #f0faf8;
}
.user-mgmt .um-user-cell {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}
.user-mgmt .um-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: var(--um-primary-soft);
  color: var(--um-primary);
  font-weight: 700;
  font-size: 0.85rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.user-mgmt .um-user-name {
  display: block;
  font-size: 0.92rem;
  color: #212529;
}
.user-mgmt .um-user-meta {
  display: block;
  font-size: 0.8rem;
  color: var(--um-muted);
}
.user-mgmt .um-user-email {
  display: block;
  font-size: 0.75rem;
  color: #adb5bd;
}
.user-mgmt .um-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.25rem 0.55rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
}
.user-mgmt .um-badge-group {
  background: var(--um-primary-soft);
  color: var(--um-teal);
}
.user-mgmt .um-badge-active {
  background: #d1e7dd;
  color: var(--um-success);
}
.user-mgmt .um-badge-active i {
  font-size: 0.45rem;
}
.user-mgmt .um-badge-blocked {
  background: #f8d7da;
  color: var(--um-danger);
}
.user-mgmt .um-badge-blocked i {
  font-size: 0.45rem;
}
.user-mgmt .um-actions {
  display: flex;
  gap: 0.35rem;
}
.user-mgmt .um-actions .btn {
  width: 32px;
  height: 32px;
  padding: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
}
.user-mgmt .um-btn-edit {
  background: var(--um-primary-soft);
  color: var(--um-primary);
  border: none;
}
.user-mgmt .um-btn-block {
  background: #fff3cd;
  color: #856404;
  border: none;
}
.user-mgmt .um-btn-unblock {
  background: #d1e7dd;
  color: var(--um-success);
  border: none;
}
.user-mgmt .um-btn-reset {
  background: #f1f3f5;
  color: #495057;
  border: none;
}
.user-mgmt .um-empty {
  padding: 2.5rem 1rem !important;
  color: var(--um-muted);
}
.user-mgmt .um-empty-icon {
  font-size: 2rem;
  opacity: 0.35;
  display: block;
  margin-bottom: 0.5rem;
}
.user-mgmt .um-pagination {
  padding: 0.75rem 1rem;
  border-top: 1px solid var(--um-border);
}
.user-mgmt .um-form-card {
  position: sticky;
  top: 1rem;
}
.user-mgmt .um-form-card .form-group label {
  font-size: 0.82rem;
  font-weight: 600;
  color: #495057;
}
.user-mgmt .um-form-actions {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}
.user-mgmt .um-form-actions .btn-primary,
.user-mgmt .btn-primary {
  background: var(--um-teal);
  border-color: var(--um-teal);
  border-radius: 8px;
  font-weight: 600;
}
.user-mgmt .um-form-actions .btn-primary:hover,
.user-mgmt .btn-primary:hover,
.user-mgmt .um-form-actions .btn-primary:focus,
.user-mgmt .btn-primary:focus {
  background: var(--um-teal-dark);
  border-color: var(--um-teal-dark);
  box-shadow: 0 0 0 0.2rem rgba(0, 86, 98, 0.25);
}
.user-mgmt .um-form-actions .btn-primary {
  border-radius: 8px;
  font-weight: 600;
}
.user-mgmt .pagination .page-item.active .page-link,
.user-mgmt .pagination .page-item.active span.page-link {
  background-color: var(--um-teal);
  border-color: var(--um-teal);
  color: #fff;
}
.user-mgmt .pagination .page-link {
  color: var(--um-teal);
}
.user-mgmt .pagination .page-link:hover {
  color: var(--um-teal-dark);
}
.user-mgmt .um-loading .text-primary,
.user-mgmt .fa-spinner.text-primary {
  color: var(--um-teal) !important;
}
.user-mgmt .um-form-actions .btn-light {
  border-radius: 8px;
}
.user-mgmt .um-hint {
  font-size: 0.78rem;
  color: var(--um-muted);
  margin-top: 0.75rem;
  padding: 0.65rem 0.75rem;
  background: #f8f9fa;
  border-radius: 8px;
}
.user-mgmt .um-loading {
  position: absolute;
  inset: 0;
  background: rgba(255,255,255,0.75);
  display: none;
  align-items: center;
  justify-content: center;
  z-index: 5;
  border-radius: 0 0 var(--um-radius) var(--um-radius);
}
.user-mgmt .um-loading.is-active {
  display: flex;
}
.user-mgmt .um-status {
  min-height: 1.25rem;
  font-size: 0.85rem;
}
@media (max-width: 991px) {
  .user-mgmt .um-form-card {
    position: static;
  }
}
</style>

<div class="user-mgmt">
  <div class="um-hero d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
      <h2><i class="fas fa-users-cog mr-2"></i>User Management</h2>
      <p>Manage system accounts, roles, and <?php echo strtolower(htmlspecialchars($facility_label, ENT_QUOTES, 'UTF-8')); ?> access.</p>
    </div>
    <div class="d-flex gap-2">
      <div class="um-stat">
        <strong id="um-total-count"><?php echo (int) $total_rows; ?></strong>
        <span>Total</span>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-8 order-lg-1 order-2">
      <div class="um-card">
        <div class="um-card-header">
          <h3><i class="fas fa-list"></i>Users</h3>
        </div>
        <div class="um-card-body pb-0">
          <form id="um-search-form" class="um-search mb-3" method="post" action="<?php echo base_url('auth/users'); ?>">
            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
            <div class="form-group">
              <label for="um-search-key">Search</label>
              <input type="text" id="um-search-key" name="search_key" class="form-control" placeholder="Name or username…" value="<?php echo htmlspecialchars($search_key, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-group" style="flex: 0 1 160px;">
              <label for="um-search-status">Status</label>
              <select id="um-search-status" name="status" class="form-control">
                <option value="1" <?php echo $search_status === '1' ? 'selected' : ''; ?>>Active</option>
                <option value="0" <?php echo $search_status === '0' ? 'selected' : ''; ?>>Blocked / New</option>
              </select>
            </div>
            <div class="form-group" style="flex: 0 0 auto;">
              <label>&nbsp;</label>
              <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search mr-1"></i> Search</button>
            </div>
          </form>
        </div>

        <div id="users-list-container" style="position:relative;">
          <div class="um-loading" id="users-list-loading"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i></div>
          <?php
          $this->load->view('partials/users_list', [
            'users'      => $users,
            'links'      => $links,
            'total_rows' => $total_rows,
            'row_offset' => ($this->uri->segment(3)) ? (int) $this->uri->segment(3) : 0,
            'usergroups' => $usergroups,
            'districts'  => $districts,
          ]);
          ?>
        </div>
      </div>
    </div>

    <div class="col-lg-4 order-lg-2 order-1">
      <div class="um-card um-form-card">
        <div class="um-card-header">
          <h3><i class="fas fa-user-plus"></i>Add User</h3>
        </div>
        <div class="um-card-body">
          <p class="text-muted small mb-3">For users not already in iHRIS Manage.</p>
          <form class="user_form" method="post" action="<?php echo base_url('auth/addUser'); ?>" enctype="multipart/form-data">
            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">

            <div class="form-group">
              <label>Full name</label>
              <input type="text" name="name" autocomplete="off" class="form-control" placeholder="Full name" required>
            </div>
            <div class="form-group">
              <label>Username</label>
              <input type="text" name="username" autocomplete="off" class="form-control" placeholder="Username" required>
            </div>
            <div class="form-group">
              <label>Email</label>
              <input type="email" name="email" class="form-control" placeholder="email@example.com" required>
            </div>
            <div class="form-group">
              <label>User group</label>
              <select name="role" class="role form-control select2" style="width:100%;" required>
                <option value="" disabled selected>Select group</option>
                <?php foreach ($usergroups as $usergroup) { ?>
                  <option value="<?php echo (int) $usergroup->group_id; ?>"><?php echo htmlspecialchars($usergroup->group_name, ENT_QUOTES, 'UTF-8'); ?></option>
                <?php } ?>
              </select>
            </div>
            <div class="form-group">
              <label>District</label>
              <select onchange="getuserFacs($(this).val());" name="district_id" class="form-control select2 userdistrict" style="width:100%;" required>
                <option value="" disabled selected>Select district</option>
                <?php foreach ($districts as $district) { ?>
                  <option value="<?php echo htmlspecialchars($district->district_id, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($district->district, ENT_QUOTES, 'UTF-8'); ?></option>
                <?php } ?>
              </select>
            </div>
            <div class="form-group">
              <label><?php echo htmlspecialchars($facility_label, ENT_QUOTES, 'UTF-8'); ?></label>
              <select id="facility" onchange="getuserDeps($(this).val());" name="facility_id[]" class="form-control select2 userfacility" style="width:100%;" multiple required></select>
            </div>
            <?php if (!$is_education) { ?>
            <div class="form-group">
              <label>Department</label>
              <select id="department" name="department_id" class="form-control select2 userdepartment" style="width:100%;">
                <option value="" disabled selected>Department</option>
              </select>
            </div>
            <?php } else { ?>
            <input type="hidden" name="department_id" value="">
            <?php } ?>
            <input type="hidden" name="password" value="<?php echo htmlspecialchars($default_password, ENT_QUOTES, 'UTF-8'); ?>">

            <div class="um-status status mb-2"></div>
            <div class="um-form-actions">
              <button type="submit" class="btn btn-primary flex-fill"><i class="fas fa-save mr-1"></i> Create user</button>
              <button type="reset" class="btn btn-light clear">Reset</button>
            </div>
            <div class="um-hint">
              <i class="fas fa-info-circle mr-1"></i>
              Default password is applied automatically. User should change it on first login.
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function() {
  var listUrl = '<?php echo base_url('auth/usersListFragment'); ?>';
  var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
  var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';

  function parseJsonResponse(raw) {
    if (typeof raw !== 'string') {
      return raw;
    }
    try {
      return JSON.parse(raw);
    } catch (e) {
      return { success: raw.indexOf('Added') !== -1 || raw.indexOf('updated') !== -1, message: raw };
    }
  }

  function notifyMsg(msg, type) {
    if (typeof $.notify === 'function') {
      $.notify(msg, type || 'info');
    }
  }

  window.reloadUsersList = function(page) {
    var $container = $('#users-list-container');
    var $loading = $('#users-list-loading');
    var params = {
      search_key: $('#um-search-key').val() || '',
      status: $('#um-search-status').val() || '1',
      page: (page !== undefined && page !== null) ? page : 0
    };
    params[csrfName] = csrfHash;

    $loading.addClass('is-active');
    return $.ajax({
      url: listUrl,
      method: 'GET',
      data: params,
      cache: false
    }).done(function(html) {
      $container.html('<div class="um-loading is-active" id="users-list-loading"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i></div>' + html);
      var cnt = $('#users-list-container .um-total-rows').data('count');
      if (cnt !== undefined) {
        $('#um-total-count').text(cnt);
      }
      if (typeof $.fn.select2 === 'function') {
        $('#users-list-container .select2').select2({ width: '100%' });
      }
    }).fail(function() {
      notifyMsg('Could not refresh user list. Please reload the page.', 'error');
    }).always(function() {
      $('#users-list-loading').removeClass('is-active');
    });
  };

  function resetAddUserForm($form) {
    $form[0].reset();
    $form.find('.select2').val(null).trigger('change');
    $form.find('.userfacility').empty();
    <?php if (!$is_education) { ?>
    $form.find('.userdepartment').html('<option value="" disabled selected>Department</option>');
    <?php } ?>
  }

  function bindUserListEvents() {
    $(document).off('click.umPage', '#users-list-container .um-pagination a, #users-list-container .pagination a');
    $(document).on('click.umPage', '#users-list-container .um-pagination a, #users-list-container .pagination a', function(e) {
      var href = $(this).attr('href');
      if (!href || href === '#') {
        return;
      }
      e.preventDefault();
      var match = href.match(/[?&]page=(\d+)/) || href.match(/\/usersListFragment\/(\d+)/);
      var page = match ? parseInt(match[1], 10) : 0;
      reloadUsersList(page);
    });
  }

  $(document).ready(function() {
    bindUserListEvents();

    $('#um-search-form').on('submit', function(e) {
      e.preventDefault();
      reloadUsersList(0);
    });

    $('.user_form').on('submit', function(e) {
      e.preventDefault();
      var $form = $(this);
      var $status = $form.find('.status');
      $status.html('<span class="text-muted"><i class="fas fa-spinner fa-spin"></i> Saving…</span>');

      $.ajax({
        url: '<?php echo base_url('auth/addUser'); ?>',
        method: 'POST',
        data: $form.serialize(),
        dataType: 'text'
      }).done(function(res) {
        var parsed = parseJsonResponse(res);
        var ok = parsed.success === true;
        var msg = parsed.message || (ok ? 'User created' : 'Could not create user');
        $status.html(ok ? '<span class="text-success">' + msg + '</span>' : '<span class="text-danger">' + msg + '</span>');
        notifyMsg(msg, ok ? 'success' : 'error');
        if (ok) {
          resetAddUserForm($form);
          reloadUsersList(0).done(function() {
            $('html, body').animate({ scrollTop: $('#users-list-container').offset().top - 80 }, 400);
          });
        }
      }).fail(function(xhr) {
        var msg = xhr.status === 403 ? 'Session expired. Refresh the page.' : 'Failed to create user.';
        $status.html('<span class="text-danger">' + msg + '</span>');
        notifyMsg(msg, 'error');
      });
    });

    $(document).on('submit', '.update_user', function(e) {
      e.preventDefault();
      var $form = $(this);
      var $status = $form.find('.status');
      $status.html('<span class="text-muted"><i class="fas fa-spinner fa-spin"></i> Updating…</span>');
      var formData = new FormData(this);
      formData.append(csrfName, csrfHash);

      $.ajax({
        url: '<?php echo base_url('auth/updateUser'); ?>',
        method: 'POST',
        contentType: false,
        processData: false,
        data: formData
      }).done(function(result) {
        var parsed = parseJsonResponse(result);
        var msg = parsed.message || result;
        var ok = parsed.success !== false && (String(msg).indexOf('updated') !== -1 || String(msg).indexOf('Updated') !== -1);
        notifyMsg(msg, ok ? 'success' : 'info');
        $status.html('');
        if (ok) {
          $form.closest('.modal').modal('hide');
          reloadUsersList();
        }
      }).fail(function(xhr) {
        var msg = xhr.status === 403 ? 'Update blocked. Refresh the page.' : 'Update failed.';
        notifyMsg(msg, 'error');
        $status.html('');
      });
    });

    function handleActionForm(selector, url) {
      $(document).on('submit', selector, function(e) {
        e.preventDefault();
        var $form = $(this);
        var data = $form.serialize() + '&' + csrfName + '=' + encodeURIComponent(csrfHash);
        $.post(url, data).done(function(result) {
          notifyMsg(result, 'info');
          $form.closest('.modal').modal('hide');
          reloadUsersList();
        }).fail(function() {
          notifyMsg('Action failed. Refresh the page.', 'error');
        });
      });
    }

    handleActionForm('.reset', '<?php echo base_url('auth/resetPass'); ?>');
    handleActionForm('.block', '<?php echo base_url('auth/blockUser'); ?>');
    handleActionForm('.unblock', '<?php echo base_url('auth/unblockUser'); ?>');
  });

  window.getuserFacs = function(val) {
    $.get('<?php echo base_url('departments/get_facilities'); ?>', { dist_data: val }, function(data) {
      $('.userfacility').html(data).trigger('change');
    });
  };

  window.getDeps = function(val) {
    <?php if ($is_education) { ?>return;<?php } ?>
    $.get('<?php echo base_url('departments/get_departments'); ?>', { fac_data: val }, function(data) {
      $('.sdepartment, .userdepartment').html(data);
    });
  };

  window.getuserDeps = function(val) {
    getDeps(val);
  };
})();
</script>
