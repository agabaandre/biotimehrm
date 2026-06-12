<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$profile = isset($profile) ? $profile : null;
$facilities = isset($facilities) && is_array($facilities) ? $facilities : [];
$attendance = isset($attendance) && is_array($attendance) ? $attendance : null;
$is_education = function_exists('is_education_deployment') && is_education_deployment();
$facility_label = entity_label('facility');
$flash = $this->session->flashdata('msg');
if ($flash && (
  stripos($flash, 'Login Failed') !== false
  || stripos($flash, 'Wrong credentials') !== false
  || stripos($flash, 'Unauthorized access') !== false
  || stripos($flash, 'First time access') !== false
)) {
  $flash = '';
}

$photo_web_base = isset($photo_web_base) ? (string) $photo_web_base : 'assets/images/sm/';

if (!$profile) {
    echo '<div class="alert alert-danger">Unable to load your profile.</div>';
    return;
}

$initials = '';
foreach (preg_split('/\s+/', trim((string) $profile->name), 3) as $part) {
    if ($part !== '') {
        $initials .= strtoupper(substr($part, 0, 1));
    }
}
$initials = substr($initials, 0, 2) ?: '?';

$photo_file = isset($profile->photo) ? trim((string) $profile->photo) : '';
$photo_url = '';
if ($photo_file !== '') {
  $candidates = [
    rtrim($photo_web_base, '/') . '/',
    'assets/images/sm/',
    'uploads/profile/',
  ];
  foreach ($candidates as $base) {
    $disk = rtrim(FCPATH, '/\\') . '/' . trim($base, '/') . '/' . $photo_file;
    if (is_file($disk)) {
      $photo_url = base_url($base . rawurlencode($photo_file));
      break;
    }
  }
  if ($photo_url === '') {
    $photo_url = base_url(rtrim($photo_web_base, '/') . '/' . rawurlencode($photo_file));
  }
}
?>

<style>
.profile-page {
  --pp-teal: #005662;
  --pp-mint: #20c198;
  --pp-soft: #e8f6f3;
  --pp-border: #e9ecef;
  --pp-shadow: 0 4px 24px rgba(0, 86, 98, 0.08);
  --pp-radius: 14px;
}
.profile-page .pp-hero {
  background: linear-gradient(135deg, var(--pp-teal) 0%, var(--pp-mint) 100%);
  border-radius: var(--pp-radius);
  color: #fff;
  padding: 2rem;
  margin-bottom: 1.5rem;
  box-shadow: var(--pp-shadow);
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 1.5rem;
}
.profile-page .pp-avatar-wrap {
  position: relative;
  flex-shrink: 0;
}
.profile-page .pp-avatar {
  width: 110px;
  height: 110px;
  border-radius: 50%;
  border: 4px solid rgba(255,255,255,0.35);
  object-fit: cover;
  background: rgba(255,255,255,0.15);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  font-weight: 700;
  color: #fff;
}
.profile-page .pp-avatar img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
}
.profile-page .pp-avatar-btn {
  position: absolute;
  bottom: 0;
  right: 0;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  border: 2px solid #fff;
  background: var(--pp-teal);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}
.profile-page .pp-avatar-btn:hover {
  background: #00424d;
}
.profile-page .pp-hero h1 {
  font-size: 1.6rem;
  font-weight: 700;
  margin: 0 0 0.25rem;
}
.profile-page .pp-hero .pp-meta {
  opacity: 0.92;
  font-size: 0.92rem;
  margin: 0;
}
.profile-page .pp-badge {
  display: inline-block;
  background: rgba(255,255,255,0.2);
  padding: 0.25rem 0.65rem;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 600;
  margin-top: 0.5rem;
}
.profile-page .pp-card {
  background: #fff;
  border: 1px solid var(--pp-border);
  border-radius: var(--pp-radius);
  box-shadow: var(--pp-shadow);
  margin-bottom: 1.25rem;
  overflow: hidden;
}
.profile-page .pp-card-head {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--pp-border);
  font-weight: 600;
  font-size: 1rem;
  color: var(--pp-teal);
}
.profile-page .pp-card-head i {
  margin-right: 0.45rem;
  color: var(--pp-mint);
}
.profile-page .pp-card-body {
  padding: 1.25rem;
}
.profile-page .pp-stat-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
  gap: 0.75rem;
}
.profile-page .pp-stat {
  text-align: center;
  padding: 0.85rem 0.5rem;
  border-radius: 10px;
  background: #f8fafb;
  border: 1px solid var(--pp-border);
}
.profile-page .pp-stat strong {
  display: block;
  font-size: 1.35rem;
  color: var(--pp-teal);
}
.profile-page .pp-stat span {
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: #6c757d;
}
.profile-page .pp-stat.present strong { color: #198754; }
.profile-page .pp-stat.absent strong { color: #dc3545; }
.profile-page .pp-facility-list {
  list-style: none;
  padding: 0;
  margin: 0;
}
.profile-page .pp-facility-list li {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  padding: 0.65rem 0;
  border-bottom: 1px solid var(--pp-border);
}
.profile-page .pp-facility-list li:last-child {
  border-bottom: none;
}
.profile-page .pp-facility-icon {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: var(--pp-soft);
  color: var(--pp-teal);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.profile-page .pp-primary-tag {
  font-size: 0.65rem;
  background: var(--pp-mint);
  color: #fff;
  padding: 0.1rem 0.45rem;
  border-radius: 999px;
  margin-left: 0.35rem;
  text-transform: uppercase;
  font-weight: 700;
}
.profile-page .pp-empty {
  color: #6c757d;
  font-size: 0.9rem;
  text-align: center;
  padding: 1rem 0;
}
.profile-page .pp-form label {
  font-size: 0.82rem;
  font-weight: 600;
  color: #495057;
}
.profile-page .pp-alert-slot:empty {
  display: none;
}
</style>

<div class="profile-page">
  <?php if ($flash) { echo $flash; } ?>
  <div class="pp-alert-slot" id="profile-alert"></div>

  <div class="pp-hero">
    <div class="pp-avatar-wrap">
      <div class="pp-avatar" id="pp-avatar">
        <?php if ($photo_url !== '') { ?>
          <img src="<?php echo htmlspecialchars($photo_url, ENT_QUOTES, 'UTF-8'); ?>" alt="Profile photo">
        <?php } else { ?>
          <?php echo htmlspecialchars($initials, ENT_QUOTES, 'UTF-8'); ?>
        <?php } ?>
      </div>
      <label class="pp-avatar-btn mb-0" for="pp-photo-input" title="Change photo">
        <i class="fas fa-camera"></i>
      </label>
      <input type="file" id="pp-photo-input" accept="image/jpeg,image/png,image/gif,image/webp" class="d-none">
    </div>
    <div class="flex-grow-1">
      <h1><?php echo htmlspecialchars((string) $profile->name, ENT_QUOTES, 'UTF-8'); ?></h1>
      <p class="pp-meta">
        <?php echo htmlspecialchars((string) $profile->username, ENT_QUOTES, 'UTF-8'); ?>
        <?php if (!empty($profile->email)) { ?>
          · <?php echo htmlspecialchars((string) $profile->email, ENT_QUOTES, 'UTF-8'); ?>
        <?php } ?>
      </p>
      <span class="pp-badge"><?php echo htmlspecialchars((string) ($profile->group_name ?? $profile->role ?? 'User'), ENT_QUOTES, 'UTF-8'); ?></span>
      <?php if (!empty($profile->district)) { ?>
        <span class="pp-badge ml-1"><?php echo htmlspecialchars((string) $profile->district, ENT_QUOTES, 'UTF-8'); ?></span>
      <?php } ?>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-8">
      <?php if ($attendance !== null) { ?>
      <div class="pp-card">
        <div class="pp-card-head"><i class="fas fa-calendar-check"></i> Attendance — <?php echo htmlspecialchars($attendance['label'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="pp-card-body">
          <div class="pp-stat-grid mb-3">
            <div class="pp-stat present">
              <strong><?php echo (int) $attendance['present']; ?></strong>
              <span>Present</span>
            </div>
            <div class="pp-stat">
              <strong><?php echo (int) $attendance['offduty']; ?></strong>
              <span>Off duty</span>
            </div>
            <div class="pp-stat">
              <strong><?php echo (int) $attendance['leave']; ?></strong>
              <span>Leave</span>
            </div>
            <div class="pp-stat">
              <strong><?php echo (int) $attendance['request']; ?></strong>
              <span>Official</span>
            </div>
            <div class="pp-stat absent">
              <strong><?php echo (int) $attendance['absent']; ?></strong>
              <span>Absent</span>
            </div>
            <div class="pp-stat">
              <strong><?php echo (float) $attendance['attendance_rate']; ?>%</strong>
              <span>Present rate</span>
            </div>
          </div>
          <p class="text-muted small mb-0">Based on recorded duty days in the system for your linked employee record.</p>
        </div>
      </div>
      <?php } ?>

      <div class="pp-card">
        <div class="pp-card-head"><i class="fas fa-building"></i> Assigned <?php echo htmlspecialchars(entity_label('facility', true), ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="pp-card-body">
          <?php if (empty($facilities)) { ?>
            <p class="pp-empty mb-0">No <?php echo strtolower(htmlspecialchars(entity_label('facility', true), ENT_QUOTES, 'UTF-8')); ?> assigned to your account.</p>
          <?php } else { ?>
            <ul class="pp-facility-list">
              <?php foreach ($facilities as $fac) { ?>
                <li>
                  <span class="pp-facility-icon"><i class="fas fa-school"></i></span>
                  <div>
                    <strong><?php echo htmlspecialchars((string) $fac->facility, ENT_QUOTES, 'UTF-8'); ?></strong>
                    <div class="text-muted small"><?php echo htmlspecialchars((string) $fac->facility_id, ENT_QUOTES, 'UTF-8'); ?></div>
                  </div>
                  <?php if (!empty($fac->is_primary)) { ?>
                    <span class="pp-primary-tag">Primary</span>
                  <?php } ?>
                </li>
              <?php } ?>
            </ul>
          <?php } ?>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="pp-card">
        <div class="pp-card-head"><i class="fas fa-id-card"></i> Account details</div>
        <div class="pp-card-body pp-form">
          <form id="profile-details-form">
            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
            <div class="form-group">
              <label>Display name</label>
              <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars((string) $profile->name, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="form-group">
              <label>Email</label>
              <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars((string) ($profile->email ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-group mb-0">
              <label>Username</label>
              <input type="text" class="form-control" value="<?php echo htmlspecialchars((string) $profile->username, ENT_QUOTES, 'UTF-8'); ?>" readonly disabled>
            </div>
            <button type="submit" class="btn btn-primary btn-block mt-3"><i class="fas fa-save mr-1"></i> Save details</button>
          </form>
        </div>
      </div>

      <div class="pp-card" id="change-password">
        <div class="pp-card-head"><i class="fas fa-key"></i> Change password</div>
        <div class="pp-card-body pp-form">
          <form id="profile-password-form">
            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
            <div class="form-group">
              <label>Current password</label>
              <input type="password" name="oldpass" class="form-control" autocomplete="current-password" required>
            </div>
            <div class="form-group">
              <label>New password</label>
              <input type="password" name="newpass" class="form-control" autocomplete="new-password" required minlength="8">
            </div>
            <div class="form-group">
              <label>Confirm new password</label>
              <input type="password" name="newpass_confirm" class="form-control" autocomplete="new-password" required minlength="8">
            </div>
            <button type="submit" class="btn btn-outline-primary btn-block"><i class="fas fa-lock mr-1"></i> Update password</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function() {
  var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
  var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';

  function showAlert(msg, type) {
    var cls = type === 'error' ? 'danger' : (type === 'success' ? 'success' : 'info');
    $('#profile-alert').html('<div class="alert alert-' + cls + ' alert-dismissible fade show">' + msg + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
    if (typeof $.notify === 'function') {
      $.notify(msg, type === 'error' ? 'error' : 'success');
    }
  }

  $('#pp-photo-input').on('change', function() {
    var file = this.files && this.files[0];
    if (!file) {
      return;
    }
    var fd = new FormData();
    fd.append('photo', file);
    fd.append(csrfName, csrfHash);

    showAlert('<i class="fas fa-spinner fa-spin"></i> Uploading photo…', 'info');

    $.ajax({
      url: '<?php echo base_url('auth/saveProfilePhoto'); ?>',
      method: 'POST',
      data: fd,
      processData: false,
      contentType: false,
      dataType: 'json'
    }).done(function(res) {
      if (res.success && res.photo_url) {
        $('#pp-avatar').html('<img src="' + res.photo_url + '?t=' + Date.now() + '" alt="Profile photo">');
        $('.user-avatar').attr('src', res.photo_url + '?t=' + Date.now());
        showAlert(res.message || 'Photo updated', 'success');
      } else {
        showAlert(res.message || 'Upload failed', 'error');
      }
    }).fail(function(xhr) {
      showAlert(xhr.status === 403 ? 'Session expired. Refresh the page.' : 'Photo upload failed.', 'error');
    });

    $(this).val('');
  });

  $('#profile-details-form').on('submit', function(e) {
    e.preventDefault();
    $.post('<?php echo base_url('auth/saveProfileDetails'); ?>', $(this).serialize())
      .done(function(msg) {
        showAlert(msg, msg.indexOf('updated') !== -1 ? 'success' : 'info');
      })
      .fail(function() {
        showAlert('Could not save profile details.', 'error');
      });
  });

  $('#profile-password-form').on('submit', function(e) {
    e.preventDefault();
    var $f = $(this);
    var np = $f.find('[name=newpass]').val();
    var npc = $f.find('[name=newpass_confirm]').val();
    if (np !== npc) {
      showAlert('New passwords do not match.', 'error');
      return;
    }
    $.post('<?php echo base_url('auth/changePass'); ?>', $f.serialize())
      .done(function(msg) {
        showAlert(msg, msg.indexOf('Successful') !== -1 ? 'success' : 'error');
        if (msg.indexOf('Successful') !== -1) {
          $f[0].reset();
        }
      })
      .fail(function() {
        showAlert('Password change failed.', 'error');
      });
  });

  if (window.location.hash === '#change-password') {
    setTimeout(function() {
      $('html, body').animate({ scrollTop: $('#change-password').offset().top - 80 }, 400);
    }, 300);
  }
})();
</script>
