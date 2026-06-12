<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$users = isset($users) && is_array($users) ? $users : [];
$usergroups = isset($usergroups) ? $usergroups : [];
$districts = isset($districts) ? $districts : [];
$links = isset($links) ? $links : '';
$total_rows = isset($total_rows) ? (int) $total_rows : count($users);
$is_education = function_exists('is_education_deployment') && is_education_deployment();
$facility_label = entity_label('facility');
$no = isset($row_offset) ? (int) $row_offset + 1 : 1;
?>
<span class="d-none um-total-rows" data-count="<?php echo (int) $total_rows; ?>"></span>

<div class="um-table-wrap">
  <div class="table-responsive">
    <table class="table um-table mb-0">
      <thead>
        <tr>
          <th style="width:3%;">#</th>
          <th>User</th>
          <th>Group</th>
          <th>District</th>
          <th><?php echo htmlspecialchars($facility_label, ENT_QUOTES, 'UTF-8'); ?></th>
          <?php if (!$is_education) { ?><th>Department</th><?php } ?>
          <th>Status</th>
          <th style="width:14%;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($users)) : ?>
          <tr>
            <td colspan="<?php echo $is_education ? 7 : 8; ?>" class="text-center um-empty">
              <i class="fas fa-users-slash um-empty-icon"></i>
              <p class="mb-0">No users found. Add a user or adjust your search filters.</p>
            </td>
          </tr>
        <?php else : ?>
          <?php foreach ($users as $user) :
            $initials = '';
            foreach (preg_split('/\s+/', trim((string) $user->name), 3) as $part) {
              if ($part !== '') {
                $initials .= strtoupper(substr($part, 0, 1));
              }
            }
            $initials = substr($initials, 0, 2) ?: '?';
            $is_active = (int) ($user->status ?? 0) === 1;
          ?>
            <tr>
              <td data-label="#"><?php echo $no; ?></td>
              <td data-label="User">
                <div class="um-user-cell">
                  <span class="um-avatar" aria-hidden="true"><?php echo htmlspecialchars($initials, ENT_QUOTES, 'UTF-8'); ?></span>
                  <div>
                    <strong class="um-user-name"><?php echo htmlspecialchars((string) $user->name, ENT_QUOTES, 'UTF-8'); ?></strong>
                    <span class="um-user-meta"><?php echo htmlspecialchars((string) $user->username, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php if (!empty($user->email)) { ?>
                      <span class="um-user-email"><?php echo htmlspecialchars((string) $user->email, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php } ?>
                  </div>
                </div>
              </td>
              <td data-label="Group">
                <span class="um-badge um-badge-group"><?php echo htmlspecialchars((string) ($user->group_name ?? '—'), ENT_QUOTES, 'UTF-8'); ?></span>
              </td>
              <td data-label="District"><?php echo htmlspecialchars((string) ($user->district ?? '—'), ENT_QUOTES, 'UTF-8'); ?></td>
              <td data-label="<?php echo htmlspecialchars($facility_label, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars((string) ($user->facility ?? '—'), ENT_QUOTES, 'UTF-8'); ?></td>
              <?php if (!$is_education) { ?>
                <td data-label="Department"><?php echo htmlspecialchars((string) ($user->department ?? '—'), ENT_QUOTES, 'UTF-8'); ?></td>
              <?php } ?>
              <td data-label="Status">
                <?php if ($is_active) { ?>
                  <span class="um-badge um-badge-active"><i class="fas fa-circle"></i> Active</span>
                <?php } else { ?>
                  <span class="um-badge um-badge-blocked"><i class="fas fa-circle"></i> Blocked</span>
                <?php } ?>
              </td>
              <td data-label="Actions">
                <div class="um-actions">
                  <button type="button" class="btn btn-sm um-btn-edit" data-toggle="modal" data-target="#user<?php echo (int) $user->user_id; ?>" title="Edit">
                    <i class="fas fa-pen"></i>
                  </button>
                  <?php if ($is_active) { ?>
                    <button type="button" class="btn btn-sm um-btn-block" data-toggle="modal" data-target="#block<?php echo (int) $user->user_id; ?>" title="Block">
                      <i class="fas fa-ban"></i>
                    </button>
                  <?php } else { ?>
                    <button type="button" class="btn btn-sm um-btn-unblock" data-toggle="modal" data-target="#unblock<?php echo (int) $user->user_id; ?>" title="Activate">
                      <i class="fas fa-check"></i>
                    </button>
                  <?php } ?>
                  <button type="button" class="btn btn-sm um-btn-reset" data-toggle="modal" data-target="#reset<?php echo (int) $user->user_id; ?>" title="Reset password">
                    <i class="fas fa-key"></i>
                  </button>
                </div>
              </td>
            </tr>
          <?php
            $no++;
          endforeach;
        endif; ?>
      </tbody>
    </table>
  </div>

  <?php if ($links !== '') { ?>
    <div class="um-pagination"><?php echo $links; ?></div>
  <?php } ?>
</div>

<div id="users-modals">
  <?php
  $no = isset($row_offset) ? (int) $row_offset + 1 : 1;
  foreach ($users as $user) {
    include(dirname(__DIR__) . '/user_details_modal.php');
    include(dirname(__DIR__) . '/confirm_reset.php');
    include(dirname(__DIR__) . '/confirm_block.php');
    if ((int) ($user->status ?? 0) === 0) {
      include(dirname(__DIR__) . '/confirm_unblock.php');
    }
    $no++;
  }
  ?>
</div>
