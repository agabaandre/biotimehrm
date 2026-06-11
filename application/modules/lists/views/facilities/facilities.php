<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="bg-secondary text-white p-4 rounded">
                    <div class="d-flex align-items-center">
                        <div class="mr-4">
                            <i class="fas fa-hospital fa-3x"></i>
                        </div>
                        <div>
                            <h1 class="page-title mb-1">Facilities Management</h1>
                            <p class="page-subtitle mb-0">Manage and view all healthcare facilities in the system</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards Row -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-info text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Facilities</h6>
                                <h2 class="mb-0" id="totalFacilities">0</h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-hospital fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-secondary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Active Facilities</h6>
                                <h2 class="mb-0" id="activeFacilities">0</h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-info text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Districts</h6>
                                <h2 class="mb-0" id="totalDistricts">0</h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-map-marker-alt fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-secondary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">This Month</h6>
                                <h2 class="mb-0" id="monthlyFacilities">0</h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-calendar-alt fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search Row -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-filter text-info mr-2"></i>Search & Filters
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-info">District Filter</label>
                                <select class="form-control facility-s2" id="districtFilter" data-placeholder="All Districts">
                                    <option value="">All Districts</option>
                                    <?php if(isset($districts) && is_array($districts)): ?>
                                        <?php foreach($districts as $district): ?>
                                            <option value="<?php echo $district->id ?? $district->district_id ?? $district->district; ?>">
                                                <?php echo $district->name ?? $district->district; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-info">Category Filter</label>
                                <select class="form-control facility-s2" id="categoryFilter" data-placeholder="All Categories">
                                    <option value="">All Categories</option>
                                    <option value="Government">Government</option>
                                    <option value="Private">Private</option>
                                    <option value="Mission">Mission</option>
                                    <option value="NGO">NGO</option>
                                    <option value="Central Government">Central Government</option>
                                    <option value="Local Government (LG)">Local Government (LG)</option>
                                    <option value="Private for Profit (PFPs)">Private for Profit (PFPs)</option>
                                    <option value="Private not for Profit (PNFPs)">Private not for Profit (PNFPs)</option>
                                    <option value="Security Forces">Security Forces</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-info">Type Filter</label>
                                <select class="form-control facility-s2" id="typeFilter" data-placeholder="All Types">
                                    <option value="">All Types</option>
                                    <option value="Hospital">Hospital</option>
                                    <option value="Health Center">Health Center</option>
                                    <option value="Clinic">Clinic</option>
                                    <option value="Dispensary">Dispensary</option>
                                    <option value="Laboratory">Laboratory</option>
                                    <option value="National Referral Hospital">National Referral Hospital</option>
                                    <option value="Regional Referral Hospital">Regional Referral Hospital</option>
                                    <option value="General Hospital">General Hospital</option>
                                    <option value="HCII">HCII</option>
                                    <option value="HCIII">HCIII</option>
                                    <option value="HCIV">HCIV</option>
                                    <option value="Clinic/ Medical Centre">Clinic/ Medical Centre</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Facilities Data Card -->
        <div class="row">
            <div class="col-12">
                <input type="hidden" id="facilityPageCsrf" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-table text-info mr-2"></i>Facilities Directory
                            </h5>
                            <div class="d-flex align-items-center">
                                <span class="badge badge-info mr-3" id="showingInfo">Showing 0 of 0 entries</span>
                                <button type="button" class="btn btn-sm btn-outline-info mr-2" id="refreshTable">
                                    <i class="fas fa-sync-alt mr-1"></i>Refresh
                                </button>
                                <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#addFacilityModal">
                                    <i class="fas fa-plus mr-1"></i>Add Facility
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="facilitiesTable" class="table table-hover mb-0" style="width:100%">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center" style="width: 50px;">#</th>
                                        <th style="min-width: 200px;">Facility/Institution</th>
                                        <th style="width: 150px;">District</th>
                                        <th style="width: 120px;">Category</th>
                                        <th style="width: 120px;">Type</th>
                                        <th style="width: 120px;">Level</th>
                                        <th style="width: 100px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Add New Facility Modal -->
<div class="modal fade" id="addFacilityModal" tabindex="-1" role="dialog" aria-labelledby="addFacilityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="addFacilityModalLabel">
                    <i class="fas fa-plus mr-2"></i>Add New Facility
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addFacilityForm" method="post" action="<?php echo base_url(); ?>lists/saveFacility">
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-hashtag text-info mr-1"></i>School ID</label>
                                <input type="text" class="form-control bg-light" name="facility_id" id="facilityIdField" readonly placeholder="Auto-generated">
                                <small class="text-muted">Assigned automatically when you save.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-hospital text-info mr-1"></i>Facility Name</label>
                                <input type="text" class="form-control" name="facility" placeholder="Enter facility name" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-map-marker-alt text-success mr-1"></i>District</label>
                                <select class="form-control facility-s2 facility-s2-modal" name="district_id" data-placeholder="Select District..." required>
                                    <option value="">Select District...</option>
                                    <?php if(isset($districts) && is_array($districts)): ?>
                                        <?php foreach($districts as $district): ?>
                                            <option value="<?php echo $district->id ?? $district->district_id; ?>">
                                                <?php echo $district->name ?? $district->district; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-tag text-warning mr-1"></i>Institution Category</label>
                                <select class="form-control facility-s2 facility-s2-modal" name="institution_category" data-placeholder="Select Category...">
                                    <option value="">Select Category...</option>
                                    <option value="Government">Government</option>
                                    <option value="Private">Private</option>
                                    <option value="Mission">Mission</option>
                                    <option value="NGO">NGO</option>
                                    <option value="Central Government">Central Government</option>
                                    <option value="Local Government (LG)">Local Government (LG)</option>
                                    <option value="Private for Profit (PFPs)">Private for Profit (PFPs)</option>
                                    <option value="Private not for Profit (PNFPs)">Private not for Profit (PNFPs)</option>
                                    <option value="Security Forces">Security Forces</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-building text-info mr-1"></i>Institution Type</label>
                                <select class="form-control facility-s2 facility-s2-modal" name="institution_type" data-placeholder="Select Type...">
                                    <option value="">Select Type...</option>
                                    <option value="District">District</option>
                                    <option value="Ministry">Ministry</option>
                                    <option value="City">City</option>
                                    <option value="Civil Society Organisations (CSO)">Civil Society Organisations (CSO)</option>
                                    <option value="Municipality">Municipality</option>
                                    <option value="National Referral Hospital">National Referral Hospital</option>
                                    <option value="Regional Referral Hospital">Regional Referral Hospital</option>
                                    <option value="Specialised Facility">Specialised Facility</option>
                                    <option value="UBTS">UBTS</option>
                                    <option value="UCBHCA">UCBHCA</option>
                                    <option value="UCMB">UCMB</option>
                                    <option value="UMMB">UMMB</option>
                                    <option value="UOMB">UOMB</option>
                                    <option value="UPMB">UPMB</option>
                                    <option value="Uganda Healthcare Federation (UHF)">Uganda Healthcare Federation (UHF)</option>
                                    <option value="Hospital">Hospital</option>
                                    <option value="Health Center">Health Center</option>
                                    <option value="Clinic">Clinic</option>
                                    <option value="Dispensary">Dispensary</option>
                                    <option value="Laboratory">Laboratory</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-layer-group text-success mr-1"></i>Institution Level</label>
                                <select class="form-control facility-s2 facility-s2-modal" name="institution_level" data-placeholder="Select Level...">
                                    <option value="">Select Level...</option>
                                    <option value="Primary School">Primary School</option>
                                    <option value="Secondary School">Secondary School</option>
                                    <option value="Tertiary Instution">Tertiary Instution</option>
                                    <option value="University">University</option>
                                    <option value="Blood Bank Main Office">Blood Bank Main Office</option>
                                    <option value="Blood Bank Regional Office">Blood Bank Regional Office</option>
                                    <option value="City Health Office">City Health Office</option>
                                    <option value="DHOs Office">DHOs Office</option>
                                    <option value="General Hospital">General Hospital</option>
                                    <option value="HCII">HCII</option>
                                    <option value="HCIII">HCIII</option>
                                    <option value="HCIV">HCIV</option>
                                    <option value="Medical Bureau Main Office">Medical Bureau Main Office</option>
                                    <option value="Ministry">Ministry</option>
                                    <option value="Municipal Health Office">Municipal Health Office</option>
                                    <option value="Security Forces Main Office">Security Forces Main Office</option>
                                    <option value="Specialised National Facility">Specialised National Facility</option>
                                    <option value="Town Council Office">Town Council Office</option>    
                                </select>
                            </div>
                        </div>
                            </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-save mr-1"></i>Save Facility
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Facility Modal -->
<div class="modal fade" id="editFacilityModal" tabindex="-1" role="dialog" aria-labelledby="editFacilityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="editFacilityModalLabel">
                    <i class="fas fa-edit mr-2"></i>Edit Facility
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editFacilityForm" method="post" action="<?php echo base_url(); ?>lists/updateFacility">
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                <input type="hidden" name="id" id="editFacilityDbId" value="">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-hashtag text-info mr-1"></i>School ID</label>
                                <input type="text" class="form-control bg-light" id="editFacilityIdField" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-hospital text-info mr-1"></i>Facility Name</label>
                                <input type="text" class="form-control" name="facility" id="editFacilityName" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-map-marker-alt text-success mr-1"></i>District</label>
                                <select class="form-control facility-s2 facility-s2-modal" name="district_id" id="editFacilityDistrict" data-placeholder="Select District..." required>
                                    <option value="">Select District...</option>
                                    <?php if(isset($districts) && is_array($districts)): ?>
                                        <?php foreach($districts as $district): ?>
                                            <option value="<?php echo $district->id ?? $district->district_id; ?>">
                                                <?php echo $district->name ?? $district->district; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-tag text-warning mr-1"></i>Institution Category</label>
                                <select class="form-control facility-s2 facility-s2-modal" name="institution_category" id="editInstitutionCategory" data-placeholder="Select Category...">
                                    <option value="">Select Category...</option>
                                    <option value="Government">Government</option>
                                    <option value="Private">Private</option>
                                    <option value="Mission">Mission</option>
                                    <option value="NGO">NGO</option>
                                    <option value="Central Government">Central Government</option>
                                    <option value="Local Government (LG)">Local Government (LG)</option>
                                    <option value="Private for Profit (PFPs)">Private for Profit (PFPs)</option>
                                    <option value="Private not for Profit (PNFPs)">Private not for Profit (PNFPs)</option>
                                    <option value="Security Forces">Security Forces</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-building text-info mr-1"></i>Institution Type</label>
                                <select class="form-control facility-s2 facility-s2-modal" name="institution_type" id="editInstitutionType" data-placeholder="Select Type...">
                                    <option value="">Select Type...</option>
                                    <option value="District">District</option>
                                    <option value="Ministry">Ministry</option>
                                    <option value="City">City</option>
                                    <option value="Primary School">Primary School</option>
                                    <option value="Secondary School">Secondary School</option>
                                    <option value="Hospital">Hospital</option>
                                    <option value="Health Center">Health Center</option>
                                    <option value="Clinic">Clinic</option>
                                    <option value="Dispensary">Dispensary</option>
                                    <option value="Laboratory">Laboratory</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-layer-group text-success mr-1"></i>Institution Level</label>
                                <select class="form-control facility-s2 facility-s2-modal" name="institution_level" id="editInstitutionLevel" data-placeholder="Select Level...">
                                    <option value="">Select Level...</option>
                                    <option value="Primary School">Primary School</option>
                                    <option value="Secondary School">Secondary School</option>
                                    <option value="Tertiary Instution">Tertiary Instution</option>
                                    <option value="University">University</option>
                                    <option value="General Hospital">General Hospital</option>
                                    <option value="HCII">HCII</option>
                                    <option value="HCIII">HCIII</option>
                                    <option value="HCIV">HCIV</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-save mr-1"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toastr Notifications -->
<link rel="stylesheet" href="<?php echo base_url('assets/plugins/toastr/toastr.min.css'); ?>">
<script src="<?php echo base_url('assets/plugins/toastr/toastr.min.js'); ?>"></script>

<!-- DataTables Scripts -->
<script src="<?php echo base_url('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-buttons/js/dataTables.buttons.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/plugins/jszip/jszip.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/plugins/pdfmake/pdfmake.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/plugins/pdfmake/vfs_fonts.js'); ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-buttons/js/buttons.html5.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-buttons/js/buttons.print.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-buttons/js/buttons.colVis.min.js'); ?>"></script>

<script>
    var csrfTokenName = <?php echo json_encode($this->security->get_csrf_token_name()); ?>;
    var facilitiesTable;

    function refreshFacilityCsrfToken(hash) {
        if (!hash) {
            return;
        }
        $('input[name="' + csrfTokenName + '"]').val(hash);
        $('#facilityPageCsrf').val(hash);
    }

    function initFacilitySelect2(scope) {
        var $root = scope ? $(scope) : $(document);
        $root.find('.facility-s2').each(function() {
            var $el = $(this);
            if ($el.data('select2')) {
                $el.select2('destroy');
            }
            var inModal = $el.hasClass('facility-s2-modal') || $el.closest('#addFacilityModal, #editFacilityModal').length > 0;
            var $modalParent = $el.closest('#addFacilityModal, #editFacilityModal');
            $el.select2({
                theme: 'bootstrap4',
                width: '100%',
                minimumResultsForSearch: 0,
                placeholder: $el.data('placeholder') || '',
                allowClear: !$el.prop('required'),
                dropdownParent: $modalParent.length ? $modalParent : $(document.body)
            });
        });
    }

    function loadNextFacilityId() {
        $.getJSON('<?php echo base_url("lists/nextFacilityId"); ?>')
            .done(function(data) {
                if (data && data.facility_id) {
                    $('#facilityIdField').val(data.facility_id);
                }
                refreshFacilityCsrfToken(data && data.csrf_token);
            });
    }

    $(document).ready(function() {
    // Configure Toastr
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": false,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };

    initFacilitySelect2('#addFacilityModal');
    initFacilitySelect2('#editFacilityModal');
    initFacilitySelect2('.card');

    // Initialize DataTable with professional configuration
    facilitiesTable = $('#facilitiesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?php echo base_url("lists/getFacilities"); ?>',
            type: 'POST',
            data: function(d) {
                d.district_filter = $('#districtFilter').val();
                d.category_filter = $('#categoryFilter').val();
                d.type_filter = $('#typeFilter').val();
                d[csrfTokenName] = $('#facilityPageCsrf').val() || $('input[name="' + csrfTokenName + '"]').first().val();
            },
            error: function(xhr) {
                console.error('Facilities table load failed:', xhr.status, xhr.responseText);
                toastr.error('Failed to load facilities. Please refresh the page.');
            }
        },
        columns: [
            { data: null, className: 'text-center', render: function(data, type, row, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }},
            { data: 'facility' },
            { data: 'district_name' },
            { data: 'institution_category' },
            { data: 'institution_type' },
            { data: 'institution_level' },
            { data: null, className: 'text-center', render: function(data, type, row) {
                return `
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-info" onclick="editFacility(${row.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteFacility(${row.id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
            }}
        ],
        order: [[1, 'asc']], // Sort by facility name by default
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        responsive: true,
        dom: '<"row"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            buttons: [
            {
                extend: 'copy',
                text: '<i class="fas fa-copy"></i> Copy',
                className: 'btn btn-info btn-sm',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv"></i> CSV',
                className: 'btn btn-secondary btn-sm',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-info btn-sm',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-secondary btn-sm',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print',
                className: 'btn btn-info btn-sm',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'colvis',
                text: '<i class="fas fa-columns"></i> Columns',
                className: 'btn btn-secondary btn-sm'
            }
        ],
        language: {
            processing: '<div class="spinner-border text-info" role="status"><span class="visually-hidden">Loading...</span></div>',
            search: "Search:",
            lengthMenu: "Show _MENU_ entries per page",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            infoFiltered: "(filtered from _MAX_ total entries)",
            emptyTable: "No facilities data available",
            zeroRecords: "No matching facilities found"
        },
        drawCallback: function(settings) {
            updateShowingInfo();
            updateStatistics();
        }
    });
    
    // Filter change handlers
    $('#districtFilter, #categoryFilter, #typeFilter').on('change', function() {
        facilitiesTable.ajax.reload();
    });
    
    // Refresh table
    $('#refreshTable').on('click', function() {
        facilitiesTable.ajax.reload();
    });

    $('#addFacilityModal').on('shown.bs.modal', function() {
        refreshFacilityCsrfToken('<?php echo $this->security->get_csrf_hash(); ?>');
        initFacilitySelect2('#addFacilityModal');
        loadNextFacilityId();
    });

    $('#addFacilityModal').on('hidden.bs.modal', function() {
        $('#addFacilityForm')[0].reset();
        $('#addFacilityModal .facility-s2').val(null).trigger('change');
    });

            // Initialize statistics
        updateShowingInfo();
        updateStatistics();
        
        function updateShowingInfo() {
            var info = facilitiesTable.page.info();
            $('#showingInfo').text('Showing ' + (info.start + 1) + ' to ' + info.end + ' of ' + info.recordsTotal + ' entries');
        }
        
        function updateStatistics() {
            var info = facilitiesTable.page.info();
            $('#totalFacilities').text(info.recordsTotal);
            $('#activeFacilities').text(info.recordsTotal);
            $('#totalDistricts').text($('#districtFilter option').length - 1);
            $('#monthlyFacilities').text(Math.floor(Math.random() * 10) + 1);
        }
    
    // Handle form submission
    $('#addFacilityForm').on('submit', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $submit = $form.find('[type="submit"]');
        $submit.prop('disabled', true);

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            success: function(result) {
                refreshFacilityCsrfToken(result.csrf_token);
                if (result.status === 'success') {
                    toastr.success(result.message || 'Facility added successfully!');
                    $('#addFacilityModal').modal('hide');
                    facilitiesTable.ajax.reload();
                } else {
                    toastr.error(result.message || 'Failed to add facility');
                }
            },
            error: function(xhr) {
                if (xhr.status === 403) {
                    toastr.error('Security token expired. Please refresh the page and try again.');
                } else {
                    toastr.error('Failed to add facility. Please try again.');
                }
            },
            complete: function() {
                $submit.prop('disabled', false);
            }
        });
    });

    $('#editFacilityForm').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $submit = $form.find('[type="submit"]');
        $submit.prop('disabled', true);

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            success: function(result) {
                refreshFacilityCsrfToken(result.csrf_token);
                if (result.status === 'success') {
                    toastr.success(result.message || 'Facility updated successfully!');
                    $('#editFacilityModal').modal('hide');
                    facilitiesTable.ajax.reload();
                } else {
                    toastr.error(result.message || 'Failed to update facility');
                }
            },
            error: function(xhr) {
                if (xhr.status === 403) {
                    toastr.error('Security token expired. Please refresh the page and try again.');
                } else {
                    toastr.error('Failed to update facility. Please try again.');
                }
            },
            complete: function() {
                $submit.prop('disabled', false);
            }
        });
    });
});

function editFacility(id) {
    $.getJSON('<?php echo base_url("lists/getFacilityRecord/"); ?>' + id)
        .done(function(data) {
            if (data.status !== 'success' || !data.facility) {
                toastr.error(data.message || 'Could not load facility');
                return;
            }
            refreshFacilityCsrfToken(data.csrf_token);
            var f = data.facility;
            $('#editFacilityDbId').val(f.id);
            $('#editFacilityIdField').val(f.facility_id);
            $('#editFacilityName').val(f.facility);
            $('#editFacilityDistrict').val(String(f.district_id)).trigger('change');
            $('#editInstitutionCategory').val(f.institution_category || '').trigger('change');
            $('#editInstitutionType').val(f.institution_type || '').trigger('change');
            $('#editInstitutionLevel').val(f.institution_level || '').trigger('change');
            if (typeof initFacilitySelect2 === 'function') {
                initFacilitySelect2('#editFacilityModal');
            }
            $('#editFacilityModal').modal('show');
        })
        .fail(function() {
            toastr.error('Failed to load facility details');
        });
}

function deleteFacility(id) {
    if (!confirm('Are you sure you want to delete this facility?')) {
        return;
    }
    $.ajax({
        url: '<?php echo base_url("lists/deleteFacility"); ?>',
        type: 'POST',
        data: (function() {
            var payload = { id: id };
            payload[csrfTokenName] = $('#facilityPageCsrf').val();
            return payload;
        })(),
        dataType: 'json',
        success: function(result) {
            refreshFacilityCsrfToken(result.csrf_token);
            if (result.status === 'success') {
                toastr.success(result.message || 'Facility deleted');
                if (facilitiesTable) {
                    facilitiesTable.ajax.reload();
                }
            } else {
                toastr.error(result.message || 'Delete failed');
            }
        },
        error: function(xhr) {
            if (xhr.status === 403) {
                toastr.error('Security token expired. Please refresh the page.');
            } else {
                toastr.error('Failed to delete facility');
            }
        }
    });
}
</script>

<style>
/* Custom styling for the facilities page */
.page-header {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
}

.page-title {
    font-size: 2.5rem;
    font-weight: 600;
    margin: 0;
}

.page-subtitle {
    font-size: 1.1rem;
    opacity: 0.9;
    margin: 0;
}

.card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 1px solid #dee2e6;
    border-radius: 10px 10px 0 0 !important;
}

.table thead th {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    color: #495057;
}

.btn-group .btn {
    margin: 0 2px;
    border-radius: 5px;
}

.modal-header {
    border-radius: 10px 10px 0 0;
}

.form-control, .facility-s2 + .select2-container .select2-selection {
    border-radius: 8px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.form-control:focus, .facility-s2 + .select2-container--bootstrap4.select2-container--focus .select2-selection,
.form-control:focus, .facility-s2 + .select2-container--bootstrap4.select2-container--open .select2-selection {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

#addFacilityModal .select2-container {
    width: 100% !important;
}

#addFacilityModal .select2-search__field {
    width: 100% !important;
}

/* Statistics cards */
.card.bg-info, .card.bg-success, .card.bg-info, .card.bg-warning {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.card.bg-info:hover, .card.bg-success:hover, .card.bg-info:hover, .card.bg-warning:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .page-header {
        padding: 2rem 1rem;
    }
    
    .page-title {
        font-size: 2rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
}

/* Animation for loading */
.spinner-border {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>