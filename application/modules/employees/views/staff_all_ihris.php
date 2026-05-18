<style>
/* All iHRIS Staff page */
.page-header { background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; }
.page-title { font-size: 1.5rem; font-weight: 600; color: #212529; margin-bottom: 0.25rem; }
.page-subtitle { font-size: 0.9rem; color: #6c757d; margin: 0; }
.card { border-radius: 8px; border: 1px solid #dee2e6; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
.card-header { background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 1rem 1.25rem; font-weight: 600; }
.table thead th { background-color: #f8f9fa; font-weight: 600; padding: 0.75rem; }
#staffTable .btn-mark-disabled, #staffTable .btn-mark-enabled { font-size: 0.7rem; padding: 0.2rem 0.4rem; }
.include-inactive-toggle { min-width: 3.5rem; font-size: 0.8rem; font-weight: 600; padding: 0.25rem 0.5rem; }
.filter-row .form-group { margin-bottom: 0.75rem; }
.filter-row label { font-weight: 600; font-size: 0.875rem; }
.filter-row .select2-container { width: 100% !important; max-width: 100%; }
.filter-row .select2-selection--single { min-height: 31px; }
</style>

<?php
$filter_options = (isset($filter_options) && is_array($filter_options)) ? $filter_options : [];
if (empty($filter_options['districts'])) {
    $CI =& get_instance();
    if (!isset($CI->empModel)) {
        $CI->load->model('employee_model', 'empModel');
    }
    $filter_options = $CI->empModel->get_all_ihris_filter_options(false);
}
$filter_districts = isset($filter_options['districts']) ? $filter_options['districts'] : [];
$filter_jobs = isset($filter_options['jobs']) ? $filter_options['jobs'] : [];
$filter_institution_types = isset($filter_options['institution_types']) ? $filter_options['institution_types'] : [];
$filter_facility_types = isset($filter_options['facility_types']) ? $filter_options['facility_types'] : [];
$filter_counts_label = count($filter_districts) . ' districts, ' . count($filter_jobs) . ' jobs';
?>
<section class="content">
  <div class="container-fluid">
    <div class="row mb-3">
      <div class="col-12">
        <div class="page-header">
          <h4 class="page-title"><i class="fas fa-users-cog text-info"></i> All iHRIS Staff</h4>
          <p class="page-subtitle">View and manage staff across all districts with filters (permission 15)</p>
          <?php if (count($filter_districts) === 0) { ?>
          <p class="text-warning small mb-0"><i class="fas fa-exclamation-triangle"></i> Filter lists could not be loaded from ihrisdata.</p>
          <?php } ?>
        </div>
      </div>
    </div>

    <div class="row mb-4">
      <div class="col-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-filter text-info mr-2"></i>Filters</span>
            <small class="text-muted" id="filterCountsLabel"><?php echo htmlspecialchars($filter_counts_label, ENT_QUOTES, 'UTF-8'); ?></small>
          </div>
          <div class="card-body">
            <form id="filterForm" class="row g-3">
              <div class="col-md-2">
                <label class="form-label">District</label>
                <select class="form-control form-control-sm ihris-filter-s2" id="filterDistrict" name="district">
                  <option value="">All</option>
                  <?php foreach ($filter_districts as $o) {
                      if (empty($o['value'])) { continue; }
                  ?>
                  <option value="<?php echo htmlspecialchars((string) $o['value'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars((string) $o['label'], ENT_QUOTES, 'UTF-8'); ?></option>
                  <?php } ?>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label">Facility</label>
                <select class="form-control form-control-sm ihris-filter-s2" id="filterFacility" name="facility">
                  <option value="">All (select district to narrow)</option>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label">Job</label>
                <select class="form-control form-control-sm ihris-filter-s2" id="filterJob" name="job">
                  <option value="">All</option>
                  <?php foreach ($filter_jobs as $o) {
                      if (empty($o['value'])) { continue; }
                  ?>
                  <option value="<?php echo htmlspecialchars((string) $o['value'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars((string) $o['label'], ENT_QUOTES, 'UTF-8'); ?></option>
                  <?php } ?>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label">Institution Type</label>
                <select class="form-control form-control-sm ihris-filter-s2" id="filterInstitutionType" name="institution_type">
                  <option value="">All</option>
                  <?php foreach ($filter_institution_types as $o) {
                      if (empty($o['value'])) { continue; }
                  ?>
                  <option value="<?php echo htmlspecialchars((string) $o['value'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars((string) $o['label'], ENT_QUOTES, 'UTF-8'); ?></option>
                  <?php } ?>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label">Facility Type</label>
                <select class="form-control form-control-sm ihris-filter-s2" id="filterFacilityType" name="facility_type">
                  <option value="">All</option>
                  <?php foreach ($filter_facility_types as $o) {
                      if (empty($o['value'])) { continue; }
                  ?>
                  <option value="<?php echo htmlspecialchars((string) $o['value'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars((string) $o['label'], ENT_QUOTES, 'UTF-8'); ?></option>
                  <?php } ?>
                </select>
              </div>
              <div class="col-md-2 d-flex align-items-end">
                <button type="button" id="resetFilters" class="btn btn-outline-secondary btn-sm">Reset</button>
              </div>
              <div class="col-12 mt-2">
                <label class="form-label">Search all fields</label>
                <div class="input-group input-group-sm">
                  <input type="text" class="form-control" id="globalSearch" placeholder="Name, ID, NIN, phone, email...">
                  <div class="input-group-append">
                    <button type="submit" class="btn btn-info">Search</button>
                  </div>
                </div>
              </div>
              <div class="col-12 mt-2 pt-2" style="border-top: 1px solid #dee2e6;">
                <input type="hidden" id="includeInactive" value="0">
                <span class="font-weight-bold mr-2">Include inactive (Former Staff)</span>
                <button type="button" id="includeInactiveToggle" class="btn btn-sm btn-secondary include-inactive-toggle" role="switch" aria-checked="false">
                  <span class="toggle-off">Off</span><span class="toggle-on d-none">On</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0"><i class="fas fa-table text-info mr-2"></i>Staff</h5>
            <span class="badge badge-info" id="showingInfo">—</span>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="staffTable" class="table table-hover mb-0" style="width:100%">
                <thead>
                  <tr>
                    <th class="text-center">#</th>
                    <th>Staff ID</th>
                    <th>NIN</th>
                    <th>Full Name</th>
                    <th>Gender</th>
                    <th>Birth Date</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>District</th>
                    <th>Facility</th>
                    <th>Department</th>
                    <th>Job</th>
                    <th>Terms</th>
                    <th>Card #</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Incharge modal (same as district) -->
<div class="modal fade" id="inchargeModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-user-plus mr-2"></i>Assign Incharge Role</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-4 text-center">
            <h6 class="staff-name mt-2"></h6>
            <p class="text-muted staff-job"></p>
            <p class="text-muted staff-facility"></p>
          </div>
          <div class="col-md-8">
            <form id="inchargeForm">
              <input type="hidden" name="ihris_pid">
              <input type="hidden" name="district_id">
              <input type="hidden" name="facility_id[]">
              <input type="hidden" name="department_id">
              <div class="form-group">
                <label>Full Name</label>
                <input type="text" class="form-control" name="name" readonly>
              </div>
              <div class="form-group">
                <label>Username (iHRIS ID)</label>
                <input type="text" class="form-control" name="username" readonly>
              </div>
              <div class="form-group">
                <label>Email</label>
                <input type="email" class="form-control" name="email">
              </div>
              <div class="form-group">
                <label>Password</label>
                <input type="text" class="form-control" name="password" readonly>
              </div>
              <input type="hidden" name="is_incharge" value="1">
              <button type="submit" class="btn btn-info">Assign Incharge</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
