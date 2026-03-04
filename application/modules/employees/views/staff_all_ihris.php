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
</style>

<?php
$filter_options = isset($filter_options) && is_array($filter_options) ? $filter_options : ['districts' => [], 'facilities' => [], 'jobs' => [], 'institution_types' => [], 'facility_types' => []];
?>
<section class="content">
  <div class="container-fluid">
    <div class="row mb-3">
      <div class="col-12">
        <div class="page-header">
          <h4 class="page-title"><i class="fas fa-users-cog text-info"></i> All iHRIS Staff</h4>
          <p class="page-subtitle">View and manage staff across all districts with filters (permission 15)</p>
        </div>
      </div>
    </div>

    <div class="row mb-4">
      <div class="col-12">
        <div class="card">
          <div class="card-header"><i class="fas fa-filter text-info mr-2"></i>Filters</div>
          <div class="card-body">
            <form id="filterForm" class="row g-3">
              <div class="col-md-2">
                <label class="form-label">District</label>
                <select class="form-control form-control-sm select2-filter" id="filterDistrict" name="district">
                  <option value="">All</option>
                  <?php if (!empty($filter_options['districts'])) { foreach ($filter_options['districts'] as $o) { ?>
                  <option value="<?php echo htmlspecialchars($o['value']); ?>"><?php echo htmlspecialchars($o['label']); ?></option>
                  <?php } } ?>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label">Facility</label>
                <select class="form-control form-control-sm select2-filter" id="filterFacility" name="facility">
                  <option value="">All</option>
                  <?php if (!empty($filter_options['facilities'])) { foreach ($filter_options['facilities'] as $o) { ?>
                  <option value="<?php echo htmlspecialchars($o['value']); ?>"><?php echo htmlspecialchars($o['label']); ?></option>
                  <?php } } ?>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label">Job</label>
                <select class="form-control form-control-sm select2-filter" id="filterJob" name="job">
                  <option value="">All</option>
                  <?php if (!empty($filter_options['jobs'])) { foreach ($filter_options['jobs'] as $o) { ?>
                  <option value="<?php echo htmlspecialchars($o['value']); ?>"><?php echo htmlspecialchars($o['label']); ?></option>
                  <?php } } ?>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label">Institution Type</label>
                <select class="form-control form-control-sm select2-filter" id="filterInstitutionType" name="institution_type">
                  <option value="">All</option>
                  <?php if (!empty($filter_options['institution_types'])) { foreach ($filter_options['institution_types'] as $o) { ?>
                  <option value="<?php echo htmlspecialchars($o['value']); ?>"><?php echo htmlspecialchars($o['label']); ?></option>
                  <?php } } ?>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label">Facility Type</label>
                <select class="form-control form-control-sm select2-filter" id="filterFacilityType" name="facility_type">
                  <option value="">All</option>
                  <?php if (!empty($filter_options['facility_types'])) { foreach ($filter_options['facility_types'] as $o) { ?>
                  <option value="<?php echo htmlspecialchars($o['value']); ?>"><?php echo htmlspecialchars($o['label']); ?></option>
                  <?php } } ?>
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

<script>
$(document).ready(function() {
    var baseUrl = '<?php echo base_url(); ?>';
    var canMarkDisabled = true;
    var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
    var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';
    var table = $('#staffTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: baseUrl + 'employees/all_ihris_staff',
            type: 'POST',
            data: function(d) {
                d.globalSearch = $('#globalSearch').val();
                d.includeInactive = $('#includeInactive').val() || 0;
                d.district = $('#filterDistrict').val() || '';
                d.facility = $('#filterFacility').val() || '';
                d.job = $('#filterJob').val() || '';
                d.institution_type = $('#filterInstitutionType').val() || '';
                d.facility_type = $('#filterFacilityType').val() || '';
                d['<?php echo $this->security->get_csrf_token_name(); ?>'] = '<?php echo $this->security->get_csrf_hash(); ?>';
            }
        },
        columns: [
            { data: 'serial', className: 'text-center' },
            { data: 'ihris_pid', className: 'text-center' },
            { data: 'nin', className: 'text-center' },
            { data: 'fullname', render: function(data, type, row) {
                if (type !== 'display') return data;
                var pid = row && row.ihris_pid ? String(row.ihris_pid) : '';
                var safe = (data || '').replace(/</g,'&lt;').replace(/"/g,'&quot;');
                if (!pid) return safe;
                var personId = pid.indexOf('person|') === 0 ? pid : 'person|' + pid;
                return '<a href="' + baseUrl + 'employees/employeeTimeLogs/' + encodeURIComponent(personId) + '">' + safe + '</a>';
            }},
            { data: 'gender', className: 'text-center' },
            { data: 'birth_date', className: 'text-center' },
            { data: 'phone', className: 'text-center' },
            { data: 'email' },
            { data: 'district', defaultContent: '' },
            { data: 'facility' },
            { data: 'department' },
            { data: 'job' },
            { data: 'employment_terms', className: 'text-center' },
            { data: 'card_number', className: 'text-center' },
            { data: 'status_label', className: 'text-center', render: function(data, type, row) {
                var label = data || (row.status === 0 ? 'Former Staff' : 'Active');
                var badge = row.status === 0 ? 'badge-secondary' : 'badge-success';
                return '<span class="badge ' + badge + '">' + String(label).replace(/</g,'&lt;') + '</span>';
            }},
            { data: null, className: 'text-center', orderable: false, responsivePriority: 1, render: function(data, type, row) {
                var pid = row.ihris_pid || '';
                var pidEnc = (pid.indexOf('person|') === 0) ? pid : ('person|' + pid);
                var inchargeHtml = (row.is_incharge == 1)
                    ? '<span class="badge badge-success">Already Incharge</span>'
                    : '<button type="button" class="btn btn-xs btn-info assign-incharge" data-staff=\'' + JSON.stringify(row).replace(/'/g, '&#39;') + '\'><i class="fas fa-user-plus"></i> Assign</button>';
                var statusHtml = '';
                if (canMarkDisabled) {
                    if (row.status === 0)
                        statusHtml = ' <button type="button" class="btn btn-xs btn-outline-success btn-mark-enabled" data-ihris-pid="' + String(pidEnc).replace(/"/g,'&quot;') + '"><i class="fas fa-user-check"></i> Active</button>';
                    else
                        statusHtml = ' <button type="button" class="btn btn-xs btn-outline-warning btn-mark-disabled" data-ihris-pid="' + String(pidEnc).replace(/"/g,'&quot;') + '"><i class="fas fa-user-minus"></i> Disable</button>';
                }
                return inchargeHtml + statusHtml;
            }}
        ],
        order: [[3, 'asc']],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        responsive: true,
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
        drawCallback: function() { updateShowingInfo(); },
        language: { processing: 'Loading...', search: 'Search:', info: 'Showing _START_ to _END_ of _TOTAL_', infoEmpty: 'No staff', zeroRecords: 'No matching staff' }
    });

    function updateShowingInfo() {
        var info = table.page.info();
        $('#showingInfo').text('Showing ' + (info.recordsDisplayed ? (info.start + 1) + '–' + Math.min(info.end, info.recordsDisplayed) : 0) + ' of ' + info.recordsDisplayed);
    }

    $('.select2-filter').select2({ theme: 'bootstrap', width: '100%' });

    $('#filterForm').on('submit', function(e) { e.preventDefault(); table.ajax.reload(); });
    $('.select2-filter').on('change', function() { table.ajax.reload(); });
    $('#resetFilters').on('click', function() {
        $('#globalSearch').val('');
        $('#filterDistrict, #filterFacility, #filterJob, #filterInstitutionType, #filterFacilityType').val('').trigger('change');
        $('#includeInactive').val('0');
        $('#includeInactiveToggle').removeClass('btn-success').addClass('btn-secondary').attr('aria-checked', 'false');
        $('#includeInactiveToggle .toggle-off').removeClass('d-none');
        $('#includeInactiveToggle .toggle-on').addClass('d-none');
        table.ajax.reload();
    });

    function setIncludeInactiveValue(on) {
        $('#includeInactive').val(on ? '1' : '0');
        $('#includeInactiveToggle').attr('aria-checked', on ? 'true' : 'false').toggleClass('btn-secondary', !on).toggleClass('btn-success', on);
        $('#includeInactiveToggle .toggle-off').toggleClass('d-none', on);
        $('#includeInactiveToggle .toggle-on').toggleClass('d-none', !on);
    }
    $('#includeInactiveToggle').on('click', function() {
        setIncludeInactiveValue($('#includeInactive').val() !== '1');
        table.ajax.reload();
    });

    $(document).on('click', '.assign-incharge', function() {
        var staffJson = $(this).attr('data-staff');
        try {
            var staffData = typeof staffJson === 'object' ? staffJson : JSON.parse(staffJson);
            $('.staff-name').text(staffData.fullname || '');
            $('.staff-job').text(staffData.job || '');
            $('.staff-facility').text(staffData.facility || '');
            $('input[name="name"]').val(staffData.fullname || '');
            $('input[name="username"]').val(staffData.ihris_pid || '');
            $('input[name="email"]').val(staffData.email || '');
            $('input[name="ihris_pid"]').val(staffData.ihris_pid || '');
            $('input[name="district_id"]').val(staffData.district_id || '');
            $('input[name="facility_id[]"]').val((staffData.facility_id || '') + '_' + (staffData.facility || ''));
            $('input[name="department_id"]').val(staffData.department_id || '');
            $('input[name="password"]').val('<?php echo Modules::run("svariables/getSettings")->default_password; ?>');
            $('#inchargeModal').modal('show');
        } catch (e) {}
    });
    $('#inchargeForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $('#inchargeForm button[type="submit"]');
        var orig = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        $.ajax({
            url: baseUrl + 'auth/addUser',
            method: 'POST',
            data: $('#inchargeForm').serialize() + '&' + csrfName + '=' + encodeURIComponent(csrfHash),
            success: function() {
                $.notify('Incharge assigned.', 'success');
                $('#inchargeModal').modal('hide');
                table.ajax.reload();
            },
            error: function() { $.notify('Error assigning incharge.', 'error'); },
            complete: function() { btn.prop('disabled', false).html(orig); }
        });
    });

    function parseJsonRes(res) { if (typeof res === 'string') { try { return JSON.parse(res); } catch(e) { return {}; } } return res || {}; }
    $('#staffTable').on('click', '.btn-mark-disabled', function() {
        var btn = $(this), pid = btn.data('ihris-pid');
        if (!pid) return;
        btn.prop('disabled', true);
        $.post(baseUrl + 'employees/setStaffDisabled', { ihris_pid: pid, [csrfName]: csrfHash }).done(function(res) {
            var d = parseJsonRes(res);
            if (d.success) { var row = btn.closest('tr'), rowData = table.row(row).data(); if (rowData) { rowData.status = 0; rowData.status_label = 'Former Staff'; table.row(row).data(rowData).draw(false); } $.notify(d.message || 'Marked as Former Staff.', 'success'); }
            else $.notify(d.message || 'Failed', 'error');
        }).fail(function() { $.notify('Request failed', 'error'); }).always(function() { btn.prop('disabled', false); });
    });
    $('#staffTable').on('click', '.btn-mark-enabled', function() {
        var btn = $(this), pid = btn.data('ihris-pid');
        if (!pid) return;
        btn.prop('disabled', true);
        $.post(baseUrl + 'employees/setStaffEnabled', { ihris_pid: pid, [csrfName]: csrfHash }).done(function(res) {
            var d = parseJsonRes(res);
            if (d.success) { var row = btn.closest('tr'), rowData = table.row(row).data(); if (rowData) { rowData.status = 1; rowData.status_label = 'Active'; table.row(row).data(rowData).draw(false); } $.notify(d.message || 'Marked as Active.', 'success'); }
            else $.notify(d.message || 'Failed', 'error');
        }).fail(function() { $.notify('Request failed', 'error'); }).always(function() { btn.prop('disabled', false); });
    });
});
</script>
