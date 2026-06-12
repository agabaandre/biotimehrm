<!-- Default modal Size -->
<div class="modal fade" id="user<?php echo (int) $user->user_id; ?>">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content um-modal">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold">Edit user</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body pt-2">
        <p class="text-muted small mb-3"><?php echo htmlspecialchars((string) $user->name, ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars((string) $user->username, ENT_QUOTES, 'UTF-8'); ?></p>
        <span class="status d-block mb-2"></span>

        <form class="update_user" enctype="multipart/form-data" method="post" action="<?php echo base_url('auth/updateUser'); ?>">
          <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">

          <div class="form-group">
            <label class="small font-weight-bold">Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars((string) $user->name, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required>
          </div>
          <div class="form-group">
            <label class="small font-weight-bold">Username</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars((string) $user->username, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" readonly>
          </div>
          <div class="form-group">
            <label class="small font-weight-bold">Email</label>
            <input type="text" name="email" value="<?php echo htmlspecialchars((string) $user->email, ENT_QUOTES, 'UTF-8'); ?>" class="form-control">
          </div>
          <div class="form-group">
            <label class="small font-weight-bold">User group</label>
            <select name="role" class="form-control role select2" style="width:100%;" required>
              <?php foreach ($usergroups as $usergroup) { ?>
                <option value="<?php echo (int) $usergroup->group_id; ?>" <?php echo ((int) $user->role === (int) $usergroup->group_id) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($usergroup->group_name, ENT_QUOTES, 'UTF-8'); ?>
                </option>
              <?php } ?>
            </select>
          </div>
          <div class="form-group">
            <label class="small font-weight-bold">District</label>
            <select onchange="getuserFacs($(this).val());" name="district_id" class="form-control select2 userdistrict" style="width:100%;">
              <?php foreach ($districts as $district) { ?>
                <option value="<?php echo htmlspecialchars($district->district_id, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($user->district == $district->district) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($district->district, ENT_QUOTES, 'UTF-8'); ?>
                </option>
              <?php } ?>
            </select>
          </div>
          <div class="form-group">
            <label class="small font-weight-bold"><?php echo htmlspecialchars(entity_label('facility'), ENT_QUOTES, 'UTF-8'); ?></label>
            <select onchange="getuserDeps($(this).val());" name="facility_id[]" class="form-control select2 userfacility" style="width:100%;" multiple>
              <option value="<?php echo htmlspecialchars($user->facility_id . '_' . $user->facility, ENT_QUOTES, 'UTF-8'); ?>" selected>
                <?php echo htmlspecialchars((string) $user->facility, ENT_QUOTES, 'UTF-8'); ?>
              </option>
            </select>
          </div>
          <?php if (!function_exists('is_education_deployment') || !is_education_deployment()) { ?>
          <div class="form-group">
            <label class="small font-weight-bold">Department</label>
            <select name="department_id" class="form-control select2 sdepartment" style="width:100%;">
              <option value="<?php echo htmlspecialchars((string) ($user->department_id ?? $user->department ?? ''), ENT_QUOTES, 'UTF-8'); ?>" selected>
                <?php echo htmlspecialchars((string) ($user->department ?? $user->department_id ?? ''), ENT_QUOTES, 'UTF-8'); ?>
              </option>
            </select>
          </div>
          <?php } else { ?>
          <input type="hidden" name="department_id" value="<?php echo htmlspecialchars((string) ($user->department_id ?? $user->department ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
          <?php } ?>

          <input type="hidden" name="user_id" value="<?php echo (int) $user->user_id; ?>">

          <div class="d-flex justify-content-end gap-2 mt-3">
            <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
